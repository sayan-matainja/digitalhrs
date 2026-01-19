<?php

namespace App\Repositories;

use App\Helpers\AppHelper;
use App\Models\Department;
use App\Models\Support;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupportRepository
{
    public function getAllQueryDetail($filterParameters,$select=['*'],$with=[])
    {

       return Support::with($with)->select($select)
           ->when(isset($filterParameters['is_seen']), function ($query) use ($filterParameters) {
               $query->where('is_seen', $filterParameters['is_seen']);
           })
           ->when(isset($filterParameters['branch_id']), function ($query) use ($filterParameters) {
               $query->where('branch_id', $filterParameters['branch_id']);
           })
           ->when(isset($filterParameters['department_id']), function ($query) use ($filterParameters) {
               $query->where('department_id', $filterParameters['department_id']);
           })
           ->when(isset($filterParameters['status']), function ($query) use ($filterParameters) {
               $query->where('status', $filterParameters['status']);
           })
           ->when(isset($filterParameters['query_from']), function($query) use ($filterParameters){
               $query->whereDate('created_at', '>=', AppHelper::dateInYmdFormatNepToEng($filterParameters['query_from']));
           })
           ->when(isset($filterParameters['query_to']), function($query) use ($filterParameters){
               $query->whereDate('created_at', '<=', AppHelper::dateInYmdFormatNepToEng($filterParameters['query_to']));
           })

           ->latest()
           ->paginate( getRecordPerPage());

    }

    public function findDetailById($id,$select=['*'])
    {
        return Support::select($select)
            ->where('id',$id)
            ->first();
    }

    public function store($validatedData)
    {
        return Support::create($validatedData)->fresh();
    }

    public function toggleIsSeenStatus($supportDetail)
    {
        return $supportDetail->update([
            'is_seen' => !$supportDetail->is_seen
        ]);
    }

    public function changeQueryStatus($supportDetail,$changedStatus)
    {
        return $supportDetail->update([
            'status' => $changedStatus
        ]);
    }

    public function changeStatusToSeen($supportDetail)
    {
        return $supportDetail->update([
            'is_seen' => 1
        ]);
    }

    public function delete($supportDetail)
    {
        return $supportDetail->delete();
    }

    public function getAuthUserSupportQueryListPaginated($filterParameters)
    {

        return DB::table('supports')
            ->select([
                'supports.title as title',
                'supports.description as description',
                'supports.status as status',
                'supports.created_at as query_date',
                'supports.updated_at as updated_date',
                'departments.dept_name as requested_department',
                'created.name as requested_by',
                'updated.name as updated_by'
            ])
            ->join('departments', 'supports.department_id',  'departments.id')
            ->join('users as created', 'created.id', 'supports.created_by')
            ->leftjoin('users as updated', 'updated.id', 'supports.updated_by')
            ->where('created.id', $filterParameters['user_id'])
            ->where('departments.is_active', Department::IS_ACTIVE)
            ->orderByDesc('query_date')
            ->paginate($filterParameters['per_page']);

    }

}
