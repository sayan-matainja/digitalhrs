<?php

namespace App\Http\Controllers\Web;

use App\Enum\HrSetupStatusEnum;
use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\PostRepository;
use App\Repositories\UserRepository;
use App\Requests\Promotion\PromotionRequest;
use App\Services\Notification\NotificationService;
use App\Services\Promotion\PromotionService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;

class PromotionController extends Controller
{
    use CustomAuthorizesRequests;
    private string $view = 'admin.promotion.';

    public function __construct(
        protected PromotionService     $promotionService,
        protected UserRepository       $userRepository, protected BranchRepository $branchRepository,
        protected DepartmentRepository $departmentRepository, protected CompanyRepository $companyRepository,
        protected PostRepository $postRepository, protected NotificationService $notificationService
    )
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('list_promotion');
        try {
            $filterParameters = [
                'branch_id' => $request->branch_id ?? null,
                'employee_id' => $request->employee_id ?? null,
                'department_id' => $request->department_id ?? null,
                'promotion_date' => $request->promotion_date ?? null,
            ];
            if(!auth('admin')->check() && auth()->check()){
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }

            $select = ['*'];
            $with = ['employee:id,name', 'post:id,post_name'];
            $promotionLists = $this->promotionService->getAllPromotionPaginated($filterParameters,$select, $with);

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view . 'index', compact('promotionLists','companyDetail','filterParameters'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        $this->authorize('create_promotion');

        try {
            $companyId = AppHelper::getAuthUserCompanyId();
            $isBsEnabled = AppHelper::ifDateInBsEnabled();
            $selectBranch = ['id', 'name'];
            $branch = $this->branchRepository->getLoggedInUserCompanyBranches($companyId, $selectBranch);
            $status = HrSetupStatusEnum::cases();

            return view($this->view . 'create', compact('branch', 'isBsEnabled', 'status'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
     * @param PromotionRequest $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(PromotionRequest $request)
    {
        $this->authorize('create_promotion');

        try {
            $validatedData = $request->validated();



            DB::beginTransaction();
            $promotionDetail = $this->promotionService->savePromotionDetail($validatedData);
            DB::commit();
            if ($promotionDetail && $validatedData['notification'] == 1) {
                $employee = $this->userRepository->findUserDetailById($validatedData['employee_id'],['*'],['post:id,post_name']);
                $oldPost = $employee?->post?->post_name;
                $post = $this->postRepository->getPostById($promotionDetail['post_id']);

                // notification to promoted employee
                if (!empty($oldPost)) {
                    $message = '🎉 Congratulations! You have been promoted from the position of ' . ucfirst($oldPost) . ' to ' . ucfirst($post->post_name) . 'effective from ' . \App\Helpers\AppHelper::formatDateForView($promotionDetail['promotion_date']) . '. We appreciate your hard work and dedication. Please access the system to review the complete details.';
                } else {
                    $message = '🎉 Congratulations! You have been promoted to the position of ' . ucfirst($post->post_name) . 'effective from ' . \App\Helpers\AppHelper::formatDateForView($promotionDetail['promotion_date']) . '. We appreciate your hard work and dedication. Please access the system to review the complete details.';
                }
                $this->sendNotification($message, $validatedData['employee_id']);

            }

            return redirect()->route('admin.promotion.index')->with('success', __('message.add_promotion'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @throws AuthorizationException
     */

    public function show($id)
    {
        $this->authorize('show_promotion');

        try {
            $select = ['*'];
            $with = ['branch:id,name', 'department:id,dept_name', 'createdBy:id,name', 'updatedBy:id,name', 'employee:id,name', 'post:id,post_name','oldPost:id,post_name'];
            $promotionDetail = $this->promotionService->findPromotionById($id, $select, $with);

            return view($this->view . 'show', compact('promotionDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     */
    public function edit($id)
    {
        $this->authorize('update_promotion');
        try {
            $promotionDetail = $this->promotionService->findPromotionById($id, ['*'],['oldPost:id,post_name']);
            $companyId = AppHelper::getAuthUserCompanyId();

            $isBsEnabled = AppHelper::ifDateInBsEnabled();
            $selectBranch = ['id', 'name'];

            $branch = $this->branchRepository->getLoggedInUserCompanyBranches($companyId, $selectBranch);

            // Fetch users by selected departments
            $filteredDepartment = isset($promotionDetail->branch_id)
                ? $this->departmentRepository->getAllActiveDepartmentsByBranchId($promotionDetail->branch_id, [], ['id', 'dept_name'])
                : [];

            $select = ['name', 'id'];
            $filteredUsers = isset($promotionDetail->department_id)
                ? $this->userRepository->getActiveEmployeeOfDepartment($promotionDetail->department_id, $select)
                : [];

            $filteredPosts = isset($promotionDetail->department_id)
                ? $this->postRepository->getAllActivePostsByDepartmentId($promotionDetail->department_id)
                : [];

            $status = HrSetupStatusEnum::cases();

            return view($this->view . 'edit', compact('promotionDetail', 'isBsEnabled', 'branch', 'filteredDepartment', 'filteredPosts', 'filteredUsers', 'status'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param PromotionRequest $request
     * @param int $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(PromotionRequest $request, $id): RedirectResponse
    {
        $this->authorize('update_promotion');
        try {

            $validatedData = $request->validated();


            $previousEmployee = $this->promotionService->findPromotionById($id, ['employee_id','post_id','old_post_id']);

            $previousEmployeeId = $previousEmployee->employee_id;
            $previousEmployeePostId = $previousEmployee->post_id;


            DB::beginTransaction();
            $promotionDetail = $this->promotionService->updatePromotionDetail($id, $validatedData);
            DB::commit();

            if ($promotionDetail && $validatedData['notification'] == 1) {

                if ($previousEmployeeId != $validatedData['employee_id']) {
                    // add notification
                    $employee = $this->userRepository->findUserDetailById($validatedData['employee_id'],['*'],['post:id,post_name']);
                    $oldPost = $employee?->post?->post_name;
                    $post = $this->postRepository->getPostById($promotionDetail['post_id']);

                    // notification to promoted employee
                    if (!empty($oldPost)) {
                        $message = '🎉 Congratulations! You have been promoted from the position of ' . ucfirst($oldPost) . ' to ' . ucfirst($post->post_name) . 'effective from ' . \App\Helpers\AppHelper::formatDateForView($promotionDetail['promotion_date']) . '. We appreciate your hard work and dedication. Please access the system to review the complete details.';
                    } else {
                        $message = '🎉 Congratulations! You have been promoted to the position of ' . ucfirst($post->post_name) . 'effective from ' . \App\Helpers\AppHelper::formatDateForView($promotionDetail['promotion_date']) . '. We appreciate your hard work and dedication. Please access the system to review the complete details.';
                    }
                    $this->sendNotification($message, $validatedData['employee_id']);

                    // Withdrawal notification
                    $removePromotion = $this->postRepository->getPostById($previousEmployeePostId);
                    $removeMessage = '⚠️ Your promotion to the position of ' . ucfirst($removePromotion->post_name) . ' has been withdrawn from consideration. No further action is required from your end.';
                    $this->sendNotification($removeMessage, $previousEmployeeId);

                }else{

                    // change notification
                    $message = '🔄 Your promotion details have been updated. Check your profile for the latest information!';
                    $this->sendNotification($message, $validatedData['employee_id']);

                }


            }
            return redirect()->route('admin.promotion.index')
                ->with('success', __('message.update_promotion'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_promotion');
        try {
            DB::beginTransaction();
            $this->promotionService->deletePromotion($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.delete_promotion'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    private function sendNotification($message, $userId)
    {
        SMPushHelper::sendPromotionNotification('Promotion Notification', $message, $userId);
    }


    public function getEmployeeAndPostByDepartment($departmentId): JsonResponse|RedirectResponse
    {
        try {

            $select = ['name', 'id'];
            $users = $this->userRepository->getAllActiveEmployeeOfDepartment($departmentId, $select);
            $posts = $this->postRepository->getAllActivePostsByDepartmentId($departmentId, [], ['post_name', 'id']);

            return response()->json([
                'users' => $users,
                'posts' => $posts,
            ]);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

//    /**
//     * @param Request $request
//     * @param $promotionId
//     * @return RedirectResponse
//     * @throws AuthorizationException
//     */
//    public function updatePromotionStatus(Request $request, $promotionId)
//    {
//        $this->authorize('update_promotion');
//
//
//        try {
//
//            $validatedData = $request->validate([
//                'status' => ['required', 'string', Rule::in(array_column(HrSetupStatusEnum::cases(), 'value'))],
//                'remark' => ['required', 'required_if:status,'.HrSetupStatusEnum::rejected->value, 'string', 'min:10'],
//            ]);
//
//            DB::beginTransaction();
//            $promotionDetail = $this->promotionService->updateStatus($promotionId, $validatedData);
//            DB::commit();
//
//            if($promotionDetail){
//                $notificationData = [
//                    'title' => 'Promotion Status Update',
//                    'type' => 'Promotion',
//                    'user_id' => [$promotionDetail->employee_id],
//                    'description' => 'Your Promotion has been ' . ucfirst($validatedData['status']),
//                    'notification_for_id' => $promotionId,
//                ];
//
//                $notification = $this->notificationService->store($notificationData);
//
//                if($notification){
//                    $this->sendNotification($notification['description'],$promotionDetail->employee_id);
//                }
//            }
//
//
//            return redirect()
//                ->route('admin.promotion.index')
//                ->with('success', __('message.status_update'));
//        } catch (Exception $exception) {
//            DB::rollBack();
//            return redirect()->back()->with('danger', $exception->getMessage());
//        }
//    }
}
