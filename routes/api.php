<?php

use App\Http\Controllers\Api\AdvanceSalaryApiController;
use App\Http\Controllers\Api\AssetAssignmentApiController;
use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\AwardApiController;
use App\Http\Controllers\Api\ComplaintApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\EmployeePayrollApiController;
use App\Http\Controllers\Api\EventApiController;
use App\Http\Controllers\Api\HolidayApiController;
use App\Http\Controllers\Api\LeaveApiController;
use App\Http\Controllers\Api\LeaveTypeApiController;
use App\Http\Controllers\Api\LoanApiController;
use App\Http\Controllers\Api\NfcApiController;
use App\Http\Controllers\Api\NoticeApiController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\ProjectApiController;
use App\Http\Controllers\Api\ProjectManagementDashboardApiController;
use App\Http\Controllers\Api\PushNotificationController;
use App\Http\Controllers\Api\ResignationApiController;
use App\Http\Controllers\Api\StaticPageContentApiController;
use App\Http\Controllers\Api\SupportApiController;
use App\Http\Controllers\Api\TadaApiController;
use App\Http\Controllers\Api\TaskApiController;
use App\Http\Controllers\Api\TaskChecklistApiController;
use App\Http\Controllers\Api\TaskCommentApiController;
use App\Http\Controllers\Api\TeamMeetingApiController;
use App\Http\Controllers\Api\TrainingApiController;
use App\Http\Controllers\Api\UserProfileApiController;
use App\Http\Controllers\Api\WarningApiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthApiController;

/**   user login **/
Route::post('login', [AuthApiController::class,'login']);

Route::group([
    'middleware' => ['auth:api','permission']
], function () {

    /**   user logout **/
    Route::get('logout', [AuthApiController::class, 'logout']);

    /** Users Routes **/
    Route::get('users/profile', [UserProfileApiController::class, 'userProfileDetail']);
    Route::post('users/change-password', [UserProfileApiController::class, 'changePassword']);
    Route::post('users/update-profile', [UserProfileApiController::class, 'updateUserProfile']);
    Route::get('users/profile-detail/{userId}', [UserProfileApiController::class, 'findEmployeeDetailById']);
    Route::get('users/company/team-sheet', [UserProfileApiController::class, 'getTeamSheetOfCompany']);
    Route::post('users/location',[UserProfileApiController::class, 'storeLocation']);
    /** content management Routes **/
    Route::get('static-page-content/{contentType}', [StaticPageContentApiController::class, 'getStaticPageContentByContentType']);
    Route::get('company-rules', [StaticPageContentApiController::class, 'getCompanyRulesDetail']);
    Route::get('static-page-content/{contentType}/{titleSlug}', [StaticPageContentApiController::class, 'getStaticPageContentByContentTypeAndTitleSlug']);

    /** notifications Routes **/
    Route::get('notifications', [NotificationApiController::class, 'getAllRecentPublishedNotification']);

    /** notice Routes **/
    Route::get('notices', [NoticeApiController::class, 'getAllRecentlyReceivedNotice']);

    /** Dashboard Routes **/
    Route::get('dashboard', [DashboardApiController::class, 'userDashboardDetail']);

    /** Attendance Routes **/
    /**
     * @Deprecated Don't use this now
     */
    Route::post('employees/check-in', [AttendanceApiController::class, 'employeeCheckIn']);
    /**
     * @Deprecated Don't use this now
     */
    Route::post('employees/check-out', [AttendanceApiController::class, 'employeeCheckOut']);
    Route::get('employees/attendance-detail', [AttendanceApiController::class, 'getEmployeeAllAttendanceDetailOfTheMonth']);
    Route::post('employees/attendance',[AttendanceApiController::class, 'employeeAttendance']);

    /** Leave Request Routes **/
    Route::get('leave-types', [LeaveTypeApiController::class, 'getAllLeaveTypeWithEmployeeLeaveRecord']);
    Route::post('leave-requests/store', [LeaveApiController::class, 'saveLeaveRequestDetail']);
    Route::get('leave-requests/employee-leave-requests', [LeaveApiController::class, 'getAllLeaveRequestOfEmployee']);
    Route::get('leave-requests/employee-leave-calendar', [LeaveApiController::class, 'getLeaveCountDetailOfEmployeeOfTwoMonth']);
    /**
     * @Deprecated Don't use this now
     */
    Route::get('leave-requests/employee-leave-list', [LeaveApiController::class, 'getAllEmployeeLeaveDetailBySpecificDay']);

    Route::get('employee/office-calendar', [LeaveApiController::class, 'getCalendarDetailBySpecificDay']);
    Route::get('office-calendar', [LeaveApiController::class, 'getCalendarDetail']);
    Route::get('leave-requests/cancel/{leaveRequestId}', [LeaveApiController::class, 'cancelLeaveRequest']);
    /** Time Leave Route */
    Route::post('time-leave-requests/store', [LeaveApiController::class, 'saveTimeLeaveRequest']);
    Route::get('time-leave-requests/cancel/{timeLeaveRequestId}', [LeaveApiController::class, 'cancelTimeLeaveRequest']);


    /** Team Meeting Routes **/
    Route::get('team-meetings', [TeamMeetingApiController::class, 'getAllAssignedTeamMeetingDetail']);
    Route::get('team-meetings/{id}', [TeamMeetingApiController::class, 'findTeamMeetingDetail']);

    /** Holiday route */
    Route::get('holidays', [HolidayApiController::class, 'getAllActiveHoliday']);

    /** Project Management Dashboard route */
    Route::get('project-management-dashboard', [ProjectManagementDashboardApiController::class, 'getUserProjectManagementDashboardDetail']);

    /** Project route */
    Route::get('assigned-projects-list', [ProjectApiController::class, 'getUserAssignedAllProjects']);
    Route::get('assigned-projects-detail/{projectId}', [ProjectApiController::class, 'getProjectDetailById']);

    /** Tasks route */
    Route::get('assigned-task-list', [TaskApiController::class, 'getUserAssignedAllTasks']);
    Route::get('assigned-task-detail/{taskId}', [TaskApiController::class, 'getTaskDetailById']);
    Route::get('assigned-task-detail/change-status/{taskId}', [TaskApiController::class, 'changeTaskStatus']);
    Route::get('assigned-task-comments', [TaskApiController::class, 'getTaskComments']);

    /** Task checklist route */
    Route::get('assigned-task-checklist/toggle-status/{checklistId}', [TaskChecklistApiController::class, 'toggleCheckListIsCompletedStatus']);

    /** Task Comment route */
    Route::post('assigned-task/comments/store', [TaskCommentApiController::class, 'saveComment']);
    Route::get('assigned-task/comment/delete/{commentId}', [TaskCommentApiController::class, 'deleteComment']);
    Route::get('assigned-task/reply/delete/{replyId}', [TaskCommentApiController::class, 'deleteReply']);

    /** Support route */
    Route::post('support/query-store', [SupportApiController::class, 'store']);
    Route::get('support/department-lists', [SupportApiController::class, 'getAuthUserBranchDepartmentLists']);
    Route::get('support/get-user-query-lists', [SupportApiController::class, 'getAllAuthUserSupportQueryList']);

    /** Tada route */
    Route::get('employee/tada-lists', [TadaApiController::class, 'getEmployeesTadaLists']);
    Route::get('employee/tada-details/{tadaId}', [TadaApiController::class, 'getEmployeesTadaDetail']);
    Route::post('employee/tada/store', [TadaApiController::class, 'storeTadaDetail']);
    Route::post('employee/tada/update', [TadaApiController::class, 'updateTadaDetail']);
    Route::get('employee/tada/delete-attachment/{attachmentId}', [TadaApiController::class, 'deleteTadaAttachment']);

    /** Advance Salary */
    Route::get('employee/advance-salaries-lists',[AdvanceSalaryApiController::class,'getEmployeesAdvanceSalaryDetailLists']);
    Route::post('employee/advance-salaries/store',[AdvanceSalaryApiController::class,'store']);
    Route::get('employee/advance-salaries-detail/{id}',[AdvanceSalaryApiController::class,'getEmployeeAdvanceSalaryDetailById']);
    Route::post('employee/advance-salaries-detail/update',[AdvanceSalaryApiController::class,'updateDetail']);

    /** Nfc  */
    Route::post('nfc/store', [NfcApiController::class, 'save']);

    /** Push Notification */
    Route::post('employee/push',[PushNotificationController::class,'sendPushNotification']);

    /** Payslip */
    Route::post('employee/payslip',[EmployeePayrollApiController::class, 'getPayrollList']);
    Route::get('employee/payslip/{id}',[EmployeePayrollApiController::class, 'getEmployeePayslipDetailById']);

    /** Award */
    Route::get('awards',[AwardApiController::class, 'getEmployeeAwards']);

    /** Event Routes **/
    Route::get('events', [EventApiController::class, 'getAllAssignedEvents']);
    Route::get('event/{id}', [EventApiController::class, 'findEventDetail']);

    /** Training Routes **/
    Route::get('training', [TrainingApiController::class, 'getAllTrainings']);
    Route::get('training/{id}', [TrainingApiController::class, 'findTrainingDetail']);

    /** Resignation */
    Route::post('resignation/store', [ResignationApiController::class, 'saveResignationDetail']);
    Route::get('resignation', [ResignationApiController::class, 'resignationDetail']);

    /** Warning */
    Route::post('warning/store/{warning_id}', [WarningApiController::class, 'saveWarningResponse']);
    Route::get('warning', [WarningApiController::class, 'getAllWarnings']);

    /** Complaint */
    Route::post('complaint/store/', [ComplaintApiController::class, 'saveComplaint']);
    Route::post('complaint/response/store/{complaint_id}', [ComplaintApiController::class, 'saveComplaintResponse']);
    Route::get('complaint', [ComplaintApiController::class, 'getAllComplaints']);
    Route::get('department-employees', [ComplaintApiController::class, 'getDepartmentEmployees']);

    /** Asset */
    Route::get('assets', [AssetAssignmentApiController::class, 'index']);
    Route::post('asset-return/{id}', [AssetAssignmentApiController::class, 'store']);

    /** SSF History */
    Route::get('payroll/ssf-history', [EmployeePayrollApiController::class, 'ssfHistory']);


    /** Loan route -list,detail,repayment, create, update, cancel */
    Route::get('employee/loan-lists', [LoanApiController::class, 'getEmployeesLoanLists']);
    Route::post('employee/loan/store', [LoanApiController::class, 'storeLoanDetail']);
    Route::post('employee/loan/update', [LoanApiController::class, 'updateLoanDetail']);
    Route::get('loan-types', [LoanApiController::class, 'getLoanTypes']);
    Route::get('employee/repayment-list/{loanId}', [LoanApiController::class, 'repayment']);

    /** settlement request */
    Route::get('employee/loan/settlement-requests', [LoanApiController::class, 'getLoanSettlementRequests']);
    Route::post('employee/loan/settlement-request/store', [LoanApiController::class, 'storeSettlementRequest']);
    Route::post('employee/loan/settlement-request/update', [LoanApiController::class, 'updateSettlementRequest']);
    Route::get('employee/settlement-detail/{loanId}', [LoanApiController::class, 'settlementDetail']);



    /** weekend list  */
    Route::get('holiday-weekend', [HolidayApiController::class, 'getAllWeekends']);

    /** App setting api */
    Route::get('app-setting', [DashboardApiController::class, 'appSetting']);


    Route::get('leave-requests/logs/{leaveRequestId}', [LeaveApiController::class, 'leaveApprovalHistory']);


});


