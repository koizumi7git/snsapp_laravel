<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Mode;
use App\Models\Userinfomation;

class ModeController extends Controller
{
    public function getMode(Request $request)
    {
        $uid = $request->input('uid');
        
        $result = Userinfomation::where('auth_id','=',$uid)
                    ->select('mode')
                    ->join('modes','mode','=','type')
                    ->select('type','name','main_color','sub_color','text_color','input_color','border_color')
                    ->get();
        
        return $this->resConversionJson($result);
    }

    public function getTempMode(Request $request)
    {
        $type = $request->input('type');

        $result = Mode::where('type','=',$type)
                    ->select('type','name','main_color','sub_color','text_color','input_color','border_color')
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
