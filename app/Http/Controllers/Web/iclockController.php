<?php

namespace App\Http\Controllers\Web;

use App\Helpers\LaravelZkteco;
use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class iclockController extends Controller
{

    public function addUsers()
    {

        $device = Device::where('status','=','working')->first();
        $systemUser = User::select(['id', 'name', 'employee_code'])
            ->where('status', 'verified')
            ->where('is_active', 1)
            ->get();

        $zk = new LaravelZkteco($device->ip_address);
        if (!$zk->connect()) {
            Log::error('Failed to connect to ZKTeco device');
            return "Device not connected";
        }

        try {
            $role = 0; // 0 = User, 14 = Super Admin
            $deviceUsers = $zk->getUser();

            $deviceUserIds = array_column($deviceUsers, 'uid'); // Create lookup for efficiency

            // Find the highest UID to avoid conflicts
            $lastId = empty($deviceUsers) ? 1 : max($deviceUserIds) + 1;

            foreach ($systemUser as $user) {
                $employeeCode = $user->employee_code;
                // Extract numeric part from employee_code
                $userId = (int)preg_replace('/[^0-9]/', '', $employeeCode);

                if ($userId <= 0) {
                    Log::warning("Invalid employee code for user: {$user->name}, employee_code: {$employeeCode}");
                    continue; // Skip invalid user IDs
                }

                Log::info("Processing user: {$user->name}, userId: {$userId}");

                // Check if user exists on the device
                $existingDeviceUser = array_filter($deviceUsers, fn($du) => $du['userid'] == (string)$userId);
                $uid = !empty($existingDeviceUser) ? reset($existingDeviceUser)['uid'] : $lastId++;

                Log::info("Processing user: {$uid}");
                try {
                    Log::info("Processing user: {$user->name}, userId: {$userId}, uId: {$uid}, role: {$role}");
                    $zk->setUser($uid, $userId, $user->name, '', $role, 0);
                    Log::info("User " . ($existingDeviceUser ? 'updated' : 'added') . ": {$user->name}, userId: {$userId}, uid: {$uid}");

                } catch (\Exception $e) {
                    Log::error("Failed to set user {$user->name} (userId: {$userId}): {$e->getMessage()}");
                    continue;
                }
            }
            return "Users processed successfully";
        } catch (\Exception $e) {
            Log::error("Error processing users: {$e->getMessage()}");
            return "Error processing users: {$e->getMessage()}";
        }
    }

    public function removeUser($userId)
    {
        $device = Device::where('status','=','working')->first();
        $user = User::select(['id','employee_code'])->where('id', $userId)->first();
        $employeeCode = $user->employee_code;
        // Extract integer part from employee_code (e.g., '00004' from 'EMP-00004')
        $code = (int)preg_replace('/[^0-9]/', '', $employeeCode);
        $zk = new LaravelZkteco($device->ip_address);
        if ($zk->connect()){

            $zk->removeUser($code);
            $zk->removeFingerprint($code,[0,1,2,3,4,5,6,7,8,9]);

//            $zk->disconnect();

            return "user removed with fingerprint";
        }
        else{

            return "Device not connected";
        }

    }


}
