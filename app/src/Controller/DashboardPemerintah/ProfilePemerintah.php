<?php

namespace App\Controller\DashboardPemerintah;

use Carbon\Carbon;
use App\Controller\Controller;
use Psr\Container\ContainerInterface;
use App\Model\ContactRequests;
use App\Model\Oauth2Providers;
use App\Model\Users;
use App\Model\JenisInstansiModel;
use App\Model\PemerintahModel;
use App\Model\DataProgramPelatihanModel;
use Illuminate\Database\Capsule\Manager as DB;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

use Slim\Views\PhpRenderer;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class ProfilePemerintah extends Controller
{
    public function profile(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('pemerintah.view')) {
            return $check;
        }
        
        $pemerintah_id = $this->auth->check()->id;

        
        $perusahaan =  PemerintahModel::find($pemerintah_id);
        
        $r = $this->request->getParams();

        $Slug = Utils::slugify( $r['nama_instansi'] );

            $checkSlug = PemerintahModel::where('id', '!=', $pemerintah_id)
                ->where('slug', '=', $Slug )
                ->get()
                ->count();
            if ($checkSlug > 0 && $Slug != $update->slug ) {
                $this->validator->addError('slug', 'Slug Sudah Di Gunakan.');
            }
        
        if ($request->isPost()) {
            if ($this->validator->isValid()) {
                $update = new PemerintahModel;
                $update = $update->where('id', '=', $pemerintah_id)->first();
                if ($update) {
                    $update->nama_instansi =  $r['nama_instansi'];
                    $update->alamat_instansi=  $r['alamat_instansi'];
                    $update->gmap_embed = $r['gmap_embed'];
                    
                    $files = $this->request->getUploadedFiles();
                    if (isset($files['logo_upload']->file) && $files['logo_upload']->file != '') {
                      $update->logo = $this->upload_image($files['logo_upload']->file, '/files/perusahaan/logo');
                    } else {
                      $update->logo = isset($r['logo']) ? $r['logo'] : NULL;
                    }
                    $update->nama_lengkap_admin = $r['nama_lengkap_admin'];
                    $update->email_instansi = $r['email_instansi'];
                    $update->no_telp = $r['no_telp'];
                    $update->no_fax = $r['no_fax'];
                    $update->website = $r['website'];
                    $update->slug =  $Slug;
                    $update->jenis_instansi_id = $r['jenis_instansi_id'] ? $r['jenis_instansi_id'] : NULL;
                    $update->save();
                }

                if (!$update) {
                    $add = new PemerintahModel;
                    $add->nama_instansi =  $r['nama_instansi'];
                    $add->id = $pemerintah_id;
                    $add->user_id = $pemerintah_id;
                    $add->slug =  $Slug;
                    $add->alamat_instansi=  $r['alamat_instansi'];
                    $add->gmap_embed = $r['gmap_embed'];
                    $files = $this->request->getUploadedFiles();
                    if (isset($files['logo_upload']->file) && $files['logo_upload']->file != '') {
                      $add->logo = $this->upload_image($files['logo_upload']->file, '/files/dikti/logo');
                    } else {
                      $add->logo = isset($r['logo']) ? $r['logo'] : NULL;
                    }
                    $add->nama_lengkap_admin = $r['nama_lengkap_admin'];
                    $add->email_instansi = $r['email_instansi'];
                    $add->no_telp = $r['no_telp'];
                    $add->no_fax = $r['no_fax'];
                    $add->website = $r['website'];
                    $add->jenis_instansi_id = $r['jenis_instansi_id'] ? $r['jenis_instansi_id'] : NULL;
                    $add->save();
                }
                if ($add || $update) {
                  $this->flash('success', 'Profil Pemerintah Berhasil di Update');
                  return $this->redirect($response, 'dashboardpemerintah-profile');
                }
                $this->flashNow('danger', 'There was an error updating your account information.');
            }
        }

        return $this->view->render(
            $response, 'bkol/dashboard-pemerintah/profil.twig',
            array (
              "p" => $perusahaan,
              'jenisinstansi' => JenisInstansiModel::get()
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
