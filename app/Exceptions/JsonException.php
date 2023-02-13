<?php

namespace App\Exceptions;

class JsonException extends \Exception
{
    public array $data = [];
    public function __construct($message, $data = []) {
        $this->data = $data;
        parent::__construct($message, 0, null);
    }
}
