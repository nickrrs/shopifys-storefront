<?php

namespace App\Contracts;

interface IntegrationClient
{
    public function request(string $query, array $variables = []): array;
}
