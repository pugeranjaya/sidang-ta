<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Validation\ValidationException;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Illuminate\Support\Facades\Hash;

class Login extends BaseLogin
{
    /**
     * @throws ValidationException
     */
    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();

        // Cari user berdasarkan email
        $user = User::where('email', $data['email'])->first();

        // Jika email tidak ada di database
        if (!$user) {
            throw ValidationException::withMessages([
                'data.email' => 'Email tidak terdaftar.',
            ]);
        }

        // Jika password tidak cocok
        if (!Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'data.password' => 'Password salah.',
            ]);
        }

        // Jika semua OK, biarkan parent menghandle sisa prosesnya (session, rate limiting, dll)
        return parent::authenticate();
    }
}
