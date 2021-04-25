<?php

namespace App\Controller;

use Carbon\Carbon;
use App\Model\PelatihanCategories;
use App\Model\PelatihanPosts;
use App\Model\PelatihanDaftarPeserta;
use App\Model\PelatihanPostsReplies;
use App\Model\PelatihanTags;
use App\Library\Email as E;
use App\Model\Users;
use App\Library\Paginator;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Pelatihan extends Controller
{

    // Main disnaker-pelatihan Page
    public function pelatihan(Request $request, Response $response)
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

        $pelatihan = PelatihanPosts::where('status', 1)
            ->where('publish_at', '<', Carbon::now())
            ->with('category', 'tags', 'author')
            ->orderBy('publish_at', 'DESC')
            ->with(['peserta' => function ($peserta) use ($user_id) {
                return $peserta->whereHas('userpelatihan', function ($user) use ($user_id) {
                    $user->where('user_id', $user_id);
                })->get();
            }]);

        $pagination = new Paginator($pelatihan->count(), $this->config['pelatihan-per-page'], $page, "/disnaker-pelatihan/(:num)");
        $pagination = $pagination;

        $pelatihan = $pelatihan->skip($this->config['pelatihan-per-page']*($page-1))
            ->take($this->config['pelatihan-per-page']);



        return $this->view->render(
            $response,
            'bkol/pelatihan/pelatihan.twig',
            array(
              "pelatihan" => $pelatihan->get(),
              "pagination" => $pagination)
        );
    }

    public function pelatihanTutup(Request $request, Response $response)
    {

        // Get Page Number
        $page = 1;
        $routeArgs =  $request->getAttribute('route')->getArguments();
        if (isset($routeArgs['page']) && is_numeric($routeArgs['page'])) {
            $page = $routeArgs['page'];
        }

        $pelatihan = PelatihanPosts::where('status', 0)
            ->where('publish_at', '<', Carbon::now())
            ->with('category', 'tags', 'author')
            ->orderBy('publish_at', 'DESC');

        $pagination = new Paginator($pelatihan->count(), $this->config['pelatihan-per-page'], $page, "/disnaker-pelatihan/tutup//(:num)");
        $pagination = $pagination;

        $pelatihan = $pelatihan->skip($this->config['pelatihan-per-page']*($page-1))
            ->take($this->config['pelatihan-per-page']);

        return $this->view->render(
            $response,
            'bkol/pelatihan/pelatihan.twig',
            array("pelatihan" => $pelatihan->get(), "pagination" => $pagination)
        );
    }

    public function pelatihanSeleksi(Request $request, Response $response)
    {
        // Get Page Number
        $page = 1;
        $routeArgs =  $request->getAttribute('route')->getArguments();
        if (isset($routeArgs['page']) && is_numeric($routeArgs['page'])) {
            $page = $routeArgs['page'];
        }

        $pelatihan = PelatihanPosts::where('status', 2)
            ->where('publish_at', '<', Carbon::now())
            ->with('category', 'tags', 'author')
            ->orderBy('publish_at', 'DESC');

        $pagination = new Paginator($pelatihan->count(), $this->config['pelatihan-per-page'], $page, "/disnaker-pelatihan/seleksi//(:num)");
        $pagination = $pagination;

        $pelatihan = $pelatihan->skip($this->config['pelatihan-per-page']*($page-1))
            ->take($this->config['pelatihan-per-page']);

        return $this->view->render(
            $response,
            'bkol/pelatihan/pelatihan.twig',
            array("pelatihan" => $pelatihan->get(), "pagination" => $pagination)
        );
    }

    // Author Posts Page
    public function pelatihanAuthor(Request $request, Response $response)
    {
        $routeArgs =  $request->getAttribute('route')->getArguments();

        $checkAuthor = Users::where('username', $routeArgs['username'])->first();

        if (!$checkAuthor) {
            $this->flash('warning', 'Author not found.');
            return $this->redirect($response, 'disnaker-pelatihan');
        }

        // Get/Set Page Number
        $page = 1;
        if (isset($routeArgs['page']) && is_numeric($routeArgs['page'])) {
            $page = $routeArgs['page'];
        }

        $posts = PelatihanPosts::where('status', 1)
            ->where('user_id', $checkAuthor->id)
            ->where('publish_at', '<', Carbon::now())
            ->with('category', 'tags', 'author')
            ->withCount('comments', 'pendingComments')
            ->orderBy('publish_at', 'DESC');

        $pagination = new Paginator(
            $posts->count(),
            $this->config['pelatihan-per-page'],
            $page,
            "/disnaker-pelatihan/author/".$checkAuthor->username."/(:num)"
        );
        $pagination = $pagination;

        $posts = $posts->skip($this->config['pelatihan-per-page']*($page-1))
            ->take($this->config['pelatihan-per-page']);

        return $this->view->render(
            $response,
            'bkol/pelatihan/pelatihan.twig',
            array(
                "author" => $checkAuthor,
                "posts" => $posts->get(),
                "pagination" => $pagination,
                "authorPage" => true
            )
        );
    }

    // Category Posts Page
    public function pelatihanCategory(Request $request, Response $response)
    {
        $routeArgs =  $request->getAttribute('route')->getArguments();

        $checkCat = PelatihanCategories::where('slug', $routeArgs['slug'])->first();

        if (!$checkCat) {
            $this->flash('warning', 'Tag not found.');
            return $this->redirect($response, 'disnaker-pelatihan');
        }

        // Get/Set Page Number
        $page = 1;
        if (isset($routeArgs['page']) && is_numeric($routeArgs['page'])) {
            $page = $routeArgs['page'];
        }

        $posts = PelatihanCategories::withCount(['posts' => function ($query) {
            $query->where('status', 1)
                    ->where('publish_at', '<', Carbon::now());
        }])
            ->with(['posts' => function ($query) use ($page) {
                $query->where('status', 1)
                    ->where('publish_at', '<', Carbon::now())
                    ->with('category', 'tags', 'author')
                    ->skip($this->config['pelatihan-per-page']*($page-1))
                    ->take($this->config['pelatihan-per-page'])
                    ->orderBy('publish_at', 'DESC');
            }])
            ->find($checkCat->id);


        $pagination = new Paginator(
            $posts->posts_count,
            $this->config['pelatihan-per-page'],
            $page,
            "/disnaker-pelatihan/category/".$checkCat->slug."/(:num)"
        );
        $pagination = $pagination;

        return $this->view->render(
            $response,
            'bkol/pelatihan/pelatihan.twig',
            array(
                "category" => $checkCat,
                "pelatihan" => $posts->posts,
                "pagination" => $pagination,
                "categoryPage" => true
            )
        );
    }

    // disnaker-pelatihan Post
    public function pelatihanPost(Request $request, Response $response)
    {
        $args =  $request->getAttribute('route')->getArguments();

        $post = PelatihanPosts::with(
            'tags',
            'category',
            'author',
            'author.profile',
            'PendaftarPelatihan'
        )
            ->where('slug', $args['slug'])
            ->where('publish_at', '<', Carbon::now())
            ->first();


        if ($this->auth->check()) {
          $loggedin_user = $this->auth->check()->id;
        } else {
          $loggedin_user = null;
        }
        $postapplied = PelatihanDaftarPeserta::where(['user_id' => $loggedin_user, 'post_id' => $post->id])->first();

        if (!$post) {
            $this->flash('danger', 'That disnaker-pelatihan post cound not be found.');
            return $this->redirect($response, 'disnaker-pelatihan');
        }

        if ($request->isPost()) {
            if (!$this->auth->check()) {
                $this->flashNow('danger', 'You need to be logged in to comment.');
                return $this->view->render($response, 'disnaker/pelatiha/detail.twig', array("post" => $post, "showSidebar" => 1));
            }

            if ($request->getParam('add_daftar') !== null) {
                if ($this->addDaftar($post)) {
                    $this->flash('success', 'Pendaftaran Untuk Pelatihan ini berhasil');
                    return $response->withRedirect($request->getUri()->getPath());
                }
                $this->flashNow('warning', 'Kamu Sudah Mendaftar Untuk Pelatihan/Skema Pelatihan ini');
            }
        }

        return $this->view->render(
            $response,
            'bkol/pelatihan/detail.twig',
            array("post" => $post, "showSidebar" => 1,'postapplied' => $postapplied, "requestParams" => $request->getParams())
        );
    }

    public function ModalDaftar(Request $request, Response $response)
    {
        $args =  $request->getAttribute('route')->getArguments();

        $post = PelatihanPosts::with(
            'tags',
            'category',
            'author',
            'author.profile',
            'PendaftarPelatihan'
        )
            ->where('slug', $args['slug'])
            ->where('status', '=', 1)
            ->where('publish_at', '<', Carbon::now())
            ->first();

        $postapplied = PelatihanDaftarPeserta::get();

        if (!$post) {
            $this->flash('danger', 'That disnaker-pelatihan post cound not be found.');
            return $this->redirect($response, 'disnaker-pelatihan');
        }

        if ($request->isPost()) {
            if (!$this->auth->check()) {
                $this->flashNow('danger', 'You need to be logged in to comment.');
                return $this->view->render($response, 'disnaker/pelatiha/detail.twig', array("post" => $post, "showSidebar" => 1));
            }
            if ($request->getParam('add_daftar') !== null) {
                if ($this->addDaftar($post)) {
                    $this->flash('success', 'Pendaftaran Untuk Pelatihan ini berhasil');
                    // return $response->withRedirect($request->getUri()->getPath());
                    return $this->redirect($response, 'disnaker-pelatihan');
                }
                $this->flashNow('warning', 'Kamu Sudah Mendaftar Untuk Pelatihan ini');

            }
        }

        return $this->view->render(
            $response,
            'bkol/pelatihan/daftar.twig',
            array("post" => $post, "showSidebar" => 1,'postapplied' => $postapplied, "requestParams" => $request->getParams())
        );
    }



    private function addDaftar($post)
    {
        // Validate Data
        $validateData = array(
            // 'comment' => array(
            //     'rules' => V::notEmpty()->length(6),
            //     'messages' => array(
            //         'notEmpty' => 'Please enter a comment.',
            //         'length' => 'Comment must contain at least 6 characters'
            //         )
            // )
        );
        $this->validator->validate($this->request, $validateData);

        if ($this->validator->isValid()) {

            $loggedin_user = $this->auth->check()->id;
            $daftar_pelatihan = PelatihanDaftarPeserta::where(['user_id' => $loggedin_user, 'post_id' => $post->id])->first();
            $tahun = $post->tahun ? $post->tahun : date('Y');
            $daftar_pelatihan_skema = PelatihanDaftarPeserta::where(['user_id' => $loggedin_user,'skema' => $post->berbasis,'tahun' => $tahun])->get();
            if(empty($daftar_pelatihan->user_id) & count($daftar_pelatihan_skema) == 0){
                $user_id = $this->auth->check()->id;
                $post_id = $post->id;
                $uniqid = uniqid();
                $rand_start = rand(1,5);
                $daftar = new PelatihanDaftarPeserta;
                $daftar->user_id = $user_id;
                $daftar->post_id = $post_id;
                // $daftar->comment = NULL;
                $daftar->skema = $post->berbasis;
                $daftar->tahun = $tahun;
                $daftar->nomor_pendaftaran = substr($uniqid,$rand_start,8);
                $daftar->status = 0;
                if ($daftar->save()) {
                    $user = Users::with('datapencarikerja')->find($this->auth->check()->id);
                    $sendEmail = new E($this->container);
                    $sendEmail = $sendEmail->sendTemplate(
                        array($post->author->id),
                        'peserta-daftar-pelatihan',
                        array(
                          'nama_pelatihan' => $post->title,
                          'nama_peserta' => $user->datapencarikerja->nama_lengkap
                        )
                    );
                    return true;
                }
            }
        }
        return false;
    }


    // Tag posts page
    public function PelatihanTag(Request $request, Response $response)
    {
        $routeArgs =  $request->getAttribute('route')->getArguments();

        $checkTag = PelatihanTags::where('slug', $routeArgs['slug'])->first();

        if (!$checkTag) {
            $this->flash('warning', 'Tag not found.');
            return $this->redirect($response, 'disnaker-pelatihan');
        }

        // Get/Set Page Number
        $page = 1;
        if (isset($routeArgs['page']) && is_numeric($routeArgs['page'])) {
            $page = $routeArgs['page'];
        }

        if (isset($routeArgs['page']) && !is_numeric($routeArgs['page'])) {
            $this->flash('warning', 'Page not found.');
            return $this->redirect($response, 'disnaker-pelatihan');
        }

        $posts = PelatihanTags::withCount(['posts' => function ($query) {
            $query->where('status', 1)
                    ->where('publish_at', '<', Carbon::now());
        }])
            ->with(['posts' => function ($query) use ($page) {
                $query->where('status', 1)
                    ->where('publish_at', '<', Carbon::now())
                    ->with('category', 'tags', 'author')
                    ->withCount('comments', 'pendingComments')
                    ->skip($this->config['pelatihan-per-page']*($page-1))
                    ->take($this->config['pelatihan-per-page'])
                    ->orderBy('publish_at', 'DESC');
            }])
            ->find($checkTag->id);


        $pagination = new Paginator(
            $posts->posts_count,
            $this->config['pelatihan-per-page'],
            $page,
            "/disnaker-pelatihan/tag/".$checkTag->slug."/(:num)"
        );
        $pagination = $pagination;

        return $this->view->render(
            $response,
            'bkol/pelatihan/pelatihan.twig',
            array(
                "tag" => $checkTag,
                "posts" => $posts->posts,
                "pagination" => $pagination,
                "tagPage" => true
            )
        );
    }


}
