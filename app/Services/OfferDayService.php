<?php

namespace App\Services;

use App\Dto\OfferDayDto;
use App\Dto\ImageDto;

class OfferDayService
{

    private const TIMER = 'дата окончания таймера';
    private const IMAGE = 'обложка';



    public static function getData(): OfferDayDto
    {
        $offerDayDto = new OfferDayDto();
        $offerDayData = GoogleDataService::getOfferDayData();

        $imageDto = new ImageDto();
        $imageDto->link = $offerDayData[self::IMAGE] ?? null;


        $offerDayDto->background = $imageDto;
        $offerDayDto->timerDateTime = !empty($offerDayData[self::TIMER]) ? new \DateTime($offerDayData[self::TIMER]) : null;

        return $offerDayDto;
    }
}
