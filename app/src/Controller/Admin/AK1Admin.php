<?php

namespace App\Controller\Admin;

use App\Model\Roles;
use Carbon\Carbon;
use App\Model\Users as U;

use App\Model\AK1Model;
use App\Model\BkolPencariKerja;
use App\Model\JenisPendidikanModel;
use App\Model\Daerah;
use App\Model\Bahasa;
use App\Model\SkillBahasa;
use App\Model\NegaraModel;
use App\Model\GajiModel;
use App\Model\GolonganJabatanModel;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class AK1Admin extends Controller
{
    protected $parsedSkillBahasa;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function index(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('user.view')) {
            return $check;
        }
        $users = U::has('pengajuanAK1')
        ->WhereHas('pengajuanAK1', function($q){$q->whereIn('status_pengajuan', [0]);})
        ->with('datapencarikerja')->get();

        $datalengkap = U::has('pengajuanAK1')
        ->with('datapencarikerja')
        ->WhereHas('pengajuanAK1', function($q){$q->whereIn('status_pengajuan', [1]);})
        ->get();

        // $siapcetak = U::has('pengajuanAK1')
        // ->with('datapencarikerja')
        // ->WhereHas('pengajuanAK1', function($q){$q->whereIn('status_pengajuan', [2]);})
        // ->get();

        $sudahjadi = U::has('pengajuanAK1')
        ->with('datapencarikerja')
        ->WhereHas('pengajuanAK1', function($q){$q->whereIn('status_pengajuan', [2]);})
        ->get();

        return $this->view->render(
            $response,
            'dashboard/ak1/ak1.twig',
            [
                "users" => $users,
                "datalengkap" => $datalengkap,
                // "siapcetak" => $siapcetak,
                "sudahjadi" => $sudahjadi
            ]
        );
    }



    public function Ak1Add(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('user.create')) {
            return $check;
        }

        if ($request->isPost()) {
            $this->validator->validate($request, [
                'no_ktp' => V::length(2, 200)
            ]);
            $checkKtp = BkolPencariKerja::where('no_ktp', '=', $request->getParam('no_ktp'))->get()->count();
            if ($checkKtp > 0) {
                $this->validator->addError('no_ktp', 'Nomor KTP Sudah Tersedia');
            }
            $this->validateUserData();

            if ($this->validator->isValid()) {
                $this->addUser();

                $this->flash('success', $request->getParam('username').' has been added successfully.');
                return $this->redirect($response, 'ak1-admin');
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



        $roles = Roles::get();

        return $this->view->render(
            $response,
            'dashboard/ak1/add.twig',
            [
                'roles' => $roles,
                'jenispendidikan' => $jenispendidikan,
                'daerahs' => $daerahs,
                'golonganjabatan' => GolonganJabatanModel::get(),
                'negara' => NegaraModel::get(),
                'bahasa' => Bahasa::get(),
                'gaji' => GajiModel::get()
            ]
        );
    }

    private function addUser()
    {
        $user = $this->auth->registerAndActivate([
            // 'fullname' => $this->request->getParam('fullname'),
            'email' => $this->request->getParam('email'),
            'username' => $this->request->getParam('username'),
            // 'photoprofile' => $this->request->getParam('photoprofile'),
            'password' => $this->request->getParam('password'),
            'permissions' => [
                'user.delete' => 0
            ]
        ]);

        $uniqid = uniqid();
        $rand_start = rand(1,5);

        $pengajuanak1 = new AK1Model;
        $pengajuanak1->user_id =  $user->id;
        //$pengajuanak1->id =  $user->id;
        $pengajuanak1->nomor_pendaftaran = substr($uniqid,$rand_start,8);
        $pengajuanak1->status_pengajuan =  $this->request->getParam('status_pengajuan');
        if ($pengajuanak1->status_pengajuan =  2) {
          $dateNow = Carbon::now();
          $MasaAktip = date('Y-m-d', strtotime('+2 year', strtotime( $dateNow)));
          $pengajuanak1->exp_date =  $MasaAktip;
        }
        $pengajuanak1->save();

        $ak1 = new BkolPencariKerja;
        $ak1->user_id =  $user->id;
        $ak1->id =  $user->id;
        $ak1->tanggal_daftar = $this->request->getParam('tanggal_daftar');
        $ak1->nomor_pencaker =$this->request->getParam('nomor_pencaker');
        $ak1->nama_lengkap =  $this->request->getParam('nama_lengkap');
        $ak1->keahlian =  $this->request->getParam('keahlian');
        $ak1->keterangan =  $this->request->getParam('keterangan');
        $ak1->usia =  $this->request->getParam('usia');
        $ak1->melamar_ke =  $this->request->getParam('melamar_ke');
        $ak1->no_ktp =  $this->request->getParam('no_ktp');
        $ak1->tempat_lahir =  $this->request->getParam('tempat_lahir');
        $ak1->tanggal_lahir =  $this->request->getParam('tanggal_lahir');
        $ak1->jenis_kelamin =  $this->request->getParam('jenis_kelamin');
        $ak1->agama =  $this->request->getParam('agama');
        $ak1->kondisi_fisik =  $this->request->getParam('kondisi_fisik');
        $ak1->status_perkawinan =  $this->request->getParam('status_perkawinan');
        $ak1->tinggi_badan =  $this->request->getParam('tinggi_badan');
        $ak1->berat_badan =  $this->request->getParam('berat_badan');
        $ak1->no_telp =  $this->request->getParam('no_telp');
        $ak1->alamat_lengkap =  $this->request->getParam('alamat_lengkap');
        $ak1->provinsi_id =  32;
        $ak1->kabupatenkota_id =  77;
        $ak1->kecamatan_id =  $this->request->getParam('kecamatan_id') ? $this->request->getParam('kecamatan_id') : NULL;
        $ak1->kelurahan_id =  $this->request->getParam('kelurahan_id') ? $this->request->getParam('kelurahan_id') : NULL;
        $ak1->kodepos =  $this->request->getParam('kodepos');
        $ak1->pendidikan_terakhir_id =  $this->request->getParam('pendidikan_terakhir_id') ? $this->request->getParam('pendidikan_terakhir_id'): NULL;
        $ak1->jurusan_pendidikan_id =  $this->request->getParam('jurusan_pendidikan_id') ? $this->request->getParam('jurusan_pendidikan_id') : NULL;
        $ak1->nama_instansi_pendidikan =  $this->request->getParam('nama_instansi_pendidikan');
        $ak1->tahun_lulus =  $this->request->getParam('tahun_lulus');
        $ak1->nilai_ijazah_ipk =  $this->request->getParam('nilai_ijazah_ipk');
        $ak1->harapan_wilayah_pekerjaan =  $this->request->getParam('harapan_wilayah_pekerjaan');
        $ak1->provinsi_dalam_negeri_id =  $this->request->getParam('provinsi_dalam_negeri_id') ? $this->request->getParam('provinsi_dalam_negeri_id') : NULL;
        $ak1->kabupatenkota_dalam_negeri_id =  $this->request->getParam('kabupatenkota_dalam_negeri_id') ? $this->request->getParam('kabupatenkota_dalam_negeri_id') : NULL;
        $ak1->negara_luar_negeri_id =  $this->request->getParam('negara_luar_negeri_id') ? $this->request->getParam('negara_luar_negeri_id') : NULL;
        $ak1->sistem_pembayaran =  $this->request->getParam('sistem_pembayaran');
        $ak1->harapan_gaji_minimum_id =  $this->request->getParam('harapan_gaji_minimum_id') ? $this->request->getParam('harapan_gaji_minimum_id') : NULL;
        $ak1->jenis_jabatan_yang_diminati_id =  $this->request->getParam('jenis_jabatan_yang_diminati_id') ? $this->request->getParam('jenis_jabatan_yang_diminati_id') : NULL;
        // if ($ak1->save()) {
        //     foreach ($this->container->request->getParam('skillbahasa') as $BAHASA) {
        //         $addBahasa = new SkillBahasa;
        //         $addBahasa->user_id = $ak1->id;
        //         $addBahasa->bahasa_id = $BAHASA;
        //         $addBahasa->save();
        //     }
        // }

        $ak1->save();

        $role = $this->auth->findRoleByName('Jobseeker');
        $role->users()->attach($user);


    }
    private function validateUserData($user = null)
    {
        // Validate Form Data
        $validateData = array(
            // 'fullname' => array(
            //     'rules' => V::length(2, 25),
            //     'messages' => array(
            //         'length' => 'Must be between 2 and 25 characters.'
            //     )
            // ),
            // 'email' => array(
            //     'rules' => V::noWhitespace()->email(),
            //     'messages' => array(
            //         'email' => 'Enter a valid email address.',
            //         'noWhitespace' => 'Must not contain any spaces.'
            //     )
            // ),
            // 'username' => array(
            //     'rules' => V::noWhitespace()->alnum(),
            //     'messages' => array(
            //         'slug' => 'Must be alpha numeric with no spaces.',
            //         'noWhitespace' => 'Must not contain any spaces.'
            //     )
            // )
        );

        if (!$user) {
            $validateData['password'] = array(
                'rules' => V::noWhitespace()->length(6, 25),
                'messages' => array(
                    'noWhitespace' => 'Must not contain spaces.',
                    'length' => 'Must be between 6 and 25 characters.'
                )
            );

            $validateData['password_confirm'] = array(
                'rules' => V::equals($this->request->getParam('password')),
                'messages' => array(
                    'equals' => 'Passwords do not match.'
                )
            );

            // Validate Username
            if ($this->auth->findByCredentials(['login' => $this->request->getParam('username')])) {
                $this->validator->addError('username', 'User already exists with this username.');
            }

            // Validate Email
            if ($this->auth->findByCredentials(['login' => $this->request->getParam('email')])) {
                $this->validator->addError('email', 'User already exists with this email.');
            }
        }
        $this->validator->validate($this->request, $validateData);

        if ($user) {
            // Validate Username
            if ($this->auth->findByCredentials(['login' => $this->request->getParam('username')]) &&
                $user->username != $this->request->getParam('username')) {
                $this->validator->addError('username', 'User already exists with this username.');
            }

            // Validate Email
            if ($this->auth->findByCredentials(['login' => $this->request->getParam('email')]) &&
                $user->email != $this->request->getParam('email')) {
                $this->validator->addError('email', 'User already exists with this email.');
            }

        }
    }

    private function editUser($user = null)
    {
        if (!$user) {
            return false;
        }

        $user->fullname =  $this->request->getParam('fullname');
        if ($user->save()) {
            $ak1 = new AK1Model;
            $ak1 = $ak1->find($user->id);
            $ak1->status_pengajuan =  $this->request->getParam('status_pengajuan');
            if ($ak1->status_pengajuan =  2) {
              $dateNow = Carbon::now();
              $MasaAktip = date('Y-m-d', strtotime('+2 year', strtotime( $dateNow)));
              $ak1->exp_date =  $MasaAktip;
            }
            $ak1->save();
            $bkolpencarikerja = new BkolPencariKerja;
            $bkolpencarikerja = $bkolpencarikerja->find($user->id);
            $bkolpencarikerja->tanggal_daftar = $this->request->getParam('tanggal_daftar');
            $bkolpencarikerja->nomor_pencaker =$this->request->getParam('nomor_pencaker');
            $bkolpencarikerja->nama_lengkap =  $this->request->getParam('nama_lengkap');
            $bkolpencarikerja->keahlian =  $this->request->getParam('keahlian');
            $bkolpencarikerja->keterangan =  $this->request->getParam('keterangan');
            $bkolpencarikerja->usia =  $this->request->getParam('usia');
            $bkolpencarikerja->melamar_ke =  $this->request->getParam('melamar_ke');
            $bkolpencarikerja->no_ktp =  $this->request->getParam('no_ktp');
            $bkolpencarikerja->tempat_lahir =  $this->request->getParam('tempat_lahir');
            $bkolpencarikerja->tanggal_lahir =  $this->request->getParam('tanggal_lahir');
            $bkolpencarikerja->jenis_kelamin =  $this->request->getParam('jenis_kelamin');
            $bkolpencarikerja->agama =  $this->request->getParam('agama');
            $bkolpencarikerja->kondisi_fisik =  $this->request->getParam('kondisi_fisik');
            $bkolpencarikerja->status_perkawinan =  $this->request->getParam('status_perkawinan');
            $bkolpencarikerja->tinggi_badan =  $this->request->getParam('tinggi_badan');
            $bkolpencarikerja->berat_badan =  $this->request->getParam('berat_badan');
            $bkolpencarikerja->no_telp =  $this->request->getParam('no_telp');
            $bkolpencarikerja->alamat_lengkap =  $this->request->getParam('alamat_lengkap');
            $bkolpencarikerja->provinsi_id =  32;
            $bkolpencarikerja->kabupatenkota_id =  77;
            $bkolpencarikerja->kecamatan_id =  $this->request->getParam('kecamatan_id') ? $this->request->getParam('kecamatan_id') : NULL;
            $bkolpencarikerja->kelurahan_id =  $this->request->getParam('kelurahan_id') ? $this->request->getParam('kelurahan_id') : NULL;
            $bkolpencarikerja->kodepos =  $this->request->getParam('kodepos');
            $bkolpencarikerja->pendidikan_terakhir_id =  $this->request->getParam('pendidikan_terakhir_id') ? $this->request->getParam('pendidikan_terakhir_id'): NULL;
            $bkolpencarikerja->jurusan_pendidikan_id =  $this->request->getParam('jurusan_pendidikan_id') ? $this->request->getParam('jurusan_pendidikan_id') : NULL;
            $bkolpencarikerja->nama_instansi_pendidikan =  $this->request->getParam('nama_instansi_pendidikan');
            $bkolpencarikerja->tahun_lulus =  $this->request->getParam('tahun_lulus');
            $bkolpencarikerja->nilai_ijazah_ipk =  $this->request->getParam('nilai_ijazah_ipk');
            $bkolpencarikerja->harapan_wilayah_pekerjaan =  $this->request->getParam('harapan_wilayah_pekerjaan');
            $bkolpencarikerja->provinsi_dalam_negeri_id =  $this->request->getParam('provinsi_dalam_negeri_id') ? $this->request->getParam('provinsi_dalam_negeri_id') : NULL;
            $bkolpencarikerja->kabupatenkota_dalam_negeri_id =  $this->request->getParam('kabupatenkota_dalam_negeri_id') ? $this->request->getParam('kabupatenkota_dalam_negeri_id') : NULL;
            $bkolpencarikerja->negara_luar_negeri_id =  $this->request->getParam('negara_luar_negeri_id') ? $this->request->getParam('negara_luar_negeri_id') : NULL;
            $bkolpencarikerja->sistem_pembayaran =  $this->request->getParam('sistem_pembayaran');
            $bkolpencarikerja->harapan_gaji_minimum_id =  $this->request->getParam('harapan_gaji_minimum_id') ? $this->request->getParam('harapan_gaji_minimum_id') : NULL;
            $bkolpencarikerja->jenis_jabatan_yang_diminati_id =  $this->request->getParam('jenis_jabatan_yang_diminati_id') ? $this->request->getParam('jenis_jabatan_yang_diminati_id') : NULL;
            // if ($bkolpencarikerja->save()) {
            //     SkillBahasa::where('user_id', $bkolpencarikerja->id)->delete();
            //     foreach ($this->container->request->getParam('skillbahasa') as $BAHASA) {
            //         $addBahasa = new SkillBahasa;
            //         $addBahasa->user_id = $bkolpencarikerja->id;
            //         $addBahasa->bahasa_id = $BAHASA;
            //         $addBahasa->save();
            //     }
            // }
            $bkolpencarikerja->save();
        }


        return true;
    }

    public function Ak1Edit(Request $request, Response $response, $userid)
    {

        if ($check = $this->sentinel->hasPerm('user.update')) {
            return $check;
        }

        $user = U::with('pengajuanAK1')->find($userid);
        $ak1 = AK1Model::find($userid);

        if (!$user) {
            $this->flash('danger', 'Sorry, that user was not found.');
            return $response->withRedirect($this->router->pathFor('bkol-pencarikerja'));
        }

        if ($request->isPost()) {
            $this->validateUserData($user);

            if ($this->validator->isValid()) {
                if ($this->editUser($user)) {
                    $this->flash('success', $user->username.' has been updated successfully.');
                    return $this->redirect($response, 'ak1-admin');
                }
            }

            $this->flash('danger', 'There was an error updating that user.');
            return $this->redirect($response, 'ak1-admin');
        }
        $daerahs = Daerah::where('lokasi_kabupatenkota', 0)
                            ->where('lokasi_kecamatan', 0)
                            ->where('lokasi_kelurahan', 0)
                            ->orderBy('lokasi_nama')
                            ->get();
        $jenispendidikan = JenisPendidikanModel::where('kode_jurusan', 0)
        ->orderBy('jenis_pendidikan')
        ->get();

        return $this->view->render($response,
         'dashboard/ak1/edit.twig',
         [
             'user' => $user,
             'jenispendidikan' => $jenispendidikan,
             'daerahs' => $daerahs,
             'golonganjabatan' => GolonganJabatanModel::get(),
             'negara' => NegaraModel::get(),
             'bahasa' => Bahasa::get(),
             'gaji' => GajiModel::get()
        ]);
    }

    public function ak1Delete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('user.delete')) {
            return $check;
        }

        $user = AK1Model::find($request->getParam('user_id'));
        $ak1 = AK1Model::find($request->getParam('user_id'));
        if ($ak1->delete()) {
            $this->flash('success', 'User has been deleted successfully.');
            return $this->redirect($response, 'ak1-admin');
        }

        $this->flash('danger'.'There was an error deleting the user.');
        return $this->redirect($response, 'ak1-admin');
    }

    public function Ak1Detail(Request $request, Response $response, $userid)
    {
        if ($check = $this->sentinel->hasPerm('user.update')) {
            return $check;
        }

        $user = U::with('pengajuanAK1')->find($userid);
        $ak1 = AK1Model::find($userid);

        if (!$user) {
            $this->flash('danger', 'Sorry, that user was not found.');
            return $response->withRedirect($this->router->pathFor('bkol-pencarikerja'));
        }

        if ($request->isPost()) {
            $this->validateUserData($user);

            if ($this->validator->isValid()) {
                if ($this->editUser($user)) {
                    $this->flash('success', $user->username.' has been updated successfully.');
                    return $this->redirect($response, 'ak1-admin');
                }
            }

            $this->flash('danger', 'There was an error updating that user.');
            return $this->redirect($response, 'ak1-admin');
        }
        return $this->view->render($response,
         'dashboard/ak1/detail.twig',
         [
             'user' => $user
        ]);
    }

    public function BelumLengkap(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('user.delete')) {
            return $check;
        }

        $user = $this->auth->findById($request->getParam('user_id'));

        if ($request->isPost()) {
            $this->validateUserData($user);
            if ($this->validator->isValid()) {
                if ($user->save()) {
                    $ak1 = new AK1Model;
                    $ak1 = $ak1->find($user->id);
                    $ak1->status_pengajuan =  0;
                    $ak1->save();
                    $this->flash('success', 'Data Pengajuan AK1 Sudah Lengkap');
                    return $this->redirect($response, 'ak1-admin');
                }
            }

            $this->flash('danger', 'There was an error updating that user.');
            return $this->redirect($response, 'ak1-admin');
        }

        $this->flash('danger'.'There was an error deleting the user.');
        return $this->redirect($response, 'ak1-admin');
    }
    public function Lengkap(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('user.delete')) {
            return $check;
        }

        $user = $this->auth->findById($request->getParam('user_id'));

        if ($request->isPost()) {
            $this->validateUserData($user);
            if ($this->validator->isValid()) {
                if ($user->save()) {
                    $ak1 = new AK1Model;
                    $ak1 = $ak1->find($user->id);
                    $ak1->status_pengajuan =  1;
                    $ak1->save();
                    $this->flash('success', 'User has been deleted successfully.');
                    return $this->redirect($response, 'ak1-admin');
                }
            }

            $this->flash('danger', 'There was an error updating that user.');
            return $this->redirect($response, 'ak1-admin');
        }

        $this->flash('danger'.'There was an error deleting the user.');
        return $this->redirect($response, 'ak1-admin');
    }

    // public function SiapCetak(Request $request, Response $response)
    // {
    //     if ($check = $this->sentinel->hasPerm('user.delete')) {
    //         return $check;
    //     }

    //     $user = $this->auth->findById($request->getParam('user_id'));

    //     if ($request->isPost()) {
    //         $this->validateUserData($user);
    //         if ($this->validator->isValid()) {
    //             if ($user->save()) {
    //                 $ak1 = new AK1Model;
    //                 $ak1 = $ak1->find($user->id);
    //                 $MasaAktip = date('Y-m-d', strtotime('+2 year', strtotime( $dateNow)));
    //                 $ak1->status_pengajuan =  2;
    //                 $ak1->save();
    //                 $this->flash('success', 'User has been deleted successfully.');
    //                 return $this->redirect($response, 'ak1-admin');
    //             }
    //         }

    //         $this->flash('danger', 'There was an error updating that user.');
    //         return $this->redirect($response, 'ak1-admin');
    //     }

    //     $this->flash('danger'.'There was an error deleting the user.');
    //     return $this->redirect($response, 'ak1-admin');
    // }
    public function SudahJadi(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('user.delete')) {
            return $check;
        }

        $user = $this->auth->findById($request->getParam('user_id'));

        if ($request->isPost()) {
            $this->validateUserData($user);
            if ($this->validator->isValid()) {
                if ($user->save()) {
                    $ak1 = new AK1Model;
                    $ak1 = $ak1->find($user->id);
                    $dateNow = Carbon::now();
                    $MasaAktip = date('Y-m-d', strtotime('+2 year', strtotime( $dateNow)));
                    $ak1->status_pengajuan =  2;
                    $ak1->exp_date =  $MasaAktip;
                    $ak1->save();
                    $this->flash('success', 'User has been deleted successfully.');
                    return $this->redirect($response, 'ak1-admin');
                }
            }

            $this->flash('danger', 'There was an error updating that user.');
            return $this->redirect($response, 'ak1-admin');
        }

        $this->flash('danger'.'There was an error deleting the user.');
        return $this->redirect($response, 'ak1-admin');
    }

    private function processSkillBahasa()
    {
        foreach ($this->container->request->getParam('skillbahasa') as $value) {

        }
    }
}
