<?php

namespace App\Controller\Front\Minisite;

use App\Controller\Controller;
use Carbon\Carbon;
use Psr\Container\ContainerInterface;
use App\Model\PemerintahModel;
use App\Model\JobPosts;
use App\Model\PelatihanPosts;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Pemerintah extends Controller
{


    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function index(Request $request, Response $response)
    {

        $pemerintah = PemerintahModel::orderBy('created_at', 'DESC')->has('userpemerintah');
        $jobs = JobPosts::whereHas('pemerintah', function( $query ){
            $query->has('userpemerintah');
        } );

        $pelatihans = PelatihanPosts::whereHas('author', function( $query ){
            $query->has('pemerintah');
        })->orderBy('updated_at', 'DESC');

        return $this->view->render(
            $response,
            'bkol/minisite/pemerintah/index.twig',
            array(
                "pemerintah" => $pemerintah->get(),
                'jobs' => $jobs->get(),
                'pelatihans' => $pelatihans->get()
            )
        );
    }

    // Blog Post
    public function detail(Request $request, Response $response)
    {
        $args =  $request->getAttribute('route')->getArguments();
        $post = PemerintahModel::where('slug', $args['slug'])->with('userpemerintah')->first();
        $id = $post->id;

        $jobs = JobPosts::where('status', 1)
            ->where('user_id', $post->id)
            ->where('publish_at', '<', Carbon::now())
            ->with('category', 'tags', 'authorCompanies')
            ->orderBy('publish_at', 'DESC');

        if (!$post) {
            $this->flash('danger', 'Pemerintah Tidak di temukan');
            return $this->redirect($response, 'minisite-pemerintah');
        }

        $pelatihans = PelatihanPosts::whereHas('author', function( $query ){
            $query->has('pemerintah');
        })->orderBy('updated_at', 'DESC');
        
        return $this->view->render(
            $response,
            'bkol/minisite/pemerintah/detail.twig',
            array(
              "post" => $post,
              "jobs" => $jobs->get(),
              "pelatihans" => $pelatihans->get()
            )
        );
    }
}
