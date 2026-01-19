<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Repositories\AppSettingRepository;
use App\Repositories\FeatureRepository;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FeatureController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.feature.';


    public function __construct(protected FeatureRepository $featureRepository)
    {}

    public function index()
    {
        $this->authorize('feature_control');
        try{
            $select=['id','group','name','status'];
            $features = $this->featureRepository->getAll($select);
            return view($this->view.'index',compact('features'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $this->authorize('feature_control');

        try {
            DB::beginTransaction();
                $this->featureRepository->toggleStatus($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.status_changed'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

}
