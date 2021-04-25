<?php
// Initialize Container
$container = $app->getContainer();

// Configure Database
$db = $container['settings']['db'][$container['settings']['environment']];
$dblogger = $container['settings']['dblogger'][$container['settings']['environment']];
$capsule = new \Illuminate\Database\Capsule\Manager();
$capsule->addConnection($db);
$capsule->addConnection($dblogger, 'dblogger');
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['db'] = function () use ($capsule) {
    return $capsule;
};

$container['session'] = function () use ($container) {
    return new \SlimSession\Helper;
};

$container['project_dir'] = function ($container) {
    $directory = __DIR__ . "/../../";
    return realpath($directory);
};

$container['public_dir'] = function ($container) {
    $directory = __DIR__ . "/../../public/";
    return realpath($directory);
};

$container['upload_dir'] = function ($container) {
    $directory = __DIR__ . "/../../public/uploads/";
    return realpath($directory);
};

// Bind config table from database
$container['config'] = function () use ($container) {
    $config = new \App\Library\SiteConfig;
    $config = $config->getGlobalConfig();

    return $config;
};

// Bind Sentinel Authorization plugin
$container['auth'] = function () {
    $sentinel = new \Cartalyst\Sentinel\Native\Facades\Sentinel(
        new \Cartalyst\Sentinel\Native\SentinelBootstrapper(__DIR__ . '/sentinel.php')
    );

    return $sentinel->getSentinel();
};

$container['LogActivity'] = function ($container) {
    return (new \App\Library\LogActivity($container));
};

// Bind User Permissions
$container['userAccess'] = function ($container) {
    return (new \App\Library\Sentinel($container))->userAccess();
};

// Bind Flash Messages
$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

// Bind Respect Validation
$container['validator'] = function () {
    return new \Awurth\SlimValidation\Validator();
};

// CSRF
$container['csrf'] = function ($container) {
    $guard = new \Slim\Csrf\Guard(
        $container->settings['csrf']['prefix'],
        $storage,
        null,
        $container->settings['csrf']['storage_limit'],
        $container->settings['csrf']['strength'],
        $container->settings['csrf']['persist_tokens']
    );

    $guard->setFailureCallable(function ($request, $response, $next) use ($container) {
        return $container['view']
            ->render($response, 'dashboard/errors/csrf.twig')
            ->withHeader('Content-type', 'text/html')
            ->withStatus(401);
    });

    return $guard;
};

// Bind Twig View
$container['view'] = function ($container) {
    $template_path = __DIR__ . '/../views/';


    $view = new \Slim\Views\Twig(
        $template_path,
        $container['settings']['view']['twig']
    );

    $view->addExtension(new \Slim\Views\TwigExtension(
        $container['router'],
        $container['request']->getUri()
    ));
    // $view->addExtension(new \DPolac\TwigLambda\LambdaExtension());
    $view->addExtension(new \Twig_Extension_Debug());
    $view->addExtension(new Twig_Extensions_Extension_Date());
    $view->addExtension(new \App\TwigExtension\Asset($container['request']));
    $view->addExtension(new \App\TwigExtension\JsonDecode($container['request']));
    $view->addExtension(new \App\TwigExtension\Oauth2($container));
    $view->addExtension(new \App\TwigExtension\Recaptcha($container['settings']['recaptcha']));
    $view->addExtension(new \App\TwigExtension\Csrf($container['csrf']));
    $view->addExtension(new \Awurth\SlimValidation\ValidatorExtension($container['validator']));
    $view->addExtension(new \App\TwigExtension\Md5($container['request']));
    if ($container['cloudinary']) {
        $view->addExtension(new \App\TwigExtension\Cloudinary());
        $view->getEnvironment()->addGlobal('hasCloudinary', 1);
        if ($container->auth->check() && $container->auth->hasAccess('media.cloudinary')) {
            $view->getEnvironment()->addGlobal(
                'cloudinaryCmsUrl',
                \App\Controller\Admin\Media::getCloudinaryCMS($container)
            );
            $view->getEnvironment()->addGlobal(
                'cloudinarySignature',
                \App\Controller\Admin\Media::getCloudinaryCMS($container, true)
            );
            $view->getEnvironment()->addGLobal('cloudinaryApiKey', $container['settings']['cloudinary']['api_key']);
            $view->getEnvironment()->addGLobal('cloudinaryCloudName', $container['settings']['cloudinary']['cloud_name']);
        }
    } else {
        $view->addExtension(new \App\TwigExtension\Cloudinary());
        $view->getEnvironment()->addGlobal('hasCloudinary', 0);
    }

    $base_chima = $container['settings']['base_chima'];
    $view->getEnvironment()->addGlobal('baseChima', $base_chima);

    
    // Get Categories With Count
    $b_job_categories = new \App\Model\JobCategories;
    $b_job_categories = $b_job_categories->withCount(['posts' => function ($query) {
        $query->where('b_job_posts.status', 1);
    }])
        ->whereHas('posts', function ($query) {
            $query->where('b_job_posts.status', 1);
        })
        ->take(5)
        ->get();

    $list_pelatihan = new \App\Model\PelatihanPosts;
    $list_pelatihan = $list_pelatihan->get();

    $job_jabatan = new \App\Model\GolonganJabatanModel;
    $job_jabatan = $job_jabatan->withCount(['posts' => function ($query) {
        $query->where('b_job_posts.status', 1);
    }])
        ->whereHas('posts', function ($query) {
            $query->where('b_job_posts.status', 1);
        })
        ->get();

    $job_provinsi = new \App\Model\Daerah;
    $job_provinsi = $job_provinsi->withCount(['jobprovinsi' => function ($query) {
        $query->where('b_job_posts.status', 1);
    }])
        ->whereHas('jobprovinsi', function ($query) {
            $query->where('b_job_posts.status', 1);
        })
        ->get();
    $jobkabupatenkota = new \App\Model\Daerah;
    $jobkabupatenkota = $jobkabupatenkota->withCount(['jobkabupatenkota' => function ($query) {
        $query->where('b_job_posts.status', 1);
    }])
        ->whereHas('jobkabupatenkota', function ($query) {
            $query->where('b_job_posts.status', 1);
        })
        ->get();

    $jobpendidikan = new \App\Model\JenisPendidikanModel;
    $jobpendidikan = $jobpendidikan->withCount(['jobpendidikan' => function ($query) {
        $query->where('b_job_posts.status', 1);
    }])
        ->whereHas('jobpendidikan', function ($query) {
            $query->where('b_job_posts.status', 1);
        })
        ->get();

    // Get Tags With Count
    $b_job_tags = new \App\Model\JobTags;
    $b_job_tags = $b_job_tags->withCount(['posts' => function ($query) {
        $query->where('b_job_posts.status', 1);
    }])
        ->whereHas('posts', function ($query) {
            $query->where('b_job_posts.status', 1);
        })
        ->get();

    $lokasiperusahaan = new \App\Model\Daerah;
    $lokasiperusahaan = $lokasiperusahaan->withCount('bkolperusahaan')
        ->whereHas('bkolperusahaan', function ($query) {
            $query->has('userperusahaan');
        })
        ->get();


    $view->getEnvironment()->addGlobal('PerusahaanLokasi', $lokasiperusahaan);
    $view->getEnvironment()->addGlobal('listPelatihan', $list_pelatihan);
    $view->getEnvironment()->addGlobal('jobPendidikan', $jobpendidikan);
    $view->getEnvironment()->addGlobal('jobProvinsi', $job_provinsi);
    $view->getEnvironment()->addGlobal('jobKabupatenkota', $jobkabupatenkota);
    $view->getEnvironment()->addGlobal('jobPendidikan', $jobpendidikan);
    $view->getEnvironment()->addGlobal('jobJabatan', $job_jabatan);
    $view->getEnvironment()->addGlobal('jobCategories', $b_job_categories);
    $view->getEnvironment()->addGlobal('jobTags', $b_job_tags);
    

    $jumlahbkk = new \App\Model\BkkModel;
    $jumlahbkk = $jumlahbkk->count();
    $view->getEnvironment()->addGlobal('jumlahBkk', $jumlahbkk);

    $jumlahlpk = new \App\Model\LpkModel;
    $jumlahlpk = $jumlahlpk->count();
    $view->getEnvironment()->addGlobal('jumlahLpk', $jumlahlpk);

    $jumlahdikti = new \App\Model\PerguruanTinggiModel;
    $jumlahdikti = $jumlahdikti->count();
    $view->getEnvironment()->addGlobal('jumlahDikti', $jumlahdikti);

    $jmlPelatihanKerja = new \App\Model\PelatihanPosts;
    $jmlPelatihanKerja = $jmlPelatihanKerja->count();
    $view->getEnvironment()->addGlobal('jmlPelatihanKerja', $jmlPelatihanKerja);

    $perusahaanTerdaftar = new \App\Model\DataPerusahaanModel;
    $perusahaanTerdaftar = $perusahaanTerdaftar->count();
    $view->getEnvironment()->addGlobal('perusahaanTerdaftar', $perusahaanTerdaftar);

    $jumlahPencaker = new \App\Model\Users;
    $jumlahPencaker  = $jumlahPencaker->with('roles')->WhereHas('roles', function($q){$q->whereIn('name', ['jobseeker']);})
          ->count();
    $view->getEnvironment()->addGlobal('jumlahPencaker', $jumlahPencaker);
    
    $jumlahPerusahaan = new \App\Model\Users;
    $jumlahPerusahaan  = $jumlahPerusahaan->with('roles')->WhereHas('roles', function($q){$q->whereIn('name', ['companies']);})
          ->count();
    $view->getEnvironment()->addGlobal('jumlahPerusahaan', $jumlahPerusahaan);
        
    $jumlahLowongan = new \App\Model\JobPosts;
    $jumlahLowongan = $jumlahLowongan->where('status', 1)->count();
    $view->getEnvironment()->addGlobal('jumlahLowongan', $jumlahLowongan);


   

    $lulusanPencariKerja = new \App\Model\JenisPendidikanModel;
    $lulusanPencariKerja = $lulusanPencariKerja->withCount('pencarikerjaLulusan')
        ->whereHas('pencarikerjaLulusan', function ($query) {
            $query->has('user');
        })
        ->get();
    $view->getEnvironment()->addGlobal('lulusanPencariKerja', $lulusanPencariKerja);

    $kecamatanPencariKerja = new \App\Model\Daerah;
    $kecamatanPencariKerja = $kecamatanPencariKerja->withCount('kecamatanpencaker')
        ->whereHas('kecamatanpencaker', function ($query) {
            $query->has('user');
        })
        ->get();
    $view->getEnvironment()->addGlobal('kecamatanPencariKerja', $kecamatanPencariKerja);



    if ($container['config']['pelatihan-enabled']) {
        // Get Categories With Count
        $c_pp_pelatihan_categories = new \App\Model\PelatihanCategories;
        $c_pp_pelatihan_categories = $c_pp_pelatihan_categories->withCount(['posts' => function ($query) {
            $query->where('c_pp_pelatihan_posts.status', 1);
        }])
            ->whereHas('posts', function ($query) {
                $query->where('c_pp_pelatihan_posts.status', 1);
            })
            ->get();
        // Get Tags With Count
        $pelatihan_tags = new \App\Model\PelatihanTags;
        $pelatihan_tags = $pelatihan_tags->withCount(['posts' => function ($query) {
            $query->where('c_pp_pelatihan_posts.status', 1);
        }])
            ->whereHas('posts', function ($query) {
                $query->where('c_pp_pelatihan_posts.status', 1);
            })
            ->get();

        $view->getEnvironment()->addGlobal('pelatihanCategories', $c_pp_pelatihan_categories);
        $view->getEnvironment()->addGlobal('pelatihanTags', $pelatihan_tags);
    }

    $view->getEnvironment()->addGlobal('flash', $container['flash']);
    $view->getEnvironment()->addGlobal('auth', $container['auth']);
    $view->getEnvironment()->addGlobal('config', $container['config']);
    $view->getEnvironment()->addGlobal('displayErrorDetails', $container['settings']['displayErrorDetails']);
    $view->getEnvironment()->addGlobal('currentRoute', $container['request']->getUri()->getPath());
    $view->getEnvironment()->addGlobal('requestParams', $container['request']->getParams());
    $view->getEnvironment()->addGlobal('projectDir', $container['project_dir']);
    $view->getEnvironment()->addGlobal('publicDir', $container['public_dir']);
    $view->getEnvironment()->addGlobal('uploadDir', $container['upload_dir']);

    $page_name = $container['request']->getAttribute('name');
    if (strpos($container['request']->getUri()->getPath(), '/dashboard') !== false) {
        $page_settings = new \App\Model\ConfigGroups;
        $page_settings = $page_settings->whereNotNull('page_name')->get();
        $view->getEnvironment()->addGlobal('userAccess', $container['userAccess']);
        $view->getEnvironment()->addGlobal('pageSettings', $page_settings);
        $view->getEnvironment()->addGlobal('showInAdmin', $container['settings']['showInAdmin']);
    }
    return $view;
};

// Bind Found Handler
$container['foundHandler'] = function () {
    return new \Slim\Handlers\Strategies\RequestResponseArgs();
};

// Bind Monolog Logging System if Enables
$container['logger'] = function ($container) {

    // Stream Log output to file
    $logger = new Monolog\Logger($container['settings']['logger']['log_name']);
    $logPath = __DIR__ . "/../../storage/log/monolog/";
    $logFile =  date("Y-m-d-") . $container['settings']['logger']['log_file_name'];
    $fileStream = new \Monolog\Handler\StreamHandler($logPath . $logFile);
    $logger->pushHandler($fileStream);

    //Stream log output to Logentries
    if ($container['settings']['logger']['le_token'] != '') {
        $le_stream = new Logentries\Handler\LogentriesHandler($container['settings']['logger']['le_token']);
        $logger->pushHandler($le_stream);
    }

    return $logger;
};

// Cloudinary PHP API
$container['cloudinary'] = function ($container) {
    if ($container['settings']['cloudinary']['enabled']) {
        \Cloudinary::config(
            array( "cloud_name" => $container['settings']['cloudinary']['cloud_name'],
                "api_key" => $container['settings']['cloudinary']['api_key'],
                "api_secret" => $container['settings']['cloudinary']['api_secret']
            )
        );

        return new \Cloudinary;
    } else {
        return false;
    }
};

// Mail Relay
$container['mail'] = function ($container) {
    $mailSettings = $container['settings']['mail'];

    $mail = new \PHPMailer\PHPMailer\PHPMailer;

    switch ($mailSettings['relay']) {
        case 'phpmail':
            break;

        case 'smtp':
            $mail->isSMTP();
            $mail->Host = $mailSettings['smtp']['host'];
            $mail->Port = $mailSettings['smtp']['port'];
            if ($mailSettings['smtp']['smtp_auth']) {
                $mail->SMTPAuth = true;
                $mail->Username = $mailSettings['smtp']['username'];
                $mail->Password = $mailSettings['smtp']['password'];
            }
            $mail->SMTPSecure = $mailSettings['smtp']['smtp_secure'];
            break;

        default:
            break;
    }
    return $mail;
};
