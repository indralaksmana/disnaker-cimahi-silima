<?php

namespace App\Controller\DashboardLpk;
use App\Controller\Controller;
use Carbon\Carbon;
use App\Model\Users;
use App\Model\KeterampilanModel;
use App\Model\KeterampilanLpkModel;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use Slim\Views\PhpRenderer;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class KeterampilanLpk extends Controller
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function index(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('lpk.view')) {
            return $check;
        }
        $lpk_id = $this->auth->check()->lpk_id;
        $posts = KeterampilanLpkModel::with('lpk','jenisketerampilan');
        $posts = $posts->where('lpk_id', $lpk_id);
        
        return $this->view->render(
            $response,
            'bkol/dashboard-lpk/keterampilan/list.twig',
            array (
                'keterampilan' => $posts->get()
            )
        );
    }

    public function add(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('lpk.view')) {
            return $check;
        }
        $k = KeterampilanModel::get();
        $lpk_id = $this->auth->check()->lpk_id;
        $title = "Tambah";
        if ($request->isPost()) {
            if ($this->validator->isValid()) {
                $add = new KeterampilanLpkModel;
                $add->lpk_id = $lpk_id;
                $add->k_id = $request->getParam('k_id') ? $request->getParam('k_id') : NULL;
                if ($add->save()) {
                    $this->flash('success', 'data Sudah di Tambahkan');
                    return $this->redirect($response, 'lpk-dashboard-keterampilan');
                }
            }
        }

        return $this->view->render(
            $response,
            'bkol/dashboard-lpk/keterampilan/add-edit.twig',
            array(
                "k" => $k,
                "PageTitle" => $title
            )
        );
    }

    public function edit(Request $request, Response $response, $editId)
    {
        if ($check = $this->sentinel->hasPerm('lpk.view')) {
            return $check;
        }
        $k = KeterampilanModel::get();


        $lpk_id = $this->auth->check()->lpk_id;
        $edit = KeterampilanLpkModel::with('jenisketerampilan');
        $edit = $edit->where('lpk_id', $lpk_id);
        $edit = $edit->find($editId);

        $title = "Edit";

        if (!$edit) {
            $this->flash('danger', 'keterampilan Tidak Di Temukan.');
            return $response->withRedirect($this->router->pathFor('data'));
        }
        if ($request->isPost()) {
            if ($this->validator->isValid()) {
                $edit->k_id = $request->getParam('k_id');
                if ($edit->save()) {
                    $this->flash('success', 'Data Berhasil di Edit');
                    return $this->redirect($response, 'lpk-dashboard-keterampilan');
                }
            }
            $this->flash('danger', 'Data Gagal di Edit');
        }
        return $this->view->render($response,
        'bkol/dashboard-lpk/keterampilan/add-edit.twig',
        [
          'data' => $edit,
          "k" => $k,
          "PageTitle" => $title
        ]);
    }

    // Delete Blog Ikhtisar Statistika
    public function delete(Request $request, Response $response)
    {
        $lpk_id = $this->auth->check()->lpk_id;
        $data = KeterampilanLpkModel::with('jenisketerampilan');
        $data = $data->where('lpk_id', $lpk_id);
        $data = $data->find($request->getParam('id'));
        if (!$data) {
            $this->flash('danger', 'Tidak ada  data');
            return $this->redirect($response, 'lpk-dashboard-keterampilan');
        }
        if ($data->delete()) {
            $this->flash('success', 'Berhasil di Hapus');
            return $this->redirect($response, 'lpk-dashboard-keterampilan');
        }

        $this->flash('danger', 'There was a problem removing the  data.');
        return $this->redirect($response, 'lpk-dashboard-keterampilan');
    }
}
