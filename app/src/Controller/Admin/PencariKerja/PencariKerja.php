<?php


namespace App\Controller\Admin\PencariKerja;
use App\Controller\Controller;
use Psr\Container\ContainerInterface;
use Carbon\Carbon;
use App\Model\PencariKerjaModel as JU;
use App\Library\PencariKerja as B;
use App\Model\DatapencarikerjaModel;
use App\Model\JurusanModel;
use App\Model\LulusanModel;
use App\Model\Daerah;
use App\Model\JenisPendidikanModel;
use App\Model\BidangPekerjaanModel;
use App\Model\PetugasPendataModel;
use App\Model\KursusPelatihanModel;
use App\Model\StatuspencarikerjaModel;
use App\Model\StatusPemilikanModel;
use App\Model\StatusPemodalanModel;
use App\Model\TenagaKerjaModel;
use App\Model\JamsostekModel;
use App\Model\FasilitaspencarikerjaModel;
use App\Model\PelanggaranModel;
// use App\Model\ProgramPensiunModel;
use App\Model\PerangkatHIModel;
use App\Model\PensiunModel;
use App\Model\FasilitasK3Model;
use App\Model\KesejahteraanModel;
use App\Model\PerangkatHIOrganisasiModel;
use App\Model\PerangkatHIHubunganKerjaModel;
use App\Model\KluiModel;
use App\Model\BonusModel;
use App\Model\PengupahanModel;
use App\Model\PeralatanK3Model;
use App\Model\ThrModel;
use App\Model\WaktuKerjaModel;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class PencariKerja extends Controller
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $pencarikerjaUtils = new B($this->container);
        $this->pencarikerjaUtils = $pencarikerjaUtils;
    }

    public function index(Request $request, Response $response)
    {
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $pencarikerjas = $pencarikerjas->where('user_id', $this->auth->check()->id);
        }

        $pencarikerja = JU::get();

        return $this->view->render(
            $response,
            'dashboard/pencarikerja/pencarikerja.twig',
            array(
                "pencarikerja" => $pencarikerja
            )
        );
    }
    // Add New
    public function Add(Request $request, Response $response)
    {
        // if ($check = $this->sentinel->hasPerm('.create', 'dashboard', $this->config['-enabled'])) {
        //     return $check;
        // }

        $daerahs = Daerah::where('lokasi_kabupatenkota', 0)
                            ->where('lokasi_kecamatan', 0)
                            ->where('lokasi_kelurahan', 0)
                            ->orderBy('lokasi_nama')
                            ->get();
       $jenispendidikan = JenisPendidikanModel::where('kode_jurusan', 0)
            ->orderBy('jenis_pendidikan')
            ->get();

        if ($request->isPost()) {
            if ($this->pencarikerjaUtils->addPost()) {

            }
            $this->flash('success', 'Nama pencarikerja Sudah tersedia');
            return $this->redirect($response, 'pencarikerja');
        }

        return $this->view->render(
            $response,
            'dashboard/pencarikerja/add.twig',
             array(
                "jurusan" => JurusanModel::get(),
                "lulusan" => LulusanModel::get(),
                "kursuspelatihan" => KursusPelatihanModel::get(),
                "petugaspendata" => PetugasPendataModel::get(),
                "bidangpekerjaan" => BidangPekerjaanModel::get(),
                "daerahs" => $daerahs,
                "jenispendidikan" => $jenispendidikan


            )
        );
    }


    // Delete
    public function Delete(Request $request, Response $response)
    {


        $pencarikerja = JU::find($request->getParam('pencarikerja_id'));

        if (!$pencarikerja) {
            $this->flash('danger', 'Tidak ada pencarikerja');
            return $this->redirect($response, 'pencarikerja');
        }

        if ($pencarikerja->delete()) {
            $this->flash('success', 'Berhasil di Hapus');
            return $this->redirect($response, 'pencarikerja');
        }

        $this->flash('danger', 'There was a problem removing the pencarikerja.');
        return $this->redirect($response, 'pencarikerja');
    }

    // Edit
    public function Edit(Request $request, Response $response, $pencarikerjaId)
    {
        $pencarikerja = JU::where('id', $pencarikerjaId)->with('petugaspendata')->first();

        $daerahs = Daerah::where('lokasi_kabupatenkota', 0)
                            ->where('lokasi_kecamatan', 0)
                            ->where('lokasi_kelurahan', 0)
                            ->orderBy('lokasi_nama')
                            ->get();
        $jenispendidikan = JenisPendidikanModel::where('kode_jurusan', 0)
            ->orderBy('jenis_pendidikan')
            ->get();
        if (!$pencarikerja) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'pencarikerja');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $pencarikerja->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'pencarikerja');
        }

        if ($request->isPost()) {
            if ($this->pencarikerjaUtils->updatePost($pencarikerja->id)) {
                return $this->redirect($this->container->response, 'pencarikerja');
            }
        }


        return $this->view->render(
            $response,
            'dashboard/pencarikerja/edit.twig',
            [
                "pencarikerja" => $pencarikerja,
                "kursuspelatihan" => KursusPelatihanModel::get(),
                "petugaspendata" => PetugasPendataModel::get(),
                "bidangpekerjaan" => BidangPekerjaanModel::get(),
                "daerahs" => $daerahs,
                "jenispendidikan" => $jenispendidikan
            ]

        );
    }

    // Edit
    public function DetailPencariKerja(Request $request, Response $response, $pencarikerjaId)
    {
        $pencarikerja = JU::where('id', $pencarikerjaId)->with('petugaspendata')->first();

        if (!$pencarikerja) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'pencarikerja');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $pencarikerja->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'pencarikerja');
        }

        if ($request->isPost()) {
            if ($this->pencarikerjaUtils->updatePost($pencarikerja->id)) {
                return $this->redirect($this->container->response, 'pencarikerja');
            }
        }


        return $this->view->render(
            $response,
            'dashboard/pencarikerja/pencari-kerja-detail.twig',
            [
                "pencarikerja" => $pencarikerja,
                "jurusan" => JurusanModel::get(),
                "lulusan" => LulusanModel::get(),
                "kursuspelatihan" => KursusPelatihanModel::get(),
                "petugaspendata" => PetugasPendataModel::get(),
                "bidangpekerjaan" => BidangPekerjaanModel::get()
            ]

        );
    }
}
