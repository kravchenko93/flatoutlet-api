<?php

namespace App\Dto;

class DeveloperDataDto
{
    public ?string $developerName;
    public ?ImageDto $logo;
    public ?BannerDto $banner;
    public ?ImageDto $background;
    public ?\DateTime $timerDateTime;
    public ?int $sort;
}
