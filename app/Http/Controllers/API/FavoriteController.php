<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Favorite;
use App\Models\Reply;
use App\Models\Share;

class FavoriteController extends Controller
{
    public function favorite(Request $request)
    {
        $user_id = $request->input('user_id');
        $post_id = $request->input('post_id');

        $exist = Favorite::where('post_id',$post_id)
                            ->where('user_id',$user_id)
                            ->get();

        if( count($exist) > 0 ){
            Favorite::where('post_id',$post_id)
                        ->where('user_id',$user_id)
                        ->delete();
        }else{
            Favorite::create(['user_id'=> $user_id,'post_id'=> $post_id,'fav_flag' => 1]);
        }
    }

    public function favoriteList(Request $request)
    {
        $user_id = $request->input('user_id');
        //$user_id = 1;

        $favorite_count = DB::table('favorites')
                            ->select(DB::raw('post_id, COUNT(post_id) AS favorite_count'))
                            ->groupBy('post_id');

        $reply_count = DB::table('replies')
                            ->select(DB::raw('replied_id, COUNT(replied_id) AS reply_count'))
                            ->groupBy('replied_id');

        $reply_flag = Reply::join('posts','replying_id','=','posts.id')
                            ->select('replied_id','reply_flag','posts.user_id')
                            ->distinct();
                            
        $reply_id = Reply::join('posts','replied_id','=','posts.id')
                        ->select('replying_id','posts.user_id')
                        ->join('userinfomations as temp','posts.user_id','=','temp.id')
                        ->select(DB::raw('replying_id ,replied_id as replied_postid, temp.user_id as reply_id,temp.delete_user_flag'))
                        ->where('temp.delete_user_flag','<>',1)
                        ->select(DB::raw('replying_id ,replied_id as replied_postid, temp.user_id as reply_id'));

        $share_count = Share::select(DB::raw('share_id ,COUNT(share_id) AS share_count'))
                        ->groupBy('share_id');
        
        $result = Favorite::where('favorites.user_id',$user_id)
                            ->join('posts','favorites.post_id','=','posts.id')
                            ->where('delete_flag','<>',1)
                            ->join('userinfomations','posts.user_id','=','userinfomations.id')
                            ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','posts.created_at','favorites.created_at as favtime')
                            ->leftJoinSub($favorite_count, 'temp' , function($leftjoin){
                                $leftjoin->on('posts.id','=','temp.post_id');
                            })
                            ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','posts.created_at','favorites.created_at as favtime')
                            ->leftJoinSub($reply_count, 'temp2', function($leftjoin){
                                $leftjoin->on('posts.id','=','temp2.replied_id');
                            })
                            ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','posts.created_at','fav_flag','favorites.created_at as favtime','favorite_count','reply_count')
                            ->leftJoinSub($reply_flag, 'reply',function($leftjoin) use($user_id){
                                $leftjoin->on('posts.id','=','reply.replied_id')
                                            ->where('reply.user_id','=',$user_id);
                            })
                            ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','posts.created_at','fav_flag','favorite_count','favorites.created_at as favtime','reply_count','reply_flag')
                            ->leftJoinSub($reply_id, 'replyid',function($leftjoin){
                                $leftjoin->on('posts.id','=', 'replyid.replying_id');
                            })
                            ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','posts.created_at','fav_flag','favorite_count','favorites.created_at as favtime','reply_count','reply_flag','reply_id','replied_postid')
                            ->leftJoinSub($share_count, 'shareCount' , function($leftjoin){
                                $leftjoin->on('posts.id','=','shareCount.share_id');
                            })
                            ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','posts.created_at','fav_flag','favorite_count','favorites.created_at as favtime','reply_count','reply_flag','reply_id','replied_postid','share_count')
                            ->leftJoin('shares',function($leftjoin) use($user_id){
                                $leftjoin->on('posts.id','=','shares.share_id')
                                            ->where('shares.user_id','=',$user_id);
                            })
                            ->select(DB::raw('posts.user_id as user_no,posts.id as post_id,userinfomations.user_id,user_name,userimage_url,text,self_introduction,first_medhia_url,second_medhia_url,first_medhia_name,second_medhia_name,thumbnail_url,NULL AS sharing_user_no,NULL AS sharing_name,file_token,posts.created_at,fav_flag,favorite_count,favorites.created_at as favtime,reply_count,reply_flag,reply_id,replied_postid,share_count,share_flag'))
                            ->orderBy('favorites.created_at','desc')
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