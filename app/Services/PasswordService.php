<?php 


// app/Services/PasswordService.php
namespace App\Services;
use Illuminate\Support\Str;

class PasswordService
{
    public static function generateTemporaryPassword()
    {
        return Str::upper(Str::random(2)) . '@' . rand(10, 99);
    }
}