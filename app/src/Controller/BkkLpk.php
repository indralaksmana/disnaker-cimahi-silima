<?php

namespace App\Controller;

use Carbon\Carbon;
use Psr\Container\ContainerInterface;
use App\Model\BkkModel;
use App\Model\LpkModel;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class BkkLpk extends Controller
{


    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function bkk(Request $request, Response $response)
    {
        $bkk = BkkModel::get();
        $lpk = LpkModel::get();
        return $this->view->render(
            $response,
            'bkol/bkk-lpk/bkk-lpk.twig',
            array(
              "bkk" => $bkk,
              "lpk" => $lpk
            )
        );
    }

    // Blog Post
    public function BkkDetail(Request $request, Response $response)
    {
        $args =  $request->getAttribute('route')->getArguments();

        $post = BkkModel::where('id', $args['id'])->first();
        $imgs = explode('#', $post->gallerys_photo);
        if (!$post) {
            $this->flash('danger', 'That blog post cound not be found.');
            return $this->redirect($response, 'bkk');
        }
        return $this->view->render(
            $response,
            'bkol/bkk-lpk/bkk-detail.twig',
            array(
              "post" => $post,
              "imgs" => $imgs
            )
        );
    }

    public function LpkDetail(Request $request, Response $response)
    {
        $args =  $request->getAttribute('route')->getArguments();

        $post = LpkModel::where('id', $args['id'])->first();
        $imgs = explode('#', $post->gallerys_photo);
        if (!$post) {
            $this->flash('danger', 'That blog post cound not be found.');
            return $this->redirect($response, 'bkk');
        }
        return $this->view->render(
            $response,
            'bkol/bkk-lpk/lpk-detail.twig',
            array(
              "post" => $post,
              "imgs" => $imgs
            )
        );
    }
}
