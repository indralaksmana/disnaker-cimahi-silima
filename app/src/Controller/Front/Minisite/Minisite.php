<?php

namespace App\Controller\Front\Minisite;

use App\Controller\Controller;
use Carbon\Carbon;
use Psr\Container\ContainerInterface;
use App\Model\AgendaPosts;
use App\Model\BlogPosts;
use App\Model\GalleryPosts;
use App\Model\BlogCategories;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Minisite extends Controller
{
    
    // Berita
    public function DetailBerita(Request $request, Response $response)
    {



        $args =  $request->getAttribute('route')->getArguments();

        $post = BlogPosts::with(
            'tags',
            'category',
            'author',
            'author.profile',
            'approvedComments',
            'approvedComments.approvedReplies'
        )
            ->where('slug', $args['slug'])
            ->where('status', '=', 1)
            ->first();

        $categories = BlogCategories::where('status', 1)->get();
        $posts = BlogPosts::where('status', 1)
                ->orderBy('publish_at', 'desc')
                ->take(5)
                ->get();

        if (!$post) {
            $this->flash('danger', 'That blog post cound not be found.');
            return $this->redirect($response, 'blog');
        }
        return $this->view->render(
            $response,
            'bkol/minisite/berita/detail.twig',
            array(
                "post" => $post,
                'categories' => $categories,
                'posts' => $posts
            )
        );
    }
    // Agenda
    public function DetailAgenda(Request $request, Response $response)
    {
        $args =  $request->getAttribute('route')->getArguments();

        $post = AgendaPosts::with(
            'category'
        )
            ->where('slug', $args['slug'])
            ->where('status', '=', 1)
            ->where('publish_at', '<', Carbon::now())
            ->first();

        if (!$post) {
            $this->flash('danger', 'Agenda Tidak Ada');
            return $this->redirect($response, 'agenda');
        }
        return $this->view->render(
            $response,
            'bkol/minisite/agenda/detail.twig',
            array("post" => $post, "showSidebar" => 1,)
        );
    }
    // Detail
    public function DetailGallery(Request $request, Response $response)
    {
        $args =  $request->getAttribute('route')->getArguments();

        $post = GalleryPosts::with(
            'category'
        )
            ->where('slug', $args['slug'])
            ->where('status', '=', 1)
            ->first();
        $imgs = explode('#', $post->gallerys_photo);
        if (!$post) {
            $this->flash('danger', 'That blog post cound not be found.');
            return $this->redirect($response, 'blog');
        }
        return $this->view->render(
            $response,
            'bkol/minisite/album/detail.twig',
            array(
              "post" => $post,
              "imgs" => $imgs
            )
        );
    }
    // Agenda
    public function DetailKategori(Request $request, Response $response)
    {
        $args = $request->getAttribute('route')->getArguments();
        $slug = $args['slug'];
        $category = BlogCategories::where('slug', $slug)->first();
        
        $posts = BlogPosts::whereHas('category', function ($query) use ( $slug ) {
            $query->where('slug', '=',  $slug);
        })->get();

        if (!$posts) {
            $this->flash('danger', 'Agenda Tidak Ada');
            return $this->redirect($response, 'agenda');
        }
        return $this->view->render(
            $response,
            'bkol/minisite/berita/kategori.twig',
            array("posts" => $posts, 'category' => $category )
        );
    }
}
