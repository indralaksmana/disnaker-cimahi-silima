<?php

namespace App\Controller\Front\Minisite;

use App\Controller\Controller;
use Carbon\Carbon;
use Psr\Container\ContainerInterface;
use App\Model\PerguruanTinggiModel;
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
class PerguruanTinggi extends Controller
{


    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function index(Request $request, Response $response)
    {

        $pt = PerguruanTinggiModel::orderBy('id', 'DESC');

        $ps = isset($_GET['program-studi']) ? (string) $_GET['program-studi'] : '';
        if(!empty($ps)){
            $pt->whereHas('programstudi', function ($query) use ( $ps) {
                $query->where('ps_id', '=',  $ps);
            });
        }

        $pstudi= new \App\Model\ProgramStudiModel;
        $pstudi = $pstudi->withCount('psdikti')
        ->whereHas('psdikti', function ($query) {
            
        })
        ->get();

        return $this->view->render(
            $response,
            'bkol/minisite/dikti/perguruan-tinggi.twig',
            array(
              "pt" => $pt->get(),
              "pstudi" => $pstudi
            )
        );
    }

    // Blog Post
    public function Detail(Request $request, Response $response)
    {
        $args =  $request->getAttribute('route')->getArguments();
        $post = PerguruanTinggiModel::where('slug', $args['slug'])->first();
        $imgs = explode('#', $post->gallerys_photo);

        
        if (!$post) {
            $this->flash('danger', 'Perguruan Tinggi Tidak di temukan');
            return $this->redirect($response, 'minisite-bkk');
        }

        $pt_id = $post->id;
        $user = Users::where('perguruan_tinggi_id', $pt_id)->first();
        $user_id = $user->id;

        // Menampilkan Berita
        $berita  = BlogPosts::where('status', 1)->orderBy('created_at', 'DESC');
        $berita = $berita->whereHas('Author', function ($query) use ($user_id){
            $query->where('user_id', '=',  $user_id);
        });

        // Menampilan Agenda
        $agenda  = AgendaPosts::where('status', 1)->orderBy('created_at', 'DESC');
        $agenda = $agenda->whereHas('author', function ($query) use ($user_id){
            $query->where('user_id', '=', $user_id);
        });

        // Menampilan Album
        $album  = GalleryPosts::where('status', 1)->orderBy('created_at', 'DESC');
        $album = $album->whereHas('author', function ($query) use ($user_id){
            $query->where('user_id', '=', $user_id);
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
            'bkol/minisite/dikti/perguruan-tinggi-detail.twig',
            array(
              "post" => $post,
              "imgs" => $imgs,
              'berita' => $berita->get(),
              'gallery' => $gallery,
              'agenda' => $agenda->get()
            )
        );
    }

    public function agenda(Request $request, Response $response)
    {
        $args =  $request->getAttribute('route')->getArguments();
        $post = PerguruanTinggiModel::where('slug', $args['slug'])->first();
        $pt = $post->id;
        $user = Users::where('perguruan_tinggi_id', $pt_id)->first();
        $user_id = $user->id;

        // Menampilan Agenda
        $agenda  = AgendaPosts::orderBy('created_at', 'DESC');
        $agenda = $agenda->whereHas('author', function ($query) use ($user_id){
            $query->where('perguruan_tinggi_id', '=',  $user_id);
        });

        if (!$post) {
            $this->flash('danger', 'Perguruan tinggi Tidak di temukan');
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
