<?php

namespace Amirys\Otp;

use Amirys\Otp\Models\Otp as OtpEntity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OtpService
{
    private string $phone_number;
    private Builder|Model $otp_entity;

    public function __construct(OtpEntity $otpEntities)
    {
        $this->otp_entity = $otpEntities;
    }

    public function request($phone_number): OtpEntity
    {
        $this->phone_number = $phone_number;

        $this->processOtpRequest();

        return $this->otp_entity;
    }

    private function processOtpRequest(): void
    {
        $otp = $this->otp_entity->where(OtpEntity::PHONE_NUMBER, $this->phone_number)->first();

        $otp ? $this->checkCodeExpiration($otp) : $this->generateNewCode();
    }

    private function checkCodeExpiration($otp): void
    {
        $expire_date = $otp->created_at->timestamp + config('otp.expiration_time');
        $now = now()->timestamp;

        $expire_date < $now
            ? $this->generateNewCode(true)
            : $this->otp_entity = $otp;
    }

    private function generateNewCode($withDelete = false): void
    {
        if ($withDelete) {
            $this->deletePreviousCOde();
        }

        $otp = $this->generateRandomUniqueCode();

        $this->otp_entity = $this->createNewOtp($otp);
    }

    private function deletePreviousCode(): void
    {
        $this->otp_entity->where(OtpEntity::PHONE_NUMBER, $this->phone_number)->delete();
    }

    private function generateRandomUniqueCode(): int
    {
        $randomCode = random_int(100000, 999999);

        //check code has unique
        $otp = $this->otp_entity->where(OtpEntity::CODE, $randomCode)->first();
        if ($otp) {
            $this->generateRandomUniqueCode();
        }
        return $randomCode;
    }

    private function createNewOtp($otp): OtpEntity
    {
        return $this->otp_entity->create([
            OtpEntity::PHONE_NUMBER => $this->phone_number,
            OtpEntity::CODE => $otp,
        ]);
    }
}
