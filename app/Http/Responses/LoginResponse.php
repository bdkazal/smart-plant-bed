<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        if ($user && $user->devices()->exists()) {
            return redirect()->route('devices.index');
        }

        return redirect()->route('devices.add');
    }
}
