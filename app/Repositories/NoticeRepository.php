<?php

namespace App\Repositories;

use App\Models\Notice;
use Illuminate\Support\Carbon;

class NoticeRepository{


    public function getAllCompanyNotices($filterParameters,$select=['*'],$with=[])
    {
        return Notice::select($select)->with($with)
            ->when(isset($filterParameters['employee_id']), function ($query) use ($filterParameters) {
                $query->whereHas('noticeReceiversDetail',function($subQuery) use ($filterParameters){
                    $subQuery->whereIn('notice_receiver_id', $filterParameters['employee_id']);
                });
            })
            ->when(isset($filterParameters['department_id']), function ($query) use ($filterParameters) {
                $query->whereHas('noticeDepartments',function($subQuery) use ($filterParameters){
                    $subQuery->whereIn('department_id', $filterParameters['department_id']);
                });
            })
            ->when(isset($filterParameters['branch_id']), function($query) use ($filterParameters){
                $query->where('branch_id',$filterParameters['branch_id']);
            })
            ->when(isset($filterParameters['publish_date_from']), function($query) use ($filterParameters){
                $query->whereDate('notice_publish_date','>=',$filterParameters['publish_date_from']);
            })
            ->when(isset($filterParameters['publish_date_to']), function($query) use ($filterParameters){
                $query->whereDate('notice_publish_date','<=',$filterParameters['publish_date_to']);
            })
            ->orderBy('notice_publish_date','Desc')
            ->paginate( getRecordPerPage());
    }

    public function getAllEmployeeNotices($perPage,$select=['*'])
    {
        return Notice::select($select)
            ->whereHas('noticeReceiversDetail',function($query){
                $query->where('notice_receiver_id',getAuthUserCode());
            })
            ->where('notice_publish_date','>=',Carbon::now()->subMonth(12))
            ->orderBy('notice_publish_date','Desc')
            ->paginate($perPage);
    }

    public function findNoticeDetailById($id,$select=['*'],$with=[])
    {
        return Notice::select($select)->with($with)->where('id',$id)->first();
    }

    public function store($validatedData)
    {
        return Notice::create($validatedData)->fresh();
    }

    public function update($noticeDetail,$validatedData)
    {
         $noticeDetail->update($validatedData);
         return $noticeDetail;
    }

    public function delete($noticeDetail)
    {
        $noticeDetail->noticeDepartments()->delete();
        $noticeDetail->noticeReceiversDetail()->delete();
        return $noticeDetail->delete();
    }

    public function toggleStatus($noticeDetail)
    {
        return $noticeDetail->update([
            'is_active' => !$noticeDetail->is_active,
        ]);
    }

    public function createManyNoticeReceiver(Notice $noticeDetail,$validatedData)
    {
        return $noticeDetail->noticeReceiversDetail()->createMany($validatedData);
    }

    public function deleteNoticeReceiversDetail($noticeDetail)
    {
        $noticeDetail->noticeReceiversDetail()->delete();
        return true;
    }

    public function createManyNoticeDepartment(Notice $noticeDetail,$validatedData)
    {
        return $noticeDetail->noticeDepartments()->createMany($validatedData);
    }

    public function deleteNoticeDepartment($noticeDetail)
    {
        $noticeDetail->noticeDepartments()->delete();
        return true;
    }

}
