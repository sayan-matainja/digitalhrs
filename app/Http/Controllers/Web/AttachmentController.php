<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Requests\Project\ProjectAttachmentRequest;
use App\Requests\Task\TaskAttachmentRequest;
use App\Services\Attachment\AttachmentService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttachmentController extends Controller
{
    use CustomAuthorizesRequests;
    private $projectView = 'admin.project.';
    private $taskView = 'admin.task.';

    private AttachmentService $attachmentService;

    public function __construct(AttachmentService $attachmentService)
    {
        $this->attachmentService = $attachmentService;
    }

    /**
     * @throws AuthorizationException
     */
    public function createProjectAttachment($projectId)
    {
        $this->authorize('upload_project_attachment');
        try {
            return view($this->projectView. 'document-upload', compact('projectId'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function createTaskAttachment($taskId)
    {
        $this->authorize('create_task');
        try {
            return view($this->taskView. 'upload-attachment', compact('taskId'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function storeProjectAttachment(ProjectAttachmentRequest $request)
    {
        $this->authorize('upload_project_attachment');
        try {
            $validatedData = $request->validated();
            DB::beginTransaction();
                $this->attachmentService->storeProjectAttachment($validatedData);
            DB::commit();
            return redirect()
                ->route('admin.projects.show',$validatedData['project_id'])
                ->with('success', __('message.attachment_added'));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }

    public function storeTaskAttachment(TaskAttachmentRequest $request)
    {
        $this->authorize('create_task');
        try {
            $validatedData = $request->validated();
            DB::beginTransaction();
                $this->attachmentService->storeTaskAttachment($validatedData);
            DB::commit();
            return redirect()
                ->route('admin.tasks.show',$validatedData['task_id'])
                ->with('success', __('message.attachment_added'));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }


    public function deleteAttachmentById($attachmentId): RedirectResponse
    {
        $this->authorize('delete_pm_attachment');
        try {
            DB::beginTransaction();
            $this->attachmentService->deleteProjectAttachment($attachmentId);
            DB::commit();
            return redirect()->back()->with('success', __('message.attachment_deleted'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }
}
