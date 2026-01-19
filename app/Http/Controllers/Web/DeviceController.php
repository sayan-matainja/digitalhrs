<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\CompanyRepository;
use App\Requests\Device\DeviceRequest;
use App\Services\Device\DeviceService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class DeviceController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.device.';

    public function __construct( protected DeviceService $deviceService, protected CompanyRepository $companyRepository){}

    /**
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('list_device');
        try {

            $select = ['*'];
            $with = [];
            $assetLists = $this->deviceService->getAllDevicesPaginated($select,$with);

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view . 'index', compact('assetLists','companyDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('create_device');
        try {
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view . 'create',compact('companyDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function store(DeviceRequest $request)
    {
        $this->authorize('create_device');
        try {
            $validatedData = $request->validated();
            DB::beginTransaction();
            $this->deviceService->saveDeviceDetail($validatedData);
            DB::commit();
            return redirect()->route('admin.biometric-devices.index')->with('success', __('message.device_saved'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
     * @throws AuthorizationException
     */
    public function edit($id)
    {
        $this->authorize('edit_device');
        try {
            $assetDetail = $this->deviceService->findDeviceById($id);

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view . 'edit', compact('assetDetail','companyDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function update(DeviceRequest $request, $id)
    {
        $this->authorize('edit_device');
        try {
            $validatedData = $request->validated();
            DB::beginTransaction();
            $this->deviceService->updateDeviceDetail($id, $validatedData);
            DB::commit();
            return redirect()->route('admin.biometric-devices.index')
                ->with('success', __('message.device_update'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function delete($id)
    {
        $this->authorize('delete_device');
        try {
            DB::beginTransaction();
                $this->deviceService->deleteDevice($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.device_delete'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }





}
