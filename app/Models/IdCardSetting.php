<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdCardSetting extends Model
{
    use HasFactory;

    protected $table = 'id_card_settings';
    const UPLOAD_PATH = 'uploads/card/';

    protected $casts = [
        'extra_fields_order' => 'array',
    ];

    protected $fillable = [
        'title', // title of card
        'slug', // unique slugified title
        'orientation', //  landscape (85.6 × 54 mm)  ,portrait  (54 × 85.6 mm)
        'extra_fields_order', //as in form
        'graph_type', // 'qr' => 'QR Code', 'barcode' => 'Barcode', 'none' => 'None'  any one among these three
        'graph_content', // what the graph data contains (give select options : employee_code, company info, etc)
        'back_title', // title like terms and conditions or any other
        'back_text', // content for back_title
        'front_logo', // logo to be used in front
        'back_logo', // logo to be used in back
        'signature_image', // signature of authorized personnel to be used in back (not compulsory)
        'footer_text', // either company name, website address or any other in back footer
        'background_color', // background color of card both front and back
        'is_active',
        'is_default',
        'text_color',
        'graph_color'];
}
