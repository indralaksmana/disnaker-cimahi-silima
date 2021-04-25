<?php

namespace App\Controller\Front\Minisite;

use App\Controller\Controller;
use Carbon\Carbon;
use Psr\Container\ContainerInterface;
use App\Model\BkolPerusahaan;
use App\Model\JobPosts;
use App\Model\AgendaPosts;
use App\Model\BlogPosts;
use App\Model\GalleryPosts;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Perusahaan extends Controller
{


    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function index(Request $request, Response $response)
    {

        $jabatan = isset($_GET['jabatan']) ? (string) $_GET['jabatan'] : '';
        $provinsi = isset($_GET['provinsi']) ? (string) $_GET['provinsi'] : '';
        $kabupatenkota = isset($_GET['kabupatenkota']) ? (string) $_GET['kabupatenkota'] : '';
        $pendidikan = isset($_GET['pendidikan']) ? (string) $_GET['pendidikan'] : '';
        $flaq_p = isset($_GET['lokasi']) ? (string) $_GET['lokasi'] : '';

        $perusahaan = BkolPerusahaan::orderBy('created_at', 'DESC')->has('userperusahaan');
        if(!empty($flaq_p)){
                $perusahaan->where('flag_perusahaan', 'LIKE' , '%'.$flaq_p.'%');
        }
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
            'bkol/minisite/perusahaan/perusahaan.twig',
            array(
                "perusahaan" => $perusahaan->get()
            )
        );
    }

    // Blog Post
    public function Detail(Request $request, Response $response)
    {
        $args =  $request->getAttribute('route')->getArguments();
        $post = BkolPerusahaan::where('slug', $args['slug'])->with('userperusahaan')->first();
        $id = $post->id;

        $jobs = JobPosts::where('status', 1)
            ->where('user_id', $post->id)
            ->where('publish_at', '<', Carbon::now())
            ->with('category', 'tags', 'authorCompanies')
            ->orderBy('publish_at', 'DESC');

        if (!$post) {
            $this->flash('danger', 'Perusahaan Tidak di temukan');
            return $this->redirect($response, 'minisite-perusahaan');
        }

        // Menampilkan Berita
        $berita  = BlogPosts::where('status', 1)->orderBy('created_at', 'DESC');
        $berita = $berita->whereHas('Author', function ($query) use ($id){
            $query->where('user_id', '=',  $id);
        });

        // Menampilan Agenda
        $agenda  = AgendaPosts::orderBy('created_at', 'DESC');
        $agenda = $agenda->whereHas('author', function ($query) use ($id){
            $query->where('user_id', '=',  $id);
        });

        // Menampilan Album
        $album  = GalleryPosts::orderBy('created_at', 'DESC');
        $album = $album->whereHas('author', function ($query) use ($id){
            $query->where('user_id', '=',  $id);
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
            'bkol/minisite/perusahaan/perusahaan-detail.twig',
            array(
              "post" => $post,
              "jobs" => $jobs->get(),
              "berita" => $berita->get(),
              "agenda" => $agenda->get(),
              "album" => $album->get(),
              'gallery' => $gallery
            )
        );
    }
}
