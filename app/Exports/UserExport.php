<?php

namespace App\Exports;

use App\Helpers\AppHelper;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class UserExport implements FromView, ShouldAutoSize
{

    protected $users;

    function __construct($users)
    {
        $this->users = $users;
    }



    public function view(): View
    {
        return view('admin.employees.export.user_list', [
            'users' => $this->users,
        ]);
    }
}
