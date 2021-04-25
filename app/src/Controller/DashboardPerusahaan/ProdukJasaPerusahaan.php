<?php

namespace App\Controller\DashboardPerusahaan;
use App\Controller\Controller;
use Carbon\Carbon;
use App\Model\ContactRequests;
use App\Model\Oauth2Providers;
use App\Model\ProdukJasaPerusahaanModel;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use Slim\Views\PhpRenderer;


class ProdukJasaPerusahaan extends Controller
{
    public function produkjasa(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('companies.view')) {
            return $check;
        }
        $perusahaan_id = $this->auth->check()->companiesprofile->id;
        $perusahaan =  ProdukJasaPerusahaanModel::where('perusahaan_id', '=', $perusahaan_id)->first();
        $r = $this->request->getParams();
        if ($request->isPost()) {
            // Validate Data
            $validateData = array(
                // 'first_name' => array(
                //     'rules' => V::length(2, 25)->alnum('\'?!@#,."'),
                //     'messages' => array(
                //         'length' => 'Must be between 2 and 25 characters.',
                //         'alnum' => 'Contains an invalid character.'
                //         )
                // ),
            );

            $this->validator->validate($request, $validateData);
    
            if ($this->validator->isValid()) {
                $update = new ProdukJasaPerusahaanModel;
                $update = $update->where('perusahaan_id', '=', $perusahaan_id)->first();
                if ($update) {
                    $update->produk_jasa =  $r['produk_jasa'];
                    $update->save();
                }
                if (!$update) {
                    $add = new ProdukJasaPerusahaanModel;
                    $add->produk_jasa =  $r['produk_jasa'];
                    $add->perusahaan_id =  $perusahaan_id;
                    $add->save();
                }
                if ($add || $update) {
                  $this->flash('success', 'Produk Dan Jasa Berhasil Di update');
                  return $this->redirect($response, 'dashboardperusahaan-produk-jasa');
                }
                $this->flashNow('danger', 'There was an error updating your account information.');
            }
        }

        return $this->view->render(
            $response, 'bkol/dashboard-perusahaan/produk-jasa.twig',
            array(
                "perusahaan" => $perusahaan
            )
        );
    }
}
