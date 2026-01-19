<?php

namespace App\Repositories;

use App\Helpers\AppHelper;
use App\Models\Branch;

class BranchRepository
{

    /**
     * @param array $select
     * @return mixed
     */
    public function getAllCompanyBranches($filterParameters,array $select=['*']): mixed
    {
        return Branch::select($select)
            ->withCount('employees')
            ->when(isset($filterParameters['name']), function ($query) use ($filterParameters) {
                $query->where('name', 'like', '%' . $filterParameters['name'] . '%');
            })
            ->latest()
            ->paginate($filterParameters['per_page']);
    }

    /**
     * @param $validatedData
     * @return mixed
     */
    public function store($validatedData):mixed
    {
        return Branch::create($validatedData)->fresh();
    }

    public function getLoggedInUserCompanyBranches($companyId,$select=['*'])
    {
       return  Branch::select($select)->where('company_id',$companyId)->get();
    }

    /**
     * @throws \Exception
     */
    public function getBranchesWithDepartments()
    {
        return Branch::select('id', 'name')
            ->with('departments:id,dept_name,branch_id')
            ->get();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function toggleStatus($id):mixed
    {
        $branchDetail = $this->findBranchDetailById($id);
        return $branchDetail->update([
            'is_active' => !$branchDetail->is_active,
        ]);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findBranchDetailById($id,$with=[]):mixed
    {
        return Branch::with($with)->where('id',$id)->first();
    }

    public function update($branchDetail, $validatedData)
    {
        return $branchDetail->update($validatedData);
    }

    public function delete(Branch $branch)
    {
        return $branch->delete();


    }

    public function checkBranchHead($userId, $branchId=0)
    {
        $branch =  Branch::where('branch_head_id', $userId);

        if($branchId != 0){
            $branch =$branch->where('id','!=',$branchId);
        }

        return  $branch->exists();

    }

}
