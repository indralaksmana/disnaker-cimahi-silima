<?php

namespace App\Controller\Dashboard\Berita;

use Carbon\Carbon;
use App\Controller\Controller;
use App\Library\Blog as B;
use App\Library\VideoParser as VP;
use Psr\Container\ContainerInterface;
use App\Model\BlogCategories;
use App\Model\BlogTags;
use App\Model\BlogPosts;
use App\Model\BlogPostsComments;
use App\Model\BlogPostsReplies;
use App\Model\BlogPostsTags;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class BeritaDashboard extends Controller
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $blogUtils = new B($this->container);
        $this->blogUtils = $blogUtils;
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function index(Request $request, Response $response)
    {
        $posts = BlogPosts::with('category');

        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            
            if ( $this->auth->check()->inRole('lpk') ) {
                $posts = $posts->whereHas('author', function($q){
                    $q->where('user_id', $this->auth->check()->id);
                });
            } elseif ( $this->auth->check()->inRole('bkk') ){
                $posts = $posts->whereHas('author', function($q){
                    $q->where('user_id', $this->auth->check()->id);
                });
            } elseif ( $this->auth->check()->inRole('perguruan-tinggi') ){
                $posts = $posts->whereHas('author', function($q){
                    $q->where('user_id', $this->auth->check()->id);
                });
            } else {
                $posts = $posts->where('user_id', $this->auth->check()->id);
            }
        }   
        return $this->view->render(
            $response,
            'bkol/dashboard/berita/berita.twig',
            array(
                "posts" => $posts->get()
            )
        );
    }

    public function blogAdd(Request $request, Response $response)
    {
        // if ($check = $this->sentinel->hasPerm('blog.create', 'dashboard', $this->config['blog-enabled'])) {
        //     return $check;
        // }
        if ($request->isPost()) {
            if ($this->blogUtils->addBerita()) {
                if ($this->auth->check()->inRole('bkk')) {
                    return $this->redirect($this->container->response, 'bkk-dashboard-berita');
                } 
                if ($this->auth->check()->inRole('lpk')) {
                    return $this->redirect($this->container->response, 'lpk-dashboard-berita');
                } 
                if ($this->auth->check()->inRole('companies')) {
                    return $this->redirect($this->container->response, 'perusahaan-dashboard-berita');
                } 
                if ($this->auth->check()->inRole('perguruan-tinggi')) {
                    return $this->redirect($this->container->response, 'pt-dashboard-berita');
                }
            }
        }
        $titlepage = 'Buat Berita';

        return $this->view->render(
            $response,
            'bkol/dashboard/berita/add-edit.twig',
            array(
                "categories" => BlogCategories::get(),
                "tags" => BlogTags::get(),
                "TitlePage" => $titlepage
            )
        );
    }

    // Edit Blog Post
    public function blogEdit(Request $request, Response $response, $postId)
    {

        $post = BlogPosts::where('id', $postId)->with('category')->with('tags')->first();
        $titlepage = 'Edit Berita';
        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'admin-blog');
        }

        if ($request->isPost()) {
            if ($this->blogUtils->updateBerita($post->id)) {
                if ($this->auth->check()->inRole('bkk')) {
                    return $this->redirect($this->container->response, 'bkk-dashboard-berita');
                } 
                if ($this->auth->check()->inRole('lpk')) {
                    return $this->redirect($this->container->response, 'lpk-dashboard-berita');
                } 
                if ($this->auth->check()->inRole('companies')) {
                    return $this->redirect($this->container->response, 'perusahaan-dashboard-berita');
                } 
                if ($this->auth->check()->inRole('perguruan-tinggi')) {
                    return $this->redirect($this->container->response, 'pt-dashboard-berita');
                } 
            }
        }

        $currentTags = $post->tags->pluck('id');

        return $this->view->render(
            $response,
            'bkol/dashboard/berita/add-edit.twig',
            array(
                "post" => $post->toArray(),
                "categories" => BlogCategories::get(),
                "tags" => BlogTags::get(),
                "currentTags" => $currentTags,
                "TitlePage" => $titlepage
            )
        );
    }

    // Publish Blog Post
    public function blogPublish(Request $request, Response $response)
    {
 
        $post = BlogPosts::find($request->getParam('post_id'));
        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            if ($this->auth->check()->inRole('bkk')) {
                return $this->redirect($response, 'bkk-dashboard-berita');
            } 
            if ($this->auth->check()->inRole('lpk')) {
                return $this->redirect($response, 'lpk-dashboard-berita');
            } 
            if ($this->auth->check()->inRole('companies')) {
                return $this->redirect($response, 'perusahaan-dashboard-berita');
            } 
            if ($this->auth->check()->inRole('perguruan-tinggi')) {
                return $this->redirect($response, 'pt-dashboard-berita');
            }
        }



        if ($this->blogUtils->publish()) {
            $this->flash('success', 'Berita Berhasil Di Publikasikan');
            if ($this->auth->check()->inRole('bkk')) {
                return $this->redirect($response, 'bkk-dashboard-berita');
            } 
            if ($this->auth->check()->inRole('lpk')) {
                return $this->redirect($response, 'lpk-dashboard-berita');
            } 
            if ($this->auth->check()->inRole('companies')) {
                return $this->redirect($response, 'perusahaan-dashboard-berita');
            } 
            if ($this->auth->check()->inRole('perguruan-tinggi')) {
                return $this->redirect($response, 'pt-dashboard-berita');
            }
        }

        $this->flash('danger', 'There was an error publishing your post.');
        if ($this->auth->check()->inRole('bkk')) {
            return $this->redirect($response, 'bkk-dashboard-berita');
        } 
        if ($this->auth->check()->inRole('lpk')) {
            return $this->redirect($response, 'lpk-dashboard-berita');
        } 
        if ($this->auth->check()->inRole('companies')) {
            return $this->redirect($response, 'perusahaan-dashboard-berita');
        } 
        if ($this->auth->check()->inRole('perguruan-tinggi')) {
            return $this->redirect($response, 'pt-dashboard-berita');
        }
    }

    // Unpublish Blog Post
    public function blogUnpublish(Request $request, Response $response)
    {
        // if ($check = $this->sentinel->hasPerm('blog.update', 'dashboard', $this->config['blog-enabled'])) {
        //     return $check;
        // }

        $post = BlogPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            if ($this->auth->check()->inRole('bkk')) {
                return $this->redirect($response, 'bkk-dashboard-berita');
            } 
            if ($this->auth->check()->inRole('lpk')) {
                return $this->redirect($response, 'lpk-dashboard-berita');
            } 
            if ($this->auth->check()->inRole('companies')) {
                return $this->redirect($response, 'perusahaan-dashboard-berita');
            } 
            if ($this->auth->check()->inRole('perguruan-tinggi')) {
                return $this->redirect($response, 'pt-dashboard-berita');
            } 
        }

        if ($this->blogUtils->unpublish()) {
            $this->flash('success', 'Post was unpublished successfully.');
            if ($this->auth->check()->inRole('bkk')) {
                return $this->redirect($response, 'bkk-dashboard-berita');
            } 
            if ($this->auth->check()->inRole('lpk')) {
                return $this->redirect($response, 'lpk-dashboard-berita');
            } 
            if ($this->auth->check()->inRole('companies')) {
                return $this->redirect($response, 'perusahaan-dashboard-berita');
            } 
            if ($this->auth->check()->inRole('perguruan-tinggi')) {
                return $this->redirect($response, 'pt-dashboard-berita');
            }
        }

        $this->flash('danger', 'There was an error unpublishing your post.');
        if ($this->auth->check()->inRole('bkk')) {
            return $this->redirect($response, 'bkk-dashboard-berita');
        } 
        if ($this->auth->check()->inRole('lpk')) {
            return $this->redirect($response, 'lpk-dashboard-berita');
        } 
        if ($this->auth->check()->inRole('companies')) {
            return $this->redirect($response, 'perusahaan-dashboard-berita');
        } 
        if ($this->auth->check()->inRole('perguruan-tinggi')) {
            return $this->redirect($response, 'pt-dashboard-berita');
        }
    }

    // Delete Blog Post
    public function blogDelete(Request $request, Response $response)
    {


        $post = BlogPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            if ($this->auth->check()->inRole('bkk')) {
                return $this->redirect($response, 'bkk-dashboard-berita');
            } 
            if ($this->auth->check()->inRole('lpk')) {
                return $this->redirect($response, 'lpk-dashboard-berita');
            } 
            if ($this->auth->check()->inRole('companies')) {
                return $this->redirect($response, 'perusahaan-dashboard-berita');
            } 
            if ($this->auth->check()->inRole('perguruan-tinggi')) {
                return $this->redirect($response, 'pt-dashboard-berita');
            } 
        }

        if ($this->blogUtils->delete()) {
            $this->flash('success', 'Post was deleted successfully.');
            if ($this->auth->check()->inRole('bkk')) {
                return $this->redirect($response, 'bkk-dashboard-berita');
            } 
            if ($this->auth->check()->inRole('lpk')) {
                return $this->redirect($response, 'lpk-dashboard-berita');
            } 
            if ($this->auth->check()->inRole('companies')) {
                return $this->redirect($response, 'perusahaan-dashboard-berita');
            } 
            if ($this->auth->check()->inRole('perguruan-tinggi')) {
                return $this->redirect($response, 'pt-dashboard-berita');
            }
        }

        $this->flash('danger', 'There was an error deleting your post.');
        if ($this->auth->check()->inRole('bkk')) {
            return $this->redirect($response, 'bkk-dashboard-berita');
        } 
        if ($this->auth->check()->inRole('lpk')) {
            return $this->redirect($response, 'lpk-dashboard-berita');
        } 
        if ($this->auth->check()->inRole('companies')) {
            return $this->redirect($response, 'perusahaan-dashboard-berita');
        } 
        if ($this->auth->check()->inRole('perguruan-tinggi')) {
            return $this->redirect($response, 'pt-dashboard-berita');
        }
    }
}
