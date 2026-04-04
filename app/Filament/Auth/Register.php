<?php

namespace App\Filament\Auth;

use App\Models\User;
use Filament\Auth\Pages\Register as BaseRegister;
use Illuminate\Database\Eloquent\Model;

class Register extends BaseRegister
{
    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRegistration(array $data): Model
    {
        $user = $this->getUserModel()::create($data);

        if ($user instanceof User && ! $user->hasRole('panel_user')) {
            $user->assignRole('panel_user');
        }

        return $user;
    }
}
