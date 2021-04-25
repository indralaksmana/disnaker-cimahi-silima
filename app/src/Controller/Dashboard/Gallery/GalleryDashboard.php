<?php

namespace App\Controller\Dashboard\Gallery;
use App\Controller\Controller;

use Carbon\Carbon;
use App\Library\Gallery as B;
use App\Library\VideoParser as VP;
use Psr\Container\ContainerInterface;
use App\Model\GalleryCategories;
use App\Model\GalleryPosts;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class GalleryDashboard extends Controller
{

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $galleryUtils = new B($this->container);
        $this->galleryUtils = $galleryUtils;
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function gallery(Request $request, Response $response)
    {


        $posts = GalleryPosts::with('category');

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
            'bkol/dashboard/gallery/gallery.twig',
            array(
                "categories" => GalleryCategories::get(),
                "posts" => $posts->get()
            )
        );
    }


    public function galleryAdd(Request $request, Response $response)
    {

        if ($request->isPost()) {
            if ($this->galleryUtils->addPost()) {
                if ($this->auth->check()->inRole('bkk')) {
                    return $this->redirect($this->container->response, 'bkk-dashboard-gallery');
                } 
                if ($this->auth->check()->inRole('lpk')) {
                    return $this->redirect($this->container->response, 'lpk-dashboard-gallery');
                } 
                if ($this->auth->check()->inRole('companies')) {
                    return $this->redirect($this->container->response, 'perusahaan-dashboard-gallery');
                } 
                if ($this->auth->check()->inRole('perguruan-tinggi')) {
                    return $this->redirect($this->container->response, 'pt-dashboard-gallery');
                }
            }
        }
        $titlepage = 'Tambah Album';
        return $this->view->render(
            $response,
            'bkol/dashboard/gallery/add-edit.twig',
            array(
                "categories" => GalleryCategories::get(),
                "TitlePage" => $titlepage,
            )
        );
    }

    // Edit Blog Post
    public function galleryEdit(Request $request, Response $response, $postId)
    {


        $post = GalleryPosts::where('id', $postId)->with('category')->first();
        $titlepage = 'Edit Album';

        $imgs = explode('#', $post->gallerys_photo);
        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            if ($this->auth->check()->inRole('bkk')) {
                return $this->redirect($response, 'bkk-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('lpk')) {
                return $this->redirect($response, 'lpk-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('companies')) {
                return $this->redirect($response, 'perusahaan-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('perguruan-tinggi')) {
                return $this->redirect($response, 'pt-dashboard-gallery');
            }
        }


        if ($request->isPost()) {
            if ($this->galleryUtils->updatePost($post->id)) {
                if ($this->auth->check()->inRole('bkk')) {
                    return $this->redirect($this->container->response, 'bkk-dashboard-gallery');
                } 
                if ($this->auth->check()->inRole('lpk')) {
                    return $this->redirect($this->container->response, 'lpk-dashboard-gallery');
                } 
                if ($this->auth->check()->inRole('companies')) {
                    return $this->redirect($this->container->response, 'perusahaan-dashboard-gallery');
                } 
                if ($this->auth->check()->inRole('perguruan-tinggi')) {
                    return $this->redirect($this->container->response, 'pt-dashboard-gallery');
                }
            }
        }

        return $this->view->render(
            $response,
            'bkol/dashboard/gallery/add-edit.twig',
            array(
                "post" => $post->toArray(),
                "categories" => GalleryCategories::get(),
                "imgs" => $imgs,
                "TitlePage" => $titlepage
            )
        );
    }

    // Publish Blog Post
    public function galleryPublish(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('gallery.update', 'dashboard', $this->config['gallery-enabled'])) {
            return $check;
        }

        $post = GalleryPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            if ($this->auth->check()->inRole('bkk')) {
                return $this->redirect($response, 'bkk-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('lpk')) {
                return $this->redirect($response, 'lpk-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('companies')) {
                return $this->redirect($response, 'perusahaan-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('perguruan-tinggi')) {
                return $this->redirect($response, 'pt-dashboard-gallery');
            }
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            if ($this->auth->check()->inRole('bkk')) {
                return $this->redirect($response, 'bkk-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('lpk')) {
                return $this->redirect($response, 'lpk-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('companies')) {
                return $this->redirect($response, 'perusahaan-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('perguruan-tinggi')) {
                return $this->redirect($response, 'pt-dashboard-gallery');
            };
        }

        if ($this->galleryUtils->publish()) {
            $this->flash('success', 'Post was published successfully.');
            if ($this->auth->check()->inRole('bkk')) {
                return $this->redirect($response, 'bkk-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('lpk')) {
                return $this->redirect($response, 'lpk-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('companies')) {
                return $this->redirect($response, 'perusahaan-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('perguruan-tinggi')) {
                return $this->redirect($response, 'pt-dashboard-gallery');
            }
        }

        $this->flash('danger', 'There was an error publishing your post.');
        if ($this->auth->check()->inRole('bkk')) {
            return $this->redirect($response, 'bkk-dashboard-gallery');
        } 
        if ($this->auth->check()->inRole('lpk')) {
            return $this->redirect($response, 'lpk-dashboard-gallery');
        } 
        if ($this->auth->check()->inRole('companies')) {
            return $this->redirect($response, 'perusahaan-dashboard-gallery');
        } 
        if ($this->auth->check()->inRole('perguruan-tinggi')) {
            return $this->redirect($response, 'pt-dashboard-gallery');
        }
    }

    // Unpublish Blog Post
    public function galleryUnpublish(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('gallery.update', 'dashboard', $this->config['gallery-enabled'])) {
            return $check;
        }

        $post = GalleryPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            if ($this->auth->check()->inRole('bkk')) {
                return $this->redirect($response, 'bkk-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('lpk')) {
                return $this->redirect($response, 'lpk-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('companies')) {
                return $this->redirect($response, 'perusahaan-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('perguruan-tinggi')) {
                return $this->redirect($response, 'pt-dashboard-gallery');
            }
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            if ($this->auth->check()->inRole('bkk')) {
                return $this->redirect($response, 'bkk-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('lpk')) {
                return $this->redirect($response, 'lpk-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('companies')) {
                return $this->redirect($response, 'perusahaan-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('perguruan-tinggi')) {
                return $this->redirect($response, 'pt-dashboard-gallery');
            }
        }

        if ($this->galleryUtils->unpublish()) {
            $this->flash('success', 'Post was unpublished successfully.');
            if ($this->auth->check()->inRole('bkk')) {
                return $this->redirect($response, 'bkk-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('lpk')) {
                return $this->redirect($response, 'lpk-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('companies')) {
                return $this->redirect($response, 'perusahaan-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('perguruan-tinggi')) {
                return $this->redirect($response, 'pt-dashboard-gallery');
            };
        }

        $this->flash('danger', 'There was an error unpublishing your post.');
        if ($this->auth->check()->inRole('bkk')) {
            return $this->redirect($response, 'bkk-dashboard-gallery');
        } 
        if ($this->auth->check()->inRole('lpk')) {
            return $this->redirect($response, 'lpk-dashboard-gallery');
        } 
        if ($this->auth->check()->inRole('companies')) {
            return $this->redirect($response, 'perusahaan-dashboard-gallery');
        } 
        if ($this->auth->check()->inRole('perguruan-tinggi')) {
            return $this->redirect($response, 'pt-dashboard-gallery');
        }
    }

    // Delete Blog Post
    public function galleryDelete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('gallery.delete', 'dashboard', $this->config['gallery-enabled'])) {
            return $check;
        }

        $post = GalleryPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            if ($this->auth->check()->inRole('bkk')) {
                return $this->redirect($response, 'bkk-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('lpk')) {
                return $this->redirect($response, 'lpk-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('companies')) {
                return $this->redirect($response, 'perusahaan-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('perguruan-tinggi')) {
                return $this->redirect($response, 'pt-dashboard-gallery');
            }
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to delete that post.');
            if ($this->auth->check()->inRole('bkk')) {
                return $this->redirect($response, 'bkk-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('lpk')) {
                return $this->redirect($response, 'lpk-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('companies')) {
                return $this->redirect($response, 'perusahaan-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('perguruan-tinggi')) {
                return $this->redirect($response, 'pt-dashboard-gallery');
            }
        }

        if ($this->galleryUtils->delete()) {
            $this->flash('success', 'Galleri Foto Berhasil Di Hapus');
            if ($this->auth->check()->inRole('bkk')) {
                return $this->redirect($response, 'bkk-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('lpk')) {
                return $this->redirect($response, 'lpk-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('companies')) {
                return $this->redirect($response, 'perusahaan-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('perguruan-tinggi')) {
                return $this->redirect($response, 'pt-dashboard-gallery');
            }
        }

        $this->flash('danger', 'Galleri Foto Gagal Di Hapus');
        if ($this->auth->check()->inRole('bkk')) {
            return $this->redirect($response, 'bkk-dashboard-gallery');
        } 
        if ($this->auth->check()->inRole('lpk')) {
            return $this->redirect($response, 'lpk-dashboard-gallery');
        } 
        if ($this->auth->check()->inRole('companies')) {
            return $this->redirect($response, 'perusahaan-dashboard-gallery');
        } 
        if ($this->auth->check()->inRole('perguruan-tinggi')) {
            return $this->redirect($response, 'pt-dashboard-gallery');
        }
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function galleryPreview(Request $request, Response $response, $slug)
    {
        if ($check = $this->sentinel->hasPerm('gallery.view', 'dashboard', $this->config['gallery-enabled'])) {
            return $check;
        }

        $post = GalleryPosts::where('slug', '=', $slug)->first();

        if (!$this->auth->check()->inRole('manager') &&
            !$this->auth->check()->inRole('admin') &&
            $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to preview that post.');
            if ($this->auth->check()->inRole('bkk')) {
                return $this->redirect($response, 'bkk-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('lpk')) {
                return $this->redirect($response, 'lpk-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('companies')) {
                return $this->redirect($response, 'perusahaan-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('perguruan-tinggi')) {
                return $this->redirect($response, 'pt-dashboard-gallery');
            }
        }

        $categories = GalleryCategories::where('status', 1)->get();


        if ($post) {
            $this->flash('danger', 'That blog post does not exist.');
            return $this->redirect($response, 'bkk-dashboard-gallery');
            if ($this->auth->check()->inRole('bkk')) {
                return $this->redirect($response, 'bkk-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('lpk')) {
                return $this->redirect($response, 'lpk-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('companies')) {
                return $this->redirect($response, 'perusahaan-dashboard-gallery');
            } 
            if ($this->auth->check()->inRole('perguruan-tinggi')) {
                return $this->redirect($response, 'pt-dashboard-gallery');
            }
        }

        return $this->view->render(
            $response,
            'dashboard/App/blog-post.twig',
            array(
                "blogPost" => $post[0]->toArray(),
                'GalleryCategories' => $categories
            )
        );
    }
}
