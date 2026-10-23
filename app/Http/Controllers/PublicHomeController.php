<?php

namespace App\Http\Controllers;

use App\Actions\Public\GetPublicHomeData;
use Illuminate\Contracts\View\View;

class PublicHomeController extends Controller
{
    public function __invoke(GetPublicHomeData $action): View
    {
        $data = $action->execute();

        return view('public.home', $data);
    }
}
