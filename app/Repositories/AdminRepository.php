<?php

namespace App\Repositories;

use App\Models\Admin;
use App\Traits\ImageService;

class AdminRepository
{
    const IS_ACTIVE = 1;

    use ImageService;

    public function getAll($select = ['*'])
    {
        return Admin::select($select)->orderBy('admins.name')
            ->latest()
            ->paginate( getRecordPerPage());
    }





    public function store($validatedData)
    {
        if(isset($validatedData['avatar'])){
            $validatedData['avatar'] = $this->storeImage($validatedData['avatar'], Admin::AVATAR_UPLOAD_PATH, 500, 500);
        }
        return Admin::create($validatedData)->fresh();
    }

    public function changePassword($userDetail, $newPassword)
    {
        return $userDetail->update([
            'password' => bcrypt($newPassword)
        ]);
    }

    public function delete($userDetail)
    {
        if ($userDetail['avatar']) {
            $this->removeImage(Admin::AVATAR_UPLOAD_PATH, $userDetail['avatar']);
        }

        $updateData = [
            'email'=>uniqid().$userDetail->email,
            'username'=>uniqid().$userDetail->username,
        ];
        $this->update($userDetail, $updateData);

        return $userDetail->delete();
    }

    public function update($userDetail, $validatedData)
    {
        if (isset($validatedData['avatar'])) {
            if ($userDetail['avatar']) {
                $this->removeImage(Admin::AVATAR_UPLOAD_PATH, $userDetail['avatar']);
            }
            $validatedData['avatar'] = $this->storeImage($validatedData['avatar'], Admin::AVATAR_UPLOAD_PATH, 500, 500);
        }
        return $userDetail->update($validatedData);
    }

    public function toggleStatus($userDetail)
    {

        return $userDetail->update([
            'is_active' => !$userDetail->is_active,
        ]);
    }

    public function find($id, $select = ['*'], $with = [])
    {
        return Admin::with($with)->select($select)->where('id', $id)->first();
    }



    public function getAdminByAdminName($userName, $select = ['*'])
    {
        return Admin::select($select)
            ->where('username', $userName)
            ->where('is_active', self::IS_ACTIVE)
            ->first();
    }

    public function getAdminByAdminEmail($userEmail, $select = ['*'])
    {
        return Admin::select($select)
            ->where('email', $userEmail)
            ->where('is_active', self::IS_ACTIVE)
            ->first();
    }
}
