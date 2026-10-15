<?php

namespace App\Services\Payments;

final readonly class Checkout
{
    public function __construct(public string $reference, public string $url) {}
}
