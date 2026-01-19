<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Repositories\AppSettingRepository;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AppSettingController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.appSetting.';

    private AppSettingRepository $appSettingRepo;

    public function __construct(AppSettingRepository $appSettingRepo)
    {
        $this->appSettingRepo = $appSettingRepo;
    }

    public function index()
    {
        $this->authorize('app_setting');
        try{
            $select=['id','name','slug','status'];
            $appSettings = $this->appSettingRepo->getAllAppSettings($select);
            return view($this->view.'index',compact('appSettings'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @param $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function toggleStatus($id)
    {
        $this->authorize('app_setting');
        try {
            if (env('DEMO_MODE', false)) {
                throw new Exception(__('message.add_company_warning'),400);
            }
            DB::beginTransaction();
                $this->appSettingRepo->toggleStatus($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.status_changed'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function changeTheme()
    {
        try{
            $slug = 'dark-theme';
           $themeDetail = $this->appSettingRepo->findAppSettingDetailBySlug($slug);
           if(!$themeDetail){
               throw new \Exception(__('message.theme_not_found'),404);
           }
           $status = $this->appSettingRepo->toggleTheme($themeDetail);
           if($status){
               Cache::forget('theme');
               $theme = $themeDetail->status ? 'dark' : 'light' ;
               Cache::forever('theme', $theme);
           }
           return AppHelper::sendSuccessResponse(__('message.theme_changed'));
        }catch(\Exception $exception){
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());
        }
    }


}
