<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Resources\award\AwardCollection;
use App\Resources\award\RecentAwardResource;
use App\Services\AwardManagement\AwardService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AwardApiController extends Controller
{

    public function __construct(protected AwardService $awardService)
    {}

    public function getEmployeeAwards(Request $request)
    {
        try {
            $awardData = [];
            $perPage = $request->input('per_page', 10);


            $select = ['*'];
            $with = [
                'employee:id,name,avatar',
                'type:id,title'
            ];
            $recentAward = $this->awardService->getRecentEmployeeAward($select,$with,getAuthUserCode());
            if (isset($recentAward)) {
                $date = Carbon::createFromFormat('Y-m-d', $recentAward->awarded_date);
                $daysAdd = AppHelper::getAwardDisplayLimit();
                $endDate = $date->copy()->addDays($daysAdd);

                if (strtotime(date('Y-m-d')) >= strtotime($date->format('Y-m-d')) &&
                    strtotime(date('Y-m-d')) <= strtotime($endDate->format('Y-m-d'))) {
                    $awardData['recent_award'] =  new RecentAwardResource($recentAward);
                } else {
                    $awardData['recent_award'] =  null;
                }

            } else {
                $awardData['recent_award'] = null;
            }

            $awardLists = $this->awardService->getEmployeeAward(getAuthUserCode(),$perPage,$select,$with);
            $awardData['total_awards'] = count($awardLists);
            $awardData['all_awards'] = new AwardCollection($awardLists);
            return AppHelper::sendSuccessResponse(__('index.data_found'), $awardData);
        }catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), 400);
        }
    }

}
