<?php

namespace App\Controller\DashboardPerguruanTinggi;

use Carbon\Carbon;
use App\Controller\Controller;
use Psr\Container\ContainerInterface;
use App\Model\ContactRequests;
use App\Model\Oauth2Providers;
use App\Model\Users;
use App\Model\Daerah;
use App\Model\PerguruanTinggiModel;
use App\Model\DataProgramPelatihanModel;
use Illuminate\Database\Capsule\Manager as DB;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

use Slim\Views\PhpRenderer;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class ProfilePerguruanTinggi extends Controller
{
    public function profile(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('perguruan-tinggi.view')) {
            return $check;
        }
        $pt_id = $this->auth->check()->perguruan_tinggi_id;
        $pt =  PerguruanTinggiModel::with('user')->find($pt_id);
        $imgs = explode('#', $pt->gallerys_photo);
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
            $Slug = Utils::slugify( $r['nama_perguruan_tinggi'] );

            $checkSlug = PerguruanTinggiModel::where('id', '!=', $pt_id)
                ->where('slug', '=', $Slug )
                ->get()
                ->count();
            if ($checkSlug > 0 && $Slug != $update->slug ) {
                $this->validator->addError('slug', 'Slug Sudah Di Gunakan.');
            }

            if ($this->validator->isValid()) {
                $update = new PerguruanTinggiModel;
                $update = $update->where('id', '=', $pt_id)->first();
                if ($update) {
                    $update->nama_perguruan_tinggi =  $r['nama_perguruan_tinggi'];
                    $update->slug =  $Slug;
                    $update->no_izin=  $r['no_izin'];
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
                    $update->tahun_beridiri=  $r['tahun_beridiri'] ? $r['tahun_beridiri'] : NULL;
                    $update->tanggal_izin_dari=  $r['tanggal_izin_dari'] ? $r['tanggal_izin_dari'] : NULL;
                    $update->tanggal_izin_sampai=  $r['tanggal_izin_sampai'] ? $r['tanggal_izin_sampai'] : NULL;
                    $update->nama_pimpinan=  $r['nama_pimpinan'];
                    $update->no_telp_pimpinan=  $r['no_telp_pimpinan'];
                    $update->email_pimpinan=  $r['email_pimpinan'];
                    $update->keterangan=  $r['keterangan'];
                    $update->profile = $r['profile'];
                    $update->fasilitas = $r['fasilitas'];
                    $files = $this->request->getUploadedFiles();
                    if (isset($files['logo_upload']->file) && $files['logo_upload']->file != '') {
                      $update->logo = $this->upload_image($files['logo_upload']->file, '/files/dikti/logo');
                    } else {
                      $update->logo = isset($r['logo']) ? $r['logo'] : NULL;
                    }
                    $update->save();
                }

                if (!$update) {
                    $add = new PerguruanTinggiModel;
                    $add->nama_perguruan_tinggi =  $r['nama_perguruan_tinggi'];
                    $add->slug =  $Slug;
                    $add->no_izin=  $r['no_izin'];
                    $add->alamat=  $r['alamat'];
                    $add->gmap_embed = $r['gmap_embed'];
                    $add->akreditasi = $r['akreditasi'] ? $r['akreditasi'] : NULL;
                    $add->provinsi_id =  $r['provinsi_id'] ? $r['provinsi_id'] : NULL;
                    $add->kabupatenkota_id=  $r['kabupatenkota_id'] ? $r['kabupatenkota_id'] : NULL;
                    $add->kecamatan_id=  $r['kecamatan_id'] ? $r['kecamatan_id'] : NULL;
                    $add->kelurahan_id=  $r['kelurahan_id'] ? $r['kelurahan_id'] : NULL;
                    $add->no_telp=  $r['no_telp'];
                    $add->fax=  $r['fax'];
                    $add->email=  $r['email'];
                    $add->tahun_beridiri=  $r['tahun_beridiri'] ? $r['tahun_beridiri'] : NULL;
                    $add->tanggal_izin_dari=  $r['tanggal_izin_dari'] ? $r['tanggal_izin_dari'] : NULL;
                    $add->tanggal_izin_sampai=  $r['tanggal_izin_sampai'] ? $r['tanggal_izin_sampai'] : NULL;
                    $add->nama_pimpinan=  $r['nama_pimpinan'];
                    $add->no_telp_pimpinan=  $r['no_telp_pimpinan'];
                    $add->email_pimpinan=  $r['email_pimpinan'];
                    $add->keterangan=  $r['keterangan'];
                    $add->profile = $r['profile'];
                    $add->fasilitas = $r['fasilitas'];
                    $files = $this->request->getUploadedFiles();
                    if (isset($files['logo_upload']->file) && $files['logo_upload']->file != '') {
                      $add->logo = $this->upload_image($files['logo_upload']->file, '/files/dikti/logo');
                    } else {
                      $add->logo = isset($r['logo']) ? $r['logo'] : NULL;
                    }
                    $add->save();
                }
                if ($add || $update) {
                  $this->flash('success', 'Profil Perguruan Tinggi Berhasil di Update');
                  return $this->redirect($response, 'profile-perguruan-tinggi');
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
            $response, 'bkol/dashboard-perguruan-tinggi/profile.twig',
            array(
                "imgs" => $imgs,
                "daerahs" => $daerahs,
                "pt" => $pt
            )
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
