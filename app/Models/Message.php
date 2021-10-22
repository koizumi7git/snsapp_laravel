<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $fillable = ["user_no","message","message_id","first_medhia_url","first_medhia_name","second_medhia_url","second_medhia_name","file_token","thumbnail_url"];
    protected $dates = ["created_at","update_at"];
}
