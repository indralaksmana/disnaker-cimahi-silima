<?php
namespace App\Controller;
use App\Model\KbliModel;
use Carbon\Carbon;
use Psr\Container\ContainerInterface;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

class Kbli extends Controller {

    public function GolPokok(Request $request, Response $response, $kategori){
        $golpokok = KbliModel::select('gol_pokok', 'judul_golongan')
                            ->where('kategori', $kategori)
                            ->where('gol_pokok', '!=', 0)
                            ->where('golongan', 0)
                            ->where('sub_golongan', 0)
                            ->where('kelompok', 0)
                            ->get();    

        echo "<option value='' id='0/0'>Golongan Pokok</option>";
        foreach ($golpokok as $data) {  
            echo "<option value='$data->gol_pokok' id='$kategori/$data->gol_pokok'>$data->judul_golongan</option>";
        }  
    }

    public function Golongan(Request $request, Response $response, $kategori, $golpokok){

        $golongan = KbliModel::select('golongan', 'judul_golongan')
                            ->where('kategori', $kategori)
                            ->where('gol_pokok', $golpokok)
                            ->where('golongan', '!=', 0)
                            ->where('sub_golongan', 0)
                            ->where('kelompok', 0)
                            ->get();    

        echo "<option value='' id='0/0/0'>Golongan</option>";
        foreach ($golongan as $data) {  
            echo "<option value='$data->golongan' id='$kategori/$golpokok/$data->golongan'>$data->judul_golongan</option>";
        }   
    }

    public function SubGolongan(Request $request, Response $response, $kategori, $golpokok, $golongan){

        $subgolongan = KbliModel::select('sub_golongan', 'judul_golongan')
                            ->where('kategori', $kategori)
                            ->where('gol_pokok', $golpokok)
                            ->where('golongan', $golongan)
                            ->where('sub_golongan', '!=', 0)
                            ->where('kelompok', 0)
                            ->get();    

        echo "<option value='' id='0/0/0/0'>Sub Golongan</option>";
        foreach ($subgolongan as $data) {  
            echo "<option value='$data->sub_golongan' id='$kategori/$golpokok/$golongan/$data->sub_golongan'>$data->judul_golongan</option>";
        }   
    }

    public function Kelompok(Request $request, Response $response, $kategori, $golpokok, $golongan, $subgolongan){

        $kelompok = KbliModel::select('kelompok', 'judul_golongan')
                            ->where('kategori', $kategori)
                            ->where('gol_pokok', $golpokok)
                            ->where('golongan', $golongan)
                            ->where('sub_golongan', $subgolongan)
                            ->where('kelompok', '!=', 0)
                            ->get();    

        echo "<option value=''>Kelompok</option>";
        foreach ($kelompok as $data) {  
            echo "<option value='$data->kelompok'>$data->judul_golongan</option>";
        }   
    }


  }