<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePayslipDetail extends Model
{
    use HasFactory;

    const PAYSLIP_STATUS = ['generated', 'review', 'paid'];

    const RECORDS_PER_PAGE = 10;

    const UPLOAD_PATH = 'uploads/payslip/';

    protected $table = 'employee_payslip_details';

    public $timestamps = false;

    protected $fillable = [
        'employee_payslip_id',
        'salary_component_id',
        'amount'
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }

    public function payslip():BelongsTo
    {
        return $this->belongsTo(EmployeePayslip::class,'employee_payslip_id','id');
    }

    public function salaryComponent(): BelongsTo
    {
        return $this->belongsTo(SalaryComponent::class, 'salary_component_id', 'id');
    }
}
