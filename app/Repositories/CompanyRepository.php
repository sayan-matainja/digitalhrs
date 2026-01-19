<?php

namespace App\Repositories;

use App\Models\Company;
use App\Traits\ImageService;

class CompanyRepository
{
    use ImageService;

    public function findOrFailCompanyDetailById($id,$select=['*'], $with=[])
    {
        return Company::with($with)
            ->select($select)
            ->where('id',$id)
            ->firstOrFail();
    }

    public function getCompanyDetail($select=['*'],$with=[])
    {
        return Company::with($with)->select($select)->first();
    }


    public function store($validatedData)
    {
        $validatedData['logo'] = $this->storeImage($validatedData['logo'],Company::UPLOAD_PATH);

        return Company::create($validatedData)->fresh();
    }

    public function update($companyDetail, $validatedData)
    {
        if(isset($validatedData['logo'])){
            $this->removeImage(Company::UPLOAD_PATH, $companyDetail['logo']);
            $validatedData['logo'] = $this->storeImage($validatedData['logo'],Company::UPLOAD_PATH);
        }
        return $companyDetail->update($validatedData);
    }

}
