<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Userinfomation extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','user_name','self_introduction','auth_id','mode','color','userimage_url','userimage_name','delete_user_flag'];
    protected $dates = ['created_at','update_at'];
}
