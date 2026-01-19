<?php

namespace App\Repositories;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class HolidayRepository
{
    public function getAllHolidays($filterParameters, $select = ['*'], $with = []): LengthAwarePaginator
    {
        $holidayLists = Holiday::with($with)->select($select)
            ->when(isset($filterParameters['event']), function ($query) use ($filterParameters) {
                $query->where('event', 'like', '%' . $filterParameters['event'] . '%');
            });
        if (isset($filterParameters['start_date'])) {
            $holidayLists
                ->whereBetween('event_date', [$filterParameters['start_date'], $filterParameters['end_date']]);
        } else {
            $holidayLists
                ->when(isset($filterParameters['event_year']), function ($query) use ($filterParameters) {
                    $query->whereYear('event_date', $filterParameters['event_year']);
                })
                ->when(isset($filterParameters['month']), function ($query) use ($filterParameters) {
                    $query->whereMonth('event_date', $filterParameters['month']);
                });
        }
        return $holidayLists
            ->orderBy('event_date', 'ASC')
            ->paginate( getRecordPerPage());
    }

    public function store($validatedData)
    {
        return Holiday::create($validatedData)->fresh();
    }

    public function findHolidayDetailById($id, $select = ['*'])
    {
        return Holiday::select($select)->where('id', $id)->first();
    }

    public function delete($holidayDetails)
    {
        return $holidayDetails->delete();
    }

    public function update($holidayDetails, $validatedData)
    {
        return $holidayDetails->update($validatedData);
    }

    public function toggleStatus($holidayDetails)
    {
        return $holidayDetails->update([
            'is_active' => !$holidayDetails->is_active,
        ]);
    }

    public function getAllActiveHolidays($date,$select=['*'])
    {
        $holidayLists = Holiday::select($select)
            ->where('is_active', 1);
            if (isset($date['start_date'])) {
                $holidayLists->whereBetween('event_date', [$date['start_date'], $date['end_date']]);
            } else {
                $holidayLists->whereYear('event_date',$date['year'])
                ->orWhereYear('event_date',Carbon::now()->addYears(1));
            }
        return $holidayLists
            ->orderBy('event_date', 'ASC')
            ->get();
    }

    public function getAllActiveHolidayList($date)
    {
        $holidayLists = Holiday::select(['event_date'])
            ->where('is_active', 1);
        if (isset($date['start_date'])) {
            $holidayLists->whereBetween('event_date', [$date['start_date'], $date['end_date']]);
        }
        return $holidayLists
            ->orderBy('event_date', 'ASC')
            ->pluck('event_date')
            ->toArray();
    }



    public function getAllActiveHolidaysBetweenGivenDates($nowDate,$toDate)
    {
        return Holiday::where('is_active', 1)
            ->whereBetween('event_date', [$nowDate, $toDate])
            ->pluck('event_date')
            ->toArray();
    }

    public function getRecentActiveHoliday()
    {

        $nowDate = Carbon::now()->format('Y-m-d');
        return  Holiday::where('is_active', 1)
            ->whereDate('event_date', '>=', $nowDate)
            ->orderBy('event_date')
            ->first();

    }

    public function getHolidayByDate($date, $select=['*'])
    {

        return  Holiday::select($select)->where('is_active', 1)
            ->whereDate('event_date',  $date)
            ->first();

    }
    public function getHolidayByDates($dates, $select=['*'])
    {

        return  Holiday::select($select)->where('is_active', 1)
            ->whereBetween('event_date', [ $dates['start_date'], $dates['end_date']])
            ->get();

    }

}
