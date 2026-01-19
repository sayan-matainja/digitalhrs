<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Resources\Holiday\HolidayCollection;
use App\Services\Holiday\HolidayService;
use Exception;
use Illuminate\Http\JsonResponse;
use stdClass;
class HolidayApiController extends Controller
{
    private HolidayService $holidayService;

    public function __construct(HolidayService $holidayService)
    {
        $this->holidayService = $holidayService;
    }

    public function getAllActiveHoliday(): JsonResponse
    {
        try {
            $holidays = $this->holidayService->getAllActiveHolidays();

            $weekends = AppHelper::getWeekendList();
            $weekendItems = collect($weekends)->map(function ($date) {
                $item = new stdClass();
                $item->id = 0;
                $item->event = 'Off Day';
                $item->event_date = $date;
                $item->note = 'Office off day - '.date('l',strtotime($date));
                $item->is_public_holiday = 0;

                return $item;
            });

            $combinedNonWorkingDays = $holidays->concat($weekendItems);

            $uniqueByDate = $combinedNonWorkingDays
                ->keyBy(function ($item) {
                    return $item->event_date;
                })
                ->values()
                ->sortBy('event_date');
            $getAllHolidays = new HolidayCollection($uniqueByDate);
            return AppHelper::sendSuccessResponse(__('index.data_found'), $getAllHolidays);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), 400);
        }
    }

    public function getAllWeekends(): JsonResponse
    {
        try {
            $holidays = $this->holidayService->getAllActiveHolidays();
            $weekends = AppHelper::getWeekendList();
            $holidayDates = $holidays->pluck('event_date')->toArray();

            $allHolidays = array_merge($weekends, $holidayDates);

            return AppHelper::sendSuccessResponse(__('index.data_found'), $allHolidays);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), 400);
        }
    }



}

