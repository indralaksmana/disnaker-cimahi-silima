<?php

namespace App\Controller\DashboardLpk;
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
class DashboardLpk extends Controller
{


    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function dashboard(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('lpk.view')) {
            return $check;
        }
        return $this->view->render(
            $response,
            'bkol/dashboard-lpk/dashboard.twig'
        );
    }

    public function GantiPassword(Request $request, Response $response)
    {
        return $this->view->render(
            $response,
            'dashboard/ganti-password.twig'
        );
    }


}
