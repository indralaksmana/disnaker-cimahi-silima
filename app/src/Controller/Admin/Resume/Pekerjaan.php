<?php

namespace App\Controller\Admin\Resume;
use App\Controller\Controller;
use Carbon\Carbon;
use App\Library\Pekerjaan as B;
use App\Library\VideoParser as VP;
use Psr\Container\ContainerInterface;
use App\Model\PekerjaanPosts;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Pekerjaan extends Controller
{

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $galleryUtils = new B($this->container);
        $this->galleryUtils = $galleryUtils;
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function pekerjaan(Request $request, Response $response)
    {


        $posts = PekerjaanPosts::get();

        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $posts = $posts->where('user_id', $this->auth->check()->id);
        }

        return $this->view->render(
            $response,
            'bkol/dashboard/resume/pekerjaan.twig',
            array(
                "posts" => $posts
            )
        );
    }



    public function pekerjaanAdd(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('resume.create', 'dashboard', $this->config['resume-enabled'])) {
            return $check;
        }

        if ($request->isPost()) {
            if ($this->galleryUtils->addPost()) {
                return $this->redirect($this->container->response, 'resume-pekerjaan');
            }
        }

        return $this->view->render(
            $response,
            'bkol/dashboard/resume/tambah_pekerjaan.twig',
            array(
                "categories" => PekerjaanCategories::get()
            )
        );
    }

    // Edit Blog Post
    public function pekerjaanEdit(Request $request, Response $response, $postId)
    {

        //die(var_dump($request->getAttribute('route')));
        if ($check = $this->sentinel->hasPerm('resume.update', 'dashboard', $this->config['resume-enabled'])) {
            return $check;
        }

        $post = PekerjaanPosts::where('id', $postId)->first();

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'resume-pekerjaan');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'resume-pekerjaan');
        }

        if ($request->isPost()) {
            if ($this->galleryUtils->updatePost($post->id)) {
                return $this->redirect($this->container->response, 'resume-pekerjaan');
            }
        }

        return $this->view->render(
            $response,
            'bkol/dashboard/resume/edit_pekerjaan.twig',
            array(
                "post" => $post->toArray()
            )
        );
    }


    // Delete Blog Post
    public function pekerjaanDelete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('resume.delete', 'dashboard', $this->config['resume-enabled'])) {
            return $check;
        }

        $post = PekerjaanPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'resume-pekerjaan');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to delete that post.');
            return $this->redirect($response, 'resume-pekerjaan');
        }

        if ($this->galleryUtils->delete()) {
            $this->flash('success', 'Post was deleted successfully.');
            return $this->redirect($response, 'resume-pekerjaan');
        }

        $this->flash('danger', 'There was an error deleting your post.');
        return $this->redirect($response, 'resume-pekerjaan');
    }


}
