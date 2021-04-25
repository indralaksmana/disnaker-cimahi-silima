<?php

namespace App\Controller\Dashboard;

use Carbon\Carbon;
use App\Controller\Controller;
use Psr\Container\ContainerInterface;
use App\Model\Users;
use Illuminate\Database\Capsule\Manager as DB;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

use Slim\Views\PhpRenderer;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class PengaturanAkun extends Controller
{
    public function akun(Request $request, Response $response)
    {
        $user = $this->auth->check();
        $akun =  Users::find($user);
        $r = $this->request->getParams();
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
                  'rules' => V::noWhitespace(),
                  'messages' => array(
                      'slug' => 'Must be alpha numeric with no spaces.',
                      'noWhitespace' => 'Must not contain any spaces.'
                      )
              )
          );

          //Check username
          $checkUsername = Users::where('id', '!=', $user->id)
              ->where('username', '=', $request->getParam('username'))
              ->first();
          if ($checkUsername) {
              $this->validator->addError('username', 'Username is already in use.');
          }

          //Check Email
          $checkEmail = Users::where('id', '!=', $user->id)
              ->where('email', '=', $request->getParam('email'))
              ->first();
          if ($checkEmail) {
              $this->validator->addError('email', 'Email address is already in use.');
          }

          $this->validator->validate($request, $validateData);

          if ($this->validator->isValid()) {
              $user = Users::with('profile')->find($this->auth->check()->id);
              $newInformation = [
                'email' => $r['email'],
                'username' => $r['username']
              ];
              //why only jobseeker
              //if ($this->auth->check()->inRole('jobseeker') ) {
                $files = $this->request->getUploadedFiles();
                if (isset($files['photoprofile_upload']->file) && $files['photoprofile_upload']->file != '') {
                  $photo = $this->upload_image($files['photoprofile_upload']->file, '/files/pencaker/foto');
                } else {
                  $photo = isset($r['photoprofile']) ? $r['photoprofile'] : NULL;
                }
                $newInformation['photoprofile'] = $photo;
              //}
              
              $updateUser = $this->auth->update($user, $newInformation);
              if ($updateUser) {
                $this->flash('success', 'Akun Berhasil di Update');
                if ($this->auth->check()->inRole('bkk')) {
                  return $this->redirect($response, 'bkk-dashboard-settings-akun');
                } 
                if ($this->auth->check()->inRole('lpk')) {
                  return $this->redirect($response, 'lpk-dashboard-settings-akun');
                } 
                if ($this->auth->check()->inRole('perguruan-tinggi')) {
                  return $this->redirect($response, 'pt-dashboard-settings-akun');
                }
                if ($this->auth->check()->inRole('jobseeker')) {
                  return $this->redirect($response, 'dashboardpencaker-settings');
                }
                
              }
              return false;
              $this->flashNow('danger', 'There was an error updating your account information.');
          }
      }


        return $this->view->render(
             $response,
            'bkol/dashboard/settings.twig'
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
