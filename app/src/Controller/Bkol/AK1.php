<?php

namespace App\Controller\Bkol;
use App\Controller\Controller;
use Carbon\Carbon;
use App\Model\ContactRequests;
use App\Library\Recaptcha;
use App\Model\Oauth2Providers;
use App\Library\Email as E;
use App\Model\Users;
use App\Model\AK1Model;
use App\Model\BkolPencariKerja;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use Slim\Views\PhpRenderer;
use \Gumlet\ImageResize;


/** @SuppressWarnings(PHPMD.StaticAccess) */
class AK1 extends Controller
{


    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function pengajuanak1(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('jobseeker.view')) {
            return $check;
        }
        $user = Users::with('profile','pengajuanAK1')->find($this->auth->check()->id);

        //$tanggal_daftar = strtotime($user->pengajuanAK1->tanggal_cetak);
        $tanggal_daftar = date("Y-m-d");
        $tanggal_kadaluarsa = strtotime($user->pengajuanAK1->exp_date);
        $tgl_sekarang = date("Y-m-d");
        $tgl_sekarang = strtotime($tgl_sekarang);
        $laporan_pertama = strtotime('+6 months', $tanggal_daftar);
        $laporan_kedua = strtotime('+12 months', $tanggal_daftar);
        $laporan_ketiga = strtotime('+18 months', $tanggal_daftar);
        $tgl_mulai = strtotime($user->pengajuanAK1->tanggal_cetak);// tanggal launching aplikasi
        $tgl_exp = strtotime($user->pengajuanAK1->exp_date); //tanggal expired

        if ($request->isPost()) {
            // Validate Data
            $validateData = array(
            );
            // Validate Recaptcha
            // $recaptcha = new Recaptcha($this->container);
            // $recaptcha = $recaptcha->validate($request->getParam('g-recaptcha-response'));
            // if (!$recaptcha) {
            //     $this->validator->addError('recaptcha', 'Recaptcha was invalid.');
            // }

            //Check username

            $this->validator->validate($request, $validateData);

            if ($this->validator->isValid()) {
                if ($this->AjukanAK1($request->getParams())) {
                    $this->flash(
                        'success', 'Nomor Antrian anda Sudah Di Buat');
                    return $this->redirect($response, 'pengajuan-ak1');
                }
                $this->flashNow('danger', 'There was an error updating your account information.');
            }

            if ($this->validator->isValid()) {
                if ($this->UpdateAK1($request->getParams())) {
                    $this->flash('success', 'Sudah Di Laporkan');
                    return $this->redirect($response, 'pengajuan-ak1');
                }
                $this->flashNow('danger', 'There was an error updating your account information.');
            }
        }

        $user = Users::with('profile','pengajuanAK1')->find($this->auth->check()->id);
        $ak1 = AK1Model::where('user_id',$user->id)->first();

        return $this->view->render(
            $response,
            'bkol/dashboard/ak1/pengajuan.twig',
            array(
              'laporan_pertama' => $laporan_pertama,
              'ak1' =>  $ak1,
              'laporan_kedua' => $laporan_kedua,
              'laporan_ketiga' => $laporan_ketiga,
              'tgl_exp' => $tgl_exp,
              'tgl_sekarang' => $tgl_sekarang,
              'tgl_mulai' => $tgl_mulai
            )
        );
    }

    public function pengajuanaklaporanakhir(Request $request, Response $response)
    {
        $user = Users::with('profile','pengajuanAK1')->find($this->auth->check()->id);
        if ($check = $this->sentinel->hasPerm('user.account')) {
            return $check;
        }
        $tanggal_daftar = strtotime($user->pengajuanAK1->tanggal_cetak);
        $tanggal_kadaluarsa = strtotime($user->pengajuanAK1->exp_date);
        $tgl_sekarang = date("Y-m-d");
        $tgl_sekarang = strtotime($tgl_sekarang);
        $laporan_pertama = strtotime('+6 months', $tanggal_daftar);
        $laporan_kedua = strtotime('+12 months', $tanggal_daftar);
        $laporan_ketiga = strtotime('+18 months', $tanggal_daftar);

        $tgl_mulai = strtotime($user->pengajuanAK1->tanggal_cetak);// tanggal launching aplikasi
        $tgl_exp = strtotime($user->pengajuanAK1->exp_date); //tanggal expired

        if ($request->isPost()) {
            // Validate Data
            $validateData = array(
            );
            // Validate Recaptcha
            // $recaptcha = new Recaptcha($this->container);
            // $recaptcha = $recaptcha->validate($request->getParam('g-recaptcha-response'));
            // if (!$recaptcha) {
            //     $this->validator->addError('recaptcha', 'Recaptcha was invalid.');
            // }
            //Check username
            $this->validator->validate($request, $validateData);
            if ($this->validator->isValid()) {
                if ($this->AjukanAK1($request->getParams())) {
                    $this->flash(
                        'success', 'Nomor Antrian anda Sudah Di Buat');
                    return $this->redirect($response, 'pengajuan-ak1');
                }
                $this->flashNow('danger', 'There was an error updating your account information.');
            }

            if ($this->validator->isValid()) {
                if ($this->UpdateAK1($request->getParams())) {
                    $this->flash('success', 'Sudah Di Laporkan');
                    return $this->redirect($response, 'pengajuan-ak1');
                }
                $this->flashNow('danger', 'There was an error updating your account information.');
            }
        }

        return $this->view->render(
            $response,
            'bkol/dashboard/ak1/pengajuan-laporan-akhir.twig',
            array(
              'laporan_pertama' => $laporan_pertama,
              'laporan_kedua' => $laporan_kedua,
              'laporan_ketiga' => $laporan_ketiga,
              'tgl_exp' => $tgl_exp,
              'tgl_sekarang' => $tgl_sekarang,
              'tgl_mulai' => $tgl_mulai
            )
        );
    }
    public function contohAk1(Request $request, Response $response)
    {
        $user = Users::with('profile','pengajuanAK1')->find($this->auth->check()->id);
        if ($check = $this->sentinel->hasPerm('user.account')) {
            return $check;
        }

        return $this->view->render(
            $response,
            'bkol/dashboard/ak1/contoh-ak1.twig'
        );
    }
    // UNTUK PENGAJUAN AK1
    private function AjukanAK1($requestParams)
    {

        $user = Users::with('profile')->find($this->auth->check()->id);
        //$newInformation = [

        //];

        //$updateUser = $this->auth->update($user, $newInformation);
        $updatePencariKerja = new AK1Model;
        //$updatePencariKerja = $updatePencariKerja->find($user->id);
        $updatePencariKerja = AK1Model::where('user_id','=',$this->auth->check()->id)->first();
        if (!$updatePencariKerja) {
            $addPencariKerja = new AK1Model;
            $addPencariKerja->user_id = $user->id;
            //$addPencariKerja->id = $user->id;
            $addPencariKerja->jenis_pengajuan = $requestParams['jenis_pengajuan'];
            $uniqid = $this->auth->check()->id;
            $tanggalnow = Carbon::now();
            $now = date_format($tanggalnow, 'dmy');
            $addPencariKerja->nomor_pendaftaran = $uniqid.''.$now;
            if ($addPencariKerja->save()) {
                $user = Users::with('datapencarikerja')->find($this->auth->check()->id);
                $sendEmail = new E($this->container);
                // UNTUK MENGIRIM NOTIFIKASI KE EMAIL ADMIN BAHWA ADA PENGAJUAN AK1 BARU
                $sendEmailtoAdmin = $sendEmail->sendTemplate(
                    array($this->config['pengajuan-ak1-email']),
                    'pengajuan-ak1-admin',
                    array(
                      'nomor_pendaftaran' => $addPencariKerja->nomor_pendaftaran,
                      'nama_lengkap' => $user->datapencarikerja->nama_lengkap
                    )
                );
                // UNTUK MENGIRIM NOTIFIKASI KE EMAIL PENCAKER BAHWA ADA PENGAJUAN AK1 BERHASIL DAN MENIFOKAN NOMOR PENDAFTARAN
                $sendEmailtoUser = $sendEmail->sendTemplate(
                    array($user->id),
                    'pengajuan-ak1-user',
                    array(
                      'nomor_pendaftaran' => $addPencariKerja->nomor_pendaftaran,
                      'nama_lengkap' => $user->datapencarikerja->nama_lengkap
                    )
                );
                return true;
            }
        }

        if ($addPencariKerja) {
            return true;
        }
        return false;
    }

    // UNTUK UPDATE PENGAJUAN AK1
    private function UpdateAK1($requestParams)
    {

        $user = Users::with('profile')->find($this->auth->check()->id);

        $newInformation = [
        ];
        $updateUser = $this->auth->update($user, $newInformation);
        $uniqid = uniqid();
        $rand_start = rand(1,5);
        $updatePencariKerja = new AK1Model;
        //$updatePencariKerja = $updatePencariKerja->find($user->id);
        $updatePencariKerja = AK1Model::where('user_id','=',$this->auth->check()->id)->first();
        $data =  new BkolPencariKerja;
        $data =  $data->where('user_id', '=', $user->id)->first();

        if ($updatePencariKerja) {
            //$uniqid = $updatePencariKerja->id;
            $uniqid = $this->auth->check()->id;
            $tanggalnow = Carbon::now();
            $now = date_format($tanggalnow, 'dmy');
            $updatePencariKerja->nomor_pendaftaran = $uniqid.''.$now;
            $data->diterima_di = $requestParams['diterima_di'];
            $data->terhitung_mulai_tanggal = $requestParams['terhitung_mulai_tanggal'] ? $requestParams['terhitung_mulai_tanggal'] : NULL;
            $data->status_pekerjaan = $requestParams['status_pekerjaan'];
            $data->domisili_perusahaan = $requestParams['domisili_perusahaan'];
            $updatePencariKerja->laporan_akhir = $tanggalnow;
            if ($data->status_pekerjaan =  'Belum Bekerja') {
              $user = Users::with('datapencarikerja')->find($this->auth->check()->id);
              $sendEmail = new E($this->container);
              // UNTUK MENGIRIM NOTIFIKASI KE EMAIL ADMIN LAPORAN AK1
              $sendEmailtoAdmin = $sendEmail->sendTemplate(
                  array($this->config['pengajuan-ak1-email']),
                  'laporan-ak1-belumkerja-ke-admin',
                  array(
                    'tanggal_pelaporan' => $now,
                    'nomor_pendaftaran' => $updatePencariKerja->nomor_pendaftaran,
                    'nama_lengkap' => $user->datapencarikerja->nama_lengkap,
                    'alamat' => $user->datapencarikerja->alamat,
                    'nomor_pencaker' => $user->datapencarikerja->nomor_pencaker,
                    'status_pekerjaan' => $updatePencariKerja->status_pekerjaan,
                    'bekerja_di' => $updatePencariKerja->diterima_di,
                    'domisili_perusahaan' =>  $updatePencariKerja->domisili_perusahaan,
                    'terhitung_mulai_tanggal' =>  $updatePencariKerja->terhitung_mulai_tanggal,
                  )
              );
            }
            if ($data->status_pekerjaan =  'Sudah Bekerja') {
              $user = Users::with('datapencarikerja')->find($this->auth->check()->id);
              $sendEmail = new E($this->container);
              // UNTUK MENGIRIM NOTIFIKASI KE EMAIL ADMIN LAPORAN AK1
              $sendEmailtoAdmin = $sendEmail->sendTemplate(
                  array($this->config['pengajuan-ak1-email']),
                  'laporan-ak1-sudahkerja-ke-admin',
                  array(
                    'tanggal_pelaporan' => $now,
                    'nomor_pendaftaran' => $updatePencariKerja->nomor_pendaftaran,
                    'nama_lengkap' => $user->datapencarikerja->nama_lengkap,
                    'alamat' => $user->datapencarikerja->alamat,
                    'nomor_pencaker' => $user->datapencarikerja->nomor_pencaker,
                    'status_pekerjaan' => $updatePencariKerja->status_pekerjaan,
                    'bekerja_di' => $updatePencariKerja->diterima_di,
                    'domisili_perusahaan' =>  $updatePencariKerja->domisili_perusahaan,
                    'terhitung_mulai_tanggal' =>  $updatePencariKerja->terhitung_mulai_tanggal,
                  )
              );
            }
            $updatePencariKerja->save();
            $data->save();
        }
        if ($updatePencariKerja && $data) {

            return true;
        }
        return false;
    }

    private function check_image($src_file_name){
        $supported_image = array('gif','jpg','jpeg','png');
        $ext = strtolower(pathinfo($src_file_name, PATHINFO_EXTENSION));

	    if (in_array($ext, $supported_image)){
	        return true;
	    }else {
	        return false;

	    }
    }

    private function check_image_size($files_size){
        if(($files_size > 2097152) || ($files_size == 0)) {
            return false;
        }else{
            return true;
        }    
    }

    public function uploadSyarat(Request $request, Response $response){
        $ak1 = AK1Model::where('user_id',$this->auth->check()->id);
        $user = Users::with('profile','pengajuanAK1')->find($this->auth->check()->id);
        $userid = $user->id;

        $ak1 = new AK1Model;
         $ak1 = AK1Model::where('user_id','=',$this->auth->check()->id)->first();
        //$ak1 = $ak1->find($user->id);
    
        $uri = $request->getUri();
        $baseUrl = $uri->getBaseUrl();
        

        if($_FILES['ktp']['tmp_name'] != ''){
            if($this->check_image($_FILES['ktp']['name']) & $this->check_image_size($_FILES['ktp']['size'])){
                $tmp_name = './files/pencaker/ktp/dok_ak1_ktp_'.$userid.'_tmp.jpg';
                $file_name =  '/files/pencaker/ktp/'.md5(Carbon::now() . $userid).'.jpg';
                $ok = move_uploaded_file($_FILES['ktp']['tmp_name'],$tmp_name);
                if($ok){
                    try{
                        $image = new ImageResize($tmp_name);
                        $image->quality_jpg = 100;
                        $image->resizeToBestFit(709,472);
                        $image->save('.'.$file_name);
                        unlink($tmp_name);
                    } catch (ImageResizeException $e) {
                        echo "Something went wrong" . $e->getMessage();
                    }
                    $ktp = $file_name;
                    $ak1->file_ktp = $baseUrl . $ktp;
                    $ak1->save();
                }else{
                    $this->flashNow('danger', $_FILES["ktp"]["error"]);;
                }
            }else{
                $this->flashNow('danger', 'upload ktp menggunakan format JPG,JPEG atau PNG max 2 MB');
            }
        }

        if($_FILES['ijazah']['tmp_name'] != ''){
            if($this->check_image($_FILES['ijazah']['name']) & $this->check_image_size($_FILES['ijazah']['size'])){
                $tmp_name = './files/pencaker/ijazah/dok_ak1_ijazah_'.$userid.'_tmp.jpg';
                $file_name =  '/files/pencaker/ijazah/'.md5(Carbon::now() . $userid).'.jpg';
                $ok = move_uploaded_file($_FILES['ijazah']['tmp_name'],$tmp_name);
                if($ok){
                    try{
                        $image = new ImageResize($tmp_name);
                        $image->quality_jpg = 100;
                        $image->resize(800, 600);
                        $image->save('.'.$file_name);
                        unlink($tmp_name);
                    } catch (ImageResizeException $e) {
                        echo "Something went wrong" . $e->getMessage();
                    }
                    $ak1->file_ijazah = $baseUrl . $file_name;
                    $ak1->save();
                }else{
                    $this->flashNow('danger', $_FILES["ijazah"]["error"]);;
                }
            }else{
                $this->flashNow('danger', 'upload ijazah menggunakan format JPG,JPEG atau PNG max 2 MB');
            }
        }

        if($_FILES['sertifikat']['tmp_name'] != ''){
            if($this->check_image($_FILES['sertifikat']['name']) & $this->check_image_size($_FILES['sertifikat']['size'])){
                $tmp_name = './files/pencaker/sertifikat/dok_ak1_sertifikat_'.$userid.'_tmp.jpg';
                $file_name =  '/files/pencaker/sertifikat/'.md5(Carbon::now() . $userid).'.jpg';
                $ok = move_uploaded_file($_FILES['sertifikat']['tmp_name'],$tmp_name);
                if($ok){
                    try{
                        $image = new ImageResize($tmp_name);
                        $image->quality_jpg = 100;
                        $image->resize(800, 600);
                        $image->save('.'.$file_name);
                        unlink($tmp_name);
                    } catch (ImageResizeException $e) {
                        echo "Something went wrong" . $e->getMessage();
                    }
                    $ak1->file_sertifikat = $baseUrl . $file_name;
                    $ak1->save();
                }else{
                    $this->flashNow('danger', $_FILES["sertifikat"]["error"]);;
                }
            }else{
                $this->flashNow('danger', 'upload sertifikat menggunakan format JPG,JPEG atau PNG max 2 MB');
            }
        }

        if($_FILES['pengalaman']['tmp_name'] != ''){
            if($this->check_image($_FILES['pengalaman']['name']) & $this->check_image_size($_FILES['pengalaman']['size'])){
                $tmp_name = './files/pencaker/pengalaman/dok_ak1_pengalaman_'.$userid.'_tmp.jpg';
                $file_name =  '/files/pencaker/pengalaman/'.md5(Carbon::now() . $userid).'.jpg';
                $ok = move_uploaded_file($_FILES['pengalaman']['tmp_name'],$tmp_name);
                //if($oke){
                    try{
                        $image = new ImageResize($tmp_name);
                        $image->quality_jpg = 100;
                        $image->resize(800, 600);
                        $image->save('.'.$file_name);
                        unlink($tmp_name);
                    } catch (ImageResizeException $e) {
                        echo "Something went wrong" . $e->getMessage();
                    }
                    $ak1->file_pengalaman = $baseUrl . $file_name;
                    $ak1->save();
                //}else{
                   // $this->flashNow('danger', $_FILES["pengalaman"]["error"]);
                //}
            }else{
                $this->flashNow('danger', 'upload pengalaman menggunakan format JPG,JPEG atau PNG max 2 MB');
            }

        }
        //print_r($_FILES);
        if($_FILES['foto']['tmp_name'] != ''){
            if($this->check_image($_FILES['foto']['name']) & $this->check_image_size($_FILES['foto']['size'])){
                $tmp_name = './files/pencaker/foto/dok_ak1_foto_'.$userid.'_tmp.jpg';
                $file_name =  '/files/pencaker/foto/'.md5(Carbon::now() . $userid).'.jpg';
                $ok = move_uploaded_file($_FILES['foto']['tmp_name'],$tmp_name);
                if($ok){
                    try{
                        $image = new ImageResize($tmp_name);
                        $image->quality_jpg = 100;
                        $image->resize(800, 600);
                        $image->save('.'.$file_name);
                        unlink($tmp_name);
                    } catch (ImageResizeException $e) {
                        echo "Something went wrong" . $e->getMessage();
                    }
                    $ak1->file_foto = $baseUrl . $file_name;
                    //$user->photoprofile = $file_name;
                    //$user->save();
                    $ak1->save();
                }else{
                    $this->flashNow('foto', $_FILES["ktp"]["error"]);;
                }

            }else{
                $this->flashNow('danger', 'upload foto menggunakan format JPG,JPEG atau PNG max 2 MB');
            }

        }

        //go to here
        return $this->redirect($response, 'pengajuan-ak1');
    }
}
