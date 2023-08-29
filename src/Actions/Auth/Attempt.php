<?php

namespace Actionjs\Actionjs\Actions\Auth;

use Actionjs\Actionjs\Action;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

class Attempt extends Action
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email:dns,rfc,spoof,filter|max:255|exists:users,email',
            'password' => ['required', Rules\Password::defaults()],
        ]);

        if ($user = Auth::user()) {
            Auth::logout();
        }

        Auth::attempt(
            $request->only([
                'email',
                'password',
            ])
        );

        $keyName = $user->getKeyName();

        return ['user' => $user->$keyName];
    }
}
