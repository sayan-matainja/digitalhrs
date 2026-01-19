<?php

namespace App\Services\Holiday;

use App\Helpers\AppHelper;
use App\Models\Company;
use App\Repositories\HolidayRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class HolidayService
{
    private HolidayRepository $holidayRepo;

    public function __construct(HolidayRepository $holidayRepo)
    {
        $this->holidayRepo = $holidayRepo;
    }

    public function getAllHolidayLists($filterParameters, $select = ['*'], $with = [])
    {
        try {
            if (AppHelper::ifDateInBsEnabled()) {
                $nepaliDate = AppHelper::getCurrentNepaliYearMonth();
                $filterParameters['event_year'] = $filterParameters['event_year'] ?? $nepaliDate['year'];
                $dateInAD = AppHelper::findAdDatesFromNepaliMonthAndYear($filterParameters['event_year'], $filterParameters['month']);
                $filterParameters['start_date'] = $dateInAD['start_date'];
                $filterParameters['end_date'] = $dateInAD['end_date'];
            }
            return $this->holidayRepo->getAllHolidays($filterParameters, $select, $with);
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    public function getAllActiveHolidays()
    {
        $date = AppHelper::yearDetailToFilterData();
        if (isset($date['end_date'])) {
            $date['end_date'] = AppHelper::getBsNxtYearEndDateInAd();
        }
        return $this->holidayRepo->getAllActiveHolidays($date);
    }

    public function getActiveHolidayList($dates)
    {

        return $this->holidayRepo->getAllActiveHolidayList($dates);
    }

    public function findHolidayDetailById($id)
    {
        try {
            $holidayDetail = $this->holidayRepo->findHolidayDetailById($id);
            if (!$holidayDetail) {
                throw new Exception(__('message.holiday_not_found'), 404);
            }
            return $holidayDetail;
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    public function store($validatedData)
    {

            $validatedData['company_id'] = AppHelper::getAuthUserCompanyId();

        return $this->holidayRepo->store($validatedData);

    }

    /**
     * @throws Exception
     */
    public function update($validatedData, $id)
    {

            $validatedData['company_id'] = AppHelper::getAuthUserCompanyId();
            $holidayDetail = $this->findHolidayDetailById($id);

        return $this->holidayRepo->update($holidayDetail, $validatedData);

    }

    /**
     * @throws Exception
     */
    public function toggleHolidayStatus($id)
    {


            $holidayDetail = $this->findHolidayDetailById($id);
        return $this->holidayRepo->toggleStatus($holidayDetail);

    }

    /**
     * @throws Exception
     */
    public function delete($id)
    {

            $holidayDetail = $this->findHolidayDetailById($id);

        return $this->holidayRepo->delete($holidayDetail);

    }

    public function getAllActiveHolidaysFromNowToGivenNumberOfDays($numberOfDays)
    {

        $nowDate = Carbon::now()->format('Y-m-d');
        $toDate = Carbon::now()->addDay($numberOfDays)->format('Y-m-d');
        return $this->holidayRepo->getAllActiveHolidaysBetweenGivenDates($nowDate,$toDate);

    }

    public function getCurrentActiveHoliday()
    {

        $holiday = $this->holidayRepo->getRecentActiveHoliday();

        $weekendDate = AppHelper::getRecentWeekend();

        if ($holiday && $holiday->event_date) {
            $holidayDate = $holiday->event_date;

            if ($holidayDate < $weekendDate) {
                $recentHolidayData = $holiday;
            } elseif ($weekendDate < $holidayDate) {
                // Weekend comes first
                $recentHolidayData = (object) [
                    'id' => 0,
                    'event' => 'Off day',
                    'event_date' => $weekendDate,
                    'note' => 'Office off day - '.date('l',strtotime($weekendDate)),
                    'is_public_holiday' => 0,
                ];
            } else {
                $recentHolidayData = $holiday;
            }
        } else {
            $recentHolidayData = (object) [
                'id' => 0,
                'event' => 'Off day',
                'event_date' => $weekendDate,
                'note' => 'Office off day - '.date('l',strtotime($weekendDate)),
                'is_public_holiday' => 0,
            ];
        }

        return $recentHolidayData;
    }

    public function getHolidayByDate($date, $select=['*'])
    {

        $recentHolidayData = null;
        $holiday = $this->holidayRepo->getHolidayByDate($date, $select);


        $companyWeekend = Company::pluck('weekend')->first();
        $weekendValue = str_replace(['[', ']', '"'], '', $companyWeekend);
        $desiredWeekday = intval($weekendValue);



        $currentWeekday = date('w', strtotime($date));

        if(isset($holiday)){
            $recentHolidayData = $holiday;
        }else{
            if ($currentWeekday == $desiredWeekday) {

                $recentHolidayData = (object) [
                    'id' => 0,
                    'event' => 'Off day',
                    'event_date' => $date,
                    'note' => 'Office off day - '.date('l',strtotime($date)),
                    'is_public_holiday' => 0,
                ];


            }
        }


        return $recentHolidayData;

    }

    public function getHolidayByDates($dates, $select=['*'])
    {
        return $this->holidayRepo->getHolidayByDates($dates, $select);

    }

}
