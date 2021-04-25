<?php

namespace App\Controller\Admin\Shortlist;

use App\Controller\Controller;
use App\Model\Roles;
use App\Library\Utils;
use App\Model\ShortlistModel as U;
use App\Model\BkolPencariKerja;
use App\Model\JenisPendidikanModel;
use App\Model\Daerah;
use App\Model\NegaraModel;
use App\Model\MinatModel;
use App\Model\MinatUserModel;
use App\Model\GajiModel;
use App\Model\BakatModel;
use App\Model\BakatUserModel;
use App\Model\GolonganJabatanModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Shortlist extends Controller
{

  protected $parsedMinat;
  protected $parsedBakat;


  public function __construct(ContainerInterface $container)
  {
      parent::__construct($container);

      $this->parsedMinat = null;
      $this->parsedBakat = null;
  }
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function index(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('companies.view')) {
            return $check;
        }
        $posts = U::with('pencaker');
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $posts = $posts->where('perusahaan_id', $this->auth->check()->id);
        }
        return $this->view->render(
            $response,
            'bkol/dashboard-perusahaan/pencaker-favorite.twig',
            [
                "users" => $posts->get()
            ]
        );
    }
    public function delete_favorite(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('companies.delete')) {
            return $check;
        }

        $r = U::find($request->getParam('perusahaan_id'));

        if ($r->delete()) {
          $this->flash('success', 'Berhasil di batalkan');
          return $this->redirect($response, 'shortlist-admin');
        }

        $this->flash('danger'.'There was an error deleting the user.');
        return $this->redirect($response, 'shortlist-admin');
    }

}
