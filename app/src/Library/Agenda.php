<?php

namespace App\Library;

use Carbon\Carbon;
use App\Library\Utils;
use App\Model\AgendaCategories;
use App\Model\AgendaPosts;
use Psr\Container\ContainerInterface;
use Respect\Validation\Validator as V;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class Agenda extends Library
{
    protected $categoryId;
    protected $slug;
    protected $publishAt;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->categoryId = null;
        $this->slug = null;
        $this->publishAt = Carbon::now();
    }

    public function addPost()
    {
        $requestParams = $this->container->request->getParams();

        // Validate Title Desc
        $this->validateTitleDesc();

        // Process Category
        $this->processCategory();

        // Process Slug
        $this->processSlug();


        // Process Publish At Date
        $this->publishAt = Carbon::parse($requestParams['publishAt']);

        if ($this->validator->isValid()) {
            $newPost = new AgendaPosts;
            $newPost->agendatitle = $requestParams['agendatitle'];
            $newPost->address = $requestParams['address'];
            $newPost->dateagenda = $requestParams['dateagenda'];
            $tanggal = $this->publishAt;
            $tgl = date_format($tanggal,"Y-m-d-H-i-s");
            $newPost->slug = $this->slug . '-'. $this->container->auth->check()->id. '-' . $tgl;
            $newPost->content = $requestParams['post_content'];
            $newPost->category_id = $this->categoryId;
            $newPost->user_id = $this->container->auth->check()->id;
            $newPost->publish_at = $this->publishAt;
            if ($requestParams['status']) {
                $newPost->status = 1;
            }

            $newPost->save();


            $this->container->flash->addMessage('success', 'Agenda Kamu Berhasil di Simpan.');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Agenda Kamu Gagal di Simpan.');
        return false;
    }

    public function updatePost($postId)
    {
        $this->agendaEdit = true;

        $requestParams = $this->container->request->getParams();

        //Check Post
        $post = AgendaPosts::find($postId);

        if (!$post) {
            return false;
        }
        // Validate Title Desc
        $this->validateTitleDesc($post->id);

        // Process Category
        $this->processCategory();

        // Process Slug
        $this->processSlug($post->id);


        // Process Publish At Date
        $this->publishAt = Carbon::parse($requestParams['publishAt']);

        if ($this->validator->isValid()) {
            $post->agendatitle = $requestParams['agendatitle'];
            $post->address = $requestParams['address'];
            $post->dateagenda = $requestParams['dateagenda'];
            $tanggal = $this->publishAt;
            $tgl = date_format($tanggal,"Y-m-d-H-i-s");
            $post->slug = $this->slug . '-'. $this->container->auth->check()->id. '-' . $tgl;
            $post->content = $requestParams['post_content'];
            $post->category_id = $this->categoryId;
            $post->user_id = $this->container->auth->check()->id;
            $post->publish_at = $this->publishAt;
            if ($requestParams['status']) {
                $post->status = 1;
            }

            $post->save();

            

            $this->container->flash->addMessage('success', 'Your blog has been updated successfully.');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'There was an error updating your blog.');
        return false;
    }

    public function delete()
    {
        $post = AgendaPosts::find($this->container->request->getParam('post_id'));

        if ($post) {
            if ($post->delete()) {
                return true;
            }
        }
        return false;
    }

    public function publish()
    {
        $post = AgendaPosts::find($this->container->request->getParam('post_id'));

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
        $post = AgendaPosts::find($this->container->request->getParam('post_id'));

        if ($post) {
            $post->status = 0;
            if ($post->save()) {
                return true;
            }
        }
        return false;
    }

    private function validateTitleDesc($postId = null)
    {
        //Validate Data
        $validateData = array(
            'agendatitle' => array(
                'rules' => V::length(6, 255)->alnum('\',.?!@#$%&*()-_"'),
                    'messages' => array(
                    'length' => 'Must be between 6 and 255 characters.',
                    'alnum' => 'Invalid Characters Only \',.?!@#$%&*()-_" are allowed.'
                )
            )
        );
        $this->validator->validate($this->container->request, $validateData);

        $checkTitle = AgendaPosts::where('agendatitle', $this->container->request->getParam('agendatitle'));
        if ($postId) {
            $checkTitle = $checkTitle->where('id', '!=', $postId);
        }
        // if ($checkTitle->first()) {
        //     $this->validator->addError('agendatitle', 'Duplicate agenda title found.  This is bad for SEO.');
        // }
    }

    private function processCategory()
    {
        $categoryId = $this->container->request->getParam('category');
        
        // Check if category exists by id
        $categoryCheck = AgendaCategories::find($categoryId);

        if (!$categoryCheck) {
            // Check if category exists by name
            $checkCat = AgendaCategories::where('name', $categoryId)->first();
            if ($checkCat) {
                $categoryId = $checkCat->category_id;
            }

            // Add new category if not exists
            $addCategory = new AgendaCategories;
            $addCategory->name = $categoryId;
            $addCategory->slug = Utils::slugify($categoryId);
            $addCategory->status = 1;
            $addCategory->save();
            $categoryId = $addCategory->id;
        }

        $this->categoryId = $categoryId;
    }

    private function processSlug($postId = null)
    {
        $slug = Utils::slugify($this->container->request->getParam('agendatitle'));
        $checkSlug = AgendaPosts::where('slug', $slug);
        if ($postId) {
            $checkSlug = $checkSlug->where('id', '!=', $postId);
        }
        // if ($checkSlug->first()) {
        //     $this->validator->addError('agendatitle', 'Possible duplicate agenda title due to duplicate slug.');
        //     return false;
        // }

        $this->slug = $slug;
        return true;
    }


}
