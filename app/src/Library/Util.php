<?php
namespace App\Library;

use Illuminate\Database\Capsule\Manager as DB;

final class Util
{

    public function getToken($length) {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet .= "0123456789";
        $max = strlen($codeAlphabet); // edited

        for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[$this->crypto_rand_secure(0, $max - 1)];
        }

        return $token;
    }

    private function crypto_rand_secure($min, $max) {
        $range = $max - $min;
        if ($range < 1)
            return $min; // not so random...
        $log = ceil(log($range, 2));
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd > $range);
        return $min + $rnd;
    }

    public function generatePassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyz1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    public function generateNoPencaker($nik) {
        $lastNumber = substr($nik, 0, 4);
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        
        $increment = DB::select(DB::raw("SELECT IFNULL((SELECT MAX(RIGHT(nomor_pencaker, 3)) FROM cb_pencaker_ak1 WHERE DATE_FORMAT(tanggal_daftar,'%Y%m') = :yearmonth), 0) + 1 AS nomor_urut FROM DUAL"), ['yearmonth' => date('Ym')]);
        $no_urut = $increment[0]->nomor_urut;
        
        switch (strlen($no_urut)) {
        case 1:
            $sort = "00".$no_urut;
            break;
        case 2:
            $sort = "0".$no_urut;
            break;
        case 3:
            $sort = $no_urut;
            break;
        default:
            $sort = $no_urut;
        }

        return $lastNumber.'/'.$year.'/'.$month.'/'.$day.'/'.$sort;
    }

}