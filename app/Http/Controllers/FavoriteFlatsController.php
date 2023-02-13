<?php

namespace App\Http\Controllers;

use App\Services\FlatFavoriteService;
use Illuminate\Http\Request;

class FavoriteFlatsController extends Controller
{
    public function getFavorites(Request $request)
    {
        return response()->json(FlatFavoriteService::getFavorites($request->json('userId')));
    }

    public function deleteFromFavorites(Request $request)
    {
        return response()->json(FlatFavoriteService::deleteFromFavorites($request->json('userId'), $request->json('flatId')));
    }

    public function addToFavorites(Request $request)
    {
        return response()->json(FlatFavoriteService::addToFavorites($request->json('userId'), $request->json('flatId')));
    }

}
