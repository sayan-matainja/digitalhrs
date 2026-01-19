<?php

namespace App\Transformers;

use App\Models\TadaAttachment;

class TadaAttachmentTransformer
{
    public $tadaAttachment;

    public function __construct($tadaAttachment)
    {
        $this->tadaAttachment = $tadaAttachment;
    }

    public function transform(): array
    {
        $images = [];
        $files = [];
        foreach($this->tadaAttachment as $key => $value) {
            if(in_array(pathinfo(asset(TadaAttachment::ATTACHMENT_UPLOAD_PATH.$value->attachment), PATHINFO_EXTENSION),['docx','pdf','doc','xls','txt'])){
                $files[] = [
                    'id' => $value->id,
                    'url' => asset(TadaAttachment::ATTACHMENT_UPLOAD_PATH.$value->attachment),
                ];
            }else{
                $images[] = [
                    'id' => $value->id,
                    'url' => asset(TadaAttachment::ATTACHMENT_UPLOAD_PATH.$value->attachment),
                ];
            }
        }
        return [
            'image' => $images,
            'file' => $files,
        ];

    }
}
