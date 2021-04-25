<?php

namespace App\Library;

use Carbon\Carbon;
use App\Library\Utils;
use App\Library\VideoParser as VP;
use App\Model\PelatihanCategories;
use App\Model\PelatihanPosts;
use App\Model\PelatihanPostsTags;
use App\Model\PelatihanTags;
use App\Model\PelatihanDaftarPeserta;
use Psr\Container\ContainerInterface;
use Respect\Validation\Validator as V;
use App\Model\PelatihanLaporanAkhirModel;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class Pelatihan extends Library
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
        $this->processVideo();

        // Process Publish At Date
        $this->publishAt = Carbon::parse($requestParams['publishAt']);

        if ($this->validator->isValid()) {
            $newPost = new PelatihanPosts;
            $newPost->title = $requestParams['title'];
            $newPost->start_date = $requestParams['start_date'];
            $newPost->end_date = $requestParams['end_date'];
            $newPost->tempat_pelaksanaan = $requestParams['tempat_pelaksanaan'];
            $newPost->sasaran = $requestParams['sasaran'];
            $newPost->berbasis = $requestParams['berbasis'];
            $newPost->keterangan = $requestParams['keterangan'];
            $newPost->kuota_peserta = $requestParams['kuoata_peserta'];
            $tanggal = $this->publishAt;
            $tgl = date_format($tanggal,"Y-m-d-H-i-s");
            $newPost->slug = $this->slug . '-'. $this->container->auth->check()->id. '-' . $tgl;
            $newPost->content = $requestParams['post_content'];
            $newPost->category_id = $this->categoryId;
            $newPost->user_id = $this->container->auth->check()->id;
            $newPost->publish_at = $this->publishAt;
            $newPost->tgl_seleksi = $requestParams['tgl_seleksi'];
            $newPost->persyaratan = $requestParams['persyaratan'];
            $newPost->keterangan_instruktur = $requestParams['keterangan_instruktur'];
            if ($requestParams['status']) {
                $newPost->status = 1;
            }else{
                $newPost->status = 0;
            }
            $newPost->save();
            foreach ($this->parsedTags as $tag) {
                $addTag = new PelatihanPostsTags;
                $addTag->post_id = $newPost->id;
                $addTag->tag_id = $tag;
                $addTag->save();
            }

            $this->container->flash->addMessage('success', 'Your Pelatihan has been saved successfully.');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'There was an error saving your Pelatihan.');
        return false;
    }

    public function updatePost($postId)
    {
        $this->pelatihanEdit = true;

        $requestParams = $this->container->request->getParams();

        //Check Post
        $post = PelatihanPosts::find($postId);

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
        $this->processVideo();

        // Process Publish At Date
        $this->publishAt = Carbon::parse($requestParams['publishAt']);

        if ($this->validator->isValid()) {
            $post->title = $requestParams['title'];
            $post->start_date = $requestParams['start_date'];
            $post->end_date = $requestParams['end_date'];
            $post->tempat_pelaksanaan = $requestParams['tempat_pelaksanaan'];
            $post->sasaran = $requestParams['sasaran'];
            $post->berbasis = $requestParams['berbasis'];
            $post->keterangan = $requestParams['keterangan'];
            $post->kuota_peserta = $requestParams['kuoata_peserta'];
            $tanggal = $this->publishAt;
            $tgl = date_format($tanggal,"Y-m-d-H-i-s");
            $post->slug = $this->slug . '-'. $this->container->auth->check()->id. '-' . $tgl;
            $post->content = $requestParams['post_content'];
            $post->category_id = $this->categoryId;
            $post->user_id = $this->container->auth->check()->id;
            $post->publish_at = $this->publishAt;
            $newPost->tgl_seleksi = $requestParams['tgl_seleksi'];
            $newPost->persyaratan = $requestParams['persyaratan'];
            $newPost->keterangan_instruktur = $requestParams['keterangan_instruktur'];
            if ($requestParams['status']) {
                $post->status = 1;
            }else{
                $post->status = 0;
            }

            $post->save();

            PelatihanPostsTags::where('post_id', $post->id)->delete();
            foreach ($this->parsedTags as $tag) {
                $addTag = new PelatihanPostsTags;
                $addTag->post_id = $post->id;
                $addTag->tag_id = $tag;
                $addTag->save();
            }

            $this->container->flash->addMessage('success', 'Your Pelatihan has been updated successfully.');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'There was an error updating your Pelatihan.');
        return false;
    }

    public function addLaporan($postId)
    {
        $this->pelatihanEdit = true;

        $requestParams = $this->container->request->getParams();

        //Check Post
        $post = PelatihanPosts::find($postId);

        if (!$post) {
            return false;
        }

        if ($this->validator->isValid()) {
           $post->status = 2;
            if ($post->save()) {
                $laporan = new PelatihanLaporanAkhirModel;
                $laporan->id = $post->id;
                $laporan->pelatihan_id =  $post->id;
                $laporan->info_kegiatan =  $requestParams['info_kegiatan'];
                $laporan->tanggal_pelaksanaan =  $requestParams['tanggal_pelaksanaan'];
                $laporan->jumlah_peserta =  $requestParams['jumlah_peserta'];
                $laporan->photo =  $requestParams['photo'];
                $laporan->save();
            }
            $this->container->flash->addMessage('success', 'Your Pelatihan has been updated successfully.');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'There was an error updating your Pelatihan.');
        return false;
    }
    public function editLaporan($postId)
    {
        $requestParams = $this->container->request->getParams();

        //Check Post
        $edit = PelatihanLaporanAkhirModel::find($postId);
        if (!$edit) {
            return false;
        }
        if ($this->validator->isValid()) {
            $edit->info_kegiatan =  $requestParams['info_kegiatan'];
            $edit->tanggal_pelaksanaan =  $requestParams['tanggal_pelaksanaan'];
            $edit->jumlah_peserta =  $requestParams['jumlah_peserta'];
            $edit->photo =  $requestParams['photo'];
            $edit->save();
            $this->container->flash->addMessage('success', 'Data Laporan Berhasil Di Edit');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Data Laporan Gagal Di Edit.');
        return false;

    }

    public function delete()
    {
        $post = PelatihanPosts::find($this->container->request->getParam('post_id'));

        if ($post) {
            if ($post->delete()) {
                return true;
            }
        }
        return false;
    }

    public function publish()
    {
        $post = PelatihanPosts::find($this->container->request->getParam('post_id'));
        if ($post) {
            $post->status = 1;
            if ($post->save()) {
                return true;
            }
        }
        return false;
    }

    public function syaratlengkap()
    {
        $post = PelatihanDaftarPeserta::find($this->container->request->getParam('post_id'));
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
        $post = PelatihanPosts::find($this->container->request->getParam('post_id'));

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

        $checkTitle = PelatihanPosts::where('title', $this->container->request->getParam('title'));
        if ($postId) {
            $checkTitle = $checkTitle->where('id', '!=', $postId);
        }
        // if ($checkTitle->first()) {
        //     $this->validator->addError('title', 'Duplicate title found.  This is bad for SEO.');
        // }
    }

    private function processCategory()
    {
        $categoryId = $this->container->request->getParam('category');

        // Check if category exists by id
        $categoryCheck = PelatihanCategories::find($categoryId);

        if (!$categoryCheck) {
            // Check if category exists by name
            $checkCat = PelatihanCategories::where('name', $categoryId)->first();
            if ($checkCat) {
                $categoryId = $checkCat->category_id;
            }

            // Add new category if not exists
            $addCategory = new PelatihanCategories;
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
        $checkSlug = PelatihanPosts::where('slug', $slug);
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

    private function processTags()
    {
        foreach ($this->container->request->getParam('tags') as $value) {
            // Check if Already Numeric
            if (is_numeric($value)) {
                $check = PelatihanTags::find($value);
                if ($check) {
                    $this->parsedTags[] = $value;
                }
                continue;
            }

            // Check if slug already exists
            $slug = Utils::slugify($value);
            $slugCheck = PelatihanTags::where('slug', '=', $slug)->first();
            if ($slugCheck) {
                $this->parsedTags[] = $slugCheck->id;
                continue;
            }

            // Add New Tag To Database
            $newTag = new PelatihanTags;
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
}
