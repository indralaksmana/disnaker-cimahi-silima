<?php

namespace App\Controller\Dashboard\Pelatihan;
use App\Controller\Controller;
use Carbon\Carbon;
use App\Library\Pelatihan as P;
use App\Library\VideoParser as VP;
use Psr\Container\ContainerInterface;
use App\Model\PelatihanCategories;
use App\Model\PelatihanTags;
use App\Model\PelatihanPosts;
use App\Model\PelatihanPostsTags;
use App\Model\PelatihanDaftarPeserta;
use App\Model\PelatihanLaporanAkhirModel;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Pelatihan extends Controller
{

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $pelatihanUtils = new P($this->container);
        $this->pelatihanUtils = $pelatihanUtils;
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function pelatihan(Request $request, Response $response)
    {
        $posts = PelatihanPosts::with('category');
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $posts = $posts->where('user_id', $this->auth->check()->id);
        }
        return $this->view->render(
            $response,
            'bkol/dashboard/pelatihan/list.twig',
            array(
                "posts" => $posts->get()
            )
        );
    }

    public function dataTables(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('pelatihan.view')) {
            return $check;
        }

        // Check User
        $isUser = false;
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $isUser = true;
        }

        $totalData = PelatihanPosts::count();

        $totalFiltered = $totalData;

        $limit = $request->getParam('length');
        $start = $request->getParam('start');
        $order = $request->getParam('columns')[$request->getParam('order')[0]['column']]['data'];
        $dir = $request->getParam('order')[0]['dir'];

        $posts = PelatihanPosts::select(
            'pelatihan_posts.id',
            'pelatihan_posts.title',
            'pelatihan_posts.slug',
            'pelatihan_posts.created_at',
            'pelatihan_posts.publish_at',
            'pelatihan_posts.category_id',
            'pelatihan_posts.status',
            'pelatihan_categories.name as category'
        )
            ->leftJoin('pelatihan_categories', 'pelatihan_posts.category_id', '=', 'pelatihan_categories.id')
            ->orderBy($order, $dir)
            ->skip($start)
            ->take($limit);

        // Check User
        if ($isUser) {
            $posts = $posts->where('user_id', $this->auth->check()->id);
        }

        if (!empty($request->getParam('search')['value'])) {
            $search = $request->getParam('search')['value'];

            $posts =  $posts->where('pelatihan_posts.title', 'LIKE', "%{$search}%")
                    ->orWhere('pelatihan_posts.slug', 'LIKE', "%{$search}%")
                    ->orWhere('pelatihan_categories.name', 'LIKE', "%{$search}%");

            $totalFiltered = PelatihanPosts::select(
                'pelatihan_posts.id',
                'pelatihan_posts.title',
                'pelatihan_posts.slug',
                'pelatihan_posts.created_at',
                'pelatihan_posts.publish_at',
                'pelatihan_posts.category_id',
                'pelatihan_posts.status',
                'pelatihan_categories.name as category'
            )
                ->leftJoin('pelatihan_categories', 'pelatihan_posts.category_id', '=', 'pelatihan_categories.id')
                ->where('pelatihan_posts.title', 'LIKE', "%{$search}%")
                ->orWhere('pelatihan_posts.slug', 'LIKE', "%{$search}%")
                ->orWhere('pelatihan_categories.name', 'LIKE', "%{$search}%");

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


    public function pelatihanAdd(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('pelatihan.create')) {
            return $check;
        }

        if ($request->isPost()) {
            if ($this->pelatihanUtils->addPost()) {
                //if ($this->auth->check()->inRole('pemerintah','lpk')) {
                    return $this->redirect($this->container->response, 'dashboardpemerintah-pelatihan');
                //}
            }
        }
        $titlepage = 'Buat';

        return $this->view->render(
            $response,
            'bkol/dashboard/pelatihan/add-edit.twig',
            array(
                "categories" => PelatihanCategories::get(),
                "tags" => PelatihanTags::get(),
                "TitlePage" => $titlepage,
            )
        );
    }

    // Edit Blog Post
    public function pelatihanEdit(Request $request, Response $response, $postId)
    {

        //die(var_dump($request->getAttribute('route')));
        if ($check = $this->sentinel->hasPerm('pelatihan.update')) {
            return $check;
        }

        $post = PelatihanPosts::where('id', $postId)->with('category')->with('tags')->first();

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'dashboardpemerintah-pelatihan');
        }
        $titlepage = 'Edit';
        if (!$this->auth->check()->inRole('manager','pemerintah','lpk')
            && !$this->auth->check()->inRole('admin','pemerintah','lpk')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'dashboardpemerintah-pelatihan');
        }

        if ($request->isPost()) {
            if ($this->pelatihanUtils->updatePost($post->id)) {
                return $this->redirect($this->container->response, 'dashboardpemerintah-pelatihan');
            }
        }

        $currentTags = $post->tags->pluck('id');

        return $this->view->render(
            $response,
            'bkol/dashboard/pelatihan/add-edit.twig',
            array(
                "post" => $post->toArray(),
                "categories" => PelatihanCategories::get(),
                "tags" => PelatihanTags::get(),
                "currentTags" => $currentTags,
                "TitlePage" => $titlepage,
            )
        );
    }

    public function pelatihanLaporan(Request $request, Response $response, $postId)
    {

        //die(var_dump($request->getAttribute('route')));
        if ($check = $this->sentinel->hasPerm('pelatihan.update', 'dashboard', $this->config['pelatihan-enabled'])) {
            return $check;
        }

        $post = PelatihanPosts::where('id', $postId)->with('category')->with('tags')->first();

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'dashboardpemerintah-pelatihan');
        }

        if (!$this->auth->check()->inRole('manager','pemerintah','lpk')
            && !$this->auth->check()->inRole('admin','pemerintah','lpk')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'dashboardpemerintah-pelatihan');
        }

        if ($request->isPost()) {
            if ($this->pelatihanUtils->addLaporan($post->id)) {
                return $this->redirect($this->container->response, 'dashboardpemerintah-pelatihan');
            }
        }

        $currentTags = $post->tags->pluck('id');

        return $this->view->render(
            $response,
            'dashboard/pelatihan/pelatihan-laporan-akhir.twig',
            array(
                "post" => $post->toArray(),
                "categories" => PelatihanCategories::get(),
                "tags" => PelatihanTags::get(),
                "currentTags" => $currentTags
            )
        );
    }
    public function editLaporan(Request $request, Response $response, $postId)
    {

        //die(var_dump($request->getAttribute('route')));
        if ($check = $this->sentinel->hasPerm('pelatihan.update', 'dashboard', $this->config['pelatihan-enabled'])) {
            return $check;
        }

        $post = PelatihanPosts::where('id', $postId)->with('category')->with('tags')->with('laporan')->first();
        $laporan = PelatihanLaporanAkhirModel::where('id', $postId)->first();
        if (!$laporan) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'dashboardpemerintah-pelatihan');
        }

        if (!$this->auth->check()->inRole('manager','pemerintah','lpk')
            && !$this->auth->check()->inRole('admin','pemerintah','lpk')
            && $laporan->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'dashboardpemerintah-pelatihan');
        }

        if ($request->isPost()) {
            if ($this->pelatihanUtils->editLaporan($laporan->id)) {
                return $this->redirect($this->container->response, 'dashboardpemerintah-pelatihan');
            }
        }

        $currentTags = $post->tags->pluck('id');

        return $this->view->render(
            $response,
            'dashboard/pelatihan/pelatihan-laporan-akhir-edit.twig',
            array(
                "post" => $post->toArray(),
                "laporan" => $laporan->get(),
                "categories" => PelatihanCategories::get(),
                "tags" => PelatihanTags::get(),
                "currentTags" => $currentTags
            )
        );
    }

    // Publish Blog Post
    public function pelatihanPublish(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('pelatihan.update', 'dashboard', $this->config['pelatihan-enabled'])) {
            return $check;
        }

        $post = PelatihanPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'dashboardpemerintah-pelatihan');
        }

        if (!$this->auth->check()->inRole('manager','pemerintah','lpk')
            && !$this->auth->check()->inRole('admin','pemerintah','lpk')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'dashboardpemerintah-pelatihan');
        }

        if ($this->pelatihanUtils->publish()) {
            $this->flash('success', 'Post was published successfully.');
            return $this->redirect($response, 'dashboardpemerintah-pelatihan');
        }

        $this->flash('danger', 'There was an error publishing your post.');
        return $this->redirect($response, 'dashboardpemerintah-pelatihan');
    }

    // Unpublish Blog Post
    public function pelatihanUnpublish(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('pelatihan.update', 'dashboard', $this->config['pelatihan-enabled'])) {
            return $check;
        }

        $post = PelatihanPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'dashboardpemerintah-pelatihan');
        }

        /*
        if (!$this->auth->check()->inRole('manager','pemerintah','lpk')
            && !$this->auth->check()->inRole('admin','pemerintah','lpk')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'dashboardpemerintah-pelatihan');
        }*/

        if ($this->pelatihanUtils->unpublish()) {
            $this->flash('success', 'Post was unpublished successfully.');
            return $this->redirect($response, 'dashboardpemerintah-pelatihan');
        }

        $this->flash('danger', 'There was an error unpublishing your post.');
        return $this->redirect($response, 'dashboardpemerintah-pelatihan');
    }

    // Delete Blog Post
    public function pelatihanDelete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('pelatihan.delete', 'dashboard', $this->config['pelatihan-enabled'])) {
            return $check;
        }

        $post = PelatihanPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'dashboardpemerintah-pelatihan');
        }

        if (!$this->auth->check()->inRole('manager','pemerintah','lpk')
            && !$this->auth->check()->inRole('admin','pemerintah','lpk')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to delete that post.');
            return $this->redirect($response, 'dashboardpemerintah-pelatihan');
        }

        if ($this->pelatihanUtils->delete()) {
            $this->flash('success', 'Post was deleted successfully.');
            return $this->redirect($response, 'dashboardpemerintah-pelatihan');
        }

        $this->flash('danger', 'There was an error deleting your post.');
        return $this->redirect($response, 'dashboardpemerintah-pelatihan');
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function pelatihanPreview(Request $request, Response $response, $slug)
    {
        if ($check = $this->sentinel->hasPerm('pelatihanUtils', 'dashboard', $this->config['pelatihan-enabled'])) {
            return $check;
        }

        $post = PelatihanPosts::where('slug', '=', $slug)->first();

        if (!$this->auth->check()->inRole('manager','pemerintah','lpk') &&
            !$this->auth->check()->inRole('admin','pemerintah','lpk') &&
            !$this->auth->check()->inRole('Employers','pemerintah','lpk') &&
            $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to preview that post.');
            return $this->redirect($response, 'dashboardpemerintah-pelatihan');
        }

        $categories = PelatihanCategories::where('status', 1)->get();

        $tags = PelatihanTags::where('status', 1)->get();

        if ($post) {
            $this->flash('danger', 'That blog post does not exist.');
            return $this->redirect($response, 'dashboardpemerintah-pelatihan');
        }

        return $this->view->render(
            $response,
            'dashboard/App/pelatihan-post.twig',
            array(
                "PelatihanPost" => $post[0]->toArray(),
                'PelatihanCategories' => $categories,
                'PelatihanTags' => $tags
            )
        );
    }



    public function PesertaPelatihan(Request $request, Response $response, $postId)
    {

        //die(var_dump($request->getAttribute('route')));
        if ($check = $this->sentinel->hasPerm('pelatihan.update', 'dashboard', $this->config['pelatihan-enabled'])) {
            return $check;
        }

        $post = PelatihanPosts::where('id', $postId)->with('category')->with('tags')->first();
        $pesertapelatihan = PelatihanDaftarPeserta::where('post_id' , $post->id)->where('status',0)
        ->with('userpelatihan')->get();

        $pesertapelatihanlengkap = PelatihanDaftarPeserta::where('post_id' , $post->id)->where('status',1)
        ->with('userpelatihan')->get();

        $pesertapelatihanterpilih = PelatihanDaftarPeserta::where('post_id' , $post->id)->where('status',2)
        ->with('userpelatihan')->get();

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'dashboardpemerintah-pelatihan');
        }

        if (!$this->auth->check()->inRole('manager','pemerintah','lpk')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'dashboardpemerintah-pelatihan');
        }

        if ($request->isPost()) {
            if ($this->pelatihanUtils->updatePost($post->id)) {
                return $this->redirect($this->container->response, 'dashboardpemerintah-pelatihan');
            }
        }

        $currentTags = $post->tags->pluck('id');

        return $this->view->render(
            $response,
            //'dashboard/pelatihan/peserta-pelatihan.twig',
            'bkol/dashboard/pelatihan/peserta-pelatihan.twig',
            array(
                "post" => $post->toArray(),
                "categories" => PelatihanCategories::get(),
                "tags" => PelatihanTags::get(),
                "peserta" => $pesertapelatihan,
                "currentTags" => $currentTags,
                'lengkap' => $pesertapelatihanlengkap,
                'terpilih' => $pesertapelatihanterpilih
            )
        );
    }

    // Publish Blog  Post
    public function pelatihanSyaratLengkap(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('pelatihan.update', 'dashboard', $this->config['pelatihan-enabled'])) {
            return $check;
        }

        $post = PelatihanDaftarPeserta::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'dashboardpemerintah-pelatihan');
        }
/*
        //TODO CHECK
        if (!$this->auth->check()->inRole('pemerintah','lpk')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'dashboardpemerintah-pelatihan');
        }
*/
        if ($this->pelatihanUtils->syaratlengkap()) {
            $this->flash('success', 'Post was published successfully.');
            return $this->redirect($response, 'dashboardpemerintah-pelatihan');
        }

        $this->flash('danger', 'There was an error publishing your post.');
        return $this->redirect($response, 'dashboardpemerintah-pelatihan');
    }

}
