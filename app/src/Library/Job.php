<?php

namespace App\Library;

use Carbon\Carbon;
use App\Library\Utils;
use App\Library\VideoParser as VP;
use App\Library\Email as E;
use App\Model\Users as U;
use App\Model\JobCategories;
use App\Model\JobPosts;
use App\Model\JobPostsTags;
use App\Model\JobTags;
use App\Model\BkolPerusahaan;
use Psr\Container\ContainerInterface;
use Respect\Validation\Validator as V;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class Job extends Library
{
    protected $categoryId;
    protected $slug;
    protected $parsedTags;
    protected $publishAt;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->categoryId = null;
        $this->slug = null;
        $this->parsedTags = null;
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

        // Process Publish At Date
        $this->publishAt = Carbon::parse($requestParams['publishAt']);

        if ($this->validator->isValid()) {
            $newPost = new JobPosts;
            $newPost->nama_lowongan = $requestParams['nama_lowongan'];
            $newPost->wilayah_pekerjaan = $requestParams['wilayah_pekerjaan'] ? $requestParams['wilayah_pekerjaan'] : NULL;
            $newPost->provinsi_id = $requestParams['provinsi_id'] ? $requestParams['provinsi_id'] : NULL;
            $newPost->kabupatenkota_id = $requestParams['kabupatenkota_id'] ? $requestParams['kabupatenkota_id'] : NULL;
            $newPost->nama_negara = $requestParams['nama_negara'];
            $newPost->kebutuhan_tenaga_pria = $requestParams['kebutuhan_tenaga_pria'];
            $newPost->kebutuhan_tenaga_wanita = $requestParams['kebutuhan_tenaga_wanita'];
            $newPost->jumlah_kebutuhan_tenaga = $requestParams['kebutuhan_tenaga_wanita'] + $requestParams['kebutuhan_tenaga_pria'];
            $newPost->jabatan_id = $requestParams['jabatan_id'] ? $requestParams['jabatan_id'] : NULL;
            $newPost->gaji_id = $requestParams['gaji_id'] ? $requestParams['gaji_id'] : NULL;
            $newPost->deskripsi_pekerjaan = $requestParams['deskripsi_pekerjaan'];
            $newPost->jenis_kelamin = $requestParams['jenis_kelamin'] ? $requestParams['jenis_kelamin'] : NULL;
            $newPost->status_pekerjaan = $requestParams['status_pekerjaan'] ? $requestParams['status_pekerjaan'] : NULL;
            $newPost->usia_maksimal = $requestParams['usia_maksimal'];
            $newPost->usia_maksimal = $requestParams['usia_maksimal'];
            $newPost->pendidikan_terakhir_id = $requestParams['pendidikan_terakhir_id'] ? $requestParams['pendidikan_terakhir_id'] : NULL;
            $newPost->jurusan_pendidikan_id = $requestParams['jurusan_pendidikan_id'] ? $requestParams['jurusan_pendidikan_id'] : NULL;
            $newPost->nilai_minimal = $requestParams['nilai_minimal'];
            $newPost->status_perkawinan = $requestParams['status_perkawinan'] ? $requestParams['status_perkawinan'] : NULL;
            $newPost->tanggal_pasang = $requestParams['tanggal_pasang'] ? $requestParams['tanggal_pasang'] : NULL;
            $newPost->tanggal_berakhir = $requestParams['tanggal_berakhir'] ? $requestParams['tanggal_berakhir'] : NULL;
            $newPost->kelengkapan_berkas = $requestParams['kelengkapan_berkas'];
            $newPost->persyaratan_umum = $requestParams['persyaratan_umum'];
            $newPost->disabilitas = $requestParams['disabilitas'];
            $tanggal = $this->publishAt;
            $tgl = date_format($tanggal,"Y-m-d-H-i-s");
            $newPost->slug = $this->slug . '-'. $this->container->auth->check()->id. '-' . $tgl;
            $newPost->category_id = $this->categoryId ? $this->categoryId : NULL;
            $newPost->user_id = $this->container->auth->check()->id;
            $newPost->publish_at = $this->publishAt;
            if ($requestParams['status']) {
                $newPost->status = 1;
            }

            $newPost->save();

            $post = JobPosts::with(
              'jabatan',
              'gaji',
              'provinsi',
              'pendidikanterakhir',
              'jurusanpendidikan'
              )
              ->find($newPost->id);

            $users = U::with('roles')
                ->WhereHas('roles', function($q){$q->whereIn('name', ['jobseeker']);})
                ->select('id')
                ->get();

            $perusahaan = BkolPerusahaan::find($this->container->auth->check()->id);

            $date = $newPost->publish_at;

            $y = date('Y',strtotime($date));
            $m = date('m',strtotime($date));
            $d = date('j',strtotime($date));
            $slug =  $newPost->slug;

            $linkDetail = $this->config['domain-bkol'] .
                "/lowongan-kerja/".$y."/".$m."/".$d."/".$slug;

            $deskription_pekerjaan = $post->deskripsi_pekerjaan;
            $berkas_lowongan = $post->kelengkapan_berkas;
            $deskription = strip_tags(html_entity_decode($deskription_pekerjaan));
            $berkas = strip_tags(html_entity_decode($berkas_lowongan));
            $persyaratan_umum = $post->persyaratan_umum;
            $syarat = strip_tags(html_entity_decode($persyaratan_umum));

            /* SKIP
            foreach($users as $key => $value){
                    $sendEmail = new E($this->container);
                    $sendEmail = $sendEmail->sendTemplate(
                    array(
                      $value->id
                    ),
                    'lowongan-kerja-baru',
                    array(
                      'judul_lowongan' => $post->nama_lowongan,
                      'detail_lowongan' => $linkDetail,
                      'jabatan' => $post->jabatan->jenis_golongan,
                      'gaji' => $post->gaji->name,
                      'deskripsi_pekerjaan' =>  $deskription,
                      'penempatan' => $post->wilayah_pekerjaan . '-' . $post->provinsi->lokasi_nama . '-' . $post->provinsi->kabupatenkota . '-' . $post->nama_negara,
                      'jenis_kelamin' => $post->jenis_kelamin,
                      'usia_maksimal' => $post->usia_maksimal,
                      'pendidikan_terakhir' => $post->pendidikanterakhir->jenis_pendidikan,
                      'jurusan_pendidikan' => $post->jurusanpendidikan->jenis_pendidikan,
                      'status_perkawinan' => $post->status_perkawinan,
                      'berlaku_hingga' => $post->tanggal_pasang .' s/d '. $post->tanggal_berakhir,
                      'berkas_lowongan' => $berkas,
                      'syarat_umum' => $syarat,
                      'nama_perusahaan' => $perusahaan->companyname,
                      'alamat_perusahaan' => $perusahaan->companysaddress,
                      'telepon_perusahaan' => $perusahaan->phonenumber,
                      'sektor_usaha' => $perusahaan->industry
                    )
                  );
            }*/

            foreach ($this->parsedTags as $tag) {
                $addTag = new JobPostsTags;
                $addTag->post_id = $newPost->id;
                $addTag->tag_id = $tag;
                $addTag->save();
            }

            $this->container->flash->addMessage('success', 'Lowongan Kerja Kamu Berhasil di Simpan.');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Lowongan Kerja Kamu Gagal di Simpan.');
        return false;
    }

    public function updatePost($postId)
    {
        $this->jobEdit = true;

        $requestParams = $this->container->request->getParams();

        //Check Post
        $post = JobPosts::find($postId);

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


        // Process Publish At Date
        $this->publishAt = Carbon::parse($requestParams['publishAt']);

        if ($this->validator->isValid()) {
            $post->nama_lowongan = $requestParams['nama_lowongan'];
            $post->wilayah_pekerjaan = $requestParams['wilayah_pekerjaan'] ? $requestParams['wilayah_pekerjaan'] : NULL;
            $post->provinsi_id = $requestParams['provinsi_id'] ? $requestParams['provinsi_id'] : NULL;
            $post->kabupatenkota_id = $requestParams['kabupatenkota_id'] ? $requestParams['kabupatenkota_id'] : NULL;
            $post->nama_negara = $requestParams['nama_negara'];
            $post->kebutuhan_tenaga_pria = $requestParams['kebutuhan_tenaga_pria'];
            $post->kebutuhan_tenaga_wanita = $requestParams['kebutuhan_tenaga_wanita'];
            $post->jumlah_kebutuhan_tenaga = $requestParams['kebutuhan_tenaga_wanita'] + $requestParams['kebutuhan_tenaga_pria'];
            $post->jabatan_id = $requestParams['jabatan_id'] ? $requestParams['jabatan_id'] : NULL;
            $post->gaji_id = $requestParams['gaji_id'] ? $requestParams['gaji_id'] : NULL;
            $post->deskripsi_pekerjaan = $requestParams['deskripsi_pekerjaan'];
            $post->jenis_kelamin = $requestParams['jenis_kelamin'] ? $requestParams['jenis_kelamin'] : NULL;
            $post->status_pekerjaan = $requestParams['status_pekerjaan'] ? $requestParams['status_pekerjaan'] : NULL;
            $post->usia_maksimal = $requestParams['usia_maksimal'];
            $post->usia_maksimal = $requestParams['usia_maksimal'];
            $post->pendidikan_terakhir_id = $requestParams['pendidikan_terakhir_id'] ? $requestParams['pendidikan_terakhir_id'] : NULL;
            $post->jurusan_pendidikan_id = $requestParams['jurusan_pendidikan_id'] ? $requestParams['jurusan_pendidikan_id'] : NULL;
            $post->nilai_minimal = $requestParams['nilai_minimal'];
            $post->status_perkawinan = $requestParams['status_perkawinan'] ? $requestParams['status_perkawinan'] : NULL;
            $post->tanggal_pasang = $requestParams['tanggal_pasang'] ? $requestParams['tanggal_pasang'] : NULL;
            $post->tanggal_berakhir = $requestParams['tanggal_berakhir'] ? $requestParams['tanggal_berakhir'] : NULL;
            $post->kelengkapan_berkas = $requestParams['kelengkapan_berkas'];
            $post->persyaratan_umum = $requestParams['persyaratan_umum'];
            $post->disabilitas = $requestParams['disabilitas'];
            $post->category_id = $this->categoryId ? $this->categoryId : NULL;
            $post->user_id = $this->container->auth->check()->id;
            $post->publish_at = $this->publishAt;
            if ($requestParams['status']) {
                $post->status = 1;
            }

            $post->save();

            //Delete Existing Post Tags
            JobPostsTags::where('post_id', $post->id)->delete();

            foreach ($this->parsedTags as $tag) {
                $addTag = new JobPostsTags;
                $addTag->post_id = $post->id;
                $addTag->tag_id = $tag;
                $addTag->save();
            }

            $this->container->flash->addMessage('success', 'Lowongan Kerja anda telah diperbarui dengan sukses.');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Ada kesalahan yang mengenaii Lowongan Kerja Anda.');
        return false;
    }

    public function delete()
    {
        $post = JobPosts::find($this->container->request->getParam('post_id'));

        if ($post) {
            if ($post->delete()) {
                return true;
            }
        }
        return false;
    }

    public function publish()
    {
        $post = JobPosts::find($this->container->request->getParam('post_id'));

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
        $post = JobPosts::find($this->container->request->getParam('post_id'));

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
            'nama_lowongan' => array(
                'rules' => V::length(6, 255)->alnum('\',.?!@#$%&*()-_"'),
                    'messages' => array(
                    'length' => 'Must be between 6 and 255 characters.',
                    'alnum' => 'Invalid Characters Only \',.?!@#$%&*()-_" are allowed.'
                )
            )
        );
        $this->validator->validate($this->container->request, $validateData);

        $checkTitle = JobPosts::where('nama_lowongan', $this->container->request->getParam('nama_lowongan'));
        if ($postId) {
            $checkTitle = $checkTitle->where('id', '!=', $postId);
        }
        // if ($checkTitle->first()) {
        //     $this->validator->addError('nama_lowongan', 'Duplicate Job title found.  This is bad for SEO.');
        // }
    }

    private function processCategory()
    {
        $categoryId = $this->container->request->getParam('category');

        // Check if category exists by id
        $categoryCheck = JobCategories::find($categoryId);

        if (!$categoryCheck) {
            // Check if category exists by name
            $checkCat = JobCategories::where('name', $categoryId)->first();
            if ($checkCat) {
                $categoryId = $checkCat->category_id;
            }

            // Add new category if not exists
            $addCategory = new JobCategories;
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
        $slug = Utils::slugify($this->container->request->getParam('nama_lowongan'));
        $checkSlug = JobPosts::where('slug', $slug);
        if ($postId) {
            $checkSlug = $checkSlug->where('id', '!=', $postId);
        }
        // if ($checkSlug->first()) {
        //     $this->validator->addError('nama_lowongan', 'Judul Lowongan Sudah Di Pakai.');
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
                $check = JobTags::find($value);
                if ($check) {
                    $this->parsedTags[] = $value;
                }
                continue;
            }

            // Check if slug already exists
            $slug = Utils::slugify($value);
            $slugCheck = JobTags::where('slug', '=', $slug)->first();
            if ($slugCheck) {
                $this->parsedTags[] = $slugCheck->id;
                continue;
            }

            // Add New Tag To Database
            $newTag = new JobTags;
            $newTag->name = $value;
            $newTag->slug = $slug;
            if ($newTag->save()) {
                if ($newTag->id) {
                    $this->parsedTags[] = $newTag->id;
                }
            }
        }
    }
}
