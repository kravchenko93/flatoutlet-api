<?php

namespace App\Dto;

class FlatMetaDto
{
    /**
     * @var DeveloperMetaDto[]
     */
    public array $developers;
    /**
     * @var int[]
     */
    public array $floors;

    /**
     * @var int[]
     */
    public array $rooms;

    /**
     * @var string[]
     */
    public array $deadlines;

    public ?int $priceFrom;
    public ?int $priceTo;
    public ?int $mortgagePaymentFrom;
    public ?int $mortgagePaymentTo;
    /**
     * @var string[]
     */
    public array $tags;
}
