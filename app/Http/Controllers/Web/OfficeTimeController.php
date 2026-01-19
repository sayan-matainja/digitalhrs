<?php

namespace App\Http\Controllers\Web;

use App\Enum\ShiftTypeEnum;
use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\OfficeTime;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\OfficeTimeRepository;
use App\Repositories\UserRepository;
use App\Requests\OfficeTime\OfficeTimeRequest;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OfficeTimeController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.officeTime.';

    public function __construct(protected OfficeTimeRepository $officeTimeRepo, protected CompanyRepository $companyRepo, protected UserRepository $userRepository,
    protected BranchRepository $branchRepository)
    {}

    public function index(Request $request)
    {
        $this->authorize('list_office_time');
        try {
            $filterParameters = [
                'branch_id' => $request->branch_id ?? null,
                'shift_type' => $request->shift_type ?? null,
                'category' => $request->category ?? null,
            ];

            if(!auth('admin')->check() && auth()->check()){
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }
            $shifts = ShiftTypeEnum::cases();
            $category = OfficeTime::CATEGORY;

            $with=['branch:id,name'];
            $officeTimes = $this->officeTimeRepo->getAllCompanyOfficeTime($filterParameters,$with);
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepo->getCompanyDetail($select, $with);
            return view($this->view . 'index', compact('officeTimes','filterParameters','companyDetail','shifts','category'));
        } catch (\Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function create()
    {
        $this->authorize('create_office_time');
        try{

            $shifts = ShiftTypeEnum::cases();
            $category = OfficeTime::CATEGORY;
            $companyId = AppHelper::getAuthUserCompanyId();
            $selectBranch = ['id','name'];
            $branch = $this->branchRepository->getLoggedInUserCompanyBranches($companyId,$selectBranch);
            return view($this->view.'create',
                compact('shifts','category','branch')
            );
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function store(OfficeTimeRequest $request)
    {
        $this->authorize('create_office_time');
        try {
            $validatedData = $request->validated();

            $officeTimeCheck = $this->officeTimeRepo->validateTime($validatedData['opening_time'],$validatedData['closing_time'], $validatedData['branch_id']);


            if($officeTimeCheck){
                return redirect()->back()
                    ->with('danger',
                        __('message.office_time_already_exists', [
                            'opening_time' => date('h:i A', strtotime($validatedData['opening_time'])),
                            'closing_time' => date('h:i A', strtotime($validatedData['closing_time']))
                        ])
                    );

            }

            $validatedData['is_early_check_in'] = $validatedData['is_early_check_in'] ?? 0;
            $validatedData['is_early_check_out'] = $validatedData['is_early_check_out'] ?? 0;
            $validatedData['is_late_check_in'] = $validatedData['is_late_check_in'] ?? 0;
            $validatedData['is_late_check_out'] = $validatedData['is_late_check_out'] ?? 0;

            $validatedData['checkin_before'] = $validatedData['is_early_check_in'] == 1 ? $validatedData['checkin_before'] : null;
            $validatedData['checkout_before'] = $validatedData['is_early_check_out'] == 1 ? $validatedData['checkout_before'] : null;
            $validatedData['checkin_after'] = $validatedData['is_late_check_in'] == 1 ? $validatedData['checkin_after'] : null;
            $validatedData['checkout_after'] = $validatedData['is_late_check_out'] == 1 ? $validatedData['checkout_after'] : null;
            $validatedData['company_id'] = AppHelper::getAuthUserCompanyId();

            $message = __('message.office_time_added');
            if($validatedData['shift_type'] == ShiftTypeEnum::night->value){

                $message = __('message.office_time_added_night_shift');
            }

            DB::beginTransaction();
            $this->officeTimeRepo->store($validatedData);
            DB::commit();
            return redirect()->route('admin.office-times.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        try {
            $this->authorize('show_office_time');
            $select = ['opening_time','closing_time','shift','checkin_before','checkout_before','checkin_after','checkout_after','halfday_mark_time'];
            $officeTimes = $this->officeTimeRepo->findCompanyOfficeTimeById($id,$select);
            return response()->json([
                'data' => $officeTimes,
            ]);
        } catch (\Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    public function edit($id)
    {
        $this->authorize('edit_office_time');
        try{
            $officeTime = $this->officeTimeRepo->findCompanyOfficeTimeById($id);
            $shifts = ShiftTypeEnum::cases();
            $category = OfficeTime::CATEGORY;
            $companyId = AppHelper::getAuthUserCompanyId();
            $selectBranch = ['id','name'];
            $branch = $this->branchRepository->getLoggedInUserCompanyBranches($companyId,$selectBranch);
            return view($this->view.'edit', compact('officeTime','shifts','category','branch'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    public function update(OfficeTimeRequest $request, $id)
    {
        $this->authorize('edit_office_time');
        try{
            $validatedData = $request->validated();

            $officeTime = $this->officeTimeRepo->findCompanyOfficeTimeById($id);
            if(!$officeTime){
                throw new \Exception(__('message.office_time_not_found'),404);
            }

            if(strtotime($validatedData['opening_time']) != strtotime($officeTime->opening_time) || strtotime($validatedData['closing_time']) != strtotime( $officeTime->closing_time)){
                $officeTimeCheck = $this->officeTimeRepo->validateTime($validatedData['opening_time'],$validatedData['closing_time']);
                if($officeTimeCheck){
                    return redirect()->back()
                        ->with('danger',
                            __('message.office_time_already_exists', [
                                'opening_time' => date('h:i A', strtotime($validatedData['opening_time'])),
                                'closing_time' => date('h:i A', strtotime($validatedData['closing_time']))
                            ])
                        );
                }
            }

            $validatedData['is_early_check_in'] = $validatedData['is_early_check_in'] ?? 0;
            $validatedData['is_early_check_out'] = $validatedData['is_early_check_out'] ?? 0;
            $validatedData['is_late_check_in'] = $validatedData['is_late_check_in'] ?? 0;
            $validatedData['is_late_check_out'] = $validatedData['is_late_check_out'] ?? 0;

            $validatedData['checkin_before'] = $validatedData['is_early_check_in'] == 1 ? $validatedData['checkin_before'] : null;
            $validatedData['checkout_before'] = $validatedData['is_early_check_out'] == 1 ? $validatedData['checkout_before'] : null;
            $validatedData['checkin_after'] = $validatedData['is_late_check_in'] == 1 ? $validatedData['checkin_after'] : null;
            $validatedData['checkout_after'] = $validatedData['is_late_check_out'] == 1 ? $validatedData['checkout_after'] : null;

            $message = __('message.office_time_updated');
            if($validatedData['shift_type'] == ShiftTypeEnum::night->value){

                $message = __('message.office_time_updated_night_shift');
            }
            DB::beginTransaction();
            $this->officeTimeRepo->update($officeTime,$validatedData);
            DB::commit();
            return redirect()->route('admin.office-times.index')
                ->with('success', $message);
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function toggleStatus($id)
    {
        $this->authorize('edit_office_time');
        try {
            $checkUserOfficeTime = $this->userRepository->checkOfficeTime($id);
            if ($checkUserOfficeTime > 0) {
                return redirect()->back()->with('danger',  __('message.office_time_status_change_error'));
            }
            DB::beginTransaction();
            $this->officeTimeRepo->toggleStatus($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.status_changed'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_office_time');
        try {
            $officeTime = $this->officeTimeRepo->findCompanyOfficeTimeById($id);
            $checkUserOfficeTime = $this->userRepository->checkOfficeTime($id);
            if ($checkUserOfficeTime > 0) {
                return redirect()->back()->with('danger', __('message.office_time_delete_error'));
            }
            if (!$officeTime) {
                throw new \Exception(__('message.office_time_not_found'), 404);
            }
            DB::beginTransaction();
            $this->officeTimeRepo->delete($officeTime);
            DB::commit();
            return redirect()->back()->with('success',  __('message.office_time_deleted'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }
}
