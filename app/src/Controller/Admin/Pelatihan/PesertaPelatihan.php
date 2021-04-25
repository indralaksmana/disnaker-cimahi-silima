<?php


namespace App\Controller\Admin\Pelatihan;
use App\Controller\Controller;
use Carbon\Carbon;
use App\Library\VideoParser as VP;
use App\Model\PelatihanCategories;
use App\Model\PelatihanTags;
use App\Model\PelatihanPosts;
use App\Model\PelatihanDaftarPeserta;
use App\Model\BlogPostsReplies;
use App\Model\PelatihanPostsTags;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class PesertaPelatihan extends Controller
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function peserta(Request $request, Response $response)
    {
        // if ($check = $this->sentinel->hasPerm('blog.view', 'dashboard', $this->config['blog-enabled'])) {
        //     return $check;
        // }

        $user = $this->auth->check()->id;
        $post = PelatihanPosts::where('id', $postId)->with('category')->with('tags')->first();
        $peserta = PelatihanDaftarPeserta::with([
                    'post' => function ($query) {
                        $query->select('id', 'title');
                    }
                ]);
        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $peserta = $peserta->whereHas(
                'post',
                function ($query) use ($user) {
                    $query->where('user_id', '=', $user);
                }
            );
        }
        return $this->view->render(
            $response,
            'dashboard/pelatihan/peserta-pelatihan.twig',
            array(
                "peserta" => $peserta->get(),
                "post" => $post->get()
            )
        );
    }
}
