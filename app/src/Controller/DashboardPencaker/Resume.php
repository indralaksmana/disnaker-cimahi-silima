<?php

namespace App\Controller\DashboardPencaker;
use App\Controller\Controller;
use Carbon\Carbon;
use App\Model\ContactRequests;
use App\Model\Oauth2Providers;
use App\Model\Users;
use App\Model\Daerah;
use App\Model\GajiModel;
use App\Model\NegaraModel;
use App\Model\GolonganJabatanModel;
use App\Model\BkolPencariKerja;
use App\Model\UsersProfile;
use App\Model\JenisPendidikanModel;
use App\Model\ResumeUpload;
use App\Model\BkolPencariKerjaDokumen;
use App\Model\PendidikanPosts;
use App\Model\PendidikanNonFormalPosts;
use App\Model\PekerjaanPosts;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use Slim\Views\PhpRenderer;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class Resume extends Controller
{

    public function CVJobseeker(Request $request, Response $response)
    {
        $routeArgs =  $request->getAttribute('route')->getArguments();

        $checkJobseeker = Users::where('username', $routeArgs['username'])->first();

        if (!$checkJobseeker) {
            $this->flash('warning', 'Author not found.');
            return $this->redirect($response, 'blog');
        }

        $pendidikan = PendidikanPosts::where('status', 1)
            ->where('user_id', $checkJobseeker->id)
            ->where('created_at', '<', Carbon::now())
            ->with('author')
            ->orderBy('created_at', 'DESC');
        $nonformal = PendidikanNonFormalPosts::where('status', 1)
            ->where('user_id', $checkJobseeker->id)
            ->where('created_at', '<', Carbon::now())
            ->with('author')
            ->orderBy('created_at', 'DESC');
        $pekerjaan = PekerjaanPosts::where('status', 1)
            ->where('user_id', $checkJobseeker->id)
            ->where('created_at', '<', Carbon::now())
            ->with('author')
            ->orderBy('created_at', 'DESC');

        return $this->view->render(
            $response,
            'bkol/dashboard/view-resume.twig',
            array(
                "author" => $checkJobseeker,
                "pendidikan" => $pendidikan->get(),
                "pekerjaan" => $pekerjaan->get(),
                "nonformal" => $nonformal->get(),
                "authorPage" => true
            )
        );
    }

    public function Biodata(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('user.account')) {
            return $check;
        }
        $user = Users::with('profile')->find($this->auth->check()->id);
        $jenispendidikan = JenisPendidikanModel::where('kode_jurusan', 0)
            ->orderBy('jenis_pendidikan')
            ->get();
        if ($request->isPost()) {
            // Validate Data
            $validateData = array(
            );
            $this->validator->validate($request, $validateData);

            if ($this->validator->isValid()) {
                if ($this->updateBiodata($request->getParams())) {
                    $this->flash('success', 'Akun anda telah berhasil diperbarui');
                    return $this->redirect($response, 'dashboardpencaker-biodata');
                }
                $this->flashNow('danger', 'There was an error updating your account information.');
            }
        }
        $daerahs = Daerah::where('lokasi_kabupatenkota', 0)
                            ->where('lokasi_kecamatan', 0)
                            ->where('lokasi_kelurahan', 0)
                            ->orderBy('lokasi_nama')
                            ->get();
        $jenispendidikan = JenisPendidikanModel::where('kode_jurusan', 0)
        ->orderBy('jenis_pendidikan')
        ->get();

        return $this->view->render(
            $response,
            'bkol/dashboard/resume/resume.twig',
            array(
                'jenispendidikan' => $jenispendidikan,
                'daerahs' => $daerahs,
                'golonganjabatan' => GolonganJabatanModel::get(),
                'negara' => NegaraModel::get(),
                'gaji' => GajiModel::get()
            )
        );
    }

    private function updateBiodata($requestParams)
    {
        $user = Users::with('profile')->find($this->auth->check()->id);
        $updatePencariKerja = new BkolPencariKerja;
        $updatePencariKerja = $updatePencariKerja->where('user_id',$user->id)->first();
        //Check KTP
        //16 digit
        //AABBCCDDEEFFGGGG
        //AA prov
        //BB kab/kota
        //CC kec
        //cimahi = 3277 
        //030708770035
        $ktp = strip_tags($requestParams['no_ktp']);
        $count = strlen($ktp);
        $iscimahi = 1;
        if($count != 16){
          return false;
        }
        if(substr($ktp, 0, 4) == "3277"){
          $iscimahi = 1;
        }
        if ($updatePencariKerja) {
            $updatePencariKerja->nama_lengkap =  strip_tags($requestParams['nama_lengkap']);
            $updatePencariKerja->no_ktp = strip_tags($requestParams['no_ktp']);
            $updatePencariKerja->tempat_lahir = strip_tags($requestParams['tempat_lahir']);
            $updatePencariKerja->tanggal_lahir = strip_tags($requestParams['tanggal_lahir']);
            $updatePencariKerja->jenis_kelamin = strip_tags($requestParams['jenis_kelamin']);
            $updatePencariKerja->agama = strip_tags($requestParams['agama']) ? strip_tags($requestParams['agama']) : NULL;
            $updatePencariKerja->kondisi_fisik = strip_tags($requestParams['kondisi_fisik']);
            $updatePencariKerja->status_perkawinan = strip_tags($requestParams['status_perkawinan']);
            $updatePencariKerja->tinggi_badan = strip_tags($requestParams['tinggi_badan']);
            $updatePencariKerja->berat_badan = strip_tags($requestParams['berat_badan']);
            $updatePencariKerja->no_telp = strip_tags($requestParams['no_telp']);
            $updatePencariKerja->alamat_lengkap = strip_tags($requestParams['alamat_lengkap']);
            $updatePencariKerja->provinsi_id = 32;
            $updatePencariKerja->kabupatenkota_id = 77;
            $updatePencariKerja->kecamatan_id = strip_tags($requestParams['kecamatan_id']) ? strip_tags($requestParams['kecamatan_id']) : NULL;
            $updatePencariKerja->kelurahan_id = strip_tags($requestParams['kelurahan_id']) ? strip_tags($requestParams['kelurahan_id']) : NULL;
            $updatePencariKerja->kodepos = strip_tags($requestParams['kodepos']);
            $updatePencariKerja->pendidikan_terakhir_id = strip_tags($requestParams['pendidikan_terakhir_id']) ? strip_tags($requestParams['pendidikan_terakhir_id']) : NULL;
            $updatePencariKerja->jurusan_pendidikan_id = strip_tags($requestParams['jurusan_pendidikan_id']) ? strip_tags($requestParams['jurusan_pendidikan_id']) : NULL;
            $updatePencariKerja->nama_instansi_pendidikan = strip_tags($requestParams['nama_instansi_pendidikan']);
            $updatePencariKerja->tahun_lulus = strip_tags($requestParams['tahun_lulus']);
            $updatePencariKerja->nilai_ijazah_ipk = strip_tags($requestParams['nilai_ijazah_ipk']);
            $updatePencariKerja->harapan_wilayah_pekerjaan = strip_tags($requestParams['harapan_wilayah_pekerjaan']);
            $updatePencariKerja->provinsi_dalam_negeri_id = strip_tags($requestParams['provinsi_dalam_negeri_id']) ? strip_tags($requestParams['provinsi_dalam_negeri_id']) : NULL;
            $updatePencariKerja->kabupatenkota_dalam_negeri_id  = strip_tags($requestParams['kabupatenkota_dalam_negeri_id']) ? strip_tags($requestParams['kabupatenkota_dalam_negeri_id']) : NULL;
            $updatePencariKerja->negara_luar_negeri_id  = strip_tags($requestParams['negara_luar_negeri_id']) ? strip_tags($requestParams['negara_luar_negeri_id']) : NULL;
            $updatePencariKerja->sistem_pembayaran  = strip_tags($requestParams['sistem_pembayaran']);
            $updatePencariKerja->harapan_gaji_minimum_id  = strip_tags($requestParams['harapan_gaji_minimum_id']) ? strip_tags($requestParams['harapan_gaji_minimum_id']) : NULL;
            $updatePencariKerja->jenis_jabatan_yang_diminati_id  = $requestParams['jenis_jabatan_yang_diminati_id'] ? $requestParams['jenis_jabatan_yang_diminati_id'] : NULL ;
            $updatePencariKerja->iscimahi = $iscimahi;
            $updatePencariKerja->save();
        }
        if (!$updatePencariKerja) {
            $addPencariKerja = new BkolPencariKerja;
            $addPencariKerja->user_id = $user->id;
            $addPencariKerja->id = $user->id;
            $addPencariKerja->nama_lengkap =  strip_tags($requestParams['nama_lengkap']);
            $addPencariKerja->no_ktp = strip_tags($requestParams['no_ktp']);
            $addPencariKerja->tempat_lahir = strip_tags($requestParams['tempat_lahir']);
            $addPencariKerja->tanggal_lahir = strip_tags($requestParams['tanggal_lahir']);
            $addPencariKerja->jenis_kelamin = strip_tags($requestParams['jenis_kelamin']);
            $addPencariKerja->agama = strip_tags($requestParams['agama']) ? strip_tags($requestParams['agama']) : NULL;
            $addPencariKerja->kondisi_fisik = strip_tags($requestParams['kondisi_fisik']);
            $addPencariKerja->status_perkawinan = strip_tags($requestParams['status_perkawinan']);
            $addPencariKerja->tinggi_badan = strip_tags($requestParams['tinggi_badan']);
            $addPencariKerja->berat_badan = strip_tags($requestParams['berat_badan']);
            $addPencariKerja->no_telp = strip_tags($requestParams['no_telp']);
            $addPencariKerja->alamat_lengkap = strip_tags($requestParams['alamat_lengkap']);
            $addPencariKerja->provinsi_id = 32;
            $addPencariKerja->kabupatenkota_id = 77;
            $addPencariKerja->kecamatan_id = strip_tags($requestParams['kecamatan_id']) ? strip_tags($requestParams['kecamatan_id']) : NULL;
            $addPencariKerja->kelurahan_id = strip_tags($requestParams['kelurahan_id']) ? strip_tags($requestParams['kelurahan_id']) : NULL;
            $addPencariKerja->kodepos = strip_tags($requestParams['kodepos']);
            $addPencariKerja->pendidikan_terakhir_id = strip_tags($requestParams['pendidikan_terakhir_id']) ? strip_tags($requestParams['pendidikan_terakhir_id']) : NULL;
            $addPencariKerja->jurusan_pendidikan_id = strip_tags($requestParams['jurusan_pendidikan_id']) ? strip_tags($requestParams['jurusan_pendidikan_id']) : NULL;
            $addPencariKerja->nama_instansi_pendidikan = strip_tags($requestParams['nama_instansi_pendidikan']);
            $addPencariKerja->tahun_lulus = strip_tags($requestParams['tahun_lulus']);
            $addPencariKerja->nilai_ijazah_ipk = strip_tags($requestParams['nilai_ijazah_ipk']);
            $addPencariKerja->harapan_wilayah_pekerjaan = strip_tags($requestParams['harapan_wilayah_pekerjaan']);
            $addPencariKerja->provinsi_dalam_negeri_id = strip_tags($requestParams['provinsi_dalam_negeri_id']) ? strip_tags($requestParams['provinsi_dalam_negeri_id']) : NULL;
            $addPencariKerja->kabupatenkota_dalam_negeri_id  = strip_tags($requestParams['kabupatenkota_dalam_negeri_id']) ? strip_tags($requestParams['kabupatenkota_dalam_negeri_id']) : NULL;
            $addPencariKerja->negara_luar_negeri_id  = strip_tags($requestParams['negara_luar_negeri_id']) ? strip_tags($requestParams['negara_luar_negeri_id']) : NULL;
            $addPencariKerja->sistem_pembayaran  = strip_tags($requestParams['sistem_pembayaran']);
            $addPencariKerja->harapan_gaji_minimum_id  = strip_tags($requestParams['harapan_gaji_minimum_id']) ? strip_tags($requestParams['harapan_gaji_minimum_id']) : NULL;
            $addPencariKerja->jenis_jabatan_yang_diminati_id  = $requestParams['jenis_jabatan_yang_diminati_id'] ? $requestParams['jenis_jabatan_yang_diminati_id'] : NULL ;
            $addPencariKerja->iscimahi = $iscimahi;
            $addPencariKerja->save();
          }
        if ($addPencariKerja || $updatePencariKerja) {
            return true;
        }
        return false;
    }


    public function SettingsAccount(Request $request, Response $response)
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
            $files = $this->request->getUploadedFiles();
            if (isset($files['photoprofile_upload']->file) && $files['photoprofile_upload']->file != '') {
              $photo = $this->upload_image($files['photoprofile_upload']->file, '/files/pencaker/foto');
            } else {
              $photo = isset($r['photoprofile']) ? $r['photoprofile'] : NULL;
            }
            $newInformation = [
              'email' => $r['email'],
              'username' => $r['username'],
              'photoprofile' => $photo,
            ];
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
    

    public function resumeUpload(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('resume.account')) {
            return $check;
        }
        $r = $this->request->getParams();
        $user = Users::find($this->auth->check()->id);
        if ($request->isPost()) {
            // Validate Data
            $validateData = array(
            );
            $this->validator->validate($request, $validateData);
            if ($this->validator->isValid()) {
                $nama_file = $this->request->getParam("nama_file");
                $docs = $this->request->getParam("upload_file");
                $doc = new BkolPencariKerjaDokumen();
                $doc->pencaker_id = $user->id;
                $doc->nama_file = $nama_file;
                $files = $this->request->getUploadedFiles();
                if (isset($files['upload_doc']->file) && $files['upload_doc']->file != '') {
                  $doc->upload_file = $this->upload_pdf($files['upload_doc']->file, '/files/pencaker/foto');
                } else {
                  $doc->upload_file = isset($r['upload_file']) ? $r['upload_file'] : NULL;
                }
                $doc->save();
                $this->flash('success', 'Dokumen Berhasil Di Simpan');
                return $this->redirect($response, 'resume-upload');
            }
        }
        return $this->view->render(
          $response, 'bkol/dashboard/resume/resume_upload.twig');
    }

    public function resumeUploadEdit(Request $request, Response $response, $id)
    {
      if ($check = $this->sentinel->hasPerm('resume.account')) {
          return $check;
      }
      $r = $this->request->getParams();
      $user = Users::find($this->auth->check()->id);
      $nama_file = $this->request->getParam("nama_file");
      $docs = $this->request->getParam("upload_file");
      $doc = new BkolPencariKerjaDokumen();
      $doc = $doc->find($id);
      if ($request->isPost()) {
          // Validate Data
          $validateData = array(
          );
          $this->validator->validate($request, $validateData);
          if ($this->validator->isValid()) {
              $doc->pencaker_id = $user->id;
              $doc->nama_file = $nama_file;
              $files = $this->request->getUploadedFiles();
              if (isset($files['upload_doc']->file) && $files['upload_doc']->file != '') {
                $doc->upload_file = $this->upload_pdf($files['upload_doc']->file, '/files/pencaker/foto');
              } else {
                $doc->upload_file = isset($r['upload_file']) ? $r['upload_file'] : NULL;
              }
              $doc->save();
              $this->flash('success', 'Dokumen Berhasil Di Simpan');
              return $this->redirect($response, 'resume-upload');
          }
          $this->flash('danger', 'Ada yang Salah di pengisian');
          return $this->redirect($response, 'resume-upload');
      }
      return $this->view->render(
        $response,
        'bkol/dashboard/resume/resume_upload_edit.twig',
          array(
              'r' => $doc,
          )
        )

      ;
    }

    public function resumeUploadDelete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('resume.view')) {
            return $check;
        }
        $dokumen = new BkolPencariKerjaDokumen;
        $dokumen = $dokumen->where('id', $request->getParam('dokumen_id'));
        $dokumen = $dokumen->first();
        if ($dokumen) {
            if ($dokumen->delete()) {
                $this->flash('success', 'Dokumen Berhasil di hapus');
                return $this->redirect($response, 'resume-upload');
            }
        }
        $this->flash('danger', 'Error.');
        return $this->redirect($response, 'resume-upload');
    }

    private function upload_pdf($tmpfile, $subdir = NULL, $mime_allowed = array(
      'jpg' => 'image/jpeg',
  		'png' => 'image/png',
  		'pdf'   => 'application/pdf',
  		'doc'   => 'application/msword',
  		'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  		'xls'   => 'application/vnd.ms-excel',
  		'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ,)
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
