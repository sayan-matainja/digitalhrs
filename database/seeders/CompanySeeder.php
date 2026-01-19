<?php

namespace Database\Seeders;

use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{

    public function run(): void
    {
        Company::create([
            'name' => "CN Nepal",
            'phone' => "9803022718",
            'email' => "info@cninfotehc.com",
            'owner_name' => "Santosh Maharjan",
            'address' => "Shankhmul",
            'logo' => "",
        ]);
    }
}
