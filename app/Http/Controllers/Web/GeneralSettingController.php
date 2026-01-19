<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\GeneralSettingRepository;
use App\Requests\GeneralSetting\GeneralSettingRequest;
use App\Traits\CustomAuthorizesRequests;
use Database\Seeders\EmployeeCodeSeeder;
use Exception;

class GeneralSettingController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.generalSetting.';

    private GeneralSettingRepository $generalSettingRepo;

    public function __construct(GeneralSettingRepository $generalSettingRepo)
    {
        $this->generalSettingRepo = $generalSettingRepo;
    }

    public function index()
    {
        $this->authorize('general_setting');
        try {
            $select=['*'];
            $generalSettings = $this->generalSettingRepo->getAllGeneralSettingDetails($select);
            return view($this->view . 'index', compact('generalSettings'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function create()
    {
        try {
            return view($this->view . 'create');
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function store(GeneralSettingRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $this->generalSettingRepo->store($validatedData);
            return redirect()->back()->with('success', __('message.general_setting_add'));
        } catch (Exception $e) {
            return redirect()->back()->with('danger', $e->getMessage())->withInput();
        }
    }


    public function edit($id)
    {
        try {
            $generalSettingDetail = $this->generalSettingRepo->findOrFailGeneralSettingDetailById($id);
            return view($this->view . 'edit', compact('generalSettingDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function update(GeneralSettingRequest $request, $id)
    {
        $this->authorize('general_setting');
        try {
            $validatedData = $request->validated();
            $generalSettingDetail = $this->generalSettingRepo->findOrFailGeneralSettingDetailById($id);

            $this->generalSettingRepo->update($generalSettingDetail, $validatedData);

            if($generalSettingDetail->key == 'employee_code_prefix'){
                $employeeCodeSeeder = new EmployeeCodeSeeder();

                $employeeCodeSeeder->run();
            }
            return redirect()->back()->with('success', __('message.general_setting_update'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function delete($id)
    {
        try {
            $this->generalSettingRepo->delete($id);
            return redirect()->back()->with('success', __('message.general_setting_delete'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

}
