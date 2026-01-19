<?php

namespace App\Repositories;

use App\Models\Tada;

class TadaRepository
{
    public function getAllTadaPaginated($filterParameters, $select, $with)
    {

        return Tada::query()->select($select)->with($with)
            ->when(isset($filterParameters['status']), function ($query) use ($filterParameters) {
                $query->where('status', $filterParameters['status']);
            })
            ->when(isset($filterParameters['branch_id']), function ($query) use ($filterParameters) {
                $query->where('branch_id', $filterParameters['branch_id']);
            })
            ->when(isset($filterParameters['department_id']), function ($query) use ($filterParameters) {
                $query->where('department_id', $filterParameters['department_id']);
            })
            ->when(isset($filterParameters['employee_id']), function ($query) use ($filterParameters) {
                $query->where('employee_id', $filterParameters['employee_id']);
            })
            ->latest()
            ->paginate(getRecordPerPage());
    }

    public function getAllActiveTadaDetail($select, $with)
    {
        return Tada::select($select)->with($with)
            ->where('is_active', 1)
            ->get();
    }

    public function findTadaDetailById($id, $select, $with)
    {
        return Tada::select($select)->with($with)
            ->where('id', $id)
            ->first();
    }

    public function findEmployeeTadaDetailByTadaId($id, $select, $with)
    {
        return Tada::select($select)->with($with)
            ->where('employee_id', getAuthUserCode())
            ->where('id', $id)
            ->first();
    }

    public function getEmployeeTadaDetailLists($employeeId, $select, $with)
    {
        return Tada::select($select)->with($with)
            ->where('employee_id', $employeeId)
            ->where('is_active', 1)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getEmployeeUnsettledTadaLists($employeeId,$firstDay,$days)
    {
        $targetDate = date('Y-m-d', strtotime("{$firstDay} -{$days} days"));
        return Tada::where('employee_id',$employeeId)
            ->where('is_settled',0)
            ->where('approved_date', '<', $targetDate)
            ->where('status','=','accepted')
            ->get(['id','total_expense']);
    }

    public function store($validatedData)
    {
        return Tada::create($validatedData)->fresh();
    }

    public function update($tadaDetail, $validatedData)
    {
        return $tadaDetail->update($validatedData);
    }

    public function delete($tadaDetail)
    {
        return $tadaDetail->delete();
    }

    public function toggleStatus($detail)
    {
        return $detail->update([
            'is_settled' => !$detail->is_settled
        ]);
    }

    public function createManyAttachment(Tada $tadaDetail, $attachments)
    {
        return $tadaDetail->attachments()->createMany($attachments);
    }

    public function deleteTadaAttachments(Tada $tadaDetail)
    {
        return $tadaDetail->attachments()->delete();
    }

    public function changeTadaStatus($tadaDetail, $validatedData)
    {
        return $tadaDetail->update([
            'approved_date' => ($validatedData['status'] == 'accepted') ? date('Y-m-d') : null,
            'status' => $validatedData['status'],
            'remark' => $validatedData['remark'],
            'verified_by' => getAuthUserCode()
        ]);
    }

    public function updateIsSettledStatus($tadaDetail)
    {
        return $tadaDetail->update([
            'is_settled' => true
        ]);
    }

    public function settleTada($updateData, $employeePaySlipDetail)
    {
        $tadaIds = $employeePaySlipDetail->tada_ids;
        return Tada::where('employee_id', $employeePaySlipDetail->employee_id)
            ->when(!empty($tadaIds), function ($query) use ($tadaIds) {
                $query->whereIn('id', $tadaIds);
            })
            ->where('status', 'accepted')
            ->where('is_settled', 0)
            ->update($updateData);
    }

}
