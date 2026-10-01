<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackgroundMusic extends Model
{
    protected $fillable = ['title', 'artist', 'file_path'];
}
