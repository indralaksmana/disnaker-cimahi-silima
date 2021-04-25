<?php

namespace App\Library;

use Carbon\Carbon;
use App\Library\Utils;
use App\Library\VideoParser as VP;
use App\Model\PendidikanNonFormalCategories;
use App\Model\PendidikanNonFormalPosts;
use Psr\Container\ContainerInterface;
use Respect\Validation\Validator as V;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class PendidikanNonFormal extends Library
{

    protected $publishAt;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->publishAt = Carbon::now();
    }

    public function addPost()
    {
        $requestParams = $this->container->request->getParams();



        // Process Category


        // Process Publish At Date
        $this->publishAt = Carbon::parse($requestParams['publishAt']);

        if ($this->validator->isValid()) {
            $newPost = new PendidikanNonFormalPosts;
            $newPost->level = $requestParams['level'];
            $newPost->nameofeducation = $requestParams['nameofeducation'];
            $newPost->startingmonth = $requestParams['startingmonth'];
            $newPost->startyear = $requestParams['startyear'];
            $newPost->finishedmonth = $requestParams['finishedmonth'];
            $newPost->jenis_keterampilan = $requestParams['jenis_keterampilan'];
            $newPost->finishedyear = $requestParams['finishedyear'];
            $newPost->featured_image = $requestParams['featured_image'];
            $newPost->user_id = $this->container->auth->check()->id;
            $newPost->publish_at = $this->publishAt;
            if ($requestParams['status']) {
                $newPost->status = 1;
            }

            $newPost->save();


            $this->container->flash->addMessage('success', 'Riwayat Pendidikan Berhasil di Tambahkan.');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Riwayat Pendidikan Gagal di Tambahkan');
        return false;
    }

    public function updatePost($postId)
    {
        $this->blogEdit = true;

        $requestParams = $this->container->request->getParams();

        //Check Post
        $post = PendidikanNonFormalPosts::find($postId);

        if (!$post) {
            return false;
        }




        // Process Publish At Date
        $this->publishAt = Carbon::parse($requestParams['publishAt']);

        if ($this->validator->isValid()) {
            $post->level = $requestParams['level'];
            $post->nameofeducation = $requestParams['nameofeducation'];
            $post->startingmonth = $requestParams['startingmonth'];
            $post->startyear = $requestParams['startyear'];
            $post->finishedmonth = $requestParams['finishedmonth'];
            $post->finishedyear = $requestParams['finishedyear'];
            $post->jenis_keterampilan = $requestParams['jenis_keterampilan'];
            $post->featured_image = $requestParams['featured_image'];
            $post->user_id = $this->container->auth->check()->id;
            $post->publish_at = $this->publishAt;
            if ($requestParams['status']) {
                $post->status = 1;
            }

            $post->save();

            $this->container->flash->addMessage('success', 'Update Riwayat Pendidikan Berhasil');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Update Riwayat Pendidikan Gagal');
        return false;
    }

    public function delete()
    {
        $post = PendidikanNonFormalPosts::find($this->container->request->getParam('post_id'));

        if ($post) {
            if ($post->delete()) {
                return true;
            }
        }
        return false;
    }

    

    private function validateTitleDesc($postId = null)
    {
        //Validate Data
        $validateData = array(
            'title' => array(
                'rules' => V::length(6, 255)->alnum('\',.?!@#$%&*()-_"'),
                    'messages' => array(
                    'length' => 'Must be between 6 and 255 characters.',
                    'alnum' => 'Invalid Characters Only \',.?!@#$%&*()-_" are allowed.'
                )
            )
        );
        // $this->validator->validate($this->container->request, $validateData);

        // $checkTitle = PendidikanNonFormalPosts::where('title', $this->container->request->getParam('title'));
        // if ($postId) {
        //     $checkTitle = $checkTitle->where('id', '!=', $postId);
        // }
        // if ($checkTitle->first()) {
        //     $this->validator->addError('title', 'Duplicate title found.  This is bad for SEO.');
        // }
    }



}
