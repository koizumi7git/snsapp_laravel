<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Follow;
use App\Models\Userinfomation;

class FollowController extends Controller
{
    public function signupFollow(Request $request)
    {
        $user_no = $request->input("id");

        Follow::create(['following_id' => $user_no, "followed_id" => $user_no]);
    }

    public function followList(Request $request)
    {
        $user_id = $request->input("id");
        //$user_id = 1;

        $following_count = count(Follow::where('following_id',$user_id)->where('followed_id','<>',$user_id)->get());

        $followed_count = count(Follow::where('followed_id', $user_id)->where('following_id','<>',$user_id)->get());

        $subFollowSql= DB::table('follows')->toSql();

        $following_list = Follow::where('follows.following_id', $user_id)
                                    ->join('userinfomations','follows.followed_id','=','userinfomations.id')
                                    ->where('follows.followed_id','<>',$user_id)
                                    ->select('userinfomations.id','user_id','user_name','userimage_url','self_introduction','follows.created_at','follows.following_id')
                                    ->leftJoinSub($subFollowSql,'Follow',function($query) use($user_id){
                                            $query->on('userinfomations.id','=','Follow.following_id')
                                                    ->where('Follow.followed_id','=',$user_id);
                                    })
                                    ->select('userinfomations.id as user_no','user_id','user_name','userimage_url','self_introduction','follows.created_at','follows.following_id as following_flag','Follow.followed_id as followed_flag')
                                    ->orderBy('follows.created_at','desc')
                                    ->get();

        $followed_list = Follow::where('follows.followed_id',$user_id)
                                    ->join('userinfomations','follows.following_id','=','userinfomations.id')
                                    ->where('follows.following_id','<>',$user_id)
                                    ->select('userinfomations.id as user_no','user_id','user_name','userimage_url','self_introduction','follows.created_at','follows.followed_id')
                                    ->leftJoinSub($subFollowSql,'Follow',function($query) use($user_id){
                                        $query->on('userinfomations.id','=','Follow.followed_id')
                                                ->where('Follow.following_id','=',$user_id);
                                    })
                                    ->select('userinfomations.id as user_no','user_id','user_name','userimage_url','self_introduction','follows.created_at','follows.followed_id as followed_flag','Follow.following_id as following_flag')
                                    ->orderBy('follows.created_at','desc')
                                    ->get();


        $result = array('user_id' => $user_id,'following_count' => $following_count , 'followed_count' => $followed_count,'following_list' => $following_list, 'followed_list' => $followed_list);
                                    
        return $this->resConversionJson($result);                           
    }

    public function followListDetails(Request $request)
    {
        $user_id = $request->input("id");
        $my_no = $request->input("myNo");

        //$user_id = 8;
        //$my_no = 1;

        $following_count = count(Follow::where('following_id',$user_id)->where('followed_id','<>',$user_id)->get());

        $followed_count = count(Follow::where('followed_id', $user_id)->where('following_id','<>',$user_id)->get());

        $subFollowSql= DB::table('follows')->toSql();

        $following_list = Follow::where('follows.following_id', $user_id)
                                    ->join('userinfomations','follows.followed_id','=','userinfomations.id')
                                    ->where('follows.followed_id','<>',$user_id)
                                    ->select('userinfomations.id','user_id','user_name','userimage_url','self_introduction','follows.created_at')
                                    ->leftJoinSub($subFollowSql,'Following',function($query) use($my_no){
                                        $query->on('userinfomations.id','=','Following.followed_id')
                                                ->where('Following.following_id','=',$my_no);
                                    })
                                    ->select('userinfomations.id as user_no','user_id','user_name','userimage_url','self_introduction','follows.created_at','Following.following_id as following_flag')
                                    ->leftJoinSub($subFollowSql,'Followed',function($query) use($my_no){
                                        $query->on('userinfomations.id','=','Followed.following_id')
                                                ->where('Followed.followed_id','=',$my_no);
                                    })
                                    ->select('userinfomations.id as user_no','user_id','user_name','userimage_url','self_introduction','follows.created_at','Following.following_id as following_flag','Followed.followed_id as followed_flag')
                                    ->orderBy('follows.created_at','desc')
                                    ->get();

        $followed_list = Follow::where('follows.followed_id',$user_id)
                                    ->join('userinfomations','follows.following_id','=','userinfomations.id')
                                    ->where('follows.following_id','<>',$user_id)
                                    ->select('userinfomations.id as user_no','user_id','user_name','userimage_url','self_introduction','follows.created_at','follows.followed_id')
                                    ->leftJoinSub($subFollowSql,'Following',function($query) use($my_no){
                                        $query->on('userinfomations.id','=','Following.followed_id')
                                                ->where('Following.following_id','=',$my_no);
                                    })
                                    ->select('userinfomations.id as user_no','user_id','user_name','userimage_url','self_introduction','follows.created_at','Following.following_id as following_flag')
                                    ->leftJoinSub($subFollowSql,'Followed',function($query) use($my_no){
                                        $query->on('userinfomations.id','=','Followed.following_id')
                                                ->where('Followed.followed_id','=',$my_no);
                                    })
                                    ->select('userinfomations.id as user_no','user_id','user_name','userimage_url','self_introduction','follows.created_at','Following.following_id as following_flag','Followed.followed_id as followed_flag')
                                    ->orderBy('follows.created_at','desc')
                                    ->get();



        $result = array('user_id' => $user_id,'following_count' => $following_count , 'followed_count' => $followed_count,'following_list' => $following_list, 'followed_list' => $followed_list);
                                    
        return $this->resConversionJson($result);  

    }

    public function addFollow(Request $request)
    {
        $following_id = $request->input('myId');
        $followed_id = $request->input('id');

        Follow::create(['following_id' => $following_id,'followed_id' => $followed_id]);
    }

    public function removeFollow(Request $request)
    {
        $following_id = $request->input('myId');
        $followed_id = $request->input('id');

        Follow::where('following_id','=', $following_id)
                ->where('followed_id','=', $followed_id)
                ->delete();
    }

    public function followFlag(Request $request)
    {
        $following_id = $request->input('followingId');
        $followed_id = $request->input('followedId');

        $result = Follow::where('following_id','=',$following_id)
                                ->where('followed_id','=',$followed_id)
                                ->select('following_id','followed_id')
                                ->get();
        
        return $this->resConversionJson($result); 

    }

    public function getFollowerUid(Request $request)
    {
        $user_no = $request->input('id');

        $result = Follow::where('followed_id','=',$user_no )
                    ->where('following_id','<>',$user_no)
                    ->select('following_id')
                    ->join('userinfomations','follows.following_id','=','userinfomations.id')
                    ->select('auth_id')
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
