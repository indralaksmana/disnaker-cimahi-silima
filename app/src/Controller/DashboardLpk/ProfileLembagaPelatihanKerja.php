<?php

namespace App\Controller\DashboardLpk;

use Carbon\Carbon;
use App\Controller\Controller;
use Psr\Container\ContainerInterface;
use App\Model\ContactRequests;
use App\Model\Oauth2Providers;
use App\Model\Users;
use App\Model\Daerah;
use App\Model\LpkModel;
use App\Model\DataProgramPelatihanModel;
use Illuminate\Database\Capsule\Manager as DB;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

use Slim\Views\PhpRenderer;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class ProfileLembagaPelatihanKerja extends Controller
{
    public function profile(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('lpk.view')) {
            return $check;
        }
        $lpk_id = $this->auth->check()->lpk_id;
        $user = $this->auth->check();
        $lpk =  LpkModel::with('user')->find($lpk_id);
        $imgs = explode('#', $lpk->gallerys_photo);
        $r = $this->request->getParams();
        if ($request->isPost()) {
            // Validate Data
            $validateData = array(
                // 'first_name' => array(
                //     'rules' => V::length(2, 25)->alnum('\'?!@#,."'),
                //     'messages' => array(
                //         'length' => 'Must be between 2 and 25 characters.',
                //         'alnum' => 'Contains an invalid character.'
                //         )
                // ),
            );

            $this->validator->validate($request, $validateData);

            //$Slug = $request->getParam('slug');

            $Slug = Utils::slugify( $r['nama_lpk'] );

            $checkSlug = LpkModel::where('id', '!=', $lpk_id)
                ->where('slug', '=', $Slug )
                ->get()
                ->count();
            if ($checkSlug > 0 && $Slug != $update->slug ) {
                $this->validator->addError('slug', 'Slug Sudah Di Gunakan.');
            }
            if ($this->validator->isValid()) {
                $update = new LpkModel;
                $update = $update->where('id', '=', $lpk_id)->first();
                if ($update) {
                    $update->nama_lpk =  $r['nama_lpk'];
                    $update->slug =  $Slug;
                    $update->alamat=  $r['alamat'];
                    $update->gmap_embed = $r['gmap_embed'];
                    $update->akreditasi = $r['akreditasi'] ? $r['akreditasi'] : NULL;
                    $update->provinsi_id =  $r['provinsi_id'] ? $r['provinsi_id'] : NULL;
                    $update->kabupatenkota_id =  $r['kabupatenkota_id'] ? $r['kabupatenkota_id'] : NULL;
                    $update->kecamatan_id =  $r['kecamatan_id'] ? $r['kecamatan_id'] : NULL;
                    $update->kelurahan_id =  $r['kelurahan_id'] ? $r['kelurahan_id'] : NULL;
                    $update->no_telp=  $r['no_telp'];
                    $update->fax=  $r['fax'];
                    $update->email=  $r['email'];
                    $update->no_izin=  $r['no_izin'];
                    $update->tanggal_izin_dari=  $r['tanggal_izin_dari'] ? $r['tanggal_izin_dari'] : NULL;
                    $update->tanggal_izin_sampai=  $r['tanggal_izin_sampai'] ? $r['tanggal_izin_sampai'] : NULL;
                    $update->nama_kepala_lpk=  $r['nama_kepala_lpk'];
                    $update->no_telp_kepala_lpk=  $r['no_telp_kepala_lpk'];
                    $update->email_kepala_lpk=  $r['email_kepala_lpk'];
                    $update->keterangan=  $r['keterangan'];
                    $update->profile_lpk=  $r['profile_lpk'] ? $r['profile_lpk'] : '' ;
                    $update->jurusan_lpk=  $r['jurusan_lpk'];
                    $update->fasilitas_lpk=  $r['fasilitas_lpk'];
                    //tambahan
                    $update->no_tanda_daftar = $r['no_tanda_daftar'];
                    $update->no_registrasi_lpk = $r['no_registrasi_lpk'];
                    $update->no_vin = $r['no_vin'];
                    $update->no_sert_akreditasi = $r['no_sert_akreditasi'];

                    $files = $this->request->getUploadedFiles();
                    if (isset($files['logo_upload']->file) && $files['logo_upload']->file != '') {
                      $update->logo = $this->upload_image($files['logo_upload']->file, '/files/lpk/logo');
                    } else {
                      $update->logo = isset($r['logo']) ? $r['logo'] : NULL;
                    }
                    $update->save();
                }

                if (!$update) {
                    $addLpk = new LpkModel;
                    $addLpk->nama_lpk =  $r['nama_lpk'];
                    $addLpk->alamat=  $r['alamat'];
                    $addLpk->gmap_embed = $r['gmap_embed'];
                    $addLpk->akreditasi = $r['akreditasi'] ? $r['akreditasi'] : NULL;
                    $addLpk->provinsi_id =  $r['provinsi_id'] ? $r['provinsi_id'] : NULL;
                    $addLpk->kabupatenkota_id=  $r['kabupatenkota_id'] ? $r['kabupatenkota_id'] : NULL;
                    $addLpk->kecamatan_id=  $r['kecamatan_id'] ? $r['kecamatan_id'] : NULL;
                    $addLpk->kelurahan_id=  $r['kelurahan_id'] ? $r['kelurahan_id'] : NULL;
                    $addLpk->no_telp=  $r['no_telp'];
                    $addLpk->fax=  $r['fax'];
                    $addLpk->slug =  $Slug;
                    $addLpk->email=  $r['email'];
                    $addLpk->no_izin=  $r['no_izin'];
                    $addLpk->tanggal_izin_dari=  $r['tanggal_izin_dari'] ? $r['tanggal_izin_dari'] : NULL;
                    $addLpk->tanggal_izin_sampai=  $r['tanggal_izin_sampai'] ? $r['tanggal_izin_sampai'] : NULL;
                    $addLpk->nama_kepala_lpk=  $r['nama_kepala_lpk'];
                    $addLpk->no_telp_kepala_lpk=  $r['no_telp_kepala_lpk'];
                    $addLpk->email_kepala_lpk=  $r['email_kepala_lpk'];
                    $addLpk->keterangan=  $r['keterangan'];
                    $addLpk->profile_lpk=  $r['profile_lpk'];;
                    $addLpk->jurusan_lpk=  $r['jurusan_lpk'];
                    $addLpk->fasilitas_lpk=  $r['fasilitas_lpk'];
                    //tambahan
                    $addLpk->no_tanda_daftar = $r['no_tanda_daftar'];
                    $addLpk->no_registrasi_lpk = $r['no_registrasi_lpk'];
                    $addLpk->no_vin = $r['no_vin'];
                    $addLpk->no_sert_akreditasi = $r['no_sert_akreditasi'];
                    $files = $this->request->getUploadedFiles();
                    if (isset($files['logo_upload']->file) && $files['logo_upload']->file != '') {
                      $addLpk->logo = $this->upload_image($files['logo_upload']->file, '/files/lpk/logo');
                    } else {
                      $addLpk->logo = isset($r['logo']) ? $r['logo'] : NULL;
                    }
                    $addLpk->save();
                }
                if ($addLpk || $update) {
                  $this->flash('success', 'Profil LPK Berhasil di Update');
                  return $this->redirect($response, 'profile-lpk');
                }
                $this->flashNow('danger', 'There was an error updating your account information.');
            }
        }

        $daerahs = Daerah::where('lokasi_kabupatenkota', 0)
        ->where('lokasi_kecamatan', 0)
        ->where('lokasi_kelurahan', 0)
        ->orderBy('lokasi_nama')
        ->get();
        return $this->view->render(
            $response, 'bkol/dashboard-lpk/profile-lpk.twig',
            array(
                "imgs" => $imgs,
                "daerahs" => $daerahs,
                "lpk" => $lpk
            )
        );
    }
    public function listProgramPelatihan(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('lpk.view')) {
          return $check;
        }
        $user = $this->auth->check();
        $lpk =  LpkModel::with('programpelatihan')->find($user->lpk_id)->first();
        return $this->view->render(
            $response,
            'lpk/program-pelatihan/list.twig',
            array(
                "lpk" => $lpk
            )
        );
    }
    public function AddPP(Request $request, Response $response)
    {
        $r = $this->request->getParams();
        $user = $this->auth->check();
        $lpk =  LpkModel::with('programpelatihan')->find($user->lpk_id)->first();
        if ($request->isPost()) {
            if ($this->validator->isValid()) {
            	  $add = new DataProgramPelatihanModel;
                $add->lpk_id =  $lpk->id;
                $add->nama_program = $r['nama_program'];
                $add->jumlah_lulusan_yang_bekerja_laki = $r['jumlah_lulusan_yang_bekerja_laki'] ? $r['jumlah_lulusan_yang_bekerja_laki'] : 0;
                $add->jumlah_lulusan_yang_bekerja_perempuan = $r['jumlah_lulusan_yang_bekerja_perempuan'] ? $r['jumlah_lulusan_yang_bekerja_perempuan'] : 0;
                $add->jumlah_lulusan_yang_belum_bekerja_laki = $r['jumlah_lulusan_yang_belum_bekerja_laki'] ? $r['jumlah_lulusan_yang_belum_bekerja_laki'] : 0;
                $add->jumlah_lulusan_yang_belum_bekerja_perempuan = $r['jumlah_lulusan_yang_belum_bekerja_perempuan'] ? $r['jumlah_lulusan_yang_belum_bekerja_perempuan'] : 0;
                $add->jumlah_lulusan_lainya_laki= $r['jumlah_lulusan_lainya_laki'] ? $r['jumlah_lulusan_lainya_laki'] : 0;
                $add->jumlah_lulusan_lainya_perempuan = $r['jumlah_lulusan_lainya_perempuan'] ? $r['jumlah_lulusan_lainya_perempuan'] : 0;
                $add->jumlah_peserta_didik_laki = $add->jumlah_lulusan_yang_bekerja_laki + $add->jumlah_lulusan_yang_belum_bekerja_laki + $add->jumlah_lulusan_lainya_laki;
                $add->jumlah_peserta_didik_perempuan = $add->jumlah_lulusan_yang_bekerja_perempuan + $add->jumlah_lulusan_yang_belum_bekerja_perempuan +  $add->jumlah_lulusan_lainya_perempuan;
                $add->jumlah_peserta = $add->jumlah_peserta_didik_laki + $add->jumlah_peserta_didik_perempuan;
                $add->tahun = $r['tahun'];
                $add->keterangan = $r['keterangan'];
                $add->save();
                if ($add) {
                  $this->flash('success', 'Data Program Kejuruan Baru Sudah Di tambahkan');
                  return $this->redirect($response, 'list-program-pelatihan-lpk');
                }
                $this->flashNow('danger', 'Gagal Menambahkan');
            }


        }
        return $this->view->render(
            $response,
            'lpk/program-pelatihan/add.twig'

        );
    }

    public function EditPP(Request $request, Response $response, $ppId)
    {
        $r = $this->request->getParams();
        $pp = DataProgramPelatihanModel::where('id', $ppId)->first();

        if (!$pp) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'list-program-pelatihan-lpk');
        }
        if ($request->isPost()) {
          $update = DataProgramPelatihanModel::find($ppId);
          if (!$update) {
              return false;
          }
          if ($this->validator->isValid()) {
              $update->nama_program = $r['nama_program'];
              $update->jumlah_lulusan_yang_bekerja_laki = $r['jumlah_lulusan_yang_bekerja_laki'] ? $r['jumlah_lulusan_yang_bekerja_laki'] : 0;
              $update->jumlah_lulusan_yang_bekerja_perempuan = $r['jumlah_lulusan_yang_bekerja_perempuan'] ? $r['jumlah_lulusan_yang_bekerja_perempuan'] : 0;
              $update->jumlah_lulusan_yang_belum_bekerja_laki = $r['jumlah_lulusan_yang_belum_bekerja_laki'] ? $r['jumlah_lulusan_yang_belum_bekerja_laki'] : 0;
              $update->jumlah_lulusan_yang_belum_bekerja_perempuan = $r['jumlah_lulusan_yang_belum_bekerja_perempuan'] ? $r['jumlah_lulusan_yang_belum_bekerja_perempuan'] : 0;
              $update->jumlah_lulusan_lainya_laki= $r['jumlah_lulusan_lainya_laki'] ? $r['jumlah_lulusan_lainya_laki'] : 0;
              $update->jumlah_lulusan_lainya_perempuan = $r['jumlah_lulusan_lainya_perempuan'] ? $r['jumlah_lulusan_lainya_perempuan'] : 0;
              $update->jumlah_peserta_didik_laki = $update->jumlah_lulusan_yang_bekerja_laki + $update->jumlah_lulusan_yang_belum_bekerja_laki + $update->jumlah_lulusan_lainya_laki;
              $update->jumlah_peserta_didik_perempuan = $update->jumlah_lulusan_yang_bekerja_perempuan + $update->jumlah_lulusan_yang_belum_bekerja_perempuan +  $update->jumlah_lulusan_lainya_perempuan;
              $update->jumlah_peserta = $update->jumlah_peserta_didik_laki + $update->jumlah_peserta_didik_perempuan;
              $update->tahun = $r['tahun'];
              $update->keterangan = $r['keterangan'];
              $update->save();
              if ($update) {
                $this->flash('success', 'Data Program Pelatihan Baru Sudah Di tambahkan');
                return $this->redirect($response, 'list-program-pelatihan-lpk');
              }
              $this->flashNow('danger', 'Gagal Menambahkan');

          }
        }
        return $this->view->render(
            $response,
            'lpk/program-pelatihan/edit.twig',
            [
                "pp" => $pp
            ]
        );
    }
    public function DeletePP(Request $request, Response $response, $ppId)
    {
      $pp = DataProgramPelatihanModel::where('id', $ppId)->first();
      if (!$pp) {
          $this->flash('danger', 'That post does not exist.');
          return $this->redirect($response, 'list-program-pelatihan-lpk');
      }
      if ($request->isPost()) {
          $update = DataProgramPelatihanModel::find($ppId);
          if (!$update) {
              return false;
          }
          if ($this->validator->isValid()) {
              $update->delete();
              $this->flash('success', 'Data Program Kejuruan Berhasil Di Hapus');
              return $this->redirect($response, 'list-program-pelatihan-lpk');
          }
      }
      return $this->view->render(
          $response,
          'lpk/program-pelatihan/delete.twig',
          [
              "pp" => $pp
          ]

      );
    }
    private function upload_image($tmpfile, $subdir = NULL, $mime_allowed = array(
    'jpg' => 'image/jpeg',
    'png' => 'image/png',)
  ) {
    $this->dir = $this->public_dir;
    $dir = $this->dir;
    $path = '';

    $curdir = $dir;
    if (!file_exists($curdir) && !is_dir($curdir)) {
      mkdir($curdir);
    }
    if ($subdir !== NULL) {
      $subdirs = explode("/", $subdir);
      foreach ($subdirs as $sub) {
        $curdir = $curdir . '/' . $sub;
        if (!file_exists($curdir) && !is_dir($curdir)) {
          mkdir($curdir);
        }
      }
      $dir = $dir . $subdir;
      $path = $path . $subdir;
    }

    if (false === $ext = array_search(
        $this->get_mime_type($tmpfile), $mime_allowed, true
        )) {
      return false;
    }

    $dest_name = sha1_file($tmpfile);

    if (!move_uploaded_file(
            $tmpfile, sprintf('%s/%s.%s', $dir, $dest_name, $ext
            )
        )) {
      return false;
    }

    return $path . '/' . $dest_name . '.' . $ext;
  }

  private function get_mime_type($filepath) {
    // Check only existing files
    if (!file_exists($filepath) || !is_readable($filepath))
      return false;

    // Trying finfo
    if (function_exists('finfo_open')) {
      $finfo = finfo_open(FILEINFO_MIME);
      $mimeType = finfo_file($finfo, $filepath);
      finfo_close($finfo);
      // Mimetype can come in text/plain; charset=us-ascii form
      if (strpos($mimeType, ';'))
        list($mimeType, ) = explode(';', $mimeType);
      return $mimeType;
    }

    // Trying mime_content_type
    if (function_exists('mime_content_type')) {
      return mime_content_type($filepath);
    }

    // Trying exec
    if (function_exists('system')) {
      $mimeType = system("file -i -b $filepath");
      if (!empty($mimeType))
        return $mimeType;
    }

    // Trying to get mimetype from images
    $imageData = @getimagesize($filepath);
    if (!empty($imageData['mime'])) {
      return $imageData['mime'];
    }

    return false;
  }

}
