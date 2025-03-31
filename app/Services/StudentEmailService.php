<?php

namespace App\Services;

use App\Models\User;
use App\Models\Department;

class StudentEmailService
{
    public static function generateEmail($firstName, $lastName, $departmentId, $domain = 'students.rhu.edu')
    {
        $firstPart = strtolower(substr($firstName, 0, 4)); 
        $lastInitial = strtoupper(substr($lastName, 0, 1));
        
        $baseEmail = "{$firstPart}{$lastInitial}@{$domain}";
        $email = $baseEmail;
        
        $count = 1;
        $additionalChars = 1;

        while (User::where('email', $email)->exists()) {
            $extraChars = substr($lastName, 1, $additionalChars);
            
            if (!empty($extraChars)) {
                $email = "{$firstPart}{$lastInitial}" . strtolower($extraChars) . "@{$domain}";
                $additionalChars++;
            } else {
                $email = "{$firstPart}{$lastInitial}{$count}@{$domain}";
                $count++;
            }
        }

        if (strpos($email, '@') === false) {
            $email .= "@{$domain}";
        }

        return $email;
    }
}