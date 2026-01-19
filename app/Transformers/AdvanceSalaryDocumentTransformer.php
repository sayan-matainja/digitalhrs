<?php

namespace App\Transformers;

use App\Models\AdvanceSalaryAttachment;

class AdvanceSalaryDocumentTransformer
{
    public $advanceSalaryDocument;

    public function __construct($advanceSalaryDocument)
    {
        $this->advanceSalaryDocument = $advanceSalaryDocument;
    }

    public function transform(): array
    {
        $images = [];
        $files = [];
        foreach($this->advanceSalaryDocument as $key => $value) {
            if(in_array(pathinfo(asset(AdvanceSalaryAttachment::UPLOAD_PATH.$value->name), PATHINFO_EXTENSION),['docx','pdf','doc','xls','txt'])){
                $files[] = [
                    'id' => $value->id,
                    'url' => asset(AdvanceSalaryAttachment::UPLOAD_PATH.$value->name),
                ];
            }else{
                $images[] = [
                    'id' => $value->id,
                    'url' => asset(AdvanceSalaryAttachment::UPLOAD_PATH.$value->name),
                ];
            }
        }
        return [
            'image' => $images,
            'file' => $files,
        ];

    }
}
