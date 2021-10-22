<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Message;
use App\Models\Messagelist;
use App\Models\Userinfomation;
use App\Models\Follow;

class MessageController extends Controller
{
    public function addMessage(Request $request)
    {
        $user_no = $request->input('id');
        $message = $request->input('message');
        $first_medhia_url = $request->input('first_medhia_url');
        $first_medhia_name = $request->input('first_medhia_name');
        $second_medhia_url = $request->input('second_medhia_url');
        $second_medhia_name = $request->input('second_medhia_name');
        $message_id = $request->input('message_id');
        $file_token = $request->input('file_token');
        $thumbnail_url = $request->input('thumbnail_url');

        Message::create(["user_no" => $user_no, "message" => $message, "message_id" => $message_id, "first_medhia_url" => $first_medhia_url, "first_medhia_name" => $first_medhia_name, "second_medhia_url" => $second_medhia_url, "second_medhia_name" => $second_medhia_name, "file_token" => $file_token, "thumbnail_url" => $thumbnail_url]);
        
    }

    public function getMessage(Request $request)
    {
        $message_id = $request->input('message_id');
        $user_no = $request->input('opponent_userno');
        //$message_id = 1;
        //$user_no = 2;

        $message = Message::where('message_id','=',$message_id)
                            ->select('message_id','user_no','message','first_medhia_url','first_medhia_name',"second_medhia_url","second_medhia_name","file_token","thumbnail_url",'created_at')
                            ->get();

        $user = Userinfomation::where('id','=',$user_no)
                            ->select('id','user_id','user_name','userimage_url')
                            ->get();

        $result = array('user' => $user,'message' => $message);

        return $this->resConversionJson($result);
    }

    public function getMessageList(Request $request)
    {
        $user_no = $request->input('id');

        //$user_no = 1;

        $one_user = DB::table('messagelists')
                                ->where('one_user','=',$user_no)
                                ->select('id','two_user as opponent_user');

        $user_list = DB::table('messagelists')
                                ->where('two_user','=',$user_no)
                                ->select('id','one_user as opponent_user')
                                ->union($one_user)
                                ->select('id','one_user as opponent_user');
        
        $subquery = DB::table('messages')
                        ->select('message_id',DB::raw('MAX(created_at) as latest_created_at'))
                        ->groupBy('message_id');

        $result = DB::table('messages')
                            ->joinSub($subquery,'sub',function($joinSub){
                                $joinSub->on('messages.message_id','=','sub.message_id')
                                    ->on('messages.created_at','=','sub.latest_created_at');
                            })
                            ->select('user_no','message','messages.message_id','first_medhia_url','first_medhia_name',"second_medhia_url","second_medhia_name","file_token","thumbnail_url",'latest_created_at')
                            ->rightjoinSub($user_list,'user',function($rightjoinsub){
                                $rightjoinsub->on('messages.message_id','=','user.id');
                            })
                            ->select('message','user.id as message_id','first_medhia_url','first_medhia_name',"second_medhia_url","second_medhia_name","file_token","thumbnail_url",'latest_created_at','opponent_user')
                            ->join('userinfomations','opponent_user','=','userinfomations.id')
                            ->select('message','user.id as message_id','first_medhia_url','first_medhia_name',"second_medhia_url","second_medhia_name","file_token","thumbnail_url",'latest_created_at as created_at','opponent_user','user_id','user_name','userimage_url')
                            ->orderBy('created_at','desc')
                            ->get();

        return $this->resConversionJson($result);

    }

    public function addMessageList(Request $request)
    {
        $user = $request->input('id');
        $opponent_user = $request->input('opponent_id');
        //$user = 1;
        //$opponent_user = 2;

        if( (Messagelist::where('one_user','=',$user)->exists() && Messagelist::where('two_user','=',$opponent_user)->exists()) || (Messagelist::where('two_user','=',$user)->exists() && Messagelist::where('one_user','=',$opponent_user)->exists())){
            return;
        }else{
            
            Messagelist::create(["one_user" => $user,"two_user" => $opponent_user]);

        }

    }

    public function getMessageFollowList(Request $request)
    {
        $user_no = $request->input('id');
        //$user_no = 1;

        $one_user = DB::table('messagelists')
                                ->where('one_user','=',$user_no)
                                ->select('id','two_user as opponent_user');

        $user_list = DB::table('messagelists')
                                ->where('two_user','=',$user_no)
                                ->select('id','one_user as opponent_user')
                                ->union($one_user)
                                ->select('id','one_user as opponent_user');

        $follow_list = DB::table('follows')
                                ->where('following_id','=',$user_no)
                                ->where('followed_id','<>',$user_no)
                                ->select('followed_id','created_at')
                                ->leftJoinSub($user_list,'user',function($leftjoin){
                                    $leftjoin->on('followed_id','=','user.opponent_user');
                                })
                                ->whereNull('opponent_user')
                                ->select('followed_id','follows.created_at')
                                ->join('userinfomations','followed_id','=','userinfomations.id')
                                ->select('followed_id','user_id','user_name','userimage_url','follows.created_at')
                                ->orderBy('follows.created_at','desc')
                                ->get();

        return $this->resConversionJson($follow_list);
    }

    private function resConversionJson($result, $statusCode=200)
    {
        if(empty($statusCode) || $statusCode < 100 || $statusCode >= 600){
            $statusCode = 500;
        }
        return response()->json($result, $statusCode, ['Content-Type' => 'application/json'],JSON_UNESCAPED_SLASHES);
    }
}