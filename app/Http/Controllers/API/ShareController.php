<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Share;

class ShareController extends Controller
{
    public function share(Request $request)
    {
        $user_id = $request->input('id');
        $share_id = $request->input('share_id');

        if(Share::where('user_id','=',$user_id)->where('share_id','=',$share_id)->exists()){
            Share::where('user_id','=',$user_id)->where('share_id','=',$share_id)->delete();
        }else{
            Share::create(['user_id' => $user_id,'share_id' => $share_id,'share_flag' => 1]);
        }
    }
}
