<?php

namespace App\Controller\Admin\Resume;
use App\Controller\Controller;
use Carbon\Carbon;
use App\Library\Pendidikan as B;
use Psr\Container\ContainerInterface;
use App\Model\PendidikanPosts;
use App\Model\JenisPendidikanModel;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Pendidikan extends Controller
{

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $galleryUtils = new B($this->container);
        $this->galleryUtils = $galleryUtils;
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function pendidikan(Request $request, Response $response)
    {


        $posts = PendidikanPosts::get();
        $jenispendidikan = JenisPendidikanModel::where('kode_jurusan', 0)
        ->orderBy('order', 'asc')
        ->get();

        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $posts = $posts->where('user_id', $this->auth->check()->id);
        }

        return $this->view->render(
            $response,
            'bkol/dashboard/resume/pendidikan.twig',
            array(
                "posts" => $posts,
                "jenispendidikan" => $jenispendidikan 
            )
        );
    }

    public function pendidikanAdd(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('resume.create', 'dashboard', $this->config['resume-enabled'])) {
            return $check;
        }

        if ($request->isPost()) {
            if ($this->galleryUtils->addPost()) {
                return $this->redirect($this->container->response, 'resume-pendidikan');
            }
        }

        return $this->view->render(
            $response,
            'bkol/dashboard/resume/tambah_pendidikan.twig',
            array(
            )
        );
    }

    // Edit Blog Post
    public function pendidikanEdit(Request $request, Response $response, $postId)
    {

        //die(var_dump($request->getAttribute('route')));
        if ($check = $this->sentinel->hasPerm('resume.update', 'dashboard', $this->config['resume-enabled'])) {
            return $check;
        }

        $post = PendidikanPosts::where('id', $postId)->first();
        $jenispendidikan = JenisPendidikanModel::where('kode_jurusan', 0)
        ->orderBy('jenis_pendidikan')
        ->get();

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'resume-pendidikan');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'resume-pendidikan');
        }

        if ($request->isPost()) {
            if ($this->galleryUtils->updatePost($post->id)) {
                return $this->redirect($this->container->response, 'resume-pendidikan');
            }
        }

        return $this->view->render(
            $response,
            'bkol/dashboard/resume/edit_pendidikan.twig',
            array(
                "post" => $post->toArray(),
                "jenispendidikan" => $jenispendidikan 
            )
        );
    }

    // Delete Blog Post
    public function pendidikanDelete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('resume.delete', 'dashboard', $this->config['resume-enabled'])) {
            return $check;
        }

        $post = PendidikanPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'resume-pendidikan');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to delete that post.');
            return $this->redirect($response, 'resume-pendidikan');
        }

        if ($this->galleryUtils->delete()) {
            $this->flash('success', 'Post was deleted successfully.');
            return $this->redirect($response, 'resume-pendidikan');
        }

        $this->flash('danger', 'There was an error deleting your post.');
        return $this->redirect($response, 'resume-pendidikan');
    }
}
