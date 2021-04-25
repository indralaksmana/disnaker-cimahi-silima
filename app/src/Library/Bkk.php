<?php

namespace App\Library;

use Carbon\Carbon;
use App\Library\Utils;
use App\Model\BkkModel;
use App\Model\Users as U;
use Psr\Container\ContainerInterface;
use Respect\Validation\Validator as V;
use App\Library\Email as E;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class Bkk extends Library
{
    protected $categoryId;
    protected $slug;
    protected $parsedTags;
    protected $videoProvider;
    protected $videoId;
    protected $publishAt;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->slug = null;
        $this->publishAt = Carbon::now();
    }

    public function addPost()
    {
        $r = $this->container->request->getParams();

        $this->validateUsers();

        if ($this->validator->isValid()) {
            
            if ( $this->update_user( $post->id ) ) {
                $this->container->flash->addMessage('success', 'Data Berhasil Di Simpan');
                return true;
            }
        }
        $this->container->flash->addMessageNow('danger', 'Data Gagal Di Simpan');
        return false;
    }


    public function validateFields() 
    {
        //Validate Data
        $validateData = array(
            'nama_bkk' => array(
                'rules' => V::length(6, 255),
                    'messages' => array(
                    'length' => 'Must be between 6 and 255 characters.',
                    // 'alnum' => 'Invalid Characters Only \',.?!@#$%&*()_ are allowed.'
                )
            ),
            'alamat' => array(
                'rules' => V::length(6, 255),
                    'messages' => array(
                    'length' => 'Must be between 6 and 255 characters.',
                    // 'alnum' => 'Invalid Characters Only \',.?!@#$%&*()_ are allowed.'
                )
            ),
            'no_telp' => array(
                'rules' => V::numeric(),
                    'messages' => array(
                    'numeric' => 'Only numeric number are allowed.',
                    // 'alnum' => 'Invalid Characters Only \',.?!@#$%&*()_ are allowed.'
                )
            )
        );
        $this->validator->validate($this->container->request, $validateData);
    }

    public function validateUsers( $update_pwd = true )
    {
        $validateData = array(
            'email' => array(
                'rules' => V::notEmpty()->noWhitespace()->email(),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'noWhitespace' => 'Tidak boleh mengandung spasi.',
                    'email' => 'Harus alamat e-mail yang valid.'
                    )
            ),
            'username' => array(
                'rules' => V::notEmpty()->alnum()->length(2, 25),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'alnum' => 'huruf dan angka saja.',
                    'length' => "Harus antara 2 hingga 25 karakter."
                    )
            ),
            'fullname' => array(
                'rules' => V::notEmpty()->alnum('\'-')->length(2, 250),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'alnum' => 'Dapat berisi huruf, angka, \' tanda hubung.',
                    'length' => "Harus antara 2 hingga 250 karakter."
                    )
            ),
            'bkk_id' => array(
                'rules' => V::notEmpty(),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.'
                 )
            )
        );

        if ( $update_pwd ) {
            $validateData['password'] = array(
                'rules' => V::notEmpty()->noWhitespace()->length(6, 64),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'noWhitespace' => 'Tidak boleh mengandung spasi.',
                    'length' => "Harus antara 6 hingga 64 karakter."
                )
            );
            $validateData['password_confirm'] = array(
                'rules' => V::notEmpty()->equals($this->request->getParam('password')),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'equals' => 'Kata sandi harus cocok.'
                )
            );
        }

        $this->validator->validate($this->container->request, $validateData);
    }

    public function updatePost($postId)
    {
        $this->blogEdit = true;

        $r = $this->container->request->getParams();

        $this->validateUsers(false);

        if ($this->validator->isValid()) {
            if ( $this->update_user( $r['user_id'] ) ) {
                $this->container->flash->addMessage('success', 'Data Berhasil di Edit');
                return true;
            }
        }
        $this->container->flash->addMessageNow('danger', 'Data Gagal di Edit');
        return false;
    }
    
    public function delete()
    {
        $post = BkkModel::find($this->container->request->getParam('post_id'));

        if ($post) {
            if ($post->delete()) {
                return true;
            }
        }
        return false;
    }

    public function publish()
    {
        $post = BkkModel::find($this->container->request->getParam('post_id'));

        if ($post) {
            $post->status = 1;
            if ($post->save()) {
                return true;
            }
        }
        return false;
    }

    public function unpublish()
    {
        $post = BkkModel::find($this->container->request->getParam('post_id'));

        if ($post) {
            $post->status = 0;
            if ($post->save()) {
                return true;
            }
        }
        return false;
    }

    private function processSlug($postId = null)
    {
        $slug = Utils::slugify($this->container->request->getParam('nama_bkk'));
        $checkSlug = BkkModel::where('slug', $slug);
        if ($postId) {
            $checkSlug = $checkSlug->where('id', '!=', $postId);
        }
        if ($checkSlug->first()) {
            $this->validator->addError('title', 'Nama BKK Sudah Di Pakai');
            return false;
        }
        $this->slug = $slug;
        return true;
    }

    public function update_user( $user_id = null )
    {
        
        if ( $user_id ) {
            $user = U::find($user_id);
            if ( $user->email !== $this->container->request->getParam('email') ) {
                $user->email = $this->container->request->getParam('email');
            }
            $user->username = $this->container->request->getParam('username');
            $user->fullname = $this->container->request->getParam('fullname');
            $user->bkk_id = $this->container->request->getParam('bkk_id');
            $user->save();

        } else {
            $userDetails = [
                'email' => $this->container->request->getParam('email'),
                'username' => $this->container->request->getParam('username'),
                'password' => $this->container->request->getParam('password'),
                'fullname' => $this->container->request->getParam('fullname'),
                'bkk_id' => $this->container->request->getParam('bkk_id'),
                'permissions' => [
                    'user.delete' => 0
                ]
            ];

            $role = $this->container->auth->findRoleByName('bkk');
            if ($this->config['activation']) {

                $user = $this->container->auth->register($userDetails);
                $role->users()->attach($user);

                $activations = $this->container->auth->getActivationRepository();

                $activations = $activations->create($user);
                $code = $activations->code;

                $confirmUrl = "https://" . $this->config['domain-bkol'] . "/activate?code=" . $code . "&email=" . $user->email;

                $sendEmail = new E($this->container);
                $sendEmail = $sendEmail->sendTemplate(
                    array($user->id),
                    'activation',
                    array('confirm_url' => $confirmUrl)
                );

                if ($sendEmail['status'] == "error") {
                    $this->container->flash->addMessage(
                        'danger',
                        'Terjadi kesalahan saat mengirim email aktivasi Anda. Silakan hubungi contact support.'
                    );
                }
                $this->container->flash->addMessage(
                    'success',
                    'Petunjuk aktivasi akun telah dikirim ke: ' .
                        $this->container->request->getParam('email')
                );
            } else {
                $user = $this->container->auth->registerAndActivate($userDetails);
                $role->users()->attach($user);
            }
        }

        return $user;
    }
}
