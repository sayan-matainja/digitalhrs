<?php

namespace App\Repositories;

use App\Helpers\AppHelper;
use App\Models\Notification;
use App\Models\UserNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationRepository
{
    const ACTIVE = 1;
    const INACTIVE = 0;
    const SEEN = 1;
    const UNSEEN = 0;

    public function getAllCompanyNotifications($filterParameters,$select=['*'],$with=[])
    {
        $branchId = null;
        $authUserId = null;
        if(auth()->user()){
            $branchId = auth()->user()->branch_id;
            $authUserId = auth()->user()->id;
        }

        return Notification::select($select)->with($with)
            ->when(isset($filterParameters['type']), function($query) use ($filterParameters){
                $query->where('type',$filterParameters['type']);
            })
            ->when(isset($branchId) && $authUserId != 1, function($query) use ($branchId){
                $query->whereHas('createdBy',function($query) use ($branchId){
                    $query->where('branch_id', $branchId);
                });
            })
            ->orderBy('notification_publish_date','Desc')
            ->paginate( getRecordPerPage());
    }

    public function getAllCompanyRecentActiveNotification($perPage,$select=['*'])
    {

        $userId = getAuthUserCode();

        $notifications = DB::table('notifications')
//          ->whereDate('notification_publish_date', '>=', Carbon::now()->subDays(90))
            ->where(function ($query)  use ($userId) {
                $query->whereExists(function ($exists) use ($userId) {
                    $exists->select(DB::raw(1))
                        ->from('user_notifications')
                        ->whereColumn('user_notifications.notification_id', 'notifications.id')
                        ->where('user_notifications.user_id', $userId);
                })
                    ->orWhereNotExists(function ($notExists) {
                        $notExists->select(DB::raw(1))
                            ->from('user_notifications')
                            ->whereColumn('user_notifications.notification_id', 'notifications.id');
                    });
            })
            ->orderBy('notification_publish_date', 'desc')
            ->paginate($perPage);


        return $notifications;
    }

    public function findNotificationDetailById($id,$select=['*'],$with=[])
    {
        return Notification::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }

    public function getNotificationForNavBar($select)
    {
        return Notification::select($select)
            ->where('company_id',AppHelper::getAuthUserCompanyId() )
            ->where('is_active',self::ACTIVE)
            ->latest()
            ->take(5)
            ->get();
    }

    public function store($validatedData)
    {
        return Notification::create($validatedData)->fresh();
    }

    public function update($notificationDetail,$validatedData)
    {
        return $notificationDetail->update($validatedData);
    }

    public function delete($notificationDetail)
    {
        return $notificationDetail->delete();
    }

    public function toggleStatus($id)
    {
        $notificationDetail = $this->findNotificationDetailById($id);
        return $notificationDetail->update([
            'is_active' => !$notificationDetail->is_active,
        ]);
    }

    public function notifyUser($notificationDetail,$usersArray)
    {
        return $notificationDetail->notifiedUsers()->createMany($usersArray);
    }

    public function findUserNotificationDetailById($id,$select)
    {
        return UserNotification::select($select)
            ->where('id',$id)
            ->first();
    }

    public function changeUserNotificationToSeen($userNotificationDetail)
    {
        return $userNotificationDetail->update([
            'is_seen' => self::SEEN
        ]);
    }

}
