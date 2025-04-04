<?php

namespace App\Services;

use App\Models\User;

class InstructorEmailService
{
    public static function generateEmail($firstName, $lastName, $domain = 'instructors.rhu.edu.lb')
    {
        $cleanFirstName = preg_replace('/[^a-zA-Z]/', '', $firstName);
        $cleanLastName = preg_replace('/[^a-zA-Z]/', '', $lastName);

        if (empty($cleanFirstName) || empty($cleanLastName)) {
            throw new \Exception("Invalid name input for email generation.");
        }

        $firstPart = strtolower(substr($cleanFirstName, 0, 2));
        if (strlen($firstPart) < 2) {
            $firstPart .= 'x';
        }

        $lastPart = strtolower(substr($cleanLastName, 0, 6)); 

        $baseEmail = "{$lastPart}{$firstPart}@{$domain}";
        $email = $baseEmail;
        $count = 1;

        while (User::where('email', $email)->orWhere('personal_email', $email)->exists()) {
            $email = "{$lastPart}{$firstPart}{$count}@{$domain}";
            $count++;
        }

        return $email;
    }
}
