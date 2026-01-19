<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Tada\TadaAttachmentService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TadaAttachmentController extends Controller
{
    use CustomAuthorizesRequests;
    public TadaAttachmentService $tadaAttachementService;
    private $view = 'admin.tada.';

    public function __construct(TadaAttachmentService $tadaAttachementService)
    {
        $this->tadaAttachementService = $tadaAttachementService;
    }

    public function create($tadaId)
    {
        $this->authorize('create_attachment');
        try {
            return view($this->view . 'upload_attachments', compact('tadaId'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->authorize('create_attachment');
        try {
            $validatedData = $request->validate([
                'tada_id' => ['required', Rule::exists('tadas', 'id')],
                'attachments' => ['required', 'array', 'min:1'],
                'attachments.*.' => ['required', 'file', 'mimes:jpeg,png,jpg,docx,doc,xls,pdf', 'max:5048']
            ]);
            DB::beginTransaction();
            $this->tadaAttachementService->store($validatedData);
            DB::commit();
            return redirect()->route('admin.tadas.show', $validatedData['tada_id'])
                ->with('success', __('message.attachment_add'));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_attachment');
        try {
            DB::beginTransaction();
            $attachmentDetail = $this->tadaAttachementService->findTadaAttachmentById($id);
            $this->tadaAttachementService->delete($attachmentDetail);
            DB::commit();
            return redirect()->back()->with('success', __('message.attachment_delete'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


}
