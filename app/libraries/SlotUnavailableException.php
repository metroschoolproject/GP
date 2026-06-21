<?php

class SlotUnavailableException extends RuntimeException
{
    /** @var array<int,array> list of unavailable-service entries for the JSON response */
    public array $services;

    public function __construct(array $services, string $message = 'Package services unavailable')
    {
        parent::__construct($message);
        $this->services = $services;
    }
}
