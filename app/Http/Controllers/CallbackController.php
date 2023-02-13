<?php

namespace App\Http\Controllers;

use App\Services\ContactService;
use Illuminate\Http\Request;

class CallbackController extends Controller
{
    public function callback(Request $request)
    {
        $phone = $request->json('phone');
        $flatId = $request->json('flatId');
        $userId = $request->json('userId');
        $date = date('Y-m-d H:i:s');

        if (empty($phone)) {
            return response()->json(['status' => 'ERROR'], 400);
        }

        ContactService::addContact($phone, $date, $userId, $flatId);

        return response()->json(['status' => 'OK']);
    }

}
