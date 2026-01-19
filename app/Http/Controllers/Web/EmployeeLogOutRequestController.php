<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Repositories\CompanyRepository;
use App\Repositories\UserRepository;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeLogOutRequestController extends Controller
{
    use CustomAuthorizesRequests;
    private $view ='admin.logoutRequest.';

    public function __construct(protected UserRepository $userRepository, protected CompanyRepository $companyRepository)
    {}

    public function getAllCompanyEmployeeLogOutRequest(Request $request)
    {
        $this->authorize('list_logout_request');
        try{
            $filterData = [
                'branch_id' => $request->branch_id ?? null,
                'department_id' => $request->department_id ?? null,
                'employee_id' => $request->employee_id ?? null,
            ];

            if(!auth('admin')->check() && auth()->check()){
                $filterData['branch_id'] = auth()->user()->branch_id;
            }
            $select = ['id','name','logout_status'];
            $logoutRequests = $this->userRepository->getAllCompanyEmployeeLogOutRequest($filterData,$select);
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view . 'index',compact('logoutRequests','companyDetail','filterData'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    public function acceptLogoutRequest($employeeId)
    {
        $this->authorize('accept_logout_request');
        try {
            DB::beginTransaction();
                $this->userRepository->acceptLogoutRequest($employeeId);
            DB::commit();
            return redirect()->back()->with('success', __('message.logout_request'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

}
