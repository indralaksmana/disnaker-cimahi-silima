<?php

namespace App\Controller\Admin;

use Carbon\Carbon;
use App\Library\ProdukHukum as P;
use App\Library\VideoParser as VP;
use Psr\Container\ContainerInterface;
use App\Model\ProdukHukumPosts;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ProdukHukum extends Controller
{

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $produkhukumUtils = new P($this->container);
        $this->produkhukumUtils = $produkhukumUtils;
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function produkhukum(Request $request, Response $response)
    {
        

        $posts = ProdukHukumPosts::with('author');

        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $posts = $posts->where('user_id', $this->auth->check()->id);
        }

        return $this->view->render(
            $response,
            'dashboard/produkhukum/produkhukum.twig',
            array(
                "posts" => $posts->get()
            )
        );
    }

    public function dataTables(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('agenda.view')) {
            return $check;
        }

        // Check User
        $isUser = false;
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $isUser = true;
        }
  
        $totalData = ProdukHukumPosts::count();
            
        $totalFiltered = $totalData;

        $limit = $request->getParam('length');
        $start = $request->getParam('start');
        $order = $request->getParam('columns')[$request->getParam('order')[0]['column']]['data'];
        $dir = $request->getParam('order')[0]['dir'];

        $posts = ProdukHukumPosts::select(
            'produkhukum_posts.id',
            'produkhukum_posts.judulprodukhukum',
            'produkhukum_posts.tentang',
            'produkhukum_posts.disahkan',
            'produkhukum_posts.diundangkan',
            'produkhukum_posts.upload_file',
            'produkhukum_posts.slug',
            'produkhukum_posts.created_at',
            'produkhukum_posts.publish_at',
            'produkhukum_posts.status'
        )
            ->orderBy($order, $dir)
            ->skip($start)
            ->take($limit);

        // Check User
        if ($isUser) {
            $posts = $posts->where('user_id', $this->auth->check()->id);
        }

        if (!empty($request->getParam('search')['value'])) {
            $search = $request->getParam('search')['value'];

            $posts =  $posts->where('produkhukum_posts.judulprodukhukum', 'LIKE', "%{$search}%")
                    ->orWhere('produkhukum_posts.slug', 'LIKE', "%{$search}%");

            $totalFiltered = ProdukHukumPosts::select(
                'produkhukum_posts.id',
                'produkhukum_posts.judulprodukhukum',
                'produkhukum_posts.tentang',
                'produkhukum_posts.disahkan',
                'produkhukum_posts.diundangkan',
                'produkhukum_posts.upload_file',
                'produkhukum_posts.slug',
                'produkhukum_posts.created_at',
                'produkhukum_posts.publish_at',
                'produkhukum_posts.status'
            )
                ->where('produkhukum_posts.judulprodukhukum', 'LIKE', "%{$search}%")
                ->where('produkhukum_posts.disahkan', 'LIKE', "%{$search}%")
                ->where('produkhukum_posts.diundangkan', 'LIKE', "%{$search}%")
                ->where('produkhukum_posts.tentang', 'LIKE', "%{$search}%")
                ->orWhere('produkhukum_posts.slug', 'LIKE', "%{$search}%");

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


    public function produkhukumAdd(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('agenda.create', 'dashboard', $this->config['agenda-enabled'])) {
            return $check;
        }
        
        if ($request->isPost()) {
            if ($this->produkhukumUtils->addPost()) {
                return $this->redirect($this->container->response, 'admin-produkhukum');
            }
        }

        return $this->view->render(
            $response,
            'dashboard/produkhukum/produkhukum-add.twig'
        );
    }

    // Edit Blog Post
    public function produkhukumEdit(Request $request, Response $response, $postId)
    {

        //die(var_dump($request->getAttribute('route')));
        if ($check = $this->sentinel->hasPerm('agenda.update', 'dashboard', $this->config['agenda-enabled'])) {
            return $check;
        }

        $post = ProdukHukumPosts::where('id', $postId)->first();

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'admin-produkhukum');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'admin-produkhukum');
        }

        if ($request->isPost()) {
            if ($this->produkhukumUtils->updatePost($post->id)) {
                return $this->redirect($this->container->response, 'admin-produkhukum');
            }
        }

        return $this->view->render(
            $response,
            'dashboard/produkhukum/produkhukum-edit.twig',
            array(
                "post" => $post->toArray()
            )
        );
    }

    // Publish Blog Post
    public function produkhukumPublish(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('agenda.update', 'dashboard', $this->config['agenda-enabled'])) {
            return $check;
        }

        $post = ProdukHukumPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'admin-produkhukum');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'admin-produkhukum');
        }

        if ($this->produkhukumUtils->publish()) {
            $this->flash('success', 'Post was published successfully.');
            return $this->redirect($response, 'admin-produkhukum');
        }

        $this->flash('danger', 'There was an error publishing your post.');
        return $this->redirect($response, 'admin-produkhukum');
    }

    // Unpublish Blog Post
    public function produkhukumUnpublish(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('agenda.update', 'dashboard', $this->config['agenda-enabled'])) {
            return $check;
        }

        $post = ProdukHukumPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'admin-produkhukum');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'admin-produkhukum');
        }

        if ($this->produkhukumUtils->unpublish()) {
            $this->flash('success', 'Post was unpublished successfully.');
            return $this->redirect($response, 'admin-produkhukum');
        }

        $this->flash('danger', 'There was an error unpublishing your post.');
        return $this->redirect($response, 'admin-produkhukum');
    }

    // Delete Blog Post
    public function produkhukumDelete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('agenda.delete', 'dashboard', $this->config['agenda-enabled'])) {
            return $check;
        }

        $post = ProdukHukumPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'admin-produkhukum');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to delete that post.');
            return $this->redirect($response, 'admin-produkhukum');
        }

        if ($this->produkhukumUtils->delete()) {
            $this->flash('success', 'Post was deleted successfully.');
            return $this->redirect($response, 'admin-produkhukum');
        }

        $this->flash('danger', 'There was an error deleting your post.');
        return $this->redirect($response, 'admin-produkhukum');
    }
}
