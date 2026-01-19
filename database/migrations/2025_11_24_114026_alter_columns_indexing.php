<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            if (! $this->indexExists('users', 'idx_deleted_at')) {
                $table->index('deleted_at', 'idx_deleted_at');
            }
            if (! $this->indexExists('users', 'idx_is_active')) {
                $table->index('is_active', 'idx_is_active');
            }
            if (! $this->indexExists('users', 'idx_branch_id')) {
                $table->index('branch_id', 'idx_branch_id');
            }
            if (! $this->indexExists('users', 'idx_department_id')) {
                $table->index('department_id', 'idx_department_id');
            }
            if (! $this->indexExists('users', 'idx_role_id')) {
                $table->index('role_id', 'idx_role_id');
            }
            if (! $this->indexExists('users', 'idx_supervisor_id')) {
                $table->index('supervisor_id', 'idx_supervisor_id');
            }
            if (! $this->indexExists('users', 'idx_office_time_id')) {
                $table->index('office_time_id', 'idx_office_time_id');
            }
        });

        Schema::table('leave_requests_master', function (Blueprint $table) {
            if (! $this->indexExists('leave_requests_master', 'idx_leave_from')) {
                $table->index('leave_from', 'idx_leave_from');
            }

            if (! $this->indexExists('leave_requests_master', 'idx_leave_to')) {
                $table->index('leave_to', 'idx_leave_to');
            }

            if (! $this->indexExists('leave_requests_master', 'idx_status')) {
                $table->index('status', 'idx_status');
            }
            if (! $this->indexExists('leave_requests_master', 'idx_requested_by')) {
                $table->index('requested_by', 'idx_requested_by');
            }
            if (! $this->indexExists('leave_requests_master', 'idx_leave_type_id')) {
                $table->index('leave_type_id', 'idx_leave_type_id');
            }
            if (! $this->indexExists('leave_requests_master', 'idx_branch_id')) {
                $table->index('branch_id', 'idx_branch_id');
            }
            if (! $this->indexExists('leave_requests_master', 'idx_department_id')) {
                $table->index('department_id', 'idx_department_id');
            }
        });

        Schema::table('time_leaves', function (Blueprint $table) {

            if (! $this->indexExists('time_leaves', 'idx_issue_date')) {
                $table->index('issue_date', 'idx_issue_date');
            }

            if (! $this->indexExists('time_leaves', 'idx_request_updated_by')) {
                $table->index('request_updated_by', 'idx_request_updated_by');
            }

            if (! $this->indexExists('time_leaves', 'idx_status')) {
                $table->index('status', 'idx_status');
            }
            if (! $this->indexExists('time_leaves', 'idx_requested_by')) {
                $table->index('requested_by', 'idx_requested_by');
            }
            if (! $this->indexExists('time_leaves', 'idx_branch_id')) {
                $table->index('branch_id', 'idx_branch_id');
            }
            if (! $this->indexExists('time_leaves', 'idx_department_id')) {
                $table->index('department_id', 'idx_department_id');
            }
        });

        Schema::table('employee_leave_types', function (Blueprint $table) {

            if (! $this->indexExists('employee_leave_types', 'idx_employee_id')) {
                $table->index('employee_id', 'idx_employee_id');
            }

            if (! $this->indexExists('employee_leave_types', 'idx_leave_type_id')) {
                $table->index('leave_type_id', 'idx_leave_type_id');
            }

            if (! $this->indexExists('employee_leave_types', 'idx_days')) {
                $table->index('days', 'idx_days');
            }
            if (! $this->indexExists('employee_leave_types', 'idx_is_active')) {
                $table->index('is_active', 'idx_is_active');
            }

        });
        Schema::table('leave_types', function (Blueprint $table) {

            if (! $this->indexExists('leave_types', 'idx_leave_allocated')) {
                $table->index('leave_allocated', 'idx_leave_allocated');
            }

            if (! $this->indexExists('leave_types', 'idx_branch_id')) {
                $table->index('branch_id', 'idx_branch_id');
            }

            if (! $this->indexExists('leave_types', 'idx_gender')) {
                $table->index('gender', 'idx_gender');
            }
            if (! $this->indexExists('leave_types', 'idx_is_active')) {
                $table->index('is_active', 'idx_is_active');
            }

        });

        Schema::table('permissions', function (Blueprint $table) {

            if (! $this->indexExists('permissions', 'idx_permission_groups_id')) {
                $table->index('permission_groups_id', 'idx_permission_groups_id');
            }
            if (! $this->indexExists('permissions', 'idx_permission_key')) {
                $table->index('permission_key', 'idx_permission_key');
            }

        });

        Schema::table('permission_roles', function (Blueprint $table) {

            if (! $this->indexExists('permission_roles', 'idx_permission_id')) {
                $table->index('permission_id', 'idx_permission_id');
            }
            if (! $this->indexExists('permission_roles', 'idx_role_id')) {
                $table->index('role_id', 'idx_role_id');
            }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_deleted_at');
            $table->dropIndex('idx_is_active');
            $table->dropIndex('idx_branch_id');
            $table->dropIndex('idx_department_id');
            $table->dropIndex('idx_role_id');
            $table->dropIndex('idx_supervisor_id');
            $table->dropIndex('idx_office_time_id');
        });

        Schema::table('leave_requests_master', function (Blueprint $table) {
            $table->dropIndex('idx_leave_from');
            $table->dropIndex('idx_leave_to');
            $table->dropIndex('idx_requested_by');
            $table->dropIndex('idx_leave_type_id');
            $table->dropIndex('idx_status');
            $table->dropIndex('idx_branch_id');
            $table->dropIndex('idx_department_id');
        });

        Schema::table('employee_leave_types', function (Blueprint $table) {
            $table->dropIndex('idx_employee_id');
            $table->dropIndex('idx_leave_type_id');
            $table->dropIndex('idx_days');
            $table->dropIndex('idx_is_active');
        });
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropIndex('idx_branch_id');
            $table->dropIndex('idx_gender');
            $table->dropIndex('idx_leave_allocated');
            $table->dropIndex('idx_is_active');
        });

        Schema::table('time_leaves', function (Blueprint $table) {

           $table->dropIndex( 'idx_issue_date');
           $table->dropIndex( 'idx_request_updated_by');
           $table->dropIndex('idx_status');
           $table->dropIndex( 'idx_requested_by');
           $table->dropIndex( 'idx_branch_id');
           $table->dropIndex( 'idx_department_id');
        });

        Schema::table('permissions', function (Blueprint $table) {

            $table->dropIndex( 'idx_permission_groups_id');
            $table->dropIndex( 'idx_permission_key');

        });

        Schema::table('permission_roles', function (Blueprint $table) {

            $table->dropIndex('idx_permission_id');
            $table->dropIndex( 'idx_role_id');

        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return collect(DB::select("SHOW INDEX FROM {$table}"))->pluck('Key_name')->contains($indexName);
    }
};
