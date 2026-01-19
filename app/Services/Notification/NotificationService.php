<?php

namespace App\Services\Notification;

use App\Helpers\AppHelper;
use App\Repositories\NotificationRepository;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public function __construct(public NotificationRepository $notificationRepo)
    {
    }

    public function getAllCompanyNotifications($select=['*'],$with=[])
    {
        return $this->notificationRepo->getAllCompanyNotifications($select,$with);
    }

    public function getAllCompanyRecentActiveNotification($perPage,$select=['*'])
    {
        return $this->notificationRepo->getAllCompanyRecentActiveNotification($perPage,$select);
    }

    public function findNotificationDetailById($id,$with=[],$select=['*'])
    {
        try{
            $notificationDetail = $this->notificationRepo->findNotificationDetailById($id,$select,$with);
            if(!$notificationDetail){
                throw new \Exception(__('message.notification_not_found'),404);
            }
            return $notificationDetail;
        }catch(\Exception $exception){
            throw $exception;
        }
    }

    public function store($validatedData)
    {
        try{
            DB::beginTransaction();
            $validatedData['company_id'] = AppHelper::getAuthUserCompanyId();
            $notification = $this->notificationRepo->store($validatedData);
            if($notification){
                $notifyUser = $this->prepareDataForNotifyUser($validatedData['user_id']);
                $this->notificationRepo->notifyUser($notification,$notifyUser);
            }
            DB::commit();
            return $notification;
        }catch(\Exception $exception){
            DB::rollBack();
            throw $exception;
        }
    }

    private function prepareDataForNotifyUser($userIds): array
    {
        try{
            $users = [];
            foreach ($userIds as $key =>  $value){
                $users[$key]['user_id'] = $value;
            }
            return $users;
        }catch(\Exception $exception){
            throw $exception;
        }
    }

    public function findUserNotificationDetailById($id,$select=['*'])
    {
        try{
            $userNotificationDetail = $this->notificationRepo->findUserNotificationDetailById($id,$select);
            if(!$userNotificationDetail){
                throw new \Exception(__('message.user_notification_not_found'),404);
            }
            return $userNotificationDetail;
        }catch(\Exception $exception){
            throw $exception;
        }
    }

    public function changeUserNotificationToSeen($id)
    {
        try{
            $userNotificationDetail = $this->findUserNotificationDetailById($id);
            if($userNotificationDetail->user_id !== getAuthUserCode()){
                throw new \Exception(__('message.unauthorized_action'),422);
            }
            DB::beginTransaction();
            $this->notificationRepo->changeUserNotificationToSeen($userNotificationDetail);
            DB::commit();
            return $userNotificationDetail;
        }catch(\Exception $exception){
            DB::rollBack();
            throw $exception;
        }

    }

}
