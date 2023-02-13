<?php

namespace App\Dto;

class BannerDto
{
    public ?string $link;
    public ?ImageDto $image;
    public ?string $color;
    public ?string $header;
    public ?string $description;
    public ?string $colorText;
    public ?DeveloperDto $developer;
    private ?int $countView = null;
    private ?int $factView = null;
    private ?int $position = null;

    public function setCountView(?int $countView) {
        $this->countView = $countView;
    }

    public function setFactView(?int $factView) {
        $this->factView = $factView;
    }

    public function setPosition(?int $position) {
        $this->position = $position;
    }

    public function getCountView(): ?int {
        return $this->countView;
    }

    public function getFactView(): ?int {
        return $this->factView;
    }

    public function getPosition(): ?int {
        return $this->position;
    }
}
