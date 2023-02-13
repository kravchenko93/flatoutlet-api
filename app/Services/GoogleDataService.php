<?php

namespace App\Services;

use App\Models\GoogleData;

class GoogleDataService
{
    private const FLAT_LIST_PREFIX = 'flats.';
    public static function write(): GoogleData
    {
        $flatsList = [];
        foreach (GoogleSheetsClient::getLists(env('SYSTEM_SPREAD_SHEET_ID')) as $list) {
            if (str_starts_with($list, self::FLAT_LIST_PREFIX)) {
                $flatsList[] = $list;
            }
        }
        $flatRows = [];

        foreach ($flatsList as $flatList) {
            $response = GoogleSheetsClient::getService()->spreadsheets_values->get(env('SYSTEM_SPREAD_SHEET_ID'), $flatList);
            $rows = $response->getValues();
            // Remove the first one that contains headers
            $headers = array_shift($rows);
            // Combine the headers with each following row

            foreach ($rows as $row) {
                $diffCount = count($headers) - count($row);
                if ($diffCount) {
                    for ($i = 1; $i <= $diffCount; $i++) {
                        $row[] = null;
                    }
                }
                if (count($row) > count($headers)) {
                    $row = array_slice($row, 0, count($headers));
                }
                $flatRows[] = array_combine($headers, $row);
            }

        }

        $response = GoogleSheetsClient::getService()->spreadsheets_values->get(env('SYSTEM_SPREAD_SHEET_ID'), 'developers');
        $rows = $response->getValues();
        // Remove the first one that contains headers
        $headers = array_shift($rows);
        // Combine the headers with each following row
        $developersRows = [];
        foreach ($rows as $row) {
            $diffCount = count($headers) - count($row);
            if ($diffCount) {
                for ($i = 1; $i <= $diffCount; $i++) {
                    $row[] = null;
                }
            }
            if (count($row) > count($headers)) {
                $row = array_slice($row, 0, count($headers));
            }
            $developersRows[] = array_combine($headers, $row);
        }

        $bannersRows = self::getBannerRows();

        $response = GoogleSheetsClient::getService()->spreadsheets_values->get(env('SYSTEM_SPREAD_SHEET_ID'), 'offerDay');
        $rows = $response->getValues();
        $offerDay = [];
        foreach ($rows as $row) {
            if (isset($row[0]) && isset($row[1])) {
                $offerDay[$row[0]] = $row[1];
            }
        }


        $googleData = GoogleData::first();
        if (null === $googleData) {
            $googleData = new GoogleData();
        } else {
            foreach (json_decode($googleData->flats, true) as $oldFlat) {
                $oldFlatIsDeleted = true;
                foreach ($flatRows as $newFlat) {
                    if (FlatService::getFlatIdByFlatData($newFlat) === FlatService::getFlatIdByFlatData($oldFlat)) {
                        $oldFlatIsDeleted = false;
                        break;
                    }
                }

                if (true === $oldFlatIsDeleted) {
                    $oldFlat['deleted'] = true;
                    $flatRows[] = $oldFlat;
                }
            }
        }

        $googleData->flats = json_encode($flatRows);
        $googleData->banners = json_encode($bannersRows);
        $googleData->developers = json_encode($developersRows);
        $googleData->offerDay = json_encode($offerDay);

        $googleData->save();

        return $googleData;
    }

    public static function getFlats(): array
    {
        $googleData = GoogleData::first();
        if (null === $googleData) {
            $googleData = self::write();
        }

        return json_decode($googleData->flats, true);
    }

    public static function getBanners(): array
    {
        $googleData = GoogleData::first();
        if (null === $googleData) {
            $googleData = self::write();
        }

        return json_decode($googleData->banners, true);
    }

    private static function getBannerRows() {
        $response = GoogleSheetsClient::getService()->spreadsheets_values->get(env('SYSTEM_SPREAD_SHEET_ID'), 'banners');
        $rows = $response->getValues();
        // Remove the first one that contains headers
        $headers = array_shift($rows);
        // Combine the headers with each following row
        $bannersRows = [];
        foreach ($rows as $row) {
            $diffCount = count($headers) - count($row);
            if ($diffCount) {
                for ($i = 1; $i <= $diffCount; $i++) {
                    $row[] = null;
                }
            }
            if (count($row) > count($headers)) {
                $row = array_slice($row, 0, count($headers));
            }
            $bannersRows[] = array_combine($headers, $row);
        }

        return $bannersRows;
    }

    public static function writeBannerFactView(string $rowKey, int $position)
    {
        $bannersRows = self::getBannerRows();

        $searchRow = $bannersRows[$position];
        $value = $searchRow[$rowKey] ?? 0;
        $value++;

        $body = new \Google_Service_Sheets_ValueRange();
        $body->setValues([[$value]]);

        $params = [
            'valueInputOption' => 'USER_ENTERED'
        ];
        GoogleSheetsClient::getService()->spreadsheets_values->update(env('SYSTEM_SPREAD_SHEET_ID'), 'banners!H' . ($position + 2), $body, $params);


        //обновляем в бд
        $googleData = GoogleData::first();
        $googleData->banners = json_encode(self::getBannerRows());

        $googleData->save();
    }

    public static function getOfferDayData(): array
    {
        $googleData = GoogleData::first();
        if (null === $googleData) {
            $googleData = self::write();
        }

        return json_decode($googleData->offerDay, true);
    }

    public static function getDevelopers(): array
    {
        $googleData = GoogleData::first();
        if (null === $googleData) {
            $googleData = self::write();
        }

        return json_decode($googleData->developers, true);
    }
}
