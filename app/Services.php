<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    protected $table = 'services';
    protected $hidden = ['created_at','deleted_at','updated_at'];
}
