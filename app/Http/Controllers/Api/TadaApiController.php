<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Requests\Tada\TadaRequest;
use App\Resources\Tada\TadaCollection;
use App\Resources\Tada\TadaDetailResource;
use App\Services\Tada\TadaAttachmentService;
use App\Services\Tada\TadaService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;

class TadaApiController extends Controller
{
    use CustomAuthorizesRequests;
    public TadaService $tadaService;
    public TadaAttachmentService $attachmentService;

    public function __construct(TadaService $tadaService,TadaAttachmentService $attachmentService)
    {
        $this->tadaService = $tadaService;
        $this->attachmentService = $attachmentService;
    }

    public function getEmployeesTadaLists(Request $request)
    {
        try {
            $select = ['id','title','employee_id','created_at','status','remark','total_expense'];
            $with = ['employeeDetail:id,name'];
            $tadaLists = $this->tadaService->getAllTadaDetailOfEmployee(getAuthUserCode(),$select,$with);
            $data = new TadaCollection($tadaLists);
            return AppHelper::sendSuccessResponse(__('index.data_found'),$data);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function getEmployeesTadaDetail($tadaId)
    {
        try {
            $select= ['*'];
            $with = ['employeeDetail:id,name','attachments','verifiedBy:id,name'];
            $tadaDetail = $this->tadaService->findEmployeeTadaDetailByTadaId($tadaId,$with,$select);
            $detail = new TadaDetailResource($tadaDetail);
            return AppHelper::sendSuccessResponse(__('index.data_found'),$detail);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());

        }
    }

    /**
     * @throws AuthorizationException
     * @throws MessagingException
     * @throws FirebaseException
     */
    public function storeTadaDetail(TadaRequest $request)
    {
        $this->authorize('tada_create');

        try {
            $permissionKeyForNotification = 'tada_alert';

            $validatedData = $request->validated();

            DB::beginTransaction();
                $tada = $this->tadaService->store($validatedData);
            DB::commit();
            AppHelper::sendNotificationToAuthorizedUser(
                __('index.tada_alert'),
                __('index.tada_notification',['name' => ucfirst( auth()->user()->name),
                    'title'=> $tada->title]),
                $permissionKeyForNotification
            );
            return AppHelper::sendSuccessResponse(__('index.data_created_successfully'));
        }catch(Exception $e) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @throws AuthorizationException
     * @throws MessagingException
     * @throws FirebaseException
     */
    public function updateTadaDetail(TadaRequest $request)
    {
        $this->authorize('tada_update');
        try {

            $permissionKeyForNotification = 'tada_alert';

            $tadaId = $request->tada_id;
            $with = ['attachments'];
            $detail = $this->tadaService->findEmployeeTadaDetailByTadaId($tadaId,$with);
            if($detail['status'] != 'pending'){
                throw new Exception(__('index.tada_update_alert'),403);
            }
            $validatedData = $request->validated();
            DB::beginTransaction();
                $this->tadaService->update($detail,$validatedData);
            DB::commit();
            AppHelper::sendNotificationToAuthorizedUser(
                __('index.tada_alert'),
                __('index.tada_detail_notification',['name' => ucfirst( auth()->user()->name),
                    'title'=> $detail->title]),
                $permissionKeyForNotification
            );
            return AppHelper::sendSuccessResponse(__('index.data_updated_successfully'));
        }catch (Exception $e) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function deleteTadaAttachment($attachmentId)
    {
        $this->authorize('delete_tada_attachment');
        try{

            $select = ['*'];
            $with = ['tada:id,status,employee_id'];
            $attachmentDetail = $this->attachmentService->findEmployeeTadaAttachmentDetail($attachmentId,$select,$with);
            if(!$attachmentDetail){
                throw new Exception(__('index.attachment_detail_not_found'),404);
            }
            if($attachmentDetail?->tada?->status !== 'pending'){
                throw new Exception(__('index.cannot_delete_attachment_detail'),403);
            }
            if($attachmentDetail->total_attachments < 2 ){
                throw new Exception(__('index.upload_another_attachment_before_delete'),400);
            }
            DB::beginTransaction();
            $this->attachmentService->delete($attachmentDetail);
            DB::commit();
            return AppHelper::sendSuccessResponse(__('index.attachment_deleted_successfully'));
        }catch(Exception $exception){
            DB::rollBack();
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }

    }

}
