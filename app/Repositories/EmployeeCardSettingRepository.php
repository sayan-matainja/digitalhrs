<?php

namespace App\Repositories;


use App\Models\IdCardSetting;
use App\Traits\ImageService;
use Illuminate\Support\Facades\DB;

class EmployeeCardSettingRepository
{
    use ImageService;

    public function getAll($select=['*'])
    {
        return IdCardSetting::select($select)->paginate(getRecordPerPage());
    }

    public function find($id,$select=['*'])
    {
        return IdCardSetting::where('id',$id)->select($select)->first();
    }



    public function getDefault()
    {
        return IdCardSetting::where('is_default',1)->first();
    }

    public function store($validatedData)
    {
        if (isset($validatedData['front_logo'])) {
            $validatedData['front_logo'] = $this->storeImage($validatedData['front_logo'], IdCardSetting::UPLOAD_PATH, 500, 500);
        }

        if (isset($validatedData['back_logo'])) {
            $validatedData['back_logo'] = $this->storeImage($validatedData['back_logo'], IdCardSetting::UPLOAD_PATH, 500, 500);
        }

        if (isset($validatedData['signature_image'])) {

            $validatedData['signature_image'] = $this->storeImage($validatedData['signature_image'], IdCardSetting::UPLOAD_PATH, 500, 500);
        }
        return IdCardSetting::create($validatedData)->fresh();
    }

    public function update($cardSetting, $validatedData)
    {
        if (isset($validatedData['front_logo'])) {
            if (isset($cardSetting['front_logo'])) {
                $this->removeImage(IdCardSetting::UPLOAD_PATH, $cardSetting['front_logo']);
            }
            $validatedData['front_logo'] = $this->storeImage($validatedData['front_logo'], IdCardSetting::UPLOAD_PATH, 500, 500);
        }

        if (isset($validatedData['back_logo'])) {
            if (isset($cardSetting['back_logo'])) {
                $this->removeImage(IdCardSetting::UPLOAD_PATH, $cardSetting['back_logo']);
            }
            $validatedData['back_logo'] = $this->storeImage($validatedData['back_logo'], IdCardSetting::UPLOAD_PATH, 500, 500);
        }

        if (isset($validatedData['signature_image'])) {
            if (isset($cardSetting['signature_image'])) {
                $this->removeImage(IdCardSetting::UPLOAD_PATH, $cardSetting['signature_image']);
            }
            $validatedData['signature_image'] = $this->storeImage($validatedData['signature_image'], IdCardSetting::UPLOAD_PATH, 500, 500);
        }

        $cardSetting->update($validatedData);
        return $cardSetting->fresh();
    }

    public function delete($cardSetting)
    {

        if (isset($cardSetting['front_logo'])) {
            $this->removeImage(IdCardSetting::UPLOAD_PATH, $cardSetting['front_logo']);
        }

        if (isset($cardSetting['back_logo'])) {
            $this->removeImage(IdCardSetting::UPLOAD_PATH, $cardSetting['back_logo']);
        }

        if (isset($cardSetting['signature_image'])) {
            $this->removeImage(IdCardSetting::UPLOAD_PATH, $cardSetting['signature_image']);
        }

        return $cardSetting->delete();
    }


    public function setAsDefault($cardSetting): bool
    {

        IdCardSetting::where('is_default', true)->update(['is_default' => false]);
        return $cardSetting->update(['is_default' => true]);

    }

    public function checkTemplateSlug($slug)
    {
        return IdCardSetting::where('slug', $slug)->exists();
    }

    public function toggleStatus($cardSetting):mixed
    {
        return $cardSetting->update([
            'is_active' => !$cardSetting->is_active,
        ]);
    }
}

