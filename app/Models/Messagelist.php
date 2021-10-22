<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Messagelist extends Model
{
    use HasFactory;

    protected $fillable = ["one_user","two_user"];
    protected $dates = ["created_at","update_at"];
}
