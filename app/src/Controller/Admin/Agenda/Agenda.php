<?php

namespace App\Controller\Admin\Agenda;
use App\Controller\Controller;
use Carbon\Carbon;
use App\Library\Agenda as A;
use App\Library\VideoParser as VP;
use Psr\Container\ContainerInterface;
use App\Model\AgendaPosts;
use App\Model\AgendaCategories;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Agenda extends Controller
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $agendaUtils = new A($this->container);
        $this->agendaUtils = $agendaUtils;
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function agenda(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('agenda.view')) {
            return $check;
        }
        $posts = AgendaPosts::with('category');
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin') && !$this->auth->check()->inRole('adminweb')) {
            $posts = $posts->where('user_id', $this->auth->check()->id);
        }
        return $this->view->render(
            $response,
            'dashboard/agenda/agenda.twig',
            array(
                "categories" => AgendaCategories::get(),
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

        $totalData = AgendaPosts::count();

        $totalFiltered = $totalData;

        $limit = $request->getParam('length');
        $start = $request->getParam('start');
        $order = $request->getParam('columns')[$request->getParam('order')[0]['column']]['data'];
        $dir = $request->getParam('order')[0]['dir'];

        $posts = AgendaPosts::select(
            'd_agenda_posts.id',
            'd_agenda_posts.agendatitle',
            'd_agenda_posts.slug',
            'd_agenda_posts.created_at',
            'd_agenda_posts.publish_at',
            'd_agenda_posts.category_id',
            'd_agenda_posts.status',
            'd_agenda_categories.name as category'
        )
            ->leftJoin('d_agenda_categories', 'd_agenda_posts.category_id', '=', 'd_agenda_categories.id')
            ->orderBy($order, $dir)
            ->skip($start)
            ->take($limit);

        // Check User
        if ($isUser) {
            $posts = $posts->where('user_id', $this->auth->check()->id);
        }

        if (!empty($request->getParam('search')['value'])) {
            $search = $request->getParam('search')['value'];

            $posts =  $posts->where('d_agenda_posts.agendatitle', 'LIKE', "%{$search}%")
                    ->orWhere('d_agenda_posts.slug', 'LIKE', "%{$search}%")
                    ->orWhere('d_agenda_categories.name', 'LIKE', "%{$search}%");

            $totalFiltered = AgendaPosts::select(
                'd_agenda_posts.id',
                'd_agenda_posts.agendatitle',
                'd_agenda_posts.slug',
                'd_agenda_posts.created_at',
                'd_agenda_posts.publish_at',
                'd_agenda_posts.category_id',
                'd_agenda_posts.status',
                'd_agenda_categories.name as category'
            )
                ->leftJoin('d_agenda_categories', 'd_agenda_posts.category_id', '=', 'd_agenda_categories.id')
                ->where('d_agenda_posts.agendatitle', 'LIKE', "%{$search}%")
                ->orWhere('d_agenda_posts.slug', 'LIKE', "%{$search}%")
                ->orWhere('d_agenda_categories.name', 'LIKE', "%{$search}%");

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


    public function agendaAdd(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('agenda.create', 'dashboard', $this->config['agenda-enabled'])) {
            return $check;
        }

        if ($request->isPost()) {
            if ($this->agendaUtils->addPost()) {
                return $this->redirect($this->container->response, 'admin-agenda');
            }
        }

        return $this->view->render(
            $response,
            'dashboard/agenda/agenda-add.twig',
            array(
                "categories" => AgendaCategories::get()
            )
        );
    }

    // Edit Blog Post
    public function agendaEdit(Request $request, Response $response, $postId)
    {

        //die(var_dump($request->getAttribute('route')));
        if ($check = $this->sentinel->hasPerm('agenda.update', 'dashboard', $this->config['agenda-enabled'])) {
            return $check;
        }

        $post = AgendaPosts::where('id', $postId)->with('category')->first();

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'admin-agenda');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'admin-agenda');
        }

        if ($request->isPost()) {
            if ($this->agendaUtils->updatePost($post->id)) {
                return $this->redirect($this->container->response, 'admin-agenda');
            }
        }

        return $this->view->render(
            $response,
            'dashboard/agenda/agenda-edit.twig',
            array(
                "post" => $post->toArray(),
                "categories" => AgendaCategories::get()
            )
        );
    }

    // Publish Blog Post
    public function agendaPublish(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('agenda.update', 'dashboard', $this->config['agenda-enabled'])) {
            return $check;
        }

        $post = AgendaPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'admin-agenda');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'admin-agenda');
        }

        if ($this->agendaUtils->publish()) {
            $this->flash('success', 'Post was published successfully.');
            return $this->redirect($response, 'admin-agenda');
        }

        $this->flash('danger', 'There was an error publishing your post.');
        return $this->redirect($response, 'admin-agenda');
    }

    // Unpublish Blog Post
    public function agendaUnpublish(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('agenda.update', 'dashboard', $this->config['agenda-enabled'])) {
            return $check;
        }

        $post = AgendaPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'admin-agenda');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'admin-agenda');
        }

        if ($this->agendaUtils->unpublish()) {
            $this->flash('success', 'Post was unpublished successfully.');
            return $this->redirect($response, 'admin-agenda');
        }

        $this->flash('danger', 'There was an error unpublishing your post.');
        return $this->redirect($response, 'admin-agenda');
    }

    // Delete Blog Post
    public function agendaDelete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('agenda.delete', 'dashboard', $this->config['agenda-enabled'])) {
            return $check;
        }

        $post = AgendaPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'admin-agenda');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to delete that post.');
            return $this->redirect($response, 'admin-agenda');
        }

        if ($this->agendaUtils->delete()) {
            $this->flash('success', 'Post was deleted successfully.');
            return $this->redirect($response, 'admin-agenda');
        }

        $this->flash('danger', 'There was an error deleting your post.');
        return $this->redirect($response, 'admin-agenda');
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function agendaPreview(Request $request, Response $response, $slug)
    {
        if ($check = $this->sentinel->hasPerm('agendaUtils', 'dashboard', $this->config['agenda-enabled'])) {
            return $check;
        }

        $post = AgendaPosts::where('slug', '=', $slug)->first();

        if (!$this->auth->check()->inRole('manager') &&
            !$this->auth->check()->inRole('admin') &&
            !$this->auth->check()->inRole('Employers') &&
            $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to preview that post.');
            return $this->redirect($response, 'admin-agenda');
        }

        $categories = AgendaCategories::where('status', 1)->get();

        if ($post) {
            $this->flash('danger', 'That blog post does not exist.');
            return $this->redirect($response, 'admin-agenda');
        }

        return $this->view->render(
            $response,
            'dashboard/App/agenda-post.twig',
            array(
                "blogPost" => $post[0]->toArray(),
                'AgendaCategories' => $categories
            )
        );
    }
}
