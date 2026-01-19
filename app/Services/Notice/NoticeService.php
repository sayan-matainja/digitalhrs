<?php

namespace App\Services\Notice;

use App\Helpers\AppHelper;
use App\Repositories\NoticeRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class NoticeService
{
    private NoticeRepository $noticeRepo;

    public function __construct(NoticeRepository $noticeRepo)
    {
        $this->noticeRepo = $noticeRepo;
    }

    public function getAllCompanyNotices($filterParameters, $select = ['*'], $with = [])
    {
        if (AppHelper::ifDateInBsEnabled()) {
            $filterParameters['publish_date_from'] = isset($filterParameters['publish_date_from']) ?
                AppHelper::dateInYmdFormatNepToEng($filterParameters['publish_date_from']) : null;
            $filterParameters['publish_date_to'] = isset($filterParameters['publish_date_to']) ?
                AppHelper::dateInYmdFormatNepToEng($filterParameters['publish_date_to']) : null;
        }
        return $this->noticeRepo->getAllCompanyNotices($filterParameters, $select, $with);
    }

    public function getAllReceivedNoticeDetail($perPage,$select=['*'])
    {
        return $this->noticeRepo->getAllEmployeeNotices($perPage,$select);
    }

    /**
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function store($validatedData)
    {

        $validatedData['company_id'] = AppHelper::getAuthUserCompanyId();

        $notice = $this->noticeRepo->store($validatedData);
        if ($notice) {
            $this->createManyNoticeReceiver($notice, $validatedData);
            $this->createManyNoticeDepartment($notice, $validatedData);
        }

        return $notice;

    }

    public function createManyNoticeReceiver($noticeDetail, $validatedData)
    {
        if (empty($validatedData['receiver']['employee'])) {
            return [];
        }

        $receivers = array_map(function ($employeeId) {
            return ['notice_receiver_id' => $employeeId];
        }, $validatedData['receiver']['employee']);

        return $this->noticeRepo->createManyNoticeReceiver($noticeDetail, $receivers);
    }
    public function createManyNoticeDepartment($noticeDetail, $validatedData)
    {
        if (empty($validatedData['receiver']['department'])) {
            return [];
        }

        $departments = array_map(function ($departmentId) {
            return ['department_id' => $departmentId];
        }, $validatedData['receiver']['department']);

        return $noticeDetail->noticeDepartments()->createMany($departments);
    }

    /**
     * @param $id
     * @return bool
     * @throws Exception
     */
    public function changeNoticeStatus($id): bool
    {
        try {
            $noticeDetail = $this->findOrFailNoticeDetailById($id);
            DB::beginTransaction();
            $this->noticeRepo->toggleStatus($noticeDetail);
            DB::commit();
            return true;
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * @param $id
     * @param $select
     * @param $with
     * @return mixed
     * @throws Exception
     */
    public function findOrFailNoticeDetailById($id, $select = ['*'], $with = [])
    {
        $noticeDetail = $this->noticeRepo->findNoticeDetailById($id, $select, $with);
        if (!$noticeDetail) {
            throw new Exception(__('message.notice_not_found'), 400);
        }
        return $noticeDetail;
    }

    /**
     * @param $noticeDetail
     * @param $validatedData
     * @return mixed
     */
    public function update($noticeDetail, $validatedData)
    {

        $notice = $this->noticeRepo->update($noticeDetail, $validatedData);
        if ($notice) {
            $deleteReceiverDetail = $this->noticeRepo->deleteNoticeReceiversDetail($notice);
            if ($deleteReceiverDetail) {
                $this->createManyNoticeReceiver($notice, $validatedData);
            }
            $deleteDepartment = $this->noticeRepo->deleteNoticeDepartment($notice);
            if ($deleteDepartment) {
                $this->createManyNoticeDepartment($notice, $validatedData);
            }
        }

        return $notice;

    }

    public function updatePublishDateAndStatus($noticeDetail, $validatedData)
    {
        return $this->noticeRepo->update($noticeDetail, $validatedData);

    }

    /**
     * @param $id
     * @return void
     * @throws Exception
     */
    public function deleteNotice($id)
    {
        try {
            DB::beginTransaction();
            $noticeDetail = $this->findOrFailNoticeDetailById($id);
            $this->noticeRepo->delete($noticeDetail);
            DB::commit();
            return;
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

}
