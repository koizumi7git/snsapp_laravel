<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ["user_id","text","first_medhia_url","second_medhia_url","first_medhia_name","second_medhia_name","thumbnail_url","file_token","delete_flag"];
    protected $dates = ['created_at','update_at'];
}
