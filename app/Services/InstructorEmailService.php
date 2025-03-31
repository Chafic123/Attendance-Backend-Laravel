<?php

namespace App\Services;

use App\Models\User;

class InstructorEmailService
{
    public static function generateEmail($firstName, $lastName, $domain = 'instructors.rhu.edu')
    {
        $firstPart = strtolower(substr($firstName, 0, 2)); 
        $lastPart = strtolower($lastName);
        
        $baseEmail = "{$lastPart}{$firstPart}@{$domain}";
        $email = $baseEmail;
        
        $count = 1;
        
        while (User::where('email', $email)->exists()) {
            $email = "{$lastPart}{$firstPart}{$count}@{$domain}";
            $count++;
        }

        return $email;
    }
}
