<?php

use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\AppSettingController;
use App\Http\Controllers\Web\AssetAssignmentController;
use App\Http\Controllers\Web\AssetController;
use App\Http\Controllers\Web\AssetTypeController;
use App\Http\Controllers\Web\AttachmentController;
use App\Http\Controllers\Web\AttendanceController;
use App\Http\Controllers\Web\AttendanceSettingController;
use App\Http\Controllers\Web\AwardController;
use App\Http\Controllers\Web\AwardTypeController;
use App\Http\Controllers\Web\BioAttendanceController;
use App\Http\Controllers\Web\BonusController;
use App\Http\Controllers\Web\BranchController;
use App\Http\Controllers\Web\ClientController;
use App\Http\Controllers\Web\CompanyController;
use App\Http\Controllers\Web\ComplaintController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DataExportController;
use App\Http\Controllers\Web\DepartmentController;
use App\Http\Controllers\Web\DeviceController;
use App\Http\Controllers\Web\EmiCalculatorController;
use App\Http\Controllers\Web\EmployeeCardController;
use App\Http\Controllers\Web\EmployeeCardCustomFieldController;
use App\Http\Controllers\Web\EmployeeCardTemplateController;
use App\Http\Controllers\Web\EmployeeCardUploadController;
use App\Http\Controllers\Web\EmployeeLogOutRequestController;
use App\Http\Controllers\Web\EmployeeSalaryController;
use App\Http\Controllers\Web\EventController;
use App\Http\Controllers\Web\FeatureController;
use App\Http\Controllers\Web\FiscalYearController;
use App\Http\Controllers\Web\GeneralSettingController;
use App\Http\Controllers\Web\HolidayController;
use App\Http\Controllers\Web\iclockController;
use App\Http\Controllers\Web\LeaveApprovalController;
use App\Http\Controllers\Web\LeaveController;
use App\Http\Controllers\Web\LeaveTypeController;
use App\Http\Controllers\Web\LoanController;
use App\Http\Controllers\Web\LoanRepaymentController;
use App\Http\Controllers\Web\LoanSettlementRequestController;
use App\Http\Controllers\Web\LoanTypeController;
use App\Http\Controllers\Web\NFCController;
use App\Http\Controllers\Web\NoticeController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\OfficeTimeController;
use App\Http\Controllers\Web\OverTimeSettingController;
use App\Http\Controllers\Web\PaymentCurrencyController;
use App\Http\Controllers\Web\PaymentMethodController;
use App\Http\Controllers\Web\PostController;
use App\Http\Controllers\Web\PrivacyPolicyController;
use App\Http\Controllers\Web\ProjectController;
use App\Http\Controllers\Web\PromotionController;
use App\Http\Controllers\Web\QrCodeController;
use App\Http\Controllers\Web\ResignationController;
use App\Http\Controllers\Web\RoleController;
use App\Http\Controllers\Web\RouterController;
use App\Http\Controllers\Web\AdvanceSalaryController;
use App\Http\Controllers\Web\SalaryComponentController;
use App\Http\Controllers\Web\SalaryGroupController;
use App\Http\Controllers\Web\SalaryHistoryController;
use App\Http\Controllers\Web\SalaryTDSController;
use App\Http\Controllers\Web\SSFController;
use App\Http\Controllers\Web\PFController;
use App\Http\Controllers\Web\StaticPageContentController;
use App\Http\Controllers\Web\SupportController;
use App\Http\Controllers\Web\TadaAttachmentController;
use App\Http\Controllers\Web\TadaController;
use App\Http\Controllers\Web\TaskChecklistController;
use App\Http\Controllers\Web\TaskCommentController;
use App\Http\Controllers\Web\TaskController;
use App\Http\Controllers\Web\TaxReportController;
use App\Http\Controllers\Web\TeamMeetingController;
use App\Http\Controllers\Web\TerminationController;
use App\Http\Controllers\Web\TerminationTypeController;
use App\Http\Controllers\Web\ThemeController;
use App\Http\Controllers\Web\ThemeSettingController;
use App\Http\Controllers\Web\TimeLeaveController;
use App\Http\Controllers\Web\TrainerController;
use App\Http\Controllers\Web\TrainingController;
use App\Http\Controllers\Web\TrainingTypeController;
use App\Http\Controllers\Web\TransferController;
use App\Http\Controllers\Web\UnderTimeSettingController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\WarningController;
use App\Models\EmployeeCardSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes([
    'register' => false,
    'login' => false,
    'logout' => false
]);

Route::get('/', function () {
    return redirect()->route('admin.login');
});
Route::get('/add-user', [iclockController::class, 'addUsers']);
Route::get('/remove-user/{id}', [iclockController::class, 'removeUser']);

// routes/web.php
Route::any('/iclock/cdata', [BioAttendanceController::class, 'handleDevice']);

/** settings */


/** app privacy policy route */
Route::get('privacy', [PrivacyPolicyController::class, 'index'])->name('privacy-policy');

Route::group([
    'prefix' => 'admin',
    'as' => 'admin.',
    'middleware' => ['web']
    ], function () {
    Route::get('login', [AdminAuthController::class, 'showAdminLoginForm'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('login.process');



    Route::group(['middleware' => ['admin.auth','permission']], function () {

        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('showQR', [DashboardController::class, 'showQR'])->name('showQR');

        Route::group(['middleware' => 'superAdmin'], function () {


            /** User route */
            Route::resource('users', AdminController::class);
            Route::get('users/toggle-status/{id}', [AdminController::class, 'toggleStatus'])->name('users.toggle-status');
            Route::get('users/delete/{id}', [AdminController::class, 'delete'])->name('users.delete');
            Route::post('users/change-password/{userId}', [AdminController::class, 'changePassword'])->name('users.change-password');

            /** company route */
            Route::resource('company', CompanyController::class);

            /** branch route */
            Route::resource('branch', BranchController::class);
            Route::get('branch/toggle-status/{id}', [BranchController::class, 'toggleStatus'])->name('branch.toggle-status');
            Route::get('branch/delete/{id}', [BranchController::class, 'delete'])->name('branch.delete');

            /** app settings */
            Route::get('app-settings/index', [AppSettingController::class, 'index'])->name('app-settings.index');
            Route::get('app-settings/toggle-status/{id}', [AppSettingController::class, 'toggleStatus'])->name('app-settings.toggle-status');

            /** General settings */
            Route::resource('general-settings', GeneralSettingController::class);
            Route::get('general-settings/delete/{id}', [GeneralSettingController::class, 'delete'])->name('general-settings.delete');

            /** app settings */
            Route::get('feature/index', [FeatureController::class, 'index'])->name('feature.index');
            Route::get('feature/toggle-status/{id}', [FeatureController::class, 'toggleStatus'])->name('feature.toggle-status');

            /** roles & permissions route */
            Route::resource('roles', RoleController::class);
            Route::get('roles/toggle-status/{id}', [RoleController::class, 'toggleStatus'])->name('roles.toggle-status');
            Route::get('roles/delete/{id}', [RoleController::class, 'delete'])->name('roles.delete');
            Route::get('roles/permissions/{roleId}', [RoleController::class, 'createPermission'])->name('roles.permission');
            Route::put('roles/assign-permissions/{roleId}', [RoleController::class, 'assignPermissionToRole'])->name('role.assign-permissions');


        });

        /** Employee Location Logs */
        Route::get('employee/location-logs', [UserController::class, 'logs'])->name('employee.log');
        /** Employees route */
        Route::resource('employees', UserController::class);
        Route::get('employees/toggle-status/{id}', [UserController::class, 'toggleStatus'])->name('employees.toggle-status');
        Route::get('employees/toggle-holiday-checkin/{id}', [UserController::class, 'toggleHolidayCheckIn'])->name('employees.toggle-holiday-checkin');
        Route::get('employees/delete/{id}', [UserController::class, 'delete'])->name('employees.delete');
        Route::get('employees/change-workspace/{id}', [UserController::class, 'changeWorkSpace'])->name('employees.change-workspace');
        Route::get('employees/get-company-employee/{branchId}', [UserController::class, 'getAllCompanyEmployeeDetail'])->name('employees.getAllCompanyUsers');
        Route::get('employees/get-branch-employee/{branchId}', [UserController::class, 'getAllBranchEmployees'])->name('employees.getAllBranchEmployees');
        Route::post('employees/change-password/{userId}', [UserController::class, 'changePassword'])->name('employees.change-password');
        Route::get('employees/force-logout/{userId}', [UserController::class, 'forceLogOutEmployee'])->name('employees.force-logout');
        Route::get('employees/get-all-employees/{departmentId}', [UserController::class, 'getAllEmployeeByDepartmentId'])->name('employees.getAllUsersByDepartmentId');
        Route::post('employees/fetch-employees-by-department', [UserController::class, 'fetchEmployeesByDepartment'])->name('employees.fetchByDepartment');
        Route::post('employees/fetch-employees-by-departments', [UserController::class, 'fetchDepartmentEmployees'])->name('employees.getByDepartment');


        /** Department route */
        Route::resource('departments', DepartmentController::class);
        Route::get('departments/toggle-status/{id}', [DepartmentController::class, 'toggleStatus'])->name('departments.toggle-status');
        Route::get('departments/delete/{id}', [DepartmentController::class, 'delete'])->name('departments.delete');
        Route::get('departments/get-All-Departments/{branchId}', [DepartmentController::class, 'getAllDepartmentsByBranchId'])->name('departments.getAllDepartmentsByBranchId');


        /** post route */
        Route::resource('posts', PostController::class);
        Route::get('posts/toggle-status/{id}', [PostController::class, 'toggleStatus'])->name('posts.toggle-status');
        Route::get('posts/delete/{id}', [PostController::class, 'delete'])->name('posts.delete');
        Route::get('posts/get-All-posts/{deptId}', [PostController::class, 'getAllPostsByBranchId'])->name('posts.getAllPostsByBranchId');


        /** office_time route */
        Route::resource('office-times', OfficeTimeController::class);
        Route::get('office-times/toggle-status/{id}', [OfficeTimeController::class, 'toggleStatus'])->name('office-times.toggle-status');
        Route::get('office-times/delete/{id}', [OfficeTimeController::class, 'delete'])->name('office-times.delete');

        /** branch_router route */
        Route::resource('routers', RouterController::class);
        Route::get('routers/toggle-status/{id}', [RouterController::class, 'toggleStatus'])->name('routers.toggle-status');
        Route::get('routers/delete/{id}', [RouterController::class, 'delete'])->name('routers.delete');

        /** holiday route */
        Route::get('holidays/import-csv', [HolidayController::class, 'holidayImport'])->name('holidays.import-csv.show');
        Route::post('holidays/import-csv', [HolidayController::class, 'importHolidays'])->name('holidays.import-csv.store');
        Route::resource('holidays', HolidayController::class);
        Route::get('holidays/toggle-status/{id}', [HolidayController::class, 'toggleStatus'])->name('holidays.toggle-status');
        Route::get('holidays/delete/{id}', [HolidayController::class, 'delete'])->name('holidays.delete');


        /** Leave route */
        Route::resource('leaves', LeaveTypeController::class);
        Route::get('leaves/toggle-status/{id}', [LeaveTypeController::class, 'toggleStatus'])->name('leaves.toggle-status');
        Route::get('leaves/toggle-early-exit/{id}', [LeaveTypeController::class, 'toggleEarlyExit'])->name('leaves.toggle-early-exit');
        Route::get('leaves/delete/{id}', [LeaveTypeController::class, 'delete'])->name('leaves.delete');
        Route::get('leaves/get-leave-types/{earlyExitStatus}', [LeaveTypeController::class, 'getLeaveTypesBasedOnEarlyExitStatus']);
        Route::get('leaves/get-gender-leave-types/{branchId}/{gender}', [LeaveTypeController::class, 'getGenderSpecificLeaveTypes'])->name('leaves.gender-data');
        Route::get('leaves/get-employee-leave-types/{employeeId}', [LeaveTypeController::class, 'getEmployeeLeaveTypes'])->name('leaves.employee-data');

        /** Company Content Management route */
        Route::resource('static-page-contents', StaticPageContentController::class);
        Route::get('static-page-contents/toggle-status/{id}', [StaticPageContentController::class, 'toggleStatus'])->name('static-page-contents.toggle-status');
        Route::get('static-page-contents/delete/{id}', [StaticPageContentController::class, 'delete'])->name('static-page-contents.delete');

        /** Notification route */
        Route::get('notifications/get-nav-notification', [NotificationController::class, 'getNotificationForNavBar'])->name('nav-notifications');
        Route::resource('notifications', NotificationController::class);
        Route::get('notifications/toggle-status/{id}', [NotificationController::class, 'toggleStatus'])->name('notifications.toggle-status');
        Route::get('notifications/delete/{id}', [NotificationController::class, 'delete'])->name('notifications.delete');
        Route::get('notifications/send-notification/{id}', [NotificationController::class, 'sendNotificationToAllCompanyUser'])->name('notifications.send-notification');

        /** Attendance route */
        Route::put('employees/night-attendance/{id}', [AttendanceController::class, 'updateNightAttendance'])->name('night_attendances.update');

        Route::resource('attendances', AttendanceController::class);
        Route::get('employees/attendance/check-in/{companyId}/{userId}', [AttendanceController::class, 'checkInEmployee'])->name('employees.check-in');
        Route::get('employees/attendance/check-out/{companyId}/{userId}', [AttendanceController::class, 'checkOutEmployee'])->name('employees.check-out');
        Route::get('employees/attendance/delete/{id}', [AttendanceController::class, 'delete'])->name('attendance.delete');
        Route::get('employees/attendance/change-status/{id}', [AttendanceController::class, 'changeAttendanceStatus'])->name('attendances.change-status');
        Route::get('employees/attendance/{type}', [AttendanceController::class, 'dashboardAttendance'])->name('dashboard.takeAttendance');

        /** Leave route */
        Route::get('leave-request', [LeaveController::class, 'index'])->name('leave-request.index');
        Route::post('leave-request/store', [LeaveController::class, 'storeLeaveRequest'])->name('employee-leave-request.store');
        Route::get('leave-request/show/{leaveId}', [LeaveController::class, 'show'])->name('leave-request.show');
        Route::put('leave-request/status-update/{leaveRequestId}', [LeaveController::class, 'updateLeaveRequestStatus'])->name('leave-request.update-status');
        Route::get('leave-request/create', [LeaveController::class, 'createLeaveRequest'])->name('leave-request.create');
        Route::get('leave-request/add', [LeaveController::class, 'addLeaveRequest'])->name('leave-request.add');
        Route::post('leave-request/add', [LeaveController::class, 'saveLeaveRequest'])->name('leave-request.save');

        /** Time Leave Route */
        Route::get('time-leave-request', [TimeLeaveController::class, 'index'])->name('time-leave-request.index');
        Route::put('time-leave-request/status-update/{leaveRequestId}', [TimeLeaveController::class, 'updateLeaveRequestStatus'])->name('time-leave-request.update-status');
        Route::get('time-leave-request/show/{leaveId}', [TimeLeaveController::class, 'show'])->name('time-leave-request.show');
        Route::get('time-leave-request/create', [TimeLeaveController::class, 'createLeaveRequest'])->name('time-leave-request.create');
        Route::post('time-leave-request/store', [TimeLeaveController::class, 'storeLeaveRequest'])->name('time-leave-request.store');


        /**logout request Routes */
        Route::get('employee/logout-requests', [EmployeeLogOutRequestController::class, 'getAllCompanyEmployeeLogOutRequest'])->name('logout-requests.index');
        Route::get('employee/logout-requests/toggle-status/{employeeId}', [EmployeeLogOutRequestController::class, 'acceptLogoutRequest'])->name('logout-requests.accept');

        /** Notice route */
        Route::resource('notices', NoticeController::class);
        Route::get('notices/toggle-status/{id}', [NoticeController::class, 'toggleStatus'])->name('notices.toggle-status');
        Route::get('notices/delete/{id}', [NoticeController::class, 'delete'])->name('notices.delete');
        Route::get('notices/send-notice/{id}', [NoticeController::class, 'sendNotice'])->name('notices.send-notice');

        /** Team Meeting route */
        Route::resource('team-meetings', TeamMeetingController::class);
        Route::get('team-meetings/delete/{id}', [TeamMeetingController::class, 'delete'])->name('team-meetings.delete');
        Route::get('team-meetings/remove-image/{id}', [TeamMeetingController::class, 'removeImage'])->name('team-meetings.remove-image');

        /** Clients route */
        Route::post('clients/ajax/store', [ClientController::class, 'ajaxClientStore'])->name('clients.ajax-store');
        Route::resource('clients', ClientController::class);
        Route::get('clients/delete/{id}', [ClientController::class, 'delete'])->name('clients.delete');
        Route::get('clients/toggle-status/{id}', [ClientController::class, 'toggleIsActiveStatus'])->name('clients.toggle-status');

        /** Project Management route */
        Route::resource('projects', ProjectController::class);
        Route::get('projects/delete/{id}', [ProjectController::class, 'delete'])->name('projects.delete');
        Route::get('projects/toggle-status/{id}', [ProjectController::class, 'toggleStatus'])->name('projects.toggle-status');
        Route::get('projects/get-assigned-members/{projectId}', [ProjectController::class, 'getProjectAssignedMembersByProjectId'])->name('projects.get-assigned-members');
        Route::get('projects/get-employees-to-add/{addEmployeeType}/{projectId}', [ProjectController::class, 'getEmployeesToAddTpProject'])->name('projects.add-employee');
        Route::post('projects/update-leaders', [ProjectController::class, 'updateLeaderToProject'])->name('projects.update-leader-data');
        Route::post('projects/update-members', [ProjectController::class, 'updateMemberToProject'])->name('projects.update-member-data');

        /** Project & Task Attachment route */
        Route::get('projects/attachment/create/{projectId}', [AttachmentController::class, 'createProjectAttachment'])->name('project-attachment.create');
        Route::post('projects/attachment/store', [AttachmentController::class, 'storeProjectAttachment'])->name('project-attachment.store');
        Route::get('tasks/attachment/create/{taskId}', [AttachmentController::class, 'createTaskAttachment'])->name('task-attachment.create');
        Route::post('tasks/attachment/store', [AttachmentController::class, 'storeTaskAttachment'])->name('task-attachment.store');
        Route::get('attachment/delete/{id}', [AttachmentController::class, 'deleteAttachmentById'])->name('attachment.delete');


        /** Task Management route */
        Route::resource('tasks', TaskController::class);
        Route::get('projects/task/create/{projectId}', [TaskController::class, 'createTaskFromProjectPage'])->name('project-task.create');
        Route::get('tasks/delete/{id}', [TaskController::class, 'delete'])->name('tasks.delete');
        Route::get('tasks/toggle-status/{id}', [TaskController::class, 'toggleStatus'])->name('tasks.toggle-status');
        Route::get('tasks/get-all-tasks/{projectId}', [TaskController::class, 'getAllTaskByProjectId'])->name('employees.getAllTaskByProjectId');

        /** Task Checklist route */
        Route::post('task-checklists/save', [TaskChecklistController::class, 'store'])->name('task-checklists.store');
        Route::get('task-checklists/edit/{id}', [TaskChecklistController::class, 'edit'])->name('task-checklists.edit');
        Route::put('task-checklists/update/{id}', [TaskChecklistController::class, 'update'])->name('task-checklists.update');
        Route::get('task-checklists/delete/{id}', [TaskChecklistController::class, 'delete'])->name('task-checklists.delete');
        Route::get('task-checklists/toggle-status/{id}', [TaskChecklistController::class, 'toggleIsCompletedStatus'])->name('task-checklists.toggle-status');

        /** Task Comments  route */
        Route::post('task-comment/store', [TaskCommentController::class, 'saveCommentDetail'])->name('task-comment.store');
        Route::get('task-comment/delete/{commentId}', [TaskCommentController::class, 'deleteComment'])->name('comment.delete');
        Route::get('task-comment/reply/delete/{replyId}', [TaskCommentController::class, 'deleteReply'])->name('reply.delete');


        /** Support route */
        Route::get('supports/get-all-query',[SupportController::class,'getAllQueriesPaginated'])->name('supports.index');
        Route::get('supports/change-seen-status/{queryId}', [SupportController::class, 'changeIsSeenStatus'])->name('supports.changeSeenStatus');
        Route::put('supports/update-status/{id}', [SupportController::class, 'changeQueryStatus'])->name('supports.updateStatus');
        Route::get('supports/delete/{id}', [SupportController::class, 'delete'])->name('supports.delete');

        /** Tada route */
        Route::put('tadas/update-status/{id}', [TadaController::class, 'changeTadaStatus'])->name('tadas.update-status');
        Route::resource('tadas', TadaController::class);
        Route::get('tadas/delete/{id}', [TadaController::class, 'delete'])->name('tadas.delete');
        Route::get('tadas/toggle-active-status/{id}', [TadaController::class, 'toggleTadaIsActive'])->name('tadas.toggle-status');

        /** Tada Attachment route */
        Route::get('tadas/attachment/create/{tadaId}', [TadaAttachmentController::class, 'create'])->name('tadas.attachment.create');
        Route::post('tadas/attachment/store', [TadaAttachmentController::class, 'store'])->name('tadas.attachment.store');
        Route::get('tadas/attachment/delete/{id}', [TadaAttachmentController::class, 'delete'])->name('tadas.attachment-delete');

        /** Export data route */
        Route::get('leave-types-export', [DataExportController::class, 'exportLeaveType'])->name('leave-type-export');
        Route::get('leave-requests-export', [DataExportController::class, 'exportEmployeeLeaveRequestLists'])->name('leave-request-export');
        Route::get('employee-detail-export', [DataExportController::class, 'exportEmployeeDetail'])->name('employee-lists-export');
        Route::get('attendance-detail-export', [DataExportController::class, 'exportAttendanceDetail'])->name('attendance-lists-export');

        /** Asset Management route */
        Route::resource('asset-types', AssetTypeController::class,[
            'except' => ['destroy']
        ]);
        Route::get('asset-types/delete/{id}', [AssetTypeController::class, 'delete'])->name('asset-types.delete');
        Route::get('asset-types/toggle-status/{id}', [AssetTypeController::class, 'toggleIsActiveStatus'])->name('asset-types.toggle-status');

        Route::resource('assets', AssetController::class,[
            'except' => ['destroy']
        ]);
        Route::get('assets/delete/{id}', [AssetController::class, 'delete'])->name('assets.delete');
        Route::get('assets/toggle-status/{id}', [AssetController::class, 'changeAvailabilityStatus'])->name('assets.change-Availability-status');

        /** Salary Component route */
        Route::resource('salary-components', SalaryComponentController::class,[
            'except' => ['destroy','show']
        ]);
        Route::get('salary-components/delete/{id}', [SalaryComponentController::class, 'delete'])->name('salary-components.delete');
        Route::get('salary-components/change-status/{id}', [SalaryComponentController::class, 'toggleSalaryComponentStatus'])->name('salary-components.toggle-status');

        // added
        Route::get('salary-components/toggle-taxable/{id}', [SalaryComponentController::class, 'toggleTaxableStatus'])->name('salary-components.toggle-taxable');

        /** Payment Methods route */
        Route::resource('payment-methods', PaymentMethodController::class,[
            'except' => ['destroy','show','edit']
        ]);
        Route::get('payment-methods/delete/{id}', [PaymentMethodController::class, 'deletePaymentMethod'])->name('payment-methods.delete');
        Route::get('payment-methods/change-status/{id}', [PaymentMethodController::class, 'togglePaymentMethodStatus'])->name('payment-methods.toggle-status');

        /** Payment Currency route */
        Route::get('payment-currency', [PaymentCurrencyController::class, 'index'])->name('payment-currency.index');
        Route::post('payment-currency', [PaymentCurrencyController::class, 'updateOrSetPaymentCurrency'])->name('payment-currency.save');

        /** Salary TDS route */
        Route::resource('salary-tds', SalaryTDSController::class,[
            'except' => ['destroy','show']
        ]);
        Route::get('salary-tds/delete/{id}', [SalaryTDSController::class, 'deleteSalaryTDS'])->name('salary-tds.delete');
        Route::get('salary-tds/change-status/{id}', [SalaryTDSController::class, 'toggleSalaryTDSStatus'])->name('salary-tds.toggle-status');

        /** Salary Group route */
        Route::resource('salary-groups', SalaryGroupController::class,[
            'except' => ['destroy','show']
        ]);
        Route::get('salary-groups/delete/{id}', [SalaryGroupController::class, 'deleteSalaryGroup'])->name('salary-groups.delete');
        Route::get('salary-groups/change-status/{id}', [SalaryGroupController::class, 'toggleSalaryGroupStatus'])->name('salary-groups.toggle-status');

        /** Employee Salary route */
        Route::resource('employee-salaries', EmployeeSalaryController::class,[
            'except' =>['destroy','create','edit','update','store','show']
        ]);
        Route::get('employee-salaries/update-cycle/{employeeId}/{cycle}', [EmployeeSalaryController::class, 'changeSalaryCycle'])->name('employee-salaries.update-salary-cycle');
        Route::post('employee-salaries/payroll-create', [EmployeeSalaryController::class, 'payrollCreate'])->name('employee-salaries.payroll-create');
        Route::get('employee-salaries/payroll', [EmployeeSalaryController::class, 'payroll'])->name('employee-salary.payroll');
        Route::get('employee-salaries/payroll/{payslipId}', [EmployeeSalaryController::class, 'viewPayroll'])->name('employee-salary.payroll-detail');
        Route::get('employee-salaries/payroll/{payslipId}/print', [EmployeeSalaryController::class, 'printPayslip'])->name('employee-salary.payroll-print');
        Route::get('employee-salaries/payroll/{payslipId}/edit', [EmployeeSalaryController::class, 'editPayroll'])->name('employee-salary.payroll-edit');
        Route::put('employee-salaries/payroll/{payslipId}/update', [EmployeeSalaryController::class, 'updatePayroll'])->name('employee-salary.payroll-update');
        Route::delete('employee-salaries/payroll/{payslipId}/delete', [EmployeeSalaryController::class, 'deletePayroll'])->name('employee-salary.payroll-delete');

        Route::get('employee-salaries/salary/create/{employeeId}', [EmployeeSalaryController::class, 'createSalary'])->name('employee-salaries.add');
        Route::post('employee-salaries/salary/{employeeId}', [EmployeeSalaryController::class, 'saveSalary'])->name('employee-salaries.store-salary');
        Route::get('employee-salaries/salary/edit/{employeeId}', [EmployeeSalaryController::class, 'editSalary'])->name('employee-salaries.edit-salary');
        Route::put('employee-salaries/salary/{employeeId}', [EmployeeSalaryController::class, 'updateSalary'])->name('employee-salaries.update-salary');
        Route::get('employee-salaries/salary/{employeeId}', [EmployeeSalaryController::class, 'deleteSalary'])->name('employee-salaries.delete-salary');

        Route::put('employee-salaries/{payslipId}/make-payment', [EmployeeSalaryController::class, 'makePayment'])->name('employee-salaries.make_payment');

        /** get weeks list */
        Route::get('employee-salaries/getWeeks/{year}', [EmployeeSalaryController::class, 'getWeeks'])->name('employee-salaries.get-weeks');


        /** Employee Salary History route */
        Route::get('employee-salaries/salary-update/{accountId}', [SalaryHistoryController::class, 'create'])->name('employee-salaries.increase-salary');
        Route::post('employee-salaries/salary-history/store', [SalaryHistoryController::class, 'store'])->name('employee-salaries.updated-salary-store');
        Route::get('employee-salaries/salary-increment-history/{employeeId}', [SalaryHistoryController::class, 'getEmployeeAllSalaryHistory'])->name('employee-salaries.salary-revise-history.show');

        Route::get('advance-salaries/setting/', [AdvanceSalaryController::class, 'setting'])->name('advance-salaries.setting');
        Route::post('advance-salaries/setting/{id}', [AdvanceSalaryController::class, 'updateSetting'])->name('advance-salaries.setting.store');

        /** Advance Salary route */
        Route::resource('advance-salaries', AdvanceSalaryController::class,[
            'except' => ['destroy','store','edit']
        ]);
        Route::get('advance-salaries/delete/{id}', [AdvanceSalaryController::class, 'delete'])->name('advance-salaries.delete');

        /** Tax report */

        Route::get('employee-salaries/tax-report', [TaxReportController::class, 'index'])->name('payroll.tax-report.index');
        Route::get('employee-salaries/tax-report/{id}/detail', [TaxReportController::class, 'taxReport'])->name('payroll.tax-report.detail');
        Route::get('employee-salaries/tax-report/{id}/print', [TaxReportController::class, 'printTaxReport'])->name('payroll.tax-report.print');
        Route::get('employee-salaries/tax-report/{id}/edit', [TaxReportController::class, 'editTaxReport'])->name('payroll.tax-report.edit');
        Route::get('employee-salaries/tax-report/{id}/delete', [TaxReportController::class, 'deleteTaxReport'])->name('payroll.tax-report.delete');
        Route::put('employee-salaries/tax-report/{id}', [TaxReportController::class, 'updateTaxReport'])->name('payroll.tax-report.update');


        /** Payroll OverTime Setting route */
        Route::get('overtime/get-employee-by-payroll/{type}', [EmployeeSalaryController::class, 'getEmployeeSalaryData'])->name('overtime.get-employee');
        Route::resource('overtime', OverTimeSettingController::class,[
        'except' => ['destroy']
        ]);
        Route::get('overtime/delete/{id}', [OverTimeSettingController::class, 'delete'])->name('overtime.delete');
        Route::get('overtime/change-status/{id}', [OverTimeSettingController::class, 'toggleOTStatus'])->name('overtime.toggle-status');


        /** Payroll UnderTime Setting route */
        Route::resource('under-time', UnderTimeSettingController::class,[
            'except' => ['destroy']
        ]);
        Route::get('under-time/delete/{id}', [UnderTimeSettingController::class, 'delete'])->name('under-time.delete');
        Route::get('under-time/change-status/{id}', [UnderTimeSettingController::class, 'toggleUTStatus'])->name('under-time.toggle-status');


        Route::resource('qr', QrCodeController::class,[
            'except' => ['destroy','show']
        ]);
        Route::get('qr/delete/{id}', [QrCodeController::class, 'delete'])->name('qr.destroy');
        Route::get('qr/print/{id}', [QrCodeController::class, 'print'])->name('qr.print');

        Route::get('/nfc', [NFCController::class, 'index'])->name('nfc.index');
        Route::get('/nfc/delete/{id}', [NFCController::class, 'delete'])->name('nfc.destroy');


        /** delete employee leave type */
        Route::get('employees/leave_type/delete/{id}', [UserController::class, 'deleteEmployeeLeaveType'])->name('employee_leave_type.delete');


        /** Award Management route */
        Route::resource('award-types', AwardTypeController::class,[
            'except' => ['destroy']
        ]);
        Route::get('award-types/delete/{id}', [AwardTypeController::class, 'delete'])->name('award-types.delete');
        Route::get('award-types/toggle-status/{id}', [AwardTypeController::class, 'toggleStatus'])->name('award-types.toggle-status');

        Route::resource('awards', AwardController::class,[
            'except' => ['destroy']
        ]);
        Route::get('awards/delete/{id}', [AwardController::class, 'delete'])->name('awards.delete');

        /** language route */
        Route::get('language/change', [LanguageController::class, 'change'])->name('language.change');

        /** Bonus route */
        Route::resource('bonus', BonusController::class,[
            'except' => ['destroy','show']
        ]);
        Route::get('bonus/delete/{id}', [BonusController::class, 'delete'])->name('bonus.delete');
        Route::get('bonus/change-status/{id}', [BonusController::class, 'toggleBonusStatus'])->name('bonus.toggle-status');

        /** fiscal year route */
        Route::resource('fiscal_year', FiscalYearController::class,[
            'except' => ['destroy','show']
        ]);
        Route::get('fiscal_year/delete/{id}', [FiscalYearController::class, 'delete'])->name('fiscal_year.delete');
        Route::get('fiscal_year/change-current-status/{id}', [FiscalYearController::class, 'toggleCurrentStatus'])->name('fiscal_year.toggle-current-status');

        /** SSF route */
        Route::resource('ssf', SSFController::class,[
            'except' => ['destroy','show']
        ]);

        /** Attendance Logs */
        Route::get('attendance/logs', [AttendanceController::class, 'logs'])->name('attendance.log');

        /** calculate tax */
        Route::get('calculate_tax',[EmployeeSalaryController::class, 'calculateTax'])->name('get-tax');

        /** theme change */
        Route::get('change-theme', [ThemeController::class, 'changeTheme'])->name('change-theme');

        /** leave approval route */
        Route::get('leave-approval/get-employees-by-role', [LeaveApprovalController::class, 'getEmployeesByRole'])->name('leave-approval.fetchByRole');
        Route::resource('leave-approval', LeaveApprovalController::class,[
            'except' => ['destroy']
        ]);
        Route::get('leave-approval/delete/{id}', [LeaveApprovalController::class, 'delete'])->name('leave-approval.delete');
        Route::get('leave-approval/change-status/{id}', [LeaveApprovalController::class, 'toggleStatus'])->name('leave-approval.toggle-status');

        /** get leave request approvals */
        Route::get('leave-request/get-approvers/{id}', [LeaveController::class, 'getLeaveRequestApproval'])->name('leave-request.approval-details');

        /** Attendance Export */
        Route::get('attendance/export', [AttendanceController::class, 'export'])->name('attendance.export');

        /** Event route */
        Route::resource('event', EventController::class);
        Route::get('event/delete/{id}', [EventController::class, 'delete'])->name('event.delete');
        Route::get('event/remove-image/{id}', [EventController::class, 'removeImage'])->name('event.remove-image');


        /** Training Management */
        Route::resource('training-types', TrainingTypeController::class,
            ['except' => ['destroy','create','edit']]);
        Route::get('training-types/delete/{id}', [TrainingTypeController::class, 'delete'])->name('training-types.delete');
        Route::get('training-types/toggle-status/{id}', [TrainingTypeController::class, 'toggleStatus'])->name('training-types.toggle-status');

        /** Trainer */
        Route::resource('trainers', TrainerController::class,[
            'except' => ['destroy']
        ]);
        Route::get('trainers/delete/{id}', [TrainerController::class, 'delete'])->name('trainers.delete');
        Route::get('trainers/toggle-status/{id}', [TrainerController::class, 'toggleStatus'])->name('trainers.toggle-status');
        Route::get('trainers/get-all-trainers/{type}', [TrainerController::class, 'getAllTrainersByType'])->name('trainers.getAllTrainersByType');

        /** Trainer */
        Route::resource('training', TrainingController::class,[
            'except' => ['destroy']
        ]);
        Route::get('training/delete/{id}', [TrainingController::class, 'delete'])->name('training.delete');
        Route::get('training/toggle-status/{id}', [TrainingController::class, 'toggleStatus'])->name('training.toggle-status');



        /** termination Management */
        /** termination Type */
        Route::resource('termination-types', TerminationTypeController::class,
            ['except' => ['destroy','create','edit']]);
        Route::get('termination-types/delete/{id}', [TerminationTypeController::class, 'delete'])->name('termination-types.delete');
        Route::get('termination-types/toggle-status/{id}', [TerminationTypeController::class, 'toggleStatus'])->name('termination-types.toggle-status');


        /** termination */
        Route::resource('termination', TerminationController::class,[
            'except' => ['destroy']
        ]);
        Route::get('termination/delete/{id}', [TerminationController::class, 'delete'])->name('termination.delete');
        Route::put('termination/status-update/{terminationId}', [TerminationController::class, 'updateTerminationStatus'])->name('termination.update-status');

        /** resignation */
        Route::resource('resignation', ResignationController::class,[
            'except' => ['destroy']
        ]);
        Route::get('resignation/delete/{id}', [ResignationController::class, 'delete'])->name('resignation.delete');
        Route::put('resignation/status-update/{resignationId}', [ResignationController::class, 'updateResignationStatus'])->name('resignation.update-status');


//        /** user export */
//        Route::get('/export-employees', [UserController::class, 'export'])->name('employees.export');

        /** Warning */
        Route::resource('warning', WarningController::class,[
            'except' => ['destroy']
        ]);
        Route::get('warning/delete/{id}', [WarningController::class, 'delete'])->name('warning.delete');

        /** Complaint */
        Route::resource('complaint', ComplaintController::class,[
            'except' => ['destroy']
        ]);
        Route::get('complaint/delete/{id}', [ComplaintController::class, 'delete'])->name('complaint.delete');

        /** add member to task */
        Route::get('task/get-employees-to-add/{taskId}', [TaskController::class, 'getEmployeesToAddToTask'])->name('tasks.add-employee');
        Route::post('task/update-members', [TaskController::class, 'updateTaskMember'])->name('tasks.update-member-data');


        /** Promotion */
        Route::resource('promotion', PromotionController::class,[
            'except' => ['destroy']
        ]);
        Route::get('promotion/delete/{id}', [PromotionController::class, 'delete'])->name('promotion.delete');
        Route::get('promotion/get-employees-posts/{departmentId}', [PromotionController::class, 'getEmployeeAndPostByDepartment'])->name('promotion.getUsersAndPostByDepartment');


        Route::resource('transfer', TransferController::class,[
            'except' => ['destroy']
        ]);
        Route::get('transfer/delete/{id}', [TransferController::class, 'delete'])->name('transfer.delete');
        Route::get('transfer/get-user-transfer-department-data/{departmentId}', [TransferController::class, 'getUserTransferDepartmentData']);
        Route::get('transfer/get-user-transfer-branch-data/{branchId}', [TransferController::class, 'getUserTransferBranchData']);
        Route::get('transfer/get-user-data/{employeeId}', [TransferController::class, 'getUserTransferData']);


        /** Theme Color route */
        Route::resource('theme-color-setting', ThemeSettingController::class,[
            'except' => ['destroy','show']
        ]);

        /** get branch wise client and employees in project */
        Route::get('get-branch-project-data/{branchId}', [ProjectController::class, 'getBranchProjectData']);

        /** get branch wise  employees */
        Route::get('get-branch-employee-data/{branchId}', [UserController::class, 'getBranchEmployeeData']);

        /** get branch wise asset type and employees in asset */
        Route::get('get-branch-asset-data/{branchId}', [AssetController::class, 'getBranchAssetData']);

        /** get branch wise leave type and departments in leave approval */
        Route::get('get-branch-leave-data/{branchId}', [LeaveApprovalController::class, 'getBranchLeaveData']);
        /** get branch wise termination type and departments in termination */
        Route::get('get-branch-termination-data/{branchId}', [TerminationController::class, 'getBranchTerminationData']);

        /** get branch wise award type and departments in awards */
        Route::get('get-branch-award-data/{branchId}', [AwardController::class, 'getBranchAwardData']);

        /** get branch wise award type and departments in awards */
        Route::get('get-branch-task-data/{branchId}', [TaskController::class, 'getBranchTaskData']);
        Route::get('get-project-member-data/{projectId}', [TaskController::class, 'getProjectMemberData']);

        /** get branch wise training type and departments in termination */
        Route::get('get-branch-training-data/{branchId}', [TrainingController::class, 'getBranchTrainingData']);


        /** Asset Assignment */
        Route::post('asset-assignment/{assetId}', [AssetAssignmentController::class, 'store'])->name('asset-assignment.store');
        Route::get('asset-assignments/{assetId}', [AssetAssignmentController::class, 'index'])->name('asset-assignment.index');

        /** Asset Return */
        Route::get('asset-return', [AssetAssignmentController::class, 'returnlist'])->name('asset-return.index');
        Route::get('asset/toggle-repair-status/{id}', [AssetAssignmentController::class, 'changeRepairStatus'])->name('asset.toggle-repair-status');
        Route::post('asset-return/{id}', [AssetAssignmentController::class, 'storeReturn'])->name('asset.return');


        /** get employee to assign in overtime setting */
        Route::post('overtime/get-user-department-data', [EmployeeSalaryController::class, 'getEmployeeByDepartmentPayrollType']);

        /** get employee for bonus */
        Route::post('bonus/get-user-department-data', [BonusController::class, 'getUserDepartmentData']);

         /** PF route */
        Route::resource('pf', PFController::class,[
            'except' => ['destroy','show']
        ]);
        Route::resource('biometric-devices', DeviceController::class,[
            'except' => ['edit','show','destroy']
        ]);
        Route::get('biometric-devices/delete/{id}', [DeviceController::class, 'delete'])->name('biometric-devices.delete');

        /** log route */
        Route::get('/log', static function () {
            $log = base_path('storage/logs/laravel.log');
            if (file_exists($log)) {
                $content = file($log);
                $lines = array_slice($content, -500); // show last 100 lines
                $output = implode("", $lines);
                return response($output, 200)->header('Content-Type', 'text/plain');
            }

            return response('Log file not found', 404);
        });

        Route::get('attendance-settings', [AttendanceSettingController::class, 'index'])->name('attendance-settings.index');
        Route::put('attendance-settings', [AttendanceSettingController::class, 'update'])->name('attendance-settings.update');
        Route::get('attendance-settings/toggle-status/{id}', [AttendanceSettingController::class, 'toggleStatus'])->name('attendance-settings.toggle-status');

        /** Loan Setting */
        Route::get('loan/setting', [LoanController::class, 'setting'])->name('loanSetting.index');
        Route::post('loan/setting/{id}', [LoanController::class, 'updateSetting'])->name('loanSetting.update');

        /** Loan Management route */
        Route::resource('loan-types', LoanTypeController::class,[
            'except' => ['destroy']
        ]);
        Route::get('loan-types/delete/{id}', [LoanTypeController::class, 'delete'])->name('loan-types.delete');
        Route::get('loan-types/toggle-status/{id}', [LoanTypeController::class, 'toggleIsActiveStatus'])->name('loan-types.toggle-status');

        Route::put('loan/update-status/{id}', [LoanController::class, 'changeLoanStatus'])->name('loan.update-status');
        Route::get('loan/history/{id}', [LoanController::class, 'showHistory'])->name('loan.history');
        Route::resource('loan', LoanController::class,[
            'except' => ['destroy']
        ]);
        Route::get('loan/delete/{id}', [LoanController::class, 'delete'])->name('loan.delete');

        Route::get('loan/get-branch-loan-data/{branchId}', [LoanController::class, 'getBranchLoanData'])->name('loan.get-branch-loan-data');
        Route::get('loan/get-branch-loan-type/{branchId}', [LoanController::class, 'getBranchLoanType'])->name('loan.get-branch-loan-type');
        Route::get('loan/get-employee-loan/{employeeId}', [LoanController::class, 'getEmployeeLoan'])->name('loan.get-employee-loan');

        Route::get('loan/request', [LoanController::class, 'requestLoan'])->name('loan-request.index');
        Route::post('loan/request', [LoanController::class, 'storeLoan'])->name('loan-request.store');

        Route::get('loan-repayment', [LoanRepaymentController::class, 'repaymentList'])->name('loan-repayment.list');
        Route::get('loan-repayment/{loanId}', [LoanRepaymentController::class, 'repaymentDetail'])->name('loan-repayment.detail');

        /** loan settlement request */
        Route::resource('request-settlement', LoanSettlementRequestController::class,[
            'except' => ['destroy']
        ]);
        Route::put('request-settlement/update-status/{id}', [LoanSettlementRequestController::class, 'changeSettlementRequestStatus'])->name('request-settlement.update-status');
        Route::get('request-settlement/delete/{id}', [LoanSettlementRequestController::class, 'delete'])->name('request-settlement.delete');
        Route::get('request/loan-settlement', [LoanSettlementRequestController::class, 'requestLoanSettlement'])->name('settlementRequest.index');
        Route::post('request/loan-settlement', [LoanSettlementRequestController::class, 'storeLoanSettlement'])->name('settlementRequest.store');

        /** leave balance */
        Route::get('leave/balance', [LeaveController::class, 'leaveBalance'])->name('leaveBalance.index');

        /** leave cancel request approval and reject */
        Route::put('leave-cancel/status-update/{leaveRequestId}', [LeaveController::class, 'cancelRequestUpdate'])->name('leave-cancel.update-status');

        Route::put('time-leave-cancel/status-update/{leaveRequestId}', [TimeLeaveController::class, 'cancelRequestUpdate'])->name('time-leave-cancel.update-status');

        /** Employee Card setting */
        Route::get('id-card/templates', [EmployeeCardController::class, 'index'])->name('card.template-list');
        Route::get('id-card/template/detail/{id}', [EmployeeCardController::class, 'show'])->name('card.show-template');
        Route::get('id-card/template/create', [EmployeeCardController::class, 'create'])->name('card.create-template');
        Route::post('id-card/template', [EmployeeCardController::class, 'save'])->name('card.save-template');
        Route::get('id-card/template/{id}/edit', [EmployeeCardController::class, 'edit'])->name('card.edit-template');
        Route::put('id-card/template/{id}', [EmployeeCardController::class, 'update'])->name('card.update-template');
        Route::get('id-card/template/{id}/delete', [EmployeeCardController::class, 'delete'])->name('card.delete-template');
        Route::get('id-card/template/toggle-status/{id}', [EmployeeCardController::class, 'toggleIsActive'])->name('card.toggle-status');
        Route::get('id-card/template/default/{id}', [EmployeeCardController::class, 'makeDefault'])->name('card.make-default');

    });

});



Route::get('emi-calculator', [EmiCalculatorController::class, 'emiCalculator'])->name('emi-calculator');

Route::get('employee/card/{employeeCode}', [EmployeeCardController::class, 'viewCard'])
    ->name('employee.card.view');
Route::get('employee/card/{employeeCode}/download', [EmployeeCardController::class, 'downloadCard'])->name('employee.card.download');

Route::fallback(function() {
    return view('errors.404');
});





