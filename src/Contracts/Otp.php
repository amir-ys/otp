<?php

use Amirys\Otp\Models\Otp as OtpEntity;

interface Otp
{
    public function request($phone_number): OtpEntity;
}