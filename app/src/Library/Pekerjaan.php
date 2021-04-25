<?php

namespace App\Library;

use Carbon\Carbon;
use App\Library\Utils;
use App\Library\VideoParser as VP;
use App\Model\PekerjaanCategories;
use App\Model\PekerjaanPosts;
use Psr\Container\ContainerInterface;
use Respect\Validation\Validator as V;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class Pekerjaan extends Library
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
            $newPost = new PekerjaanPosts;
            $newPost->companyname = $requestParams['companyname'];
            $newPost->typeofwork = $requestParams['typeofwork'];
            $newPost->position = $requestParams['position'];
            $newPost->salary = $requestParams['salary'];
            $newPost->startmonth = $requestParams['startmonth'];
            $newPost->startyear = $requestParams['startyear'];
            $newPost->finishedmonth = $requestParams['finishedmonth'];
            $newPost->finishedyear = $requestParams['finishedyear'];
            $newPost->descjob = $requestParams['descjob'];
            $newPost->featured_image = $requestParams['featured_image'];
            $newPost->user_id = $this->container->auth->check()->id;
            $newPost->publish_at = $this->publishAt;
            if ($requestParams['status']) {
                $newPost->status = 1;
            }

            $newPost->save();


            $this->container->flash->addMessage('success', 'Riwayat Pekerjaan Berhasil di Tambahkan.');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Riwayat Pekerjaan Gagal di Tambahkan');
        return false;
    }

    public function updatePost($postId)
    {
        $this->blogEdit = true;

        $requestParams = $this->container->request->getParams();

        //Check Post
        $post = PekerjaanPosts::find($postId);

        if (!$post) {
            return false;
        }

        // Process Publish At Date
        $this->publishAt = Carbon::parse($requestParams['publishAt']);

        if ($this->validator->isValid()) {
            $post->companyname = $requestParams['companyname'];
            $post->typeofwork = $requestParams['typeofwork'];
            $post->position = $requestParams['position'];
            $post->salary = $requestParams['salary'];
            $post->startmonth = $requestParams['startmonth'];
            $post->startyear = $requestParams['startyear'];
            $post->finishedmonth = $requestParams['finishedmonth'];
            $post->finishedyear = $requestParams['finishedyear'];
            $post->descjob = $requestParams['descjob'];
            $post->featured_image = $requestParams['featured_image'];
            $post->user_id = $this->container->auth->check()->id;
            $post->publish_at = $this->publishAt;
            if ($requestParams['status']) {
                $post->status = 1;
            }

            $post->save();

            $this->container->flash->addMessage('success', 'Update Riwayat Pekerjaan Berhasil');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Update Riwayat Pekerjaan Gagal');
        return false;
    }

    public function delete()
    {
        $post = PekerjaanPosts::find($this->container->request->getParam('post_id'));

        if ($post) {
            if ($post->delete()) {
                return true;
            }
        }
        return false;
    }



    private function validateTitleDesc($postId = null)
    {
        
        $validateData = array(
            'title' => array(
                'rules' => V::length(6, 255)->alnum('\',.?!@#$%&*()-_"'),
                    'messages' => array(
                    'length' => 'Must be between 6 and 255 characters.',
                    'alnum' => 'Invalid Characters Only \',.?!@#$%&*()-_" are allowed.'
                )
            )
        );

    }



}
