<?php

namespace App\Library;

use Carbon\Carbon;
use App\Library\Utils;
use App\Library\VideoParser as VP;
use App\Model\GalleryCategories;
use App\Model\GalleryPosts;
use Psr\Container\ContainerInterface;
use Respect\Validation\Validator as V;
use App\Bundle\FileUpload\UploadHandler as UploadHandler;
/** @SuppressWarnings(PHPMD.StaticAccess) */
class Gallery extends Library
{
    protected $categoryId;
    protected $slug;
    protected $videoProvider;
    protected $videoId;
    protected $publishAt;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->categoryId = null;
        $this->slug = null;
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
        // $this->processCategory();

        // Process Slug
        $this->processSlug();


        // Process Video
        $this->processVideo();

        // Process Publish At Date
        $this->publishAt = Carbon::parse($requestParams['publishAt']);



        if ($this->validator->isValid()) {

            $newPost = new GalleryPosts;
            $newPost->title = $requestParams['title'];
            $tanggal = $this->publishAt;
            $tgl = date_format($tanggal,"Y-m-d-H-i-s");
            $newPost->slug = $this->slug . '-'. $this->container->auth->check()->id. '-' . $tgl;
            $newPost->video_provider = $this->videoProvider;
            $newPost->video_id = $this->videoId;
            $newPost->category_id = 6;
            $newPost->user_id = $this->container->auth->check()->id;
            $newPost->publish_at = Carbon::parse($requestParams['publish_at']);
            if ($requestParams['status']) {
                $newPost->status = 1;
            }

            $gallery_photos = isset($requestParams['gallerys_photo']) ? $requestParams['gallerys_photo'] : [];
            if ($_FILES['upload_gallery_photo']['name'][0] != '') {
              for ($i = 0; $i < sizeof($_FILES['upload_gallery_photo']['tmp_name']); $i++) {
                if(in_array($_FILES['upload_gallery_photo']['name'][$i], $requestParams['gallery_photos_name'])){
                  $gallery_photos[] = $this->upload_image($_FILES['upload_gallery_photo']['tmp_name'][$i], '/files/gallery/' . $newPost->user_id  );
                }
              }
            }

            $newPost->gallerys_photo = implode("#", array_filter($gallery_photos));

            $newPost->save();


            $this->container->flash->addMessage('success', 'Galeri album Berhasil Di simpan');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Galeri album Gagal Di simpan');
        return false;
    }

    public function updatePost($postId)
    {
        $this->blogEdit = true;

        $requestParams = $this->container->request->getParams();

        //Check Post
        $post = GalleryPosts::find($postId);

        if (!$post) {
            return false;
        }
        // Validate Title Desc
        $this->validateTitleDesc($post->id);

        // Process Category
        // $this->processCategory();

        // Process Slug
        $this->processSlug($post->id);


        // Process Video
        $this->processVideo();

        // Process Publish At Date

        $this->publishAt = Carbon::parse($requestParams['publish_at']);




        if ($this->validator->isValid()) {
            $post->title = $requestParams['title'];
            $tanggal = $this->publishAt;
            $tgl = date_format($tanggal,"Y-m-d-H-i-s");
            $post->slug= $this->slug . '-'. $this->container->auth->check()->id. '-' . $tgl;

            $post->video_provider = $this->videoProvider;
            $post->video_id = $this->videoId;
            $post->category_id = 6;
            $post->user_id = $this->container->auth->check()->id;
            $post->publish_at = $this->publishAt;
            if ($requestParams['status']) {
                $post->status = 1;
            }
            $gallery_photos = isset($requestParams['gallerys_photo']) ? $requestParams['gallerys_photo'] : [];
            if ($_FILES['upload_gallery_photo']['name'][0] != '') {
              for ($i = 0; $i < sizeof($_FILES['upload_gallery_photo']['tmp_name']); $i++) {
                if(in_array($_FILES['upload_gallery_photo']['name'][$i], $requestParams['gallery_photos_name'])){
                  $gallery_photos[] = $this->upload_image($_FILES['upload_gallery_photo']['tmp_name'][$i], '/files/gallery/' . $post->user_id);
                }
              }
            }
            $post->gallerys_photo = implode("#", array_filter($gallery_photos));

            $post->save();

            $this->container->flash->addMessage('success', 'Galeri album Berhasil Di edit');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Galeri album Gagal Di edit');
        return false;
    }

    public function delete()
    {
        $post = GalleryPosts::find($this->container->request->getParam('post_id'));

        if ($post) {
            if ($post->delete()) {
                return true;
            }
        }
        return false;
    }

    public function publish()
    {
        $post = GalleryPosts::find($this->container->request->getParam('post_id'));

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
        $post = GalleryPosts::find($this->container->request->getParam('post_id'));

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
                'rules' => V::length(6, 255)->alnum('\',.?!@#$%&*()-_"'),
                    'messages' => array(
                    'length' => 'Must be between 6 and 255 characters.',
                    'alnum' => 'Invalid Characters Only \',.?!@#$%&*()-_" are allowed.'
                )
            )
        );
        $this->validator->validate($this->container->request, $validateData);

        $checkTitle = GalleryPosts::where('title', $this->container->request->getParam('title'));
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
        $categoryCheck = GalleryCategories::find($categoryId);

        if (!$categoryCheck) {
            // Check if category exists by name
            $checkCat = GalleryCategories::where('name', $categoryId)->first();
            if ($checkCat) {
                $categoryId = $checkCat->category_id;
            }

            // Add new category if not exists
            $addCategory = new GalleryCategories;
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
        $checkSlug = GalleryPosts::where('slug', $slug);
        if ($postId) {
            $checkSlug = $checkSlug->where('id', '!=', $postId);
        }
        // if ($checkSlug->first()) {
        //     $this->validator->addError('title', 'Possible duplicate title due to duplicate slug.');
        //     return false;
        // }

        $this->slug = $slug;
        return true;
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
