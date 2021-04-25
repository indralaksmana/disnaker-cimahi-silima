<?php

namespace App\Controller\Admin\Resume;
use App\Controller\Controller;
use Carbon\Carbon;
use App\Library\PendidikanNonFormal as B;
use App\Library\VideoParser as VP;
use Psr\Container\ContainerInterface;
use App\Model\PendidikanNonFormalCategories;
use App\Model\PendidikanNonFormalPosts;
use App\Model\KeterampilanModel;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class PendidikanNonFormal extends Controller
{

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $galleryUtils = new B($this->container);
        $this->galleryUtils = $galleryUtils;
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function nonformalpendidikan(Request $request, Response $response)
    {


        $posts = PendidikanNonFormalPosts::get();
        $k= KeterampilanModel::get();

        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $posts = $posts->where('user_id', $this->auth->check()->id);
        }

        return $this->view->render(
            $response,
            'bkol/dashboard/resume/pendidikan_non_formal.twig',
            array(
                "posts" => $posts,
                "k" => $k
            )
        );
    }


    public function nonformalpendidikanAdd(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('resume.create', 'dashboard', $this->config['resume-enabled'])) {
            return $check;
        }

        if ($request->isPost()) {
            if ($this->galleryUtils->addPost()) {
                return $this->redirect($this->container->response, 'resume-nonformalpendidikan');
            }
        }

        return $this->view->render(
            $response,
            'bkol/dashboard/resume/tambah_pendidikan_non_formal.twig',
            array(
            )
        );
    }

    // Edit Blog Post
    public function nonformalpendidikanEdit(Request $request, Response $response, $postId)
    {

        // //die(var_dump($request->getAttribute('route')));
        if ($check = $this->sentinel->hasPerm('resume.update', 'dashboard', $this->config['resume-enabled'])) {
            return $check;
        }

        $post = PendidikanNonFormalPosts::where('id', $postId)->first();
        $k= KeterampilanModel::get();
        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'resume-nonformalpendidikan');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'resume-nonformalpendidikan');
        }

        if ($request->isPost()) {
            if ($this->galleryUtils->updatePost($post->id)) {
                return $this->redirect($this->container->response, 'resume-nonformalpendidikan');
            }
        }

        return $this->view->render(
            $response,
            'bkol/dashboard/resume/edit_pendidikan_non_formal.twig',
            array(
                "post" => $post->toArray(),
                "k" => $k
            )
        );
    }

    // Publish Blog Post
    public function nonformalpendidikanPublish(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('resume.update', 'dashboard', $this->config['resume-enabled'])) {
            return $check;
        }

        $post = PendidikanNonFormalPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'resume-nonformalpendidikan');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'resume-nonformalpendidikan');
        }

        if ($this->galleryUtils->publish()) {
            $this->flash('success', 'Post was published successfully.');
            return $this->redirect($response, 'resume-nonformalpendidikan');
        }

        $this->flash('danger', 'There was an error publishing your post.');
        return $this->redirect($response, 'resume-nonformalpendidikan');
    }

    // Unpublish Blog Post
    public function nonformalpendidikanUnpublish(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('resume.update', 'dashboard', $this->config['resume-enabled'])) {
            return $check;
        }

        $post = PendidikanNonFormalPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'resume-nonformalpendidikan');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'resume-nonformalpendidikan');
        }

        if ($this->galleryUtils->unpublish()) {
            $this->flash('success', 'Post was unpublished successfully.');
            return $this->redirect($response, 'resume-nonformalpendidikan');
        }

        $this->flash('danger', 'There was an error unpublishing your post.');
        return $this->redirect($response, 'resume-nonformalpendidikan');
    }

    // Delete Blog Post
    public function nonformalpendidikanDelete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('resume.delete', 'dashboard', $this->config['resume-enabled'])) {
            return $check;
        }

        $post = PendidikanNonFormalPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'resume-nonformalpendidikan');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to delete that post.');
            return $this->redirect($response, 'resume-nonformalpendidikan');
        }

        if ($this->galleryUtils->delete()) {
            $this->flash('success', 'Post was deleted successfully.');
            return $this->redirect($response, 'resume-nonformalpendidikan');
        }

        $this->flash('danger', 'There was an error deleting your post.');
        return $this->redirect($response, 'resume-nonformalpendidikan');
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function nonformalpendidikanPreview(Request $request, Response $response, $slug)
    {
        if ($check = $this->sentinel->hasPerm('resume.view', 'dashboard', $this->config['resume-enabled'])) {
            return $check;
        }

        $post = PendidikanNonFormalPosts::where('slug', '=', $slug)->first();

        if (!$this->auth->check()->inRole('manager') &&
            !$this->auth->check()->inRole('admin') &&
            $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to preview that post.');
            return $this->redirect($response, 'resume-nonformalpendidikan');
        }

        $categories = PendidikanNonFormalCategories::where('status', 1)->get();


        if ($post) {
            $this->flash('danger', 'That blog post does not exist.');
            return $this->redirect($response, 'resume-nonformalpendidikan');
        }

        return $this->view->render(
            $response,
            'bkol/dashboard/App/blog-post.twig',
            array(
                "blogPost" => $post[0]->toArray(),
                'PendidikanNonFormalCategories' => $categories
            )
        );
    }
}
