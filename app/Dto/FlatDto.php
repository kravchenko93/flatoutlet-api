<?php

namespace App\Dto;

class FlatDto
{
    public ?string $flatId;
    public ?DeveloperDataDto $developer;
    public ?int $price;
    public ?int $priceTomorrow;

    public ?int $rooms;
    public ?string $residentialComplex;
    public ?int $mortgagePayment;
    public ?int $floor;
    /**
     * @var ImageDto[]
     */
    public array $images;
    public ?int $floorCount;
    public ?float $area;
    public ?string $housing;
    public ?string $housingTotal;
    public ?string $section;
    public ?string $developerPhone;
    public ?MetroDto $metro;
    public ?string $deadline;
    public ?string $deadlineQuarter;
    public ?string $code;
    public ?bool $offerDay;
    public bool $goodPrice = false;
    public ?int $salePercent;
    public ?int $countFavorites;

    /**
     * @var string[]
     */
    public array $tags;
    public bool $isDeleted;
}
