<?php

namespace App\Services;

use App\Dto\BannerDto;
use App\Dto\ImageDto;
use App\Models\LastBanner;

class BannerService
{

    private const DEVELOPER_KEY = 'застройщик';
    private const IMAGE = 'image';
    private const COLOR = 'color';
    private const COLOR_TEXT = 'colorText';
    private const HEADER = 'header';
    private const DESCRIPTION = 'description';

    private const COUNT_VIEW = 'countView';
    private const FACT_VIEW = 'factView';

    private const MAP = [
        self::IMAGE => 'картинка',
        self::HEADER => 'заголовок',
        self::DESCRIPTION => 'текст',
        self::COLOR => 'цвет',
        self::COLOR_TEXT => 'цвет текста',
        self::COUNT_VIEW => 'кол-во показов',
        self::FACT_VIEW => 'фактически показов',
    ];

    /**
     * @return BannerDto[]
     */
    private static function getBanners(): array
    {
        $developersByNames = [];
        foreach (FlatService::getDevelopers() as $developer) {
            $developersByNames[$developer->developerName] = $developer;
        }

        $banners = [];
        foreach (GoogleDataService::getBanners() as $key => $bannerData) {
            $bannerDto = new BannerDto();
            $bannerDto->setPosition($key);
            foreach (self::MAP as $keyDto => $keyRow) {
                if (isset($bannerData[$keyRow])) {
                    switch ($keyDto) {
                        case self::COLOR:
                        case self::COLOR_TEXT:
                        case self::HEADER:
                        case self::DESCRIPTION:
                            $bannerDto->{$keyDto} = $bannerData[$keyRow];
                            break;
                        case self::COUNT_VIEW:
                            $bannerDto->setCountView((int) $bannerData[$keyRow]);
                            break;
                        case self::FACT_VIEW:
                            $bannerDto->setFactView((int) $bannerData[$keyRow]);
                            break;
                        case self::IMAGE:
                            $imageDto = new ImageDto();
                            $imageDto->link = $bannerData[$keyRow];
                            $bannerDto->image = $imageDto;
                            break;
                    }
                }
            }
            if (
                isset($developersByNames[$bannerData[self::DEVELOPER_KEY]]) &&
                $bannerDto->getFactView() < $bannerDto->getCountView()
            ) {
                $bannerDto->developer = $developersByNames[$bannerData[self::DEVELOPER_KEY]];
                $banners[] = $bannerDto;
            }
        }

        return $banners;
    }

    public static function getBanner(): ?BannerDto
    {
        $banners = self::getBanners();
        $countBanners = count($banners);

        if (0 === $countBanners) {
            return null;
        }

        $lastBanner = LastBanner::first();
        if (null === $lastBanner) {
            $lastBanner = new LastBanner();
            $lastBanner->number = 0;
            $lastBanner->save();

            return self::getBannerAfterLog($banners[$lastBanner->number]);
        }

        $lastBanner->number++;
        if (!isset($banners[$lastBanner->number])) {
            $lastBanner->number = 0;
        }
        $lastBanner->save();

        return self::getBannerAfterLog($banners[$lastBanner->number]);
    }

    private static function getBannerAfterLog(BannerDto $bannerDto) {
        GoogleDataService::writeBannerFactView(self::MAP[self::FACT_VIEW],  $bannerDto->getPosition());
        return $bannerDto;
    }
}
