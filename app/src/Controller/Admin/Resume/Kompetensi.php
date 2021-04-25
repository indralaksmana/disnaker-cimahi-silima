<?php

namespace App\Controller\Admin\Resume;
use App\Controller\Controller;
use Carbon\Carbon;
use App\Library\Pekerjaan as B;
use App\Library\VideoParser as VP;
use Psr\Container\ContainerInterface;
use App\Model\KompetensiPosts;
use App\Model\JenisKompetensiModel;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Kompetensi extends Controller
{

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function kompetensi(Request $request, Response $response)
    {


        $posts = KompetensiPosts::get();

        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $posts = $posts->where('user_id', $this->auth->check()->id);
        }

        $jks = JenisKompetensiModel::get();

        return $this->view->render(
            $response,
            'bkol/dashboard/resume/kompetensi.twig',
            array(
                "posts" => $posts,
                'jks' => $jks
            )
        );
    }



    public function add(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('resume.create', 'dashboard', $this->config['resume-enabled'])) {
            return $check;
        }

        if ($request->isPost()) {
            $this->validateFields();
            if ($this->validator->isValid()) {
                if ( $this->_updatePost() ) {
                    $this->flash('success', 'Data berhasil disimpan.');
                    return $this->redirect($this->container->response, 'resume-kompetensi');
                }
            }
            $this->flash('danger', 'Data gagal disimpan.');
        }

        $jks = JenisKompetensiModel::get();

        return $this->view->render(
            $response,
            'bkol/dashboard/resume/kompetensi_form.twig',
            array(
                'form_state' => 'add',
                "jks" => $jks
            )
        );
    }

    // Edit Blog Post
    public function edit(Request $request, Response $response, $postId)
    {

        //die(var_dump($request->getAttribute('route')));
        if ($check = $this->sentinel->hasPerm('resume.update', 'dashboard', $this->config['resume-enabled'])) {
            return $check;
        }

        $post = KompetensiPosts::where('id', $postId)->first();

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'resume-kompetensi');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'resume-kompetensi');
        }

        if ($request->isPost()) {
            $this->validateFields();
            if ($this->validator->isValid()) {
                if ( $this->_updatePost( $post ) ) {
                    $this->flash('success', 'Data berhasil disimpan.');
                    return $this->redirect($this->container->response, 'resume-kompetensi');
                }
            } else {

            }
        }

        $jks = JenisKompetensiModel::get();
        return $this->view->render(
            $response,
            'bkol/dashboard/resume/kompetensi_form.twig',
            array(
                "post" => $post,
                'jks' => $jks,
                'form_state' => 'edit'
            )
        );
    }


    // Delete Blog Post
    public function delete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('resume.delete', 'dashboard', $this->config['resume-enabled'])) {
            return $check;
        }

        $post = KompetensiPosts::find($request->getParam('post_id'));

        if (!$post) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'resume-kompetensi');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $post->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to delete that post.');
            return $this->redirect($response, 'resume-kompetensi');
        }

        if ($post->delete()) {
            $this->flash('success', 'Post was deleted successfully.');
            return $this->redirect($response, 'resume-kompetensi');
        }

        $this->flash('danger', 'There was an error deleting your post.');
        return $this->redirect($response, 'resume-kompetensi');
    }

    public function validateFields() 
    {
        //Validate Data
        $validateData = array(
            'nama_lsp' => array(
                'rules' => V::length(6, 255),
                    'messages' => array(
                    'length' => 'Must be between 6 and 255 characters.',
                    // 'alnum' => 'Invalid Characters Only \',.?!@#$%&*()_ are allowed.'
                )
            ),
            'no_registrasi' => array(
                'rules' => V::length(6, 255),
                    'messages' => array(
                    'length' => 'Must be between 6 and 255 characters.',
                    // 'alnum' => 'Invalid Characters Only \',.?!@#$%&*()_ are allowed.'
                )
            ),
            'jurusan_sertifikat' => array(
                'rules' => V::length(6, 255),
                    'messages' => array(
                    'length' => 'Must be between 6 and 255 characters.',
                    // 'alnum' => 'Invalid Characters Only \',.?!@#$%&*()_ are allowed.'
                )
            ),
            'tanggal_terbit' => array(
                'rules' => V::notBlank(),
                    'messages' => array(
                    'length' => 'Must be between 6 and 255 characters.',
                    // 'alnum' => 'Invalid Characters Only \',.?!@#$%&*()_ are allowed.'
                )
            ),
            'no_sertifikat' => array(
                'rules' => V::length(6, 255),
                    'messages' => array(
                    'length' => 'Must be between 6 and 255 characters.',
                    // 'alnum' => 'Invalid Characters Only \',.?!@#$%&*()_ are allowed.'
                )
            ),
            'jenis_kompetensi_id' => array(
                'rules' => V::numeric(),
                    'messages' => array(
                    'numeric' => 'Only numeric number are allowed.',
                    // 'alnum' => 'Invalid Characters Only \',.?!@#$%&*()_ are allowed.'
                )
            )
        );
        $this->validator->validate($this->container->request, $validateData);
    }

    protected function _updatePost( $post = null )
    {
        $r = $this->container->request->getParams();
        if ( ! $post ) {
            $post = new KompetensiPosts;
        }
        $post->nama_lsp = $r['nama_lsp'];
        $post->user_id = $this->container->auth->check()->id;
        $post->jenis_kompetensi_id = $r['jenis_kompetensi_id'];
        $post->jurusan_sertifikat = $r['jurusan_sertifikat'];
        $post->no_sertifikat = $r['no_sertifikat'];
        $post->no_registrasi = $r['no_registrasi'];
        $post->masa_berlaku = $r['masa_berlaku'];
        $post->tanggal_terbit = $r['tanggal_terbit'];
        $post->diterbitkan_oleh = $r['diterbitkan_oleh'];
        
        $post->status = 1;
        return $post->save();
    }


}
