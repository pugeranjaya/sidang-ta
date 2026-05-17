<?php

namespace App\Filament\Pages;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    /**
     * Redirect to the dashboard after the profile is successfully updated.
     */
    protected function getRedirectUrl(): ?string
    {
        return filament()->getUrl();
    }

    protected function getEmailFormComponent(): \Filament\Forms\Components\TextInput
    {
        return parent::getEmailFormComponent()
            ->rule(function () {
                return function (string $attribute, $value, \Closure $fail) {
                    if (!str_ends_with($value, '@upnyk.ac.id')) {
                        $fail('Email wajib menggunakan format @upnyk.ac.id');
                    }
                };
            });
    }
}
