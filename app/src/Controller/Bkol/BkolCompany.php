<?php


namespace App\Controller\Bkol;
use App\Controller\Controller;
use Psr\Container\ContainerInterface;
use Carbon\Carbon;
use App\Model\BkolPerusahaan as U;
use App\Model\Daerah;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class BkolCompany extends Controller
{


    public function list(Request $request, Response $response)
    {
        $jabatan = isset($_GET['jabatan']) ? (string) $_GET['jabatan'] : '';
        $provinsi = isset($_GET['provinsi']) ? (string) $_GET['provinsi'] : '';
        $kabupatenkota = isset($_GET['kabupatenkota']) ? (string) $_GET['kabupatenkota'] : '';
        $pendidikan = isset($_GET['pendidikan']) ? (string) $_GET['pendidikan'] : '';
        $perusahaan = U::orderBy('created_at', 'DESC')->has('userperusahaan');
        if(!empty($jabatan)){
                $perusahaan->where('jabatan_id', 'LIKE' , '%'.$jabatan.'%');
        }
        if(!empty($provinsi)){
                $perusahaan->where('provinsi_id', '=', $provinsi);
        }
        if(!empty($kabupatenkota))  {
                $perusahaan->where('kabupatenkota_id', '=', $kabupatenkota);
        }
        if(!empty($pendidikan))  {
                $perusahaan->where('pendidikan_terakhir_id', '=', $pendidikan);
        }

        return $this->view->render(
            $response,
            'bkol/perusahaan/list.twig',
            array(
                "perusahaan" => $perusahaan->get()
            )
        );
    }


}
