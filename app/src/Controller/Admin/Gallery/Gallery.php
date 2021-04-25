<?php

namespace App\Controller\Admin\Gallery;
use App\Controller\Controller;
use Carbon\Carbon;
use App\Library\Gallery as B;
use App\Library\VideoParser as VP;
use Psr\Container\ContainerInterface;
use App\Model\GalleryCategories;
use App\Model\GalleryPosts;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Gallery extends Controller
{

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $galleryUtils = new B($this->container);
        $this->galleryUtils = $galleryUtils;
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function gallery(Request $request, Response $response)
    {


        $posts = GalleryPosts::with('category');

        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $posts = $posts->where('user_id', $this->auth->check()->id);
        }

        return $this->view->render(
            $response,
            'dashboard/gallery/gallery.twig',
            array(
                "categories" => GalleryCategories::get(),
                "posts" => $posts->get()
            )
        );
    }

    public function dataTables(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('gallery.view')) {
            return $check;
        }

        // Check User
        $isUser = false;
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $isUser = true;
        }

        $totalData = GalleryPosts::count();

        $totalFiltered = $totalData;

        $limit = $request->getParam('length');
        $start = $request->getParam('start');
        $order = $request->getParam('columns')[$request->getParam('order')[0]['column']]['data'];
        $dir = $request->getParam('order')[0]['dir'];

        $posts = GalleryPosts::select(
            'd_gallery_posts.id',
            'd_gallery_posts.title',
            'd_gallery_posts.slug',
            'd_gallery_posts.created_at',
            'd_gallery_posts.publish_at',
            'd_gallery_posts.category_id',
            'd_gallery_posts.status',
            'd_gallery_categories.name as category'
        )
            ->leftJoin('d_gallery_categories', 'd_gallery_posts.category_id', '=', 'd_gallery_categories.id')
            ->orderBy($order, $dir)
            ->skip($start)
            ->take($limit);

        // Check User
        if ($isUser) {
            $posts = $posts->where('user_id', $this->auth->check()->id);
        }

        if (!empty($request->getParam('search')['value'])) {
            $search = $request->getParam('search')['value'];

            $posts =  $posts->where('d_gallery_posts.title', 'LIKE', "%{$search}%")
                    ->orWhere('d_gallery_posts.slug', 'LIKE', "%{$search}%")
                    ->orWhere('d_gallery_categories.name', 'LIKE', "%{$search}%");

            $totalFiltered = GalleryPosts::select(
                'd_gallery_posts.id',
                'd_gallery_posts.title',
                'd_gallery_posts.slug',
                'd_gallery_posts.created_at',
                'd_gallery_posts.publish_at',
                'd_gallery_posts.category_id',
                'd_gallery_posts.status',
                'd_gallery_categories.name as category'
            )
                ->leftJoin('d_gallery_categories', 'd_gallery_posts.category_id', '=', 'd_gallery_categories.id')
                ->where('d_gallery_posts.title', 'LIKE', "%{$search}%")
                ->orWhere('d_gallery_posts.slug', 'LIKE', "%{$search}%")
                ->orWhere('d_gallery_categories.name', 'LIKE', "%{$search}%");

            if ($isUser) {
                $totalFiltered = $totalFiltered->where('user_id', $this->auth->check()->id);
            }

             $totalFiltered = $totalFiltered->count();
        }

        $jsonData = array(
            "draw"            => intval($request->getParam('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $posts->get()->toArray()
            );

        return $response->withJSON(
            $jsonData,
            200
        );
    }


    public function galleryAdd(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('gallery.create', 'dashboard', $this->config['gallery-enabled'])) {
            return $check;
        }

        if ($request->isPost()) {
            if ($this->galleryUtils->addPost()) {
                return $this->redirect($this->container->response, 'admin-gallery');
            }
        }

        return $this->view->render(
            $response,
            'dashboard/gallery/gallery-add.twig',
            array(
                "categories" => GalleryCategories::get()
            )
        );
    }

    // Edit Blog Post
    public function galleryEdit(Request $request, Response $response, $postId)
    {

        //die(var_dump($request->getAttribute('route')));
        if ($check = $this->sentinel->hasPerm('gallery.update', 'dashboard', $this->config['gallery-enabled'])) {
            return $check;
        }

        $post = GalleryPosts::where('id', $postId)->with('category')->first();

        $imgs = explode('#', $post->gallerys_photo);
        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'admin-gallery');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'admin-gallery');
        }

        if ($request->isPost()) {
            if ($this->galleryUtils->updatePost($post->id)) {
                return $this->redirect($this->container->response, 'admin-gallery');
            }
        }

        return $this->view->render(
            $response,
            'dashboard/gallery/gallery-edit.twig',
            array(
                "post" => $post->toArray(),
                "categories" => GalleryCategories::get(),
                "imgs" => $imgs
            )
        );
    }

    // Publish Blog Post
    public function galleryPublish(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('gallery.update', 'dashboard', $this->config['gallery-enabled'])) {
            return $check;
        }

        $post = GalleryPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'admin-gallery');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'admin-gallery');
        }

        if ($this->galleryUtils->publish()) {
            $this->flash('success', 'Post was published successfully.');
            return $this->redirect($response, 'admin-gallery');
        }

        $this->flash('danger', 'There was an error publishing your post.');
        return $this->redirect($response, 'admin-gallery');
    }

    // Unpublish Blog Post
    public function galleryUnpublish(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('gallery.update', 'dashboard', $this->config['gallery-enabled'])) {
            return $check;
        }

        $post = GalleryPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'admin-gallery');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'admin-gallery');
        }

        if ($this->galleryUtils->unpublish()) {
            $this->flash('success', 'Post was unpublished successfully.');
            return $this->redirect($response, 'admin-gallery');
        }

        $this->flash('danger', 'There was an error unpublishing your post.');
        return $this->redirect($response, 'admin-gallery');
    }

    // Delete Blog Post
    public function galleryDelete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('gallery.delete', 'dashboard', $this->config['gallery-enabled'])) {
            return $check;
        }

        $post = GalleryPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'admin-gallery');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to delete that post.');
            return $this->redirect($response, 'admin-gallery');
        }

        if ($this->galleryUtils->delete()) {
            $this->flash('success', 'Galleri Foto Berhasil Di Hapus');
            return $this->redirect($response, 'admin-gallery');
        }

        $this->flash('danger', 'Galleri Foto Gagal Di Hapus');
        return $this->redirect($response, 'admin-gallery');
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function galleryPreview(Request $request, Response $response, $slug)
    {
        if ($check = $this->sentinel->hasPerm('gallery.view', 'dashboard', $this->config['gallery-enabled'])) {
            return $check;
        }

        $post = GalleryPosts::where('slug', '=', $slug)->first();

        if (!$this->auth->check()->inRole('manager') &&
            !$this->auth->check()->inRole('admin') &&
            $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to preview that post.');
            return $this->redirect($response, 'admin-gallery');
        }

        $categories = GalleryCategories::where('status', 1)->get();


        if ($post) {
            $this->flash('danger', 'That blog post does not exist.');
            return $this->redirect($response, 'admin-gallery');
        }

        return $this->view->render(
            $response,
            'dashboard/App/blog-post.twig',
            array(
                "blogPost" => $post[0]->toArray(),
                'GalleryCategories' => $categories
            )
        );
    }
}
