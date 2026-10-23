<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\GetDashboardStats;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(GetDashboardStats $action): View
    {
        $data = $action->execute();

        return view('admin.dashboard', $data);
    }
}
