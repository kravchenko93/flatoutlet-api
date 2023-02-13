<?php

namespace App\Http\Controllers;

use App\Services\OfferDayService;
use Illuminate\Http\Request;

class OfferDayController extends Controller
{
    public function getOfferDay(Request $request)
    {
        return response()->json(OfferDayService::getData());
    }

}
