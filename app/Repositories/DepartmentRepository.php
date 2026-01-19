<?php

namespace App\Repositories;

use App\Helpers\AppHelper;
use App\Models\Branch;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DepartmentRepository
{

    /**
     * @param array $with
     * @param array $select
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllPaginatedDepartments($filterParameters, array $with=[], array $select=['*'])
    {
        return Department::select($select)
            ->with($with)
            ->withCount('employees')
            ->when(isset($filterParameters['branch']), function ($query) use ($filterParameters) {
                $query->whereHas('branch',function($subQuery) use ($filterParameters){
                    $subQuery->where('id', $filterParameters['branch']);
                });
            })
            ->when(isset($filterParameters['name']), function ($query) use ($filterParameters) {
                $query->where('dept_name', 'like', '%' . $filterParameters['name'] . '%');
            })->latest()
            ->paginate($filterParameters['per_page']);
    }

    /**
     * @param array $with
     * @param array $select
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAllActiveDepartments(array $with=[], array $select=['*'])
    {
        return  Department::with($with)
            ->select($select)
            ->where('is_active',1)->get();
    }

    public function getAllActiveDepartmentsByBranchId($branchId,$with=[], $select=['*'])
    {
        return Department::with($with)
            ->select($select)
            ->where('is_active',1)
            ->where('branch_id',$branchId)
            ->get();
    }


    /**
     * @param $id
     * @param $select
     * @return mixed
     */
    public function findDepartmentById($id, $select=['*'],$with=[])
    {
        return Department::select($select)->where('id',$id)->first();
    }

    /**
     * @param $validatedData
     * @return mixed
     * @throws \Exception
     */
    public function store($validatedData)
    {
        $validatedData['slug'] = Str::slug($validatedData['dept_name']);
        $validatedData['company_id'] = AppHelper::getAuthUserCompanyId();
        return Department::create($validatedData)->fresh();
    }

    /**
     * @param $departmentDetail
     * @return mixed
     */
    public function delete($departmentDetail)
    {
        return $departmentDetail->delete();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function toggleStatus($id)
    {
        $departmentDetail = $this->findDepartmentById($id);
        return $departmentDetail->update([
            'is_active' => !$departmentDetail->is_active,
        ]);
    }

    /**
     * @param $departmentDetail
     * @param $validatedData
     * @return mixed
     */
    public function update($departmentDetail, $validatedData)
    {
       return $departmentDetail->update($validatedData);
    }

    public function pluckAllDepartments()
    {
        return  Department::pluck('dept_name','id')->toArray();
    }

    public function getDepartmentListUsingAuthUserBranchId()
    {
        return DB::table('departments')
            ->join('branches', 'departments.branch_id', '=', 'branches.id')
            ->join('users', 'branches.id', '=', 'users.branch_id')
            ->where('users.id', getAuthUserCode())
            ->where('departments.is_active', Department::IS_ACTIVE)
            ->get(['departments.id','departments.dept_name']);

    }

    public function checkDepartmentHead($userId, $departmentId=0)
    {
        $department =  Department::where('dept_head_id', $userId);

        if($departmentId != 0){
            $department =$department->where('id','!=',$departmentId);
        }

        return  $department->exists();

    }

    /**
     * Check if any of the given departments (visible in current user's branch)
     * has one of the given users as department head.
     */
     public function checkSpecificDepartmentHead($departmentIds, $userIds)
    {
        return Department::whereIn('dept_head_id', $userIds)->whereIn('id',$departmentIds)->exists();

    }




}
