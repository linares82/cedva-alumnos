<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{

    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);
    }

    //Mass Assignment
    protected $fillable = ['user_id', 'code_error', 'mensaje'];
}
