<?php


namespace App\Controller\Admin\Job;
use App\Controller\Controller;
use Carbon\Carbon;
use App\Library\VideoParser as VP;
use App\Model\JobCategories;
use App\Model\JobTags;
use App\Model\JobPosts;
use App\Model\AgendaPosts;
use App\Model\JobPostsApply;

use App\Model\JobPostsTags;
use App\Model\PendidikanPosts;
use App\Model\PendidikanNonFormalPosts;
use App\Model\PekerjaanPosts;
use App\Model\Users;
use JasonGrimes\Paginator;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class JobApply extends Controller
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function CVJobseeker(Request $request, Response $response)
    {
        $routeArgs =  $request->getAttribute('route')->getArguments();

        $checkJobseeker = Users::where('username', $routeArgs['username'])->first();

        if (!$checkJobseeker) {
            $this->flash('warning', 'Author not found.');
            return $this->redirect($response, 'blog');
        }

        $pendidikan = PendidikanPosts::where('status', 1)
            ->where('user_id', $checkJobseeker->id)
            ->where('created_at', '<', Carbon::now())
            ->with('author')
            ->orderBy('created_at', 'DESC');
        $nonformal = PendidikanNonFormalPosts::where('status', 1)
            ->where('user_id', $checkJobseeker->id)
            ->where('created_at', '<', Carbon::now())
            ->with('author')
            ->orderBy('created_at', 'DESC');
        $pekerjaan = PekerjaanPosts::where('status', 1)
            ->where('user_id', $checkJobseeker->id)
            ->where('created_at', '<', Carbon::now())
            ->with('author')
            ->orderBy('created_at', 'DESC');

        return $this->view->render(
            $response,
            'dashboard/job/cv.twig',
            array(
                "author" => $checkJobseeker,
                "pendidikan" => $pendidikan->get(),
                "pekerjaan" => $pekerjaan->get(),
                "nonformal" => $nonformal->get(),
                "authorPage" => true
            )
        );
    }

    public function apply(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('job.view', 'dashboard', $this->config['job-enabled'])) {
            return $check;
        }

        $user = $this->auth->check()->id;
        $checkJobseeker = Users::where('username', $routeArgs['username'])->first();

        $apply = JobPostsApply::with([
                    'post' => function ($query) {
                        $query->select('id', 'nama_lowongan');
                    }
                ]);

        // Get Page Number
        $page = 1;
        $routeArgs =  $request->getAttribute('route')->getArguments();
        if (isset($routeArgs['page']) && is_numeric($routeArgs['page'])) {
            $page = $routeArgs['page'];
        }
        $listpelamar = JobPostsApply::where('status', 0)
            ->where('updated_at', '<', Carbon::now())
            ->with('post')
            ->orderBy('updated_at', 'DESC');
        // List Prosses
        $listprosses = JobPostsApply::where('status', 1)
            ->where('updated_at', '<', Carbon::now())
            ->with('post')
            ->orderBy('updated_at', 'DESC');
        // LIST CANDIDATE
        $listcandidate = JobPostsApply::where('status', 2)
            ->where('updated_at', '<', Carbon::now())
            ->with('post')
            ->orderBy('updated_at', 'DESC');
        // LIST CANDIDATE
        $listketerima = JobPostsApply::where('status', 3)
            ->where('updated_at', '<', Carbon::now())
            ->with('post')
            ->orderBy('updated_at', 'DESC');

        $listout = JobPostsApply::where('status', 4)
            ->where('updated_at', '<', Carbon::now())
            ->with('post')
            ->orderBy('updated_at', 'DESC');
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {

            $listout = $listout->whereHas(
                'post',
                function ($query) use ($user) {
                    $query->where('user_id', '=', $user);
                }
            );
            $listketerima = $listketerima->whereHas(
                'post',
                function ($query) use ($user) {
                    $query->where('user_id', '=', $user);
                }
            );
            $listcandidate = $listcandidate->whereHas(
                'post',
                function ($query) use ($user) {
                    $query->where('user_id', '=', $user);
                }
            );
            $listprosses = $listprosses->whereHas(
                'post',
                function ($query) use ($user) {
                    $query->where('user_id', '=', $user);
                }
            );
            $listpelamar = $listpelamar->whereHas(
                'post',
                function ($query) use ($user) {
                    $query->where('user_id', '=', $user);
                }
            );
            $apply = $apply->whereHas(
                'post',
                function ($query) use ($user) {
                    $query->where('user_id', '=', $user);
                }
            );
        }
        return $this->view->render(
            $response,
            'dashboard/job/job-apply.twig',
            array(
                "apply" => $apply->get(),
                "listcandidate" => $listcandidate->get(),
                "listpelamar" => $listpelamar->get(),
                "listprosses" => $listprosses->get(),
                "listketerima" => $listketerima->get(),
                "listout" => $listout->get()

            )
        );
    }
    public function datatables(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('job.view')) {
            return $check;
        }

        // Check User
        $isUser = false;
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $isUser = true;
        }

        $totalData = JobPostsApply::count();

        $totalFiltered = $totalData;

        $limit = $request->getParam('length');
        $start = $request->getParam('start');
        $order = $request->getParam('columns')[$request->getParam('order')[0]['column']]['data'];
        $dir = $request->getParam('order')[0]['dir'];

        // Check User

        $apply = JobPostsApply::
            select(
                'b_job_posts_apply.created_at',
                'b_job_posts_apply.id',
                'b_job_posts.nama_lowongan as name',
                'b_job_posts_apply.coverletter',
                'b_job_posts.user_id as user_id',
                'b_job_posts_apply.status'
            )
            ->leftJoin('b_job_posts', 'b_job_posts_apply.post_id', '=', 'b_job_posts.id')
            ->orderBy($order, $dir)
            ->skip($start)
            ->where('b_job_posts_apply.status', 0)
            ->take($limit);

        if ($isUser) {
            $apply = $apply->where('b_job_posts.user_id', $this->auth->check()->id);
        }

        if (!empty($request->getParam('search')['value'])) {
            $search = $request->getParam('search')['value'];

            $apply =  $apply->where('b_job_posts_apply.coverletter', 'LIKE', "%{$search}%")
                ->orWhere('b_job_posts.nama_lowongan', 'LIKE', "%{$search}%");

            $totalFiltered = JobPostsApply::
                select(
                    'b_job_posts_apply.created_at',
                    'b_job_posts_apply.id',
                    'b_job_posts.nama_lowongan as name',
                    'b_job_posts_apply.coverletter',
                    'b_job_posts.user_id as user_id'
                )
                ->leftJoin('b_job_posts', 'b_job_posts_apply.post_id', '=', 'b_job_posts.id')
                ->where('b_job_posts_apply.coverletter', 'LIKE', "%{$search}%")
                ->orWhere('b_job_posts.nama_lowongan', 'LIKE', "%{$search}%");

            if ($isUser) {
                $totalFiltered = $totalFiltered->where('b_job_posts.user_id', $this->auth->check()->id);
            }

             $totalFiltered = $totalFiltered->count();
        }

        $jsonData = array(
            "draw"            => intval($request->getParam('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $apply->get()->toArray()
            );

        return $response->withJSON(
            $jsonData,
            200
        );
    }

    public function diprosses(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('job.view')) {
            return $check;
        }

        // Check User
        $isUser = false;
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $isUser = true;
        }

        $totalData = JobPostsApply::count();

        $totalFiltered = $totalData;

        $limit = $request->getParam('length');
        $start = $request->getParam('start');
        $order = $request->getParam('columns')[$request->getParam('order')[0]['column']]['data'];
        $dir = $request->getParam('order')[0]['dir'];

        // Check User

        $apply = JobPostsApply::
            select(
                'b_job_posts_apply.created_at',
                'b_job_posts_apply.id',
                'b_job_posts.nama_lowongan as name',
                'b_job_posts_apply.coverletter',
                'b_job_posts.user_id as user_id',
                'b_job_posts_apply.status'
            )
            ->leftJoin('b_job_posts', 'b_job_posts_apply.post_id', '=', 'b_job_posts.id')
            ->orderBy($order, $dir)
            ->skip($start)
            ->where('b_job_posts_apply.status', 1)
            ->take($limit);

        if ($isUser) {
            $apply = $apply->where('b_job_posts.user_id', $this->auth->check()->id);
        }

        if (!empty($request->getParam('search')['value'])) {
            $search = $request->getParam('search')['value'];

            $apply =  $apply->where('b_job_posts_apply.coverletter', 'LIKE', "%{$search}%")
                ->orWhere('b_job_posts.nama_lowongan', 'LIKE', "%{$search}%");

            $totalFiltered = JobPostsApply::
                select(
                    'b_job_posts_apply.created_at',
                    'b_job_posts_apply.id',
                    'b_job_posts.nama_lowongan as name',
                    'b_job_posts_apply.coverletter',
                    'b_job_posts.user_id as user_id'
                )
                ->leftJoin('b_job_posts', 'b_job_posts_apply.post_id', '=', 'b_job_posts.id')
                ->where('b_job_posts_apply.coverletter', 'LIKE', "%{$search}%")
                ->orWhere('b_job_posts.nama_lowongan', 'LIKE', "%{$search}%");

            if ($isUser) {
                $totalFiltered = $totalFiltered->where('b_job_posts.user_id', $this->auth->check()->id);
            }

             $totalFiltered = $totalFiltered->count();
        }

        $jsonData = array(
            "draw"            => intval($request->getParam('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $apply->get()->toArray()
            );

        return $response->withJSON(
            $jsonData,
            200
        );
    }

    public function applyDetails(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('job.view', 'dashboard', $this->config['job-enabled'])) {
            return $check;
        }

        $checkJobseeker = Users::where('username', $routeArgs['username'])->first();

        $apply = JobPostsApply::with('post', 'post.tags', 'post.category', 'post.author')
            ->find($request->getAttribute('route')->getArgument('comment_id'));
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $userId = $this->auth->check()->id;

            $apply = JobPostsApply::with('post', 'post.tags', 'post.category', 'post.author')
                ->where('id', $request->getAttribute('route')->getArgument('comment_id'))
                ->whereHas(
                    'post',
                    function ($query) use ($userId) {
                        $query->where('user_id', '=', $userId);
                    }
                );
        }

        $apply = $apply->first();

        if (!$apply) {
            $this->flash('danger', 'You do not have permnission to do that.');
            return $this->redirect($response, 'admin-job-apply');
        }

        return $this->view->render($response, 'dashboard/job/job-apply-detail.twig', array("apply" => $apply));
    }

    public function applyDelete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('job.view', 'dashboard', $this->config['job-enabled'])) {
            return $check;
        }

        $apply = new JobPostsApply;
        $apply = $apply->where('id', $request->getParam('apply'));
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $userId = $this->auth->check()->id;
            $apply = $apply->where('id', $request->getParam('apply'))
                ->whereHas(
                    'post',
                    function ($query) use ($userId) {
                        $query->where('user_id', $userId);
                    }
                );
        }

        $apply = $apply->first();

        if ($apply && $apply->delete()) {
            $this->flash('success', 'apply has been deleted.');
            return $this->redirect($response, 'admin-job-apply');
        }

        $this->flash('danger', 'There was an error deleting your apply.');
        return $this->redirect($response, 'admin-job-apply');
    }

    public function applyPublish(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('job.view', 'dashboard', $this->config['job-enabled'])) {
            return $check;
        }

        $apply = new JobPostsApply;
        $apply = $apply->where('id', $request->getParam('apply'));
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $userId = $this->auth->check()->id;
            $apply = $apply->whereHas(
                'post',
                function ($query) use ($userId) {
                    $query->where('user_id', '=', $userId);
                }
            );
        }

        $apply = $apply->first();

        if ($apply) {
            $apply->status = 1;
            if ($apply->save()) {
                $this->flash('success', 'Lamaran akan di prossess');
                return $this->redirect($response, 'admin-job-apply');
            }
        }

        $this->flash('danger', 'There was an error publishing your apply.');
        return $this->redirect($response, 'admin-job-apply');

    }

    public function applyCandidate(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('job.view', 'dashboard', $this->config['job-enabled'])) {
            return $check;
        }

        $apply = new JobPostsApply;
        $apply = $apply->where('id', $request->getParam('apply'));
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $userId = $this->auth->check()->id;
            $apply = $apply->whereHas(
                'post',
                function ($query) use ($userId) {
                    $query->where('user_id', '=', $userId);
                }
            );
        }

        $apply = $apply->first();

        if ($apply) {
            $apply->status = 2;
            if ($apply->save()) {
                $this->flash('success', 'apply has been published.');
                return $this->redirect($response, 'admin-job-apply');
            }
        }

        $this->flash('danger', 'There was an error publishing your apply.');
        return $this->redirect($response, 'admin-job-apply');
    }
    public function applyReceived(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('job.view', 'dashboard', $this->config['job-enabled'])) {
            return $check;
        }

        $apply = new JobPostsApply;
        $apply = $apply->where('id', $request->getParam('apply'));
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $userId = $this->auth->check()->id;
            $apply = $apply->whereHas(
                'post',
                function ($query) use ($userId) {
                    $query->where('user_id', '=', $userId);
                }
            );
        }

        $apply = $apply->first();

        if ($apply) {
            $apply->status = 3;
            if ($apply->save()) {
                $this->flash('success', 'apply has been published.');
                return $this->redirect($response, 'admin-job-apply');
            }
        }

        $this->flash('danger', 'There was an error publishing your apply.');
        return $this->redirect($response, 'admin-job-apply');
    }

    public function applyOut(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('job.view', 'dashboard', $this->config['job-enabled'])) {
            return $check;
        }

        $apply = new JobPostsApply;
        $apply = $apply->where('id', $request->getParam('apply'));
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $userId = $this->auth->check()->id;
            $apply = $apply->whereHas(
                'post',
                function ($query) use ($userId) {
                    $query->where('user_id', '=', $userId);
                }
            );
        }

        $apply = $apply->first();

        if ($apply) {
            $apply->status = 4;
            if ($apply->save()) {
                $this->flash('success', 'apply has been published.');
                return $this->redirect($response, 'admin-job-apply');
            }
        }

        $this->flash('danger', 'There was an error publishing your apply.');
        return $this->redirect($response, 'admin-job-apply');
    }

    public function candidate(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('job.view', 'dashboard', $this->config['job-enabled'])) {
            return $check;
        }

        $apply = new JobPostsApply;
        $apply = $apply->where('id', $request->getParam('apply'));
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $userId = $this->auth->check()->id;
            $apply = $apply->whereHas(
                'post',
                function ($query) use ($userId) {
                    $query->where('user_id', '=', $userId);
                }
            );
        }

        $apply = $apply->first();

        if ($apply) {
            $apply->status = 1;
            if ($apply->save()) {
                $this->flash('success', 'Pelamar Ini sudah Menjadi Kandidat');
                return $this->redirect($response, 'admin-job-apply');
            }
        }

        $this->flash('danger', 'There was an error publishing your apply.');
        return $this->redirect($response, 'admin-job-apply');
    }

    public function listcandidate(Request $request, Response $response)
    {

        if ($check = $this->sentinel->hasPerm('job.view')) {
            return $check;
        }

        // Check User
        $isUser = false;
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $isUser = true;
        }

        $totalData = JobPostsApply::count();

        $totalFiltered = $totalData;

        $limit = $request->getParam('length');
        $start = $request->getParam('start');
        $order = $request->getParam('columns')[$request->getParam('order')[0]['column']]['data'];
        $dir = $request->getParam('order')[0]['dir'];

        // Check User

        $apply = JobPostsApply::
            select(
                'b_job_posts_apply.created_at',
                'b_job_posts_apply.id',
                'b_job_posts.nama_lowongan as name',
                'b_job_posts_apply.coverletter',
                'b_job_posts.user_id as user_id',
                'b_job_posts_apply.status'
            )
            ->leftJoin('b_job_posts', 'b_job_posts_apply.post_id', '=', 'b_job_posts.id')
            ->orderBy($order, $dir)
            ->skip($start)
            ->where('b_job_posts_apply.status', 2)
            ->take($limit);

        if ($isUser) {
            $apply = $apply->where('b_job_posts.user_id', $this->auth->check()->id);
        }

        if (!empty($request->getParam('search')['value'])) {
            $search = $request->getParam('search')['value'];

            $apply =  $apply->where('b_job_posts_apply.coverletter', 'LIKE', "%{$search}%")
                ->orWhere('b_job_posts.nama_lowongan', 'LIKE', "%{$search}%");

            $totalFiltered = JobPostsApply::
                select(
                    'b_job_posts_apply.created_at',
                    'b_job_posts_apply.id',
                    'b_job_posts.nama_lowongan as name',
                    'b_job_posts_apply.coverletter',
                    'b_job_posts.user_id as user_id'
                )
                ->leftJoin('b_job_posts', 'b_job_posts_apply.post_id', '=', 'b_job_posts.id')
                ->where('b_job_posts_apply.coverletter', 'LIKE', "%{$search}%")
                ->orWhere('b_job_posts.nama_lowongan', 'LIKE', "%{$search}%");

            if ($isUser) {
                $totalFiltered = $totalFiltered->where('b_job_posts.user_id', $this->auth->check()->id);
            }

             $totalFiltered = $totalFiltered->count();
        }

        $jsonData = array(
            "draw"            => intval($request->getParam('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $apply->get()->toArray()
            );

        return $response->withJSON(
            $jsonData,
            200
        );
    }

    public function listreceived(Request $request, Response $response)
    {

        if ($check = $this->sentinel->hasPerm('job.view')) {
            return $check;
        }

        // Check User
        $isUser = false;
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $isUser = true;
        }

        $totalData = JobPostsApply::count();

        $totalFiltered = $totalData;

        $limit = $request->getParam('length');
        $start = $request->getParam('start');
        $order = $request->getParam('columns')[$request->getParam('order')[0]['column']]['data'];
        $dir = $request->getParam('order')[0]['dir'];

        // Check User

        $apply = JobPostsApply::
            select(
                'b_job_posts_apply.created_at',
                'b_job_posts_apply.id',
                'b_job_posts.nama_lowongan as name',
                'b_job_posts_apply.coverletter',
                'b_job_posts.user_id as user_id',
                'b_job_posts_apply.status'
            )
            ->leftJoin('b_job_posts', 'b_job_posts_apply.post_id', '=', 'b_job_posts.id')
            ->orderBy($order, $dir)
            ->skip($start)
            ->where('b_job_posts_apply.status', 3)
            ->take($limit);

        if ($isUser) {
            $apply = $apply->where('b_job_posts.user_id', $this->auth->check()->id);
        }

        if (!empty($request->getParam('search')['value'])) {
            $search = $request->getParam('search')['value'];

            $apply =  $apply->where('b_job_posts_apply.coverletter', 'LIKE', "%{$search}%")
                ->orWhere('b_job_posts.nama_lowongan', 'LIKE', "%{$search}%");

            $totalFiltered = JobPostsApply::
                select(
                    'b_job_posts_apply.created_at',
                    'b_job_posts_apply.id',
                    'b_job_posts.nama_lowongan as name',
                    'b_job_posts_apply.coverletter',
                    'b_job_posts.user_id as user_id'
                )
                ->leftJoin('b_job_posts', 'b_job_posts_apply.post_id', '=', 'b_job_posts.id')
                ->where('b_job_posts_apply.coverletter', 'LIKE', "%{$search}%")
                ->orWhere('b_job_posts.nama_lowongan', 'LIKE', "%{$search}%");

            if ($isUser) {
                $totalFiltered = $totalFiltered->where('b_job_posts.user_id', $this->auth->check()->id);
            }

             $totalFiltered = $totalFiltered->count();
        }

        $jsonData = array(
            "draw"            => intval($request->getParam('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $apply->get()->toArray()
            );

        return $response->withJSON(
            $jsonData,
            200
        );
    }
    public function listout(Request $request, Response $response)
    {

        if ($check = $this->sentinel->hasPerm('job.view')) {
            return $check;
        }

        // Check User
        $isUser = false;
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $isUser = true;
        }

        $totalData = JobPostsApply::count();

        $totalFiltered = $totalData;

        $limit = $request->getParam('length');
        $start = $request->getParam('start');
        $order = $request->getParam('columns')[$request->getParam('order')[0]['column']]['data'];
        $dir = $request->getParam('order')[0]['dir'];

        // Check User

        $apply = JobPostsApply::
            select(
                'b_job_posts_apply.created_at',
                'b_job_posts_apply.id',
                'b_job_posts.nama_lowongan as name',
                'b_job_posts_apply.coverletter',
                'b_job_posts.user_id as user_id',
                'b_job_posts_apply.status'
            )
            ->leftJoin('b_job_posts', 'b_job_posts_apply.post_id', '=', 'b_job_posts.id')
            ->orderBy($order, $dir)
            ->skip($start)
            ->where('b_job_posts_apply.status', 4)
            ->take($limit);

        if ($isUser) {
            $apply = $apply->where('b_job_posts.user_id', $this->auth->check()->id);
        }

        if (!empty($request->getParam('search')['value'])) {
            $search = $request->getParam('search')['value'];

            $apply =  $apply->where('b_job_posts_apply.coverletter', 'LIKE', "%{$search}%")
                ->orWhere('b_job_posts.nama_lowongan', 'LIKE', "%{$search}%");

            $totalFiltered = JobPostsApply::
                select(
                    'b_job_posts_apply.created_at',
                    'b_job_posts_apply.id',
                    'b_job_posts.nama_lowongan as name',
                    'b_job_posts_apply.coverletter',
                    'b_job_posts.user_id as user_id'
                )
                ->leftJoin('b_job_posts', 'b_job_posts_apply.post_id', '=', 'b_job_posts.id')
                ->where('b_job_posts_apply.coverletter', 'LIKE', "%{$search}%")
                ->orWhere('b_job_posts.nama_lowongan', 'LIKE', "%{$search}%");

            if ($isUser) {
                $totalFiltered = $totalFiltered->where('b_job_posts.user_id', $this->auth->check()->id);
            }

             $totalFiltered = $totalFiltered->count();
        }

        $jsonData = array(
            "draw"            => intval($request->getParam('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $apply->get()->toArray()
            );

        return $response->withJSON(
            $jsonData,
            200
        );
    }

    public function applyUnpublish(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('job.view', 'dashboard', $this->config['job-enabled'])) {
            return $check;
        }
        $apply = new JobPostsApply;
        $apply = $apply->where('id', $request->getParam('apply'));
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $userId = $this->auth->check()->id;
            $apply = $apply->whereHas(
                'post',
                function ($query) use ($userId) {
                    $query->where('user_id', '=', $userId);
                }
            );
        }

        $apply = $apply->first();

        if ($apply) {
            $apply->status = 0;
            if ($apply->save()) {
                $this->flash('success', 'apply has been unpublished.');
                return $this->redirect($response, 'admin-job-apply');
            }
        }

        $this->flash('danger', 'There was an error unpublishing your apply.');
        return $this->redirect($response, 'admin-job-apply');
    }


}
