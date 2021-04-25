<?php

namespace App\Controller\DashboardBkk;
use App\Library\Email as E;
use App\Library\Util;
use App\Controller\Controller;
use Carbon\Carbon;
use App\Model\BkolPencariKerja;
use App\Model\Users;
use App\Model\SpektrumKeahlian;
use App\Model\JurusanBkkModel;
use App\Model\BkkModel;
use App\Model\AlumniBkkModel;
use App\Model\HitApiDisdukcapil;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use Slim\Views\PhpRenderer;
use App\Model\PendidikanPosts;
use App\Model\PekerjaanPosts;
use App\Model\UsahaPosts;
use App\Library\DisdukApi;
use App\Library\WhatsappApi;
use Cartalyst\Sentinel\Hashing\NativeHasher;

include_once "SimpleXLSX.php";
use MyApp\Util\SimpleXLSX as XLS;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class JurusanBkk extends Controller
{
    CONST ALLOW_IDENTITY = ['1607102012920005', '3204351204880005', '1802072111830004'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function index(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('bkk.view')) {
            return $check;
        }
        $bkk_id = $this->auth->check()->bkk_id;
        $posts = JurusanBkkModel::with('bkk');
        $posts = $posts->where('bkk_id', $bkk_id);
        
        return $this->view->render(
            $response,
            'bkol/dashboard-bkk/jurusan/list.twig',
            array (
                'jurusan' => $posts->get()
            )
        );
    }

    public function add(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('bkk.view')) {
            return $check;
        }
        $sk = SpektrumKeahlian::where('kode_program', 0)
        ->where('kode_kompetensi', 0)
        ->orderBy('id')
        ->get();
        $bkk_id = $this->auth->check()->bkk_id;
        $title = "Tambah";
        if ($request->isPost()) {
            if ($this->validator->isValid()) {
                $add = new JurusanBkkModel;
                $add->bkk_id = $bkk_id;
                $add->sk_id = $request->getParam('sk_id') ? $request->getParam('sk_id') : NULL;
                if ($add->save()) {
                    $this->flash('success', 'data Sudah di Tambahkan');
                    return $this->redirect($response, 'bkk-dashboard-jurusan');
                }
            }
        }

        return $this->view->render(
            $response,
            'bkol/dashboard-bkk/jurusan/add-edit.twig',
            array(
                "sk" => $sk,
                "PageTitle" => $title
            )
        );
    }

    public function edit(Request $request, Response $response, $editId)
    {
        if ($check = $this->sentinel->hasPerm('bkk.view')) {
            return $check;
        }
        $sk = SpektrumKeahlian::where('kode_program', 0)
        ->where('kode_kompetensi', 0)
        ->orderBy('id')
        ->get();


        $bkk_id = $this->auth->check()->bkk_id;
        $edit = JurusanBkkModel::where('bkk_id', $bkk_id);
        $edit = $edit->find($editId);

        $title = "Edit";

        if (!$edit) {
            $this->flash('danger', 'Jurusan Tidak Di Temukan.');
            return $response->withRedirect($this->router->pathFor('data'));
        }
        if ($request->isPost()) {
            if ($this->validator->isValid()) {
                $edit->sk_id = $request->getParam('sk_id');
                if ($edit->save()) {
                    $this->flash('success', 'Data Berhasil di Edit');
                    return $this->redirect($response, 'bkk-dashboard-jurusan');
                }
            }
            $this->flash('danger', 'Data Gagal di Edit');
        }
        return $this->view->render($response,
        'bkol/dashboard-bkk/jurusan/add-edit.twig',
        [
          'data' => $edit,
          "sk" => $sk,
          "PageTitle" => $title
        ]);
    }

    // Delete Blog Ikhtisar Statistika
    public function delete(Request $request, Response $response)
    {
        $bkk_id = $this->auth->check()->bkk_id;
        $data = JurusanBkkModel::where('bkk_id', $bkk_id);
        $data = $data->find($request->getParam('id'));
        if (!$data) {
            $this->flash('danger', 'Tidak ada  data');
            return $this->redirect($response, 'bkk-dashboard-jurusan');
        }
        if ($data->delete()) {
            $this->flash('success', 'Berhasil di Hapus');
            return $this->redirect($response, 'bkk-dashboard-jurusan');
        }

        $this->flash('danger', 'There was a problem removing the  data.');
        return $this->redirect($response, 'bkk-dashboard-jurusan');
    }

    public function upload(Request $request, Response $response, $editId, $nama)
    {
        $bkk_id = $this->auth->check()->bkk_id;
        $data = JurusanBkkModel::where('bkk_id', $bkk_id);
        $data = $data->find($editId);
        $jurusan_bkk_id = $editId;
        if (!$data) {
            $this->flash('danger', 'Tidak ada  data');
            return $this->redirect($response, 'bkk-dashboard-jurusan');
        }

        //users
        $users = Users::where('jurusan_bkk_id', $jurusan_bkk_id);
        
        if ($request->isPost()) {
          //users
          $users = Users::where('jurusan_bkk_id', $jurusan_bkk_id)->where('tahun_lulus', $request->getParam('tahun_lulus'));
        }

        $title = "Upload Alumni";
        return $this->view->render(
            $response,
            'bkol/dashboard-bkk/jurusan/upload.twig',
            array(
                'nama' => $nama,
                'data' => $data,
                'users' => $users->get(),
                "sk" => $sk,
                "PageTitle" => $title
            )
        );
    }

    public function uploadAction(Request $request, Response $response)
    {
        $bkk_id = $this->auth->check()->bkk_id;
        $data = JurusanBkkModel::where('bkk_id', $bkk_id);
        $data = $data->find($request->getParam('id'));
        $bkk = BkkModel::where('id',$bkk_id);
        $bkk = $bkk->find($bkk_id);//id PK jurusan_bkk
        $jurusan_bkk_id = $request->getParam('id');
        $nama = $request->getParam('nama');
        $nama_jurusan = $request->getParam('nama');
        move_uploaded_file($_FILES['xls']['tmp_name'],
         './uploads/bkk'.$bkk_id.'_'.$request->getParam('id').'.xlsx');
        if ($xlsx = XLS::parse('./uploads/bkk'.$bkk_id.'_'.$request->getParam('id').'.xlsx')) {
            $header_values = $rows = [];
            foreach ( $xlsx->rows() as $k => $r ) {
                if ( $k === 0 ) {
                    $header_values = $r;
                    continue;
                }
                $rows[] = array_combine( $header_values, $r );
            }
            /*
           [nama_lengkap] => AISYAH PUTRI HUMAIRA 
           [tempat_lahir] => BANDUNG 
           [tanggal_lahir] => 2000-08-25
           [jenis_kelamin] => P 
           [nisn] => 123456 
           [tahun_lulus] => 2019 
           [email] => user@gmail.com 
           [status] => 
           [alamat_rumah] => Jl.ABC 
           [no_telpon] => 852123456
           [nama_instansi] => 
           [tanggal_mulai] => 
           [alamat_instansi] => 
           [telpon_instansi] => 
           [jabatan_dalam] => 

           [nama_universitas] => 
           [jurusan] => 

           [nama_badan_usaha] => 
           [jenis_usaha] => 
           [alamat] =>
            */
            $provinsi_id = $bkk->provinsi_id;
            $kabupatenkota_id = $bkk->kabupatenkota_id;
            $kecamatan_id = $bkk->kecamatan_id;
            $kelurahan_id = $bkk->kelurahan_id;
            $tanggal_mulai = $row['tanggal_mulai'];
            $bulan = '';
            $tahun = '';
            if($tanggal_mulai){
                $tmp = explode("-",$tanggal_mulai);
                if($tmp){
                    $bulan = $tmp[1];
                    $tahun = $tmp[0];
                }
            }
            
            foreach($rows as $row){
                if($row['nisn'] == ''){
                    $this->flash('danger', 'nisn tidak boleh kosong');
                    return $this->redirect($response, 'bkk-dashboard-jurusan');
                }
                /**
                 *  1. cek nisn,jika tidak ada insert baru (user,ak1,resume pendidikan,resume pekerjaan)
                 *  2. jika ada nisn cek email, jika tidak ada maka update user,send email update data (ak1,pendidikan,pekerjaan)
                 *  3. jika ada maka hanya update (ak1,resume,pendidikan,pekerjaan)
                 */

                // save excel data into tmp table
                $tmp = new AlumniBkkModel;
                $tmp->jurusan_bkk_id = $jurusan_bkk_id;
                $tmp->no_ktp = $row['no_ktp'];
                $tmp->nama_lengkap = $row['nama_lengkap'];
                $tmp->tempat_lahir = $row['tempat_lahir'];
                $tmp->tanggal_lahir = $row['tanggal_lahir'];
                $tmp->jenis_kelamin = $row['jenis_kelamin'];
                $tmp->nisn = $row['nisn'];
                $tmp->tahun_lulus = $row['tahun_lulus'];
                $tmp->email = $row['email'];
                $tmp->status = $row['status'];
                $tmp->alamat_rumah = $row['alamat_rumah'];
                $tmp->no_telpon = $row['no_telpon'];
                $tmp->nama_instansi = $row['nama_instansi'];
                $tmp->tanggal_mulai = $row['tanggal_mulai'];
                $tmp->alamat_instansi = $row['alamat_instansi'];
                $tmp->telpon_instansi = $row['telpon_instansi'];
                $tmp->jabatan_dalam = $row['jabatan_dalam'];
                $tmp->nama_universitas = $row['nama_universitas'];
                $tmp->jurusan = $row['jurusan'];
                $tmp->nama_badan_usaha = $row['nama_badan_usaha'];
                $tmp->jenis_usaha = $row['jenis_usaha'];
                $tmp->alamat = $row['alamat'];
                $tmp->save();
            }

            $alumni = AlumniBkkModel::where('jurusan_bkk_id', $jurusan_bkk_id)->where('is_created_account', 0)->get();
            return $this->view->render(
                    $response,
                    'bkol/dashboard-bkk/jurusan/upload-validation.twig',
                    array(
                        'nama' => $nama,
                        'data' => $data,
                        'alumni' => $alumni,
                        "sk" => $sk,
                        "PageTitle" => $title
                    )
                );
        } else {
            echo XLS::parseError();
        }
    }
               
    public function detailAlumni(Request $request, Response $response,$username){
        $post = Users::with('roles','riwayatpekerjaan','riwayatpendidikan','riwayatnonformalpendidikan','riwayatusaha')
                ->WhereHas('roles', function($q){$q->whereIn('name', ['jobseeker']);})
                ->where('nim', $username)->first();
        
        return $this->view->render(
            $response,
            'bkol/dashboard-bkk/jurusan/alumni-detail.twig',
            array(
                "r" => $post,
                "ktp" => str_replace(substr($post->datapencarikerja->no_ktp, 0, 4), 'XXXX', $post->datapencarikerja->no_ktp),
                "s" => $shortlist,
                "requestParams" => $request->getParams(),
                'base_chima' => $settings['base_chima']
            )
        );
    }

    public function createPencakerAlumni(Request $request, Response $response) {
        $bkk_id = $this->auth->check()->bkk_id;
        $data = JurusanBkkModel::where('bkk_id', $bkk_id);
        $data = $data->find($request->getParam('id'));
        $bkk = BkkModel::where('id',$bkk_id);
        $bkk = $bkk->find($bkk_id);//id PK jurusan_bkk
        $jurusan_bkk_id = $request->getParam('id');
        $nama = $request->getParam('nama');
        $nama_jurusan = $request->getParam('nama');

        for($i = 0; $i < count($request->getParam('no_ktp')); $i++) {
            // save update data alumni
            $update_alumni = AlumniBkkModel::find($request->getParam('id_alumni')[$i]);
            $update_alumni->no_ktp = $request->getParam('no_ktp')[$i];
            $update_alumni->nisn = $request->getParam('nisn')[$i];
            $update_alumni->nama_lengkap = $request->getParam('nama_lengkap')[$i];
            $update_alumni->email = $request->getParam('email')[$i];
            $update_alumni->no_telpon = $request->getParam('no_telpon')[$i];
            $update_alumni->save();

            $hits = HitApiDisdukcapil::where('nik', $request->getParam('no_ktp')[$i])->where('result', 'True');
            $count = $hits->count();

            if ($count <= 0) {
                $disduk_api = new DisdukApi($this->container);
                $disduk_api->getIndentitasPenduduk($request->getParam('no_ktp')[$i]);
            }

            // ambil data dari hasil request API
            $hits_data = $hits->first();
            $identity = json_decode($hits_data->content_result);

            if (strpos($identity->KAB_NAME, 'CIMAHI') !== FALSE || in_array($identity->NIK, self::ALLOW_IDENTITY)) {

                //get data alumni
                $alumni = AlumniBkkModel::where('id', $request->getParam('id_alumni')[$i])->where('is_created_account', 0)->first();

                //ak1
                $bkolpencarikerja = new BkolPencariKerja;
                $bkolpencarikerja->no_ktp = $request->getParam('no_ktp')[$i];
                $bkolpencarikerja->tanggal_daftar = Carbon::now();
                $bkolpencarikerja->nama_lengkap = $alumni->nama_lengkap;
                $bkolpencarikerja->tempat_lahir = $alumni->tempat_lahir;
                $bkolpencarikerja->tanggal_lahir = $alumni->tanggal_lahir;
                $bkolpencarikerja->jenis_kelamin = $alumni->jenis_kelamin;
                $bkolpencarikerja->no_telp =  $alumni->no_telpon;
                $bkolpencarikerja->alamat_lengkap =  $alumni->alamat_rumah;
                $bkolpencarikerja->provinsi_id =  32;
                $bkolpencarikerja->kabupatenkota_id =  77;
                $bkolpencarikerja->jurusan_pendidikan_id =  $jurusan_bkk_id;
                $bkolpencarikerja->nama_instansi_pendidikan =  $bkk->nama_bkk;
                $bkolpencarikerja->tahun_lulus =  $alumni->tahun_lulus;
                $bkolpencarikerja->nomor_pencaker = Util::generateNoPencaker($request->getParam('no_ktp')[$i]);
                $bkolpencarikerja->nama_lengkap = $identity->NAMA_LGKP;
                $bkolpencarikerja->tempat_lahir = $identity->TMPT_LHR;
                $bkolpencarikerja->tanggal_lahir = $identity->TGL_LHR;
                $bkolpencarikerja->jenis_kelamin = $identity->JENIS_KLMIN;
                $bkolpencarikerja->agama = $identity->AGAMA;
                $bkolpencarikerja->status_perkawinan = $identity->STATUS_KAWIN;
                $bkolpencarikerja->alamat_lengkap = $identity->ALAMAT;
                $bkolpencarikerja->status_pekerjaan = "Belum Bekerja";

                //pendidikan
                $pendidikan = new PendidikanPosts;
                $pendidikan->level = 'SMK';
                $pendidikan->schoolname = $bkk->nama_bkk;
                $pendidikan->schoolmajors = $nama_jurusan;
                $pendidikan->graduationyear = $alumni->tahun_lulus;
                $pendidikan->publish_at = Carbon::now();
                $pendidikan->status = 1;

                //pekerjaan
                $pekerjaan = new PekerjaanPosts;
                $pekerjaan->companyname = $alumni->nama_instansi;
                $pekerjaan->position = $alumni->jabatan_dalam;
                $pekerjaan->startmonth = $bulan;
                $pekerjaan->startyear = $tahun;
                $pekerjaan->publish_at = Carbon::now();
                $pekerjaan->status = 1;

                //usaha
                $usaha = new UsahaPosts;
                $usaha->nama_badanusaha = $alumni->nama_badan_usaha;
                $usaha->jenis_usaha = $alumni->jenis_usaha;
                $usaha->alamat = $alumni->alamat;
                $usaha->publish_at = Carbon::now();
                $usaha->status = 1;

                $role = $this->auth->findRoleByName('Jobseeker');
                $password = Util::generatePassword();
                $userDetails = [
                    'nik' => $request->getParam('no_ktp')[$i],
                    'jurusan_bkk_id' => $jurusan_bkk_id,
                    'nim' => $alumni->nisn,
                    'tahun_lulus' => $alumni->tahun_lulus,
                    'fullname' => $identity->NAMA_LGKP,
                    'email' => $alumni->email,
                    'username' => $alumni->email ? $alumni->email : $alumni->no_telpon,
                    'password' => $password,
                    'placeofbirth' => $identity->TMPT_LHR,
                    'dateofbirth' => $identity->TGL_LHR,
                    'gender' => $identity->JENIS_KLMIN,
                    'religion' => $identity->AGAMA,
                    'nationality' => 'Indonesia',
                    'address' => $identity->ALAMAT,
                    'districts' => $identity->KEL_NAME,
                    'maritalstatus' => $identity->STATUS_KAWIN,
                    'phonenumber' => $alumni->no_telpon,
                    'permissions' => [
                        'user.delete' => 0
                    ]
                ];
                
                $user_by_nisn = Users::where('nim', $alumni->nisn);
                $pencaker = BkolPencariKerja::where('no_ktp', $request->getParam('no_ktp')[$i])->count();
                if($user_by_nisn->count() <= 0){
                    //1.user
                    $user = $this->auth->registerAndActivate($userDetails);
                    $role->users()->attach($user);
                    $sendEmail = new E($this->container);
                    $sendEmail = $sendEmail->sendTemplate(
                        array($user->id),
                        'registration_system',
                        array(
                            'password' => $userDetails['password']
                        )
                    );
                    $this->logger->addInfo("Pendaftaran pengguna baru.", array("user" => $user));

                    // whatsapp notification
                    if (strlen($userDetails['phonenumber']) > 0) {
                        $whatsappMessage = "Selamat Datang di Sistem Link and Match Disnaker Kota Cimahi".PHP_EOL.PHP_EOL."Berikut adalah detail info masuk Anda :".PHP_EOL.PHP_EOL."Username : ".$userDetails['username'].PHP_EOL.PHP_EOL."Password : ". $userDetails['password'].PHP_EOL.PHP_EOL."Kunjungi Website SILIMA Disnaker Kota Cimahi di ".PHP_EOL.PHP_EOL."https://" . $this->config['domain-bkol'];

                        $whatsapp_api = new WhatsappApi($this->container);
                        $whatsapp_api->sendMessage($userDetails['phonenumber'], $whatsappMessage);
                    }

                    // if pencaker not found
                    if ($pencaker <= 0 ) {
                        //2.ak1
                        $bkolpencarikerja->user_id = $user->id;
                        $bkolpencarikerja->id = $user->id;
                        $bkolpencarikerja->save();
                    }

                    //3.pendidikan
                    $pendidikan->user_id = $user->id;
                    $pendidikan->save();

                    //4.pekerjaan
                    $pekerjaan->user_id = $user->id;
                    $pekerjaan->save();

                    //5.usaha
                    $usaha->user_id = $user->id;
                    $usaha->save();
                } else {
                    $users = Users::where('email', $alumni->email);
                    if($users->count() <= 0){
                        $user_nisn = $user_by_nisn->first();
                        $user_update = Users::find($user_nisn->id);
                        $user_update->email = $alumni->email;
                        $user_update->password = NativeHasher::hash($userDetails['password']);
                        $user_update->save();
                        $sendEmail = new E($this->container);
                        $sendEmail = $sendEmail->sendTemplate(
                            array($user->id),
                            'registration_system',
                            array(
                                'password' => $userDetails['password']
                            )
                        );
                        $this->logger->addInfo("Pendaftaran pengguna baru.", array("user" => $user));
                        
                        // whatsapp notification
                        if (strlen($userDetails['phonenumber']) > 0) {
                            $whatsappMessage = "Selamat Datang di Sistem Link and Match Disnaker Kota Cimahi".PHP_EOL.PHP_EOL."Berikut adalah detail info masuk Anda :".PHP_EOL.PHP_EOL."Username : ".$userDetails['username'].PHP_EOL.PHP_EOL."Password : ". $userDetails['password'].PHP_EOL.PHP_EOL."Kunjungi Website SILIMA Disnaker Kota Cimahi di ".PHP_EOL.PHP_EOL."https://" . $this->config['domain-bkol'];

                            $whatsapp_api = new WhatsappApi($this->container);
                            $whatsapp_api->sendMessage($userDetails['phonenumber'], $whatsappMessage);
                        }
                    } else {
                        // insert bkk id into user table
                        $user = $users->first();
                        $update_users = Users::find($user->id);
                        $update_users->jurusan_bkk_id = $jurusan_bkk_id;
                        $update_users->save();
                        
                        $this->flash('danger', 'email '. $alumni->email . 'sudah terdaftar');
                    }

                    // get user data
                    $user_nisn = $user_by_nisn->first();
                    
                    // if pencaker not found
                    if ($pencaker <= 0 ) {
                        //update add row
                        //2.ak1
                        $bkolpencarikerja->user_id = $user_nisn->id;
                        $bkolpencarikerja->id = $user_nisn->id;
                        $bkolpencarikerja->save();
                    }

                    //3.pendidikan
                    $pendidikan->user_id = $user_nisn->id;
                    $pendidikan->save();

                    //4.pekerjaan
                    $pekerjaan->user_id = $user_nisn->id;
                    $pekerjaan->save();

                    //5.usaha
                    $usaha->user_id = $user_nisn->id;
                    $usaha->save();
                }

                // set alumni account was created
                $alumni = AlumniBkkModel::find($request->getParam('id_alumni')[$i]);
                $alumni->is_created_account = 1;
                $alumni->save();
            }
        }

        $alumni = AlumniBkkModel::where('jurusan_bkk_id', $jurusan_bkk_id)->where('is_created_account', 0);
        
        if ($alumni->count() > 0) {
            $alumni_data = $alumni->get();
            return $this->view->render(
                $response,
                'bkol/dashboard-bkk/jurusan/upload-validation.twig',
                array(
                    'nama' => $nama,
                    'data' => $data,
                    'alumni' => $alumni_data,
                    "sk" => $sk,
                    "PageTitle" => $title
                )
            );
        } else {
            return $response->withRedirect('/bkk/dashboard/jurusan/upload/'.$jurusan_bkk_id.'/'.str_replace(' ', '%20', $nama_jurusan));
        }
    }
}
