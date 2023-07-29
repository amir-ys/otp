<?php

namespace Amirys\Otp\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $guarded = [];

    const PHONE_NUMBER = "phone_number";
    const CODE = "code";
}