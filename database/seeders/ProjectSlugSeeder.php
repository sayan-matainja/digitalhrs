<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectSlugSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $existingRows = DB::table('projects')->get();

        foreach ($existingRows as $row) {
            DB::table('projects')
                ->where('id', $row->id)
                ->update(['slug' => \Illuminate\Support\Str::uuid()]);
        }

    }
}
