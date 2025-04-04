<?php

namespace App\Services;

use App\Models\User;

class StudentEmailService
{
    public static function generateEmail($firstName, $lastName, $departmentId, $domain = 'students.rhu.edu.lb')
    {
        $cleanFirstName = preg_replace('/[^a-zA-Z]/', '', $firstName);
        $cleanLastName = preg_replace('/[^a-zA-Z]/', '', $lastName);
        
        $firstPart = strtolower(substr($cleanFirstName, 0, 4)); 
        $lastInitial = strtoupper(substr($cleanLastName, 0, 1));

        // Ensure firstPart is at least 2 characters
        if (strlen($firstPart) < 2) {
            $firstPart .= 'x'; // Default extra character if needed
        }

        $baseEmail = "{$firstPart}{$lastInitial}@{$domain}";
        $email = $baseEmail;
        
        $count = 1;
        $additionalChars = 1;

        while (User::where('email', $email)->exists()) {
            $extraChars = substr($cleanLastName, 1, $additionalChars);
            
            if (!empty($extraChars)) {
                $email = "{$firstPart}{$lastInitial}" . strtolower($extraChars) . "@{$domain}";
                $additionalChars++;
            } else {
                $email = "{$firstPart}{$lastInitial}{$count}@{$domain}";
                $count++;
            }
        }

        return $email;
    }
}
