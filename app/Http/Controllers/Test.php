<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Test extends Controller
{
    public function test(Request $request) {
        return response([
            'data' => [
                'key' => '123123'
            ]
        ], 201);
    }
}
