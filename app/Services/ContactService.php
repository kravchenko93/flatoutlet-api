<?php

namespace App\Services;
class ContactService
{
    public static function addContact(string $phone, string $date, ?string $userId = null, ?string $flatId = null)
    {
        $values = [
            [
                $phone,
                $date,
                $userId,
                $flatId
            ]
        ];
        $body = new \Google_Service_Sheets_ValueRange(array(
            'values' => $values
        ));
        $params = array(
            'valueInputOption' => 'USER_ENTERED'
        );
        GoogleSheetsClient::getService()->spreadsheets_values->append(
            env('SYSTEM_SPREAD_SHEET_ID'),
            'contacts',
            $body,
            $params
        );
    }


}
