<?php


namespace App\Controller\Admin\Bkol;
use App\Controller\Controller;
use Psr\Container\ContainerInterface;
use Carbon\Carbon;
use App\Model\LpkModel as LPK;
use App\Model\Users as U;
use App\Model\Daerah;
use App\Library\Utils;
use App\Library\Lpk as UT;
use App\Model\ActivationModel;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class BkolLpk extends Controller
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
            ->WhereHas('roles', function($q){$q->whereIn('name', ['lpk']);})
            ->get();

        return $this->view->render(
            $response,
            'dashboard/admin/bkol/bkollpk/index.twig',
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
                return $this->redirect($response, 'admin-bkol-lpk');
            }
        }

        $lpk = LPK::select('id', 'nama_lpk')->get();
        return $this->view->render(
            $response,
            'dashboard/admin/bkol/bkollpk/add.twig',
            ['form_state' => 'add', 'lpk' => $lpk]
        );
    }


    // Delete
    public function delete(Request $request, Response $response)
    {
        $pt = LPK::find($request->getParam('lpk_id'));

        if (!$pt) {
            $this->flash('danger', 'Tidak ada data');
            return $this->redirect($response, 'admin-bkol-lpk');
        }

        if ($pt->delete()) {
            $this->flash('success', 'Data Berhasil di Hapus');
            return $this->redirect($response, 'admin-bkol-lpk');
        }

        $this->flash('danger', 'There was a problem removing the data.');
        return $this->redirect($response, 'admin-bkol-lpk');
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
            return $this->redirect($response, 'admin-bkol-lpk');
        }

        if ($request->isPost()) {
            if ($this->utils->updatePost($user->id)) {
                return $this->redirect($this->container->response, 'admin-bkol-lpk');
            }
        }

        $lpk = LPK::select('id', 'nama_lpk')->get();
        return $this->view->render(
            $response,
            'dashboard/admin/bkol/bkollpk/edit.twig',
             ['form_state' => 'edit', 'user' => $user->toArray(), 'lpk' => $lpk]
        );
    }

    public function update_user( $post_id, $user_id = null )
    {
        if ( $user_id ) {
            $user = U::with('lpk')->find($user_id);
            if ( $user->email !== $this->container->request->getParam('email') ) {
                $user->email = $this->container->request->getParam('email');
            }
            $user->username = $this->container->request->getParam('username');
            $user->password = $this->container->request->getParam('password');
            $user->save();

        } else {
            $userDetails = [
                'email' => $this->container->request->getParam('email'),
                'username' => $this->container->request->getParam('username'),
                'password' => $this->container->request->getParam('password'),
                'lpk_id' => $post_id,
                'permissions' => [
                    'user.delete' => 0
                ]
            ];

            $role = $this->container->auth->findRoleByName('lpk');
            $user = $this->container->auth->registerAndActivate($userDetails);
            $role->users()->attach($user);
        }
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
        return $this->redirect($response, 'admin-bkol-lpk');
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
        return $this->redirect($response, 'admin-bkol-lpk');
    }
}
