<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ApiResponse;
use Illuminate\Http\Request;

class MainController extends Controller
{
    use ApiResponse;

    public function changeLanguage($locale)
    {
        session()->put('locale', $locale);

        return redirect()->back();
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function darkMode(Request $request)
    {
        $darkMode = $request->dark_mode ? 'true' : 'false';

        return $this->apiResponse(200, 'theme_mode', null, [
            'dark_mode' => $darkMode
        ])
            ->cookie('dark_mode', $darkMode, 525600);
    }
}
