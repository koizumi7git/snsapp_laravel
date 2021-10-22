<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Reply;
use App\Models\Post;

class ReplyController extends Controller
{
    public function reply(Request $request)
    {
        $user_id = $request->input('user_id');
        $replied_id = $request->input('replied_id');
        $reply_message = $request->input('reply_message');
        $first_medhia_url = $request->input('first_medhia_url');
        $second_medhia_url = $request->input('second_medhia_url');
        $first_medhia_name = $request->input('first_medhia_name');
        $second_medhia_name = $request->input('second_medhia_name');
        $thumbnail_url = $request->input("thumbnail_url");
        $file_token = $request->input('file_token');

        $post = Post::create(["user_id" => $user_id, "text" => $reply_message, "first_medhia_url" => $first_medhia_url,"second_medhia_url" => $second_medhia_url,"first_medhia_name" => $first_medhia_name,"second_medhia_name"=>$second_medhia_name,"thumbnail_url" =>$thumbnail_url,"file_token"=>$file_token]);

        Reply::create(['replying_id' => $post['id'], "replied_id" => $replied_id, "reply_flag" => 1]);

        //return $this->resConversionJson($result);
    }

    private function resConversionJson($result, $statusCode=200)
    {
        if(empty($statusCode) || $statusCode < 100 || $statusCode >= 600){
            $statusCode = 500;
        }
        return response()->json($result, $statusCode, ['Content-Type' => 'application/json'],JSON_UNESCAPED_SLASHES);
    }
}
