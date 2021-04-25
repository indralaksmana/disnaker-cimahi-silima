<?php

namespace App\Controller\Bkol;
use App\Controller\Controller;
use Carbon\Carbon;
use App\Model\ContactRequests;
use App\Model\Oauth2Providers;
use App\Library\Email as E;
use App\Model\Users;
use App\Model\CompaniesProfile;
use App\Model\JobCategories;
use App\Model\GolonganJabatanModel;
use App\Model\JobPosts;
use App\Model\Users as U;
use App\Model\JobPostsTags;
use App\Model\JobTags;
use App\Model\JobPostsApply;
use App\Model\PelatihanPosts;
use App\Library\Paginator;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use Slim\Views\PhpRenderer;


/** @SuppressWarnings(PHPMD.StaticAccess) */
class Bkol extends Controller
{


    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    // Main job Page

    public function lowongan(Request $request, Response $response)
    {
        // Get Page Number
        $page = 1;
        $routeArgs =  $request->getAttribute('route')->getArguments();
        if (isset($routeArgs['page']) && is_numeric($routeArgs['page'])) {
            $page = $routeArgs['page'];
        }
        if ($this->auth->check()) {
          $user_id = $this->auth->check()->id;
        } else {
          $user_id = null;
        }
        $jobs = JobPosts::where('status', 1)
        ->where('publish_at', '<', Carbon::now())
        ->where('tanggal_berakhir', '>', Carbon::now())
        ->orderBy('id', 'DESC')->with(['pelamar' => function ($pelamar) use ($user_id) {
            return $pelamar->whereHas('user', function ($user) use ($user_id) {
                $user->where('user_id', $user_id);
            })->get();
        }]);


        // $pagination = new Paginator($jobs->count(),
        //     $this->config['job-per-page'],
        //     $page,
        //     "/lowongan-kerja/(:num)");
        // $pagination = $pagination;
        // $jobs = $jobs ->skip($this->config['job-per-page']*($page-1))
        //     ->take($this->config['job-per-page']);
        return $this->view->render(
            $response,
            'bkol/lowongan/list.twig',
            array(
                "jobs" => $jobs->get(),
                // "pagination" => $pagination,
                "requestParams" => $request->getParams()
            )
        );
    }

    public function searchResult(Request $request, Response $response)
    {


      $count = JobPosts::where('status','=','1')->count();
      // Get Page Number

      $page = 1;
      $routeArgs =  $request->getAttribute('route')->getArguments();
      if (isset($routeArgs['page']) && is_numeric($routeArgs['page'])) {
          $page = $routeArgs['page'];
      }
      $title = isset($_GET['search']) ? (string) $_GET['search'] : '';
      $jobs = JobPosts::where('status', 1)
          ->where('publish_at', '<', Carbon::now())
          ->with('category', 'tags', 'authorCompanies')
          ->where('nama_lowongan', 'LIKE', '%' . $title . '%')

          ->orderBy('publish_at', 'DESC');

      $pagination = new Paginator($jobs->count(),
          $this->config['job-per-page'],
          $page,
          "/lowongan-kerja/(:num)");
      $pagination = $pagination;

      $jobs = $jobs ->skip($this->config['job-per-page']*($page-1))
          ->take($this->config['job-per-page']);

      return $this->view->render(
          $response,
          'bkol/lowongan/list.twig',
          array(
              "jobs" => $jobs->get(),
              "pagination" => $pagination,
              "requestParams" => $request->getParams()
          )
      );
    }

    public function filterbkol(Request $request, Response $response)
    {
      $page = 1;
      $routeArgs =  $request->getAttribute('route')->getArguments();
      if (isset($routeArgs['page']) && is_numeric($routeArgs['page'])) {
          $page = $routeArgs['page'];
      }

      $term = $_GET;
      $jabatan = $_GET['jabatan'] ?? false;
      $provinsi = $_GET['provinsi'] ?? false;
      $kabupatenkota = $_GET['kabupatenkota'] ?? false;
      $pendidikan = $_GET['pendidikan'] ?? false;

        $jobs = JobPosts::query()
              ->where('status', 1)
              ->with('category', 'tags', 'authorCompanies')
              ->orderBy('publish_at', 'DESC')
              ->where('publish_at', '<', Carbon::now());
              if(!empty($jabatan)){
                 $jobs->whereIn('jabatan_id',$jabatan);
              }
              if(!empty($provinsi)){
                 $jobs->orWhereIn('provinsi_id',$provinsi);
              }
              if(!empty($kabupatenkota)){
                 $jobs->orWhereIn('kabupatenkota_id', $kabupatenkota);
              }
              if(!empty($pendidikan)){
                  $jobs->orWhereIn('pendidikan_terakhir_id',$pendidikan);
              }

      $pagination = new Paginator($jobs->count(),
          $this->config['job-per-page'],
          $page,
          "/lowongan-kerja/(:num)");
      $pagination = $pagination;

      $jobs = $jobs ->skip($this->config['job-per-page']*($page-1))
          ->take($this->config['job-per-page']);

      return $this->view->render(
          $response,
          'bkol/lowongan/list.twig',
          array(
              "jobs" => $jobs->get(),
              "pagination" => $pagination,
              "requestParams" => $request->getParams()
          )
      );
    }
    // Author Posts Page
    public function jobCompanies(Request $request, Response $response)
    {
        $routeArgs =  $request->getAttribute('route')->getArguments();

        $checkCompanies = CompaniesProfile::where('companyname', $routeArgs['companyname'])->first();

        if (!$checkCompanies) {
            $this->flash('warning', 'Author not found.');
            return $this->redirect($response, 'homebkol');
        }

        // Get/Set Page Number
        $page = 1;
        if (isset($routeArgs['page']) && is_numeric($routeArgs['page'])) {
            $page = $routeArgs['page'];
        }

        $jobs = JobPosts::where('status', 1)
            ->where('user_id', $checkCompanies->id)
            ->where('publish_at', '<', Carbon::now())
            ->with('category', 'tags', 'authorCompanies')
            ->orderBy('publish_at', 'DESC');

        $pagination = new Paginator(
            $jobs->count(),
            $this->config['job-per-page'],
            $page,
            "/lowongan-kerja/companies/".$checkCompanies->companyname."/(:num)"
        );
        $pagination = $pagination;

        $jobs = $jobs->skip($this->config['job-per-page']*($page-1))
            ->take($this->config['job-per-page']);

        return $this->view->render(
            $response,
            'bkol/companies.twig',
            array(
                "authorCompanies" => $checkCompanies,
                "jobs" => $jobs->get(),
                "pagination" => $pagination,
                "companiesPage" => true
            )
        );
    }

    // Category Posts Page
    public function jobCategory(Request $request, Response $response)
    {
        $routeArgs =  $request->getAttribute('route')->getArguments();

        $checkCat = JobCategories::where('slug', $routeArgs['slug'])->first();

        if (!$checkCat) {
            $this->flash('warning', 'Tag not found.');
            return $this->redirect($response, 'home');
        }

        // Get/Set Page Number
        $page = 1;
        if (isset($routeArgs['page']) && is_numeric($routeArgs['page'])) {
            $page = $routeArgs['page'];
        }

        $jobs = JobCategories::withCount(['posts' => function ($query) {
            $query->where('status', 1)
                    ->where('publish_at', '<', Carbon::now());
        }])
            ->with(['posts' => function ($query) use ($page) {
                $query->where('status', 1)
                    ->where('publish_at', '<', Carbon::now())
                    ->with('category', 'tags', 'author')
                    ->skip($this->config['job-per-page']*($page-1))
                    ->take($this->config['job-per-page'])
                    ->orderBy('publish_at', 'DESC');
            }])
            ->find($checkCat->id);


        $pagination = new Paginator(
            $jobs->jobs_count,
            $this->config['job-per-page'],
            $page,
            "/lowongan-kerja/category/".$checkCat->slug."/(:num)"
        );
        $pagination = $pagination;

        return $this->view->render(
            $response,
            'bkol/lowongan/list.twig',
            array(
                "category" => $checkCat,
                "jobs" => $jobs->posts,
                "pagination" => $pagination,
                "categoryPage" => true
            )
        );
    }

    public function jobJabatan(Request $request, Response $response)
    {
        $routeArgs =  $request->getAttribute('route')->getArguments();

        $checkCat = GolonganJabatanModel::where('id', $routeArgs['id'])->first();

        if (!$checkCat) {
            $this->flash('warning', 'Tag not found.');
            return $this->redirect($response, 'home');
        }

        // Get/Set Page Number
        $page = 1;
        if (isset($routeArgs['page']) && is_numeric($routeArgs['page'])) {
            $page = $routeArgs['page'];
        }

        $jobs = GolonganJabatanModel::withCount(['posts' => function ($query) {
            $query->where('status', 1)
                    ->where('publish_at', '<', Carbon::now());
        }])
            ->with(['posts' => function ($query) use ($page) {
                $query->where('status', 1)
                    ->where('publish_at', '<', Carbon::now())
                    ->with('category', 'tags', 'author')
                    ->skip($this->config['job-per-page']*($page-1))
                    ->take($this->config['job-per-page'])
                    ->orderBy('publish_at', 'DESC');
            }])
            ->find($checkCat->id);


        $pagination = new Paginator(
            $jobs->jobs_count,
            $this->config['job-per-page'],
            $page,
            "/lowonga-kerja/jabatan/".$checkCat->id."/(:num)"
        );
        $pagination = $pagination;

        return $this->view->render(
            $response,
            'bkol/lowongan/list.twig',
            array(
                "category" => $checkCat,
                "jobs" => $jobs->posts,
                "pagination" => $pagination,
                "categoryPage" => true
            )
        );
    }

    // job Post
    public function jobPost(Request $request, Response $response)
    {

        $args =  $request->getAttribute('route')->getArguments();

        $post = JobPosts::with(
            'tags',
            'category',
            'authorCompanies',
            'authorCompanies.profile',
            'pemerintah'
        )
            ->where('slug', $args['slug'])
            ->where('status', '=', 1)
            ->where('publish_at', '<', Carbon::now())
            ->first();

        if (!$post) {
            $this->flash('danger', 'Lowongan Tersebut Tidak Tersedia');
            return $this->redirect($response, 'bkol-lowongan');
        }

        if ($request->isPost()) {
            if (!$this->auth->check()) {
                $this->flashNow('danger', 'Kamu Harus Login Agar Bisa Melamar Pekerjaan ini');
                return $this->view->render($response, 'job-detail.twig', array("post" => $post, "showSidebar" => 1));
            }

            if ($request->getParam('add_apply') !== null) {
                if ($this->addApplying($post)) {
                    $this->flash('success', 'Lamaran berhasil terkirim');
                    return $response->withRedirect($request->getUri()->getPath());
                }
                $this->flashNow('warning', 'Kamu sudah Melamar pekerjaan ini');
            }

            if ($request->getParam('add_reply') !== null) {
                if ($this->addReply()) {
                    $this->flash('success', 'Your reply has been submitted.');
                    return $response->withRedirect($request->getUri()->getPath());
                }
                $this->flashNow('danger', 'There was a problem submitting your reply. please try again.');
            }
        }

        if ($this->auth->check()) {
          $loggedin_user = $this->auth->check()->id;
        } else {
          $loggedin_user = null;
        }
        $postlamaran = JobPostsApply::where(['user_id' => $loggedin_user, 'post_id' => $post->id])->first();

        return $this->view->render(
            $response,
            'bkol/job-detail.twig',
            array(
              "post" => $post,
              "postlamaran" => $postlamaran,
              "showSidebar" => 1, "requestParams" => $request->getParams())
        );
    }

    // job Post
    public function jobPostApplication(Request $request, Response $response)
    {

        $args =  $request->getAttribute('route')->getArguments();

        $post = JobPosts::with(
            'tags',
            'category',
            'authorCompanies',
            'authorCompanies.profile'
        )
            ->where('slug', $args['slug'])
            ->where('status', '=', 1)
            ->where('publish_at', '<', Carbon::now())
            ->first();

        if (!$post) {
            $this->flash('danger', 'That job post cound not be found.');
            return $this->redirect($response, 'bkol-lowongan');
        }

        if ($request->isPost()) {
            if (!$this->auth->check()) {
                $this->flashNow('danger', 'You need to be logged in to comment.');
                return $this->view->render($response, 'job-detail.twig', array("post" => $post, "showSidebar" => 1));
            }

            if ($request->getParam('add_apply') !== null) {
                if ($this->addApplying($post)) {
                    $this->flash('success', 'Lamaran Berhasil Terkirim');
                    return $this->redirect($response, 'homebkol');
                }
                $this->flashNow('warning', 'Anda Sudah Melamar di Lowongan ini');
            }
        }

        return $this->view->render(
            $response,
            'bkol/application.twig',
            array("post" => $post, "showSidebar" => 1, "requestParams" => $request->getParams())
        );
    }

    public function ModalApplication(Request $request, Response $response)
    {

        $args =  $request->getAttribute('route')->getArguments();

        $post = JobPosts::with(
            'tags',
            'category',
            'author',
            'authorCompanies',
            'authorCompanies.profile'
        )
            ->where('slug', $args['slug'])
            ->where('status', '=', 1)
            ->where('publish_at', '<', Carbon::now())
            ->first();

        if (!$post) {
            $this->flash('danger', 'Lowongan Tidak Tersedia');
            return $this->redirect($response, 'homebkol');
        }

        if ($request->isPost()) {
            if (!$this->auth->check()) {
                $this->flashNow('danger', 'Kamu Harus Login Terlebih Dahulu Untuk Bisa Melamar Pekerjaan Ini');
                return $this->view->render($response, 'job-detail.twig', array("post" => $post, "showSidebar" => 1));
            }

            if ($request->getParam('add_apply') !== null) {
                if ($this->addApplying($post)) {
                    $this->flash('success', 'Lamaran Berhasil Terkirim');
                    return $this->redirect($response, 'homebkol');
                }
                $this->flashNow('danger', 'Lamaran Gagal Terkirim');
            }
        }

        return $this->view->render(
            $response,
            'bkol/modal-lamaran.twig',
            array(
                "post" => $post,
                "requestParams" => $request->getParams()
            )
        );
    }

    private function addApplying($post)
    {
        // Validate Data

        $validateData = array(
            'coverletter' => array(
                'rules' => V::notEmpty()->length(6),
                'messages' => array(
                    'notEmpty' => 'Please enter a coverletter.',
                    'length' => 'Comment must contain at least 6 characters'
                    )
            )
        );
        $this->validator->validate($this->request, $validateData);
        if ($this->validator->isValid()) {
            $loggedin_user = $this->auth->check()->id;
            $daftar_kerjaan = JobPostsApply::where(['user_id' => $loggedin_user, 'post_id' => $post->id])->first();
            if(empty($daftar_kerjaan->user_id)){
                $user_id = $this->auth->check()->id;
                $post_id = $post->id;
                $daftar = new JobPostsApply;
                $daftar->user_id = $user_id;
                $daftar->post_id = $post_id;
                $daftar->coverletter = strip_tags($this->request->getParam('coverletter'));
                $daftar->status = 0;
                if ($daftar->save()) {
                    $user = Users::with('datapencarikerja')->find($this->auth->check()->id);
                    $sendEmail = new E($this->container);
                    $sendEmail = $sendEmail->sendTemplate(
                        array($post->author->id),
                        'pelamar-baru',
                        array(
                          'nama_lowongan' => $post->nama_lowongan,
                          'nama_pelamar' => $user->datapencarikerja->nama_lengkap
                        )
                    );
                    return true;
                }

            }


        }
        return false;
    }

    // Tag posts page
    public function jobTag(Request $request, Response $response)
    {
        $routeArgs =  $request->getAttribute('route')->getArguments();

        $checkTag = JobTags::where('slug', $routeArgs['slug'])->first();

        if (!$checkTag) {
            $this->flash('warning', 'Tag not found.');
            return $this->redirect($response, 'bkol-lowongan');
        }

        // Get/Set Page Number
        $page = 1;
        if (isset($routeArgs['page']) && is_numeric($routeArgs['page'])) {
            $page = $routeArgs['page'];
        }

        if (isset($routeArgs['page']) && !is_numeric($routeArgs['page'])) {
            $this->flash('warning', 'Page not found.');
            return $this->redirect($response, 'bkol-lowongan');
        }

        $posts = JobTags::withCount(['posts' => function ($query) {
            $query->where('status', 1)
                    ->where('publish_at', '<', Carbon::now());
        }])
            ->with(['posts' => function ($query) use ($page) {
                $query->where('status', 1)
                    ->where('publish_at', '<', Carbon::now())
                    ->with('category', 'tags', 'author')

                    ->skip($this->config['job-per-page']*($page-1))
                    ->take($this->config['job-per-page'])
                    ->orderBy('publish_at', 'DESC');
            }])
            ->find($checkTag->id);


        $pagination = new Paginator(
            $posts->posts_count,
            $this->config['job-per-page'],
            $page,
            "/berita/tag/".$checkTag->slug."/(:num)"
        );
        $pagination = $pagination;

        return $this->view->render(
            $response,
            'disnaker/job.twig',
            array(
                "tag" => $checkTag,
                "posts" => $posts->posts,
                "pagination" => $pagination,
                "tagPage" => true
            )
        );
    }

    // Page
    public function tentang(Request $request, Response $response)
    {

        return $this->view->render(
            $response,
            'bkol/tentang.twig'
        );
    }

    public function kontak(Request $request, Response $response)
    {

        return $this->view->render(
            $response,
            'bkol/page/kontak.twig'
        );
    }

    public function peraturan(Request $request, Response $response)
    {

        return $this->view->render(
            $response,
            'bkol/page/peraturan.twig'
        );
    }

}
