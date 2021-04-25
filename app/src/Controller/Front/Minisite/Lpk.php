<?php

namespace App\Controller\Front\Minisite;

use App\Controller\Controller;
use Carbon\Carbon;
use Psr\Container\ContainerInterface;
use App\Model\BkkModel;
use App\Model\LpkModel;
use App\Model\AgendaPosts;
use App\Model\BlogPosts;
use App\Model\GalleryPosts;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use App\Model\Users;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Lpk extends Controller
{
    

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function index(Request $request, Response $response)
    {

       
        $kt = isset($_GET['keterampilan']) ? (string) $_GET['keterampilan'] : '';

        $lpk = LpkModel::orderBy('nama_lpk', 'ASC');
        if(!empty($kt)){
            $lpk->whereHas('keterampilan', function ($query) use ( $kt) {
                $query->where('k_id', '=',  $kt);
            });
        }
        
        $keterampilan = new \App\Model\KeterampilanModel;
        $keterampilan = $keterampilan->withCount('keterampilanlpk')
        ->whereHas('keterampilanlpk', function ($query) {
            $query->has('lpk');
        })
        ->get();
        
        return $this->view->render(
            $response,
            'bkol/minisite/lpk/lpk.twig',
            array(
              "lpk" => $lpk->get(),
              "keterampilan" => $keterampilan
            )
        );
    }


    public function Detail(Request $request, Response $response)
    {
        $args =  $request->getAttribute('route')->getArguments();
        $post = LpkModel::with('keterampilan','user')->where('slug', $args['slug'])->first();
        $imgs = explode('#', $post->gallerys_photo);

        // Get Page Number
        $page = 1;
        if (isset($args['page']) && is_numeric($args['page'])) {
            $page = $args['page'];
        }

        $lpk = $post->id;
        //lpk id tidak sama dengan userid
        //cari userid
        $user = Users::where('lpk_id', $lpk)->first();
        $user_id = $user->id;

        // Menampilkan Berita
        $berita  = BlogPosts::where('status', 1)->orderBy('created_at', 'DESC');
        $berita = $berita->whereHas('author', function ($query) use ($user_id){
            $query->where('user_id', '=',  $user_id);
        });

        // Menampilan Agenda
        $agenda  = AgendaPosts::where('status', 1)->orderBy('created_at', 'DESC');
        $agenda = $agenda->whereHas('author', function ($query) use ($user_id){
            $query->where('user_id', '=',  $user_id);
        });

        // Menampilan Album
        $album  = GalleryPosts::where('status', 1)->orderBy('created_at', 'DESC');
        $album = $album->whereHas('author', function ($query) use ($user_id){
            $query->where('user_id', '=',  $user_id);
        });

        $gallery = array();
        foreach ($album->get() as $ab) {
            $imgs = explode('#', $ab->gallerys_photo);
            $imgs = isset( $imgs[0] ) && ! empty( $imgs[0] ) ? $imgs[0] : '/img/nologo.jpg';
            $ab->thumb = $imgs;
            $gallery[] = $ab;
        }

        if (!$post) {
            $this->flash('danger', 'BKK Tidak di temukan');
            return $this->redirect($response, 'minisite-bkk');
        }
        return $this->view->render(
            $response,
            'bkol/minisite/lpk/lpk-detail.twig',
            array(
              "post" => $post,
              "imgs" => $imgs,
              "berita" => $berita->get(),
              "agenda" => $agenda->get(),
              "album" => $album->get(),
              'gallery' => $gallery
            )
        );
    }

    public function agenda(Request $request, Response $response)
    {
        $args =  $request->getAttribute('route')->getArguments();
        $post = LpkModel::with('keterampilan','user')->where('slug', $args['slug'])->first();
        $lpk = $post->id;

        $user = Users::where('lpk_id', $lpk)->first();
        $user_id = $user->id;

        // Menampilan Agenda
        $agenda  = AgendaPosts::orderBy('created_at', 'DESC');
        $agenda = $agenda->whereHas('author', function ($query) use ($user_id){
            $query->where('user_id', '=',  $user_id);
        });

        if (!$post) {
            $this->flash('danger', 'LPK Tidak di temukan');
            return $this->redirect($response, 'minisite-lpk');
        }
        return $this->view->render(
            $response,
            'bkol/minisite/agenda/agenda.twig',
            array(
              "agenda" => $agenda->get()
            )
        );
    }

   

}
