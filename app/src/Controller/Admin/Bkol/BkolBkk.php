<?php


namespace App\Controller\Admin\Bkol;
use App\Controller\Controller;
use Psr\Container\ContainerInterface;
use Carbon\Carbon;
use App\Model\BkkModel as BKK;
use App\Model\Users as U;
use App\Model\Daerah;
use App\Library\Utils;
use App\Library\Bkk as UT;
use App\Model\ActivationModel;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class BkolBkk extends Controller
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $utils = new UT($this->container);
        $this->utils = $utils;
    }

    public function index(Request $request, Response $response)
    {

        $users = U::with('roles')
            ->WhereHas('roles', function($q){$q->whereIn('name', ['bkk']);})
            ->get();

        return $this->view->render(
            $response,
            'dashboard/admin/bkol/bkolbkk/index.twig',
            array(
                "users" => $users
            )
        );
    }
    // Add New
    public function add(Request $request, Response $response)
    {
        // if ($check = $this->sentinel->hasPerm('.create', 'dashboard', $this->config['-enabled'])) {
        //     return $check;
        // }

        if ($request->isPost()) {
            if ( $this->utils->addPost() ) {
                return $this->redirect($response, 'admin-bkol-bkk');
            }
        }

        $bkk = BKK::select('id', 'nama_bkk')->get();
        return $this->view->render(
            $response,
            'dashboard/admin/bkol/bkolbkk/add.twig',
            ['form_state' => 'add', 'bkk' => $bkk]
        );
    }


    // Delete
    public function delete(Request $request, Response $response)
    {
        $pt = U::find($request->getParam('bkk_id'));

        if (!$pt) {
            $this->flash('danger', 'Tidak ada data');
            return $this->redirect($response, 'admin-bkol-bkk');
        }

        if ($pt->delete()) {
            $this->flash('success', 'Data Berhasil di Hapus');
            return $this->redirect($response, 'admin-bkol-bkk');
        }

        $this->flash('danger', 'There was a problem removing the data.');
        return $this->redirect($response, 'admin-bkol-bkk');
    }

    // Edit
    public function edit(Request $request, Response $response, $bkolptId)
    {
        //die(var_dump($request->getAttribute('route')));
        // if ($check = $this->sentinel->hasPerm('blog.update', 'dashboard', $this->config['blog-enabled'])) {
        //     return $check;
        // }

        $user = U::find( $bkolptId );

        if (!$user) {
            $this->flash('danger', 'That user does not exist.');
            return $this->redirect($response, 'admin-bkol-bkk');
        }

        if ($request->isPost()) {
            if ($this->utils->updatePost($user->id)) {
                return $this->redirect($this->container->response, 'admin-bkol-bkk');
            }
        }

        $bkk = BKK::select('id', 'nama_bkk')->get();
        return $this->view->render(
            $response,
            'dashboard/admin/bkol/bkolbkk/edit.twig',
             ['form_state' => 'edit', 'user' => $user->toArray(), 'bkk' => $bkk]
        );
    }

    public function aktifkan(Request $request, Response $response)
    {
        $user = ActivationModel::where('user_id', '=', $request->getParam('user_id'))->first();
        if ($user) {
            $user->completed = 1;

            if ($user->save()) {
                $this->flash('success', 'Akun Dengan Username ' .$user->user->username. ' Sudah Di Aktipkan');
            }
        }
        return $this->redirect($response, 'admin-bkol-bkk');
    }
    public function nonaktifkan(Request $request, Response $response)
    {
        $user = ActivationModel::where('user_id', '=', $request->getParam('user_id'))->first();
        if ($user) {
            $user->completed = 0;
            if ($user->save()) {
                $this->flash('success', 'Akun Dengan Username ' .$user->user->username. ' Sudah Di Non Aktipkan');
            }
        }
        return $this->redirect($response, 'admin-bkol-bkk');
    }
}
