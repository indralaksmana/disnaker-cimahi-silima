<?php

namespace App\Controller\DashboardPemerintah;
use App\Controller\Controller;
use Carbon\Carbon;
use App\Model\ContactRequests;
use App\Model\Oauth2Providers;
use App\Model\JenisUsahaPost as JU;
use App\Model\Users;
use App\Model\Daerah;
use App\Model\JobPosts;
use App\Model\JobPostsApply;
use App\Model\UsersProfile;
use App\Model\WaktuKerjaModel;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use Slim\Views\PhpRenderer;


class DashboardPemerintah extends Controller
{
    public function dashboard(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('pemerintah.view')) {
            return $check;
        }
        $userId = $this->auth->check()->id;

        // Jumlah Lowongan
        $jobs = JobPosts::where('user_id', $this->auth->check()->id);
        $jumlahjobs = $jobs->count();
        $jobsaktip = $jobs->where('status', '=', 1)->get();
        // Jumlah Pelamar
        $pelamar = new JobPostsApply;
        $pelamar = $pelamar->whereHas(
            'post',
            function ($query) use ($userId) {
                $query->where('user_id', '=', $userId);
            }
        );
        $jumlahpelamar = $pelamar->count();
        $palamarbekerja  = $pelamar->where('status', '=', 5)->count();
        return $this->view->render(
            $response,
            'bkol/dashboard-perusahaan/dashboard.twig',
            array(
              'jumlahlowongan' => $jumlahjobs,
              'lowonganAktip' => $jobsaktip, 
              'jumlahpelamar' => $jumlahpelamar,
              'pelamarbekerja' =>   $palamarbekerja
            )
        );
    }

    public function SettingsAkun(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('pemerintah.view')) {
            return $check;
        }
        $user = Users::find($this->auth->check()->id);
        return $this->view->render(
            $response, 'bkol/dashboard-pemerintah/pengatuan-akun.twig'
        );
    }
    public function EditProfilPerusahaan(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('pemerintah.view')) {
            return $check;
        }
        $user = Users::find($this->auth->check()->id);

        return $this->view->render(
            $response, 'bkol/dashboard-pemerintah/profil.twig',
            array(
            )
        );
    }


    public function PengaturanAkun(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('pemerintah.view')) {
            return $check;
        }
        $user = Users::find($this->auth->check()->id);
        if ($request->isPost()) {
            // Validate Data
            $validateData = array(
                'email' => array(
                    'rules' => V::noWhitespace()->email(),
                    'messages' => array(
                        'email' => 'Enter a valid email address.',
                        'noWhitespace' => 'Must not contain any spaces.'
                        )
                ),
                'username' => array(
                    'rules' => V::noWhitespace()->alnum(),
                    'messages' => array(
                        'username' => 'Must be alpha numeric with no spaces.',
                        'noWhitespace' => 'Must not contain any spaces.'
                        )
                )
            );
            //Check username
            $checkUsername = Users::where('id', '!=', $user->id)
                ->where('username', '=', $request->getParam('username'))
                ->first();
            if ($checkUsername) {
                $this->validator->addError('username', 'Username sudah digunakan.');
            }
            //Check Email
            $checkEmail = Users::where('id', '!=', $user->id)
                ->where('email', '=', $request->getParam('email'))
                ->first();
            if ($checkEmail) {
                $this->validator->addError('email', 'Alamat email sudah digunakan.');
            }
            $this->validator->validate($request, $validateData);
            if ($this->validator->isValid()) {
                if ($this->updateAkun($request->getParams())) {
                    $this->flash('success', 'Account anda telah diperbarui dengan sukses.');
                    return $this->redirect($response, 'DashboardPemerintah-settings-akun');
                }
                $this->flashNow('danger', 'Ada kesalahan update informasi account anda.');
            }
        }
        return $this->withJson([
          'data' => "sukses"
        ]);
    }
    private function updateAkun($requestParams)
    {
        $user = Users::find($this->auth->check()->id);
        $files = $this->request->getUploadedFiles();
        if (isset($files['photoprofile_upload']->file) && $files['photoprofile_upload']->file != '') {
          $photo = $this->upload_image($files['photoprofile_upload']->file, '/files/pencaker/foto');
        } else {
          $photo = isset($requestParams['photoprofile']) ? $requestParams['photoprofile'] : NULL;
        }
        $newInformation = [
            'first_name' => $requestParams['first_name'],
            'last_name' => $requestParams['last_name'],
            'email' => $requestParams['email'],
            'username' => $requestParams['username'],
            'photoprofile' => $photo,
        ];
        $updateUser = $this->auth->update($user, $newInformation);
        if ($updateUser) {
            return true;
        }
        return false;
    }
    public function GantiPassword(Request $request, Response $response)
    {
        return $this->view->render(
            $response,
            'dashboard/ganti-password.twig'
        );
    }

    private function upload_pdf($tmpfile, $subdir = NULL, $mime_allowed = array(
      'pdf'   => 'application/pdf',)
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

    private function upload_image($tmpfile, $subdir = NULL, $mime_allowed = array(
			'jpg' => 'image/jpeg',
			'png' => 'image/png',
			'gif' => 'image/gif',)
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
