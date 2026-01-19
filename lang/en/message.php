<?php

return [

    /** AdvanceSalary */
    'status_changed' => 'Status Changed Successfully',
    'salary_deleted'=>'Advance Salary Detail Deleted Successfully',
    'theme_not_found'=>'Theme Detail Not found',
    'theme_changed'=>'Theme Changed Successfully',

    /** Asset */
    'asset_saved'=>'Asset record saved successfully',
    'asset_delete'=>'Asset Detail Deleted Successfully',
    'asset_update'=>'Asset Detail Updated Successfully',

    /** Asset Type */
    'asset_type_create'=>'Asset Type Created Successfully',
    'asset_type_update'=>'Asset Type Detail Updated Successfully',
    'asset_type_delete'=>'Asset Type Deleted Successfully',

    /** attachment */
    'attachment_added'=>'Attachments Added Successfully',
    'attachment_deleted'=>'Attachment Deleted Successfully',

    /** Attendance */
    'check_in'=>'Employee Check In Successful',
    'check_out'=>'Employee Check Out Successful',
    'attendance_status_change'=>'Attendance status changed successful',
    'add_attendance'=>'Employee Attendance Added Successfully',
    'router_not_found'=>'Branch Routers Detail Not Found.',
    'checkout_notification'=>'Check Out Notification',
    'employee_checkout'=>':name has checked out at :time',
    'employee_shift_checkout_alert'=>'Employee already checked out for the shift',
    'checkin_alert'=>'You cannot checkOut without checkIn',
    'employee_detail_not_found'=>'Employee Detail Not Found',
    'attendance_delete'=>'Attendance Deleted Successfully',
    'attendance_edit'=>'Employee Attendance Edited Successfully',
    'checkIn'=>'Check In SuccessFull',
    'checkOut'=>'Check Out SuccessFull',
    'employee_checkin'=>':name has checked in at  :time',
    'checkin_notification'=>'Check In Notification',

    /** award */
    'delete_award'=>'Award Deleted Successfully',
    'update_award'=>'Award Detail Updated Successfully',
    'add_award'=>'Award Created Successfully',

    /** award Type */
    'delete_award_type'=>'Award Type Deleted Successfully',
    'update_award_type'=>'Award Type Detail Updated Successfully',
    'add_award_type'=>'Award Type Created Successfully',


    /** award */
    'delete_bonus'=>'Bonus Detail Deleted Successfully',
    'update_bonus'=>'Bonus Detail Updated Successfully',
    'add_bonus'=>'Bonus Detail Created Successfully',

    /** Branch */
    'add_branch'=>'New Branch Added Successfully',
    'branch_not_found'=>'Branch Detail Not Found',
    'update_branch'=>'Branch Detail Updated Successfully',
    'branch_delete_warning'=>'Cannot Delete Branch With Departments',
    'branch_delete_router_warning'=>'Cannot Delete Branch With Router Detail',
    'delete_branch'=>'Branch Record Deleted  Successfully',

    /** client */
    'add_client'=>'Client Detail Created Successfully',
    'update_client'=>'Client Detail Updated Successfully',
    'delete_client'=>'Client Detail Deleted  Successfully',


    /** company */
    'add_company'=>'Company Detail Added Successfully',
    'update_company'=>'Company Detail Updated Successfully',
    'add_company_warning'=>'This is a demo version. Please buy the application to use the full feature',

    /** Dashboard */
    'company_not_found'=>'Company Detail Not Found',

    /** Department */
    'add_department'=>'New Department Added Successfully',
    'department_not_found'=>'Department Detail Not Found',
    'update_department'=>'Department Detail Updated Successfully',
    'delete_department_warning'=>'Cannot Delete Department With Posts',
    'delete_department'=>'Department Record Deleted  Successfully',

    /** Employee Logout */
    'logout_request'=>'Employee Logout Request Accepted',

    /** Employee salary */
    'account_not_found'=>'Employee Account Detail Not Found',
    'invalid_cycle'=>'Invalid Cycle Data',
    'salary_cycle_update'=>'Salary Cycle Updated to :cycle Successfully',
    'payroll_update'=>'Payroll updated successfully',
    'payroll_delete_error'=>'Payslip cannot be deleted once paid or locked.',
    'payroll_delete'=>'Payroll deleted successfully',
    'salary_add'=>'Employee Salary added successfully',
    'salary_update'=>'Employee Salary Updated successfully',
    'payment_date_error'=>'Please select the paid on date while making payment.',
    'payment_method_error'=>'Please select a payment method while making payment.',
    'salary_delete'=>'Employee Salary deleted successfully',

    /** fiscal year */
    'invalid_format' => 'Invalid fiscal year format.',
    'start_date_mismatch' => 'Start date year must match the first year in the fiscal year.',
    'end_date_mismatch' => 'End date year must match the second year in the fiscal year.',
    'minimum_duration' => 'The fiscal year must be at least 365 days long.',
    'overlaps_existing' => 'The new fiscal year overlaps with an existing one.',
    'fiscal_year_created' => 'Fiscal Year Created Successfully',
    'fiscal_year_updated' => 'Fiscal Year Detail Updated Successfully',
    'fiscal_year_deleted' => 'Fiscal Year Deleted Successfully',

    /** general setting */
    'general_setting_update'=> 'General Setting Detail Updated Successfully',
    'general_setting_delete'=>'General Setting Detail Deleted  Successfully',
    'general_setting_add'=>'New Detail In General Setting Added',

    /** holiday */
    'holidays_added' => 'New Holiday Detail Added Successfully',
    'holidays_updated' => 'Holiday Detail Updated Successfully',
    'holiday_status_changed' => 'Holiday Status Changed Successfully',
    'holidays_removed' => 'Holiday Removed Successfully',
    'holidays_imported' => 'Holidays Detail Imported Successfully',
    'holidays_import_error' => 'Your CSV files have unmatched Columns to our database. Your columns must be in this sequence: event, event_date, note and is_public_holiday only',

    /** Leave */
    'leave_status_updated' => 'Status Updated Successfully',
    'leave_submitted' => 'Leave request submitted Successfully',
    'leave_notification_title' => 'Leave Request Notification',
    'leave_notification_message' => ':name has requested :days day(s) leave from :from_date on :request_date. Reason: :reason',
    'leave_notification_message_on_behalf' => ':requester_name has requested :days day(s) of leave from :from_date on :request_date on your behalf.',

    /** leave type  */
    'data_found'=>'Data Found',
    'leave_type_added' => 'New Leave type added successfully, make sure you have leave approval process for this leave type.',
    'leave_type_updated' => 'Leave type updated successfully, make sure you have leave approval process for this leave type.',
    'leave_type_not_found' => 'Leave Type Not Found',
    'leave_type_cannot_delete_in_use' => 'Cannot delete :name as it is in use',
    'leave_type_deleted' => 'Leave Type Deleted Successfully',
    'leave_type_early_exit_status_changed' => 'Early exit status changed Successfully',

    /** nfc */
    'nfc_deleted' => 'NFC deleted successfully',

    /** Notice */
    'notice_status_changed' => 'Notice Status changed Successfully',
    'notice_deleted' => 'Notice Deleted Successfully',
    'notice_sent' => 'Notice Sent Successfully',
    'notice_update_sent'=>'Notice Updated and Sent Successfully',
    'notice_create_sent'=>'Notice created and sent Successfully',

    /** Notification */
    'notification_update_sent'=>'Notification Updated and Sent Successfully',
    'notification_create_sent'=>'Notification created and sent Successfully',
    'notification_not_found'=>'Notification Not Found',
    'notification_status_change'=>'Notification Status changed  Successfully',
    'notification_deleted'=>'Notification Deleted  Successfully',
    'notification_sent'=>'Notification Sent Successfully',

    /** office Time */
    'office_time_already_exists' => 'Office Schedule with the start time :opening_time and closing time :closing_time already exists',
    'office_time_added' => 'New Office Schedule Added Successfully',
    'office_time_added_night_shift' => 'New Office Schedule Added Successfully. Please remember multiple check in, check out will not work for this shift',
    'office_time_not_found' => 'Office Time Detail Not Found',
    'office_time_updated' => 'Office Time Detail Updated Successfully',
    'office_time_updated_night_shift' => 'Office Time Detail Updated Successfully. Please remember multiple check in, check out will not work for this shift',
    'office_time_status_change_error' => 'Office time status cannot be changed. It is in use.',
    'office_time_delete_error' => 'Office time cannot be deleted. It is in use.',
    'office_time_deleted' => 'Office schedule Deleted Successfully',

    /** Overtime */
    'overtime_add'=>'OverTime Added Successfully',
    'overtime_update'=>'OverTime updated Successfully',
    'overtime_delete'=> 'OverTime Deleted Successfully',

    /** Payment Currency */
    'currency_update'=>'Payment Currency Updated Successfully',

    /** Payment Method */
    'payment_method_add'=>'Payment Methods Added Successfully',
    'payment_method_update'=>'Payment Method Updated Successfully',
    'payment_method_delete'=>'Payment Method Detail Deleted Successfully',

    /** post */
    'post_add'=>'New Post Added Successfully',
    'post_update'=>'Post Detail Updated Successfully',
    'post_not_found'=>'Post Detail Not Found',
    'post_delete_error'=>'Post With Active or Inactive Employees Cannot Be Deleted.',
    'post_delete'=>'Post Detail Deleted Successfully',


    /** privacy policy */
    'privacy_policy_not_found'=>'privacy policy not found',
    'page_not_found'=>'Page Not Found',

    /** Project */
    'project_notification'=>'Project Notification',
    'project_assign'=>'You are assigned to a new project :name with deadline on :deadline',
    'project_add'=>'New Project Added Successfully',
    'project_update'=>'Project Detail Updated Successfully',
    'project_delete'=>'Project Detail Deleted  Successfully',
    'something_went_wrong'=>'Something went wrong',
    'project_not_found'=>'Project detail not found',
    'project_leader_updated'=>':name project leader data updated  successfully ',
    'project_member_updated'=>':name project member data updated successfully ',

    /** QR */
    'qr_add'=>'QR created successfully',
    'qr_update'=>'QR updated successfully',
    'qr_delete'=>'QR deleted successfully',

    /** Role */
    'add_role'=>'New Role Added Successfully',
    'role_not_found'=>'Role Detail Not Found',
    'admin_role_delete_error'=>'Cannot Edit Admin Role',
    'role_update'=>'Role Detail Updated Successfully',
    'assign_role_delete_error'=>'Cannot Delete Assigned Role',
    'role_delete'=>'Role Detail Deleted  Successfully',
    'assign_admin_warning'=>'Admin Role Is Always Assigned With All Permission',
    'permission_update'=>'Permission Updated To Role Successfully',

    /** Router */
    'router_add'=>'New Router Detail Added Successfully',
    'router_detail_not_found'=>'Router Detail Not Found',
    'router_delete'=>'Router Detail Trashed Successfully',
    'router_update'=>'Router Detail Updated Successfully',

    /** salary component */
    'salary_component_add'=>'Salary Component Added Successfully',
    'salary_component_update'=>'Salary Component Detail Updated Successfully',
    'salary_component_delete'=>'Salary Component Detail Deleted Successfully',

    /** SalaryGroup */
    'salary_group_delete'=>'Salary Group Detail Deleted Successfully',
    'salary_group_delete_error'=>'You cannot delete this salary group, it is in use.',
    'salary_group_update'=>'Salary Group Detail Updated Successfully',
    'salary_group_add'=>'Salary Group Added Successfully',

    /** salary History */
    'salary_history_update'=>'Employee Salary Updated Successfully',

    /** Salary Tds */
    'salary_tds_add'=>'Salary TDS Detail Added Successfully',
    'salary_tds_update'=>'TDS Detail Updated Successfully',
    'salary_tds_delete'=>'Salary TDS Detail Deleted Successfully',

    /** SSF */
    'ssf_add'=>'SSF Detail Added Successfully',
    'ssf_update'=>'SSF Detail Updated Successfully',

    /** Static Page */
    'add_page'=>'New Company Static Page Content Added Successfully',
    'update_page'=>'Company Static Page Content Updated Successfully',
    'change_page_status'=>'Company Static Page Content Status changed  Successfully',
    'static_page_not_found'=>'Company Static Page Content Detail Not Found',
    'delete_page'=>'Company Static Page Content Deleted  Successfully',

    /** Support */
    'query_not_found'=>'Query detail not found',
    'support_notification'=>'Support Notification',
    'support_message'=> 'Your Support Request for :title is viewed and is :status',
    'query_delete'=>'Query Deleted Successfully',
    'query_status_change'=>'Query Status Changed Successfully',

    /** Tada Attachment */
    'attachment_add'=>'Tada Attachment Added Successfully',
    'attachment_delete'=>'Tada Attachment Deleted Successfully',

    /** Tada */
    'add_tada'=>'Tada Detail Added Successfully',
    'update_tada'=>'Tada Detail Updated Successfully',
    'delete_tada'=>'Tada Detail Deleted Successfully',
    'tada_settlement_change'=>'Tada Settlement Status changed Successfully',
    'tada_status_change'=>'Tada Detail Status Changed Successfully',

    /** Task CheckList */
    'checklist_add'=>'Task Checklists Added Successfully',
    'checklist_update'=>'Task Checklist Updated Successfully',
    'checklist_delete'=>'Task Checklist Deleted  successfully',
    'checklist_status_change'=>'Task Checklist status changed successfully',

    /** comment */
    'add_comment'=>'Successfully Created Data',
    'delete_comment'=>'Comment Deleted Successfully',
    'delete_reply'=>'Comment Reply Deleted Successfully',

    /** Task */
    'task_notification'=>'Task Assignment Notification',
    'task_notification_message'=>'You are assigned to a new task :name with deadline on :end_date',
    'task_remove_notification_message'=>'You are removed from the task :name',
    'task_add'=>'New Task Added Successfully',
    'task_update'=>'Task Detail Updated Successfully',
    'task_delete'=>'Task Detail Deleted Successfully',

    /** Tax report */
    'tax_report_add'=>'Tax Report Updated Successfully',

    /** team meeting */
    'team_meeting_create'=>'Team Meeting created and sent Successfully',
    'team_meeting_update'=>'Team Meeting Updated and Sent Successfully',
    'team_meeting_delete'=>'Team Meeting Detail Deleted  Successfully',
    'image_delete'=>'Image Deleted  Successfully',

    /** Time Leave */
    'time_leave_not_found'=>'Leave request not found',
    'time_leve_fetch_error'=>'An error occurred while fetching the leave request details',
    'status_update' => 'Status Updated Successfully',
    'time_leve_submit'=>'Leave request submitted Successfully',

    /** Undertime */
    'ut_create'=>'UnderTime created Successfully',
    'ut_update'=>'UnderTime Updated Successfully',
    'ut_delete'=>'UnderTime Deleted Successfully',

    /** User */
    'add_user'=>'New Employee Detail Added Successfully',
    'user_not_found'=>'User Detail Not Found',
    'update_user'=>'User Detail Updated Successfully',
    'user_is_active_changed'=>'Users Is Active Status Changed  Successfully',
    '_delete_own'=>'cannot delete own records',
    'user_remove'=>'User Detail Removed Successfully',
    'workspace_change'=>'User Workspace Changed Successfully',
    'user_password_change'=>'User Password Changed Successfully',
    'force_logout'=>'Force log out successFull',
    'employee_leave_not_found'=>'Employee Leave Type Detail Not Found',
    'employee_leave_removed'=>'User Leave Type Removed Successfully',


    /** service */
    'asset_type_not_found'=>'Asset Type Not Found',
    'attachment_not_found'=>'Attachment Detail not found',
    'project_attachment_not_found'=>'Project Attachment Detail Not Found',
    'leave_attendance'=>'Cannot check in when leave request is Approved/Pending.',
    'holiday_attendance'=>'Check In not allowed on holidays or on office Off Days',
    'earlier_checkin'=>'CheckIn is earlier than allowed time!',
    'late_checkin'=>'CheckIn is late than allowed time contact Admin!',
    'early_checkout'=>'You cannot check-out early!',
    'late_checkout'=>'Check-Out is late than allowed time. contact Admin',
    'attendance_outside'=>'Cannot take Attendance outside of workspace area',
    'attendance_not_found'=>'Attendance Detail Not Found',
    'invalid_credential'=>'Invalid User Name Credentials !',
    'no_record_credentials'=>'These credentials do not match our records.',
    'log_out_request'=>'Log Out Request Still Pending, Please Contact Administrator !!',
    'log_out_error'=>'Please Log Out From Another Device',
    'client_not_found'=>'Client Detail Not Found',
    'holiday_not_found'=>'Holiday Detail Not Found',
    'different_leave_bs_year'=>'Leave to B.S year must be the same as the leave from B.S year.',
    'different_leave_ad_year'=>'Leave to A.D year must be the same as the leave from A.D year.',
    'offday_leave'=>'You cannot take leave on holidays or on office Off Days',
    'leave_status_error'=>'Leave request is already :status for given date.',
    'leave_exceed_error'=>'Leave Request Days Exceeded by :day days for :name. Please try another type of leave',
    'leave_request_not_found'=>'Leave request detail not found',
    'leave_pending_error'=>'Leave request is already :status for given date.',
    'leave_end_time_error'=>'You cannot take leave after office end time',
    'leave_start_time_error'=>'You cannot take leave before office start time',
    'nfc_not_found'=>'Nfc detail not found',
    'notice_not_found'=>'Notice Detail Not Found',
    'user_notification_not_found'=>'User notification detail not found',
    'unauthorized_action'=>'UnAuthorized Action',
    'advance_salary_not_found'=>'Advance Salary Detail Not Found',
    'approve_salary_delete_error'=>'Approved Advance Salary Detail Cannot Be Deleted.',
    'advance_salary_limit'=>'The released amount cannot be greater than the requested amount (:amount )',
    'advance_salary_update_error'=>'Detail Cannot Be Updated or Changed Once Approved or Rejected',
    'employee_salary_not_found'=>'Employee Salary Not found',
    'cannot_comment'=>'sorry you cannot comment on this task',
    'cannot_delete_comment'=>'Sorry, You cannot delete this comment',
    'cannot_delete_reply'=>'Sorry, You cannot delete this comment reply',


    /** Leave Approval */
    'add_leave_approval'=>'Leave Approval Successfully',
    'update_leave_approval'=>'Leave Approval Updated Successfully',
    'delete_leave_approval'=>'Leave Approval Deleted Successfully',

    /** Event */
    'event_create'=>'Event created Successfully',
    'event_update'=>'Event Updated Successfully',
    'event_delete'=>'Event Deleted Successfully',

    /** training Type */
    'delete_training_type'=>'Training Type Deleted Successfully',
    'update_training_type'=>'Training Type Detail Updated Successfully',
    'add_training_type'=>'Training Type Created Successfully',


    /** trainer */
    'delete_trainer'=>'Trainer Deleted Successfully',
    'update_trainer'=>'Trainer Detail Updated Successfully',
    'add_trainer'=>'Trainer Created Successfully',

    /** training */
    'delete_training'=>'Training Deleted Successfully',
    'update_training'=>'Training Detail Updated Successfully',
    'add_training'=>'Training Created Successfully',


    'training_type_status_change_error' => 'This training type status cannot be changed. It is in use.',
    'training_type_delete_error' => 'This training type cannot be deleted. It is in use.',
    'trainer_status_change_error' => 'This trainer status cannot be changed. It is in use.',
    'trainer_delete_error' => 'This trainer cannot be deleted. It is in use.',
    'award_type_status_change_error' => 'This award type status cannot be changed. It is in use.',
    'award_type_delete_error' => 'This award type cannot be deleted. It is in use.',

    'project_remove'=>'You are removed from the project :name',
    'project_change_msg' => 'The project ":name" you are associated with has been updated',
    'task_change_msg' => 'The task ":name" you are assigned to has been updated',


    'salary_notification'=>'Salary Notification',

    /** warning */
    'delete_warning'=>'Warning Deleted Successfully',
    'update_warning'=>'Warning Detail Updated Successfully',
    'add_warning'=>'Warning Created Successfully',


    /** Termination Type */
    'delete_termination_type'=>'Termination Type Deleted Successfully',
    'update_termination_type'=>'Termination Type Detail Updated Successfully',
    'add_termination_type'=>'Termination Type Created Successfully',

    /** Termination */
    'delete_termination'=>'Termination Deleted Successfully',
    'update_termination'=>'Termination Detail Updated Successfully',
    'add_termination'=>'Termination Created Successfully',

    /** Resignation */
    'delete_resignation'=>'Resignation Deleted Successfully',
    'update_resignation'=>'Resignation Detail Updated Successfully',
    'add_resignation'=>'Resignation Created Successfully',

    /** complaint */
    'delete_complaint'=>'Complaint Deleted Successfully',
    'update_complaint'=>'Complaint Detail Updated Successfully',
    'add_complaint'=>'Complaint Created Successfully',

    'task_member_updated'=>':name task member data updated successfully ',
    'task_not_found'=>'Task detail not found',

    /** promotion */
    'delete_promotion'=>'Promotion Deleted Successfully',
    'update_promotion'=>'Promotion Detail Updated Successfully',
    'add_promotion'=>'Promotion Created Successfully',

     /** transfer */
    'delete_transfer'=>'Transfer Deleted Successfully',
    'update_transfer'=>'Transfer Detail Updated Successfully',
    'add_transfer'=>'Transfer Created Successfully',

    /** Theme Color */
    'theme_color_create'=>'Theme Color Created Successfully',
    'theme_color_update'=>'Theme Color Detail Updated Successfully',


    'client_status_change_error' => 'Client status cannot be changed. It is in use.',
    'client_delete_error' => 'Client cannot be deleted. It is in use.',

    'add_admin'=>'New User Added Successfully',
    'asset_notification_title' => 'Asset Assignment Notification',
    'asset_notification_message' => ':asset has been assigned to you.',
    'asset_assignment_return' => 'Asset returned successful.',

    'just_check_in'=>'You have just checked in!',

    'user_allow_holiday_check_in_changed'=>'Users allow checkin/checkout on holidays and off days status changed successfully',

    'asset_assignment_saved' => 'Asset has been assigned successfully',
    'tax_report_remove'=>'Tax Report Removed Successfully',
    'salary_component_delete_error'=>'You cannot delete this salary component, it is in use.',
    'salary_component_status_change_error'=>'You cannot disable this salary component, it is in use.',


    /** device */
    'device_delete'=>'Biometric attendance device deleted successfully',
    'device_update'=>'Biometric attendance device updated successfully',
    'device_saved'=>'Biometric attendance device created successfully',

    /** attendance setting */
    'attendance_setting_update'=> 'Attendance Setting Detail Updated Successfully',
    'attendance_setting_delete'=>'Attendance Setting Detail Deleted  Successfully',

    'payslip_payment_error'=>'Cannot process payment for a future or current month payslip.',
    /** PF */
       'pf_add'=>'EPF Detail Added Successfully',
       'pf_update'=>'EPF Detail Updated Successfully',


    'invalid_leave_request' =>'You have previous leave request pending for approval.',


    /** loan type */

    'loan_type_create'=>'Loan type created successfully',
    'loan_type_update'=>'Loan type detail updated successfully',
    'loan_type_delete'=>'Loan type deleted successfully',
    'loan_type_not_found'=>'Loan type not found',

    /** loan  */
    'loan_saved'=>'Loan created successfully',
    'loan_request'=>'Loan request stored successfully',
    'loan_update'=>'Loan detail updated successfully',
    'loan_delete'=>'Loan deleted successfully',
    'loan_not_found'=>'Loan not found',
    'loan_settlement_error'=>'Loan already settled',
    'loan_status_change'=>'Loan Status Changed Successfully',
    'loan_request_notification'=>'Loan Request Notification',
    'loan_request_message' => ':name has requested loan for :reasons',
    'loan_notification_message_on_behalf' => ':requester_name has requested loan for :reason on your behalf.',
    'loan_notification_status_update' => 'your loan requested on :date for :reason has been :status.',
    'loan_pending_error' => 'You already have pending loan.',

    'loan_settlement_request_notification'=>'Loan Settlement Request Notification',
    'loan_settlement_request_message' => ':name has requested for loan settlement. Reason :reasons',
    'loan_settlement_notification_message_on_behalf' => ':requester_name has requested for loan settlement on your behalf. Reason :reason.',
    'loan_settlement_notification_status_update' => 'your loan settlement request on :date has been :status.',
    'loan_settlement_saved'=>'Loan settlement request created successfully',
    'loan_settlement_request'=>'Loan settlement request stored successfully',
    'loan_settlement_update'=>'Loan settlement request updated successfully',
    'loan_settlement_delete'=>'Loan settlement request deleted successfully',
    'loan_settlement_not_found'=>'Loan settlement request not found',
    'loan_settlement_pending_error' => 'You already have pending loan settlement request.',
    'loan_settlement_status_change'=>'Loan Settlement Status Changed Successfully',

    'first_half_checkin_error' => 'You cannot check in because your half day leave is in the first half between :opening to :half',
    'first_half_checkout_error' => 'You cannot check out because your half day leave is in the first half between :opening to :half',
    'second_half_checkin_error' => 'You cannot check in because your half day leave is in the second half between :half to :closing',
    'recent_loan_settlement_error' => 'You have settled your previous loan recently, you must wait another month to apply new loan.',


    'half_leave_notification_message' => ':name has requested half day leave on :from_date for :half. Reason: :reason',
    'half_leave_notification_message_on_behalf' => ':requester_name has requested half day of leave on :from_date for :half on your behalf.',
    'time_leave_pending_error'=>'you already have time leave request for given date.',

    'leave_request_error'=>'You already have leave request for given date.',

    /** employee_card setting */
    'card_setting_update'=> 'Employee card setting detail updated successfully',
    'card_setting_delete'=>'Employee card setting detail deleted  successfully',
    'card_setting_add'=>'New detail in employee card setting added',

    /** Complaint */
    'complaint_notification'=>'Complaint Notification',

     /** Warning */
    'warning_notification'=>'Warning Notification',

    /** Training */
    'training_notification'=>'Training Notification',


    /** Event */
    'event_notification'=>'Event Notification',

    /** Loan */
    'loan_notification'=>'Loan Notification',
    'team_meeting_notification'=>'Team Meeting Notification',
    'complaint_response_notification'=>'Complaint Response Notification',




];
