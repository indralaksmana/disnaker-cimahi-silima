<?php
namespace App\Library;

final class Session
{
    // Save SESSION variable
    public function updateSession($row)
    {
        $_SESSION['user'] = [
            'login' => true,
            'id' => $row->id,
            'username' => $row->username,
            'nickname' => $row->nickname,
            'lastlogin' => $row->lastlogin_date,
            'role' => $row->role,
            'level' => $row->level,
            'country' => $row->country_iso,
            'currency' => $row->currency,
            'lang' => $row->language,
            'type' => $row->usertype,
            'photo_profiles' =>  $row->photo_profiles,
        ];
    }
    
}
