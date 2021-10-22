<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mode extends Model
{
    use HasFactory;
    protected $fillable = ["type","name","main_color","sub_color","text_color","input_color","border_color"];
    protected $dates = ["created_at","update_at"];
}
