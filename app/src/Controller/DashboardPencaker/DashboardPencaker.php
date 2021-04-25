<?php

namespace App\Controller\DashboardPencaker;
use App\Controller\Controller;
use Carbon\Carbon;
use App\Model\ContactRequests;
use App\Model\Oauth2Providers;
use App\Model\JenisUsahaPost as JU;
use App\Model\Users;
use App\Model\JobPosts;
use App\Model\JobPostsApply;
use App\Model\UsersProfile;
use App\Model\BkolPerusahaan;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use Slim\Views\PhpRenderer;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class DashboardPencaker extends Controller
{

    public function dashboardpencaker(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('jobseeker.view')) {
            return $check;
        }

        if ($this->auth->check()) {
          $minat_id = $this->auth->check()->datapencarikerja->jenis_jabatan_yang_diminati_id;
          $user_id = $this->auth->check()->id;
        } else {
          $minat_id = null;
          $user = null;
        }

        $jobs = JobPosts::where('status', 1)->count();
        $jobsminat = JobPosts::where('status', 1)
        ->where('jabatan_id', $minat_id);
        $lamaranterkirim = JobPostsApply::where('user_id', $user_id)->count();
        return $this->view->render(
            $response,
            'bkol/dashboard/dashboard.twig',
            array(
              'jobs' => $jobs,
              'jobsminat' => $jobsminat->count(),
              'listjobsminat' => $jobsminat->get(),
              'lamaranterkirim' => $lamaranterkirim
            )
        );
    }

}
