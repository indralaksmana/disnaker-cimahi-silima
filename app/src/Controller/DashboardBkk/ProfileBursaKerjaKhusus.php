<?php

namespace App\Controller\DashboardBkk;

use Carbon\Carbon;
use App\Controller\Controller;
use Psr\Container\ContainerInterface;
use App\Model\Users;
use App\Model\Daerah;
use App\Model\UsersProfile;
use App\Model\BkkModel;
use App\Model\DataProgramKejuruanModel;
use Illuminate\Database\Capsule\Manager as DB;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

use Slim\Views\PhpRenderer;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class ProfileBursaKerjaKhusus extends Controller
{
    public function profile(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('bkk.view')) {
            return $check;
        }
        $bkk_id = $this->auth->check()->bkk_id;
        $bkk =  BkkModel::with('user')->find($bkk_id);
        $imgs = explode('#', $bkk->gallerys_photo);
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

            //$Slug = $request->getParam('slug');
            $Slug = Utils::slugify( $r['nama_bkk'] );

            $checkSlug = BkkModel::where('id', '!=', $bkk_id)
                ->where('slug', '=', $Slug )
                ->get()
                ->count();
            if ($checkSlug > 0 && $Slug != $update->slug ) {
                $this->validator->addError('slug', 'Slug Sudah Di Gunakan.');
            }

            if ($this->validator->isValid()) {
                $user = $this->auth->check();
                $update = new BkkModel;
                $update = $update->where('id', '=', $bkk_id)->first();
                if ($update) {
                    $update->nama_bkk =  $r['nama_bkk'];
                    $update->slug =  $Slug;
                    $update->akreditasi= $r['akreditasi'] ? $r['akreditasi'] : NULL;
                    $update->alamat =  $r['alamat'];
                    $update->gmap_embed = $r['gmap_embed'];
                    $update->provinsi_id =  $r['provinsi_id'] ? $r['provinsi_id'] : NULL;
                    $update->kabupatenkota_id =  $r['kabupatenkota_id'] ?  $r['kabupatenkota_id'] : NULL;
                    $update->kecamatan_id =  $r['kecamatan_id'] ? $r['kecamatan_id'] : NULL;
                    $update->kelurahan_id =  $r['kelurahan_id'] ? $r['kelurahan_id'] : NULL;
                    $update->no_telp=  $r['no_telp'];
                    $update->fax =  $r['fax'];
                    $update->email =  $r['email'];
                    $update->no_izin =  $r['no_izin'];
                    $update->tanggal_izin_dari =  $r['tanggal_izin_dari'] ? $r['tanggal_izin_dari'] : NULL;
                    $update->tanggal_izin_sampai =  $r['tanggal_izin_sampai'] ? $r['tanggal_izin_sampai'] : NULL;
                    $update->nama_kepala_bkk =  $r['nama_kepala_bkk'];
                    $update->no_telp_kepala_bkk =  $r['no_telp_kepala_bkk'];
                    $update->email_kepala_bkk =  $r['email_kepala_bkk'];
                    $update->keterangan =  $r['keterangan'];
                    $update->profile_bkk=  $r['profile_bkk'];
                    $update->jurusan_bkk=  $r['jurusan_bkk'];
                    $update->fasilitas_bkk=  $r['fasilitas_bkk'];
                    $files = $this->container->request->getUploadedFiles();
                    if (isset($files['logo_upload']->file) && $files['logo_upload']->file != '') {
                      $update->logo = $this->upload_image($files['logo_upload']->file, '/files/bkk/logo');
                    } else {
                      $update->logo = isset($r['logo']) ? $r['logo'] : NULL;
                    }
                    
                  $update->save();
                }
                if (!$update) {
                    $addBkk = new BkkModel;
                    // $addBkk->user_id = $user->id;
                    // $addBkk->id = $user->id;
                    $addBKK->nama_bkk =  $r['nama_bkk'];
                    $addBKK->slug =  $Slug;
                    $addBKK->alamat =  $r['alamat'];
                    $addBKK->gmap_embed = $r['gmap_embed'];
                    $addBKK->akreditasi=  $r['akreditasi'] ? $r['akreditasi'] : NULL;
                    $addBKK->provinsi_id =  $r['provinsi_id'] ? $r['provinsi_id'] : NULL;
                    $addBKK->kabupatenkota_id =  $r['kabupatenkota_id'] ?  $r['kabupatenkota_id'] : NULL;
                    $addBKK->kecamatan_id =  $r['kecamatan_id'] ? $r['kecamatan_id'] : NULL;
                    $addBKK->kelurahan_id =  $r['kelurahan_id'] ? $r['kelurahan_id'] : NULL;
                    $addBKK->no_telp=  $r['no_telp'];
                    $addBKK->fax=  $r['fax'];
                    $addBKK->email=  $r['email'];
                    $addBKK->no_izin=  $r['no_izin'];
                    $addBKK->tanggal_izin_dari=  $r['tanggal_izin_dari'] ? $r['tanggal_izin_dari'] : NULL;
                    $addBKK->tanggal_izin_sampai=  $r['tanggal_izin_sampai'] ? $r['tanggal_izin_sampai'] : NULL;
                    $addBKK->nama_kepala_bkk=  $r['nama_kepala_bkk'];
                    $addBKK->no_telp_kepala_bkk=  $r['no_telp_kepala_bkk'];
                    $addBKK->email_kepala_bkk=  $r['email_kepala_bkk'];
                    $addBKK->keterangan=  $r['keterangan'];
                    $addBKK->profile_bkk=  $r['profile_bkk'];
                    $addBKK->jurusan_bkk=  $r['jurusan_bkk'];
                    $addBKK->fasilitas_bkk=  $r['fasilitas_bkk'];
                    $files = $this->request->getUploadedFiles();
                    if (isset($files['logo_upload']->file) && $files['logo_upload']->file != '') {
                      $addBKK->logo = $this->upload_image($files['logo_upload']->file, '/files/bkk/logo');
                    } else {
                      $addBKK->logo = isset($r['logo']) ? $r['logo'] : NULL;
                    }
                    $addBkk->save();
                }
                if ($addBkk || $update) {
                  $this->flash('success', 'Profil BKK Berhasil di Update');
                  return $this->redirect($response, 'profile-bkk');
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
            $response, 'bkol/dashboard-bkk/profile-bkk.twig',
            array(
                "imgs" => $imgs,
                "daerahs" => $daerahs,
                "bkk" => $bkk,
            )
        );
    }
    public function listProgramKejuruan(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('bkk.view')) {
          return $check;
        }
        $user = $this->auth->check();
        $bkk =  BkkModel::with('programkejuruan')->find($user->bkk_id)->first();
        return $this->view->render(
            $response,
            'bkk/program-kejuruan/list.twig',
            array(
                "bkk" => $bkk,
            )
        );
    }
    public function AddPP(Request $request, Response $response)
    {
        $r = $this->request->getParams();
        $user = $this->auth->check();
        $bkk =  BkkModel::with('programkejuruan')->find($user->bkk_id)->first();
        if ($request->isPost()) {
            if ($this->validator->isValid()) {
            	  $add = new DataProgramKejuruanModel;
                $add->bkk_id =  $bkk->id;
                $add->nama_kejuruan = $r['nama_kejuruan'];
                $add->jumlah_lulusan_yang_bekerja_laki = $r['jumlah_lulusan_yang_bekerja_laki'] ? $r['jumlah_lulusan_yang_bekerja_laki'] : 0;
                $add->jumlah_lulusan_yang_bekerja_perempuan = $r['jumlah_lulusan_yang_bekerja_perempuan'] ? $r['jumlah_lulusan_yang_bekerja_perempuan'] : 0;
                $add->jumlah_lulusan_yang_belum_bekerja_laki = $r['jumlah_lulusan_yang_belum_bekerja_laki'] ? $r['jumlah_lulusan_yang_belum_bekerja_laki'] : 0;
                $add->jumlah_lulusan_yang_belum_bekerja_perempuan = $r['jumlah_lulusan_yang_belum_bekerja_perempuan'] ? $r['jumlah_lulusan_yang_belum_bekerja_perempuan'] : 0;
                $add->jumlah_lulusan_wirausaha_laki= $r['jumlah_lulusan_wirausaha_laki'] ? $r['jumlah_lulusan_wirausaha_laki'] : 0;
                $add->jumlah_lulusan_wirausaha_perempuan = $r['jumlah_lulusan_wirausaha_perempuan'] ? $r['jumlah_lulusan_wirausaha_perempuan'] : 0;
                $add->jumlah_lulusan_kuliah_laki = $r['jumlah_lulusan_kuliah_laki'] ? $r['jumlah_lulusan_kuliah_laki'] : 0;
                $add->jumlah_lulusan_kuliah_perempuan = $r['jumlah_lulusan_kuliah_perempuan'] ? $r['jumlah_lulusan_kuliah_perempuan'] : 0;
                $add->jumlah_peserta_didik_laki = $add->jumlah_lulusan_yang_bekerja_laki + $add->jumlah_lulusan_yang_belum_bekerja_laki + $add->jumlah_lulusan_wirausaha_laki + $add->jumlah_lulusan_kuliah_laki;
                $add->jumlah_peserta_didik_perempuan = $add->jumlah_lulusan_yang_bekerja_perempuan + $add->jumlah_lulusan_yang_belum_bekerja_perempuan + $add->jumlah_lulusan_wirausaha_perempuan + $add->jumlah_lulusan_kuliah_perempuan;
                $add->jumlah_semua_peserta = $add->jumlah_peserta_didik_laki + $add->jumlah_peserta_didik_perempuan;
                $add->tahun = $r['tahun'];
                $add->tahun_end = $r['tahun_end'];
                $add->keterangan = $r['keterangan'];
                $add->save();
                if ($add) {
                  $this->flash('success', 'Data Program Kejuruan Baru Sudah Di tambahkan');
                  return $this->redirect($response, 'list-program-kejuruan-bkk');
                }
                $this->flashNow('danger', 'Gagal Menambahkan');
            }


        }
        return $this->view->render(
            $response,
            'bkk/program-kejuruan/add.twig'

        );
    }

    public function EditPP(Request $request, Response $response, $ppId)
    {
        $r = $this->request->getParams();
        $pp = DataProgramKejuruanModel::where('id', $ppId)->first();
        if (!$pp) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'list-program-kejuruan-bkk');
        }
        if ($request->isPost()) {
          $update = DataProgramKejuruanModel::find($ppId);
          if (!$update) {
              return false;
          }
          if ($this->validator->isValid()) {
              $update->nama_kejuruan = $r['nama_kejuruan'];
              $update->jumlah_lulusan_yang_bekerja_laki = $r['jumlah_lulusan_yang_bekerja_laki'] ? $r['jumlah_lulusan_yang_bekerja_laki'] : 0;
              $update->jumlah_lulusan_yang_bekerja_perempuan = $r['jumlah_lulusan_yang_bekerja_perempuan'] ? $r['jumlah_lulusan_yang_bekerja_perempuan'] : 0;
              $update->jumlah_lulusan_yang_belum_bekerja_laki = $r['jumlah_lulusan_yang_belum_bekerja_laki'] ? $r['jumlah_lulusan_yang_belum_bekerja_laki'] : 0;
              $update->jumlah_lulusan_yang_belum_bekerja_perempuan = $r['jumlah_lulusan_yang_belum_bekerja_perempuan'] ? $r['jumlah_lulusan_yang_belum_bekerja_perempuan'] : 0;
              $update->jumlah_lulusan_wirausaha_laki= $r['jumlah_lulusan_wirausaha_laki'] ? $r['jumlah_lulusan_wirausaha_laki'] : 0;
              $update->jumlah_lulusan_wirausaha_perempuan = $r['jumlah_lulusan_wirausaha_perempuan'] ? $r['jumlah_lulusan_wirausaha_perempuan'] : 0;
              $update->jumlah_lulusan_kuliah_laki= $r['jumlah_lulusan_kuliah_laki'] ? $r['jumlah_lulusan_kuliah_laki'] : 0;
              $update->jumlah_lulusan_kuliah_perempuan = $r['jumlah_lulusan_kuliah_perempuan'] ?  $r['jumlah_lulusan_kuliah_perempuan'] : 0;
              $update->jumlah_peserta_didik_laki = $update->jumlah_lulusan_yang_bekerja_laki + $update->jumlah_lulusan_yang_belum_bekerja_laki + $update->jumlah_lulusan_wirausaha_laki + $update->jumlah_lulusan_kuliah_laki;
              $update->jumlah_peserta_didik_perempuan = $update->jumlah_lulusan_yang_bekerja_perempuan + $update->jumlah_lulusan_yang_belum_bekerja_perempuan + $update->jumlah_lulusan_wirausaha_perempuan + $update->jumlah_lulusan_kuliah_perempuan;
              $update->jumlah_semua_peserta = $update->jumlah_peserta_didik_laki + $update->jumlah_peserta_didik_perempuan;
              $update->tahun = $r['tahun'];
              $update->tahun_end = $r['tahun_end'];
              $update->keterangan = $r['keterangan'];
              $update->save();
              if ($update) {
                $this->flash('success', 'Data Program Kejuruan Baru Sudah Di tambahkan');
                return $this->redirect($response, 'list-program-kejuruan-bkk');
              }
              $this->flashNow('danger', 'Gagal Menambahkan');

          }
        }
        return $this->view->render(
            $response,
            'bkk/program-kejuruan/edit.twig',
            [
                "pp" => $pp
            ]
        );
    }
    public function DeletePP(Request $request, Response $response, $ppId)
    {
      $pp = DataProgramKejuruanModel::where('id', $ppId)->first();
      if (!$pp) {
          $this->flash('danger', 'That post does not exist.');
          return $this->redirect($response, 'list-program-kejuruan-bkk');
      }
      if ($request->isPost()) {
          $update = DataProgramKejuruanModel::find($ppId);
          if (!$update) {
              return false;
          }
          if ($this->validator->isValid()) {
              $update->delete();
              $this->flash('success', 'Data Program Kejuruan Berhasil Di Hapus');
              return $this->redirect($response, 'list-program-kejuruan-bkk');
          }
      }
      return $this->view->render(
          $response,
          'dashboard/bkk/program-kejuruan/delete.twig',
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
