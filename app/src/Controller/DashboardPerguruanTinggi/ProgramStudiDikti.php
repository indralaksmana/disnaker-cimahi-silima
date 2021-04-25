<?php

namespace App\Controller\DashboardPerguruanTinggi;
use App\Controller\Controller;
use Carbon\Carbon;
use App\Model\Users;
use App\Library\Email as E;
use App\Model\ProgramStudiModel;
use App\Model\ProgramStudiDiktiModel;
use App\Model\PerguruanTinggiModel;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use Slim\Views\PhpRenderer;
use App\Model\PendidikanPosts;
use App\Model\PekerjaanPosts;
use App\Model\UsahaPosts;
use App\Model\BkolPencariKerja;

include_once "SimpleXLSX.php";
use MyApp\Util\SimpleXLSX as XLS;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class ProgramStudiDikti extends Controller
{
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
        ])->get();
       $title = "Upload Alumni";
       return $this->view->render(
            $response,
            'bkol/dashboard-perguruan-tinggi/program-studi/upload.twig',
            array(
                'nama' => $nama,
                'ps_id' => $ps_id,
                'pt_id' => $pt_id,
                'data' => $data,
                'users' => $users,
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
         './uploads/pt_'.$pt_id.'_'.$request->getParam('id').'.xlsx');
        if ($xlsx = XLS::parse('./uploads/pt_'.$pt_id.'_'.$request->getParam('id').'.xlsx')) {
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
                $role = $this->auth->findRoleByName('Jobseeker');
                $userDetails = [
                    'email' => $row['email'],
                    'username' => $row['nisn'],
                    'password' => '12345678',
                    'fullname' => $row['nama_lengkap'],
                    'pt_id' => $pt_id,
                    'ps_id' => $ps_id,
                    'nim' => $row['nisn'],
                    'tahun_lulus' => $row['tahun_lulus'],
                    'permissions' => [
                        'user.delete' => 0
                    ]
                ];
                if($row['nisn'] == ''){
                    $this->flash('danger', 'nisn tidak boleh kosong');
                    return $this->redirect($response, 'pt-dashboard-program-studi');
                }
                /**
                 *  1. cek nisn,jika tidak ada insert baru (user,ak1,resume pendidikan,resume pekerjaan)
                 *  2. jika ada nisn cek email, jika tidak ada maka update user,send email update data (ak1,pendidikan,pekerjaan)
                 *  3. jika ada maka hanya update (ak1,resume,pendidikan,pekerjaan)
                 */

                //ak1
                $bkolpencarikerja = new BkolPencariKerja;
                $bkolpencarikerja->tanggal_daftar = Carbon::now();
                $bkolpencarikerja->nama_lengkap =  $row['nama_lengkap'];
                $bkolpencarikerja->tempat_lahir =  $row['tempat_lahir'];
                $bkolpencarikerja->tanggal_lahir =  $row['tanggal_lahir'];
                $bkolpencarikerja->jenis_kelamin =  $row['jenis_kelamin'];
                $bkolpencarikerja->no_telp =  $row['no_telpon'];
                $bkolpencarikerja->alamat_lengkap =  $row['alamat_rumah'];
                $bkolpencarikerja->provinsi_id =  32;
                $bkolpencarikerja->kabupatenkota_id =  77;
                $bkolpencarikerja->jurusan_pendidikan_id =  $jurusan_bkk_id;
                $bkolpencarikerja->nama_instansi_pendidikan =  $bkk->nama_bkk;
                $bkolpencarikerja->tahun_lulus =  $row['tahun_lulus'];

                //pendidikan
                $pendidikan = new PendidikanPosts;
                $pendidikan->level = 'Universitas';
                $pendidikan->schoolname = $bkk->nama_perguruan_tinggi;
                $pendidikan->schoolmajors = $nama_jurusan;
                $pendidikan->graduationyear = $row['tahun_lulus'];
                $pendidikan->publish_at = Carbon::now();
                $pendidikan->status = 1;

                //pekerjaan
                $pekerjaan = new PekerjaanPosts;
                $pekerjaan->companyname = $row['nama_instansi'];
                $pekerjaan->position = $row['jabatan_dalam'];
                $pekerjaan->startmonth = $bulan;
                $pekerjaan->startyear = $tahun;
                $pekerjaan->publish_at = Carbon::now();
                $pekerjaan->status = 1;

                //usaha
                $usaha = new UsahaPosts;
                $usaha->nama_badanusaha = $row['nama_badan_usaha'];
                $usaha->jenis_usaha = $row['jenis_usaha'];
                $usaha->alamat = $row['alamat'];
                $usaha->publish_at = Carbon::now();
                $usaha->status = 1;

                $user_nisn = Users::where('nim', $row['nisn'])->get();
                if($user_nisn->isEmpty()){
                    //1.user
                    $user = $this->auth->registerAndActivate($userDetails);
                    $role->users()->attach($user);
                    $sendEmail = new E($this->container);
                    $sendEmail = $sendEmail->sendTemplate(array($user->id), 'registration_system');
                    //$this->auth->login($user);
                    $this->logger->addInfo("Pendaftaran pengguna baru.", array("user" => $user));

                    //2.ak1
                    $bkolpencarikerja->user_id =  $user->id;
                    $bkolpencarikerja->id =  $user->id;
                    //TODO filter by userid
                    $bkolpencarikerja->save();

                    //3.pendidikan
                    $pendidikan->user_id = $user->id;
                    $pendidikan->save();

                    //4.pekerjaan
                    $pekerjaan->user_id = $user->id;
                    $pekerjaan->save();

                    //5.usaha
                    $usaha->user_id = $user->id;
                    $usaha->save();
                }else{
                    $users = Users::where('email', $row['email'])->get();
                    if($users->isEmpty()){
                        $user_nisn = $user_nisn->first();
                        $user_update = Users::find($user_nisn->id);
                        $user_update->email = $row['email'];
                        $user_update->save();
                        $sendEmail = new E($this->container);
                        $sendEmail = $sendEmail->sendTemplate(array($user_nisn->id), 'registration_system');
                        $this->logger->addInfo("Pendaftaran pengguna baru.", array("user" => $user));
                    }
                    //update add row
                    //2.ak1
                    $user_nisn = $user_nisn->first();

                    $bkolpencarikerja->user_id =  $user_nisn->id;
                    $bkolpencarikerja->id =  $user_nisn->id;
                    $bkolpencarikerja->save();

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

            }

            $users = Users::where(
                'pt_id',$pt_id
                )
                ->where('ps_id',$ps_id)
                ->get();
               $title = "Upload Alumni";
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
            echo XLS::parseError();
        }
    }
}
