<?php

namespace App\Controller\DashboardPerusahaan\Job;
use App\Controller\Controller;
use Carbon\Carbon;
use App\Library\Job as B;
use App\Library\VideoParser as VP;
use Psr\Container\ContainerInterface;
use App\Library\Email as E;
use App\Model\Users as U;
use App\Model\JobCategories;
use App\Model\JobTags;
use App\Model\JobPosts;
use App\Model\JobPostsApply;

use App\Model\JobPostsTags;
use App\Model\NegaraModel;
use App\Model\Daerah;
use App\Model\GolonganJabatanModel;
use App\Model\GajiModel;
use App\Model\JenisPendidikanModel;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Job extends Controller
{

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $jobUtils = new B($this->container);
        $this->jobUtils = $jobUtils;
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function job(Request $request, Response $response)
    {
        $posts = JobPosts::with('category');
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $posts = $posts->where('user_id', $this->auth->check()->id);
        }
        return $this->view->render(
            $response,
            'bkol/dashboard-perusahaan/lowongan/list.twig',
            array(
                "posts" => $posts->get()
            )
        );
    }

    public function dataTables(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('job.view')) {
            return $check;
        }

        // Check User
        $isUser = false;
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $isUser = true;
        }

        $totalData = JobPosts::count();

        $totalFiltered = $totalData;

        $limit = $request->getParam('length');
        $start = $request->getParam('start');
        $order = $request->getParam('columns')[$request->getParam('order')[0]['column']]['data'];
        $dir = $request->getParam('order')[0]['dir'];

        $posts = JobPosts::select(
            'b_job_posts.id',
            'b_job_posts.nama_lowongan',
            'b_job_posts.slug',
            'b_job_posts.created_at',
            'b_job_posts.publish_at',
            'b_job_posts.category_id',
            'b_job_posts.status',
            'b_job_categories.name as category'
        )
            ->leftJoin('b_job_categories', 'b_job_posts.category_id', '=', 'b_job_categories.id')
            ->orderBy($order, $dir)
            ->skip($start)
            ->take($limit);

        // Check User
        if ($isUser) {
            $posts = $posts->where('user_id', $this->auth->check()->id);
        }

        if (!empty($request->getParam('search')['value'])) {
            $search = $request->getParam('search')['value'];

            $posts =  $posts->where('b_job_posts.nama_lowongan', 'LIKE', "%{$search}%")
                    ->orWhere('b_job_posts.slug', 'LIKE', "%{$search}%")
                    ->orWhere('b_job_categories.name', 'LIKE', "%{$search}%");

            $totalFiltered = JobPosts::select(
                'b_job_posts.id',
                'b_job_posts.nama_lowongan',
                'b_job_posts.slug',
                'b_job_posts.created_at',
                'b_job_posts.publish_at',
                'b_job_posts.category_id',
                'b_job_posts.status',
                'b_job_categories.name as category'
            )
                ->leftJoin('b_job_categories', 'b_job_posts.category_id', '=', 'b_job_categories.id')
                ->where('b_job_posts.nama_lowongan', 'LIKE', "%{$search}%")
                ->orWhere('b_job_posts.slug', 'LIKE', "%{$search}%")
                ->orWhere('b_job_categories.name', 'LIKE', "%{$search}%");

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


    public function jobAdd(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('job.create', 'dashboard', $this->config['job-enabled'])) {
            return $check;
        }

        if ($request->isPost()) {
            if ($this->jobUtils->addPost()) {
                return $this->redirect($this->container->response, 'dashboardperusahaan-lowongan');
            }
        }
        $daerah = Daerah::where('lokasi_kabupatenkota', 0)
                            ->where('lokasi_kecamatan', 0)
                            ->where('lokasi_kelurahan', 0)
                            ->orderBy('lokasi_nama')
                            ->get();

        $jenispendidikan = JenisPendidikanModel::where('kode_jurusan', 0)
        ->orderBy('jenis_pendidikan')
        ->get();


        return $this->view->render(
            $response,
            'bkol/dashboard-perusahaan/lowongan/add.twig',
            array(
                "categories" => JobCategories::get(),
                "tags" => JobTags::get(),
                "negara" => NegaraModel::get(),
                "daerah" => $daerah,
                "jabatan" => GolonganJabatanModel::get(),
                "gaji" => GajiModel::get(),
                "jenispendidikan" => $jenispendidikan
            )
        );
    }

    // Edit Blog Post
    public function jobEdit(Request $request, Response $response, $postId)
    {
        //die(var_dump($request->getAttribute('route')));
        if ($check = $this->sentinel->hasPerm('job.update', 'dashboard', $this->config['job-enabled'])) {
            return $check;
        }
        $post = JobPosts::where('id', $postId)
        ->with(
            'category',
            'provinsi',
            'kabupatenkota',
            'jabatan',
            'gaji',
            'pendidikanterakhir',
            'jurusanpendidikan'
        )->with('tags')->first();

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'dashboardperusahaan-lowongan');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'dashboardperusahaan-lowongan');
        }

        if ($request->isPost()) {
            if ($this->jobUtils->updatePost($post->id)) {
                return $this->redirect($this->container->response, 'dashboardperusahaan-lowongan');
            }
        }

        $currentTags = $post->tags->pluck('id');

        $daerah = Daerah::where('lokasi_kabupatenkota', 0)
                            ->where('lokasi_kecamatan', 0)
                            ->where('lokasi_kelurahan', 0)
                            ->orderBy('lokasi_nama')
                            ->get();
                            $jenispendidikan = JenisPendidikanModel::where('kode_jurusan', 0)
                            ->orderBy('jenis_pendidikan')
                            ->get();

        return $this->view->render(
            $response,
            'bkol/dashboard-perusahaan/lowongan/edit.twig',
            array(
                "post" => $post->toArray(),
                "categories" => JobCategories::get(),
                "tags" => JobTags::get(),
                "negara" => NegaraModel::get(),
                "daerah" => $daerah,
                "jabatan" => GolonganJabatanModel::get(),
                "gaji" => GajiModel::get(),
                "jenispendidikan" => $jenispendidikan
            )
        );
    }

    // Publish Blog Post
    public function jobPublish(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('job.update', 'dashboard', $this->config['job-enabled'])) {
            return $check;
        }

        $post = JobPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'dashboardperusahaan-lowongan');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'dashboardperusahaan-lowongan');
        }

        if ($this->jobUtils->publish()) {
            $this->flash('success', 'Lowongan Kerja Berhasil Di Publikasikan');
            return $this->redirect($response, 'dashboardperusahaan-lowongan');
        }

        $this->flash('danger', 'There was an error publishing your post.');
        return $this->redirect($response, 'dashboardperusahaan-lowongan');
    }

    // Unpublish Blog Post
    public function jobUnpublish(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('job.update', 'dashboard', $this->config['job-enabled'])) {
            return $check;
        }

        $post = JobPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'dashboardperusahaan-lowongan');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'dashboardperusahaan-lowongan');
        }

        if ($this->jobUtils->unpublish()) {
            $this->flash('success', 'Post was unpublished successfully.');
            return $this->redirect($response, 'dashboardperusahaan-lowongan');
        }

        $this->flash('danger', 'There was an error unpublishing your post.');
        return $this->redirect($response, 'dashboardperusahaan-lowongan');
    }

    // Delete Blog Post
    public function jobDelete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('job.delete', 'dashboard', $this->config['job-enabled'])) {
            return $check;
        }

        $post = JobPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'dashboardperusahaan-lowongan');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to delete that post.');
            return $this->redirect($response, 'dashboardperusahaan-lowongan');
        }

        if ($this->jobUtils->delete()) {
            $this->flash('success', 'Lowongan Pekerjaan Berhasil Di Hapus');
            return $this->redirect($response, 'dashboardperusahaan-lowongan');
        }

        $this->flash('danger', 'There was an error deleting your post.');
        return $this->redirect($response, 'dashboardperusahaan-lowongan');
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function jobPreview(Request $request, Response $response, $slug)
    {
        if ($check = $this->sentinel->hasPerm('jobUtils', 'dashboard', $this->config['job-enabled'])) {
            return $check;
        }

        $post = JobPosts::where('slug', '=', $slug)->first();

        if (!$this->auth->check()->inRole('manager') &&
            !$this->auth->check()->inRole('admin') &&
            !$this->auth->check()->inRole('Employers') &&
            $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to preview that post.');
            return $this->redirect($response, 'dashboardperusahaan-lowongan');
        }

        $categories = JobCategories::where('status', 1)->get();

        $tags = JobTags::where('status', 1)->get();

        if ($post) {
            $this->flash('danger', 'That blog post does not exist.');
            return $this->redirect($response, 'dashboardperusahaan-lowongan');
        }

        return $this->view->render(
            $response,
            'dashboard/App/job-post.twig',
            array(
                "blogPost" => $post[0]->toArray(),
                'JobCategories' => $categories,
                'JobTags' => $tags
            )
        );
    }
}
