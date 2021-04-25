<?php

namespace App\Controller;

use App\Library\Email as E;
use App\Library\Recaptcha;
use App\Model\Oauth2Providers;
use App\Model\BkolPencariKerja;
use App\Model\BkolPerusahaan;
use App\Model\PemerintahModel;
use App\Model\JenisInstansiModel;
use App\Model\BkkModel;
use App\Model\LpkModel;
use App\Model\PerguruanTinggiModel;
use App\Model\Daerah;
use App\Model\JenisPendidikanModel;
use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use Cartalyst\Sentinel\Checkpoints\NotActivatedException;
use Cartalyst\Sentinel\Reminders\Reminder;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Auth extends Controller
{

    public function activate(Request $request, Response $response)
    {
        $credentials = [
            'email' => $request->getParam('email')
        ];

        $user = $this->auth->findByCredentials($credentials);

        if ($user) {
            $activations = $this->auth->getActivationRepository();

            $activation = $activations->complete($user, $request->getParam('code'));

            if ($activation) {
                $this->auth->login($user);

                $sendEmail = new E($this->container);
                $sendEmail = $sendEmail->sendTemplate(array($user->id), 'registration');



                $this->flash('success', 'Akun Anda berhasil diaktifkan dan Anda telah masuk.');
                return $this->redirect($response, 'home');
            }

            $this->flash('danger', 'Maaf, kredensial aktivasi Anda salah.');
            return $this->redirect($response, 'home');
        }

        $this->flash('danger', 'Akun tersebut tidak ada.');
        return $this->redirect($response, 'home');
    }

    public function forgotPassword(Request $request, Response $response)
    {
        if ($request->isPost()) {
            // Validate Data
            $validateData = array(
                'email' => array(
                    'rules' => V::email(),
                    'messages' => array(
                        'email' => 'Pastikan alamat email yang valid.'
                        )
                )
            );
            $this->validator->validate($request, $validateData);

            // Validate Recaptcha
            // $recaptcha = new Recaptcha($this->container);
            // $recaptcha = $recaptcha->validate($request->getParam('g-recaptcha-response'));
            // if (!$recaptcha) {
            //     $this->validator->addError('recaptcha', 'Recaptcha was invalid.');
            // }

            $credentials = [
                'email' => $request->getParam('email')
            ];

            $user = $this->auth->findByCredentials($credentials);

            if (!$user) {
                $this->validator->addError('email', 'Tidak ada akun valid dengan email itu.');
            }

            if ($this->validator->isValid()) {
                $reminders = $this->auth->getReminderRepository();

                $reminder = $reminders->create($user);
                $reminder = $reminder->code;

                $resetUrl = "http://" . $this->config['domain-bkol'] .
                    "/reset-password?reminder=" . $reminder . "&email=" . $request->getParam('email');

                $sendEmail = new E($this->container);
                $sendEmail = $sendEmail->sendTemplate(
                    array($user->id),
                    'password-reset',
                    array('reset_url' => $resetUrl)
                );
                if ($sendEmail['status'] == "error") {
                    $this->flash('danger', 'Email Yang Berisikan Link untuk merubah password GAGAL terkirim.');
                    return $this->redirect($response, 'forgot-password');
                }

                $this->flash(
                    'success',
                    'Email Yang Berisikan Link Untuk Merubah Password BERHASIL Terkirim ke : ' . $request->getParam('email')
                );
                return $this->redirect($response, 'login');
            }
        }

        return $this->view->render($response, 'bkol/auth/forgot-password.twig');
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity) At threshold
     */
    public function login(Request $request, Response $response)
    {
        if ($request->isPost()) {
            $credentials = [
                'username' => $request->getParam('login'),
                'password' => $request->getParam('password')
            ];
            if (filter_var($request->getParam('login'), FILTER_VALIDATE_EMAIL)) {
                $credentials = [
                    'email' => $request->getParam('login'),
                    'password' => $request->getParam('password')
                ];
            }

            $remember = $request->getParam('remember') ? true : false;

            // Validate Recaptcha
            $recaptcha = new Recaptcha($this->container);
            $recaptcha = $recaptcha->validate($this->request->getParam('g-recaptcha-response'));
            if (!$recaptcha) {
                $this->validator->addError('recaptcha', 'Recaptcha was invalid.');
            }

            if ($this->validator->isValid()) {
                if ($this->processLogin($credentials, $remember)) {
                    $this->flashNow('success', 'Anda telah masuk.');
                    if ($request->getParam('redirect') !== null) {
                        return $response->withRedirect($request->getParam('redirect'));
                    }
                    if ($this->auth->inRole("admin")) {
                        return $this->redirect($response, 'dashboard');
                    }
                    if ($this->auth->inRole("manager")) {
                        return $this->redirect($response, 'dashboard');
                    }
                    if ($this->auth->inRole("companies")) {
                        return $this->redirect($response, 'dashboardperusahaan');
                    }
                    if ($this->auth->inRole("bkk")) {
                        return $this->redirect($response, 'bkk-dashboard');
                    }
                    if ($this->auth->inRole("lpk")) {
                        return $this->redirect($response, 'lpk-dashboard');
                    }
                    if ($this->auth->inRole("perguruan-tinggi")) {
                        return $this->redirect($response, 'pt-dashboard');
                    }
                    if ($this->auth->inRole("jobseeker")) {
                        return $this->redirect($response, 'dashboardpencaker');
                    }
                    if ($this->auth->inRole("pemerintah")) {
                        return $this->redirect($response, 'dashboardpemerintah');
                    }

                    
                    return $this->redirect($response, 'home');
                }
            }
        }

    
        return $this->view->render(
            $response,
            'bkol/auth/login.twig'
        );
    }

    public function loginModal(Request $request, Response $response)
    {
        if ($request->isPost()) {
            $credentials = [
                'username' => $request->getParam('login'),
                'password' => $request->getParam('password')
            ];
            if (filter_var($request->getParam('login'), FILTER_VALIDATE_EMAIL)) {
                $credentials = [
                    'email' => $request->getParam('login'),
                    'password' => $request->getParam('password')
                ];
            }
            $remember = $request->getParam('remember') ? true : false;
            // Validate Recaptcha
            $recaptcha = new Recaptcha($this->container);
            $recaptcha = $recaptcha->validate($this->request->getParam('g-recaptcha-response'));
            if (!$recaptcha) {
                $this->validator->addError('recaptcha', 'Recaptcha was invalid.');
            }

            if ($this->validator->isValid()) {
                if ($this->processLogin($credentials, $remember)) {
                    $this->flashNow('success', 'Anda telah masuk.');
                    if ($request->getParam('redirect') !== null) {
                        return $response->withRedirect($request->getParam('redirect'));
                    }
                    if ($this->auth->inRole("admin")) {
                        return $this->redirect($response, 'dashboard');
                    }
                    if ($this->auth->inRole("manager")) {
                        return $this->redirect($response, 'dashboard');
                    }
                    if ($this->auth->inRole("companies")) {
                        return $this->redirect($response, 'dashboardperusahaan');
                    }

                    if ($this->auth->inRole("jobseeker")) {
                        return $this->redirect($response, 'dashboardpencaker');
                    }
                    return $this->redirect($response, 'home');
                }
            }
        }

        return $this->view->render(
            $response,
            'bkol/auth/login-modal.twig'
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function logout(Request $request, Response $response)
    {
      $this->auth->logout();

      $this->session->clear();

      $this->flash('success', 'Anda telah keluar.');
      return $this->redirect($response, 'home');
    }

    public function logoutbkol(Request $request, Response $response)
    {
      $this->auth->logout();

      $this->session->clear();

      $this->flash('success', 'Anda telah keluar.');
      return $this->redirect($response, 'home');
    }

    public function register(Request $request, Response $response)
    {
        if ($request->isPost()) {
            // Validate Data
            $this->validateNewJobseeker();

            if ($this->validator->isValid()) {
                $role = $this->auth->findRoleByName('Jobseeker');
                $userDetails = [
                    // 'nik' => $request->getParam('nik'),
                    // 'fullname' => $request->getParam('fullname'),
                    'email' => $request->getParam('email'),
                    'username' => $request->getParam('username'),
                    'password' => $request->getParam('password'),
                    'bkk_id' => $request->getParam('bkk_id'),
                    // 'placeofbirth' => $request->getParam('placeofbirth'),
                    // 'dateofbirth' => $request->getParam('dateofbirth'),
                    // 'gender' => $request->getParam('gender'),
                    // 'religion' => $request->getParam('religion'),
                    // 'nationality' => $request->getParam('nationality'),
                    // 'address' => $request->getParam('address'),
                    // 'districts' => $request->getParam('districts'),
                    // 'maritalstatus' => $request->getParam('maritalstatus'),
                    // 'phonenumber' => $request->getParam('phonenumber'),
                    'permissions' => [
                        'user.delete' => 0
                    ]
                ];

                if ($this->config['activation']) {

                    $user = $this->auth->register($userDetails);
                    $role->users()->attach($user);

                    $activations = $this->auth->getActivationRepository();

                    $activations = $activations->create($user);
                    $code = $activations->code;

                    $confirmUrl = "https://" . $this->config['domain-bkol'] . "/activate?code=" .
                        $code . "&email=" . $user->email;


                    $sendEmail = new E($this->container);
                    $sendEmail = $sendEmail->sendTemplate(
                        array($user->id),
                        'activation',
                        array('confirm_url' => $confirmUrl)
                    );

                    if ($sendEmail['status'] == "error") {
                        $this->flash(
                            'danger',
                            'Terjadi kesalahan saat mengirim email aktivasi Anda. Silakan hubungi contact support.'
                        );
                        return $this->redirect($response, 'forgot-password');
                    }

                    $this->flash(
                        'success',
                        'Anda harus mengaktifkan akun Anda sebelum Anda dapat masuk. Petunjuk telah dikirim ke: ' .
                            $request->getParam('email')
                    );
                    return $this->redirect($response, 'daftar-pencaker-berhasil');
                    // return $this->view->render(
                    //     $response,
                    //     'bkol/auth/aktifasi.twig'
                    // );
                }

                $user = $this->auth->registerAndActivate($userDetails);
                $role->users()->attach($user);

                $sendEmail = new E($this->container);
                $sendEmail = $sendEmail->sendTemplate(array($user->id), 'registration');

                $this->flash('success', 'Your account has been created.');

                $this->auth->login($user);

                return $this->redirect($response, 'home');

                $this->logger->addInfo("Pendaftaran pengguna baru.", array("user" => $user));
            }
        }
        return $this->view->render(
            $response,
            'bkol/auth/daftar.twig'
        );
    }
    public function registerBkk(Request $request, Response $response)
    {
        $bkk = BkkModel::select('id','nama_bkk')->get();

        if ($request->isPost()) {
            // Validate Data
            $this->validateNewBkk();

            if ($this->validator->isValid()) {
                $role = $this->auth->findRoleByName('bkk');
                $userDetails = [
                    // 'nik' => $request->getParam('nik'),
                    'fullname' => $request->getParam('fullname'),
                    'email' => $request->getParam('email'),
                    'username' => $request->getParam('username'),
                    'password' => $request->getParam('password'),
                    'bkk_id' => $request->getParam('bkk_id'),
                    // 'placeofbirth' => $request->getParam('placeofbirth'),
                    // 'dateofbirth' => $request->getParam('dateofbirth'),
                    // 'gender' => $request->getParam('gender'),
                    // 'religion' => $request->getParam('religion'),
                    // 'nationality' => $request->getParam('nationality'),
                    // 'address' => $request->getParam('address'),
                    // 'districts' => $request->getParam('districts'),
                    // 'maritalstatus' => $request->getParam('maritalstatus'),
                    'phonenumber' => $request->getParam('phonenumber'),
                    'permissions' => [
                        'user.delete' => 0
                    ]
                ];

                if ($this->config['activation']) {

                    $user = $this->auth->register($userDetails);
                    $role->users()->attach($user);
                    $activations = $this->auth->getActivationRepository();
                    $activations = $activations->create($user);
                    $code = $activations->code;
                    $confirmUrl = "https://" . $this->config['domain-bkol'] . "/activate?code=" .
                        $code . "&email=" . $user->email;
                    $sendEmail = new E($this->container);
                    $sendEmail = $sendEmail->sendTemplate(
                        array($user->id),
                        'activation',
                        array('confirm_url' => $confirmUrl)
                    );

                    if ($sendEmail['status'] == "error") {
                        $this->flash(
                            'danger',
                            'Terjadi kesalahan saat mengirim email aktivasi Anda. Silakan hubungi contact support.'
                        );
                        return $this->redirect($response, 'forgot-password');
                    }

                    $this->flash(
                        'success',
                        'Anda harus mengaktifkan akun Anda sebelum Anda dapat masuk. Petunjuk telah dikirim ke: ' .
                            $request->getParam('email')
                    );
                    return $this->redirect($response, 'daftar-pencaker-berhasil');
                    // return $this->view->render(
                    //     $response,
                    //     'bkol/auth/aktifasi.twig'
                    // );
                }

                $user = $this->auth->registerAndActivate($userDetails);
                $role->users()->attach($user);

                $sendEmail = new E($this->container);
                $sendEmail = $sendEmail->sendTemplate(array($user->id), 'registration');

                $this->flash('success', 'Your account has been created.');

                $this->auth->login($user);

                return $this->redirect($response, 'home');

                $this->logger->addInfo("Pendaftaran pengguna baru.", array("user" => $user));
            }
        }

        return $this->view->render(
            $response,
            'bkol/auth/daftar_akademi.twig',
            array(
                "bkk" => $bkk
            )
        );
    }

    public function registerLpk(Request $request, Response $response)
    {
        $lpk = LpkModel::select('id','nama_lpk')->get();

        if ($request->isPost()) {
            // Validate Data
            $this->validateNewLpk();

            if ($this->validator->isValid()) {
                $role = $this->auth->findRoleByName('lpk');
                $userDetails = [
                    // 'nik' => $request->getParam('nik'),
                    'fullname' => $request->getParam('fullname'),
                    'email' => $request->getParam('email'),
                    'username' => $request->getParam('username'),
                    'password' => $request->getParam('password'),
                    'lpk_id' => $request->getParam('lpk_id'),
                    // 'placeofbirth' => $request->getParam('placeofbirth'),
                    // 'dateofbirth' => $request->getParam('dateofbirth'),
                    // 'gender' => $request->getParam('gender'),
                    // 'religion' => $request->getParam('religion'),
                    // 'nationality' => $request->getParam('nationality'),
                    // 'address' => $request->getParam('address'),
                    // 'districts' => $request->getParam('districts'),
                    // 'maritalstatus' => $request->getParam('maritalstatus'),
                    'phonenumber' => $request->getParam('phonenumber'),
                    'permissions' => [
                        'user.delete' => 0
                    ]
                ];

                if ($this->config['activation']) {

                    $user = $this->auth->register($userDetails);
                    $role->users()->attach($user);
                    $activations = $this->auth->getActivationRepository();
                    $activations = $activations->create($user);
                    $code = $activations->code;
                    $confirmUrl = "https://" . $this->config['domain-bkol'] . "/activate?code=" .
                        $code . "&email=" . $user->email;
                    $sendEmail = new E($this->container);
                    $sendEmail = $sendEmail->sendTemplate(
                        array($user->id),
                        'activation',
                        array('confirm_url' => $confirmUrl)
                    );

                    if ($sendEmail['status'] == "error") {
                        $this->flash(
                            'danger',
                            'Terjadi kesalahan saat mengirim email aktivasi Anda. Silakan hubungi contact support.'
                        );
                        return $this->redirect($response, 'forgot-password');
                    }

                    $this->flash(
                        'success',
                        'Anda harus mengaktifkan akun Anda sebelum Anda dapat masuk. Petunjuk telah dikirim ke: ' .
                            $request->getParam('email')
                    );
                    return $this->redirect($response, 'daftar-pencaker-berhasil');
                    // return $this->view->render(
                    //     $response,
                    //     'bkol/auth/aktifasi.twig'
                    // );
                }

                $user = $this->auth->registerAndActivate($userDetails);
                $role->users()->attach($user);

                $sendEmail = new E($this->container);
                $sendEmail = $sendEmail->sendTemplate(array($user->id), 'registration');

                $this->flash('success', 'Your account has been created.');

                $this->auth->login($user);

                return $this->redirect($response, 'home');

                $this->logger->addInfo("Pendaftaran pengguna baru.", array("user" => $user));
            }
        }

        return $this->view->render(
            $response,
            'bkol/auth/daftar_lpk.twig',
            array(
                "lpk" => $lpk
            )
        );
    }

    public function registerPerguruanTinggi(Request $request, Response $response)
    {
        $perguruantinggi = PerguruanTinggiModel::select('id','nama_perguruan_tinggi')->get();

        if ($request->isPost()) {
            // Validate Data
            $this->validateNewPerguruanTinggi();

            if ($this->validator->isValid()) {
                $role = $this->auth->findRoleBySlug('perguruan-tinggi');
                $userDetails = [
                    'fullname' => $request->getParam('fullname'),
                    'email' => $request->getParam('email'),
                    'username' => $request->getParam('username'),
                    'password' => $request->getParam('password'),
                    'perguruan_tinggi_id' => $request->getParam('perguruan_tinggi_id'),
                    'phonenumber' => $request->getParam('phonenumber'),
                    'permissions' => [
                        'user.delete' => 0
                    ]
                ];

                if ($this->config['activation']) {

                    $user = $this->auth->register($userDetails);
                    $role->users()->attach($user);
                    $activations = $this->auth->getActivationRepository();
                    $activations = $activations->create($user);
                    $code = $activations->code;
                    $confirmUrl = "https://" . $this->config['domain-bkol'] . "/activate?code=" .
                        $code . "&email=" . $user->email;
                    $sendEmail = new E($this->container);
                    $sendEmail = $sendEmail->sendTemplate(
                        array($user->id),
                        'activation',
                        array('confirm_url' => $confirmUrl)
                    );

                    if ($sendEmail['status'] == "error") {
                        $this->flash(
                            'danger',
                            'Terjadi kesalahan saat mengirim email aktivasi Anda. Silakan hubungi contact support.'
                        );
                        return $this->redirect($response, 'forgot-password');
                    }

                    $this->flash(
                        'success',
                        'Anda harus mengaktifkan akun Anda sebelum Anda dapat masuk. Petunjuk telah dikirim ke: ' .
                            $request->getParam('email')
                    );
                    return $this->redirect($response, 'daftar-pencaker-berhasil');
                    // return $this->view->render(
                    //     $response,
                    //     'bkol/auth/aktifasi.twig'
                    // );
                }

                $user = $this->auth->registerAndActivate($userDetails);
                $role->users()->attach($user);

                $sendEmail = new E($this->container);
                $sendEmail = $sendEmail->sendTemplate(array($user->id), 'registration');

                $this->flash('success', 'Your account has been created.');

                $this->auth->login($user);

                return $this->redirect($response, 'home');

                $this->logger->addInfo("Pendaftaran pengguna baru.", array("user" => $user));
            }
        }

        return $this->view->render(
            $response,
            'bkol/auth/daftar_akademi.twig',
            array(
                "perguruantinggi" => $perguruantinggi
            )
        );
    }

    public function registerAkademi(Request $request, Response $response)
    {
        $bkk = BkkModel::select('id','nama_bkk')->get();
        $perguruantinggi = PerguruanTinggiModel::select('id','nama_perguruan_tinggi')->get();
        return $this->view->render(
            $response,
            'bkol/auth/daftar_akademi.twig',
            array(
                "perguruantinggi" => $perguruantinggi,
                "bkk" => $bkk
            )
        );
    }

    public function registerJobseeker(Request $request, Response $response)
    {
        if ($request->isPost()) {
            // Validate Data
            $this->validateNewJobseeker();

            if ($this->validator->isValid()) {
                $role = $this->auth->findRoleByName('Jobseeker');
                $userDetails = [
                    // 'nik' => $request->getParam('nik'),
                    // 'fullname' => $request->getParam('fullname'),
                    'email' => $request->getParam('email'),
                    'username' => $request->getParam('username'),
                    'password' => $request->getParam('password'),
                    // 'placeofbirth' => $request->getParam('placeofbirth'),
                    // 'dateofbirth' => $request->getParam('dateofbirth'),
                    // 'gender' => $request->getParam('gender'),
                    // 'religion' => $request->getParam('religion'),
                    // 'nationality' => $request->getParam('nationality'),
                    // 'address' => $request->getParam('address'),
                    // 'districts' => $request->getParam('districts'),
                    // 'maritalstatus' => $request->getParam('maritalstatus'),
                    // 'phonenumber' => $request->getParam('phonenumber'),
                    'permissions' => [
                        'user.delete' => 0
                    ]
                ];

                if ($this->config['activation']) {

                    $user = $this->auth->register($userDetails);
                    $role->users()->attach($user);

                    $activations = $this->auth->getActivationRepository();

                    $activations = $activations->create($user);
                    $code = $activations->code;

                    $confirmUrl = "https://" . $this->config['domain-bkol'] . "/activate?code=" .
                        $code . "&email=" . $user->email;

                    $bkolpencarikerja = new BkolPencariKerja;
                    $bkolpencarikerja->user_id =  $user->id;
                    $bkolpencarikerja->id =  $user->id;
                    $bkolpencarikerja->tanggal_daftar = $request->getParam('tanggal_daftar');
                    $bkolpencarikerja->nomor_pencaker = $request->getParam('nomor_pencaker');
                    $bkolpencarikerja->nama_lengkap =  $request->getParam('nama_lengkap');
                    $bkolpencarikerja->keahlian =  $request->getParam('keahlian');
                    $bkolpencarikerja->keterangan =  $request->getParam('keterangan');
                    $bkolpencarikerja->usia =  $request->getParam('usia');
                    $bkolpencarikerja->melamar_ke =  $request->getParam('melamar_ke');
                    $bkolpencarikerja->no_ktp =  $request->getParam('no_ktp');
                    $bkolpencarikerja->tempat_lahir =  $request->getParam('tempat_lahir');
                    $bkolpencarikerja->tanggal_lahir =  $request->getParam('tanggal_lahir');
                    $bkolpencarikerja->jenis_kelamin =  $request->getParam('jenis_kelamin');
                    $bkolpencarikerja->agama =  $request->getParam('agama');
                    $bkolpencarikerja->kondisi_fisik =  $request->getParam('kondisi_fisik');
                    $bkolpencarikerja->status_perkawinan =  $request->getParam('status_perkawinan');
                    $bkolpencarikerja->tinggi_badan =  $request->getParam('tinggi_badan');
                    $bkolpencarikerja->berat_badan =  $request->getParam('berat_badan');
                    $bkolpencarikerja->no_telp =  $request->getParam('no_telp');
                    $bkolpencarikerja->alamat_lengkap =  $request->getParam('alamat_lengkap');
                    $bkolpencarikerja->provinsi_id =  32;
                    $bkolpencarikerja->kabupatenkota_id =  77;
                    $bkolpencarikerja->kecamatan_id =  $request->getParam('kecamatan_id') ? $request->getParam('kecamatan_id') : NULL;
                    $bkolpencarikerja->kelurahan_id =  $request->getParam('kelurahan_id') ? $request->getParam('kelurahan_id') : NULL;
                    $bkolpencarikerja->kodepos =  $request->getParam('kodepos');
                    $bkolpencarikerja->pendidikan_terakhir_id =  $request->getParam('pendidikan_terakhir_id') ? $request->getParam('pendidikan_terakhir_id'): NULL;
                    $bkolpencarikerja->jurusan_pendidikan_id =  $request->getParam('jurusan_pendidikan_id') ? $request->getParam('jurusan_pendidikan_id') : NULL;
                    $bkolpencarikerja->nama_instansi_pendidikan =  $request->getParam('nama_instansi_pendidikan');
                    $bkolpencarikerja->tahun_lulus =  $request->getParam('tahun_lulus');
                    $bkolpencarikerja->nilai_ijazah_ipk =  $request->getParam('nilai_ijazah_ipk');
                    $bkolpencarikerja->harapan_wilayah_pekerjaan =  $request->getParam('harapan_wilayah_pekerjaan');
                    $bkolpencarikerja->provinsi_dalam_negeri_id =  $request->getParam('provinsi_dalam_negeri_id') ? $request->getParam('provinsi_dalam_negeri_id') : NULL;
                    $bkolpencarikerja->kabupatenkota_dalam_negeri_id =  $request->getParam('kabupatenkota_dalam_negeri_id') ? $request->getParam('kabupatenkota_dalam_negeri_id') : NULL;
                    $bkolpencarikerja->negara_luar_negeri_id =  $request->getParam('negara_luar_negeri_id') ? $request->getParam('negara_luar_negeri_id') : NULL;
                    $bkolpencarikerja->sistem_pembayaran =  $request->getParam('sistem_pembayaran');
                    $bkolpencarikerja->harapan_gaji_minimum_id =  $request->getParam('harapan_gaji_minimum_id') ? $request->getParam('harapan_gaji_minimum_id') : NULL;
                    $bkolpencarikerja->jenis_jabatan_yang_diminati_id =  $request->getParam('jenis_jabatan_yang_diminati_id') ? $request->getParam('jenis_jabatan_yang_diminati_id') : NULL;
                    $bkolpencarikerja->save();


                    $sendEmail = new E($this->container);
                    $sendEmail = $sendEmail->sendTemplate(
                        array($user->id),
                        'activation',
                        array('confirm_url' => $confirmUrl)
                    );

                    if ($sendEmail['status'] == "error") {
                        $this->flash(
                            'danger',
                            'Terjadi kesalahan saat mengirim email aktivasi Anda. Silakan hubungi contact support.'
                        );
                        return $this->redirect($response, 'forgot-password');
                    }

                    $this->flash(
                        'success',
                        'Anda harus mengaktifkan akun Anda sebelum Anda dapat masuk. Petunjuk telah dikirim ke: ' .
                            $request->getParam('email')
                    );
                    return $this->redirect($response, 'daftar-pencaker-berhasil');
                    // return $this->view->render(
                    //     $response,
                    //     'bkol/auth/aktifasi.twig'
                    // );
                }

                $user = $this->auth->registerAndActivate($userDetails);
                $role->users()->attach($user);

                $sendEmail = new E($this->container);
                $sendEmail = $sendEmail->sendTemplate(array($user->id), 'registration');

                $this->flash('success', 'Your account has been created.');

                $this->auth->login($user);

                return $this->redirect($response, 'home');

                $this->logger->addInfo("Pendaftaran pengguna baru.", array("user" => $user));
            }
        }

        return $this->view->render(
            $response,
            'bkol/auth/jobseeker_registration.twig'
        );
    }
    public function registerEmployer(Request $request, Response $response)
    {
        if ($request->isPost()) {
            // Validate Data
            $this->validateNewCompanies();
            if ($this->validator->isValid()) {
                $role = $this->auth->findRoleByName('Companies');
                $userDetails = [
                    'username' => $request->getParam('username'),
                    'password' => $request->getParam('password'),
                    'companyname' => $request->getParam('companyname'),
                    'companysaddress' => $request->getParam('companysaddress'),
                    'email' => $request->getParam('email'),
                    'permissions' => [
                        'user.delete' => 0
                    ]
                ];

                if ($this->config['activation']) {
                    $user = $this->auth->register($userDetails);
                    $role->users()->attach($user);
                    $activations = $this->auth->getActivationRepository();

                    $activations = $activations->create($user);
                    $code = $activations->code;

                    $confirmUrl = "https://" . $this->config['domain-bkol'] . "/activate?code=" .
                        $code . "&email=" . $user->email;

                    $bkolperusahaan = new BkolPerusahaan;
                    $bkolperusahaan->user_id =  $user->id;
                    $bkolperusahaan->id =  $user->id;
                    $bkolperusahaan->companyname =  $user->companyname;
                    $bkolperusahaan->companysaddress =  $user->companysaddress;
                    $bkolperusahaan->save();

                    $sendEmail = new E($this->container);
                    $sendEmail = $sendEmail->sendTemplate(
                        array($user->id),
                        'activation',
                        array('confirm_url' => $confirmUrl)
                    );

                    if ($sendEmail['status'] == "error") {
                        $this->flash(
                            'danger',
                            'Terjadi kesalahan saat mengirim email aktivasi Anda. Silakan hubungi contact support.'
                        );
                        return $this->redirect($response, 'forgot-password');
                    }

                    $this->flash(
                        'success',
                        'Anda harus mengaktifkan akun Anda sebelum Anda dapat masuk. Petunjuk telah dikirim ke: ' .
                            $request->getParam('email')
                    );
                    return $this->redirect($response, 'daftar-perusahaan-berhasil');
                }

                $user = $this->auth->registerAndActivate($userDetails);
                $role->users()->attach($user);

                $sendEmail = new E($this->container);
                $sendEmail = $sendEmail->sendTemplate(array($user->id), 'registration');

                $this->flash('success', 'Akun anda telah dibuat.');

                $this->auth->login($user);

                return $this->redirect($response, 'home');

                $this->logger->addInfo("Pendaftaran pengguna baru.", array("user" => $user));
            }
        }

        return $this->view->render(
            $response,
            'bkol/auth/daftar_perusahaan.twig'
        );
    }
    public function registerPerusahaan(Request $request, Response $response)
    {
        if ($request->isPost()) {
            // Validate Data
            $this->validateNewCompanies();
            if ($this->validator->isValid()) {
                $role = $this->auth->findRoleByName('Companies');
                $userDetails = [
                    'username' => $request->getParam('username'),
                    'password' => $request->getParam('password'),
                    'companyname' => $request->getParam('companyname'),
                    'companysaddress' => $request->getParam('companysaddress'),
                    'email' => $request->getParam('email'),
                    'permissions' => [
                        'user.delete' => 0
                    ]
                ];

                if ($this->config['activation']) {
                    $user = $this->auth->register($userDetails);
                    $role->users()->attach($user);
                    $activations = $this->auth->getActivationRepository();

                    $activations = $activations->create($user);
                    $code = $activations->code;

                    $confirmUrl = "https://" . $this->config['domain-bkol'] . "/activate?code=" .
                        $code . "&email=" . $user->email;

                    $bkolperusahaan = new BkolPerusahaan;
                    $bkolperusahaan->user_id =  $user->id;
                    $bkolperusahaan->id =  $user->id;
                    $bkolperusahaan->companyname =  $user->companyname;
                    $bkolperusahaan->companysaddress =  $user->companysaddress;
                    $bkolperusahaan->flag_perusahaan =  $request->getParam('flag_perusahaan');
                    $bkolperusahaan->provinsi_id =  $request->getParam('provinsi_id') ? $request->getParam('provinsi_id') : NULL;
                    $bkolperusahaan->kabupatenkota_id =  $request->getParam('kabupatenkota_id') ? $request->getParam('kabupatenkota_id') : NULL;
                    $bkolperusahaan->kecamatan_id =  $request->getParam('kecamatan_id') ? $request->getParam('kecamatan_id') : NULL;
                    $bkolperusahaan->kelurahan_id =  $request->getParam('kelurahan_id') ? $request->getParam('kelurahan_id') : NULL;
                    $bkolperusahaan->save();

                    $sendEmail = new E($this->container);
                    $sendEmail = $sendEmail->sendTemplate(
                        array($user->id),
                        'activation',
                        array('confirm_url' => $confirmUrl)
                    );

                    if ($sendEmail['status'] == "error") {
                        $this->flash(
                            'danger',
                            'Terjadi kesalahan saat mengirim email aktivasi Anda. Silakan hubungi contact support.'
                        );
                        return $this->redirect($response, 'forgot-password');
                    }

                    $this->flash(
                        'success',
                        'Anda harus mengaktifkan akun Anda sebelum Anda dapat masuk. Petunjuk telah dikirim ke: ' .
                            $request->getParam('email')
                    );
                    return $this->redirect($response, 'daftar-perusahaan-berhasil');
                }

                $user = $this->auth->registerAndActivate($userDetails);
                $role->users()->attach($user);

                $sendEmail = new E($this->container);
                $sendEmail = $sendEmail->sendTemplate(array($user->id), 'registration');

                $this->flash('success', 'Akun anda telah dibuat.');

                $this->auth->login($user);

                return $this->redirect($response, 'home');

                $this->logger->addInfo("Pendaftaran pengguna baru.", array("user" => $user));
            }
        }
        $daerahs = Daerah::where('lokasi_kabupatenkota', 0)
        ->where('lokasi_kecamatan', 0)
        ->where('lokasi_kelurahan', 0)
        ->orderBy('lokasi_nama')
        ->get();

        return $this->view->render(
            $response,
            'bkol/auth/daftar_perusahaan.twig',
            array(
                "daerahs" => $daerahs
            )
        );
    }
    public function registerPemerintah(Request $request, Response $response)
    {
        if ($request->isPost()) {
            // Validate Data
            $this->validateNewPemerintah();
            if ($this->validator->isValid()) {
                $role = $this->auth->findRoleByName('Pemerintah');
                $userDetails = [
                    'username' => $request->getParam('username'),
                    'password' => $request->getParam('password'),
                    'email' => $request->getParam('email'),
                    'permissions' => [
                        'user.delete' => 0
                    ]
                ];

                if ($this->config['activation']) {
                    $user = $this->auth->register($userDetails);
                    $role->users()->attach($user);
                    $activations = $this->auth->getActivationRepository();
                    $activations = $activations->create($user);
                    $code = $activations->code;
                    $confirmUrl = "https://" . $this->config['domain-bkol'] . "/activate?code=" .
                        $code . "&email=" . $user->email;
                    $pemerintah = new PemerintahModel;
                    $pemerintah->user_id =  $user->id;
                    $pemerintah->id =  $user->id;
                    $pemerintah->jenis_instansi_id =  $request->getParam('jenis_instansi_id') ? $request->getParam('jenis_instansi_id') : NULL;
                    $pemerintah->nama_instansi =  $request->getParam('nama_instansi');
                    $pemerintah->nama_lengkap_admin =  $request->getParam('nama_lengkap_admin');
                    $pemerintah->save();

                    $sendEmail = new E($this->container);
                    $sendEmail = $sendEmail->sendTemplate(
                        array($user->id),
                        'activation',
                        array('confirm_url' => $confirmUrl)
                    );

                    if ($sendEmail['status'] == "error") {
                        $this->flash(
                            'danger',
                            'Terjadi kesalahan saat mengirim email aktivasi Anda. Silakan hubungi contact support.'
                        );
                        return $this->redirect($response, 'forgot-password');
                    }

                    $this->flash(
                        'success',
                        'Anda harus mengaktifkan akun Anda sebelum Anda dapat masuk. Petunjuk telah dikirim ke: ' .
                            $request->getParam('email')
                    );
                    return $this->redirect($response, 'daftar-perusahaan-berhasil');
                }

                $user = $this->auth->registerAndActivate($userDetails);
                $role->users()->attach($user);

                $sendEmail = new E($this->container);
                $sendEmail = $sendEmail->sendTemplate(array($user->id), 'registration');

                $this->flash('success', 'Akun anda telah dibuat.');

                $this->auth->login($user);

                return $this->redirect($response, 'home');

                $this->logger->addInfo("Pendaftaran Akun Pemerintah baru.", array("user" => $user));
            }
        }

        return $this->view->render(
            $response,
            'bkol/auth/daftar_pemerintah.twig',
            array (
                'jenisinstansi' => JenisInstansiModel::get()
            )
        );
    }

    public function resetPassword(Request $request, Response $response)
    {
        if ($request->isPost()) {
            if (filter_var($request->getParam('email'), FILTER_VALIDATE_EMAIL)) {
                $credentials = [
                    'email' => $request->getParam('email')
                ];
            }
            $user = $this->auth->findByCredentials($credentials);
            if ($user) {
                // Validate Data
                $validateData = array(
                    'password' => array(
                        'rules' => V::length(6, 64),
                        'messages' => array(
                            'length' => "Harus antara 6 hingga 64 karakter."
                            )
                    ),
                    'password_confirm' => array(
                        'rules' => V::equals($request->getParam('password')),
                        'messages' => array(
                            'equals' => 'Kata sandi harus cocok.'
                            )
                    )
                );
                $this->validator->validate($request, $validateData);

                if ($this->validator->isValid()) {
                    $reminders = $this->auth->getReminderRepository();

                    if ($reminders->complete($user, $request->getParam('reminder'), $request->getParam('password'))) {
                        $this->auth->login($user);

                        $this->flash(
                            'success',
                            'Kata sandi Anda telah berhasil diubah dan Anda telah masuk.'
                        );
                        return $this->redirect($response, 'home');
                    }

                    $this->flash('danger', 'Ada kesalahan memvalidasi informasi Anda. Silakan coba lagi.');
                    return $this->redirect($response, 'forgot-password');
                }
            }

            $this->flash('danger', 'That account does not exist.');
            return $this->redirect($response, 'forgot-password');
        }

        return $this->view->render($response, 'bkol/auth/reset-password.twig');
    }

    private function validateNewJobseeker()
    {
        $validateData = array(
            'nama_lengkap' => array(
                'rules' => V::notEmpty()->length(3, 25),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'length' => "Harus antara 3 hingga 25 karakter."
                    )
            ),
            'email' => array(
                'rules' => V::notEmpty()->noWhitespace()->email(),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'noWhitespace' => 'Tidak boleh mengandung spasi.',
                    'email' => 'Harus alamat e-mail yang valid.'
                    )
            ),
            'alamat_lengkap' => array(
                'rules' => V::notEmpty()->length(3, 25),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'length' => "Harus antara 3 hingga 25 karakter."
                    )
            ),
            'username' => array(
                'rules' => V::notEmpty()->alnum()->length(2, 25),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'alnum' => 'huruf dan angka saja.',
                    'length' => "Harus antara 2 hingga 25 karakter."
                    )
            ),
            'password' => array(
                'rules' => V::notEmpty()->noWhitespace()->length(6, 64),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'noWhitespace' => 'Tidak boleh mengandung spasi.',
                    'length' => "Harus antara 6 hingga 64 karakter."
                    )
            ),
            'password-confirm' => array(
                'rules' => V::notEmpty()->equals($this->request->getParam('password')),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'equals' => 'Kata sandi harus cocok.'
                    )
            )
        );
        $this->validator->validate($this->request, $validateData);

        // Validate Recaptcha
        $recaptcha = new Recaptcha($this->container);
        $recaptcha = $recaptcha->validate($this->request->getParam('g-recaptcha-response'));
        if (!$recaptcha) {
            $this->validator->addError('recaptcha', 'Recaptcha was invalid.');
        }

        // Validate Username
        if ($this->auth->findByCredentials(['login' => $this->request->getParam('username')])) {
            $this->validator->addError('username', 'Pengguna lain sudah menggunakan username ini');
        }

        // Validate Email
        if ($this->auth->findByCredentials(['login' => $this->request->getParam('email')])) {
            $this->validator->addError('email', 'Pengguna lain sudah menggunakan email ini');
        }
    }
    private function validateNewBkk()
    {
        $validateData = array(

            'email' => array(
                'rules' => V::notEmpty()->noWhitespace()->email(),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'noWhitespace' => 'Tidak boleh mengandung spasi.',
                    'email' => 'Harus alamat e-mail yang valid.'
                    )
            ),
            'username' => array(
                'rules' => V::notEmpty()->alnum()->length(2, 25),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'alnum' => 'huruf dan angka saja.',
                    'length' => "Harus antara 2 hingga 25 karakter."
                    )
            ),
            'password' => array(
                'rules' => V::notEmpty()->noWhitespace()->length(6, 64),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'noWhitespace' => 'Tidak boleh mengandung spasi.',
                    'length' => "Harus antara 6 hingga 64 karakter."
                    )
            ),
            'password-confirm' => array(
                'rules' => V::notEmpty()->equals($this->request->getParam('password')),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'equals' => 'Kata sandi harus cocok.'
                    )
            )
        );
        $this->validator->validate($this->request, $validateData);

        // Validate Recaptcha
        $recaptcha = new Recaptcha($this->container);
        $recaptcha = $recaptcha->validate($this->request->getParam('g-recaptcha-response'));
        if (!$recaptcha) {
            $this->validator->addError('recaptcha', 'Recaptcha was invalid.');
        }

        // Validate Username
        if ($this->auth->findByCredentials(['login' => $this->request->getParam('username')])) {
            $this->validator->addError('username', 'Pengguna lain sudah menggunakan username ini');
        }

        // Validate Email
        if ($this->auth->findByCredentials(['login' => $this->request->getParam('email')])) {
            $this->validator->addError('email', 'Pengguna lain sudah menggunakan email ini');
        }
    }

    private function validateNewLpk()
    {
        $validateData = array(
            'fullname' => array(
                'rules' => V::notEmpty()->length(3, 25),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'length' => "Harus antara 3 hingga 25 karakter."
                    )
            ),
            
            'email' => array(
                'rules' => V::notEmpty()->noWhitespace()->email(),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'noWhitespace' => 'Tidak boleh mengandung spasi.',
                    'email' => 'Harus alamat e-mail yang valid.'
                    )
            ),
            'lpk_id' => array(
                'rules' => V::notEmpty()->length(3, 25),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'noWhitespace' => 'Tidak boleh mengandung spasi.',
                    'lpk_id' => 'Harus Memilih Nama LPK.'
                    )
            ),
            'username' => array(
                'rules' => V::notEmpty()->alnum()->length(2, 25),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'alnum' => 'huruf dan angka saja.',
                    'length' => "Harus antara 2 hingga 25 karakter."
                    )
            ),
            'password' => array(
                'rules' => V::notEmpty()->noWhitespace()->length(6, 64),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'noWhitespace' => 'Tidak boleh mengandung spasi.',
                    'length' => "Harus antara 6 hingga 64 karakter."
                    )
            ),
            'password-confirm' => array(
                'rules' => V::notEmpty()->equals($this->request->getParam('password')),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'equals' => 'Kata sandi harus cocok.'
                    )
            )
        );
        $this->validator->validate($this->request, $validateData);

        // Validate Recaptcha
        $recaptcha = new Recaptcha($this->container);
        $recaptcha = $recaptcha->validate($this->request->getParam('g-recaptcha-response'));
        if (!$recaptcha) {
            $this->validator->addError('recaptcha', 'Recaptcha was invalid.');
        }

        // Validate Username
        if ($this->auth->findByCredentials(['login' => $this->request->getParam('username')])) {
            $this->validator->addError('username', 'Pengguna lain sudah menggunakan username ini');
        }

        // Validate Email
        if ($this->auth->findByCredentials(['login' => $this->request->getParam('email')])) {
            $this->validator->addError('email', 'Pengguna lain sudah menggunakan email ini');
        }
    }

    private function validateNewPerguruanTinggi()
    {
        $validateData = array(
            'fullname' => array(
                'rules' => V::notEmpty()->length(3, 25),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'length' => "Harus antara 3 hingga 25 karakter."
                    )
            ),
            
            'email' => array(
                'rules' => V::notEmpty()->noWhitespace()->email(),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'noWhitespace' => 'Tidak boleh mengandung spasi.',
                    'email' => 'Harus alamat e-mail yang valid.'
                    )
            ),
            'username' => array(
                'rules' => V::notEmpty()->alnum()->length(2, 25),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'alnum' => 'huruf dan angka saja.',
                    'length' => "Harus antara 2 hingga 25 karakter."
                    )
            ),
            'password' => array(
                'rules' => V::notEmpty()->noWhitespace()->length(6, 64),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'noWhitespace' => 'Tidak boleh mengandung spasi.',
                    'length' => "Harus antara 6 hingga 64 karakter."
                    )
            ),
            'password-confirm' => array(
                'rules' => V::notEmpty()->equals($this->request->getParam('password')),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'equals' => 'Kata sandi harus cocok.'
                    )
            )
        );
        $this->validator->validate($this->request, $validateData);

        // Validate Recaptcha
        $recaptcha = new Recaptcha($this->container);
        $recaptcha = $recaptcha->validate($this->request->getParam('g-recaptcha-response'));
        if (!$recaptcha) {
            $this->validator->addError('recaptcha', 'Recaptcha was invalid.');
        }

        // Validate Username
        if ($this->auth->findByCredentials(['login' => $this->request->getParam('username')])) {
            $this->validator->addError('username', 'Pengguna lain sudah menggunakan username ini');
        }

        // Validate Email
        if ($this->auth->findByCredentials(['login' => $this->request->getParam('email')])) {
            $this->validator->addError('email', 'Pengguna lain sudah menggunakan email ini');
        }
    }

    private function validateNewCompanies()
    {
        $validateData = array(
            'username' => array(
                'rules' => V::notEmpty()->alnum()->length(2, 25),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'alnum' => 'huruf dan angka saja.',
                    'length' => "Harus antara 2 hingga 25 karakter."
                    )
            ),
            'password' => array(
                'rules' => V::notEmpty()->noWhitespace()->length(6, 64),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'noWhitespace' => 'Tidak boleh mengandung spasi.',
                    'length' => "Harus antara 6 hingga 64 karakter."
                    )
            ),
            'password-confirm' => array(
                'rules' => V::notEmpty()->equals($this->request->getParam('password')),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'equals' => 'Kata sandi harus cocok.'
                    )
                ),
            'companyname' => array(
                'rules' => V::notEmpty()->alnum('\'-')->length(2, 250),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'alnum' => 'Dapat berisi huruf, angka, \' tanda hubung.',
                    'length' => "Harus antara 2 hingga 250 karakter."
                    )
            ),
            'companysaddress' => array(
                'rules' => V::notEmpty()->length(2, 100),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'alnum' => 'Dapat berisi huruf, angka, \' tanda hubung.',
                    'length' => "Harus antara 2 hingga 100 karakter."
                    )
            ),
            'email' => array(
                'rules' => V::notEmpty()->noWhitespace()->email(),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'noWhitespace' => 'Tidak boleh mengandung spasi.',
                    'email' => 'Harus alamat e-mail yang valid.'
                    )
            )
        );
        $this->validator->validate($this->request, $validateData);

        // Validate Recaptcha
        $recaptcha = new Recaptcha($this->container);
        $recaptcha = $recaptcha->validate($this->request->getParam('g-recaptcha-response'));
        if (!$recaptcha) {
            $this->validator->addError('recaptcha', 'Recaptcha was invalid.');
        }

        // Validate Username
        if ($this->auth->findByCredentials(['login' => $this->request->getParam('username')])) {
            $this->validator->addError('username', 'Pengguna lain sudah menggunakan username ini');
        }

        // Validate Email
        if ($this->auth->findByCredentials(['login' => $this->request->getParam('email')])) {
            $this->validator->addError('email', 'Pengguna lain sudah menggunakan email ini');
        }
    }
    private function validateNewPemerintah()
    {
        $validateData = array(
            'username' => array(
                'rules' => V::notEmpty()->alnum()->length(2, 25),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'alnum' => 'huruf dan angka saja.',
                    'length' => "Harus antara 2 hingga 25 karakter."
                    )
            ),
            'password' => array(
                'rules' => V::notEmpty()->noWhitespace()->length(6, 64),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'noWhitespace' => 'Tidak boleh mengandung spasi.',
                    'length' => "Harus antara 6 hingga 64 karakter."
                    )
            ),
            'password-confirm' => array(
                'rules' => V::notEmpty()->equals($this->request->getParam('password')),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'equals' => 'Kata sandi harus cocok.'
                    )
                ),
            'nama_instansi' => array(
                'rules' => V::notEmpty()->alnum('\'-')->length(2, 250),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'alnum' => 'Dapat berisi huruf, angka, \' tanda hubung.',
                    'length' => "Harus antara 2 hingga 250 karakter."
                    )
            ),
            'email' => array(
                'rules' => V::notEmpty()->noWhitespace()->email(),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'noWhitespace' => 'Tidak boleh mengandung spasi.',
                    'email' => 'Harus alamat e-mail yang valid.'
                    )
            )
        );
        $this->validator->validate($this->request, $validateData);

        // Validate Recaptcha
        $recaptcha = new Recaptcha($this->container);
        $recaptcha = $recaptcha->validate($this->request->getParam('g-recaptcha-response'));
        if (!$recaptcha) {
            $this->validator->addError('recaptcha', 'Recaptcha was invalid.');
        }

        // Validate Username
        if ($this->auth->findByCredentials(['login' => $this->request->getParam('username')])) {
            $this->validator->addError('username', 'Pengguna lain sudah menggunakan username ini');
        }

        // Validate Email
        if ($this->auth->findByCredentials(['login' => $this->request->getParam('email')])) {
            $this->validator->addError('email', 'Pengguna lain sudah menggunakan email ini');
        }
    }

    

    private function processLogin($credentials, $remember)
    {
        try {
            if ($this->auth->authenticate($credentials, $remember)) {
                return true;
            }

            $this->flashNow('danger', 'Username atau password salah.');
        } catch (ThrottlingException $e) {
            $this->flashNow(
                'danger',
                'Terlalu banyak upaya tidak valid untuk Anda ' . $e->getType() . '!  '.
                    'Harap tunggu .' . $e->getDelay() . 'beberapa detik sebelum mencoba lagi.'
            );
        } catch (NotActivatedException $e) {
            $this->flashNow('danger', 'Silakan periksa email Anda untuk instruksi mengaktifkan akun Anda.');
        }

        return false;
    }
    public function daftarberhasil(Request $request, Response $response)
    {
      return $this->view->render(
          $response,
          'bkol/auth/aktifasi.twig'
      );
    }
}
