<?php

namespace App\Controller\DashboardPerusahaan;

use Carbon\Carbon;
use App\Controller\Controller;
use Psr\Container\ContainerInterface;
use App\Model\ContactRequests;
use App\Model\Oauth2Providers;
use App\Model\JenisUsahaPost as JU;
use App\Model\WaktuKerjaModel;
use App\Model\Users;
use App\Model\Daerah;
use App\Model\BkolPerusahaan;
use App\Model\DataProgramPelatihanModel;
use Illuminate\Database\Capsule\Manager as DB;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

use Slim\Views\PhpRenderer;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class ProfilePerusahaan extends Controller
{
    public function profile(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('companies.view')) {
            return $check;
        }
        
        $perusahaan_id = $this->auth->check()->id;

        
        $perusahaan =  BkolPerusahaan::find($perusahaan_id);
        
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

            $Slug = Utils::slugify( $r['companyname'] );

            $checkSlug = BkolPerusahaan::where('id', '!=', $perusahaan_id)
                ->where('slug', '=', $Slug )
                ->get()
                ->count();
            if ($checkSlug > 0 && $Slug != $update->slug ) {
                $this->validator->addError('slug', 'Slug Sudah Di Gunakan.');
            }

            if ($this->validator->isValid()) {
                $update = new BkolPerusahaan;
                $update = $update->where('id', '=', $perusahaan_id)->first();
                if ($update) {
                    $update->companyname =  $r['companyname'];
                    $update->slug = $Slug;
                    $update->companysaddress=  $r['companysaddress'];
                    $update->gmap_embed = $r['gmap_embed'];
                    
                    $files = $this->request->getUploadedFiles();
                    if (isset($files['logo_upload']->file) && $files['logo_upload']->file != '') {
                      $update->logo = $this->upload_image($files['logo_upload']->file, '/files/perusahaan/logo');
                    } else {
                      $update->logo = isset($r['logo']) ? $r['logo'] : NULL;
                    }
                    $update->about = $r['about'];
                    $update->website = $r['website'];
                    $update->industry = $r['industry'];
                    $update->phonenumber = $r['phonenumber'];
                    $update->faxnumber = $r['faxnumber'] ? $r['faxnumber'] : NULL;
                    $update->companysize = $r['companysize'];
                    $update->workingtime = $r['workingtime'];
                    $update->fashionstyle = $r['fashionstyle'];
                    $update->languageused = $r['languageused'];
                    $update->whyjoinus = $r['whyjoinus'];
                    $update->provinsi_id = $r['provinsi_id'] ? $r['provinsi_id'] : NULL;
                    $update->kabupatenkota_id = $r['kabupatenkota_id'] ? $r['kabupatenkota_id'] : NULL;
                    $update->kecamatan_id = $r['kecamatan_id'] ? $r['kecamatan_id'] : NULL;
                    $update->kelurahan_id = $r['kelurahan_id'] ? $r['kelurahan_id'] : NULL;
                    $update->save();
                }

                if (!$update) {
                    $add = new BkolPerusahaan;
                    $add->companyname =  $r['companyname'];
                    $add->id = $perusahaan_id;
                    $add->user_id = $perusahaan_id;
                    $add->slug = $Slug;
                    $add->companysaddress=  $r['companysaddress'];
                    $add->gmap_embed = $r['gmap_embed'];
                    $files = $this->request->getUploadedFiles();
                    if (isset($files['logo_upload']->file) && $files['logo_upload']->file != '') {
                      $add->logo = $this->upload_image($files['logo_upload']->file, '/files/dikti/logo');
                    } else {
                      $add->logo = isset($r['logo']) ? $r['logo'] : NULL;
                    }
                    $add->about = $r['about'];
                    $add->website = $r['website'];
                    $add->industry = $r['industry'];
                    $add->phonenumber = $r['phonenumber'];
                    $add->faxnumber = $r['faxnumber'] ? $r['faxnumber'] : NULL;
                    $add->companysize = $r['companysize'];
                    $add->workingtime = $r['workingtime'];
                    $add->fashionstyle = $r['fashionstyle'];
                    $add->languageused = $r['languageused'];
                    $add->whyjoinus = $r['whyjoinus'];
                    $add->provinsi_id = $r['provinsi_id'] ? $r['provinsi_id'] : NULL;
                    $add->kabupatenkota_id = $r['kabupatenkota_id'] ? $r['kabupatenkota_id'] : NULL;
                    $add->kecamatan_id = $r['kecamatan_id'] ? $r['kecamatan_id'] : NULL;
                    $add->kelurahan_id = $r['kelurahan_id'] ? $r['kelurahan_id'] : NULL;
                    $add->save();
                }
                if ($add || $update) {
                  $this->flash('success', 'Profil Perusahaan Berhasil di Update');
                  return $this->redirect($response, 'dashboardperusahaan-profile');
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
            $response, 'bkol/dashboard-perusahaan/profil-perusahaan.twig',
            array(
                "daerahs" => $daerahs,
                "p" => $perusahaan,
                "jenisusaha" => JU::get(),
                "waktukerja" => WaktuKerjaModel::get()
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
