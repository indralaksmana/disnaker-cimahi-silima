<?php

namespace App\Library;

use Carbon\Carbon;
use App\Library\Utils;
use App\Library\VideoParser as VP;
use App\Model\BlogCategories;
use App\Model\PerusahaanModel;
use App\Model\PerusahaanModelTags;
use App\Model\BlogTags;
use App\Model\DataPerusahaanModel as JU;
use App\Model\BkolPerusahaan;
use App\Model\Users as U;
use App\Model\JenisUsahaPost;
use App\Model\StatusPerusahaanModel;
use App\Model\StatusPemilikanModel;
use App\Model\StatusPemodalanModel;
use App\Model\TenagaKerjaModel;
use App\Model\JamsostekModel;
use App\Model\FasilitasPerusahaanModel;
use App\Model\PelanggaranModel;
use App\Model\ProgramPensiunModel;
use App\Model\PerangkatHIModel;
use App\Model\PensiunModel;
use App\Model\FasilitasK3Model;
use App\Model\KesejahteraanModel;
use App\Model\PerangkatHIOrganisasiModel;
use App\Model\PerangkatHIHubunganKerjaModel;
use App\Model\FasilitasKesejahteraanModel;
use App\Model\KluiModel;
use App\Model\BonusModel;
use App\Model\PhioModel;
use App\Model\PhihkModel;
use App\Model\PengupahanModel;
use App\Model\PeralatanK3Model;
use App\Model\FasilitasPerusahaanKesejahteraanModel;
use App\Model\ThrModel;
use App\Model\FasilitasPerusahaanK3Model;
use Psr\Container\ContainerInterface;
use Respect\Validation\Validator as V;
use App\Library\Email as E;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class Perusahaan extends Library
{
    protected $jenisusahaId;
    protected $slug;
    protected $parsedPHIOrganisasi;
    protected $parsedPHIHubunganKerja;
    protected $parsedProgramPensiun;
    protected $parsedFasilitasK3;
    protected $parsedFasilitasKesejahteraan;
    protected $videoProvider;
    protected $videoId;
    protected $publishAt;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->jenisusahaId = null;
        $this->slug = null;
        $this->parsedPHIOrganisasi = null;
        $this->parsedPHIHubunganKerja = null;
        $this->parsedProgramPensiun = null;
        $this->parsedFasilitasK3 = null;
        $this->parsedFasilitasKesejahteraan = null;
        $this->videoProvider = null;
        $this->videoId = null;
        $this->publishAt = Carbon::now();
    }

    public function addPost()
    {
        $r = $this->container->request->getParams();

        $this->validateUsers();

        // // Process Slug
        $this->processSlug();

        if ($this->validator->isValid()) {

            $user = $this->update_user();
            if ( $user ) {
                $post = new BkolPerusahaan;
                $post->companyname = $r['companyname'];
                $post->flag_perusahaan = $r['flag_perusahaan'];
                $post->id = $user->id;
                $post->user_id = $user->id;
                $post->slug = $this->slug;
                
                if ( $post->save() ) {
                    $this->container->flash->addMessage('success', 'Data Berhasil Di Simpan');
                } else {
                    $this->container->flash->addMessage('danger', 'Data Gagal Di Simpan');
                }
                return true;
            }
            $this->container->flash->addMessage('danger', 'Data Gagal Di Simpan');
            return false;
        }


        // $this->container->flash->addMessageNow('danger', 'Gagal Menammbahkan');
        return false;
    }

    public function updatePost($perusahaanId)
    {
        $this->blogEdit = true;

        $r = $this->container->request->getParams();

        //Check Post
        $post = BkolPerusahaan::find($perusahaanId);

        if (!$post) {
            return false;
        }
        
        $this->validateUsers(false);

        //var_dump( $this->validator->getErrors() );

        if ($this->validator->isValid()) {
            
            $user = $this->update_user( $perusahaanId );
            if ( $user ) {
                $post = new BkolPerusahaan;
                $post = $post->where('id', '=', $user->id)->first();
                $post->companyname =  $r['companyname'];
                $post->flag_perusahaan = $r['flag_perusahaan'];
                $post->id = $user->id;
                $post->user_id = $user->id;
                $post->slug =  $this->slug;
                
                if ( $post->save() ) {
                    $this->container->flash->addMessage('success', 'Data Berhasil Di Simpan');
                } else {
                    $this->container->flash->addMessage('danger', 'Data Gagal Di Simpan');
                }
                return true;
            }

            $this->container->flash->addMessage('success', 'Data Perusahaan Berhasil Di Edit');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Data Perusahaan Gagal Di Edit.');
        return false;
    }

    // PENGUPAHAN
    public function addPengupahan($perusahaanId)
    {
        $r = $this->container->request->getParams();
        $pengupahan = JU::find($perusahaanId);
        $perusahaan = JU::find($perusahaanId);
        if (!$pengupahan) {
            return false;
        }
        // $checkTahun = PengupahanModel::where('tahun', '=', $r['tahun'])->get()->count();
        // if ($checkTahun  > 0) {
        //     $this->validator->addError('tahun', 'Nama Sudah tersedia');
        // }
        if ($this->validator->isValid()) {
            if ($perusahaan->save()) {
                $pengupahan = new PengupahanModel;
                $pengupahan->perusahaan_id = $perusahaanId;
                $pengupahan->jumlah_perbulan = $r['jumlah_perbulan'];
                $pengupahan->tahun = $r['tahun'];
                $pengupahan->upah_max = $r['upah_max'];
                $pengupahan->upah_min = $r['upah_min'];
                $pengupahan->jumlah_penerima_org = $r['jumlah_penerima_org'];
                $pengupahan->jumlah_penerima_persen = $r['jumlah_penerima_persen'];
                $pengupahan->bonus_id = $r['bonus_id'];
                $pengupahan->thr_id = $r['thr_id'];
                $pengupahan->save();
            }
            $this->container->flash->addMessage('success', 'Data Pengupahan Berhasil Di Tambahkan');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Data Tahun Tersebut Sudah ada' );
        return false;
    }

    public function updatePengupahan($perusahaanId)
    {
        $r = $this->container->request->getParams();

        //Check Post
        $updatePengupahan = PengupahanModel::find($perusahaanId);
        if (!$updatePengupahan) {
            return false;
        }
        if ($this->validator->isValid()) {
            $updatePengupahan->jumlah_perbulan = $r['jumlah_perbulan'];
            $updatePengupahan->tahun = $r['tahun'];
            $updatePengupahan->upah_max = $r['upah_max'];
            $updatePengupahan->upah_min = $r['upah_min'];
            $updatePengupahan->jumlah_penerima_org = $r['jumlah_penerima_org'];
            $updatePengupahan->jumlah_penerima_persen = $r['jumlah_penerima_persen'];
            $updatePengupahan->bonus_id = $r['bonus_id'];
            $updatePengupahan->thr_id = $r['thr_id'];
            $updatePengupahan->save();
            if ($r['status']) {
                $updatePengupahan->status = 1;
            }
            if ($updatePengupahan->save()) {
            }
            $this->container->flash->addMessage('success', 'Data Pengupahan Berhasil Di Edit');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Data Pengupahan Gagal Di Edit.');
        return false;

    }

    public function deletePengupahan($perusahaanId)
    {
        $r = $this->container->request->getParams();
        //Check Post
        $updatePengupahan = PengupahanModel::find($perusahaanId);
        if (!$updatePengupahan) {
            return false;
        }
        if ($this->validator->isValid()) {
            $updatePengupahan->delete();
            if ($r['status']) {
                $updatePengupahan->status = 1;
            }
            if ($updatePengupahan->delete()) {
            }
            $this->container->flash->addMessage('success', 'Data Pengupahan Berhasil Di Hapus');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Data Pengupahan Gagal Di Edit.');
        return false;
    }
    // JAMSOSTEK

    public function addJamsostek($perusahaanId)
    {
        $r = $this->container->request->getParams();
        $jamsostek = JU::find($perusahaanId);
        $perusahaan = JU::find($perusahaanId);
        if (!$jamsostek) {
            return false;
        }
        // $checkTahun = JamsostekModel::where('tahun', '=', $r['tahun'])->get()->count();
        // if ($checkTahun  > 0) {
        //     $this->validator->addError('tahun', 'Nama Sudah tersedia');
        // }
        if ($this->validator->isValid()) {
            if ($perusahaan->save()) {
                $jamsostek = new JamsostekModel;
                $jamsostek->perusahaan_id = $perusahaanId;
                $jamsostek->tgl_kepesertaan = $r['tgl_kepesertaan'];
                $jamsostek->tahun = $r['tahun'];
                $jamsostek->npp = $r['npp'];
                $jamsostek->program_tk = $r['program_tk'];
                $jamsostek->program_kel = $r['program_kel'];
                $jamsostek->program_jkk = $r['program_jkk'];
                $jamsostek->program_jk = $r['program_jk'];
                $jamsostek->program_jht = $r['program_jht'];
                $jamsostek->program_jpk = $r['program_jpk'];
                $jamsostek->save();
            }
            $this->container->flash->addMessage('success', 'Data Pengupahan Berhasil Di Tambahkan');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Data Tahun Tersebut Sudah ada' );
        return false;
    }

    public function updateJamsostek($perusahaanId)
    {
        $r = $this->container->request->getParams();

        //Check Post
        $updateJamsostek = JamsostekModel::find($perusahaanId);
        if (!$updateJamsostek) {
            return false;
        }
        if ($this->validator->isValid()) {
            $updateJamsostek->tgl_kepesertaan = $r['tgl_kepesertaan'];
            $updateJamsostek->tahun = $r['tahun'];
            $updateJamsostek->npp = $r['npp'];
            $updateJamsostek->program_tk = $r['program_tk'];
            $updateJamsostek->program_kel = $r['program_kel'];
            $updateJamsostek->program_jkk = $r['program_jkk'];
            $updateJamsostek->program_jk = $r['program_jk'];
            $updateJamsostek->program_jht = $r['program_jht'];
            $updateJamsostek->program_jpk = $r['program_jpk'];
            $updateJamsostek->save();
            if ($r['status']) {
                $updateJamsostek->status = 1;
            }
            if ($updateJamsostek->save()) {
            }
            $this->container->flash->addMessage('success', 'Data Jamsostek Berhasil Di Edit');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Data Jamsostek Gagal Di Edit.');
        return false;

    }

    public function deleteJamsostek($perusahaanId)
    {
        $r = $this->container->request->getParams();
        //Check Post
        $updateJamsostek = JamsostekModel::find($perusahaanId);
        if (!$updateJamsostek) {
            return false;
        }
        if ($this->validator->isValid()) {
            $updateJamsostek->delete();
            if ($r['status']) {
                $updateJamsostek->status = 1;
            }
            if ($updateJamsostek->delete()) {
            }
            $this->container->flash->addMessage('success', 'Data Jamsostek Berhasil Di Hapus');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Data Jamsostek Gagal Di Hapus');
        return false;
    }

    // PELANGGARAN
    public function addPelanggaran($perusahaanId)
    {
        $r = $this->container->request->getParams();
        $pelanggaran = JU::find($perusahaanId);
        $perusahaan = JU::find($perusahaanId);
        if (!$pelanggaran) {
            return false;
        }
        // $checkTahun = PelanggaranModel::where('tahun', '=', $r['tahun'])->get()->count();
        // if ($checkTahun  > 0) {
        //     $this->validator->addError('tahun', 'Nama Sudah tersedia');
        // }
        if ($this->validator->isValid()) {
            if ($perusahaan->save()) {
                $pelanggaran = new PelanggaranModel;
                $pelanggaran->perusahaan_id = $perusahaanId;
                $pelanggaran->tahun = $r['tahun'];
                $pelanggaran->pwbd = $r['pwbd'];
                $pelanggaran->pds_tk = $r['pds_tk'];
                $pelanggaran->pds_jml = $r['pds_jml'];
                $pelanggaran->pds_upah = $r['pds_upah'];
                $pelanggaran->pds_program = $r['pds_program'];
                $pelanggaran->save();


            }
            $this->container->flash->addMessage('success', 'Data Pengupahan Berhasil Di Tambahkan');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Data Tahun Tersebut Sudah ada' );
        return false;
    }
    public function updatePelanggaran($perusahaanId)
    {
        $r = $this->container->request->getParams();

        //Check Post
        $updatePelanggaran = PelanggaranModel::find($perusahaanId);
        if (!$updatePelanggaran) {
            return false;
        }
        if ($this->validator->isValid()) {
            $updatePelanggaran->tahun = $r['tahun'];
            $updatePelanggaran->pwbd = $r['pwbd'];
            $updatePelanggaran->pds_tk = $r['pds_tk'];
            $updatePelanggaran->pds_jml = $r['pds_jml'];
            $updatePelanggaran->pds_upah = $r['pds_upah'];
            $updatePelanggaran->pds_program = $r['pds_program'];
            $updatePelanggaran->save();
            if ($r['status']) {
                $updatePelanggaran->status = 1;
            }
            if ($updatePelanggaran->save()) {
            }
            $this->container->flash->addMessage('success', 'Data Jamsostek Berhasil Di Edit');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Data Jamsostek Gagal Di Edit.');
        return false;

    }

    public function deletePelanggaran($perusahaanId)
    {
        $r = $this->container->request->getParams();

        //Check Post
        $updatePelanggaran = PelanggaranModel::find($perusahaanId);
        if (!$updatePelanggaran) {
            return false;
        }
        if ($this->validator->isValid()) {
            $updatePelanggaran->delete();
            if ($r['status']) {
                $updatePelanggaran->status = 1;
            }
            if ($updatePelanggaran->delete()) {
            }
            $this->container->flash->addMessage('success', 'Data Jamsostek Berhasil Di Hapus');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Data Jamsostek Gagal Di Hapus');
        return false;

    }

    // TENAGAKERJA
    public function addTenagakerja($perusahaanId)
    {
        $r = $this->container->request->getParams();
        $tenagakerja = JU::find($perusahaanId);
        $perusahaan = JU::find($perusahaanId);
        if (!$tenagakerja) {
            return false;
        }
        // $checkTahun = TenagaKerjaModel::where('tahun', '=', $r['tahun'])->get()->count();
        // if ($checkTahun  > 0) {
        //     $this->validator->addError('tahun', 'Nama Sudah tersedia');
        // }
        if ($this->validator->isValid()) {
            if ($perusahaan->save()) {
                $tenagakerja = new TenagaKerjaModel;
                $tenagakerja->perusahaan_id = $perusahaanId;
                $tenagakerja->tahun = $r['tahun'];
                $tenagakerja->wni_laki_laki = $r['wni_laki_laki'];
                $tenagakerja->wni_perempuan = $r['wni_perempuan'];
                $tenagakerja->wna_laki_laki = $r['wna_laki_laki'];
                $tenagakerja->wna_perempuan = $r['wna_perempuan'];
                $tenagakerja->jumlah_wni = $r['wni_laki_laki'] + $r['wni_perempuan'];
                $tenagakerja->jumlah_wna = $r['wna_laki_laki'] + $r['wna_perempuan'];
                $tenagakerja->jumlah_tenaga_kerja = $r['wni_laki_laki'] + $r['wni_perempuan'] + $r['wna_laki_laki'] + $r['wna_perempuan'] ;
                $tenagakerja->save();


            }
            $this->container->flash->addMessage('success', 'Data Pengupahan Berhasil Di Tambahkan');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Data Tahun Tersebut Sudah ada' );
        return false;
    }
    public function updateTenagakerja($perusahaanId)
    {
        $r = $this->container->request->getParams();

        //Check Post
        $updatetenagakerja = TenagaKerjaModel::find($perusahaanId);
        if (!$updatetenagakerja) {
            return false;
        }

        if ($this->validator->isValid()) {
            $updatetenagakerja->tahun = $r['tahun'];
            $updatetenagakerja->wni_laki_laki = $r['wni_laki_laki'];
            $updatetenagakerja->wni_perempuan = $r['wni_perempuan'];
            $updatetenagakerja->wna_laki_laki = $r['wna_laki_laki'];
            $updatetenagakerja->wna_perempuan = $r['wna_perempuan'];
            $updatetenagakerja->jumlah_wni = $r['wni_laki_laki'] + $r['wni_perempuan'];
            $updatetenagakerja->jumlah_wna = $r['wna_laki_laki'] + $r['wna_perempuan'];
            $updatetenagakerja->jumlah_tenaga_kerja = $r['wni_laki_laki'] + $r['wni_perempuan'] + $r['wna_laki_laki'] + $r['wna_perempuan'];
            $updatetenagakerja->save();
            if ($r['status']) {
                $updatetenagakerja->status = 1;
            }
            if ($updatetenagakerja->save()) {
            }
            $this->container->flash->addMessage('success', 'Data Tenaga Kerja Berhasil Di Edit');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Data Tenaga Kerja Gagal Di Edit.');
        return false;

    }

    public function deleteTenagakerja($perusahaanId)
    {
        $r = $this->container->request->getParams();

        //Check Post
        $updatetenagakerja = TenagaKerjaModel::find($perusahaanId);
        if (!$updatetenagakerja) {
            return false;
        }

        if ($this->validator->isValid()) {
            $updatetenagakerja->delete();
            if ($r['status']) {
                $updatetenagakerja->status = 1;
            }
            if ($updatetenagakerja->delete()) {
            }
            $this->container->flash->addMessage('success', 'Data Tenaga Kerja Berhasil Di Hapus');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Data Tenaga Kerja Gagal Di Hapus.');
        return false;

    }

    // PERALATANk3
    public function addPeralatanK3($perusahaanId)
    {
        $r = $this->container->request->getParams();
        $peralatank3 = JU::find($perusahaanId);
        $perusahaan = JU::find($perusahaanId);
        if (!$peralatank3) {
            return false;
        }
        // $checkTahun = peralatank3Model::where('tahun', '=', $r['tahun'])->get()->count();
        // if ($checkTahun  > 0) {
        //     $this->validator->addError('tahun', 'Nama Sudah tersedia');
        // }
        if ($this->validator->isValid()) {
            if ($perusahaan->save()) {
                $peralatank3 = new peralatank3Model;
                $peralatank3->perusahaan_id = $perusahaanId;
                $peralatank3->tahun = $r['tahun'];
                $peralatank3->PU = $r['PU'];
                $peralatank3->PA_AND_A = $r['PA_AND_A'];
                $peralatank3->MTR = $r['MTR'];
                $peralatank3->IL = $r['IL'];
                $peralatank3->IPK = $r['IPK'];
                $peralatank3->IPP = $r['IPP'];
                $peralatank3->PL = $r['PL'];
                $peralatank3->LIFT = $r['LIFT'];
                $peralatank3->BT = $r['BT'];
                $peralatank3->B3 = $r['B3'];
                $peralatank3->TRBN = $r['TRBN'];
                $peralatank3->BB = $r['BB'];
                $peralatank3->BRA = $r['BRA'];
                $peralatank3->save();


            }
            $this->container->flash->addMessage('success', 'Data Pengupahan Berhasil Di Tambahkan');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Data Tahun Tersebut Sudah ada' );
        return false;
    }
    public function updatePeralatanK3($perusahaanId)
    {
        $r = $this->container->request->getParams();

        //Check Post
        $updateperalatank3 = peralatank3Model::find($perusahaanId);
        if (!$updateperalatank3) {
            return false;
        }

        if ($this->validator->isValid()) {
            $updateperalatank3->tahun = $r['tahun'];
            $updateperalatank3->PU = $r['PU'];
            $updateperalatank3->PA_AND_A = $r['PA_AND_A'];
            $updateperalatank3->MTR = $r['MTR'];
            $updateperalatank3->IL = $r['IL'];
            $updateperalatank3->IPK = $r['IPK'];
            $updateperalatank3->IPP = $r['IPP'];
            $updateperalatank3->PL = $r['PL'];
            $updateperalatank3->LIFT = $r['LIFT'];
            $updateperalatank3->BT = $r['BT'];
            $updateperalatank3->B3 = $r['B3'];
            $updateperalatank3->TRBN = $r['TRBN'];
            $updateperalatank3->BB = $r['BB'];
            $updateperalatank3->BRA = $r['BRA'];
            $updateperalatank3->save();
            if ($r['status']) {
                $updateperalatank3->status = 1;
            }
            if ($updateperalatank3->save()) {
            }
            $this->container->flash->addMessage('success', 'Data Tenaga Kerja Berhasil Di Edit');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Data Tenaga Kerja Gagal Di Edit.');
        return false;

    }

    public function deletePeralatanK3($perusahaanId)
    {
        $r = $this->container->request->getParams();

        //Check Post
        $updateperalatank3 = peralatank3Model::find($perusahaanId);
        if (!$updateperalatank3) {
            return false;
        }
        if ($this->validator->isValid()) {
            $updateperalatank3->delete();
            if ($r['status']) {
                $updateperalatank3->status = 1;
            }
            if ($updateperalatank3->delete()) {
            }
            $this->container->flash->addMessage('success', 'Data Tenaga Kerja Berhasil Di Hapus');
            return true;
        }
        $this->container->flash->addMessageNow('danger', 'Data Tenaga Kerja Gagal Di Hapus.');
        return false;

    }


    public function delete()
    {
        $post = PerusahaanModel::find($this->container->request->getParam('post_id'));

        if ($post) {
            if ($post->delete()) {
                return true;
            }
        }
        return false;
    }

    public function publish()
    {
        $post = PerusahaanModel::find($this->container->request->getParam('post_id'));

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
        $post = PerusahaanModel::find($this->container->request->getParam('post_id'));

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
            ),
            'description' => array(
                'rules' => V::length(6, 255)->alnum('\',.?!@#$%&*()-_"'),
                    'messages' => array(
                    'length' => 'Must be between 6 and 255 characters.',
                    'alnum' => 'Invalid Characters Only \',.?!@#$%&*()-_" are allowed.'
                )
            )
        );
        $this->validator->validate($this->container->request, $validateData);

        $checkTitle = PerusahaanModel::where('title', $this->container->request->getParam('title'));
        if ($postId) {
            $checkTitle = $checkTitle->where('id', '!=', $postId);
        }
        if ($checkTitle->first()) {
            $this->validator->addError('title', 'Duplicate title found.  This is bad for SEO.');
        }
    }

    private function processJenisUsaha()
    {
        $jenisusahaId = $this->container->request->getParam('jenisusaha');

        // Check if category exists by id
        $jenisusahaCheck = JenisUsahaPost::find($jenisusahaId);

        if (!$jenisusahaCheck) {
            // Check if category exists by name
            $checkJU = JenisUsahaPost::where('name', $jenisusahaId)->first();
            if ($checkJU) {
                $jenisusahaId = $checkJU->jenis_usaha_id;
            }

            // Add new category if not exists
            $addJenisUsaha = new JenisUsahaPost;
            $addJenisUsaha->name = $jenisusahaId;
            $addJenisUsaha->status = 1;
            $addJenisUsaha->save();
            $jenisusahaId = $addJenisUsaha->id;
        }

        $this->jenisusahaId = $jenisusahaId;
    }

    private function processPHIOrganisasi()
    {
        foreach ($this->container->request->getParam('phiorganisasi') as $value) {
            // Check if Already Numeric
            if (is_numeric($value)) {
                $check = PerangkatHIOrganisasiModel::find($value);
                if ($check) {
                    $this->parsedPHIOrganisasi[] = $value;
                }
                continue;
            }

            $nama = Utils::slugify($value);
            $namaCheck = PerangkatHIOrganisasiModel::where('nama', '=', $nama)->first();
            if ($namaCheck) {
                $this->parsedPHIOrganisasi[] = $namaCheck->id;
                continue;
            }

            // Add New Tag To Database
            $newPHIO = new PerangkatHIOrganisasiModel;
            $newPHIO->nama = $value;
            if ($newPHIO->save()) {
                if ($newPHIO->id) {
                    $this->parsedPHIOrganisasi[] = $newPHIO->id;
                }
            }
        }
    }
    private function processPHIHubunganKerja()
    {
        foreach ($this->container->request->getParam('phihubungankerja') as $value) {
            // Check if Already Numeric
            if (is_numeric($value)) {
                $check = PerangkatHIHubunganKerjaModel::find($value);
                if ($check) {
                    $this->parsedPHIHubunganKerja[] = $value;
                }
                continue;
            }
            // Check if name already exists
            $nama = Utils::slugify($value);
            $namaCheck = PerangkatHIHubunganKerjaModel::where('nama', '=', $nama)->first();
            if ($namaCheck) {
                $this->parsedPHIHubunganKerja[] = $namaCheck->id;
                continue;
            }

            // Add New Tag To Database
            $newPHIHK = new PerangkatHIHubunganKerjaModel;
            $newPHIHK->nama = $value;
            if ($newPHIHK->save()) {
                if ($newPHIHK->id) {
                    $this->parsedPHIHubunganKerja[] = $newPHIHK->id;
                }
            }
        }
    }

    private function processProgramPensiun()
    {
        foreach ($this->container->request->getParam('programpensiun') as $value) {
            // Check if Already Numeric
            if (is_numeric($value)) {
                $check = PensiunModel::find($value);
                if ($check) {
                    $this->parsedProgramPensiun[] = $value;
                }
                continue;
            }

            // Check if name already exists
            $name = Utils::slugify($value);
            $nameCheck = PensiunModel::where('name', '=', $name)->first();
            if ($nameCheck) {
                $this->parsedProgramPensiun[] = $nameCheck->id;
                continue;
            }

            // Add New Tag To Database
            $newProgramPensiun = new PensiunModel;
            $newProgramPensiun->name = $value;
            if ($newProgramPensiun->save()) {
                if ($newProgramPensiun->id) {
                    $this->parsedProgramPensiun[] = $newProgramPensiun->id;
                }
            }
        }
    }

    private function processFasilitasK3()
    {
        foreach ($this->container->request->getParam('fasilitask3') as $value) {
            // Check if Already Numeric
            if (is_numeric($value)) {
                $check = FasilitasK3Model::find($value);
                if ($check) {
                    $this->parsedFasilitasK3[] = $value;
                }
                continue;
            }

            // Check if name already exists
            $name = Utils::slugify($value);
            $nameCheck = FasilitasK3Model::where('name', '=', $name)->first();
            if ($nameCheck) {
                $this->parsedFasilitasK3[] = $nameCheck->id;
                continue;
            }

            // Add New Tag To Database
            $newFasilitasK3 = new FasilitasK3Model;
            $newFasilitasK3->name = $value;
            if ($newFasilitasK3->save()) {
                if ($newFasilitasK3->id) {
                    $this->parsedFasilitasK3[] = $newFasilitasK3->id;
                }
            }
        }
    }

    private function processFasilitasKesejahteraan()
    {
        foreach ($this->container->request->getParam('fasilitaskesjahteraan') as $value) {
            // Check if Already Numeric
            if (is_numeric($value)) {
                $check = KesejahteraanModel::find($value);
                if ($check) {
                    $this->parsedFasilitasKesejahteraan[] = $value;
                }
                continue;
            }

            // Check if name already exists
            $name = Utils::slugify($value);
            $nameCheck = KesejahteraanModel::where('name', '=', $name)->first();
            if ($nameCheck) {
                $this->parsedFasilitasKesejahteraan[] = $nameCheck->id;
                continue;
            }

            // Add New Tag To Database
            $newFasilitasKesejahteraan = new KesejahteraanModel;
            $newFasilitasKesejahteraan->name = $value;
            if ($newFasilitasKesejahteraan->save()) {
                if ($newFasilitasKesejahteraan->id) {
                    $this->parsedFasilitasKesejahteraan[] = $newFasilitasKesejahteraan->id;
                }
            }
        }
    }

    private function processSlug($postId = null)
    {
        $slug = Utils::slugify($this->container->request->getParam('companyname'));
        $checkSlug = BkolPerusahaan::where('slug', $slug);
        if ($postId) {
            $checkSlug = $checkSlug->where('id', '!=', $postId);
        }
        if ($checkSlug->first()) {
            $this->validator->addError('title', 'Nama Perusahaan Sudah Di Pakai');
            return false;
        }
        $this->slug = $slug;
        return true;
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
            'companyname' => array(
                'rules' => V::notEmpty()->alnum('\'-')->length(2, 250),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'alnum' => 'Dapat berisi huruf, angka, \' tanda hubung.',
                    'length' => "Harus antara 2 hingga 250 karakter."
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
            /*'companysaddress' => array(
                'rules' => V::notEmpty()->alnum('\'-')->length(2, 100),
                'messages' => array(
                    'notEmpty' => 'Harus di isi.',
                    'alnum' => 'Dapat berisi huruf, angka, \' tanda hubung.',
                    'length' => "Harus antara 2 hingga 100 karakter."
                    )
            )*/
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

    public function update_user( $user_id = null )
    {
        
        if ( $user_id ) {
            $user = U::with('companiesprofile')->find($user_id);
            if ( $user->email !== $this->container->request->getParam('email') ) {
                $user->email = $this->container->request->getParam('email');
            }
            $user->username = $this->container->request->getParam('username');
            //$user->password = $this->container->request->getParam('password');
            $user->companyname = $this->container->request->getParam('companyname');
            $user->fullname = $this->container->request->getParam('fullname');
            $user->save();

        } else {
            $userDetails = [
                'email' => $this->container->request->getParam('email'),
                'username' => $this->container->request->getParam('username'),
                'password' => $this->container->request->getParam('password'),
                'companyname' => $this->container->request->getParam('companyname'),
                'fullname' => $this->container->request->getParam('fullname'),
                'permissions' => [
                    'user.delete' => 0
                ]
            ];

            $role = $this->container->auth->findRoleByName('Companies');
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
