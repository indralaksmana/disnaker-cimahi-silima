<?php
namespace App\Controller;
use App\Model\SpektrumKeahlian;
use Carbon\Carbon;
use Psr\Container\ContainerInterface;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

class SKeahlian extends Controller {

    public function program(Request $request, Response $response, $bidang){
        $program = SpektrumKeahlian::select('kode_program', 'nama', 'id')
                            ->where('kode_bidang', $bidang)
                            ->where('kode_program', '!=', 0)
                            ->where('kode_kompetensi', 0)
                            ->get();

        echo "<option value='' id='0/0'>Pilih Program Keahlian</option>";
        foreach ($program as $data) {
            echo "<option value='$data->id' id='$bidang/$data->kode_program'>$data->nama</option>";
        }
    }

    public function kompetensi(Request $request, Response $response, $bidang, $program){

        $kompetensi = SpektrumKeahlian::select('kode_kompetensi', 'nama','id')
                            ->where('kode_bidang', $bidang)
                            ->where('kode_program', $program)
                            ->where('kode_kompetensi', '!=', 0)
                            ->get();

        echo "<option value='' id='0/0/0'>Pilih Kompetensi Keahlian</option>";
        foreach ($kompetensi as $data) {
            echo "<option value='$data->id' id='$bidang/$program/$data->kode_kompetensi'>$data->nama</option>";
        }
    }
}