<?php

namespace App\Library;

use Carbon\Carbon;
use App\Model\HitApiDisdukcapil;

class DisdukApi extends Library
{
    CONST API_URL = "https://gsb.cimahikota.go.id/api/disduk/s_dwh_disnaker/fn_get_penduduk";
    CONST API_ACCESS_KEY = 'cebyyu10kq';
    CONST API_AGENT = 'MANTRA';
    CONST ALLOW_IDENTITY = ['1607102012920005', '3204351204880005', '1802072111830004'];

    public function getIndentitasPenduduk($nik){
        $agent=self::API_AGENT;
        $uri = self::API_URL;
        $http_pars = array();
        $http_pars = http_build_query(array("nik"=>$nik,"headers"=>""));
        $content_type = "Content-Type:application/x-www-form-urlencoded;charset=UTF-8";
        $accesskey = self::API_ACCESS_KEY;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);

        // Output dengan header=true hanya untuk meta document (xml/json)
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($content_type,"AccessKey:".$accesskey));
        
        // Mendapatkan tanggapan
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, FALSE);
        curl_setopt($ch, CURLOPT_FAILONERROR, FALSE); // TRUE untuk menangkap error message
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$http_pars);

        // Buka koneksi dan dapatkan tanggapan
        $content = curl_exec($ch);
        $errno = curl_errno($ch);
        $errmsg = curl_strerror($errno);

        // Periksa kesalahan
        if ($errno!=0){
            $response = array('status'=>0,'code'=>$errno,'message'=>$errmsg,'data'=>'');
        }
        else{
            $response = $content;
        }

        // decode api result
        $apiResponse = json_decode($response);

        // jika hasil decode response nya null akibat kesalahan config atau api yang tak merespon
        if($apiResponse == NULL) {
            $content_result = "Terjadi kesalahan saat request ke API";
            $result = 'False'; 
        } else {
            // jika data tidak ditemukan 
            if (array_key_exists('RESPONSE_CODE', $apiResponse->content[0])) {
                $content_result = $this->translateResponse($apiResponse, FALSE);
                $result = 'False';
            } else {
                // jika data ditemukan
                // dan yang register orang cimahi
                if (strpos($apiResponse->content[0]->KAB_NAME, 'CIMAHI') !== FALSE || in_array($apiResponse->content[0]->NIK, self::ALLOW_IDENTITY)) {
                    $content_result = $this->translateResponse($apiResponse, TRUE);
                    $result = 'True';
                } else {
                    // jika yang register bukan orang cimahi
                    $content_result = $this->translateResponse($apiResponse, TRUE);
                    $result = 'False';
                }
            }
        }
        
        $hitApi = new HitApiDisdukcapil;
        $hitApi->nik = $nik;
        $hitApi->hit_access = Carbon::now();
        $hitApi->create = Carbon::now()->toTimeString();
        $hitApi->result = $result;
        $hitApi->content_result = json_encode($content_result);
        $hitApi->save();
        
        return $apiResponse;
    }

    public function translateResponse ($response, $found) {
        if ($found) {
            return (Object) [
                'NIK' => $response->content[0]->NIK,
                'NAMA_LGKP' => $response->content[0]->NAMA_LGKP,
                'JENIS_KLMIN' => $response->content[0]->JENIS_KLMIN,
                'TGL_LHR' => $response->content[0]->TGL_LHR,
                'TMPT_LHR' => $response->content[0]->TMPT_LHR,
                'AGAMA' => $response->content[0]->AGAMA,
                'STATUS_KAWIN' => $response->content[0]->STATUS_KAWIN,
                'ALAMAT' => $response->content[0]->ALAMAT,
                'KAB_NAME' => $response->content[0]->KAB_NAME,
                'KEC_NAME' => $response->content[0]->KEC_NAME,
                'KEL_NAME' => $response->content[0]->KEL_NAME,
                'PROP_NAME' => $response->content[0]->PROP_NAME
            ];
        } else {
            return (Object) [
                'RESPONSE_CODE' => $response->content[0]->RESPONSE_CODE,
                'RESPONSE_DESC' => $response->content[0]->RESPONSE_DESC
            ];
        }
    }
}