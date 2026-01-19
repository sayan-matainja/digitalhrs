<?php

namespace Database\Seeders;

use App\Enum\EmployeeAttendanceTypeEnum;
use App\Models\EmployeeCardTemplate;
use App\Models\GeneralSetting;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeCardTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $templates = [
            // 1. Orange Gradient Modern
            [
                'name'            => 'Orange Gradient Modern',
                'slug'            => 'orange-modern',
                'orientation'     => 'portrait',
                'is_default'      => 1,
                'front_background'=> ['type' => 'color', 'value' => '#fb923c'],     // orange-400
                'back_background' => ['type' => 'color', 'value' => '#0f172a'],
                'front_extra_fields' => [
                    ['field' => 'employee_code'],
                    ['field' => 'join_date'],
                    ['field' => 'email'],
                    ['field' => 'phone'],
                    ['field' => 'dob']
                ],
                'back_extra_fields' => [['field' => 'signature']],
                'graph_type'       => 'qr',
                'graph_color'      => '#fb923c',
                'term_conditions'  => "• This card is the property of DigitalHRS\n• Report loss immediately\n• Non-transferable\n• Valid only with signature",
                'front_settings'   => json_encode([
                    'portrait' => [
                        'layout'        => 'orange-modern-simple',
                        'photo_border'  => '#fb923c',
                        'qr_type'       => 'real',
                        'qr_size'       => 140,        // new standardized size
                        'barcode_height'=> 90,
                        'show_barcode'  => false
                    ]
                ])
            ],

            // 2. Blue Circle Professional
            [
                'name'            => 'Blue Circle Professional',
                'slug'            => 'blue-circle-professional',
                'orientation'     => 'portrait',
                'front_background'=> ['type' => 'color', 'value' => '#3b82f6'],     // blue-500
                'back_background' => ['type' => 'color', 'value' => '#0f172a'],
                'front_extra_fields' => [
                    ['field' => 'employee_code'],
                    ['field' => 'join_date'],
                    ['field' => 'email'],
                    ['field' => 'phone'],
                    ['field' => 'dob']
                ],
                'back_extra_fields' => [],
                'graph_type'       => 'barcode',
                'graph_color'      => '#3b82f6',
                'term_conditions'  => "This card must be displayed at all times.\nLost or damaged card must be reported immediately.",
                'front_settings'   => json_encode([
                    'portrait' => [
                        'layout'         => 'modern-centered',
                        'photo_border'   => '#3b82f6',
                        'qr_type'        => 'real',
                        'qr_size'        => 140,
                        'barcode_height' => 90,
                        'show_barcode'   => true
                    ]
                ])
            ],

            // 3. Corporate Blue Landscape – Professional Split Design
            [
                'name'               => 'Modern Executive Landscape',
                'slug'               => 'modern-executive-landscape',
                'orientation'        => 'landscape',
                'front_background'   => ['type' => 'gradient', 'value' => 'linear-gradient(to bottom, #1f2937 60%, #1e40af 40%)'],
                'back_background'    => ['type' => 'color', 'value' => '#f9fafb'],
                'graph_type'         => 'both', // barcode + qr
                'graph_color'        => '#000000',
                'term_conditions'    => "This card is valid only during employment.\nMust be surrendered upon termination.\nFor official use only.",
                'front_extra_fields' => [
                    ['field' => 'phone'],
                    ['field' => 'email'],
                    ['field' => 'website'],
                    ['field' => 'address']
                ],
                'back_extra_fields'  => [],
                'front_settings'     => json_encode([
                    'landscape' => [
                        'layout' => 'modern-executive',
                        'accent_color' => '#3b82f6',
                        'show_barcode' => true,
                        'show_qr' => true
                    ]
                ]),
            ],

            // 4. Cyclone Nepal Premium – Light Blue + Red Accent (Updated)
            [
                'name'               => 'Vibrant Orange Premium',
                'slug'               => 'vibrant-orange-premium',
                'orientation'        => 'landscape',
                'front_background'   => ['type' => 'gradient', 'value' => 'linear-gradient(90deg, #f97316 0%, #fb923c 100%)'],
                'back_background'    => ['type' => 'color', 'value' => '#ffffff'],
                'graph_type'         => 'qr',
                'graph_color'        => '#000000',
                'term_conditions'    => "Valid only with signature.\nReport lost card immediately.",
                'front_extra_fields' => [
                    ['field' => 'employee_code'], ['field' => 'date_of_birth'],
                    ['field' => 'joining_date'],   ['field' => 'expiry_date'],
                    ['field' => 'email'],          ['field' => 'phone']
                ],
                'back_extra_fields'  => [['field' => 'signature']],
                'front_settings'     => json_encode([
                    'landscape' => [
                        'layout' => 'orange-gradient-header',
                        'accent_color' => '#f97316',
                        'show_barcode' => false,
                        'show_qr' => true
                    ]
                ]),
            ],
        ];

        foreach ($templates as $template) {
            EmployeeCardTemplate::updateOrCreate(
                ['slug' => $template['slug']],
                $template
            );
        }

        // Ensure only one default
        EmployeeCardTemplate::where('is_default', true)
            ->where('slug', '!=', 'blue-circle-portrait')
            ->update(['is_default' => false]);
    }


}
