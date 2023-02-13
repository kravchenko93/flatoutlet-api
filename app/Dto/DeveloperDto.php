<?php

namespace App\Dto;

class DeveloperDto
{
    public ?string $developerName;
    public ?ImageDto $logo;
    public ?BannerDto $banner;
    public ?ImageDto $background;
    public ?int $priceFrom;
    public ?\DateTime $timerDateTime;
    public ?int $flatCount;
    public ?int $maxSalePercent;

    /**
     * @var string[]
     */
    public array $tags;
}
