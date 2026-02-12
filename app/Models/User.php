<?php

namespace App\Models;

use App\Helpers\AttendanceHelper;
use App\Notifications\ResetPasswordNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    const AVATAR_UPLOAD_PATH = 'uploads/user/avatar/';
    const RECORDS_PER_PAGE = 20;
    const GENDER = ['male', 'female', 'others'];
    const STATUS = ['pending', 'verified', 'rejected', 'suspended'];
    const EMPLOYMENT_TYPE = ['contract', 'permanent', 'temporary'];
    const USER_TYPE = ['field', 'nonField'];
    const DEVICE_TYPE = ['android', 'ios', 'web'];
    const ANDROID = 'android';
    const IOS = 'ios';
    const WEB = 'web';
    const ONLINE = 1;
    const OFFLINE = 0;
    const FIELD = 0;
    const OFFICE = 1;
    const DEMO_USERS_USERNAME = [];

    const MARITAL_STATUS = [
        'single',
        'married'
    ];



    const LOGOUT_STATUS = [
        'pending' => 1,
        'approve' => 0
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'surname',           // ADD THIS
        'first_name',        // ADD THIS
        'middle_name',       // ADD THIS
        'email',
        'username',
        'password',
        'address',
        'dob',
        'gender',
        'marital_status',
        'nin',
        'phone',
        'status',
        'is_active',
        'avatar',
        'leave_allocated',
        'employment_type',
        'user_type',
        'joining_date',
        'workspace_type',
        'remarks',
        'uuid',
        'fcm_token',
        'device_type',
        'logout_status',
        'company_id',
        'online_status',
        'branch_id',
        'department_id',
        'post_id',
        'grade_level',       // ADD THIS
        'tax_id',            // ADD THIS
        'sbu_code',          // ADD THIS
        'rsa_no',            // ADD THIS
        'hmo_id',            // ADD THIS
        'role_id',
        'supervisor_id',
        'office_time_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
        'created_at',
        'updated_at',
        'employee_code',
        'allow_holiday_check_in'
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        static::updating(function ($model) {
            $model->updated_by = Auth::user()->id ?? null;
        });

        static::deleting(function ($model) {
            $model->deleted_by = auth()->user()->id ?? null;
            $model->save();
        });

        if (Auth::check()  && isset(Auth::user()->branch_id)) {
            $branchId = Auth::user()->branch_id;

            static::addGlobalScope('branch', function (Builder $builder) use($branchId){
                $builder->whereHas('branch', function ($query) use ($branchId) {
                    $query->where('id', $branchId);
                });
            });
        }

    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id', 'id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'id')->select('name', 'id', 'slug');
    }

    public function officeTime(): BelongsTo
    {
        return $this->belongsTo(OfficeTime::class, 'office_time_id', 'id')->select('id', 'opening_time', 'closing_time', 'shift');
    }

    public function employeeAttendance(): HasMany
    {
        return $this->hasMany(Attendance::class, 'user_id', 'id');
    }

    public function employeeTodayAttendance(): HasMany
    {
        return $this->hasMany(Attendance::class, 'user_id', 'id')
            ->where('attendance_date', Carbon::now()->format('Y-m-d'))
            ->orderBy('attendances.created_at','desc');
    }

    public function employeeWeeklyAttendance(): HasMany
    {
        $currentDate = Carbon::now();
        $weekStartDate = AttendanceHelper::getStartOfWeekDate($currentDate);
        $weekEndDate = AttendanceHelper::getEndOfWeekDate($currentDate);
        return $this->hasMany(Attendance::class, 'user_id', 'id')
            ->where('attendance_status', 1)
            ->whereBetween('attendance_date', [$weekStartDate, $weekEndDate])
            ->orderBy('attendance_date', 'ASC');
    }

    public function accountDetail(): HasOne
    {
        return $this->hasOne(EmployeeAccount::class, 'user_id', 'id');
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function scopeNotAdmin($query)
    {
        return $query->whereHas('role', function ($query) {
            $query->where('slug', '!=', 'admin');
        });
    }
    public function awards(): HasMany
    {
        return $this->hasMany(Award::class, 'employee_id', 'id');
    }

    public function attendanceLog()
    {
        return $this->hasOne(AttendanceLog::class, 'employee_id','id');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id', 'id');
    }
    public function employeeSalary()
    {
        return $this->hasOne(EmployeeSalary::class, 'employee_id', 'id');
    }

    public function employeeLeaveTypes()
    {
        return $this->hasMany(EmployeeLeaveType::class, 'employee_id');
    }

    public function employeeLeaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequestMaster::class, 'requested_by', 'id');
    }

}
