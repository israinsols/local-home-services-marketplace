<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentPage extends Model
{
    protected $fillable = ['title', 'slug', 'body', 'meta_title', 'meta_description', 'status'];
}
