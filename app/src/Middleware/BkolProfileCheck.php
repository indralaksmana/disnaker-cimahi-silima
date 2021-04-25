<?php

namespace App\Middleware;

use App\Model\Users;

class BkolProfileCheck extends Middleware
{
    public function __invoke($request, $response, $next)
    {
        if ($this->auth->check()) {
            if ($this->auth->check()->inRole('jobseeker')) {
                $user = $this->auth->check();
                $err = 0;

                // if (!$user->fullname || $user->fullname == "") {
                //     $err++;
                // }
                if (!$user->email || $user->email == "") {
                    $err++;
                }
                if (!$user->datapencarikerja->no_ktp || $user->datapencarikerja->no_ktp == "") {
                    $err++;
                }
                if (!$user->datapencarikerja->nama_lengkap || $user->datapencarikerja->nama_lengkap == "") {
                    $err++;
                }
                if (!$user->datapencarikerja->tempat_lahir || $user->datapencarikerja->tempat_lahir == "") {
                    $err++;
                }
                if (!$user->datapencarikerja->alamat_lengkap || $user->datapencarikerja->alamat_lengkap == "") {
                    $err++;
                }
                if (!$user->datapencarikerja->kecamatan || $user->datapencarikerja->kecamatan == "") {
                    $err++;
                }
                if (!$user->datapencarikerja->kelurahan || $user->datapencarikerja->kelurahan == "") {
                    $err++;
                }
                if (!$user->datapencarikerja->no_telp || $user->datapencarikerja->no_telp == "") {
                    $err++;
                }
                if (!$user->datapencarikerja->kodepos || $user->datapencarikerja->kodepos == "") {
                    $err++;
                }
                if ($err) {
                    $this->flash->addMessage(
                        'warning',
                        'Ups! Tampaknya Profile Anda kehilangan beberapa informasi..'.
                            '  Harap tinjau / perbaiki profil Anda di bawah ini untuk melanjutkan.'
                    );
                    return $response->withRedirect($this->router->pathFor('dashboardpencaker-biodata'));
                }
            } elseif ($this->auth->check()->inRole('companies')) {
                $user = $this->auth->check();
                $err = 0;

                if (!$user->companiesprofile->companyname || $user->companiesprofile->companyname == "") {
                    $err++;
                }
                if (!$user->companiesprofile->companysaddress || $user->companiesprofile->companysaddress == "") {
                    $err++;
                }
                if (!$user->companiesprofile->provinsi || $user->companiesprofile->provinsi == "") {
                    $err++;
                }

                if (!$user->companiesprofile->kabupatenkota || $user->companiesprofile->kabupatenkota == "") {
                    $err++;
                }
                if (!$user->companiesprofile->kecamatan || $user->companiesprofile->kecamatan == "") {
                    $err++;
                }
                if (!$user->companiesprofile->kelurahan || $user->companiesprofile->kelurahan == "") {
                    $err++;
                }
                /*if (!$user->companiesprofile->industry || $user->companiesprofile->industry == "") {
                    $err++;
                }*/

                if (!$user->email || $user->email == "") {
                    $err++;
                }
                if ($err) {
                    $this->flash->addMessage(
                        'warning',
                        'Ups! Tampaknya Profile Anda kehilangan beberapa informasi..'.
                            '  Harap tinjau / perbaiki profil Anda di bawah ini untuk melanjutkan.'
                    );
                    return $response->withRedirect($this->router->pathFor('dashboardperusahaan-profile'));
                }
            } elseif ($this->auth->check()->inRole('admin')) {
                $user = $this->auth->check();
                $err = 0;
                if ($err) {
                    $this->flash->addMessage(
                        'warning',
                        'Ups! Tampaknya Profile Anda kehilangan beberapa informasi..'.
                            '  Harap tinjau / perbaiki profil Anda di bawah ini untuk melanjutkan.'
                    );
                    return $response->withRedirect($this->router->pathFor('home-bkol'));
                }
            } elseif ($this->auth->check()->inRole('manager')) {
                $user = $this->auth->check();
                $err = 0;
                if ($err) {
                    $this->flash->addMessage(
                        'warning',
                        'Ups! Tampaknya Profile Anda kehilangan beberapa informasi..'.
                            '  Harap tinjau / perbaiki profil Anda di bawah ini untuk melanjutkan.'
                    );
                    return $response->withRedirect($this->router->pathFor('home'));
                }
            } else {
                return $response->withRedirect($this->router->pathFor('home'));
            }

        } elseif ($this->auth->check()) {
            return $response->withRedirect($this->router->pathFor('home'));
        }


        return $next($request, $response);
    }
}
