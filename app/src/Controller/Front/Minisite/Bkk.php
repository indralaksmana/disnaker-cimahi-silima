<?php

namespace App\Controller\Front\Minisite;

use App\Controller\Controller;
use Carbon\Carbon;
use Psr\Container\ContainerInterface;
use App\Model\BkkModel;
use App\Model\LpkModel;
use App\Model\GalleryPosts;
use App\Model\BlogPosts;
use App\Model\AgendaPosts;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use App\Model\Users;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Bkk extends Controller
{


    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function index(Request $request, Response $response)
    {
        $kk = isset($_GET['kompetensi']) ? (string) $_GET['kompetensi'] : '';
        $bkk = BkkModel::orderBy('id', 'DESC');
        if(!empty($kk)){
            $bkk->whereHas('kompetensikeahlian', function ($query) use ( $kk) {
                $query->where('sk_id', '=',  $kk);
            });
        }

        $kompentensikeahlian = new \App\Model\SpektrumKeahlian;
        $kompentensikeahlian = $kompentensikeahlian->withCount('jurusanbkk')
        ->whereHas('jurusanbkk', function ($query) {
            $query->has('bkk');
        })
        ->get();
          
        return $this->view->render(
            $response,
            'bkol/minisite/bkk/bkk.twig',
            array(
              "bkk" => $bkk->get(),
              "kk" => $kompentensikeahlian
            )
        );
    }

    // Blog Post
    public function Detail(Request $request, Response $response)
    {
        $args =  $request->getAttribute('route')->getArguments();
        $post = BkkModel::where('slug', $args['slug'])->first();
        $imgs = explode('#', $post->gallerys_photo);

        if (!$post) {
            $this->flash('danger', 'BKK Tidak di temukan');
            return $this->redirect($response, 'minisite-bkk');
        }

        $bkk = $post->id;
        $user = Users::where('bkk_id', $bkk)->first();
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

        return $this->view->render(
            $response,
            'bkol/minisite/bkk/bkk-detail.twig',
            array(
              "post" => $post,
              "imgs" => $imgs,
              'berita' => $berita->get(),
              'gallery' => $gallery,
              'agenda' => $agenda->get(),
            )
        );
    }

    public function agenda(Request $request, Response $response)
    {
        $args =  $request->getAttribute('route')->getArguments();
        $post = BkkModel::where('slug', $args['slug'])->first();
        $bkk = $post->id;

        $user = Users::where('bkk_id', $bkk)->first();
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
