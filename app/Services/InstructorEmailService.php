<?php

namespace App\Services;

use App\Models\User;

class InstructorEmailService
{
    public static function generateEmail($firstName, $lastName, $domain = 'instructors.rhu.edu')
    {
        $cleanFirstName = preg_replace('/[^a-zA-Z]/', '', $firstName);
        $cleanLastName = preg_replace('/[^a-zA-Z]/', '', $lastName);
        
        $firstPart = strtolower(substr($cleanFirstName, 0, 2)); 
        $lastPart = strtolower($cleanLastName);
        
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