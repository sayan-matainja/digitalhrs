<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePayslipAdditionalDetail extends Model
{
    use HasFactory;

    const RECORDS_PER_PAGE = 10;

    protected $table = 'employee_payslip_additional_details';

    public $timestamps = false;

    protected $fillable = [
        'employee_payslip_id',
        'salary_component_id',
        'amount'
    ];


    public function payslip():BelongsTo
    {
        return $this->belongsTo(EmployeePayslip::class,'employee_payslip_id','id');
    }

    public function salaryComponent(): BelongsTo
    {
        return $this->belongsTo(SalaryComponent::class, 'salary_component_id', 'id');
    }
}
