<?php

namespace App\Concerns;

use Illuminate\Validation\Rule;

trait StoreValidationRules
{
    /**
     * Get the validation rules for store settings.
     *
     * @param  int|null  $storeId
     * @return array<string, array<int, string>>
     */
    protected function storeRules(?int $storeId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:255'],
            'currency' => ['required', 'string', 'max:10'],
        ];
    }
}
