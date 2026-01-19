<?php

namespace App\Repositories;

use App\Enum\EventStatusEnum;
use App\Models\Event;
use App\Traits\ImageService;
use Illuminate\Support\Carbon;

class EventRepository
{
    use ImageService;

    public function getAll($filterParameters,$select=['*'],$with=[])
    {
        return Event::select($select)
            ->with($with)
            ->when(isset($filterParameters['branch_id']), function($query) use ($filterParameters){
                $query->where('branch_id', $filterParameters['branch_id']);
            })
            ->when(isset($filterParameters['department_id']), function ($query) use ($filterParameters) {
                $query->whereHas('eventDepartment',function($subQuery) use ($filterParameters){
                    $subQuery->whereIn('department_id', $filterParameters['department_id']);
                });
            })
            ->when(isset($filterParameters['employee_id']), function ($query) use ($filterParameters) {
                $query->whereHas('eventUser',function($subQuery) use ($filterParameters){
                    $subQuery->whereIn('user_id', $filterParameters['employee_id']);
                });
            })
            ->when(isset($filterParameters['start_date']), function($query) use ($filterParameters){
                $query->whereDate('start_date',date('Y-m-d',strtotime($filterParameters['start_date'])));
            })
            ->when(isset($filterParameters['end_date']), function($query) use ($filterParameters){
                $query->whereDate('end_date',date('Y-m-d',strtotime($filterParameters['end_date'])));
            })
            ->paginate( getRecordPerPage());
    }

    public function getApiEvents($perPage, $select=['*'], $isUpcomingEvent=1)
    {
        $events = Event::select($select)
            ->whereHas('eventUser', function ($query) {
                $query->where('user_id', getAuthUserCode());
            });

        if($isUpcomingEvent == 0){
            $events = $events->where(function ($query) {
                $query->where('start_date', '<', Carbon::today());
            });
        }else{
            $events = $events->where(function ($query) {
                $query->where('start_date', '>=', Carbon::today())
                    ->orWhereBetween('start_date',[Carbon::today(),'end_date']);
            });
        }
        return $events->paginate($perPage);
    }

    public function getMonthEvents($filterParameter, $select = ['*'])
    {
        return Event::select($select)
            ->whereHas('eventUser', function ($query) {
                $query->where('user_id', getAuthUserCode());
            })
            ->where(function ($q) use ($filterParameter) {

                $q->where('start_date', '<=', $filterParameter['end_date'])
                    ->where(function ($sub) use ($filterParameter) {
                        $sub->whereNull('end_date')
                            ->orWhere('end_date', '>=', $filterParameter['start_date']);
                    });

            })
            ->get();
    }

    public function getActiveBackendEvents($perPage, $select=['*'])
    {
       return Event::select($select)
            ->where(function ($query) {
                $query->where('start_date', '>=', Carbon::today())
                    ->orWhereRaw('? BETWEEN start_date AND end_date', [Carbon::today()]);
            })
            ->orderBy('start_date')
            ->paginate($perPage);

    }
    public function getPastBackendEvents($perPage, $select=['*'])
    {
        return Event::select($select)
            ->where(function ($query) {
                $query->where('start_date', '<', Carbon::today());
            })
            ->orderBy('start_date')
            ->paginate($perPage);
    }

    public function getRecentEvent($select=['*'])
    {
        return Event::select($select)
            ->whereHas('eventUser', function ($query) {
                $query->where('user_id', getAuthUserCode());
            })
            ->where(function ($query) {
                $query->where('start_date', '>=', Carbon::today())
                    ->orWhereRaw('? BETWEEN start_date AND end_date', [Carbon::today()]);
            })
            ->orderBy('start_date')
            ->first();
    }

    public function find($id,$select=['*'],$with=[])
    {
        return Event::select($select)->with($with)->where('id',$id)->first();
    }

    public function store($validatedData)
    {
        if(isset($validatedData['attachment'])){
            $validatedData['attachment'] = $this->storeImage($validatedData['attachment'], Event::UPLOAD_PATH);
        }
        return Event::create($validatedData)->fresh();
    }

    public function update($eventDetail,$validatedData)
    {
        if (isset($validatedData['attachment'])) {
            $this->removeImage(Event::UPLOAD_PATH, $eventDetail['attachment']);
            $validatedData['attachment'] = $this->storeImage($validatedData['attachment'], Event::UPLOAD_PATH);
        }
        $eventDetail->update($validatedData);
        return $eventDetail;
    }

    public function delete($eventDetail)
    {
        if($eventDetail['attachment']){
            $this->removeImage(Event::UPLOAD_PATH, $eventDetail['attachment']);
        }
        $eventDetail->eventDepartment()->delete();
        $eventDetail->eventUser()->delete();
        return $eventDetail->delete();
    }

    public function deleteAttachment($eventDetail)
    {
        if (isset($eventDetail['attachment'])) {
            $this->removeImage(Event::UPLOAD_PATH, $eventDetail['attachment']);
        }
        return $eventDetail->update(['image' => null ]);
    }

    public function saveDepartment(Event $eventDetail,$departmentArray)
    {
        return $eventDetail->eventDepartment()->createMany($departmentArray);
    }

    public function updateDepartment(Event $eventDetail,$departmentArray)
    {
        $eventDetail->eventDepartment()->delete();
        return $eventDetail->eventDepartment()->createMany($departmentArray);
    }

    public function saveUser(Event $eventDetail,$userArray)
    {
        return $eventDetail->eventUser()->createMany($userArray);
    }

    public function updateUser(Event $eventDetail,$userArray)
    {
        $eventDetail->eventUser()->delete();
        return $eventDetail->eventUser()->createMany($userArray);
    }

    public function updateAllStatus()
    {

        $now = Carbon::today();

        Event::where(function ($query) use ($now) {
            $query->where('start_date', '<', $now) // Case 1: Start date is less than today
            ->orWhere(function ($subQuery) use ($now) { // Case 2: End date is less than today and not null
                $subQuery->whereNotNull('end_date')
                    ->where('end_date', '<', $now)
                    ->where('end_time', '<', $now);;
            });
        })
            ->update(['status' => EventStatusEnum::completed->value]);


        Event::where(function ($query) use ($now) {
            $query->where('start_date', '=', $now) // Case 1: Start date is today
            ->orWhere(function ($subQuery) use ($now) { // Case 2: Start date is past, end_date is not null and >= today
                $subQuery->where('start_date', '<', $now)
                    ->whereNotNull('end_date')
                    ->where('end_date', '>=', $now)
                    ->where('end_time', '>=', $now);
            });
        })
            ->update(['status' => EventStatusEnum::ongoing->value]);

        Event::where('start_date', '>', $now)
            ->update(['status' => EventStatusEnum::pending->value]);
    }

}
