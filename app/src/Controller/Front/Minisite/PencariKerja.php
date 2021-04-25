<?php

namespace App\Controller\Front\Minisite;
use App\Controller\Controller;
use Carbon\Carbon;
use App\Library\VideoParser as VP;
use App\Model\BkolPencariKerja;
use App\Model\JenisPendidikanModel;
use App\Library\Paginator;
use App\Library\Utils;
use App\Library\Settings as Settings;
use Illuminate\Support\Facades\DB;
use App\Model\ShortlistModel;
use App\Model\Users;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use Slim\Views\PhpRenderer;
/** @SuppressWarnings(PHPMD.StaticAccess) */
class PencariKerja extends Controller
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function main(Request $request, Response $response)
    {
        $pendidikan = isset($_GET['pendidikan']) ? (string) $_GET['pendidikan'] : '';
        $jurusan = isset($_GET['jurusan']) ? (string) $_GET['jurusan'] : '';

        $pencarikerja = Users::with('roles')
            ->WhereHas('roles', function($q){$q->whereIn('name', ['jobseeker']);}
        );
        return $this->view->render(
            $response,
            'bkol/minisite/pencarikerja/pencarikerja.twig',
            array(
                "pencarikerja" => $pencarikerja->get()
            )
        );
    }
    public function list(Request $request, Response $response)
    {
        $pendidikan = isset($_GET['pendidikan']) ? (string) $_GET['pendidikan'] : '';
        $jurusan = isset($_GET['jurusan']) ? (string) $_GET['jurusan'] : '';
        $pencarikerja = BkolPencariKerja::
          with(array('pendidikanterakhir'=>function($query){
              $query->select('jenis_pendidikan')->where('jenis_pendidikan', '=', '');
          }))
          ->with(array('jurusanpendidikan'=>function($query){
              $query->select('jenis_pendidikan')->where('jenis_pendidikan', '=', '');
          }))
            ->with(array('userpencarikerja'=>function($query){
              $query->select('id','photoprofile');
          }))
          ->has('user');
        // $pencarikerja = BkolPencariKerja::with('userpencarikerja');
        if(!empty($pendidikan)){
                $pencarikerja->where('pendidikan_terakhir_id', 'LIKE' , '%'.$pendidikan.'%');
        }
        if(!empty($jurusan)){
                $pencarikerja->where('jurusan_pendidikan_id', '=', $jurusan);
        }

        return $response->withJson($pencarikerja->get(), 200);
    }
    public function Detail(Request $request, Response $response, $args)
    {
        $args =  $request->getAttribute('route')->getArguments();
        $post = Users::with('roles','riwayatpekerjaan','riwayatpendidikan','riwayatnonformalpendidikan')
                ->WhereHas('roles', function($q){$q->whereIn('name', ['jobseeker']);})
                ->where('username', $args['username'])->first();
        
        if (!$post) {
            $this->flash('danger', 'Pencari Kerja Tidak tersedia');
            return $this->redirect($response, 'list-pencarikerja');
        }
        if ($request->isPost()) {
            if (!$this->auth->check()) {
                $this->flashNow('danger', 'Kamu Harus Login dulu');
                return $this->view->render($response, 'bkol/pencarikerja/pencarikerja-detail.twig', array("post" => $post));
            }
            if ($request->getParam('add_favorite') !== null) {
                if ($this->addFavorite($post)) {
                    $this->flash('success', 'Favoritkan Pencaker Berhasil');
                    return $response->withRedirect($request->getUri()->getPath());
                }
                $this->flashNow('warning', 'Kamu Sudah Favoritkan Pencaker ini');
            }
            if ($request->getParam('delete_favorite') !== null) {
                if ($this->deleteFavorite($post)) {
                    $this->flash('success', 'Dibatalkan');
                    return $response->withRedirect($request->getUri()->getPath());
                }
                $this->flashNow('success', 'Dibatalkan');
            }
        }

        if ($this->auth->check()) {
          $loggedin_user = $this->auth->check()->id;
          $is_login = true;
        } else {
            $loggedin_user = null;
            $is_login = false;
        }

        $settings = Settings::getSettingsFile();

        $shortlist = ShortlistModel::where(['perusahaan_id' => $loggedin_user, 'pencaker_id' => $post->id])->first();

        return $this->view->render(
            $response,
            'bkol/minisite/pencarikerja/pencarikerja-detail.twig',
            array(
              "r" => $post,
              "s" => $shortlist,
              "requestParams" => $request->getParams(),
              'is_user_logged_in' => $is_login,
              'base_chima' => $settings['base_chima']
            )
        );
    }
    public function KodePendidikan(Request $request, Response $response){
        $kodejurusan = JenisPendidikanModel::withCount('jurusanpencarikerja')
            ->whereHas('jurusanpencarikerja');

      return $response->withJson($kodejurusan->get(), 200);
    }
    public function KodeJurusan(Request $request, Response $response, $kodependidikan){
        $kodejurusan = JenisPendidikanModel::select('kode_jurusan', 'jenis_pendidikan' ,'id')
            ->withCount('jurusanpencarikerja')
            ->whereHas('jurusanpencarikerja')
            ->where('kode_pendidikan', $kodependidikan)
            ->where('kode_jurusan', '!=', 0)->get();
        echo "<li class='d-flex justify-content-between align-items-center'></li>";
            foreach ($kodejurusan as $data) {
                echo "<li class='d-flex justify-content-between align-items-center'>
                  <input type='checkbox' data-jplist-control='checkbox-text-filter' data-path='.jurusan$data->id' data-group='pencaker' data-name='cb-filter' value='$data->jenis_pendidikan'/>
                  <label for='$data->id'>$data->jenis_pendidikan</label>
                </li>";
        }

    }

    public function addFavorite($post){
      // Validate Data
      $validateData = array(
          // 'coverletter' => array(
          //     'rules' => V::notEmpty()->length(6),
          //     'messages' => array(
          //         'notEmpty' => 'Please enter a coverletter.',
          //         'length' => 'Comment must contain at least 6 characters'
          //         )
          // )
      );
      $this->validator->validate($this->request, $validateData);
      if ($this->validator->isValid()) {
          $loggedin_user = $this->auth->check()->id;
          $shortlist = ShortlistModel::where(['perusahaan_id' => $loggedin_user, 'pencaker_id' => $post->id])->first();
          if(empty($shortlist->perusahaan_id)){
              $perusahaan_id = $this->auth->check()->id;
              $pencaker_id = $post->id;
              $favorite = new ShortlistModel;
              $favorite->perusahaan_id = $perusahaan_id;
              $favorite->pencaker_id = $pencaker_id;
              if ($favorite->save()) {
                  return true;
              }
          }
      }
      return false;
  	}

    public function deleteFavorite($post){
      // Validate Data
      $validateData = array(
          // 'coverletter' => array(
          //     'rules' => V::notEmpty()->length(6),
          //     'messages' => array(
          //         'notEmpty' => 'Please enter a coverletter.',
          //         'length' => 'Comment must contain at least 6 characters'
          //         )
          // )
      );
      $this->validator->validate($this->request, $validateData);
      if ($this->validator->isValid()) {
          $loggedin_user = $this->auth->check()->id;
          $shortlist = ShortlistModel::where(['perusahaan_id' => $loggedin_user, 'pencaker_id' => $post->id])->first();
          $shortlist->delete();
      }
      return false;
  	}

    public function follow(Request $request, Response $response, $args){
    if ($this->auth->check()) {
      $loggedin_user = $this->auth->check()->id;
    } else {
      $loggedin_user = null;
    }
    if ($this->auth->check()) {
      $args =  $request->getAttribute('route')->getArguments();
      $post = BkolPencariKerja::with('userpencarikerja')->where('id', $args['id'])->first();
			/* check ke follow */
			$shortlist =  ShortlistModel::where(['perusahaan_id' => $loggedin_user, 'pencaker_id' => $post->id])->first();
      if(empty($shortlist->perusahaan_id && $shortlist->pencaker_id)){
          $perusahaan_id = $this->auth->check()->id;
          $pencaker_id = $post->userpencarikerja->id;
          $favorite = new ShortlistModel;
          $favorite->perusahaan_id = $perusahaan_id;
          $favorite->pencaker_id = $pencaker_id;
          if ($favorite->save()) {
              return true;
              $response_text = "This item has been followed";
      				$status = 'followed';
          }
      } else {
        $loggedin_user = $this->auth->check()->id;
        $shortlist = ShortlistModel::where(['perusahaan_id' => $loggedin_user, 'pencaker_id' => $post->id])->first();
        $shortlist->delete();
        $response_text = "This item has been unfollowed";
				$status = 'unfollowed';
      }
		} else {
      $response_text = "You must login first!";
      $status = 'error';
		}
		$response = $response->withJson([
			'response' => $response_text,
			'status' => $status
		]);
		return $response;
  }

}
