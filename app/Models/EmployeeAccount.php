<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class EmployeeAccount extends Model
{
    use HasFactory;

    const BANK_ACCOUNT_TYPE = ['saving', 'current', 'salary'];

    const SALARY_CYCLE = ['monthly','weekly'];

    public $timestamps = false;

    protected $table = 'employee_accounts';

    protected $fillable = [
        'user_id',
        'bank_name',
        'bank_account_no',
        'bank_account_type',
        'salary',
        'salary_cycle',
        'salary_group_id',
        'allow_generate_payroll',
        'account_holder',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function salaryGroup()
    {
        return $this->belongsTo(SalaryGroup::class,'salary_group_id','id');
    }

}
