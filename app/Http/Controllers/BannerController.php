<?php

namespace App\Http\Controllers;

use App\Services\BannerService;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function getBanner(Request $request)
    {
        return response()->json(BannerService::getBanner());
    }

}
