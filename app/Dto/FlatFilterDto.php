<?php

namespace App\Dto;

class FlatFilterDto
{
    /**
     * @var string[]
     */
    public array $developerNames;
    /**
     * @var int[]
     */
    public array $floors;
    /**
     * @var int[]
     */
    public array $rooms;
    public ?int $priceFrom;
    public ?int $priceTo;
    public ?int $mortgagePaymentFrom;
    public ?int $mortgagePaymentTo;
    /**
     * @var string[]
     */
    public array $deadlines;
    public ?bool $offerDay;
    public ?bool $goodPrice;
    /**
     * @var string[]
     */
    public array $tags;
}
