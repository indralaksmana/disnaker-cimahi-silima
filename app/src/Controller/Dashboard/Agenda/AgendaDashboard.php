<?php

namespace App\Controller\Dashboard\Agenda;
use App\Controller\Controller;
use Carbon\Carbon;
use App\Library\Agenda as A;
use App\Library\VideoParser as VP;
use Psr\Container\ContainerInterface;
use App\Model\AgendaPosts;
use App\Model\AgendaCategories;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class AgendaDashboard extends Controller
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $agendaUtils = new A($this->container);
        $this->agendaUtils = $agendaUtils;
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function agenda(Request $request, Response $response)
    {
        $posts = AgendaPosts::with('category');
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
            'bkol/dashboard/agenda/agenda.twig',
            array(
                "categories" => AgendaCategories::get(),
                "posts" => $posts->get()
            )
        );
    }
    public function agendaAdd(Request $request, Response $response)
    {
        if ($request->isPost()) {
            if ($this->agendaUtils->addPost()) {
                if ($this->auth->check()->inRole('bkk')) {
                    return $this->redirect($this->container->response, 'bkk-dashboard-agenda');
                } 
                if ($this->auth->check()->inRole('lpk')) {
                    return $this->redirect($this->container->response, 'lpk-dashboard-agenda');
                } 
                if ($this->auth->check()->inRole('companies')) {
                    return $this->redirect($this->container->response, 'perusahaan-dashboard-agenda');
                } 
                if ($this->auth->check()->inRole('perguruan-tinggi')) {
                    return $this->redirect($this->container->response, 'pt-dashboard-agenda');
                }
            }
        }
        $titlePage = 'Tambah Agenda Kegiatan';

        return $this->view->render(
            $response,
            'bkol/dashboard/agenda/add-edit.twig',
            array(
                "categories" => AgendaCategories::get(),
                'TitlePage' => $titlePage
            )
        );
    }

    // Edit Blog Post
    public function agendaEdit(Request $request, Response $response, $postId)
    {

        
        $post = AgendaPosts::where('id', $postId)->with('category')->first();

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'dashboard-agenda');
        }

        

        if ($request->isPost()) {
            if ($this->agendaUtils->updatePost($post->id)) {
                if ($this->auth->check()->inRole('bkk')) {
                    return $this->redirect($this->container->response, 'bkk-dashboard-agenda');
                } 
                if ($this->auth->check()->inRole('lpk')) {
                    return $this->redirect($this->container->response, 'lpk-dashboard-agenda');
                } 
                if ($this->auth->check()->inRole('companies')) {
                    return $this->redirect($this->container->response, 'perusahaan-dashboard-agenda');
                } 
                if ($this->auth->check()->inRole('perguruan-tinggi')) {
                    return $this->redirect($this->container->response, 'pt-dashboard-agenda');
                } 
            }
        }

        return $this->view->render(
            $response,
            'bkol/dashboard/agenda/add-edit.twig',
            array(
                "post" => $post->toArray(),
                "categories" => AgendaCategories::get()
            )
        );
    }

    // Publish Blog Post
    public function agendaPublish(Request $request, Response $response)
    {
        /*if ($check = $this->sentinel->hasPerm('agenda.update', 'dashboard', $this->config['agenda-enabled'])) {
            return $check;
        }*/

        $post = AgendaPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            if ($this->auth->check()->inRole('bkk')) {
                return $this->redirect($response, 'bkk-dashboard-agenda');
            } 
            if ($this->auth->check()->inRole('lpk')) {
                return $this->redirect($response, 'lpk-dashboard-agenda');
            } 
            if ($this->auth->check()->inRole('companies')) {
                return $this->redirect($response, 'perusahaan-dashboard-agenda');
            } 
            if ($this->auth->check()->inRole('perguruan-tinggi')) {
                return $this->redirect($response, 'pt-dashboard-agenda');
            }
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            if ($this->auth->check()->inRole('bkk')) {
                return $this->redirect($response, 'bkk-dashboard-agenda');
            } 
            if ($this->auth->check()->inRole('lpk')) {
                return $this->redirect($response, 'lpk-dashboard-agenda');
            } 
            if ($this->auth->check()->inRole('companies')) {
                return $this->redirect($response, 'perusahaan-dashboard-agenda');
            } 
            if ($this->auth->check()->inRole('perguruan-tinggi')) {
                return $this->redirect($response, 'pt-dashboard-agenda');
            }
        }

        if ($this->agendaUtils->publish()) {
            $this->flash('success', 'Post was published successfully.');
            if ($this->auth->check()->inRole('bkk')) {
                return $this->redirect($response, 'bkk-dashboard-agenda');
            } 
            if ($this->auth->check()->inRole('lpk')) {
                return $this->redirect($response, 'lpk-dashboard-agenda');
            } 
            if ($this->auth->check()->inRole('companies')) {
                return $this->redirect($response, 'perusahaan-dashboard-agenda');
            } 
            if ($this->auth->check()->inRole('perguruan-tinggi')) {
                return $this->redirect($response, 'pt-dashboard-agenda');
            }
        }

        $this->flash('danger', 'There was an error publishing your post.');
        if ($this->auth->check()->inRole('bkk')) {
            return $this->redirect($response, 'bkk-dashboard-agenda');
        } 
        if ($this->auth->check()->inRole('lpk')) {
            return $this->redirect($response, 'lpk-dashboard-agenda');
        } 
        if ($this->auth->check()->inRole('companies')) {
            return $this->redirect($response, 'perusahaan-dashboard-agenda');
        } 
        if ($this->auth->check()->inRole('perguruan-tinggi')) {
            return $this->redirect($response, 'pt-dashboard-agenda');
        }
    }

    // Unpublish Blog Post
    public function agendaUnpublish(Request $request, Response $response)
    {
        /*if ($check = $this->sentinel->hasPerm('agenda.update', 'dashboard', $this->config['agenda-enabled'])) {
            return $check;
        }*/

        $post = AgendaPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            if ($this->auth->check()->inRole('bkk')) {
                return $this->redirect($response, 'bkk-dashboard-agenda');
            } 
            if ($this->auth->check()->inRole('lpk')) {
                return $this->redirect($response, 'lpk-dashboard-agenda');
            } 
            if ($this->auth->check()->inRole('companies')) {
                return $this->redirect($response, 'perusahaan-dashboard-agenda');
            } 
            if ($this->auth->check()->inRole('perguruan-tinggi')) {
                return $this->redirect($response, 'pt-dashboard-agenda');
            }
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            if ($this->auth->check()->inRole('bkk')) {
                return $this->redirect($response, 'bkk-dashboard-agenda');
            } 
            if ($this->auth->check()->inRole('lpk')) {
                return $this->redirect($response, 'lpk-dashboard-agenda');
            } 
            if ($this->auth->check()->inRole('companies')) {
                return $this->redirect($response, 'perusahaan-dashboard-agenda');
            } 
            if ($this->auth->check()->inRole('perguruan-tinggi')) {
                return $this->redirect($response, 'pt-dashboard-agenda');
            }
        }

        if ($this->agendaUtils->unpublish()) {
            $this->flash('success', 'Post was unpublished successfully.');
            if ($this->auth->check()->inRole('bkk')) {
                return $this->redirect($response, 'bkk-dashboard-agenda');
            } 
            if ($this->auth->check()->inRole('lpk')) {
                return $this->redirect($response, 'lpk-dashboard-agenda');
            } 
            if ($this->auth->check()->inRole('companies')) {
                return $this->redirect($response, 'perusahaan-dashboard-agenda');
            } 
            if ($this->auth->check()->inRole('perguruan-tinggi')) {
                return $this->redirect($response, 'pt-dashboard-agenda');
            }
        }

        $this->flash('danger', 'There was an error unpublishing your post.');
        if ($this->auth->check()->inRole('bkk')) {
            return $this->redirect($response, 'bkk-dashboard-agenda');
        } 
        if ($this->auth->check()->inRole('lpk')) {
            return $this->redirect($response, 'lpk-dashboard-agenda');
        } 
        if ($this->auth->check()->inRole('companies')) {
            return $this->redirect($response, 'perusahaan-dashboard-agenda');
        } 
        if ($this->auth->check()->inRole('perguruan-tinggi')) {
            return $this->redirect($response, 'pt-dashboard-agenda');
        }
    }

    // Delete Blog Post
    public function agendaDelete(Request $request, Response $response)
    {
        /*if ($check = $this->sentinel->hasPerm('agenda.delete', 'dashboard', $this->config['agenda-enabled'])) {
            return $check;
        }*/

        $post = AgendaPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            if ($this->auth->check()->inRole('bkk')) {
                return $this->redirect($response, 'bkk-dashboard-agenda');
            } 
            if ($this->auth->check()->inRole('lpk')) {
                return $this->redirect($response, 'lpk-dashboard-agenda');
            } 
            if ($this->auth->check()->inRole('companies')) {
                return $this->redirect($response, 'perusahaan-dashboard-agenda');
            } 
            if ($this->auth->check()->inRole('perguruan-tinggi')) {
                return $this->redirect($response, 'pt-dashboard-agenda');
            }
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to delete that post.');
            if ($this->auth->check()->inRole('bkk')) {
                return $this->redirect($response, 'bkk-dashboard-agenda');
            } 
            if ($this->auth->check()->inRole('lpk')) {
                return $this->redirect($response, 'lpk-dashboard-agenda');
            } 
            if ($this->auth->check()->inRole('companies')) {
                return $this->redirect($response, 'perusahaan-dashboard-agenda');
            } 
            if ($this->auth->check()->inRole('perguruan-tinggi')) {
                return $this->redirect($response, 'pt-dashboard-agenda');
            }
        }

        if ($this->agendaUtils->delete()) {
            $this->flash('success', 'Post was deleted successfully.');
            if ($this->auth->check()->inRole('bkk')) {
                return $this->redirect($response, 'bkk-dashboard-agenda');
            } 
            if ($this->auth->check()->inRole('lpk')) {
                return $this->redirect($response, 'lpk-dashboard-agenda');
            } 
            if ($this->auth->check()->inRole('companies')) {
                return $this->redirect($response, 'perusahaan-dashboard-agenda');
            } 
            if ($this->auth->check()->inRole('perguruan-tinggi')) {
                return $this->redirect($response, 'pt-dashboard-agenda');
            }
        }

        $this->flash('danger', 'There was an error deleting your post.');
        if ($this->auth->check()->inRole('bkk')) {
            return $this->redirect($response, 'bkk-dashboard-agenda');
        } 
        if ($this->auth->check()->inRole('lpk')) {
            return $this->redirect($response, 'lpk-dashboard-agenda');
        } 
        if ($this->auth->check()->inRole('companies')) {
            return $this->redirect($response, 'perusahaan-dashboard-agenda');
        } 
        if ($this->auth->check()->inRole('perguruan-tinggi')) {
            return $this->redirect($response, 'pt-dashboard-agenda');
        }
    }

}
