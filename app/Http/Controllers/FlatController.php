<?php

namespace App\Http\Controllers;

use App\Services\FlatService;
use App\Services\FlatFavoriteService;
use Illuminate\Http\Request;
use App\Dto\FlatFilterDto;

class FlatController extends Controller
{
    private static function getFlatFilterDto(Request $request): FlatFilterDto {
        $flatFilterDto = new FlatFilterDto();
        $flatFilterDto->developerNames = $request->json('developerNames') ?? [];
        $flatFilterDto->floors = $request->json('floors') ?? [];
        $flatFilterDto->rooms = $request->json('rooms') ?? [];
        $flatFilterDto->priceFrom = $request->json('priceFrom');
        $flatFilterDto->priceTo = $request->json('priceTo');
        $flatFilterDto->mortgagePaymentFrom = $request->json('mortgagePaymentFrom');
        $flatFilterDto->mortgagePaymentTo = $request->json('mortgagePaymentTo');
        $flatFilterDto->deadlines = $request->json('deadlines') ?? [];
        $flatFilterDto->tags = $request->json('tags') ?? [];
        $flatFilterDto->offerDay = $request->json('offerDay') ?? null;
        $flatFilterDto->goodPrice = $request->json('goodPrice') ?? null;

        return  $flatFilterDto;
    }
    public function getFlats(Request $request)
    {
        $userId = $request->json('userId');

        if (empty($userId)) {
            return response()->json(['error' => 'userId is required'], 400);
        }

        $flatFilterDto = self::getFlatFilterDto($request);

        $favoriteFlatsIds = array_map(function ($flatDto) {
            return $flatDto->flatId;
        }, FlatFavoriteService::getFavorites($userId));

        $flats = FlatService::getFlats($flatFilterDto);
        return response()->json([
            'flats' => array_map(function ($flatDto) use ($favoriteFlatsIds) {
                $flatDto->isInFavourites = in_array($flatDto->flatId, $favoriteFlatsIds);
                return $flatDto;
            }, $flats),
            'meta' =>  FlatService::getFlatsMeta(),
            'metaFiltered' =>  FlatService::getFlatsMeta($flats)
        ]);
    }

    public function getDevelopers(Request $request)
    {
        $flatFilterDto = self::getFlatFilterDto($request);
        return response()->json([
            'developers' => FlatService::getDevelopers($flatFilterDto),
            'meta' =>  FlatService::getDevelopersMeta(),
            'metaFiltered' =>  FlatService::getDevelopersMeta($flatFilterDto)
        ]);
    }

}
