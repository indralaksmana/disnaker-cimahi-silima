<?php

namespace App\Controller\DashboardBkk;
use App\Controller\Controller;
use Carbon\Carbon;
use App\Model\Users;
use App\Model\Daerah;
use App\Model\UsersProfile;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use Slim\Views\PhpRenderer;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class DashboardBkk extends Controller
{


    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function dashboard(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('bkk.view')) {
            return $check;
        }
        return $this->view->render(
            $response,
            'bkol/dashboard-bkk/dashboard.twig'
        );
    }
}
