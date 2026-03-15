<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'store_name' => ['required', 'string', 'max:255'],
            'password' => $this->passwordRules(),
        ])->validate();

        return \DB::transaction(function () use ($input) {
            $store = \App\Models\Store::create([
                'name' => $input['store_name'],
                'subscription_status' => 'active', // Default for new signups
            ]);

            $role = \App\Models\Role::where('slug', \App\Models\Role::OWNER)->first();

            return User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'store_id' => $store->id,
                'role_id' => $role?->id,
                'is_active' => true,
            ]);
        });
    }
}
