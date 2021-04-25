<?php
namespace App\Controller;
use App\Model\Daerah;

use App\Model\JenisPendidikanModel;
use App\Model\DataPerusahaanModel;

use Carbon\Carbon;

use Psr\Container\ContainerInterface;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

class Ajax extends Controller {

  	public function index()
    {
        $daerahs = Daerah::where('lokasi_kabupatenkota', 0)
                            ->where('lokasi_kecamatan', 0)
                            ->where('lokasi_kelurahan', 0)
                            ->orderBy('lokasi_nama')
                            ->get();
        return view('daerah.index', compact('daerahs'));
    }
    public function perusahaan(Request $request, Response $response, $perusahaan){
        $perusahaan = DataPerusahaanModel::where('id', $perusahaan)
                            ->get();

        foreach ($perusahaan as $data) {
            foreach ($data->serikat as $serikat) {
              $federasi = $serikat->federasi->nama_federasi;
              echo "<option value='serikat' selected>$federasi</option>";
            }

        }
    }

    public function kabupatenkota(Request $request, Response $response, $provinsi){
        $kabupatenkota = Daerah::select('lokasi_kabupatenkota', 'lokasi_nama', 'id')
                            ->where('lokasi_provinsi', $provinsi)
                            ->where('lokasi_kabupatenkota', '!=', 0)
                            ->where('lokasi_kecamatan', 0)
                            ->where('lokasi_kelurahan', 0)
                            ->get();

        echo "<option value='' id='0/0'>Pilih Kabupaten/Kota</option>";
        foreach ($kabupatenkota as $data) {
            echo "<option value='$data->id' id='$provinsi/$data->lokasi_kabupatenkota'>$data->lokasi_nama</option>";
        }
    }

    public function kecamatan(Request $request, Response $response, $provinsi, $kabupatenkota){

        $kecamatan = Daerah::select('lokasi_kecamatan', 'lokasi_nama','id')
                            ->where('lokasi_provinsi', $provinsi)
                            ->where('lokasi_kabupatenkota', $kabupatenkota)
                            ->where('lokasi_kecamatan', '!=', 0)
                            ->where('lokasi_kelurahan', 0)
                            ->get();

        echo "<option value='' id='0/0/0'>Pilih Kecamatan</option>";
        foreach ($kecamatan as $data) {
            echo "<option value='$data->id' id='$provinsi/$kabupatenkota/$data->lokasi_kecamatan'>$data->lokasi_nama</option>";
        }
    }

    public function kelurahan(Request $request, Response $response, $provinsi, $kabupatenkota, $kecamatan){

        $kelurahan = Daerah::select('lokasi_kecamatan', 'lokasi_nama','id')
                            ->where('lokasi_provinsi', $provinsi)
                            ->where('lokasi_kabupatenkota', $kabupatenkota)
                            ->where('lokasi_kecamatan',  $kecamatan)
                            ->where('lokasi_kelurahan', '!=', 0)
                            ->get();

        echo "<option value=''>Pilih Kelurahan</option>";
        foreach ($kelurahan as $data) {
            echo "<option value='$data->id'>$data->lokasi_nama</option>";
        }
    }

    public function KodeJurusan(Request $request, Response $response, $kodependidikan){
        $kodejurusan = JenisPendidikanModel::select('kode_jurusan', 'jenis_pendidikan' ,'id')
                            ->where('kode_pendidikan', $kodependidikan)
                            ->where('kode_jurusan', '!=', 0)
                            ->orderBy('jenis_pendidikan', 'ASC')
                            ->get();

        echo "<option value=''>Jurusan</option>";
            foreach ($kodejurusan as $data) {
                echo "<option value='$data->id'>$data->jenis_pendidikan</option>";
        }
    }
    
    public function NamaJurusan(Request $request, Response $response, $kodependidikan){
        $kodejurusan = JenisPendidikanModel::select('kode_jurusan', 'jenis_pendidikan' ,'id')
                            ->where('kode_pendidikan', $kodependidikan)
                            ->where('kode_jurusan', '!=', 0)
                            ->orderBy('jenis_pendidikan', 'ASC')
                            ->get();

        echo "<option value=''>Jurusan</option>";
            foreach ($kodejurusan as $data) {
                echo "<option value='$data->jenis_pendidikan'>$data->jenis_pendidikan</option>";
        }
    }

  }
