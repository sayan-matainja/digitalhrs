<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\UserRepository;
use App\Requests\User\Api\UserChangePasswordRequest;
use App\Requests\User\Api\UserProfileUpdateApiRequest;
use App\Resources\award\AwardCollection;
use App\Resources\User\CompanyResource;
use App\Resources\User\EmployeeDetailResource;
use App\Resources\User\UserResource;
use App\Services\Attendance\AttendanceService;
use App\Services\AwardManagement\AwardService;
use App\Services\EmployeeCardSetting\EmployeeCardSettingService;
use App\Traits\CustomAuthorizesRequests;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Picqer\Barcode\Exceptions\UnknownTypeException;


class UserProfileApiController extends Controller
{
    use CustomAuthorizesRequests;

    public function __construct(protected UserRepository             $userRepo,
                                protected CompanyRepository          $companyRepo,
                                protected AttendanceService          $attendanceService,
                                protected BranchRepository           $branchRepository,
                                protected AwardService               $awardService,
                                protected EmployeeCardSettingService $cardTemplateService,
    )
    {}

    /**
     * @throws AuthorizationException
     */
    public function userProfileDetail(): JsonResponse
    {
        $this->authorize('view_profile');
        try {

            $with = [
                'branch:id,name',
                'company:id,name',
                'post:id,post_name',
                'department:id,dept_name',
                'role:id,name',
                'accountDetail'
            ];
            $select = ['users.*', 'branch_id', 'company_id', 'department_id', 'post_id', 'role_id'];
            $user = $this->userRepo->findUserDetailById(getAuthUserCode(), $select, $with);

//            $cardHtml = $this->generateEmployeeCardHtml($user);

//            $userDetail = (new UserResource($user))->cardHtml($cardHtml);

            $userDetail = new UserResource($user);

            return AppHelper::sendSuccessResponse(__('index.data_found'), $userDetail);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), 400);
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function changePassword(UserChangePasswordRequest $request): JsonResponse
    {
        $this->authorize('allow_change_password');

        try {
            $validatedData = $request->validated();
            $userDetail = $this->userRepo->findUserDetailById(getAuthUserCode());
            if(in_array($userDetail->username, User::DEMO_USERS_USERNAME)){
                throw new Exception(__('index.demo_version'),400);
            }
            if (!Hash::check($validatedData['current_password'], $userDetail->password)) {
                throw new Exception(__('index.incorrect_current_password'), 403);
            }
            if (Hash::check($validatedData['new_password'],$userDetail->password )) {
                throw new Exception(__('index.new_password_same_as_old'), 400);
            }
            DB::beginTransaction();
            $this->userRepo->changePassword($userDetail, $validatedData['new_password']);
            DB::commit();
            return AppHelper::sendSuccessResponse(__('index.password_changed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($exception->getMessage(), 400);
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function updateUserProfile(UserProfileUpdateApiRequest $request)
    {
        $this->authorize('update_profile');
        try {

            $validatedData = $request->validated();
            $userDetail = $this->userRepo->findUserDetailById(getAuthUserCode());
            if(in_array($userDetail->username, User::DEMO_USERS_USERNAME)){
                throw new Exception(__('index.demo_version'),400);
            }
            if (!$userDetail) {
                throw new Exception(__('index.user_not_found'), 404);
            }
            DB::beginTransaction();
                $this->userRepo->update($userDetail, $validatedData);
            DB::commit();
            return AppHelper::sendSuccessResponse(__('index.profile_updated'),new UserResource($userDetail));
        } catch (Exception $exception) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($exception->getMessage(), 400);
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function findEmployeeDetailById($userId): JsonResponse
    {
        $this->authorize('show_profile_detail');
        try {

            $with = ['branch:id,name', 'company:id,name', 'post:id,post_name', 'department:id,dept_name'];
            $select = ['users.*', 'branch_id', 'company_id', 'department_id', 'post_id'];

            $employee = $this->userRepo->findUserDetailById($userId, $select, $with);

            $awardList = $this->awardService->getEmployeeAward($userId,5, ['*'],['employee:id,name,avatar', 'type:id,title'], 1);

            // Wrap the employee details in a resource and attach the awards
            $employeeDetail = (new EmployeeDetailResource($employee))->additional(['awards' => new AwardCollection($awardList)]);

            return AppHelper::sendSuccessResponse(__('index.data_found'), $employeeDetail);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), 400);
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function getTeamSheetOfCompany()
    {
        $this->authorize('list_team_sheet');
        try {

            $data = [];

            $select = ['id', 'name'];
            $with = ['employee'];
//            $updateOnline = $this->updateOnlineStatusBasedOnTodayAttendance();
//            if($updateOnline){
                $companyWithEmployee = $this->companyRepo
                    ->findOrFailCompanyDetailById(AppHelper::getAuthUserCompanyId(), $select, $with);
                $companyDetail = new CompanyResource($companyWithEmployee);

                $branches = $this->branchRepository->getBranchesWithDepartments();
                $data = [
                    'companyDetail'=> $companyDetail,
                    'branches' => $branches
                ];

//            }
            return AppHelper::sendSuccessResponse(__('index.data_found'), $data);

        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getFile(), 400);
        }
    }

    private function updateOnlineStatusBasedOnTodayAttendance()
    {
        $select = ['id'];
        $with = ['employee:id,online_status,company_id',
            'employee.employeeTodayAttendance'
        ];
        try {
            $companyWithEmployee = $this->companyRepo->findOrFailCompanyDetailById(AppHelper::getAuthUserCompanyId(), $select, $with);
            $employeeDetail = $companyWithEmployee?->employee;
            foreach ($employeeDetail as $key => $value){

                $user['user_id'] = $value->id;
                $user['online_status'] = $value->online_status;
                $user['check_in_at'] = $value->employeeTodayAttendance[0]?->check_in_at;
                $user['check_out_at'] = $value->employeeTodayAttendance[0]?->check_out_at;
                if(is_null($user['check_in_at']) && $user['online_status'] == 1){
                    $this->attendanceService->updateUserOnlineStatusToOffline($user['user_id']);
                }
            }
            return true;
        } catch (Exception $exception) {
            AppHelper::sendErrorResponse($exception->getMessage(), 400);
            return;
        }
    }

    public function decodeBase64($b64, $file_folder_name){
        try{
            $bin = base64_decode($b64);
            $size = getImageSizeFromString($bin);
            if (empty($size['mime']) || strpos($size['mime'], 'image/') !== 0) {
                throw new Exception(__('index.invalid_base64_image'));
            }
            $ext = substr($size['mime'], 6);
            if (!in_array($ext, ['png', 'gif', 'jpeg', 'jfif', 'jpg', 'jif'])) {
                return "default.jpeg";
            }
            $path = User::AVATAR_UPLOAD_PATH;
            $fileName = uniqid().$file_folder_name;
            $img_file = $path. '/' . $fileName.'.'.$ext;
            file_put_contents($img_file, $bin);
            return $fileName . '.' . $ext;
        }catch(Exception $e){
            return AppHelper::sendErrorResponse($e->getMessage(),$e->getCode());
        }

    }


    public function storeLocation(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'latitude' => ['required'],
                'longitude' => ['required'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => __('index.validation_failed'),
                    'errors' => $validator->errors()->toArray()
                ],422);
            }
            $validatedData = $validator->validated();


            $userDetail = auth()->user();

            $validatedData['employee_id'] = $userDetail['id'];

            $this->userRepo->setEmployeeLocation($validatedData);

            return AppHelper::sendSuccessResponse('Location successfully sent', []);
        } catch (Exception $exception) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @throws \ImagickException
     * @throws UnknownTypeException
     */
    private function generateEmployeeCardHtml($user)
    {

//        $template = $this->cardTemplateService->getDefaultCardTemplate();
//
//        if (!$template) {
            return '<div style="padding:40px;text-align:center;color:#999;background:#f8f8f8;border-radius:16px;">No ID card template configured</div>';
//        }
//        $settings = collect($template->settings);
//        $photoUrl = User::AVATAR_UPLOAD_PATH;
//        $employeeData = (object) [
//            'name' => $user->name,
//            'department' => $user->department->dept_name ?? 'Head Office',
//            'designation' => $user->post->post_name ?? 'Software Engineer',
//            'employee_id' => $user->employee_code,
//            'photo_url' => asset($photoUrl . $user->avatar),
//        ];
//
//        $barcodeValue = $user->employee_code ?? 'EC18V-00123';
//        $barcodeColor = $settings->get('barcode_color', $settings->get('text_color', '#FFFFFF'));
//
//        $generator = new BarcodeGeneratorSVG();
//        $svg = $generator->getBarcode($barcodeValue, $generator::TYPE_CODE_128, 2.8, 78,$barcodeColor);
//        $barcodeSvg64 = base64_encode($svg);
//
//       return view('admin.employees.card', [
//            'settings'     => $settings->toArray(),
//            'employee'     => $employeeData,
//            'barcodeSvg64' => $barcodeSvg64,
//            'mode'         => 'pdf'
//        ])->render();
    }
}


