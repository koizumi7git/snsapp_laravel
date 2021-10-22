<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Color;
use App\Models\Userinfomation;

class ColorController extends Controller
{
    public function getColor(Request $request)
    {
        $uid = $request->input('uid');

        $result = Userinfomation::where('auth_id','=',$uid)
                            ->select('color')
                            ->join('colors','color','=','type')
                            ->select('type','value')
                            ->get();

        return $this->resConversionJson($result);
    }

    public function getTempColor(Request $request)
    {
        $type = $request->input('type');

        $result = Color::where('type','=',$type)
                    ->select('type','value')
                    ->get();
        
        return $this->resConversionJson($result);
    }

    private function resConversionJson($result, $statusCode=200)
    {
        if(empty($statusCode) || $statusCode < 100 || $statusCode >= 600){
            $statusCode = 500;
        }
        return response()->json($result, $statusCode, ['Content-Type' => 'application/json'],JSON_UNESCAPED_SLASHES);
    }
}
