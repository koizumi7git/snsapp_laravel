<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Post;
use App\Models\Follow;
use App\Models\Reply;
use App\Models\Userinfomation;
use App\Models\Favorite;
use App\Models\Share;

class PostController extends Controller
{
    //お気に入り 数
    public function favoriteCount(){

        return DB::table('favorites')
                    ->select(DB::raw('favorites.post_id, COUNT(favorites.post_id) AS favorite_count'))
                    ->groupBy('post_id');
                    //->toSql();
    }

    //返信　数
    public function replyCount(){

        return  DB::table('replies')
                    ->join('posts','replies.replying_id','=','posts.id')
                    ->where('posts.delete_flag','<>',1)
                    ->select(DB::raw('replied_id, COUNT(replied_id) AS reply_count'))
                    ->groupBy('replied_id');
                    //->toSql();
    }

    //返信フラグ
    public function replyFlag(){

        return Reply::join('posts','replying_id','=','posts.id')
                    ->select('replied_id','reply_flag','posts.user_id')
                    ->distinct();
                    //->toSql();

    }

    //返信　相手id
    public function replyId(){
        return Reply::join('posts','replied_id','=','posts.id')
                    ->select('replying_id','posts.user_id')
                    ->join('userinfomations as temp','posts.user_id','=','temp.id')
                    ->select(DB::raw('replying_id ,replied_id as replied_postid, temp.user_id as reply_id,temp.delete_user_flag'))
                    ->where('temp.delete_user_flag','<>',1)
                    ->select(DB::raw('replying_id ,replied_id as replied_postid, temp.user_id as reply_id'));
                    //->toSql();
    }

    //シェア 数
    public function shareCount(){
        return Share::select(DB::raw('share_id ,COUNT(share_id) AS share_count'))
                        ->groupBy('share_id');
                        //->toSql();
    }

    //全てのリストのシェア 
    public function sharePost($user_id){

        $favorite_count = $this->favoriteCount();

        $reply_count = $this->replyCount();

        $reply_flag = $this->replyFlag();

        $reply_id = $this->replyId();

        $share_count = $this->shareCount();

        return Follow::where('following_id','=',$user_id)
                        ->join('shares','followed_id','=','shares.user_id')
                        ->select('share_id','shares.user_id','share_flag','shares.created_at')
                        ->join('posts','shares.share_id','=','posts.id')
                        ->where('delete_flag','<>',1)
                        ->select('posts.user_id as user_no','posts.id','text','first_medhia_url','second_medhia_url','first_medhia_name','first_medhia_name','file_token','thumbnail_url','shares.created_at','shares.user_id as sharing_user_no')
                        ->join('userinfomations','posts.user_id','=','userinfomations.id')
                        ->select('posts.user_id as user_no','posts.id','userinfomations.user_id','userinfomations.user_name','userinfomations.userimage_url','text','userinfomations.self_introduction','first_medhia_url','second_medhia_url','first_medhia_name','first_medhia_name','file_token','thumbnail_url','shares.created_at','shares.user_id as sharing_user_no')
                        ->join('userinfomations as tempuserinfomations','shares.user_id','=','tempuserinfomations.id')
                        ->select('posts.user_id as user_no','posts.id','userinfomations.user_id','userinfomations.user_name','userinfomations.userimage_url','text','userinfomations.self_introduction','first_medhia_url','second_medhia_url','first_medhia_name','first_medhia_name','file_token','thumbnail_url','shares.created_at','shares.user_id as sharing_user_no','tempuserinfomations.user_name as sharing_name')
                        ->leftjoin('favorites', function($leftjoin) use($user_id){
                            $leftjoin->on('posts.id','=','favorites.post_id')
                                        ->where('favorites.user_id','=',$user_id);
                        })
                        ->select('posts.user_id as user_no','posts.id','userinfomations.user_id','userinfomations.user_name','userinfomations.userimage_url','text','userinfomations.self_introduction','first_medhia_url','second_medhia_url','first_medhia_name','first_medhia_name','file_token','thumbnail_url','shares.created_at','shares.user_id as sharing_user_no','tempuserinfomations.user_name as sharing_name','fav_flag')
                        ->leftJoinSub($favorite_count, 'temp' , function($leftjoin){
                            $leftjoin->on('posts.id','=','temp.post_id');
                        })
                        ->select('posts.user_id as user_no','posts.id','userinfomations.user_id','userinfomations.user_name','userinfomations.userimage_url','text','userinfomations.self_introduction','first_medhia_url','second_medhia_url','first_medhia_name','first_medhia_name','file_token','thumbnail_url','shares.created_at','shares.user_id as sharing_user_no','tempuserinfomations.user_name as sharing_name','fav_flag','favorite_count')
                        ->leftJoinSub($reply_count, 'temp2', function($leftjoin){
                            $leftjoin->on('posts.id','=','temp2.replied_id');
                        })
                        ->select('posts.user_id as user_no','posts.id','userinfomations.user_id','userinfomations.user_name','userinfomations.userimage_url','text','userinfomations.self_introduction','first_medhia_url','second_medhia_url','first_medhia_name','first_medhia_name','file_token','thumbnail_url','shares.created_at','shares.user_id as sharing_user_no','tempuserinfomations.user_name as sharing_name','fav_flag','favorite_count','reply_count')
                        ->leftJoinSub($reply_flag, 'reply',function($leftjoin) use($user_id){
                            $leftjoin->on('posts.id','=','reply.replied_id')
                                        ->where('reply.user_id','=',$user_id);
                        })
                        ->select('posts.user_id as user_no','posts.id','userinfomations.user_id','userinfomations.user_name','userinfomations.userimage_url','text','userinfomations.self_introduction','first_medhia_url','second_medhia_url','first_medhia_name','first_medhia_name','file_token','thumbnail_url','shares.created_at','shares.user_id as sharing_user_no','tempuserinfomations.user_name as sharing_name','fav_flag','favorite_count','reply_count','reply_flag')
                        ->leftJoinSub($reply_id, 'replyid',function($leftjoin){
                            $leftjoin->on('posts.id','=', 'replyid.replying_id');
                        })
                        ->select('posts.user_id as user_no','posts.id','userinfomations.user_id','userinfomations.user_name','userinfomations.userimage_url','text','userinfomations.self_introduction','first_medhia_url','second_medhia_url','first_medhia_name','first_medhia_name','file_token','thumbnail_url','shares.created_at','shares.user_id as sharing_user_no','tempuserinfomations.user_name as sharing_name','fav_flag','favorite_count','reply_count','reply_flag','reply_id','replied_postid')
                        ->leftJoinSub($share_count, 'shareCount' , function($leftjoin){
                            $leftjoin->on('posts.id','=','shareCount.share_id');
                        })
                        ->select('posts.user_id as user_no','posts.id','userinfomations.user_id','userinfomations.user_name','userinfomations.userimage_url','text','userinfomations.self_introduction','first_medhia_url','second_medhia_url','first_medhia_name','first_medhia_name','file_token','thumbnail_url','shares.created_at','shares.user_id as sharing_user_no','tempuserinfomations.user_name as sharing_name','fav_flag','favorite_count','reply_count','reply_flag','reply_id','replied_postid','share_count')
                        ->leftJoin('shares as tempshares',function($leftjoin) use($user_id){
                            $leftjoin->on('posts.id','=','tempshares.share_id')
                                        ->where('tempshares.user_id','=',$user_id);
                        })
                        ->select(DB::raw('posts.user_id as user_no,posts.id as post_id,userinfomations.user_id as user_id,userinfomations.user_name as user_name,userinfomations.userimage_url as userimage_url,text,userinfomations.self_introduction as self_introduction,shares.created_at as created_at,first_medhia_url,second_medhia_url,first_medhia_name,first_medhia_name,file_token,thumbnail_url,shares.user_id as sharing_user_no,tempuserinfomations.user_name as sharing_name,fav_flag,favorite_count,reply_count,reply_flag,reply_id,replied_postid,share_count,tempshares.share_flag as share_flag'));
    }

    //自分のリストのシェア 
    public function mySharePost($user_id){
        $favorite_count = $this->favoriteCount();

        $reply_count = $this->replyCount();

        $reply_flag = $this->replyFlag();

        $reply_id = $this->replyId();

        $share_count = $this->shareCount();

        return Share::where('shares.user_id','=',$user_id)
                        ->select('shares.user_id','share_id','created_at')
                        ->join('posts','share_id','=','posts.id')
                        ->where('delete_flag','<>',1)
                        ->select('posts.user_id as user_no','posts.id','text','first_medhia_url','second_medhia_url','first_medhia_name','first_medhia_name','file_token','thumbnail_url','shares.created_at','shares.user_id as sharing_user_no')
                        ->join('userinfomations','posts.user_id','=','userinfomations.id')
                        ->select('posts.user_id as user_no','posts.id','userinfomations.user_id','userinfomations.user_name','userinfomations.userimage_url','text','userinfomations.self_introduction','first_medhia_url','second_medhia_url','first_medhia_name','first_medhia_name','file_token','thumbnail_url','shares.created_at','shares.user_id as sharing_user_no')
                        ->join('userinfomations as tempuserinfomations','shares.user_id','=','tempuserinfomations.id')
                        ->select('posts.user_id as user_no','posts.id','userinfomations.user_id','userinfomations.user_name','userinfomations.userimage_url','text','userinfomations.self_introduction','first_medhia_url','second_medhia_url','first_medhia_name','first_medhia_name','file_token','thumbnail_url','shares.created_at','shares.user_id as sharing_user_no','tempuserinfomations.user_name as sharing_name')
                        ->leftjoin('favorites', function($leftjoin) use($user_id){
                            $leftjoin->on('posts.id','=','favorites.post_id')
                                        ->where('favorites.user_id','=',$user_id);
                        })
                        ->select('posts.user_id as user_no','posts.id','userinfomations.user_id','userinfomations.user_name','userinfomations.userimage_url','text','userinfomations.self_introduction','first_medhia_url','second_medhia_url','first_medhia_name','first_medhia_name','file_token','thumbnail_url','shares.created_at','shares.user_id as sharing_user_no','tempuserinfomations.user_name as sharing_name','fav_flag')
                        ->leftJoinSub($favorite_count, 'temp' , function($leftjoin){
                            $leftjoin->on('posts.id','=','temp.post_id');
                        })
                        ->select('posts.user_id as user_no','posts.id','userinfomations.user_id','userinfomations.user_name','userinfomations.userimage_url','text','userinfomations.self_introduction','first_medhia_url','second_medhia_url','first_medhia_name','first_medhia_name','file_token','thumbnail_url','shares.created_at','shares.user_id as sharing_user_no','tempuserinfomations.user_name as sharing_name','fav_flag','favorite_count')
                        ->leftJoinSub($reply_count, 'temp2', function($leftjoin){
                            $leftjoin->on('posts.id','=','temp2.replied_id');
                        })
                        ->select('posts.user_id as user_no','posts.id','userinfomations.user_id','userinfomations.user_name','userinfomations.userimage_url','text','userinfomations.self_introduction','first_medhia_url','second_medhia_url','first_medhia_name','first_medhia_name','file_token','thumbnail_url','shares.created_at','shares.user_id as sharing_user_no','tempuserinfomations.user_name as sharing_name','fav_flag','favorite_count','reply_count')
                        ->leftJoinSub($reply_flag, 'reply',function($leftjoin) use($user_id){
                            $leftjoin->on('posts.id','=','reply.replied_id')
                                        ->where('reply.user_id','=',$user_id);
                        })
                        ->select('posts.user_id as user_no','posts.id','userinfomations.user_id','userinfomations.user_name','userinfomations.userimage_url','text','userinfomations.self_introduction','first_medhia_url','second_medhia_url','first_medhia_name','first_medhia_name','file_token','thumbnail_url','shares.created_at','shares.user_id as sharing_user_no','tempuserinfomations.user_name as sharing_name','fav_flag','favorite_count','reply_count','reply_flag')
                        ->leftJoinSub($reply_id, 'replyid',function($leftjoin){
                            $leftjoin->on('posts.id','=', 'replyid.replying_id');
                        })
                        ->select('posts.user_id as user_no','posts.id','userinfomations.user_id','userinfomations.user_name','userinfomations.userimage_url','text','userinfomations.self_introduction','first_medhia_url','second_medhia_url','first_medhia_name','first_medhia_name','file_token','thumbnail_url','shares.created_at','shares.user_id as sharing_user_no','tempuserinfomations.user_name as sharing_name','fav_flag','favorite_count','reply_count','reply_flag','reply_id','replied_postid')
                        ->leftJoinSub($share_count, 'shareCount' , function($leftjoin){
                            $leftjoin->on('posts.id','=','shareCount.share_id');
                        })
                        ->select('posts.user_id as user_no','posts.id','userinfomations.user_id','userinfomations.user_name','userinfomations.userimage_url','text','userinfomations.self_introduction','first_medhia_url','second_medhia_url','first_medhia_name','first_medhia_name','file_token','thumbnail_url','shares.created_at','shares.user_id as sharing_user_no','tempuserinfomations.user_name as sharing_name','fav_flag','favorite_count','reply_count','reply_flag','reply_id','replied_postid','share_count')
                        ->leftJoin('shares as tempshares',function($leftjoin) use($user_id){
                            $leftjoin->on('posts.id','=','tempshares.share_id')
                                        ->where('tempshares.user_id','=',$user_id);
                        })
                        ->select(DB::raw('posts.user_id as user_no,posts.id as post_id,userinfomations.user_id as user_id,userinfomations.user_name as user_name,userinfomations.userimage_url as userimage_url,text,userinfomations.self_introduction as self_introduction,shares.created_at as created_at,first_medhia_url,second_medhia_url,first_medhia_name,first_medhia_name,file_token,thumbnail_url,shares.user_id as sharing_user_no,tempuserinfomations.user_name as sharing_name,fav_flag,favorite_count,reply_count,reply_flag,reply_id,replied_postid,share_count,tempshares.share_flag as share_flag'));
                        //->get();
    }

    //自分含め、フォローしている人の投稿取得
    public function allList(Request $request)
    {
        $user_id = $request->input('id');
        //$user_id = 1;

        $favorite_count = $this->favoriteCount();

        $reply_count = $this->replyCount();

        $reply_flag = $this->replyFlag();

        $reply_id = $this->replyId();

        $share_count = $this->shareCount();

        $share_post = $this->sharePost($user_id);

        $result = Follow::where('following_id','=',$user_id)
                        ->join('posts','followed_id','=','posts.user_id')
                        ->where('delete_flag','<>',1)
                        ->join('userinfomations','posts.user_id','=','userinfomations.id')
                        ->select('posts.user_id as user_no','posts.id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token')
                        ->leftjoin('favorites', function($leftjoin) use($user_id){
                            $leftjoin->on('posts.id','=','favorites.post_id')
                                        ->where('favorites.user_id','=',$user_id);
                        })
                        ->select('posts.user_id as user_no','posts.id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag')
                        ->leftJoinSub($favorite_count, 'temp' , function($leftjoin){
                            $leftjoin->on('posts.id','=','temp.post_id');
                        })
                        ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count')
                        ->leftJoinSub($reply_count, 'temp2', function($leftjoin){
                            $leftjoin->on('posts.id','=','temp2.replied_id');
                        })
                        ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count')
                        ->leftJoinSub($reply_flag, 'reply',function($leftjoin) use($user_id){
                            $leftjoin->on('posts.id','=','reply.replied_id')
                                        ->where('reply.user_id','=',$user_id);
                        })
                        ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count','reply_flag')
                        ->leftJoinSub($reply_id, 'replyid',function($leftjoin){
                            $leftjoin->on('posts.id','=', 'replyid.replying_id');
                        })
                        ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count','reply_flag','reply_id','replied_postid')
                        ->leftJoinSub($share_count, 'shareCount' , function($leftjoin){
                            $leftjoin->on('posts.id','=','shareCount.share_id');
                        })
                        ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count','reply_flag','reply_id','replied_postid','share_count')
                        ->leftJoin('shares',function($leftjoin) use($user_id){
                            $leftjoin->on('posts.id','=','shares.share_id')
                                        ->where('shares.user_id','=',$user_id);
                        })
                        ->select(DB::raw('posts.user_id as user_no,posts.id as post_id,userinfomations.user_id,user_name,userimage_url,text,self_introduction,posts.created_at,first_medhia_url,second_medhia_url,first_medhia_name,second_medhia_name,file_token,thumbnail_url,NULL AS sharing_user_no,NULL AS sharing_name,fav_flag,favorite_count,reply_count,reply_flag,reply_id,replied_postid,share_count,share_flag'))
                        ->where('share_flag','=',null)
                        ->unionAll($share_post)
                        ->orderBy('created_at','desc')
                        ->get();

        return $this->resConversionJson($result);
    }

    //自分の投稿を取得
    public function myList(Request $request)
    {
        $user_id = $request->input("id");
        //$user_id = 1;

        $favorite_count = $this->favoriteCount();

        $reply_count = $this->replyCount();

        $reply_flag = $this->replyFlag();

        $reply_id = $this->replyId();

        $share_count = $this->shareCount();

        $my_share_post = $this->mySharePost($user_id);

        $result = Post::where('posts.user_id','=',$user_id)
                        ->where('delete_flag','<>',1)
                        ->join('userinfomations','posts.user_id','=','userinfomations.id')
                        ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token')
                        ->leftjoin('favorites', function($leftjoin) use($user_id){
                            $leftjoin->on('posts.id','=','favorites.post_id')
                                        ->where('favorites.user_id','=',$user_id);
                        })
                        ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag')
                        ->leftJoinSub($favorite_count, 'temp' , function($leftjoin){
                            $leftjoin->on('posts.id','=','temp.post_id');
                        })
                        ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count')
                        ->leftJoinSub($reply_count, 'temp2', function($leftjoin){
                            $leftjoin->on('posts.id','=','temp2.replied_id');
                        })
                        ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count')
                        ->leftJoinSub($reply_flag, 'reply',function($leftjoin) use($user_id){
                            $leftjoin->on('posts.id','=','reply.replied_id')
                                        ->where('reply.user_id','=',$user_id);
                        })
                        ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count','reply_flag')
                        ->leftJoinSub($reply_id, 'replyid',function($leftjoin){
                            $leftjoin->on('posts.id','=', 'replyid.replying_id');
                        })
                        ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count','reply_flag','reply_id','replied_postid')
                        ->leftJoinSub($share_count, 'shareCount' , function($leftjoin){
                            $leftjoin->on('posts.id','=','shareCount.share_id');
                        })
                        ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count','reply_flag','reply_id','replied_postid','share_count')
                        ->leftJoin('shares',function($leftjoin) use($user_id){
                            $leftjoin->on('posts.id','=','shares.share_id')
                                        ->where('shares.user_id','=',$user_id);
                        })
                        ->select(DB::raw('posts.user_id as user_no,posts.id as post_id,userinfomations.user_id,user_name,userimage_url,text,self_introduction,posts.created_at,first_medhia_url,second_medhia_url,first_medhia_name,second_medhia_name,file_token,thumbnail_url,NULL as sharing_user_no,NULL AS sharing_name,fav_flag,favorite_count,reply_count,reply_flag,reply_id,replied_postid,share_count,share_flag'))
                        ->where('share_flag','=',null)
                        ->unionAll($my_share_post)
                        ->orderBy('created_at','desc')
                        ->get();

        
        return $this->resConversionJson($result);
    }

    //画像、動画のみのリストを取得
    public function medhiaList(Request $request)
    {
        $user_id = $request->input('id');
        //$user_id = 1;

        $favorite_count = $this->favoriteCount();

        $reply_count = $this->replyCount();

        $reply_flag = $this->replyFlag();

        $reply_id = $this->replyId();

        $share_count = $this->shareCount();

        $result = Follow::where('following_id','=',$user_id)
                        ->join('posts','followed_id','=','posts.user_id')
                        ->where('delete_flag','<>',1)
                        ->join('userinfomations','posts.user_id','=','userinfomations.id')
                        ->whereNotNull('first_medhia_url')
                        ->select('posts.user_id as user_no','posts.id','following_id','followed_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token')
                        ->leftjoin('favorites', function($leftjoin) use($user_id){
                            $leftjoin->on('posts.id','=','favorites.post_id')
                                        ->where('favorites.user_id','=',$user_id);
                        })
                        ->select('posts.user_id as user_no','posts.id','following_id','followed_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag')
                        ->leftJoinSub($favorite_count, 'temp' , function($leftjoin){
                            $leftjoin->on('posts.id','=','temp.post_id');
                        })
                        ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count')
                        ->leftJoinSub($reply_count, 'temp2', function($leftjoin){
                            $leftjoin->on('posts.id','=','temp2.replied_id');
                        })
                        ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count')
                        ->leftJoinSub($reply_flag, 'reply',function($leftjoin) use($user_id){
                            $leftjoin->on('posts.id','=','reply.replied_id')
                                        ->where('reply.user_id','=',$user_id);
                        })
                        ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count','reply_flag')
                        ->leftJoinSub($reply_id, 'replyid',function($leftjoin){
                            $leftjoin->on('posts.id','=', 'replyid.replying_id');
                        })
                        ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count','reply_flag','reply_id','replied_postid')
                        ->leftJoinSub($share_count, 'shareCount' , function($leftjoin){
                            $leftjoin->on('posts.id','=','shareCount.share_id');
                        })
                        ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count','reply_flag','reply_id','replied_postid','share_count')
                        ->leftJoin('shares',function($leftjoin) use($user_id){
                            $leftjoin->on('posts.id','=','shares.share_id')
                                        ->where('shares.user_id','=',$user_id);
                        })
                        ->select(DB::raw('posts.user_id as user_no,posts.id as post_id,userinfomations.user_id,user_name,userimage_url,text,self_introduction,posts.created_at,first_medhia_url,second_medhia_url,first_medhia_name,second_medhia_name,thumbnail_url,file_token,NULL AS sharing_user_no,NULL AS sharing_name,fav_flag,favorite_count,reply_count,reply_flag,reply_id,replied_postid,share_count,share_flag'))
                        ->orderBy('posts.created_at','desc')
                        ->get();

        return $this->resConversionJson($result);
    }

    //人気のあるリストを取得
    public function hotList(Request $request)
    {
        $user_id = $request->input('id');

        //$user_id = 1;

        $favorite_count = $this->favoriteCount();

        $reply_count = $this->replyCount();

        $reply_flag = $this->replyFlag();
        
        $reply_id = $this->replyId();

        $share_count = $this->shareCount();

        $nowtime = now()->subDay(4);

        $hot_favorite = Post::where('posts.created_at','>=',$nowtime)
                    ->where('delete_flag','<>',1)
                    ->join('userinfomations','posts.user_id','=','userinfomations.id')
                    ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token')
                    ->leftjoin('favorites', function($leftjoin) use($user_id){
                        $leftjoin->on('posts.id','=','favorites.post_id')
                                    ->where('favorites.user_id','=',$user_id);
                    })
                    ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag')
                    ->leftJoinSub($favorite_count, 'temp' , function($leftjoin){
                        $leftjoin->on('posts.id','=','temp.post_id');
                    })
                    ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count')
                    ->whereNotNull('favorite_count')
                    ->leftJoinSub($reply_count, 'temp2', function($leftjoin){
                        $leftjoin->on('posts.id','=','temp2.replied_id');
                    })
                    ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count')
                    ->leftJoinSub($reply_flag, 'reply',function($leftjoin) use($user_id){
                        $leftjoin->on('posts.id','=','reply.replied_id')
                                    ->where('reply.user_id','=',$user_id);
                    })
                    ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count','reply_flag')
                    ->leftJoinSub($reply_id, 'replyid',function($leftjoin){
                        $leftjoin->on('posts.id','=', 'replyid.replying_id');
                    })
                    ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count','reply_flag','reply_id','replied_postid')
                    ->leftJoinSub($share_count, 'shareCount' , function($leftjoin){
                        $leftjoin->on('posts.id','=','shareCount.share_id');
                    })
                    ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count','reply_flag','reply_id','replied_postid','share_count')
                    ->leftJoin('shares',function($leftjoin) use($user_id){
                        $leftjoin->on('posts.id','=','shares.share_id')
                                    ->where('shares.user_id','=',$user_id);
                    })
                    ->select(DB::raw('posts.user_id as user_no,posts.id as post_id,userinfomations.user_id,user_name,userimage_url,text,self_introduction,posts.created_at,first_medhia_url,second_medhia_url,first_medhia_name,second_medhia_name,thumbnail_url,file_token,NULL AS sharing_user_no,NULL AS sharing_name,fav_flag,favorite_count,reply_count,reply_flag,reply_id,replied_postid,share_count,share_flag'))
                    ->whereNotNull('favorite_count','AND','reply_count')
                    ->orderBy('favorite_count','desc')
                    ->limit(50);
                    //->get();

        $hot_reply = Post::where('posts.created_at','>=',$nowtime)
                    ->where('delete_flag','<>',1)
                    ->join('userinfomations','posts.user_id','=','userinfomations.id')
                    ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token')
                    ->leftjoin('favorites', function($leftjoin) use($user_id){
                        $leftjoin->on('posts.id','=','favorites.post_id')
                                    ->where('favorites.user_id','=',$user_id);
                    })
                    ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag')
                    ->leftJoinSub($favorite_count, 'temp' , function($leftjoin){
                        $leftjoin->on('posts.id','=','temp.post_id');
                    })
                    ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count')
                    ->leftJoinSub($reply_count, 'temp2', function($leftjoin){
                        $leftjoin->on('posts.id','=','temp2.replied_id');
                    })
                    ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count')
                    ->whereNotNull('reply_count')
                    ->leftJoinSub($reply_flag, 'reply',function($leftjoin) use($user_id){
                        $leftjoin->on('posts.id','=','reply.replied_id')
                                    ->where('reply.user_id','=',$user_id);
                    })
                    ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count','reply_flag')
                    ->leftJoinSub($reply_id, 'replyid',function($leftjoin){
                        $leftjoin->on('posts.id','=', 'replyid.replying_id');
                    })
                    ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count','reply_flag','reply_id','replied_postid')
                    ->leftJoinSub($share_count, 'shareCount' , function($leftjoin){
                        $leftjoin->on('posts.id','=','shareCount.share_id');
                    })
                    ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count','reply_flag','reply_id','replied_postid','share_count')
                    ->leftJoin('shares',function($leftjoin) use($user_id){
                        $leftjoin->on('posts.id','=','shares.share_id')
                                    ->where('shares.user_id','=',$user_id);
                    })
                    ->select(DB::raw('posts.user_id as user_no,posts.id as post_id,userinfomations.user_id,user_name,userimage_url,text,self_introduction,posts.created_at,first_medhia_url,second_medhia_url,first_medhia_name,second_medhia_name,thumbnail_url,file_token,NULL AS sharing_user_no,NULL AS sharing_name,fav_flag,favorite_count,reply_count,reply_flag,reply_id,replied_postid,share_count,share_flag'))
                    ->orderBy('reply_count','desc')
                    ->limit(50)
                    ->union($hot_favorite)
                    ->orderBy('created_at','desc')
                    ->get();

        return $this->resConversionJson($hot_reply);

    }

    //投稿する
    public function postList(Request $request)
    {
        $user_id = $request->input("post_id");
        $text = $request->input("post_message");
        $first_medhia_url = $request->input("first_medhia_url");
        $second_medhia_url = $request->input("second_medhia_url");
        $first_medhia_name = $request->input("first_medhia_name");
        $second_medhia_name = $request->input("second_medhia_name");
        $thumbnail_url = $request->input("thumbnail_url");
        $file_token = $request->input("file_token");
        Post::create(["user_id" => $user_id, "text" => $text, "first_medhia_url" => $first_medhia_url ,"second_medhia_url" => $second_medhia_url,"first_medhia_name" => $first_medhia_name,"second_medhia_name"=> $second_medhia_name,"thumbnail_url" => $thumbnail_url,"file_token"=>$file_token]);
    }

    // user post details 取得
    public function getUserPostDetails(Request $request)
    {
        $user_id = $request->input("id");
        $my_user_no = $request->input("myNo");

        $favorite_count = $this->favoriteCount();

        $reply_count = $this->replyCount();

        $reply_flag = $this->replyFlag();
        
        $reply_id = $this->replyId();

        $share_count = $this->shareCount();

        $my_share_post = $this->mySharePost($user_id);

        $result = Post::where('posts.user_id','=',$user_id)
                        ->where('delete_flag','<>',1)
                        ->join('userinfomations','posts.user_id','=','userinfomations.id')
                        ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','text','image_url','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token')
                        ->leftjoin('favorites', function($leftjoin) use($my_user_no){
                            $leftjoin->on('posts.id','=','favorites.post_id')
                                        ->where('favorites.user_id','=',$my_user_no);
                        })
                        ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag')
                        ->leftJoinSub($favorite_count, 'temp' , function($leftjoin){
                            $leftjoin->on('posts.id','=','temp.post_id');
                        })
                        ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count')
                        ->leftJoinSub($reply_count, 'temp2', function($leftjoin){
                            $leftjoin->on('posts.id','=','temp2.replied_id');
                        })
                        ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count')
                        ->leftJoinSub($reply_flag, 'reply',function($leftjoin) use($my_user_no){
                            $leftjoin->on('posts.id','=','reply.replied_id')
                                        ->where('reply.user_id','=',$my_user_no);
                        })
                        ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count','reply_flag')
                        ->leftJoinSub($reply_id, 'replyid',function($leftjoin){
                            $leftjoin->on('posts.id','=', 'replyid.replying_id');
                        })
                        ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count','reply_flag','reply_id','replied_postid')
                        ->leftJoinSub($share_count, 'shareCount' , function($leftjoin){
                            $leftjoin->on('posts.id','=','shareCount.share_id');
                        })
                        ->select('posts.user_id as user_no','posts.id as post_id','userinfomations.user_id','user_name','userimage_url','text','self_introduction','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count','reply_flag','reply_id','replied_postid','share_count')
                        ->leftJoin('shares',function($leftjoin) use($my_user_no){
                            $leftjoin->on('posts.id','=','shares.share_id')
                                        ->where('shares.user_id','=',$my_user_no);
                        })
                        ->select(DB::raw('posts.user_id as user_no,posts.id as post_id,userinfomations.user_id,user_name,userimage_url,text,self_introduction,posts.created_at,first_medhia_url,second_medhia_url,first_medhia_name,second_medhia_name,thumbnail_url,file_token,NULL AS sharing_user_no,NULL AS sharing_name,fav_flag,favorite_count,reply_count,reply_flag,reply_id,replied_postid,share_count,share_flag'))
                        ->unionAll($my_share_post)
                        ->orderBy('created_at','desc')
                        ->get();

        
        return $this->resConversionJson($result);
    }

    //post details 取得
    public function getPostDetails(Request $request)
    {
        $post_id = $request->input('postId');
        $user_no = $request->input('userNo');

        $favorite_count = $this->favoriteCount();

        $reply_count = $this->replyCount();

        $reply_flag = $this->replyFlag();

        $reply_id = $this->replyId();

        $share_count = $this->shareCount();
        
        if(DB::table('posts')->where('posts.id','=',$post_id)->where('delete_flag','=',1)->exists()){
            $result = false;
        }else{
            $main_post = Post::where('posts.id','=', $post_id)
                            ->where('delete_flag','<>',1)
                            ->select('posts.id as post_id','posts.user_id','text','image_url','posts.created_at')
                            ->join('userinfomations','posts.user_id','=','userinfomations.id')
                            ->select('posts.id as post_id','userinfomations.id as user_no','userinfomations.user_id','userinfomations.user_name','userimage_url','text','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token')
                            ->leftjoin('favorites',function($leftjoin) use($user_no){
                                $leftjoin->on('posts.id','=','favorites.post_id')
                                            ->where('favorites.user_id','=',$user_no);
                            })
                            ->select('posts.id as post_id','userinfomations.id as user_no','userinfomations.user_id','userinfomations.user_name','userimage_url','text','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag')
                            ->leftJoinSub($favorite_count, 'temp' , function($leftjoin){
                                $leftjoin->on('posts.id','=','temp.post_id');
                            })
                            ->select('posts.id as post_id','userinfomations.id as user_no','userinfomations.user_id','userinfomations.user_name','userimage_url','text','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count')
                            ->leftJoinSub($reply_count, 'temp2', function($leftjoin){
                                $leftjoin->on('posts.id','=','temp2.replied_id');
                            })
                            ->select('posts.id as post_id','userinfomations.id as user_no','userinfomations.user_id','userinfomations.user_name','userimage_url','text','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count')
                            ->leftJoinSub($reply_flag, 'reply',function($leftjoin) use($user_no){
                                $leftjoin->on('posts.id','=','reply.replied_id')
                                            ->where('reply.user_id','=',$user_no);
                            })
                            ->select('posts.id as post_id','userinfomations.id as user_no','userinfomations.user_id','userinfomations.user_name','userimage_url','text','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count','reply_flag')
                            ->leftJoinSub($reply_id, 'replyid',function($leftjoin){
                                $leftjoin->on('posts.id','=', 'replyid.replying_id');
                            })
                            ->select('posts.id as post_id','userinfomations.id as user_no','userinfomations.user_id','userinfomations.user_name','userimage_url','text','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count','reply_flag','reply_id','replied_postid')
                            ->leftJoinSub($share_count, 'shareCount' , function($leftjoin){
                                $leftjoin->on('posts.id','=','shareCount.share_id');
                            })
                            ->select('posts.id as post_id','userinfomations.id as user_no','userinfomations.user_id','userinfomations.user_name','userimage_url','text','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count','reply_flag','reply_id','replied_postid','share_count')
                            ->leftJoin('shares',function($leftjoin) use($user_no){
                                $leftjoin->on('posts.id','=','shares.share_id')
                                            ->where('shares.user_id','=',$user_no);
                            })
                            ->select(DB::raw('posts.id as post_id,userinfomations.id as user_no,userinfomations.user_id,userinfomations.user_name,userimage_url,text,posts.created_at,first_medhia_url,second_medhia_url,first_medhia_name,second_medhia_name,thumbnail_url,file_token,NULL AS sharing_user_no,NULL AS sharing_name,fav_flag,favorite_count,reply_count,reply_flag,reply_id,replied_postid,share_count,share_flag'))
                            ->get();
        
            $reply = Reply::leftJoinSub($reply_count,'test',function($leftjoin){
                        $leftjoin->on('replies.replying_id','=','test.replied_id');
                        })
                        ->select('replies.replying_id','replies.replied_id','replies.created_at','reply_count')
                        ->where('replies.replied_id','=',$post_id)
                        ->select('replies.replying_id','replies.replied_id','replies.created_at','reply_count')
                        ->join('posts','replies.replying_id','=','posts.id')
                        ->where('delete_flag','<>',1)
                        ->select('posts.id as post_id','posts.user_id as user_no','text','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','replies.replied_id','replies.created_at','reply_count')
                        ->join('userinfomations','posts.user_id','=','userinfomations.id')
                        ->select('posts.id as post_id','posts.user_id as user_no','userinfomations.user_id','userinfomations.user_name','userimage_url','text','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','replies.replied_id','replies.created_at','reply_count')
                        ->leftJoinSub($favorite_count, 'temp' , function($leftjoin){
                            $leftjoin->on('posts.id','=','temp.post_id');
                        })
                        ->select('posts.id as post_id','posts.user_id as user_no','userinfomations.user_id','userinfomations.user_name','userimage_url','text','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','replies.replied_id','replies.created_at','reply_count','favorite_count')
                        ->leftjoin('favorites',function($leftjoin) use($user_no){
                            $leftjoin->on('posts.id','=','favorites.post_id')
                                        ->where('favorites.user_id','=',$user_no);
                        })
                        ->select('posts.id as post_id','posts.user_id as user_no','userinfomations.user_id','userinfomations.user_name','userimage_url','text','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','replies.created_at','reply_count','favorite_count','fav_flag')
                        ->leftJoinSub($reply_flag, 'reply',function($leftjoin) use($user_no){
                            $leftjoin->on('posts.id','=','reply.replied_id')
                                        ->where('reply.user_id','=',$user_no);
                        })
                        ->select('posts.id as post_id','posts.user_id as user_no','userinfomations.user_id','userinfomations.user_name','userimage_url','text','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','replies.created_at','reply_count','reply.reply_flag','favorite_count','fav_flag')
                        ->leftJoinSub($reply_id, 'replyid',function($leftjoin){
                            $leftjoin->on('posts.id','=', 'replyid.replying_id');
                        })
                        ->select('posts.id as post_id','posts.user_id as user_no','userinfomations.user_id','userinfomations.user_name','userimage_url','text','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','replies.created_at','reply_count','reply.reply_flag','favorite_count','fav_flag','reply_id','replied_postid')
                        ->leftJoinSub($share_count, 'shareCount' , function($leftjoin){
                            $leftjoin->on('posts.id','=','shareCount.share_id');
                        })
                        ->select('posts.id as post_id','posts.user_id as user_no','userinfomations.user_id','userinfomations.user_name','userimage_url','text','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','replies.created_at','reply_count','reply.reply_flag','favorite_count','fav_flag','reply_id','replied_postid','share_count')
                        ->leftJoin('shares',function($leftjoin) use($user_no){
                            $leftjoin->on('posts.id','=','shares.share_id')
                                        ->where('shares.user_id','=',$user_no);
                        })
                        ->select(DB::raw('posts.id as post_id,posts.user_id as user_no,userinfomations.user_id,userinfomations.user_name,userimage_url,text,first_medhia_url,second_medhia_url,first_medhia_name,second_medhia_name,thumbnail_url,file_token,NULL AS sharing_user_no,NULL AS sharing_name,replies.created_at,reply_count,reply.reply_flag,favorite_count,fav_flag,reply_id,replied_postid,share_count,share_flag'))
                        ->orderBy('replies.created_at','desc')
                        ->get();

            $result = array('post_details' => $main_post,'reply_details' => $reply );
        }

        return $this->resConversionJson($result);
    }

    public function searchList(Request $request)
    {
        $search_word = $request->input('searchWord');
        $user_no = $request->input('id');

        //$search_word = "%返信%";
        //$user_no = "1";

        $favorite_count = $this->favoriteCount();

        $reply_count = $this->replyCount();

        $reply_flag = $this->replyFlag();

        $reply_id = $this->replyId();

        $share_count = $this->shareCount();

        $subFollowSql= DB::table('follows')->toSql();
        

        $search_post = Post::where('text','like',$search_word)
                        ->where('delete_flag','<>',1)
                        ->select("id as post_id","posts.user_id","text","image_url","created_at")
                        ->join('userinfomations','userinfomations.id','=','posts.user_id')
                        ->select('userinfomations.id as user_no','userinfomations.user_id','user_name','posts.id as post_id','userimage_url','text','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token')
                        ->leftjoin('favorites', function($leftjoin) use($user_no){
                            $leftjoin->on('posts.id','=','favorites.post_id')
                                        ->where('favorites.user_id','=',$user_no);
                        })
                        ->select('userinfomations.id as user_no','userinfomations.user_id','user_name','posts.id as post_id','userimage_url','text','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag')
                        ->leftJoinSub($favorite_count, 'temp' , function($leftjoin){
                            $leftjoin->on('posts.id','=','temp.post_id');
                        })
                        ->select('userinfomations.id as user_no','userinfomations.user_id','user_name','posts.id as post_id','userimage_url','text','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count')
                        ->leftJoinSub($reply_count, 'temp2', function($leftjoin){
                            $leftjoin->on('posts.id','=','temp2.replied_id');
                        })
                        ->select('userinfomations.id as user_no','userinfomations.user_id','user_name','posts.id as post_id','userimage_url','text','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_count')
                        ->leftJoinSub($reply_flag, 'reply',function($leftjoin) use($user_no){
                            $leftjoin->on('posts.id','=','reply.replied_id')
                                        ->where('reply.user_id','=',$user_no);
                        })
                        ->select('userinfomations.id as user_no','userinfomations.user_id','user_name','posts.id as post_id','userimage_url','text','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_flag','reply_count')
                        ->leftJoinSub($reply_id, 'replyid',function($leftjoin){
                            $leftjoin->on('posts.id','=', 'replyid.replying_id');
                        })
                        ->select('userinfomations.id as user_no','userinfomations.user_id','user_name','posts.id as post_id','userimage_url','text','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_flag','reply_count','reply_id','replied_postid')
                        ->leftJoinSub($share_count, 'shareCount' , function($leftjoin){
                            $leftjoin->on('posts.id','=','shareCount.share_id');
                        })
                        ->select('userinfomations.id as user_no','userinfomations.user_id','user_name','posts.id as post_id','userimage_url','text','posts.created_at','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','fav_flag','favorite_count','reply_flag','reply_count','reply_id','replied_postid','share_count')
                        ->leftJoin('shares',function($leftjoin) use($user_no){
                            $leftjoin->on('posts.id','=','shares.share_id')
                                        ->where('shares.user_id','=',$user_no);
                        })
                        ->select(DB::raw('userinfomations.id as user_no,userinfomations.user_id,user_name,posts.id as post_id,userimage_url,text,posts.created_at,first_medhia_url,second_medhia_url,first_medhia_name,second_medhia_name,thumbnail_url,file_token,NULL AS sharing_user_no,NULL AS sharing_name,fav_flag,favorite_count,reply_flag,reply_count,reply_id,replied_postid,share_count,share_flag'))
                        ->orderBy('posts.created_at','desc')
                        ->get();
        
        $search_user_name = Userinfomation::where('userinfomations.id','<>',$user_no)
                                ->where('delete_user_flag','<>',1)
                                ->select('userinfomations.id as user_no','user_id','user_name','userimage_url','self_introduction','userinfomations.created_at')
                                ->Where('user_name','like',$search_word)
                                ->select('userinfomations.id as user_no','user_id','user_name','userimage_url','self_introduction','userinfomations.created_at')
                                ->leftJoinSub($subFollowSql,'Following',function($query) use($user_no){
                                    $query->on('userinfomations.id','=','Following.followed_id')
                                            ->where('Following.following_id','=',$user_no);
                                })
                                ->select('userinfomations.id as user_no','user_id','user_name','userimage_url','self_introduction','userinfomations.created_at','Following.following_id as following_flag')
                                ->leftJoinSub($subFollowSql,'Followed',function($query) use($user_no){
                                    $query->on('userinfomations.id','=','Followed.following_id')
                                            ->where('Followed.followed_id','=',$user_no);
                                })
                                ->select('userinfomations.id as user_no','user_id','user_name','userimage_url','self_introduction','userinfomations.created_at','Following.following_id as following_flag','Followed.followed_id as followed_flag');
                                //->orderBy('created_at','desc')
                                //->get();
        $search_user = Userinfomation::where('userinfomations.id','<>',$user_no)
                            ->where('delete_user_flag','<>',1)
                            ->select('userinfomations.id as user_no','user_id','user_name','userimage_url','self_introduction','userinfomations.created_at')
                            ->Where('user_id','like',$search_word)
                            ->select('userinfomations.id as user_no','user_id','user_name','userimage_url','self_introduction','userinfomations.created_at')
                            ->leftJoinSub($subFollowSql,'Following',function($query) use($user_no){
                                $query->on('userinfomations.id','=','Following.followed_id')
                                        ->where('Following.following_id','=',$user_no);
                            })
                            ->select('userinfomations.id as user_no','user_id','user_name','userimage_url','self_introduction','userinfomations.created_at','Following.following_id as following_flag')
                            ->leftJoinSub($subFollowSql,'Followed',function($query) use($user_no){
                                $query->on('userinfomations.id','=','Followed.following_id')
                                        ->where('Followed.followed_id','=',$user_no);
                            })
                            ->select('userinfomations.id as user_no','user_id','user_name','userimage_url','self_introduction','userinfomations.created_at','Following.following_id as following_flag','Followed.followed_id as followed_flag')
                            ->union($search_user_name)
                            ->orderBy('created_at','desc')
                            ->get();
        
        $result = array('search_post' => $search_post,'search_user' => $search_user );

        return $this->resConversionJson($result);
    }

    public function notificationList(Request $request)
    {
        $user_id = $request->input('id');

        //$user_id = 1;

        $favorite_count = $this->favoriteCount();

        $reply_count = $this->replyCount();

        $reply_flag = $this->replyFlag();

        $reply_id = $this->replyId();

        $share_count = $this->shareCount();

        $subPostSql = DB::table('posts')->toSql();

        $reply = Post::where('posts.user_id','=',$user_id)
                        ->where('posts.delete_flag','<>',1)
                        ->select('posts.id')
                        ->join('replies','posts.id','=','replies.replied_id')
                        ->select('replying_id')
                        ->leftJoinSub($subPostSql,'Post','replying_id','=','Post.id')
                        ->select('Post.id','Post.user_id','Post.text','Post.image_url','Post.created_at')
                        ->join('userinfomations','Post.user_id','=','userinfomations.id')
                        ->where('Post.user_id','<>',$user_id)
                        ->where('delete_user_flag','<>',1)
                        ->select('Post.user_id as user_no','Post.id','following_id','followed_id','userinfomations.user_id','user_name','userimage_url','Post.text','self_introduction','Post.created_at','Post.first_medhia_url','Post.second_medhia_url','Post.first_medhia_name','Post.second_medhia_name','Post.thumbnail_url','Post.file_token')
                        ->leftjoin('favorites', function($leftjoin) use($user_id){
                            $leftjoin->on('Post.id','=','favorites.post_id')
                                        ->where('favorites.user_id','=',$user_id);
                        })
                        ->select('Post.user_id as user_no','Post.id','following_id','followed_id','userinfomations.user_id','user_name','userimage_url','Post.text','self_introduction','Post.created_at','Post.first_medhia_url','Post.second_medhia_url','Post.first_medhia_name','Post.second_medhia_name','Post.thumbnail_url','Post.file_token','fav_flag')
                        ->leftJoinSub($favorite_count, 'temp' , function($leftjoin){
                            $leftjoin->on('Post.id','=','temp.post_id');
                        })
                        ->select('Post.user_id as user_no','Post.id as post_id','userinfomations.user_id','user_name','userimage_url','Post.text','self_introduction','Post.created_at','Post.first_medhia_url','Post.second_medhia_url','Post.first_medhia_name','Post.second_medhia_name','Post.thumbnail_url','Post.file_token','fav_flag','favorite_count')
                        ->leftJoinSub($reply_count, 'temp2', function($leftjoin){
                            $leftjoin->on('Post.id','=','temp2.replied_id');
                        })
                        ->select('Post.user_id as user_no','Post.id as post_id','userinfomations.user_id','user_name','userimage_url','Post.text','self_introduction','Post.created_at','Post.first_medhia_url','Post.second_medhia_url','Post.first_medhia_name','Post.second_medhia_name','Post.thumbnail_url','Post.file_token','fav_flag','favorite_count','reply_count')
                        ->leftJoinSub($reply_flag, 'reply',function($leftjoin) use($user_id){
                            $leftjoin->on('Post.id','=','reply.replied_id')
                                        ->where('reply.user_id','=',$user_id);
                        })
                        ->select('Post.user_id as user_no','Post.id as post_id','userinfomations.user_id','user_name','userimage_url','Post.text','self_introduction','Post.created_at','Post.irst_medhia_url','Post.second_medhia_url','Post.first_medhia_name','Post.second_medhia_name','Post.thumbnail_url','Post.file_token','fav_flag','favorite_count','reply_count','reply.reply_flag')
                        ->leftJoinSub($reply_id, 'replyid',function($leftjoin){
                            $leftjoin->on('Post.id','=', 'replyid.replying_id');
                        })
                        ->select('Post.user_id as user_no','Post.id as post_id','userinfomations.user_id','user_name','userimage_url','Post.text','self_introduction','Post.created_at','Post.first_medhia_url','Post.second_medhia_url','Post.first_medhia_name','Post.second_medhia_name','Post.thumbnail_url','Post.file_token','fav_flag','favorite_count','reply_count','reply.reply_flag','reply_id','replied_postid')
                        ->leftJoinSub($share_count, 'shareCount' , function($leftjoin){
                            $leftjoin->on('Post.id','=','shareCount.share_id');
                        })
                        ->select('Post.user_id as user_no','Post.id as post_id','userinfomations.user_id','user_name','userimage_url','Post.text','self_introduction','Post.created_at','Post.first_medhia_url','Post.second_medhia_url','Post.first_medhia_name','Post.second_medhia_name','Post.thumbnail_url','Post.file_token','fav_flag','favorite_count','reply_count','reply.reply_flag','reply_id','replied_postid','share_count')
                        ->leftJoin('shares',function($leftjoin) use($user_id){
                            $leftjoin->on('Post.id','=','shares.share_id')
                                        ->where('shares.user_id','=',$user_id);
                        })
                        ->select(DB::raw('Post.user_id as user_no,Post.id as post_id,userinfomations.user_id,user_name,userimage_url,Post.text,self_introduction,Post.created_at,Post.first_medhia_url,Post.second_medhia_url,Post.first_medhia_name,Post.second_medhia_name,Post.thumbnail_url,Post.file_token,NULL AS sharing_user_no,NULL AS sharing_name,fav_flag,favorite_count,reply_count,reply.reply_flag,reply_id,replied_postid,share_count,share_flag'))
                        ->orderBy('Post.created_at','desc')
                        ->get();
        
        $follow = Follow::where('followed_id','=',$user_id)
                        ->where('following_id','<>',$user_id)
                        ->select('following_id','follows.created_at')
                        ->join('userinfomations','following_id','=','userinfomations.id')
                        ->select(DB::raw('user_name,userimage_url,userimage_name,follows.created_at AS created_at,NULL AS text,NULL AS first_medhia_url,NULL AS second_medhia_url, NULL AS first_medhia_name,NULL AS second_medhia_name,NULL AS thumbnail_url,NULL AS file_token,"follow" AS type'));
        
        $favorite = Post::where('posts.user_id','=',$user_id)
                            ->where('delete_flag','<>',1)
                            ->select('posts.id','text','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token')
                            ->join('favorites','posts.id','=','favorites.post_id')
                            ->where('favorites.user_id','<>',$user_id)
                            ->select('favorites.user_id','posts.text','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','favorites.created_at')
                            ->join('userinfomations','favorites.user_id','=','userinfomations.id')
                            ->select(DB::raw('user_name,userimage_url,userimage_name,favorites.created_at AS created_at,text,first_medhia_url,second_medhia_url,first_medhia_name,second_medhia_name,thumbnail_url,file_token,"favorite" AS type'));

        $other = Post::where('posts.user_id','=',$user_id)
                            ->where('delete_flag','<>',1)
                            ->select('posts.id','text','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token')
                            ->join('shares','posts.id','=','shares.share_id')
                            ->where('shares.user_id','<>',$user_id)
                            ->select('shares.user_id','posts.text','first_medhia_url','second_medhia_url','first_medhia_name','second_medhia_name','thumbnail_url','file_token','shares.created_at')
                            ->join('userinfomations','shares.user_id','=','userinfomations.id')
                            ->select(DB::raw('user_name,userimage_url,userimage_name,shares.created_at AS created_at,text,first_medhia_url,second_medhia_url,first_medhia_name,second_medhia_name,thumbnail_url,file_token, "share" AS type'))
                            ->unionAll($follow)
                            ->unionAll($favorite)
                            ->orderBy('created_at','desc')
                            ->get();

        $result = array('notification_reply' => $reply,'notification_other' => $other);

        return $this->resConversionJson($result);

    }

    public function deletePost(Request $request)
    {
        $post_id = $request->input('deletePostId');
        Post::where('posts.id','=',$post_id)
                ->update(['delete_flag' => 1]);
        
    }

    private function resConversionJson($result, $statusCode=200)
    {
        if(empty($statusCode) || $statusCode < 100 || $statusCode >= 600){
            $statusCode = 500;
        }
        return response()->json($result, $statusCode, ['Content-Type' => 'application/json'],JSON_UNESCAPED_SLASHES);
    }
}
