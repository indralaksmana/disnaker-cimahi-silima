<?php

namespace App\Controller;

use App\Library\Email as E;
use Carbon\Carbon;
use App\Library\Recaptcha;
use App\Model\Oauth2Providers;
use App\Model\BkolPencariKerja;
use App\Model\BkolPerusahaan;
use App\Model\PemerintahModel;
use App\Model\JenisInstansiModel;
use App\Model\Users;
use App\Model\BkkModel;
use App\Model\LpkModel;
use App\Model\PerguruanTinggiModel;
use App\Model\Daerah;
use App\Model\JenisPendidikanModel;
use App\Model\ActivationModel;
use App\Model\HitApiDisdukcapil;
use App\Model\RoleUsers;
use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use Cartalyst\Sentinel\Checkpoints\NotActivatedException;
use Cartalyst\Sentinel\Reminders\Reminder;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use App\Library\Util;
use App\Library\WhatsappApi;
use App\Library\DisdukApi;
use Cartalyst\Sentinel\Hashing\NativeHasher;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Auth extends Controller
{
    CONST ALLOW_IDENTITY = ['1607102012920005', '3204351204880005', '1802072111830004'];

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

                // $sendEmail = new E($this->container);
                // $sendEmail = $sendEmail->sendTemplate(array($user->id), 'registration');

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
            try {
                // Validate Data
                $this->validateNewJobseeker();

                if ($this->validator->isValid()) {
                    $hits = HitApiDisdukcapil::where('nik', $request->getParam('no_ktp'))->where('result', 'True');
                    $count = $hits->count();

                    if ($count <= 0) {
                        $disduk_api = new DisdukApi($this->container);
                        $disduk_api->getIndentitasPenduduk($request->getParam('no_ktp'));
                    }

                    // ambil data dari hasil request API
                    $data = $hits->first();
                    $identity = json_decode($data->content_result);

                    // jika check ulang data yang register dari cimahi atau bukan
                    if (strpos($identity->KAB_NAME, 'CIMAHI') !== FALSE || in_array($identity->NIK, self::ALLOW_IDENTITY)) {
                        $role = $this->auth->findRoleByName('Jobseeker');
                        $password = Util::generatePassword();
                        $userDetails = [
                            'nik' => $request->getParam('no_ktp'),
                            'fullname' => $identity->NAMA_LGKP,
                            'email' => $request->getParam('email'),
                            'username' => $request->getParam('email') ? $request->getParam('email') : $request->getParam('phonenumber'),
                            'password' => $password,
                            'placeofbirth' => $identity->TMPT_LHR,
                            'dateofbirth' => $identity->TGL_LHR,
                            'gender' => $identity->JENIS_KLMIN,
                            'religion' => $identity->AGAMA,
                            'nationality' => 'Indonesia',
                            'address' => $identity->ALAMAT,
                            'districts' => $identity->KEL_NAME,
                            'maritalstatus' => $identity->STATUS_KAWIN,
                            'phonenumber' => $request->getParam('phonenumber'),
                            'permissions' => [
                                'user.delete' => 0
                            ]
                        ];

                        if ($this->config['activation']) {
                            $user = $this->auth->register($userDetails);
                            
                            if ($user) {
                                $role->users()->attach($user);
        
                                $activations = $this->auth->getActivationRepository();
            
                                $activations = $activations->create($user);
                                $code = $activations->code;
        
                                $kelurahan = Daerah::select('id')
                                    ->where('lokasi_nama', $identity->KEL_NAME)
                                    ->first();
        
                                $kecamatan = Daerah::select('id')
                                    ->where('lokasi_nama', $identity->KEC_NAME)
                                    ->first();
            
                                $confirmUrl = "https://" . $this->config['domain-bkol'] . "/activate?code=" .
                                    $code . "&email=" . $user->email;
                                
                                $bkolpencarikerja = new BkolPencariKerja;
                                $bkolpencarikerja->user_id = $user->id;
                                $bkolpencarikerja->id = $user->id;
                                $bkolpencarikerja->tanggal_daftar = Carbon::now()->toDateString();
                                $bkolpencarikerja->nomor_pencaker = Util::generateNoPencaker($request->getParam('no_ktp'));
                                $bkolpencarikerja->nama_lengkap = $identity->NAMA_LGKP;
                                $bkolpencarikerja->keahlian = $request->getParam('keahlian');
                                $bkolpencarikerja->keterangan = $request->getParam('keterangan');
                                $bkolpencarikerja->usia = $request->getParam('usia');
                                $bkolpencarikerja->melamar_ke = $request->getParam('melamar_ke');
                                $bkolpencarikerja->no_ktp = $request->getParam('no_ktp');
                                $bkolpencarikerja->tempat_lahir = $identity->TMPT_LHR;
                                $bkolpencarikerja->tanggal_lahir = $identity->TGL_LHR;
                                $bkolpencarikerja->jenis_kelamin = $identity->JENIS_KLMIN;
                                $bkolpencarikerja->agama = $identity->AGAMA;
                                $bkolpencarikerja->kondisi_fisik = $request->getParam('kondisi_fisik');
                                $bkolpencarikerja->status_perkawinan = $identity->STATUS_KAWIN;
                                $bkolpencarikerja->tinggi_badan = $request->getParam('tinggi_badan');
                                $bkolpencarikerja->berat_badan = $request->getParam('berat_badan');
                                $bkolpencarikerja->no_telp = $request->getParam('phonenumber');
                                $bkolpencarikerja->alamat_lengkap = $identity->ALAMAT;
                                $bkolpencarikerja->provinsi_id = 32;
                                $bkolpencarikerja->kabupatenkota_id = 77;
                                $bkolpencarikerja->kecamatan_id = $kecamatan->id;
                                $bkolpencarikerja->kelurahan_id = $kelurahan->id;
                                $bkolpencarikerja->kodepos = $request->getParam('kodepos');
                                $bkolpencarikerja->pendidikan_terakhir_id = $request->getParam('pendidikan_terakhir_id') ? $request->getParam('pendidikan_terakhir_id'): NULL;
                                $bkolpencarikerja->jurusan_pendidikan_id = $request->getParam('jurusan_pendidikan_id') ? $request->getParam('jurusan_pendidikan_id') : NULL;
                                $bkolpencarikerja->nama_instansi_pendidikan = $request->getParam('nama_instansi_pendidikan');
                                $bkolpencarikerja->tahun_lulus = $request->getParam('tahun_lulus');
                                $bkolpencarikerja->nilai_ijazah_ipk = $request->getParam('nilai_ijazah_ipk');
                                $bkolpencarikerja->harapan_wilayah_pekerjaan = $request->getParam('harapan_wilayah_pekerjaan');
                                $bkolpencarikerja->provinsi_dalam_negeri_id = $request->getParam('provinsi_dalam_negeri_id') ? $request->getParam('provinsi_dalam_negeri_id') : NULL;
                                $bkolpencarikerja->kabupatenkota_dalam_negeri_id = $request->getParam('kabupatenkota_dalam_negeri_id') ? $request->getParam('kabupatenkota_dalam_negeri_id') : NULL;
                                $bkolpencarikerja->negara_luar_negeri_id = $request->getParam('negara_luar_negeri_id') ? $request->getParam('negara_luar_negeri_id') : NULL;
                                $bkolpencarikerja->sistem_pembayaran = $request->getParam('sistem_pembayaran');
                                $bkolpencarikerja->harapan_gaji_minimum_id = $request->getParam('harapan_gaji_minimum_id') ? $request->getParam('harapan_gaji_minimum_id') : NULL;
                                $bkolpencarikerja->jenis_jabatan_yang_diminati_id = $request->getParam('jenis_jabatan_yang_diminati_id') ? $request->getParam('jenis_jabatan_yang_diminati_id') : NULL;
                                $bkolpencarikerja->status_pekerjaan = "Belum Bekerja";
                                $bkolpencarikerja->save();
            
                                $sendEmail = new E($this->container);
                                $sendEmail = $sendEmail->sendTemplate(
                                    array($user->id),
                                    'activation',
                                    array(
                                        'confirm_url' => $confirmUrl,
                                        'password' => $userDetails['password']
                                    )
                                );
            
                                if ($sendEmail['status'] == "error") {
                                    $this->flash(
                                        'danger',
                                        'Terjadi kesalahan saat mengirim email aktivasi Anda. Silakan hubungi contact support.'
                                    );
                                    return $this->redirect($response, 'forgot-password');
                                }
        
                                if (strlen($userDetails['phonenumber']) > 0) {
                                    $whatsappMessage = "Hai *".$userDetails['fullname']."*,".PHP_EOL.PHP_EOL."Terima kasih telah membuat akun Anda. Berikut info akun Anda :".PHP_EOL.PHP_EOL."Username : ".$userDetails['username'].PHP_EOL.PHP_EOL."Password : ". $userDetails['password'].PHP_EOL.PHP_EOL."Untuk memastikan pengalaman terbaik, kami mengharuskan Anda memverifikasi alamat email Anda sebelum Anda dapat mulai menggunakan akun Anda. Untuk melakukannya, cukup klik tautan berikut dan Anda akan segera masuk ke akun Anda.".PHP_EOL.PHP_EOL.$confirmUrl;

                                    $whatsapp_api = new WhatsappApi($this->container);
                                    $whatsapp_api->sendMessage($userDetails['phonenumber'], $whatsappMessage);
                                }
        
                                $this->flash(
                                    'success',
                                    'Anda harus mengaktifkan akun Anda sebelum Anda dapat masuk. Petunjuk telah dikirim ke: ' .
                                        $request->getParam('email')
                                );
                                return $this->redirect($response, 'daftar-pencaker-berhasil');
                            } else {
                                $this->flash(
                                    'danger',
                                    'Terjadi kesalahan saat pembuatan akun'
                                );
                                return $this->redirect($response, 'registerjobseeker');
                            }
                        }
                    } else {
                        $this->flash(
                            'warning',
                            'NIK anda tidak terverifikasi oleh Dinas Kependudukan dan Catatan Sipil Kota Cimahi'
                        );
                        return $this->redirect($response, 'registerjobseeker');
                    }
                }
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
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
                    if($bkolperusahaan->flag_perusahaan == '1'){
                        $bkolperusahaan->provinsi_id = 12;
                        $bkolperusahaan->kabupatenkota_id = 176;
                    }
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
        // Validate Recaptcha
        $recaptcha = new Recaptcha($this->container);
        $recaptcha = $recaptcha->validate($this->request->getParam('g-recaptcha-response'));
        if (!$recaptcha) {
            $this->validator->addError('recaptcha', 'Recaptcha was invalid.');
        }

        // Validate Email
        if ($this->auth->findByCredentials(['login' => $this->request->getParam('email')])) {
            $this->validator->addError('email', 'Pengguna lain sudah menggunakan email ini');
        }

        // Validate NIK
        $checkKtp = BkolPencariKerja::where('no_ktp', $this->request->getParam('no_ktp'))->count();
        if ($checkKtp > 0) {
            $this->validator->addError('no_ktp', 'No identitas penduduk sudah terdaftar');
        }

        // Validate Phone Number
        $checkPhone = BkolPencariKerja::where('no_telp', $this->request->getParam('phonenumber'))->count();
        if ($checkPhone > 0) {
            $this->validator->addError('phonenumber', 'Nomor handphone sudah terdaftar');
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
                //'rules' => V::notEmpty()->alnum('\'-')->length(2, 250),
                'rules' => V::notEmpty()->length(2, 100),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    //'alnum' => 'Dapat berisi huruf, angka, \' tanda hubung.',
                    'length' => "Harus antara 2 hingga 250 karakter."
                    )
            ),
            'companysaddress' => array(
                'rules' => V::notEmpty()->length(2, 100),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    //'alnum' => 'Dapat berisi huruf, angka, \' tanda hubung.',
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

    public function registerPerusahaanApi(Request $request, Response $response)
    {
        if ($request->isPost()) {
            // Validate Data
            $this->validateNewCompanies();
            //if ($this->validator->isValid()) {
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
                $user = null;
                $users = Users::where('email', $request->getParam('email'))->get();
                if($users->isEmpty()){
                    $user = $this->auth->registerAndActivate($userDetails);
                    $role->users()->attach($user);
                    //check if fake email
                    $email = $request->getParam('email');
                    $tmpEmail = explode('@',$email);
                    if(count($tmpEmail) > 0){
                        if($tmpEmail[1] != 'system.id'){
                            $sendEmail = new E($this->container);
                            $sendEmail = $sendEmail->sendTemplate(array($user->id), 'registration_system');
                        }
                    }
                    $this->logger->addInfo("Pendaftaran pengguna baru.", array("user" => $user));
                }

                header('Content-Type: application/json');
                echo json_encode($user);
           // }
        }
        
    }

    public function registerPencakerApi(Request $request, Response $response)
    {
        if ($request->isPost()) {
                $role = $this->auth->findRoleByName('Jobseeker');
                $userDetails = [
                    'email' => $request->getParam('email'),
                    'username' => $request->getParam('username'),
                    'password' => '12345678',
                    'fullname' => $request->getParam('nama_lengkap'),
                    'permissions' => [
                        'user.delete' => 0
                    ]
                ];
                $user = null;
                $users = Users::where('email', $request->getParam('email'))->get();
                if($users->isEmpty()) {
                    $user = $this->auth->registerAndActivate($userDetails);
                    $role->users()->attach($user);
                    //check if fake email
                    $email = $request->getParam('email');
                    $tmpEmail = explode('@',$email);
                    if(count($tmpEmail) > 0) {
                        if($tmpEmail[1] != 'system.id') {
                            $sendEmail = new E($this->container);
                            $sendEmail = $sendEmail->sendTemplate(array($user->id), 'registration_system');
                        }
                    }
                    //$this->auth->login($user);
                    $this->logger->addInfo("Pendaftaran pengguna baru.", array("user" => $user));
                }
                header('Content-Type: application/json');
                echo json_encode($user);
        }
        
    }

    public function registerBkkApi(Request $request, Response $response)
    {
        if ($request->isPost()) {
            // Validate Data
            //$this->validateNewCompanies();
            //if ($this->validator->isValid()) {
                $role = $this->auth->findRoleByName('bkk');
                $userDetails = [
                    'username' => $request->getParam('username'),
                    'password' => $request->getParam('password'),
                    'email' => $request->getParam('email'),
                    'bkk_id' => $request->getParam('bkk_id'),
                    'fullname' => $request->getParam('fullname'),
                    'permissions' => [
                        'user.delete' => 0
                    ]
                ];
                $user = null;
                $users = Users::where('email', $request->getParam('email'))->get();
                if($users->isEmpty()){
                    $user = $this->auth->registerAndActivate($userDetails);
                    $role->users()->attach($user);
                    //check if fake email
                    $email = $request->getParam('email');
                    $tmpEmail = explode('@',$email);
                    if(count($tmpEmail) > 0){
                        if($tmpEmail[1] != 'system.id'){
                            $sendEmail = new E($this->container);
                            $sendEmail = $sendEmail->sendTemplate(array($user->id), 'registration_system');
                        }
                    }
                    $this->logger->addInfo("Pendaftaran pengguna baru.", array("user" => $user));
                }
                header('Content-Type: application/json');
                echo json_encode($user);
           // }
        }
        
    }

    public function registerLpkApi(Request $request, Response $response)
    {
        if ($request->isPost()) {
            // Validate Data
            //$this->validateNewCompanies();
            //if ($this->validator->isValid()) {
                $role = $this->auth->findRoleByName('lpk');
                $userDetails = [
                    'username' => $request->getParam('username'),
                    'password' => $request->getParam('password'),
                    'email' => $request->getParam('email'),
                    'lpk_id' => $request->getParam('lpk_id'),
                    'fullname' => $request->getParam('fullname'),
                    'permissions' => [
                        'user.delete' => 0
                    ]
                ];
                $user = null;
                $users = Users::where('email', $request->getParam('email'))->get();
                if($users->isEmpty()){
                    $user = $this->auth->registerAndActivate($userDetails);
                    $role->users()->attach($user);
                    //check if fake email
                    $email = $request->getParam('email');
                    $tmpEmail = explode('@',$email);
                    if(count($tmpEmail) > 0){
                        if($tmpEmail[1] != 'system.id'){
                            $sendEmail = new E($this->container);
                            $sendEmail = $sendEmail->sendTemplate(array($user->id), 'registration_system');
                        }
                    }
                    $this->logger->addInfo("Pendaftaran pengguna baru.", array("user" => $user));
                }
                header('Content-Type: application/json');
                echo json_encode($user);
           // }
        }
        
    }

    public function registerPtApi(Request $request, Response $response)
    {
        if ($request->isPost()) {
            // Validate Data
            //$this->validateNewCompanies();
            //if ($this->validator->isValid()) {
                $role = $this->auth->findRoleByName('Perguruan Tinggi');
                $userDetails = [
                    'username' => $request->getParam('username'),
                    'password' => $request->getParam('password'),
                    'email' => $request->getParam('email'),
                    'perguruan_tinggi_id' => $request->getParam('perguruan_tinggi_id'),
                    'fullname' => $request->getParam('fullname'),
                    'permissions' => [
                        'user.delete' => 0
                    ]
                ];
                $user = null;
                $users = Users::where('email', $request->getParam('email'))->get();
                    if($users->isEmpty()){
                        $user = $this->auth->registerAndActivate($userDetails);
                        $role->users()->attach($user);
                        //check if fake email
                        $email = $request->getParam('email');
                        $tmpEmail = explode('@',$email);
                        if(count($tmpEmail) > 0){
                            if($tmpEmail[1] != 'system.id'){
                                $sendEmail = new E($this->container);
                                $sendEmail = $sendEmail->sendTemplate(array($user->id), 'registration_system');
                            }
                        }
                        $this->logger->addInfo("Pendaftaran pengguna baru.", array("user" => $user));
                    }
                header('Content-Type: application/json');
                echo json_encode($user);
           // }
        }
        
    }

    public function resendActivation(Request $request, Response $response, $user_id) {

        try {
            // ambil data user berdasarkan nik
            $user = Users::where('id', $user_id)->first();

            // jika user ada
            if (count($user) > 0) {
                // check existing role
                $checkRole = RoleUsers::where('user_id', $user_id)->count();
                
                if ($checkRole <= 0) {
                    // reassign role if user don't have role
                    $role = $this->auth->findRoleByName('Jobseeker');
                    $role->users()->attach($user);
                }

                // generate password baru
                $password = Util::generatePassword();
                
                // Ganti password lama user dengan yang baru
                $newPassword = Users::find($user->id);
                $newPassword->password = NativeHasher::hash($password);
                $newPassword->save();

                // ambil code aktivasi
                $checkActivation = ActivationModel::where('user_id', $user->id)->where('completed', 0);

                // jika ada code aktivasi
                if ($checkActivation->count() > 0) {
                    $activation = $checkActivation->first();
                    $code = $activation->code;
                } else {
                    $activation = $this->auth->getActivationRepository();        
                    $activation = $activation->create($user);
                    $code = $activation->code;   
                }

                $confirmUrl = "https://" . $this->config['domain-bkol'] . "/activate?code=" . $code . "&email=" . $user->email;

                $sendEmail = new E($this->container);
                $sendEmail = $sendEmail->sendTemplate(
                    array($user->id),
                    'activation',
                    array(
                        'confirm_url' => $confirmUrl,
                        'password' => $password
                    )
                );

                if ($sendEmail['status'] == "error") {
                    return $response->withJson((object)[
                        'status' => 'failed',
                        'message' => 'Terjadi kesalahan saat mengirim email aktivasi Anda. Silakan hubungi contact support.',
                        'data' => []
                    ], 500);
                }

                if (strlen($user->phonenumber) > 0) {
                    $whatsappMessage = "Hai *".$user->fullname."*,".PHP_EOL.PHP_EOL."Terima kasih telah membuat akun Anda. Berikut info akun Anda :".PHP_EOL.PHP_EOL."Username : ".$user->username.PHP_EOL.PHP_EOL."Password : ". $password.PHP_EOL.PHP_EOL."Untuk memastikan pengalaman terbaik, kami mengharuskan Anda memverifikasi alamat email Anda sebelum Anda dapat mulai menggunakan akun Anda. Untuk melakukannya, cukup klik tautan berikut dan Anda akan segera masuk ke akun Anda.".PHP_EOL.PHP_EOL.$confirmUrl;

                    $whatsapp_api = new WhatsappApi($this->container);
                    $whatsapp_api->sendMessage($user->phonenumber, $whatsappMessage);
                }

                return $response->withJson((object)[
                    'status' => 'success',
                    'message' => 'Resend activation successfully',
                    'data' => []
                ], 200);
            } else {
                return $response->withJson((object)[
                    'status' => 'failed',
                    'message' => 'Akun tidak ditemukan.',
                    'data' => []
                ], 400);
            }
        } catch (Exception $e) {
            return $response->withJson((object)[
                'status' => 'failed',
                'message' => 'Internal Server Error.',
                'data' => [
                    'user' => $user,
                    'stacks' => $e->getMessage()
                ]
            ], 400);
        }
    }
}
