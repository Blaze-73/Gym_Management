<?php

namespace App\Http\Controllers\Concerns;

trait ValidatesCheckoutCustomer
{
    protected function checkoutCustomerRules(): array
    {
        return [
            'customer_name' => 'required|string|min:2|max:120',
            'customer_email' => 'required|email|max:150',
            'customer_phone' => 'required|string|min:6|max:30',
            'customer_address' => 'required|string|min:5|max:500',
        ];
    }

    protected function normalizeCustomerInfo(array $data): array
    {
        return [
            'customer_name' => trim($data['customer_name']),
            'customer_email' => trim($data['customer_email']),
            'customer_phone' => trim($data['customer_phone']),
            'customer_address' => trim($data['customer_address']),
        ];
    }
}
