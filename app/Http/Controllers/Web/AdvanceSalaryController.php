<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Repositories\CompanyRepository;
use App\Repositories\GeneralSettingRepository;
use App\Requests\GeneralSetting\GeneralSettingRequest;
use App\Requests\Payroll\AdvanceSalary\AdvanceSalaryUpdateRequest;
use App\Services\Payroll\AdvanceSalaryService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdvanceSalaryController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.payroll.advanceSalary.';

    public function __construct(public AdvanceSalaryService $advanceSalaryService, public GeneralSettingRepository $generalSettingRepository, protected CompanyRepository $companyRepository,){}

    public function index(Request $request)
    {
        $this->authorize('view_advance_salary_list');
        try {

            $filterParameters = [
                'branch_id' => $request->branch_id ?? null,
                'department_id' => $request->department_id ?? null,
                'employee_id' => $request->employee_id ?? null,
                'status' => $request->status ?? null,
                'month' => $request->month ?? null
            ];
            $select = ['*'];
            $with = [];
            $advanceSalaryRequestLists = $this->advanceSalaryService->getAllAdvanceSalaryDetailPaginated($filterParameters,$select,$with);
            $months = AppHelper::getMonthsList();
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view . 'index',compact('advanceSalaryRequestLists','filterParameters','months','companyDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function show($id)
    {
        $this->authorize('update_advance_salary');
        try{

            $select = ['*'];
            $with = [
                'verifiedBy:id,name',
                'requestedBy:id,name',
                'attachments'
            ];
            $advanceSalaryDetail = $this->advanceSalaryService->findAdvanceSalaryDetailById($id,$with,$select);
            $attachments = $advanceSalaryDetail->attachments;
            return view($this->view.'show',compact('advanceSalaryDetail','attachments'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function update(AdvanceSalaryUpdateRequest $request,$id)
    {
        $this->authorize('update_advance_salary');
        try {

            $validatedData = $request->validated();
            $advanceSalaryRequestDetail = $this->advanceSalaryService->findAdvanceSalaryDetailById($id);
            $this->advanceSalaryService->advanceSalaryUpdateByAdmin($advanceSalaryRequestDetail,$validatedData);

            $notificationData = [
                'title' => 'Advance Salary '.ucfirst($validatedData['status']),
                'type' => 'Advance Salary',
                'user_id' => [$advanceSalaryRequestDetail->employee_id],
                'description' => 'Your advance salary requested on ' . date('M d Y', strtotime($advanceSalaryRequestDetail->advance_requested_date)) . ' has been ' . ucfirst($validatedData['status']),
                'notification_for_id' => $id,
            ];


            $this->sendAdvanceSalaryStatusNotification($notificationData,$advanceSalaryRequestDetail->employee_id);
            return redirect()->back()->with('success',  __('message.status_changed'));
        } catch (Exception $exception) {
            return redirect()->back()
                ->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    private function sendAdvanceSalaryStatusNotification($notificationData,$userId)
    {
        SMPushHelper::sendAdvanceSalaryNotification($notificationData['title'], $notificationData['description'],$userId);
    }

    public function delete($id)
    {
        $this->authorize('delete_advance_salary');
        try {

            DB::beginTransaction();
            $this->advanceSalaryService->delete($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.salary_deleted'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function setting()
    {
        $this->authorize('advance_salary_limit');

        try {

            $key = 'advance_salary_limit';
            $advanceSalarySetting = $this->generalSettingRepository->getGeneralSettingByKey($key);
            return view('admin.payrollSetting.advanceSalary.create',compact('advanceSalarySetting'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function updateSetting(GeneralSettingRequest $request, $id)
    {
        $this->authorize('advance_salary_limit');
        try {
            $validatedData = $request->validated();
            $generalSettingDetail = $this->generalSettingRepository->findOrFailGeneralSettingDetailById($id);

            $this->generalSettingRepository->update($generalSettingDetail, $validatedData);


            return redirect()->back()->with('success', 'Advance Salary Limit Updated Successfully');
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

}
