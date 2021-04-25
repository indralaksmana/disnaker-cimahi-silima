<?php


namespace App\Controller\Admin;

use Carbon\Carbon;
use App\Library\VideoParser as VP;
use App\Model\JobCategories;
use App\Model\JobTags;
use App\Model\JobPosts;
use App\Model\JobPostsApply;

use App\Model\JobPostsTags;
use App\Model\PendidikanPosts;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Proposal extends Controller
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function proposal(Request $request, Response $response)
    {
        $user = $this->auth->check()->id;

        $apply = JobPostsApply::with([
                    'post' => function ($query) {
                        $query->select('id', 'nama_lowongan');
                    }
                ]);

        if (!$this->auth->check()->inRole('jobseeker') && !$this->auth->check()->inRole('admin')) {
            $apply = $apply->whereHas(
                'post',
                function ($query) use ($user) {
                    $query->where('user_id', '=', $user);
                }
            );
        }

        $listproposal = JobPostsApply::where('updated_at', '<', Carbon::now())
            ->with('post','post.authorCompanies')
            ->orderBy('updated_at', 'DESC');

        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $listproposal = $listproposal->where('user_id', $this->auth->check()->id);
        }
        $pendidikan = PendidikanPosts::with('category');

        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $pendidikan = $pendidikan->where('user_id', $this->auth->check()->id);
        }

        return $this->view->render(
            $response,
            'bkol/dashboard/list-proposal.twig',
            array(
                "listproposal" => $listproposal->get()
            )
        );
    }



    public function proposalDetails(Request $request, Response $response)
    {

        $apply = JobPostsApply::with('replies', 'post', 'post.tags', 'post.category', 'post.author')
            ->find($request->getAttribute('route')->getArgument('comment_id'));
        if (!$this->auth->check()->inRole('jobseeker') && !$this->auth->check()->inRole('admin')) {
            $userId = $this->auth->check()->id;

            $apply = JobPostsApply::with('replies', 'post', 'post.tags', 'post.category', 'post.author')
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
            return $this->redirect($response, 'admin-job-proposal');
        }

        return $this->view->render($response, 'dashboard/proposal/detail-proposal.twig', array("apply" => $apply));
    }
}
