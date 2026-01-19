<?php

namespace App\Repositories;


use App\Models\CompanyContentManagement;

class ContentManagementRepository
{

    public function getAllCompanyContentManagementDetail($select=['*'])
    {
        return CompanyContentManagement::select($select)->get();
    }

    public function findCompanyContentById($id,$select=['*'])
    {
        return CompanyContentManagement::select($select)
            ->where('id',$id)
            ->first();
    }

    public function getCompanyActiveContentByContentType($companyId,$contentType,$select=['*'])
    {
        return CompanyContentManagement::select($select)
            ->where('company_id',$companyId)
            ->where('is_active',1)
            ->where('content_type',$contentType)
            ->latest()
            ->first();
    }

    public function getStaticPageContentByContentTypeAndTitleSlug($companyId, $contentType,$titleSlug,$select=['*'])
    {
        return CompanyContentManagement::select($select)
            ->where('company_id',$companyId)
            ->where('title_slug',$titleSlug)
            ->where('is_active',1)
            ->where('content_type',$contentType)
            ->first();
    }

    public function getAllActiveCompanyRules($companyId,$contentType,$select=['*'])
    {
        return CompanyContentManagement::select($select)
            ->where('company_id',$companyId)
            ->where('is_active',1)
            ->where('content_type',$contentType)
            ->get();
    }

    public function findPrivacyPolicyByContentType($contentType,$select=['*'],$with=[])
    {
        return CompanyContentManagement::select($select)
            ->with($with)
            ->where('content_type',$contentType)
            ->first();
    }

    public function store($validatedData)
    {
        return CompanyContentManagement::create($validatedData)->fresh();
    }

    public function delete($companyStaticPageContent)
    {
        return $companyStaticPageContent->delete();
    }

    public function update($companyStaticPageContent,$validatedData)
    {
        return $companyStaticPageContent->update($validatedData);
    }

    public function toggleStatus($id)
    {
        $companyStaticPageContent = $this->findCompanyContentById($id);
        return $companyStaticPageContent->update([
            'is_active' => !$companyStaticPageContent->is_active,
        ]);
    }

}
