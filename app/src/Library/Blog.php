<?php

namespace App\Library;

use Carbon\Carbon;
use App\Library\Utils;
use App\Library\Upload;
use App\Library\VideoParser as VP;
use App\Model\BlogCategories;
use App\Model\BlogPosts;
use App\Model\BlogPostsTags;
use App\Model\BlogTags;
use Psr\Container\ContainerInterface;
use Respect\Validation\Validator as V;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class Blog extends Library
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
        $this->categoryId = null;
        $this->slug = null;
        $this->parsedTags = null;
        $this->videoProvider = null;
        $this->videoId = null;
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

        // Process Tags
        $this->processTags();

        // Process Video
        // $this->processVideo();

        // Process Publish At Date
        $this->publishAt = Carbon::parse($requestParams['publishAt']);

        //if ($this->validator->isValid()) {
            $newPost = new BlogPosts;
            $newPost->title = $requestParams['title'];
            $newPost->description = $requestParams['description'];
            $tanggal = $this->publishAt;
            $tgl = date_format($tanggal,"Y-m-d-H-i-s");
            $newPost->slug = $this->slug . '-'. $this->container->auth->check()->id. '-' . $tgl;
            $newPost->content = $requestParams['post_content'];
            $newPost->featured_image = $requestParams['featured_image'];
            $newPost->category_id = $this->categoryId;
            $newPost->user_id = $this->container->auth->check()->id;
            $newPost->publish_at = Carbon::parse($requestParams['publish_at']);
            if ($requestParams['status']) {
                $newPost->status = 1;
            }

            $newPost->save();

            foreach ($this->parsedTags as $tag) {
                $addTag = new BlogPostsTags;
                $addTag->post_id = $newPost->id;
                $addTag->tag_id = $tag;
                $addTag->save();
            }

            $this->container->flash->addMessage('success', 'Berita Berhasil Di Simpan');
            return true;
        //}
        $this->container->flash->addMessageNow('danger', 'Berita Gagal Di Simpan');
        return false;
    }
    public function addBerita()
    {
        $requestParams = $this->container->request->getParams();

        // Validate Title Desc
        $this->validateTitleDesc();

        // Process Category
        $this->processCategory();

        // Process Slug
        $this->processSlug();

        // Process Tags
        $this->processTags();

        // Process Video
        // $this->processVideo();

        // Process Publish At Date
        $this->publishAt = Carbon::parse($requestParams['publishAt']);

        //if ($this->validator->isValid()) {
            $newPost = new BlogPosts;
            $newPost->title = $requestParams['title'];
            $newPost->description = $requestParams['description'];
            
            $tanggal = $this->publishAt;
            $tgl = date_format($tanggal,"Y-m-d-H-i-s");
            $newPost->slug = $this->slug . '-'. $this->container->auth->check()->id. '-' . $tgl;
            $newPost->content = $requestParams['post_content'];
            $newPost->featured_image = $requestParams['featured_image'];
            $files = $this->container->request->getUploadedFiles();
            if (isset($files['featured_image_upload']->file) && $files['featured_image_upload']->file != '') {
                $newPost->featured_image = $this->upload_image($files['featured_image_upload']->file, '/img/berita');
            } else {
                $newPost->featured_image = isset($requestParams['featured_image']) ? $requestParams['featured_image'] : NULL;
            }
            $newPost->category_id = $this->categoryId;
            $newPost->user_id = $this->container->auth->check()->id;
            $newPost->publish_at = Carbon::parse($requestParams['publish_at']);
            if ($requestParams['status']) {
                $newPost->status = 1;
            }

            $newPost->save();

            foreach ($this->parsedTags as $tag) {
                $addTag = new BlogPostsTags;
                $addTag->post_id = $newPost->id;
                $addTag->tag_id = $tag;
                $addTag->save();
            }

            $this->container->flash->addMessage('success', 'Berita Berhasil Di Simpan');
            return true;
        //}
        $this->container->flash->addMessageNow('danger', 'Berita Gagal Di Simpan');
        return false;
    }

    public function updatePost($postId)
    {
        $this->blogEdit = true;

        $requestParams = $this->container->request->getParams();

        //Check Post
        $post = BlogPosts::find($postId);

        if (!$post) {
            return false;
        }
        // Validate Title Desc
        $this->validateTitleDesc($post->id);

        // Process Category
        $this->processCategory();

        // Process Slug
        $this->processSlug($post->id);

        // Process Tags
        $this->processTags();

        // Process Video
        // $this->processVideo();

        // Process Publish At Date
        $this->publishAt = Carbon::parse($requestParams['publishAt']);

        if ($this->validator->isValid()) {
            $post->title = $requestParams['title'];
            $post->description = $requestParams['description'];
            $tanggal = $this->publishAt;
            $tgl = date_format($tanggal,"Y-m-d-H-i-s");
            $post->slug = $this->slug . '-'. $this->container->auth->check()->id. '-' . $tgl;
            $post->content = $requestParams['post_content'];
            $post->featured_image = $requestParams['featured_image'];
            $post->category_id = $this->categoryId;
            $post->user_id = $this->container->auth->check()->id;
            $post->publish_at =  Carbon::parse($requestParams['publish_at']);
            if ($requestParams['status']) {
                $post->status = 1;
            }

            $post->save();

            //Delete Existing Post Tags
            BlogPostsTags::where('post_id', $post->id)->delete();

            foreach ($this->parsedTags as $tag) {
                $addTag = new BlogPostsTags;
                $addTag->post_id = $post->id;
                $addTag->tag_id = $tag;
                $addTag->save();
            }

            $this->container->flash->addMessage('success', 'Berita Berhasil di Edit');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Berita Gagal di Edit');
        return false;
    }
    public function updateBerita($postId)
    {
        $this->blogEdit = true;

        $requestParams = $this->container->request->getParams();

        //Check Post
        $post = BlogPosts::find($postId);

        if (!$post) {
            return false;
        }
        // Validate Title Desc
        $this->validateTitleDesc($post->id);

        // Process Category
        $this->processCategory();

        // Process Slug
        $this->processSlug($post->id);

        // Process Tags
        $this->processTags();

        // Process Video
        // $this->processVideo();

        // Process Publish At Date
        $this->publishAt = Carbon::parse($requestParams['publishAt']);

        if ($this->validator->isValid()) {
            $post->title = $requestParams['title'];
            $post->description = $requestParams['description'];
            $tanggal = $this->publishAt;
            $tgl = date_format($tanggal,"Y-m-d-H-i-s");
            $post->slug= $this->slug . '-'. $this->container->auth->check()->id. '-' . $tgl;
            $post->content = $requestParams['post_content'];
            $files = $this->container->request->getUploadedFiles();
            if (isset($files['featured_image_upload']->file) && $files['featured_image_upload']->file != '') {
                $post->featured_image = $this->upload_image($files['featured_image_upload']->file, '/img/berita');
            } else {
                $post->featured_image = isset($requestParams['featured_image']) ? $requestParams['featured_image'] : NULL;
            }
            $post->category_id = $this->categoryId;
            $post->user_id = $this->container->auth->check()->id;
            $post->publish_at =  Carbon::parse($requestParams['publish_at']);
            if ($requestParams['status']) {
                $post->status = 1;
            } else {
                $post->status = 0;
            }

            $post->save();

            //Delete Existing Post Tags
            BlogPostsTags::where('post_id', $post->id)->delete();

            foreach ($this->parsedTags as $tag) {
                $addTag = new BlogPostsTags;
                $addTag->post_id = $post->id;
                $addTag->tag_id = $tag;
                $addTag->save();
            }

            $this->container->flash->addMessage('success', 'Berita Berhasil di Edit');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Berita Gagal di Edit');
        return false;
    }

    public function delete()
    {
        $post = BlogPosts::find($this->container->request->getParam('post_id'));

        if ($post) {
            if ($post->delete()) {
                return true;
            }
        }
        return false;
    }

    public function publish()
    {
        $post = BlogPosts::find($this->container->request->getParam('post_id'));

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
        $post = BlogPosts::find($this->container->request->getParam('post_id'));

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
            'title' => array(
                'rules' => V::length(6, 255),
                    'messages' => array(
                    'length' => 'Must be between 6 and 255 characters.',
                    // 'alnum' => 'Invalid Characters Only \',.?!@#$%&*()_ are allowed.'
                )
            ),
            'description' => array(
                'rules' => V::length(6, 255),
                    'messages' => array(
                    'length' => 'Must be between 6 and 255 characters.',
                    // 'alnum' => 'Invalid Characters Only \',.?!@#$%&*()_ are allowed.'
                )
            )
        );
        $this->validator->validate($this->container->request, $validateData);

        $checkTitle = BlogPosts::where('title', $this->container->request->getParam('title'));
        if ($postId) {
            $checkTitle = $checkTitle->where('id', '!=', $postId);
        }
        // if ($checkTitle->first()) {
        //     $this->validator->addError('title', 'Judul Yang Sama di Temukan, Ini Buruk Untuk SEO');
        // }
    }

    private function processCategory()
    {
        $categoryId = $this->container->request->getParam('category');

        // Check if category exists by id
        $categoryCheck = BlogCategories::find($categoryId);

        if (!$categoryCheck) {
            // Check if category exists by name
            $checkCat = BlogCategories::where('name', $categoryId)->first();
            if ($checkCat) {
                $categoryId = $checkCat->category_id;
            }

            // Add new category if not exists
            $addCategory = new BlogCategories;
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
        $slug = Utils::slugify($this->container->request->getParam('title'));
        $checkSlug = BlogPosts::where('slug', $slug);
        if ($postId) {
            $checkSlug = $checkSlug->where('id', '!=', $postId);
        }
        // if ($checkSlug->first()) {
        //     $this->validator->addError('title', 'Judul Berita Sudah Di Pakai');
        //     return false;
        // }
        $this->slug = $slug;
        return true;
    }

    private function processTags()
    {
        foreach ($this->container->request->getParam('tags') as $value) {
            // Check if Already Numeric
            if (is_numeric($value)) {
                $check = BlogTags::find($value);
                if ($check) {
                    $this->parsedTags[] = $value;
                }
                continue;
            }

            // Check if slug already exists
            $slug = Utils::slugify($value);
            $slugCheck = BlogTags::where('slug', '=', $slug)->first();
            if ($slugCheck) {
                $this->parsedTags[] = $slugCheck->id;
                continue;
            }

            // Add New Tag To Database
            $newTag = new BlogTags;
            $newTag->name = $value;
            $newTag->slug = $slug;
            if ($newTag->save()) {
                if ($newTag->id) {
                    $this->parsedTags[] = $newTag->id;
                }
            }
        }
    }

    private function processVideo()
    {
        $requestParams = $this->container->request->getParams();

        // Handle Featured Video
        if (!empty($requestParams['video_id']) && !empty($requestParams['video_provider'])) {
            $this->videoProvider = $requestParams['video_provider'];
            $this->videoId = $requestParams['video_id'];
        }
        if (!empty($requestParams['video_url'])) {
            $this->videoProvider = VP::getVideoId($requestParams['video_url']);
            $this->videoId = VP::getVideoId($requestParams['video_url']);
        }

        // Check Featured Image
        if ($this->videoProvider && $this->videoId && empty($requestParams['featured_image'])) {
            $this->validator->addError('featured_image', 'Featured image is required with a video.');
        }
    }

    private function upload_image($tmpfile, $subdir = NULL, $mime_allowed = array(
        'jpg' => 'image/jpeg',
        'png' => 'image/png',)
      ) {
        $this->dir = $this->public_dir;
        $dir = $this->dir;
        $path = '';
    
        $curdir = $dir;
        if (!file_exists($curdir) && !is_dir($curdir)) {
          mkdir($curdir);
        }
        if ($subdir !== NULL) {
          $subdirs = explode("/", $subdir);
          foreach ($subdirs as $sub) {
            $curdir = $curdir . '/' . $sub;
            if (!file_exists($curdir) && !is_dir($curdir)) {
              mkdir($curdir);
            }
          }
          $dir = $dir . $subdir;
          $path = $path . $subdir;
        }
    
        if (false === $ext = array_search(
            $this->get_mime_type($tmpfile), $mime_allowed, true
            )) {
          return false;
        }
    
        $dest_name = sha1_file($tmpfile);
    
        if (!move_uploaded_file(
                $tmpfile, sprintf('%s/%s.%s', $dir, $dest_name, $ext
                )
            )) {
          return false;
        }
    
        return $path . '/' . $dest_name . '.' . $ext;
      }
    
      private function get_mime_type($filepath) {
        // Check only existing files
        if (!file_exists($filepath) || !is_readable($filepath))
          return false;
    
        // Trying finfo
        if (function_exists('finfo_open')) {
          $finfo = finfo_open(FILEINFO_MIME);
          $mimeType = finfo_file($finfo, $filepath);
          finfo_close($finfo);
          // Mimetype can come in text/plain; charset=us-ascii form
          if (strpos($mimeType, ';'))
            list($mimeType, ) = explode(';', $mimeType);
          return $mimeType;
        }
    
        // Trying mime_content_type
        if (function_exists('mime_content_type')) {
          return mime_content_type($filepath);
        }
    
        // Trying exec
        if (function_exists('system')) {
          $mimeType = system("file -i -b $filepath");
          if (!empty($mimeType))
            return $mimeType;
        }
    
        // Trying to get mimetype from images
        $imageData = @getimagesize($filepath);
        if (!empty($imageData['mime'])) {
          return $imageData['mime'];
        }
    
        return false;
      }
}
