<?php


namespace App\Controller\Admin\PencariKerja;
use App\Controller\Controller;
use App\Controller\Controller;
use Psr\Container\ContainerInterface;
use Carbon\Carbon;
use App\Library\VideoParser as VP;
use App\Model\DataPerkembanganPencariKerjaModel as JU;
use App\Model\DataPerusahaanModel;
use App\Model\FederasiModel;
use App\Model\Daerah;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class DataPerkembanganPencariKerja extends Controller
{

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $page_name = 'DATA PERKEMBANGAN PENCARI KERJA';
        $this->page_name = $page_name;
    }
    public function index(Request $request, Response $response)
    {
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $posts = $posts->where('user_id', $this->auth->check()->id);
        }
        return $this->view->render(
            $response,
            'dashboard/data-perkembangan-pencari-kerja/data-perkembangan-pencari-kerja.twig',
            array(
                "data" => JU::get(),
                "page_name" => $this->page_name
            )
        );
    }
    // Add New Blog Ikhtisar Statistika
    public function Add(Request $request, Response $response)
    {
        if ($request->isPost()) {
            if ($this->validator->isValid()) {
                $add = new JU;
                $add->tahun = $request->getParam('tahun');
                $add->bulan = $request->getParam('bulan');
                $add->penduduk_usia_kerja = 0;
                $add->ak_penduduk_yang_bekerja = 0;
                $add->ak_penganggur_terbuka = 0;
                $add->bak_sekolah = 0;
                $add->bak_mengurus_rumah_tangga = 0;
                $add->bak_lainya = 0;
                if ($add->save()) {
                    $this->flash('success', 'data Sudah di Tambahkan');
                    return $this->redirect($response, 'data-perkembangan-pencari-kerja');
                }
            }
        }
        return $this->view->render(
            $response,
            'dashboard/data-perkembangan-pencari-kerja/data-perkembangan-pencari-kerja-add.twig',
            array(
              "page_name" => $this->page_name
            )
        );
    }
    // Delete Blog Ikhtisar Statistika
    public function Delete(Request $request, Response $response)
    {
        $data = JU::find($request->getParam('id'));
        if (!$data) {
            $this->flash('danger', 'Tidak ada  data');
            return $this->redirect($response, 'data-perkembangan-pencari-kerja');
        }

        if ($data->delete()) {
            $this->flash('success', 'Berhasil di Hapus');
            return $this->redirect($response, 'data-perkembangan-pencari-kerja');
        }

        $this->flash('danger', 'There was a problem removing the  data.');
        return $this->redirect($response, 'data-perkembangan-pencari-kerja');
    }

    // Edit Blog Ikhtisar Statistika
    public function Edit(Request $request, Response $response, $dataId)
    {
        $data = JU::find($dataId);
        if (!$data) {
            $this->flash('danger', 'Sorry, that Ikhtisar Statistika was not found.');
            return $response->withRedirect($this->router->pathFor('data'));
        }
        if ($request->isPost()) {

            if ($this->validator->isValid()) {
                $data->tahun = $request->getParam('tahun');
                $data->bulan = $request->getParam('bulan');
                $data->penduduk_usia_kerja = $request->getParam('penduduk_usia_kerja') ? $request->getParam('penduduk_usia_kerja') : 0;
                $data->ak_penduduk_yang_bekerja = $request->getParam('ak_penduduk_yang_bekerja') ? $request->getParam('ak_penduduk_yang_bekerja') : 0;
                $data->ak_penganggur_terbuka = $request->getParam('ak_penganggur_terbuka') ? $request->getParam('ak_penganggur_terbuka') : 0;
                $data->bak_sekolah = $request->getParam('bak_sekolah') ? $request->getParam('bak_sekolah') : 0;
                $data->bak_mengurus_rumah_tangga = $request->getParam('bak_mengurus_rumah_tangga') ? $request->getParam('bak_mengurus_rumah_tangga') : 0;
                $data->bak_lainya = $request->getParam('bak_lainya') ? $request->getParam('bak_lainya') : 0;
                if ($data->save()) {
                    $this->flash('success', 'Data Berhasil di Edit');
                    return $this->redirect($response, 'data-perkembangan-pencari-kerja');
                }
            }
            $this->flash('danger', 'Data Gagal di Edit');
        }
        return $this->view->render($response,
        'dashboard/data-perkembangan-pencari-kerja/data-perkembangan-pencari-kerja-edit.twig',
        [
          'data' => $data
        ]);
    }

    public function DetailBulan(Request $request, Response $response, $dataId)
    {
        $data = JU::find($dataId);
        if (!$data) {
            $this->flash('danger', 'Sorry, that Ikhtisar Statistika was not found.');
            return $response->withRedirect($this->router->pathFor('data'));
        }
        return $this->view->render(
          $response,
          'dashboard/data-perkembangan-pencari-kerja/bulan-detail.twig',
          [
            'data' => $data,
            "page_name" => "data"
          ]
        );
    }
}
