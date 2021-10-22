<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Userinfomation;
use App\Models\Mode;
use App\Models\Color;
use App\Models\Post;
use App\Models\Follow;
use App\Models\Favorite;
use App\Models\Share;
use App\Models\Messagelist;

class UserInfoController extends Controller
{
    public function idCheck(Request $request)
    {
        $checkId = $request->input('id');
        //$checkId = '@test1use';
        $result = Userinfomation::where('user_id','=',$checkId)
                                    ->get();

        return $this->resConversionJson($result);

    }

    public function index(Request $request)
    {
        $authUid = $request->input("uid");

        $result = Userinfomation::where('auth_id','=',$authUid)
                                    ->select('id','user_id','user_name','self_introduction','userimage_url','userimage_name')
                                    ->get();
        
        return $this->resConversionJson($result);
    }

    public function signup(Request $request)
    {
        $userId = $request->input("user_id");
        $userName = $request->input("user_name");
        $selfIntroduction = $request->input("self_introduction");
        $authUid = $request->input("auth_id");
        $mode = 'dark';
        $color = "darkorange";
        $imageUrl = "";
        $imageName = "";

        Userinfomation::create(["user_id" => $userId, "user_name" => $userName, "self_introduction" => $selfIntroduction, "auth_id" => $authUid, "mode" => $mode, 'color'=>$color,'userimage_url'=>$imageUrl,'userimage_name'=>$imageName]);

        //$result = Userinfomation::where("user_id", $userId)->first();

        return 'complete';
    }

    public function update(Request $request)
    {
        $userId = $request->input("id");
        $updateUserId = $request->input("newId");
        $updateUserName = $request->input("name");
        $updateSelfIntroduction = $request->input("text");
        $updateImageUrl = $request->input("imageUrl");
        $updataImageName = $request->input("imageName");

        Userinfomation::where("id", $userId)->update(['user_id' => $updateUserId, 'user_name' => $updateUserName, 'self_introduction' => $updateSelfIntroduction,'userimage_url' => $updateImageUrl,'userimage_name'=>$updataImageName]);

    }

    public function changeMode(Request $request)
    {
        $userId = $request->input('id');
        $mode = $request->input('type');

        UserInfomation::where("id",$userId)->update(['mode' => $mode]);

        $result = Mode::where("type",$mode)->select('type','name','main_color','sub_color','text_color','input_color')->get();

        return $this->resConversionJson($result);

    }

    public function changeColor(Request $request)
    {
        $userId = $request->input('id');
        $color = $request->input('type');

        UserInfomation::where("id",$userId)->update(['color' => $color]);

        $result = Color::where("type",$color)->select('type','value')->get();

        return $this->resConversionJson($result);
    }

    public function getUserDetails(Request $request)
    {
        $userId = $request->input('userId');
        //$userId = '@test1user';
        $result = UserInfomation::where('user_id','=',$userId)
                        ->select('id','user_id','user_name','self_introduction','userimage_url')
                        ->get();

        return $this->resConversionJson($result);
    }

    public function getUid(Request $request)
    {
        $user_no = $request->input('user_no');

        $result = UserInfomation::where('id','=',$user_no)
                                    ->select('auth_id')
                                    ->get();
        return $this->resConversionJson($result);
    }

    public function deleteUser(Request $request)
    {
        $user_no = $request->input('user_no');
        UserInfomation::where('id','=',$user_no)
                        ->update(['delete_user_flag' => 1]);

        Post::where('user_id','=',$user_no)
                ->update(['delete_flag' => 1]);

        Follow::where('following_id','=',$user_no)
                    ->delete();

        Follow::where('followed_id','=',$user_no)
                    ->delete();

        Favorite::where('user_id','=',$user_no)
                    ->delete();

        Share::where('user_id','=',$user_no)
                    ->delete();

        Messagelist::where('one_user','=',$user_no)
                    ->delete();
        
        Messagelist::where('two_user','=',$user_no)
                    ->delete();
    }

    private function resConversionJson($result, $statusCode=200)
    {
        if(empty($statusCode) || $statusCode < 100 || $statusCode >= 600){
            $statusCode = 500;
        }
        return response()->json($result, $statusCode, ['Content-Type' => 'application/json'],JSON_UNESCAPED_SLASHES);
    }

    
}

