<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    use HasFactory;

    protected $fillable = ["replying_id","replied_id","reply_flag"];
    protected $dates = ['created_at','update_at'];
}
