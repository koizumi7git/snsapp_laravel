<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserinfoController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\FavoriteController;
use App\Http\Controllers\API\FollowController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\ModeController;
use App\Http\Controllers\API\ColorController;
use App\Http\Controllers\API\ReplyController;
use App\Http\Controllers\API\MessageController;
use App\Http\Controllers\API\ShareController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/

/*
Route::prefix('auth')->group(function () {
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout']);
});
*/
/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['api']], function () {
    Route::post('all_post', [PostController::class, 'allList']);
});
*/

/*
Route::middleware('auth:sanctum')->group(function(){
    
    Route::get('info', [LoginController::class, 'info']);
});
*/

//ユーザー登録時のIDチェック
Route::get('user_check', [UserinfoController::class, 'idCheck']);

//自分のユーザー情報取得
Route::get('user_info',[UserinfoController::class, 'index']);

//ユーザー情報登録
Route::get('user_signup', [UserinfoController::class, 'signup']);

//ユーザー情報アップデート
Route::get('user_update', [UserinfoController::class, 'update']);

//ユーザーモード変更
Route::get('user_change_mode', [UserinfoController::class, 'changeMode']);

//ユーザーカラー変更
Route::get('user_change_color', [UserinfoController::class, 'changeColor']);

//リアルタイム通知 uid 取得
Route::get('get_uid', [UserinfoController::class, 'getUid']);

//following/ed count取得
Route::get('follow_info', [FollowController::class, 'followList']);

//followしている全てのリストを取得
Route::get('all_post', [PostController::class, 'allList']);

//自分の投稿リスト取得
Route::get('my_post', [PostController::class, 'myList']);

//画像のみのリスト取得
Route::get('medhia_post', [PostController::class, 'medhiaList']);

//人気のリスト取得
Route::get('hot_post', [PostController::class, 'hotList']);

//通知リスト 取得
Route::get('notification_list', [PostController::class, 'notificationList']);

//投稿
Route::get('post_data', [PostController::class, 'postList']);

//お気に入り追加・削除
Route::get('favorite', [FavoriteController::class, "favorite"]);

//お気に入りリスト取得
Route::get('favorite_list', [FavoriteController::class, 'favoriteList']);

//mode type取得
Route::get('get_mode', [ModeController::class, 'getMode']);

//color type取得
Route::get('get_color', [ColorController::class, 'getColor']);

//一時的 mode type取得
Route::get('get_temp_mode', [ModeController::class, 'getTempMode']);

//一時的 color type取得
Route::get('get_temp_color', [ColorController::class, 'getTempColor']);

//details user取得
Route::get('get_user_details', [UserinfoController::class, "getUserDetails"]);

//details user post取得
Route::get('get_user_post_details', [PostController::class, "getUserPostDetails"]);

//details post取得
Route::get('get_post_details', [PostController::class, 'getPostDetails']);

//details followList取得
Route::get('follow_info_details', [FollowController::class, 'followListDetails']);

//signup時 follow登録
Route::get('signup_follow',[FollowController::class , 'signupFollow']);

//follow追加
Route::get('add_follow', [FollowController::class , 'addFollow']);

//follow削除
Route::get('remove_follow', [FollowController::class , 'removeFollow']);

//details follow flag 取得
Route::get('get_follow_flag', [FollowController::class , 'followFlag']);

//フォロワーのuid 取得
Route::get('get_follower_uid', [FollowController::class , 'getFollowerUid']);

//search
Route::get('search_list', [PostController::class, 'searchList']);

//返信
Route::get('reply', [ReplyController::class, 'reply']);

//メッセージ投稿
Route::get('message', [MessageController::class, 'addMessage']);

//メッセージ取得
Route::get('get_message', [MessageController::class, 'getMessage']);

//メッセージリスト取得
Route::get('get_message_list', [MessageController::class, 'getMessageList']);

//メッセージリスト追加
Route::get('add_message_list', [MessageController::class, 'addMessageList']);

//メッセージ追加可能リスト取得
Route::get('get_message_followlist', [MessageController::class, 'getMessageFollowList']);

//シェア追加・削除
Route::get('share', [ShareController::class, 'share']);

//投稿削除
Route::get('delete_post', [PostController::class, 'deletePost']);

//ユーザー削除
Route::get('delete_user', [UserinfoController::class, 'deleteUser']);
