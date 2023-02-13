<?php

namespace App\Services;
use App\Dto\FlatDto;
use App\Models\FavoriteFlat;
use App\Exceptions\JsonException;
class FlatFavoriteService
{
    /**
     * @return FlatDto[]
     */
    public static function getFavorites(string $userId): array
    {
        $flats = [];
        $flatsByFlatId = [];

        foreach (FlatService::getFlats(null, false) as $flat) {
            $flatsByFlatId[$flat->flatId] = $flat;
        }

        $favoriteFlats = FavoriteFlat::where('user_id', $userId)->get();
        foreach ($favoriteFlats as $favoriteFlat) {
            if (isset($flatsByFlatId[$favoriteFlat->flat_id])) {
                $flats[$favoriteFlat->flat_id] = $flatsByFlatId[$favoriteFlat->flat_id];
                $flats[$favoriteFlat->flat_id]->isInFavourites = true;
            }
        }


        return array_values($flats);
    }

    public static function deleteFromFavorites(string $userId, string $flatId): array
    {
        $favoriteFlat = FavoriteFlat::where('user_id', $userId)->where('flat_id', $flatId)->first();
        if (null !== $favoriteFlat) {
            $favoriteFlat->delete();
            return self::getFavorites($userId);
        } else {
            throw new JsonException('not found favorite flat to delete', ['flatId' => $flatId, 'userId' => $userId]);
        }
    }

    public static function addToFavorites(string $userId, string $flatId): array
    {
        $favoriteFlat = FavoriteFlat::where('user_id', $userId)->where('flat_id', $flatId)->first();
        if (null !== $favoriteFlat) {
            throw new JsonException('favorite flat is exist', ['flatId' => $flatId, 'userId' => $userId]);
        }

        foreach (FlatService::getFlats(null, false) as $flat) {
            if ($flat->flatId === $flatId) {
                $favoriteFlat = new FavoriteFlat();
                $favoriteFlat->user_id = $userId;
                $favoriteFlat->flat_id = $flatId;
                $favoriteFlat->save();

                return self::getFavorites($userId);
            }
        }

        throw new JsonException('not found flat', ['flatId' => $flatId]);
    }
}
