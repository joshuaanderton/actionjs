<?php

namespace Actionjs\Actionjs\Actions\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Logout
{
    public function __invoke(Request $request)
    {
        Auth::logout();

        return [];
    }
}
