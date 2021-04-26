<?php

namespace App\Controller\DashboardPerguruanTinggi;
use App\Controller\Controller;
use Carbon\Carbon;
use App\Model\Users;
use App\Library\Email as E;
use App\Library\Util;
use App\Model\ProgramStudiModel;
use App\Model\ProgramStudiDiktiModel;
use App\Model\PerguruanTinggiModel;
use App\Model\AlumniPerguruanTinggiModel;
use App\Model\HitApiDisdukcapil;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use Slim\Views\PhpRenderer;
use App\Model\PendidikanPosts;
use App\Model\PekerjaanPosts;
use App\Model\UsahaPosts;
use App\Model\BkolPencariKerja;
use App\Library\DisdukApi;
use App\Library\WhatsappApi;
use Cartalyst\Sentinel\Hashing\NativeHasher;

include_once "SimpleXLSX.php";
use MyApp\Util\SimpleXLSX as XLS;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class ProgramStudiDikti extends Controller
{
    CONST ALLOW_IDENTITY = ['1607102012920005', '3204351204880005', '1802072111830004'];
    
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function index(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('perguruan-tinggi.view')) {
            return $check;
        }
        $pt_id = $this->auth->check()->perguruan_tinggi_id;
        $posts = ProgramStudiDiktiModel::with('program','dikti');
        $posts = $posts->where('pt_id', $pt_id);
        
        return $this->view->render(
            $response,
            'bkol/dashboard-perguruan-tinggi/program-studi/list.twig',
            array (
                'posts' => $posts->get()
            )
        );
    }

    public function add(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('perguruan-tinggi.view')) {
            return $check;
        }
        $k = ProgramStudiModel::get();
        $pt_id = $this->auth->check()->perguruan_tinggi_id;
        $title = "Tambah";
        if ($request->isPost()) {
            if ($this->validator->isValid()) {
                $add = new ProgramStudiDiktiModel;
                $add->pt_id = $pt_id;
                $add->ps_id = $request->getParam('ps_id') ? $request->getParam('ps_id') : NULL;
                $add->akreditasi= $request->getParam('akreditasi') ? $request->getParam('akreditasi') : NULL;
                $add->strata= $request->getParam('strata') ? $request->getParam('strata') : NULL;
                if ($add->save()) {
                    $this->flash('success', 'data Sudah di Tambahkan');
                    return $this->redirect($response, 'pt-dashboard-program-studi');
                }
            }
        }

        return $this->view->render(
            $response,
            'bkol/dashboard-perguruan-tinggi/program-studi/add-edit.twig',
            array(
                "k" => $k,
                "PageTitle" => $title
            )
        );
    }

    public function edit(Request $request, Response $response, $editId)
    {
        if ($check = $this->sentinel->hasPerm('perguruan-tinggi.view')) {
            return $check;
        }
        $k = ProgramStudiModel::get();


        $pt_id = $this->auth->check()->perguruan_tinggi_id;
        $edit = ProgramStudiDiktiModel::with('program');
        $edit = $edit->where('pt_id', $pt_id);
        $edit = $edit->find($editId);

        $title = "Edit";

        if (!$edit) {
            $this->flash('danger', 'programstudi Tidak Di Temukan.');
            return $response->withRedirect($this->router->pathFor('pt-dashboard-program-studi'));
        }
        if ($request->isPost()) {
            if ($this->validator->isValid()) {
                $edit->ps_id = $request->getParam('ps_id');
                $edit->akreditasi= $request->getParam('akreditasi') ? $request->getParam('akreditasi') : NULL;
                $edit->strata= $request->getParam('strata') ? $request->getParam('strata') : NULL;
                if ($edit->save()) {
                    $this->flash('success', 'Data Berhasil di Edit');
                    return $this->redirect($response, 'pt-dashboard-program-studi');
                }
            }
            $this->flash('danger', 'Data Gagal di Edit');
        }
        return $this->view->render($response,
        'bkol/dashboard-perguruan-tinggi/program-studi/add-edit.twig',
        [
          'data' => $edit,
          "k" => $k,
          "PageTitle" => $title
        ]);
    }

    // Delete Blog Ikhtisar Statistika
    public function delete(Request $request, Response $response)
    {
        $pt_id = $this->auth->check()->perguruan_tinggi_id;
        $data = ProgramStudiDiktiModel::with('program');
        $data = $data->where('pt_id', $pt_id);
        $data = $data->find($request->getParam('id'));
        if (!$data) {
            $this->flash('danger', 'Tidak ada  data');
            return $this->redirect($response, 'pt-dashboard-program-studi');
        }
        if ($data->delete()) {
            $this->flash('success', 'Berhasil di Hapus');
            return $this->redirect($response, 'pt-dashboard-program-studi');
        }

        $this->flash('danger', 'There was a problem removing the  data.');
        return $this->redirect($response, 'pt-dashboard-program-studi');
    }

    public function upload(Request $request, Response $response,$editId,$nama)
    {
        if ($check = $this->sentinel->hasPerm('perguruan-tinggi.view')) {
            return $check;
        }
        $ps_id = $editId;
        $pt_id = $this->auth->check()->perguruan_tinggi_id;
        
        //PROCESS UPLOAD FILE XLS
        //utk tampil user hasi upload
        $users = Users::where([
            ['pt_id','=', $pt_id],
            ['ps_id','=', $ps_id]
        ]);

        if ($request->isPost()) {
            //users
            $users = Users::where([
                ['pt_id','=', $pt_id],
                ['ps_id','=', $ps_id]
            ])->where('tahun_lulus', $request->getParam('tahun_lulus'));
        }
        $title = "Upload Alumni";
        return $this->view->render(
            $response,
            'bkol/dashboard-perguruan-tinggi/program-studi/upload.twig',
            array(
                'nama' => $nama,
                'ps_id' => $ps_id,
                'pt_id' => $pt_id,
                'data' => $data,
                'users' => $users->get(),
                "sk" => $sk,
                "PageTitle" => $title
            )
        );
    }

    public function uploadAction(Request $request, Response $response)
    {

        if ($check = $this->sentinel->hasPerm('perguruan-tinggi.view')) {
            return $check;
        }
        $ps_id = $request->getParam('ps_id');
        $pt_id = $this->auth->check()->perguruan_tinggi_id;
        $data = ProgramStudiDiktiModel::where([
            ['pt_id','=', $pt_id],
            ['ps_id','=', $ps_id]
            ])->get();//where ps_id juga

        $bkk = PerguruanTinggiModel::where('id',$pt_id);
        $bkk = $bkk->find($pt_id);//id PK jurusan_bkk
        $nama = $request->getParam('nama');
        $nama_jurusan = $request->getParam('nama');

       //PROCESS UPLOAD FILE XLS
       //utk tampil user hasi upload
        move_uploaded_file($_FILES['xls']['tmp_name'],
         './uploads/pt_'.$pt_id.'_'.$ps_id.'.xlsx');
        if ($xlsx = XLS::parse('./uploads/pt_'.$pt_id.'_'.$ps_id.'.xlsx')) {
            $header_values = $rows = [];
            foreach ( $xlsx->rows() as $k => $r ) {
                if ( $k === 0 ) {
                    $header_values = $r;
                    continue;
                }
                $rows[] = array_combine( $header_values, $r );
            }
            /*
            Array ( [0] => Array ( [nim] => 123456 
            [nama_lengkap] => anton merdeka [email] => anton@gmail.com )
             [1] => Array ( [nim] => 122321 
             [nama_lengkap] => Ahmad b [email] => ahmadb@gmail.com ) )
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
                    return $this->redirect($response, 'pt-dashboard-program-studi');
                }
                /**
                 *  1. cek nisn,jika tidak ada insert baru (user,ak1,resume pendidikan,resume pekerjaan)
                 *  2. jika ada nisn cek email, jika tidak ada maka update user,send email update data (ak1,pendidikan,pekerjaan)
                 *  3. jika ada maka hanya update (ak1,resume,pendidikan,pekerjaan)
                 */
                
                 // save excel data into tmp table
                $tmp = new AlumniPerguruanTinggiModel;
                $tmp->pt_id = $pt_id;
                $tmp->ps_id = $ps_id;
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

            $alumni = AlumniPerguruanTinggiModel::where('pt_id', $pt_id)
                                    ->where('ps_id', $ps_id)
                                    ->where('is_created_account', 0)
                                    ->get();
            $title = "Upload Alumni";
            return $this->view->render(
                $response,
                'bkol/dashboard-perguruan-tinggi/program-studi/upload-validation.twig',
                array(
                    'nama' => $nama,
                    'data' => $data,
                    'alumni' => $alumni,
                    "sk" => $sk,
                    "PageTitle" => $title,
                    "ps_id" => $ps_id
                )
            );
        } else {
            echo XLS::parseError();
        }
    }

    public function createPencakerAlumni(Request $request, Response $response) {
        $ps_id = $request->getParam('ps_id');
        $pt_id = $this->auth->check()->perguruan_tinggi_id;
        $data = ProgramStudiDiktiModel::where([
            ['pt_id','=', $pt_id],
            ['ps_id','=', $ps_id]
            ])->get();//where ps_id juga

        $bkk = PerguruanTinggiModel::where('id', $pt_id);
        $bkk = $bkk->find($pt_id);//id PK jurusan_bkk
        $nama = $request->getParam('nama');
        $nama_jurusan = $request->getParam('nama');

        for($i = 0; $i < count($request->getParam('no_ktp')); $i++) {
            // save update data alumni
            $update_alumni = AlumniPerguruanTinggiModel::find($request->getParam('id_alumni')[$i]);
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
                $alumni = AlumniPerguruanTinggiModel::where('id', $request->getParam('id_alumni')[$i])->where('is_created_account', 0)->first();

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
                    'pt_id' => $pt_id,
                    'ps_id' => $ps_id,
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
                $alumni = AlumniPerguruanTinggiModel::find($request->getParam('id_alumni')[$i]);
                $alumni->is_created_account = 1;
                $alumni->save();
            }
        }

        $alumni = AlumniPerguruanTinggiModel::where('pt_id', $pt_id)
                                            ->where('ps_id', $ps_id)
                                            ->where('is_created_account', 0);
        
        if ($alumni->count() > 0) {
            $alumni_data = $alumni->get();
            return $this->view->render(
                $response,
                'bkol/dashboard-perguruan-tinggi/program-studi/upload.twig',
                array(
                    'nama' => $nama,
                    'data' => $data,
                    'users' => $users,
                    "sk" => $sk,
                    "PageTitle" => $title
                )
            );
        } else {
            return $response->withRedirect('/perguruan-tinggi/dashboard/program-studi/upload/'.$ps_id.'/'.str_replace(' ', '%20', $nama_jurusan));
        }
    }
}
