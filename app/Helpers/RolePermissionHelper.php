<?php

namespace App\Helpers;

class RolePermissionHelper
{
    public static function permissionModuleTypeArray(): array
    {
        return [

            [//1
                "name" => "Web",
                "slug" => "web",
            ],

            [//2
                "name" => "API",
                "slug" => "api",
            ],

        ];
    }

    public static function permissionModuleArray(): array
    {
        return [

            [//1
                "name" => "Company",
                'group_type_id' => 1
            ],
            [//2
                "name" => "Branch",
                'group_type_id' => 1
            ],
            [//3
                "name" => "Department",
                'group_type_id' => 1
            ],
            [//4
                "name" => "Post",
                'group_type_id' => 1
            ],
            [//5
                "name" => "Employee",
                'group_type_id' => 1
            ],
            [//6
                "name" => "Setting",
                'group_type_id' => 1
            ],
            [//7
                "name" => "Attendance",
                'group_type_id' => 1
            ],
            [//8
                "name" => "Leave",
                'group_type_id' => 1
            ],
            [//9
                "name" => "Holiday",
                'group_type_id' => 1
            ],
            [//10
                "name" => "Notice",
                'group_type_id' => 1
            ],
            [//11
                "name" => "Team Meeting",
                'group_type_id' => 1
            ],
            [//12
                "name" => "Content Management",
                'group_type_id' => 1
            ],
            [//13
                "name" => "Shift Management",
                'group_type_id' => 1
            ],

            [//14
                "name" => "Support",
                'group_type_id' => 1
            ],
            [//15
                "name" => "Tada",
                'group_type_id' => 1
            ],
            [//16
                "name" => "Client",
                'group_type_id' => 1
            ],
            [//17
                "name" => "Project Management",
                'group_type_id' => 1
            ],
            [//18
                "name" => "Task Management",
                'group_type_id' => 1
            ],
            [//19
                'name' => 'Employee API',
                'group_type_id' => 2
            ],
            [//20
                'name' => 'Attendance API',
                'group_type_id' => 2
            ],
            [//21
                'name' => 'Leave API',
                'group_type_id' => 2
            ],
            [//22
                'name' => 'Support API',
                'group_type_id' => 2
            ],
            [//23
                'name' => 'Tada API',
                'group_type_id' => 2
            ],
            [//24
                'name' => 'Task Management API',
                'group_type_id' => 2
            ],
            [//25
                "name" => "Dashboard",
                'group_type_id' => 1
            ],

            [//26
                "name" => "Asset Management",
                'group_type_id' => 1
            ],

            [//27
                "name" => "Mobile Notification",
                'group_type_id' => 1
            ],
            [//28
                "name" => "Attendance Method",
                'group_type_id' => 1
            ],
            [//29
                "name" => "Attendance Method API",
                'group_type_id' => 2
            ],
            [//30
                "name" => "Payroll Management",
                'group_type_id' => 1
            ],
            [//31
                "name" => "Payroll Setting",
                'group_type_id' => 1
            ],
            [//32
                "name" => "Advance Salary",
                'group_type_id' => 1
            ],
            [//33
                "name" => "Employee Salary",
                'group_type_id' => 1
            ],
            [//34
                "name" => "Payroll Management API",
                'group_type_id' => 2
            ],
            [//35
                "name" => "Advance Salary API",
                'group_type_id' => 2
            ],


            [//36
                "name" => "Time Leave",
                'group_type_id' => 1
            ],
            [//37
                "name" => "Award Management",
                'group_type_id' => 1
            ],
            [//38
                "name" => "Tax Report",
                'group_type_id' => 1
            ],
            [//39
                "name" => "Event Management",
                'group_type_id' => 1
            ],

            [//40
                "name" => "Training Management",
                'group_type_id' => 1
            ],
            [//41
                "name" => "Leave Approval",
                'group_type_id' => 1
            ],
            [//42
                "name" => "Resignation Management",
                'group_type_id' => 1
            ],
            [//43
                "name" => "Termination Management",
                'group_type_id' => 1
            ],
            [//44
                "name" => "Resignation Api",
                'group_type_id' => 2
            ],
            [//45
                "name" => "Warning",
                'group_type_id' => 1
            ],
            [//46
                "name" => "Warning Api",
                'group_type_id' => 2
            ],

            [//47
                "name" => "Complaint",
                'group_type_id' => 1
            ],
            [//48
                "name" => "Complaint Api",
                'group_type_id' => 2
            ],
            [//49
                "name" => "Promotion",
                'group_type_id' => 1
            ],
            [//50
                "name" => "Transfer",
                'group_type_id' => 1
            ],
            [//51
                "name" => "Loan Management",
                'group_type_id' => 1
            ],
            [//52
                "name" => "Loan Api",
                'group_type_id' => 2
            ],


        ];
    }

    public static function permissionArray(): array
    {
        return [
//            /** Role Permissions */
//            [
//                "name" => "List Role",
//                "permission_key" => "list_role",
//                "permission_groups_id" => 1
//            ],
//            [
//                "name" => "Create Role",
//                "permission_key" => "create_role",
//                "permission_groups_id" => 1
//            ],
//            [
//                "name" => "Edit Role",
//                "permission_key" => "edit_role",
//                "permission_groups_id" => 1
//            ],
//            [
//                "name" => "Delete Role",
//                "permission_key" => "delete_role",
//                "permission_groups_id" => 1
//            ],
//            [
//                "name" => "List Permission",
//                "permission_key" => "list_permission",
//                "permission_groups_id" => 1
//            ],
//            [
//                "name" => "Assign Permission",
//                "permission_key" => "assign_permission",
//                "permission_groups_id" => 1
//            ],

            /** Company Permissions */
            [
                "name" => "View Company",
                "permission_key" => "view_company",
                "permission_groups_id" => 1
            ],
            [
                "name" => "Create Company",
                "permission_key" => "create_company",
                "permission_groups_id" => 1
            ],
            [
                "name" => "Edit Company",
                "permission_key" => "edit_company",
                "permission_groups_id" => 1
            ],

            /** Branch Permissions */
            [
                "name" => "List Branch",
                "permission_key" => "list_branch",
                "permission_groups_id" => 2
            ],
            [
                "name" => "Create Branch",
                "permission_key" => "create_branch",
                "permission_groups_id" => 2
            ],
            [
                "name" => "Edit Branch",
                "permission_key" => "edit_branch",
                "permission_groups_id" => 2
            ],
            [
                "name" => "Delete Branch",
                "permission_key" => "delete_branch",
                "permission_groups_id" => 2
            ],

            /** Department Permissions */
            [
                "name" => "List Department",
                "permission_key" => "list_department",
                "permission_groups_id" => 3
            ],
            [
                "name" => "Create Department",
                "permission_key" => "create_department",
                "permission_groups_id" => 3
            ],
            [
                "name" => "Edit Department",
                "permission_key" => "edit_department",
                "permission_groups_id" => 3
            ],
            [
                "name" => "Delete Department",
                "permission_key" => "delete_department",
                "permission_groups_id" => 3
            ],

            /** Post Permissions */
            [
                "name" => "List Post",
                "permission_key" => "list_post",
                "permission_groups_id" => 4
            ],
            [
                "name" => "Create Post",
                "permission_key" => "create_post",
                "permission_groups_id" => 4
            ],
            [
                "name" => "Edit Post",
                "permission_key" => "edit_post",
                "permission_groups_id" => 4
            ],
            [
                "name" => "Delete Post",
                "permission_key" => "delete_post",
                "permission_groups_id" => 4
            ],

            /** Employee Management Permissions */
            [
                "name" => "List Employee",
                "permission_key" => "list_employee",
                "permission_groups_id" => 5
            ],
            [
                "name" => "Create Employee",
                "permission_key" => "create_employee",
                "permission_groups_id" => 5
            ],
            [
                "name" => "Show Detail Employee",
                "permission_key" => "show_detail_employee",
                "permission_groups_id" => 5
            ],
            [
                "name" => "Edit Employee",
                "permission_key" => "edit_employee",
                "permission_groups_id" => 5
            ],
            [
                "name" => "Delete Employee",
                "permission_key" => "delete_employee",
                "permission_groups_id" => 5
            ],
            [
                "name" => "Change Password",
                "permission_key" => "change_password",
                "permission_groups_id" => 5
            ],
            [
                "name" => "Force Logout Employee",
                "permission_key" => "force_logout",
                "permission_groups_id" => 5
            ],
            [
                "name" => "List Logout Request",
                "permission_key" => "list_logout_request",
                "permission_groups_id" => 5
            ],
            [
                "name" => "Logout Request Accept",
                "permission_key" => "accept_logout_request",
                "permission_groups_id" => 5
            ],

            /** Setting Permissions */
            [
                "name" => "General Setting",
                "permission_key" => "general_setting",
                "permission_groups_id" => 6
            ],

            [
                "name" => "App Setting",
                "permission_key" => "app_setting",
                "permission_groups_id" => 6
            ],
            [
                "name" => "Role and Permission",
                "permission_key" => "role_permission",
                "permission_groups_id" => 6
            ],
            [
                "name" => "Notification",
                "permission_key" => "notification",
                "permission_groups_id" => 6
            ],
            [
                "name" => "Feature Control",
                "permission_key" => "feature_control",
                "permission_groups_id" => 6
            ],
            [
                "name" => "Fiscal Year",
                "permission_key" => "fiscal_year",
                "permission_groups_id" => 6
            ],
            [
                "name" => "Payment Currency",
                "permission_key" => "payment_currency",
                "permission_groups_id" => 6
            ],
            [
                "name" => "App QR",
                "permission_key" => "app_qr",
                "permission_groups_id" => 6
            ],
            [
                "name" => "Theme Color Setting",
                "permission_key" => "theme_setting",
                "permission_groups_id" => 6
            ],

            /** Attendance Permissions */
            [
                "name" => "List Attendance",
                "permission_key" => "list_attendance",
                "permission_groups_id" => 7
            ],
            [
                "name" => "Attendance CSV Export",
                "permission_key" => "attendance_csv_export",
                "permission_groups_id" => 7
            ],
            [
                "name" => "Attendance Create",
                "permission_key" => "attendance_create",
                "permission_groups_id" => 7
            ],
            [
                "name" => "Attendance Update",
                "permission_key" => "attendance_update",
                "permission_groups_id" => 7
            ],
            [
                "name" => "Attendance Show",
                "permission_key" => "attendance_show",
                "permission_groups_id" => 7
            ],
            [
                "name" => "Attendance Delete",
                "permission_key" => "attendance_delete",
                "permission_groups_id" => 7
            ],

            /** Leave Permissions */
            [
                "name" => "List Leave Type",
                "permission_key" => "list_leave_type",
                "permission_groups_id" => 8
            ],
            [
                "name" => "Leave Type Create",
                "permission_key" => "leave_type_create",
                "permission_groups_id" => 8
            ],
            [
                "name" => "Leave Type Edit",
                "permission_key" => "leave_type_edit",
                "permission_groups_id" => 8
            ],
            [
                "name" => "Leave Type Delete",
                "permission_key" => "leave_type_delete",
                "permission_groups_id" => 8
            ],
            [
                "name" => "List Leave Requests",
                "permission_key" => "list_leave_request",
                "permission_groups_id" => 8
            ],
            [
                "name" => "Show Leave Request Detail",
                "permission_key" => "show_leave_request_detail",
                "permission_groups_id" => 8
            ],
            [
                "name" => "Update Leave request",
                "permission_key" => "update_leave_request",
                "permission_groups_id" => 8
            ],

            /** Holiday Permissions */
            [
                "name" => "List Holiday",
                "permission_key" => "list_holiday",
                "permission_groups_id" => 9
            ],
            [
                "name" => "Holiday Create",
                "permission_key" => "create_holiday",
                "permission_groups_id" => 9
            ],
            [
                "name" => "Show Detail",
                "permission_key" => "show_holiday",
                "permission_groups_id" => 9
            ],
            [
                "name" => "Holiday Edit",
                "permission_key" => "edit_holiday",
                "permission_groups_id" => 9
            ],
            [
                "name" => "Holiday Delete",
                "permission_key" => "delete_holiday",
                "permission_groups_id" => 9
            ],
            [
                "name" => "Csv Import Holiday",
                "permission_key" => "import_holiday",
                "permission_groups_id" => 9
            ],

            /** Notice Permissions */
            [
                "name" => "List Notice",
                "permission_key" => "list_notice",
                "permission_groups_id" => 10
            ],
            [
                "name" => "Notice Create",
                "permission_key" => "create_notice",
                "permission_groups_id" => 10
            ],
            [
                "name" => "Show Notice Detail",
                "permission_key" => "show_notice",
                "permission_groups_id" => 10
            ],
            [
                "name" => "Notice Edit",
                "permission_key" => "edit_notice",
                "permission_groups_id" => 10
            ],
            [
                "name" => "Notice Delete",
                "permission_key" => "delete_notice",
                "permission_groups_id" => 10
            ],
            [
                "name" => "Send Notice",
                "permission_key" => "send_notice",
                "permission_groups_id" => 10
            ],

            /** Team Meeting Permissions */
            [
                "name" => "List Team Meeting",
                "permission_key" => "list_team_meeting",
                "permission_groups_id" => 11
            ],
            [
                "name" => "Team Meeting Create",
                "permission_key" => "create_team_meeting",
                "permission_groups_id" => 11
            ],
            [
                "name" => "Show Team Meeting Detail",
                "permission_key" => "show_team_meeting",
                "permission_groups_id" => 11
            ],
            [
                "name" => "Team Meeting Edit",
                "permission_key" => "edit_team_meeting",
                "permission_groups_id" => 11
            ],
            [
                "name" => "Team Meeting Delete",
                "permission_key" => "delete_team_meeting",
                "permission_groups_id" => 11
            ],

            /** Content management Permissions */
            [
                "name" => "List Content",
                "permission_key" => "list_content",
                "permission_groups_id" => 12
            ],
            [
                "name" => "Content Create",
                "permission_key" => "create_content",
                "permission_groups_id" => 12
            ],
            [
                "name" => "Show Content Detail",
                "permission_key" => "show_content",
                "permission_groups_id" => 12
            ],
            [
                "name" => "Content Edit",
                "permission_key" => "edit_content",
                "permission_groups_id" => 12
            ],
            [
                "name" => "Content Delete",
                "permission_key" => "delete_content",
                "permission_groups_id" => 12
            ],

            /** Shift management Permissions */
            [
                "name" => "List Office Time",
                "permission_key" => "list_office_time",
                "permission_groups_id" => 13
            ],
            [
                "name" => "Office Time Create",
                "permission_key" => "create_office_time",
                "permission_groups_id" => 13
            ],
            [
                "name" => "Show Office Time Detail",
                "permission_key" => "show_office_time",
                "permission_groups_id" => 13
            ],
            [
                "name" => "Office Time Edit",
                "permission_key" => "edit_office_time",
                "permission_groups_id" => 13
            ],
            [
                "name" => "Office Time Delete",
                "permission_key" => "delete_office_time",
                "permission_groups_id" => 13
            ],


            /** Support Permissions */
            [
                "name" => "View Query List",
                "permission_key" => "view_query_list",
                "permission_groups_id" => 14
            ],
            [
                "name" => "Show Query Detail",
                "permission_key" => "show_query_detail",
                "permission_groups_id" => 14
            ],
            [
                "name" => "Update Status",
                "permission_key" => "update_query_status",
                "permission_groups_id" => 14
            ],
            [
                "name" => "Delete Query",
                "permission_key" => "delete_query",
                "permission_groups_id" => 14
            ],

            /** Tada Permissions */
            [
                "name" => "View Tada List",
                "permission_key" => "view_tada_list",
                "permission_groups_id" => 15
            ],
            [
                "name" => "Create Tada ",
                "permission_key" => "create_tada",
                "permission_groups_id" => 15
            ],
            [
                "name" => "Show Tada Detail",
                "permission_key" => "show_tada_detail",
                "permission_groups_id" => 15
            ],
            [
                "name" => "Edit Tada",
                "permission_key" => "edit_tada",
                "permission_groups_id" => 15
            ],
            [
                "name" => "Delete Tada",
                "permission_key" => "delete_tada",
                "permission_groups_id" => 15
            ],
            [
                "name" => "Upload Attachment ",
                "permission_key" => "create_attachment",
                "permission_groups_id" => 15
            ],
            [
                "name" => "Delete Attachment ",
                "permission_key" => "delete_attachment",
                "permission_groups_id" => 15
            ],

            /** Client Permissions */
            [
                "name" => "View Client List",
                "permission_key" => "view_client_list",
                "permission_groups_id" => 16
            ],
            [
                "name" => "Create Client ",
                "permission_key" => "create_client",
                "permission_groups_id" => 16
            ],
            [
                "name" => "Show Client Detail",
                "permission_key" => "show_client_detail",
                "permission_groups_id" => 16
            ],
            [
                "name" => "Edit Client",
                "permission_key" => "edit_client",
                "permission_groups_id" => 16
            ],
            [
                "name" => "Delete Client",
                "permission_key" => "delete_client",
                "permission_groups_id" => 16
            ],

            /** Project management Permissions */
            [
                "name" => "View Project List",
                "permission_key" => "view_project_list",
                "permission_groups_id" => 17
            ],
            [
                "name" => "Create Project",
                "permission_key" => "create_project",
                "permission_groups_id" => 17
            ],
            [
                "name" => "Show Project Detail",
                "permission_key" => "show_project_detail",
                "permission_groups_id" => 17
            ],
            [
                "name" => "Edit Project",
                "permission_key" => "edit_project",
                "permission_groups_id" => 17
            ],
            [
                "name" => "Delete Project",
                "permission_key" => "delete_project",
                "permission_groups_id" => 17
            ],
            [
                "name" => "Upload Project Attachment",
                "permission_key" => "upload_project_attachment",
                "permission_groups_id" => 17
            ],
            [
                "name" => "Delete PM Attachment",
                "permission_key" => "delete_pm_attachment",
                "permission_groups_id" => 17
            ],

            /** Task management Permissions */
            [
                "name" => "View Task List",
                "permission_key" => "view_task_list",
                "permission_groups_id" => 18
            ],
            [
                "name" => "Create Task",
                "permission_key" => "create_task",
                "permission_groups_id" => 18
            ],
            [
                "name" => "Show Task Detail",
                "permission_key" => "show_task_detail",
                "permission_groups_id" => 18
            ],
            [
                "name" => "Edit Task",
                "permission_key" => "edit_task",
                "permission_groups_id" => 18
            ],
            [
                "name" => "Delete Task",
                "permission_key" => "delete_task",
                "permission_groups_id" => 18
            ],
            [
                "name" => "Upload Task Attachment",
                "permission_key" => "upload_task_attachment",
                "permission_groups_id" => 18
            ],
            [
                "name" => "Create Checklist",
                "permission_key" => "create_checklist",
                "permission_groups_id" => 18
            ],
            [
                "name" => "Edit Checklist",
                "permission_key" => "edit_checklist",
                "permission_groups_id" => 18
            ],
            [
                "name" => "Delete Checklist",
                "permission_key" => "delete_checklist",
                "permission_groups_id" => 18
            ],
            [
                "name" => "Create Comment",
                "permission_key" => "create_comment",
                "permission_groups_id" => 18
            ],
            [
                "name" => "Delete Comment",
                "permission_key" => "delete_comment",
                "permission_groups_id" => 18
            ],

            /** Employee Apl Permissions */

            [
                "name" => "View Profile",
                "permission_key" => "view_profile",
                "permission_groups_id" => 19
            ],
            [
                "name" => "Allow Password Change",
                "permission_key" => "allow_change_password",
                "permission_groups_id" => 19
            ],
            [
                "name" => "Update Profile",
                "permission_key" => "update_profile",
                "permission_groups_id" => 19
            ],
            [
                "name" => "Show Employee Detail",
                "permission_key" => "show_profile_detail",
                "permission_groups_id" => 19
            ],
            [
                "name" => "Show Team Sheet",
                "permission_key" => "list_team_sheet",
                "permission_groups_id" => 19
            ],

//            /** Attendance Apl Permissions */
//            [
//                "name" => "Allow CheckIn",
//                "permission_key" => "check_in",
//                "permission_groups_id" => 20
//            ],
//            [
//                "name" => "Allow CheckOut",
//                "permission_key" => "check_out",
//                "permission_groups_id" => 20
//            ],


            /** Leave Apl Permissions */
            [
                "name" => "Submit Leave Request",
                "permission_key" => "leave_request_create",
                "permission_groups_id" => 21
            ],

            /** Query Api Permissions */
            [
                "name" => "Submit Query",
                "permission_key" => "query_create",
                "permission_groups_id" => 22
            ],

            /** Tada Api Permissions */
            [
                "name" => "Submit Tada Detail",
                "permission_key" => "tada_create",
                "permission_groups_id" => 23
            ],
            [
                "name" => "Update Tada Detail",
                "permission_key" => "tada_update",
                "permission_groups_id" => 23
            ],
            [
                "name" => "Delete Tada Attachment",
                "permission_key" => "delete_tada_attachment",
                "permission_groups_id" => 23
            ],

            /** Task Mgmt Apl Permissions */
            [
                "name" => "Change Task Status",
                "permission_key" => "edit_task_status",
                "permission_groups_id" => 24
            ],
            [
                "name" => "Change Checklist Status",
                "permission_key" => "toggle_checklist_status",
                "permission_groups_id" => 24
            ],
            [
                "name" => "Submit Comment",
                "permission_key" => "submit_comment",
                "permission_groups_id" => 24
            ],
            [
                "name" => "Comment Delete",
                "permission_key" => "comment_delete",
                "permission_groups_id" => 24
            ],
            [
                "name" => "Reply Delete",
                "permission_key" => "reply_delete",
                "permission_groups_id" => 24
            ],

            /** Dashboard Detail Permissions */
            [
                "name" => "Show Project Details",
                "permission_key" => "project_detail",
                "permission_groups_id" => 25
            ],

            [
                "name" => "Show Client Details",
                "permission_key" => "client_detail",
                "permission_groups_id" => 25
            ],

            [
                "name" => "Employee Attendance",
                "permission_key" => "allow_attendance",
                "permission_groups_id" => 25
            ],

            /** Asset Management  */

            [
                "name" => "List Asset Type",
                "permission_key" => "list_type",
                "permission_groups_id" => 26
            ],

            [
                "name" => "Create Asset Type",
                "permission_key" => "create_type",
                "permission_groups_id" => 26
            ],

            [
                "name" => "Show Type Detail",
                "permission_key" => "show_type",
                "permission_groups_id" => 26
            ],

            [
                "name" => "Edit Asset Type",
                "permission_key" => "edit_type",
                "permission_groups_id" => 26
            ],

            [
                "name" => "Delete Asset Type",
                "permission_key" => "delete_type",
                "permission_groups_id" => 26
            ],

            [
                "name" => "List Assets",
                "permission_key" => "list_assets",
                "permission_groups_id" => 26
            ],

            [
                "name" => "Create Assets Detail",
                "permission_key" => "create_assets",
                "permission_groups_id" => 26
            ],

            [
                "name" => "Edit Assets Detail",
                "permission_key" => "edit_assets",
                "permission_groups_id" => 26
            ],

            [
                "name" => "Show Assets Detail",
                "permission_key" => "show_asset",
                "permission_groups_id" => 26
            ],

            [
                "name" => "Delete Assets Detail",
                "permission_key" => "delete_assets",
                "permission_groups_id" => 26
            ],

            [
                "name" => "Request Leave",
                "permission_key" => "request_leave",
                "permission_groups_id" => 8
            ],

            /** Mobile Notification  */
            [
                "name" => "Leave Request Notification",
                "permission_key" => "employee_leave_request",
                "permission_groups_id" => 27
            ],
            [
                "name" => "Check In Notification",
                "permission_key" => "employee_check_in",
                "permission_groups_id" => 27
            ],

            [
                "name" => "Check Out Notification",
                "permission_key" => "employee_check_out",
                "permission_groups_id" => 27
            ],

            [
                "name" => "Support Notification",
                "permission_key" => "employee_support",
                "permission_groups_id" => 27
            ],

            [
                "name" => "Tada Notification",
                "permission_key" => "tada_alert",
                "permission_groups_id" => 27
            ],

            [
                "name" => "Advance Salary Request Notification",
                "permission_key" => "advance_salary_alert",
                "permission_groups_id" => 27
            ],
            /** Attendance Method  */
            [
                "name" => "List Router",
                "permission_key" => "list_router",
                "permission_groups_id" => 28
            ],
            [
                "name" => "Create Router",
                "permission_key" => "create_router",
                "permission_groups_id" => 28
            ],
            [
                "name" => "Edit Router",
                "permission_key" => "edit_router",
                "permission_groups_id" => 28
            ],
            [
                "name" => "Delete Router",
                "permission_key" => "delete_router",
                "permission_groups_id" => 28
            ],
            [
                "name" => "List NFC",
                "permission_key" => "list_nfc",
                "permission_groups_id" => 28
            ],
            [
                "name" => "Delete NFC",
                "permission_key" => "delete_nfc",
                "permission_groups_id" => 28
            ],
            [
                "name" => "List QR",
                "permission_key" => "list_qr",
                "permission_groups_id" => 28
            ],
            [
                "name" => "Create QR",
                "permission_key" => "create_qr",
                "permission_groups_id" => 28
            ],
            [
                "name" => "Edit QR",
                "permission_key" => "edit_qr",
                "permission_groups_id" => 28
            ],
            [
                "name" => "Delete QR",
                "permission_key" => "delete_qr",
                "permission_groups_id" => 28
            ],

            /** Attendance Method API  */
            [
                "name" => "Create NFC",
                "permission_key" => "create_nfc",
                "permission_groups_id" => 29
            ],

            /** Leave Permissions */
            [
                "name" => "Create Leave Request",
                "permission_key" => "create_leave_request",
                "permission_groups_id" => 8
            ],

            /** Payroll Permissions  -32 */
            [
                "name" => "View Payroll List",
                "permission_key" => "view_payroll_list",
                "permission_groups_id" => 30
            ],
            [
                "name" => "Generate Payroll",
                "permission_key" => "generate_payroll",
                "permission_groups_id" => 30
            ],
            [
                "name" => "Show Payroll Detail",
                "permission_key" => "show_payroll_detail",
                "permission_groups_id" => 30
            ],
            [
                "name" => "Edit Payroll",
                "permission_key" => "edit_payroll",
                "permission_groups_id" => 30
            ],
            [
                "name" => "Delete Payroll",
                "permission_key" => "delete_payroll",
                "permission_groups_id" => 30
            ],

            [
                "name" => "Payroll Payment",
                "permission_key" => "payroll_payment",
                "permission_groups_id" => 30
            ],
            [
                "name" => "Print Payroll",
                "permission_key" => "print_payroll",
                "permission_groups_id" => 30
            ],

            /** Payroll Setting Permissions  -33 */
            /** Salary Components */
            [
                "name" => "Salary Component",
                "permission_key" => "salary_component",
                "permission_groups_id" => 31
            ],
            /** Salary Group */
            [
                "name" => "Salary Group",
                "permission_key" => "salary_group",
                "permission_groups_id" => 31
            ],
            /** SSF */
            [
                "name" => "SSF",
                "permission_key" => "ssf",
                "permission_groups_id" => 31
            ],
            /** Bonus */
            [
                "name" => "Bonus",
                "permission_key" => "bonus",
                "permission_groups_id" => 31
            ],
            /** Salary TDS */
            [
                "name" => "Salary TDS Rule",
                "permission_key" => "salary_tds",
                "permission_groups_id" => 31
            ],
            /** Advance Salary Limit */
            [
                "name" => "Advance Salary Limit",
                "permission_key" => "advance_salary_limit",
                "permission_groups_id" => 31
            ],
            /** Overtime */
            [
                "name" => "OverTime Setting",
                "permission_key" => "overtime_setting",
                "permission_groups_id" => 31
            ],

            /** Undertime */
            [
                "name" => "UnderTime Setting",
                "permission_key" => "undertime_setting",
                "permission_groups_id" => 31
            ],

            /** Payment Methods */
            [
                "name" => "Payment Method",
                "permission_key" => "payment_method",
                "permission_groups_id" => 31
            ],


            /** Advance Salary Permissions  -32 */
            [
                "name" => "View Advance Salary List",
                "permission_key" => "view_advance_salary_list",
                "permission_groups_id" => 32
            ],
            [
                "name" => "Update Advance Salary",
                "permission_key" => "update_advance_salary",
                "permission_groups_id" => 32
            ],
            [
                "name" => "Delete Advance Salary",
                "permission_key" => "delete_advance_salary",
                "permission_groups_id" => 32
            ],

            /** Employee Salary Permissions  -33 */
            [
                "name" => "View Employee Salary List",
                "permission_key" => "view_salary_list",
                "permission_groups_id" => 33
            ],
            [
                "name" => "Add Employee Salary",
                "permission_key" => "add_salary",
                "permission_groups_id" => 33
            ],
            [
                "name" => "Employee Salary History",
                "permission_key" => "show_salary_history",
                "permission_groups_id" => 33
            ],
            [
                "name" => "Employee Salary Increment",
                "permission_key" => "salary_increment",
                "permission_groups_id" => 33
            ],
            [
                "name" => "Edit Employee Salary",
                "permission_key" => "edit_salary",
                "permission_groups_id" => 33
            ],
            [
                "name" => "Delete Employee Salary",
                "permission_key" => "delete_salary",
                "permission_groups_id" => 33
            ],
            [
                "name" => "Change Salary Cycle",
                "permission_key" => "change_salary_cycle",
                "permission_groups_id" => 33
            ],

            /** Payroll Management API  -34 */
            [
                "name" => "View Payslip List",
                "permission_key" => "view_payslip_list",
                "permission_groups_id" => 34
            ],
            [
                "name" => "Payslip Detail",
                "permission_key" => "view_payslip_detail",
                "permission_groups_id" => 34
            ],

            /** Advance Salary API  -35 */
            [
                "name" => "Advance Salary List",
                "permission_key" => "advance_salary_list",
                "permission_groups_id" => 35
            ],
            [
                "name" => "Add Advance Salary List",
                "permission_key" => "add_advance_salary",
                "permission_groups_id" => 35
            ],
            [
                "name" => "Update Advance Salary API",
                "permission_key" => "update_advance_salary_api",
                "permission_groups_id" => 35
            ],


            /** Time Leave   -36 */
            [
                "name" => "Time Leave List",
                "permission_key" => "time_leave_list",
                "permission_groups_id" => 36
            ],

            [
                "name" => "Update Time Leave",
                "permission_key" => "update_time_leave",
                "permission_groups_id" => 36
            ], [
                "name" => "Create Time Leave",
                "permission_key" => "create_time_leave_request",
                "permission_groups_id" => 36
            ],

            /** Award Management  -37 */
            /** Award Type */
            [
                "name" => "Award Type List",
                "permission_key" => "award_type_list",
                "permission_groups_id" => 37
            ],
            [
                "name" => "Create Award Type",
                "permission_key" => "create_award_type",
                "permission_groups_id" => 37
            ],
            [
                "name" => "Update Award Type",
                "permission_key" => "update_award_type",
                "permission_groups_id" => 37
            ],
            [
                "name" => "Delete Award Type",
                "permission_key" => "delete_award_type",
                "permission_groups_id" => 37
            ],
            /** Award  */
            [
                "name" => "Award List",
                "permission_key" => "award_list",
                "permission_groups_id" => 37
            ],
            [
                "name" => "Create Award",
                "permission_key" => "create_award",
                "permission_groups_id" => 37
            ],
            [
                "name" => "Update Award",
                "permission_key" => "update_award",
                "permission_groups_id" => 37
            ],
            [
                "name" => "Show Award Detail",
                "permission_key" => "show_award",
                "permission_groups_id" => 37
            ],
            [
                "name" => "Delete Award",
                "permission_key" => "delete_award",
                "permission_groups_id" => 37
            ],
            /** Dashboard Detail Permissions */

            [
                "name" => "View Attendance Summary",
                "permission_key" => "attendance_summary",
                "permission_groups_id" => 25
            ],
            /** Tax report Permissions  -38 */
            [
                "name" => "View Tax Report",
                "permission_key" => "view_tax_report",
                "permission_groups_id" => 38
            ],
            [
                "name" => "Edit Tax Report",
                "permission_key" => "edit_tax_report",
                "permission_groups_id" => 38
            ],

            /** Event Management  -39 */
            [
                "name" => "Event List",
                "permission_key" => "list_event",
                "permission_groups_id" => 39
            ],
            [
                "name" => "Create Event",
                "permission_key" => "create_event",
                "permission_groups_id" => 39
            ],
            [
                "name" => "Update Event",
                "permission_key" => "edit_event",
                "permission_groups_id" => 39
            ],
            [
                "name" => "Show Event Detail",
                "permission_key" => "show_event",
                "permission_groups_id" => 39
            ],
            [
                "name" => "Delete Event",
                "permission_key" => "delete_event",
                "permission_groups_id" => 39
            ],

            /** Training Management  -40 */
            /** Training Type */
            [
                "name" => "Training Type List",
                "permission_key" => "training_type_list",
                "permission_groups_id" => 40
            ],
            [
                "name" => "Create Training Type",
                "permission_key" => "create_training_type",
                "permission_groups_id" => 40
            ],
            [
                "name" => "Update Training Type",
                "permission_key" => "update_training_type",
                "permission_groups_id" => 40
            ],
            [
                "name" => "Show Training Type",
                "permission_key" => "show_training_type",
                "permission_groups_id" => 40
            ],
            [
                "name" => "Delete Training Type",
                "permission_key" => "delete_training_type",
                "permission_groups_id" => 40
            ],

            /** Trainers */
            [
                "name" => "Trainer List",
                "permission_key" => "list_trainer",
                "permission_groups_id" => 40
            ],
            [
                "name" => "Create Trainer",
                "permission_key" => "create_trainer",
                "permission_groups_id" => 40
            ],
            [
                "name" => "Update Trainer",
                "permission_key" => "update_trainer",
                "permission_groups_id" => 40
            ],
            [
                "name" => "Show Trainer",
                "permission_key" => "show_trainer",
                "permission_groups_id" => 40
            ],
            [
                "name" => "Delete Trainer",
                "permission_key" => "delete_trainer",
                "permission_groups_id" => 40
            ],
            /** Training */
            [
                "name" => "Training List",
                "permission_key" => "list_training",
                "permission_groups_id" => 40
            ],
            [
                "name" => "Create Training",
                "permission_key" => "create_training",
                "permission_groups_id" => 40
            ],
            [
                "name" => "Update Training",
                "permission_key" => "update_training",
                "permission_groups_id" => 40
            ],
            [
                "name" => "Show Training",
                "permission_key" => "show_training",
                "permission_groups_id" => 40
            ],
            [
                "name" => "Delete Training",
                "permission_key" => "delete_training",
                "permission_groups_id" => 40
            ],


            /** Leave Permissions */
            [
                "name" => "Grant Admin Leave Permission",
                "permission_key" => "access_admin_leave",
                "permission_groups_id" => 8
            ],
            /** Leave Approval Permissions */
            [
                "name" => "Leave Approval List",
                "permission_key" => "list_leave_approval",
                "permission_groups_id" => 41
            ],
            [
                "name" => "Create Leave Approval",
                "permission_key" => "create_leave_approval",
                "permission_groups_id" => 41
            ],
            [
                "name" => "Update Leave Approval",
                "permission_key" => "update_leave_approval",
                "permission_groups_id" => 41
            ],
            [
                "name" => "Show Leave Approval",
                "permission_key" => "show_leave_approval",
                "permission_groups_id" => 41
            ],
            [
                "name" => "Delete Leave Approval",
                "permission_key" => "delete_leave_approval",
                "permission_groups_id" => 41
            ],

            /** Resignation Management  -42 */

            [
                "name" => "Resignation List",
                "permission_key" => "list_resignation",
                "permission_groups_id" => 42
            ],
            [
                "name" => "Create Resignation",
                "permission_key" => "create_resignation",
                "permission_groups_id" => 42
            ],
            [
                "name" => "Update Resignation",
                "permission_key" => "update_resignation",
                "permission_groups_id" => 42
            ],
            [
                "name" => "Show Resignation",
                "permission_key" => "show_resignation",
                "permission_groups_id" => 42
            ],
            [
                "name" => "Delete Resignation",
                "permission_key" => "delete_resignation",
                "permission_groups_id" => 42
            ],

            /** Termination Management  -43 */
            /** Termination Type */
            [
                "name" => "Termination Type List",
                "permission_key" => "termination_type_list",
                "permission_groups_id" => 43
            ],
            [
                "name" => "Create Termination Type",
                "permission_key" => "create_termination_type",
                "permission_groups_id" => 43
            ],
            [
                "name" => "Update Termination Type",
                "permission_key" => "update_termination_type",
                "permission_groups_id" => 43
            ],
            [
                "name" => "Show Termination Type",
                "permission_key" => "show_termination_type",
                "permission_groups_id" => 43
            ],
            [
                "name" => "Delete Termination Type",
                "permission_key" => "delete_termination_type",
                "permission_groups_id" => 43
            ],

            /** Termination */
            [
                "name" => "Termination List",
                "permission_key" => "list_termination",
                "permission_groups_id" => 43
            ],
            [
                "name" => "Create Termination",
                "permission_key" => "create_termination",
                "permission_groups_id" => 43
            ],
            [
                "name" => "Update Termination",
                "permission_key" => "update_termination",
                "permission_groups_id" => 43
            ],
            [
                "name" => "Show Termination",
                "permission_key" => "show_termination",
                "permission_groups_id" => 43
            ],
            [
                "name" => "Delete Termination",
                "permission_key" => "delete_termination",
                "permission_groups_id" => 43
            ],


            /** Resignation Api */
            [
                "name" => "Add/View Resignation",
                "permission_key" => "add_resignation",
                "permission_groups_id" => 44
            ],

            /** Mobile Notification  */
            [
                "name" => "Resignation Notification",
                "permission_key" => "employee_resignation_request",
                "permission_groups_id" => 27
            ],

            /** Warning */
            [
                "name" => "Warning List",
                "permission_key" => "list_warning",
                "permission_groups_id" => 45
            ],
            [
                "name" => "Create Warning",
                "permission_key" => "create_warning",
                "permission_groups_id" => 45
            ],
            [
                "name" => "Update Warning",
                "permission_key" => "update_warning",
                "permission_groups_id" => 45
            ],
            [
                "name" => "Show Warning",
                "permission_key" => "show_warning",
                "permission_groups_id" => 45
            ],
            [
                "name" => "Delete Warning",
                "permission_key" => "delete_warning",
                "permission_groups_id" => 45
            ],


            /** Warning Api */
            [
                "name" => "Response Warning",
                "permission_key" => "warning_response",
                "permission_groups_id" => 46
            ],

            /** Complaint */
            [
                "name" => "Complaint List",
                "permission_key" => "list_complaint",
                "permission_groups_id" => 47
            ],
            [
                "name" => "Create Complaint",
                "permission_key" => "create_complaint",
                "permission_groups_id" => 47
            ],
            [
                "name" => "Update Complaint",
                "permission_key" => "update_complaint",
                "permission_groups_id" => 47
            ],
            [
                "name" => "Show Complaint",
                "permission_key" => "show_complaint",
                "permission_groups_id" => 47
            ],
            [
                "name" => "Delete Complaint",
                "permission_key" => "delete_complaint",
                "permission_groups_id" => 47
            ],


            /** Complaint Api */
            [
                "name" => "Add/View Complaint",
                "permission_key" => "add_complaint",
                "permission_groups_id" => 48
            ],

            /** Promotion -49 */
            [
                "name" => "Promotion List",
                "permission_key" => "list_promotion",
                "permission_groups_id" => 49
            ],
            [
                "name" => "Create Promotion",
                "permission_key" => "create_promotion",
                "permission_groups_id" => 49
            ],
            [
                "name" => "Update Promotion",
                "permission_key" => "update_promotion",
                "permission_groups_id" => 49
            ],
            [
                "name" => "Show Promotion",
                "permission_key" => "show_promotion",
                "permission_groups_id" => 49
            ],
            [
                "name" => "Delete Promotion",
                "permission_key" => "delete_promotion",
                "permission_groups_id" => 49
            ],

            /** Transfer -50 */
            [
                "name" => "Transfer List",
                "permission_key" => "list_transfer",
                "permission_groups_id" => 50
            ],
            [
                "name" => "Create Transfer",
                "permission_key" => "create_transfer",
                "permission_groups_id" => 50
            ],
            [
                "name" => "Update Transfer",
                "permission_key" => "update_transfer",
                "permission_groups_id" => 50
            ],
            [
                "name" => "Show Transfer",
                "permission_key" => "show_transfer",
                "permission_groups_id" => 50
            ],
            [
                "name" => "Delete Transfer",
                "permission_key" => "delete_transfer",
                "permission_groups_id" => 50
            ],

            /** Asset Management -26 */


            [
                "name" => "Assign Asset",
                "permission_key" => "assign_asset",
                "permission_groups_id" => 26
            ],
            [
                "name" => "Asset Assign List",
                "permission_key" => "asset_assign_list",
                "permission_groups_id" => 26
            ],
            [
                "name" => "Asset Return List",
                "permission_key" => "asset_return_list",
                "permission_groups_id" => 26
            ],
            [
                "name" => "Update Asset Repair",
                "permission_key" => "assign_repair_update",
                "permission_groups_id" => 26
            ],

            /** Mobile Notification  */
            [
                "name" => "Asset Return Notification",
                "permission_key" => "asset_return_notification",
                "permission_groups_id" => 27
            ],


            /** Attendance Method  */

            [
                "name" => "List Device",
                "permission_key" => "list_device",
                "permission_groups_id" => 28
            ],
            [
                "name" => "Delete Device",
                "permission_key" => "delete_device",
                "permission_groups_id" => 28
            ],[
                "name" => "Create Device",
                "permission_key" => "create_device",
                "permission_groups_id" => 28
            ],

            /** Setting Permissions */
            [
                "name" => "Attendance Setting",
                "permission_key" => "attendance_setting",
                "permission_groups_id" => 6
            ],

             /** PF */
            [
                "name" => "PF",
                "permission_key" => "pf",
                "permission_groups_id" => 31
            ],




            /** Loan Management  -51 */
            /** Loan Type */
            [
                "name" => "Loan Type List",
                "permission_key" => "loan_type_list",
                "permission_groups_id" => 51
            ],
            [
                "name" => "Create Loan Type",
                "permission_key" => "create_loan_type",
                "permission_groups_id" => 51
            ],
            [
                "name" => "Update Loan Type",
                "permission_key" => "update_loan_type",
                "permission_groups_id" => 51
            ],
            [
                "name" => "Show Loan Type",
                "permission_key" => "show_loan_type",
                "permission_groups_id" => 51
            ],
            [
                "name" => "Delete Loan Type",
                "permission_key" => "delete_loan_type",
                "permission_groups_id" => 51
            ],

            /** Loan */
            [
                "name" => "Loan List",
                "permission_key" => "list_loan",
                "permission_groups_id" => 51
            ],
            [
                "name" => "Create Loan",
                "permission_key" => "create_loan",
                "permission_groups_id" => 51
            ],
            [
                "name" => "Update Loan",
                "permission_key" => "update_loan",
                "permission_groups_id" => 51
            ],
            [
                "name" => "Show Loan",
                "permission_key" => "show_loan",
                "permission_groups_id" => 51
            ],
            [
                "name" => "Delete Loan",
                "permission_key" => "delete_loan",
                "permission_groups_id" => 51
            ],
            [
                "name" => "Request Loan",
                "permission_key" => "request_loan",
                "permission_groups_id" => 51
            ],
            [
                "name" => "Loan Repayment List",
                "permission_key" => "repayment_list",
                "permission_groups_id" => 51
            ],


            /** Mobile Notification  */
            [
                "name" => "Loan Request Notification",
                "permission_key" => "employee_loan_request",
                "permission_groups_id" => 27
            ],

            /** Leave Permissions */
            [
                "name" => "Leave Balance",
                "permission_key" => "leave_balance",
                "permission_groups_id" => 8
            ],


            /** Loan Setting */
            [
                "name" => "Loan Setting",
                "permission_key" => "loan_setting",
                "permission_groups_id" => 51
            ],
            [
                "name" => "Loan History",
                "permission_key" => "loan_history",
                "permission_groups_id" => 51
            ],


            /** Employee Card Permissions */
            [
                "name" => "View Employee Card",
                "permission_key" => "view_card_pdf",
                "permission_groups_id" => 5
            ],
            [
                "name" => "Card Template",
                "permission_key" => "card_template",
                "permission_groups_id" => 5
            ],


            /** Attendance Api Permissions */
            [
                "name" => "Allow CheckIn/Checkout",
                "permission_key" => "allow_checkin_checkout",
                "permission_groups_id" => 20
            ],


            /** Payroll Management API  -34 */
            [
                "name" => "View SSF History",
                "permission_key" => "ssf_history",
                "permission_groups_id" => 34
            ],

            /** Loan API  -52 */
            [
                "name" => "Add Loan Request",
                "permission_key" => "add_loan_request",
                "permission_groups_id" => 52
            ],
            [
                "name" => "Update Loan Request",
                "permission_key" => "update_loan_request",
                "permission_groups_id" => 52
            ],
            [
                "name" => "Request Loan Settlement",
                "permission_key" => "request_settlement",
                "permission_groups_id" => 52
            ],
            [
                "name" => "Update Loan Settlement Request",
                "permission_key" => "update_settlement",
                "permission_groups_id" => 52
            ],





        ];
    }

}
