<?php

$container['Cron'] = function ($container) {
    return new \App\Controller\Cron($container);
};



// Untuk Admin
$container['Admin'] = function ($container) {
    return new \App\Controller\Admin\Admin($container);
};

$container['MyCompanies'] = function ($container) {
    return new \App\Controller\Admin\MyCompanies($container);
};

// ADMIN BLOG
$container['AdminBlog'] = function ($container) {
    return new \App\Controller\Admin\Blog\Blog($container);
};
$container['AdminBlogCategories'] = function ($container) {
    return new \App\Controller\Admin\Blog\BlogCategories($container);
};
$container['AdminBlogComments'] = function ($container) {
    return new \App\Controller\Admin\Blog\BlogComments($container);
};
$container['AdminBlogTags'] = function ($container) {
    return new \App\Controller\Admin\Blog\BlogTags($container);
};

// Gallery
$container['AdminGallery'] = function ($container) {
    return new \App\Controller\Admin\Gallery\Gallery($container);
};
$container['AdminGalleryCategories'] = function ($container) {
    return new \App\Controller\Admin\Gallery\GalleryCategories($container);
};




// Admin Page
$container['AdminPage'] = function ($container) {
    return new \App\Controller\Admin\Page\Page($container);
};
$container['AdminPageCategories'] = function ($container) {
    return new \App\Controller\Admin\Page\PageCategories($container);
};

// Admin Faq
$container['AdminFaq'] = function ($container) {
    return new \App\Controller\Admin\Faq\Faq($container);
};
$container['AdminFaqCategories'] = function ($container) {
    return new \App\Controller\Admin\Faq\FaqCategories($container);
};

// ADMIN JOB
$container['AdminJob'] = function ($container) {
    return new \App\Controller\Admin\Job\Job($container);
};
$container['AdminJobCategories'] = function ($container) {
    return new \App\Controller\Admin\Job\JobCategories($container);
};
$container['AdminJobApply'] = function ($container) {
    return new \App\Controller\Admin\Job\JobApply($container);
};
$container['AdminJobTags'] = function ($container) {
    return new \App\Controller\Admin\Job\JobTags($container);
};
$container['AdminProposal'] = function ($container) {
    return new \App\Controller\Admin\Proposal($container);
};

// ADMIN Agenda
$container['AdminAgenda'] = function ($container) {
    return new \App\Controller\Admin\Agenda\Agenda($container);
};

$container['AdminProdukHukum'] = function ($container) {
    return new \App\Controller\Admin\ProdukHukum($container);
};
$container['AdminAgendaCategories'] = function ($container) {
    return new \App\Controller\Admin\Agenda\AgendaCategories($container);
};

// ADMIN Pelatihan
$container['AdminPelatihan'] = function ($container) {
    return new \App\Controller\Admin\Pelatihan\Pelatihan($container);
};
$container['AdminPelatihanCategories'] = function ($container) {
    return new \App\Controller\Admin\Pelatihan\PelatihanCategories($container);
};
$container['AdminPelatihanTags'] = function ($container) {
    return new \App\Controller\Admin\Pelatihan\PelatihanTags($container);
};
$container['AdminPesertaPelatihan'] = function ($container) {
    return new \App\Controller\Admin\Pelatihan\PesertaPelatihan($container);
};

// AdminUserManagemet
$container['AdminUsers'] = function ($container) {
    return new \App\Controller\Admin\UsersManagement\Users($container);
};
$container['AdminUsersBKK'] = function ($container) {
    return new \App\Controller\Admin\UsersManagement\UsersBKK($container);
};
$container['AdminUsersLPK'] = function ($container) {
    return new \App\Controller\Admin\UsersManagement\UsersLPK($container);
};

// Admin Email
$container['AdminEmail'] = function ($container) {
    return new \App\Controller\Admin\Email($container);
};

$container['AdminMedia'] = function ($container) {
    return new \App\Controller\Admin\Media($container);
};

$container['AdminOauth2'] = function ($container) {
    return new \App\Controller\Admin\Oauth2($container);
};

$container['AdminRoles'] = function ($container) {
    return new \App\Controller\Admin\Roles($container);
};

$container['AdminSeo'] = function ($container) {
    return new \App\Controller\Admin\Seo($container);
};

$container['AdminSettings'] = function ($container) {
    return new \App\Controller\Admin\Settings($container);
};



// Untuk Disnaker
$container['App'] = function ($container) {
    return new \App\Controller\App($container);
};

$container['Auth'] = function ($container) {
    return new \App\Controller\Auth($container);
};

$container['Deploy'] = function ($container) {
    return new \App\Controller\Deploy($container);
};

$container['Blog'] = function ($container) {
    return new \App\Controller\Blog($container);
};

$container['Page'] = function ($container) {
    return new \App\Controller\Page($container);
};
$container['Faq'] = function ($container) {
    return new \App\Controller\Faq($container);
};

$container['Agenda'] = function ($container) {
    return new \App\Controller\Agenda($container);
};
$container['ProdukHukum'] = function ($container) {
    return new \App\Controller\ProdukHukum($container);
};

$container['Pelatihan'] = function ($container) {
    return new \App\Controller\Pelatihan($container);
};

$container['Oauth2'] = function ($container) {
    return new \App\Controller\Oauth2($container);
};

$container['Profile'] = function ($container) {
    return new \App\Controller\Profile($container);
};

// Untuk BKOL

$container['Bkol'] = function ($container) {
    return new \App\Controller\Bkol\Bkol($container);
};

$container['BkolCompany'] = function ($container) {
    return new \App\Controller\Bkol\BkolCompany($container);
};

$container['BkolJobseeker'] = function ($container) {
    return new \App\Controller\Bkol\BkolJobseeker($container);
};

$container['BkolJob'] = function ($container) {
    return new \App\Controller\Bkol\Job\Job($container);
};
$container['BkolJobCategories'] = function ($container) {
    return new \App\Controller\Bkol\Job\JobCategories($container);
};
$container['BkolJobComments'] = function ($container) {
    return new \App\Controller\Bkol\Job\JobComments($container);
};
$container['BkolJobTags'] = function ($container) {
    return new \App\Controller\Bkol\Job\JobTags($container);
};

$container['CompanyProfile'] = function ($container) {
    return new \App\Controller\Bkol\CompanyProfile($container);
};

$container['BkolMedia'] = function ($container) {
    return new \App\Controller\Bkol\Media($container);
};

$container['Gallery'] = function ($container) {
    return new \App\Controller\Gallery($container);
};


// ADMIN JOB
$container['Perusahaan'] = function ($container) {
    return new \App\Controller\Admin\Perusahaan\Perusahaan($container);
};

$container['Pkwt'] = function ($container) {
    return new \App\Controller\Admin\Pkwt\Pkwt($container);
};

$container['PeraturanPerusahaan'] = function ($container) {
    return new \App\Controller\Admin\PeraturanPerusahaan\PeraturanPerusahaan($container);
};

$container['Pkb'] = function ($container) {
    return new \App\Controller\Admin\Pkb\Pkb($container);
};

$container['LksBipartit'] = function ($container) {
    return new \App\Controller\Admin\LksBipartit\LksBipartit($container);
};

$container['LksTripartit'] = function ($container) {
    return new \App\Controller\Admin\LksTripartit\LksTripartit($container);
};

$container['Khl'] = function ($container) {
    return new \App\Controller\Admin\Khl\Khl($container);
};

$container['Umk'] = function ($container) {
    return new \App\Controller\Admin\Umk\Umk($container);
};

$container['RekapUmk'] = function ($container) {
    return new \App\Controller\Admin\RekapUmk\RekapUmk($container);
};

$container['DewanPengupahan'] = function ($container) {
    return new \App\Controller\Admin\DewanPengupahan\DewanPengupahan($container);
};

$container['DPSusunanAnggota'] = function ($container) {
    return new \App\Controller\Admin\DPSusunanAnggota\DPSusunanAnggota($container);
};

$container['DPSidangAnggota'] = function ($container) {
    return new \App\Controller\Admin\DPSidangAnggota\DPSidangAnggota($container);
};

// Untuk Admin
$container['Shortlist'] = function ($container) {
    return new \App\Controller\Admin\Shortlist\Shortlist($container);
};
$container['Master'] = function ($container) {
    return new \App\Controller\Admin\Master\Master($container);
};
$container['JenisUsaha'] = function ($container) {
    return new \App\Controller\Admin\Master\JenisUsaha($container);
};
$container['WaktuKerja'] = function ($container) {
    return new \App\Controller\Admin\Master\WaktuKerja($container);
};
$container['StatusPerusahaan'] = function ($container) {
    return new \App\Controller\Admin\Master\StatusPerusahaan($container);
};
$container['StatusPemilikan'] = function ($container) {
    return new \App\Controller\Admin\Master\StatusPemilikan($container);
};
$container['StatusPemodalan'] = function ($container) {
    return new \App\Controller\Admin\Master\StatusPemodalan($container);
};
$container['Klui'] = function ($container) {
    return new \App\Controller\Admin\Master\Klui($container);
};
$container['PeralatanK3'] = function ($container) {
    return new \App\Controller\Admin\Master\PeralatanK3($container);
};
$container['PerangkatHIOrganisasi'] = function ($container) {
    return new \App\Controller\Admin\Master\PerangkatHIOrganisasi($container);
};
$container['PerangkatHIHubunganKerja'] = function ($container) {
    return new \App\Controller\Admin\Master\PerangkatHIHubunganKerja($container);
};
$container['Pensiun'] = function ($container) {
    return new \App\Controller\Admin\Master\Pensiun($container);
};

$container['FasilitasK3'] = function ($container) {
    return new \App\Controller\Admin\Master\FasilitasK3($container);
};
$container['Kesejahteraan'] = function ($container) {
    return new \App\Controller\Admin\Master\Kesejahteraan($container);
};
$container['Bonus'] = function ($container) {
    return new \App\Controller\Admin\Master\Bonus($container);
};
$container['Thr'] = function ($container) {
    return new \App\Controller\Admin\Master\Thr($container);
};


// ADMIN Data Pencari Kerja
$container['PencariKerja'] = function ($container) {
    return new \App\Controller\Admin\PencariKerja\PencariKerja($container);
};
// MASTER PENCARI KERJA
$container['PetugasPendata'] = function ($container) {
    return new \App\Controller\Admin\Master\PetugasPendata($container);
};

$container['Jurusan'] = function ($container) {
    return new \App\Controller\Admin\Master\Jurusan($container);
};
$container['Lulusan'] = function ($container) {
    return new \App\Controller\Admin\Master\Lulusan($container);
};
$container['KursusPelatihan'] = function ($container) {
    return new \App\Controller\Admin\Master\KursusPelatihan($container);
};
$container['BidangPekerjaan'] = function ($container) {
    return new \App\Controller\Admin\Master\BidangPekerjaan($container);
};


// ADMIN Data Lembaga Pelatihan Kerja
$container['LembagaPelatihanKerja'] = function ($container) {
    return new \App\Controller\Admin\LembagaPelatihanKerja\LembagaPelatihanKerja($container);
};

$container['BursaKerjaKhusus'] = function ($container) {
    return new \App\Controller\Admin\BursaKerjaKhusus\BursaKerjaKhusus($container);
};

$container['Ajax'] = function ($container) {
    return new \App\Controller\Ajax($container);
};

$container['Kbli'] = function ($container) {
    return new \App\Controller\Kbli($container);
};
$container['SKeahlian'] = function ($container) {
    return new \App\Controller\SKeahlian($container);
};

$container['BkolPerusahaan'] = function ($container) {
    return new \App\Controller\Admin\Bkol\BkolPerusahaan($container);
};

$container['BkolPerguruanTinggi'] = function ($container) {
    return new \App\Controller\Admin\Bkol\BkolPerguruanTinggi($container);
};

$container['BkolLpk'] = function ($container) {
    return new \App\Controller\Admin\Bkol\BkolLpk($container);
};

$container['BkolBkk'] = function ($container) {
    return new \App\Controller\Admin\Bkol\BkolBkk($container);
};

$container['BkolPemerintah'] = function ($container) {
    return new \App\Controller\Admin\Bkol\BkolPemerintah($container);
};

$container['UserBkolPencariKerja'] = function ($container) {
    return new \App\Controller\Admin\Bkol\UserBkolPencariKerja($container);
};

$container['AK1'] = function ($container) {
    return new \App\Controller\Bkol\AK1($container);
};
$container['AK1Admin'] = function ($container) {
    return new \App\Controller\Admin\AK1Admin($container);
};

$container['Pelatihanku'] = function ($container) {
    return new \App\Controller\Bkol\Pelatihanku($container);
};

$container['Serikat'] = function ($container) {
    return new \App\Controller\Admin\Serikat\Serikat($container);
};
$container['Federasi'] = function ($container) {
    return new \App\Controller\Admin\Serikat\Federasi($container);
};
$container['PPHI'] = function ($container) {
    return new \App\Controller\Admin\PPHI\PPHI($container);
};

// IPK
$container['IPK'] = function ($container) {
    return new \App\Controller\Admin\IPK\IPK($container);
};
$container['IPKIkhtisarStatistika'] = function ($container) {
    return new \App\Controller\Admin\IPK\IPKIkhtisarStatistika($container);
};
$container['IPKJenisPendidikan'] = function ($container) {
    return new \App\Controller\Admin\IPK\IPKJenisPendidikan($container);
};

$container['DataPerkembanganPencariKerja'] = function ($container) {
    return new \App\Controller\Admin\PencariKerja\DataPerkembanganPencariKerja($container);
};
$container['RekapTki'] = function ($container) {
    return new \App\Controller\Admin\Tki\RekapTki($container);
};

$container['ImtaPerpanjangan'] = function ($container) {
    return new \App\Controller\Admin\Imta\ImtaPerpanjangan($container);
};
$container['DataAk1'] = function ($container) {
    return new \App\Controller\Admin\DataAk1\DataAk1($container);
};

$container['BkkLpk'] = function ($container) {
    return new \App\Controller\BkkLpk($container);
};


// Dashboard Perusahaan
// Dashboard Perusahaan Lowongan
$container['DPerusahaanJob'] = function ($container) {
    return new \App\Controller\DashboardPerusahaan\Job\Job($container);
};
$container['DPerusahaanJobApply'] = function ($container) {
    return new \App\Controller\DashboardPerusahaan\Job\JobApply($container);
};
$container['ProdukJasaPerusahaan'] = function ($container) {
    return new \App\Controller\DashboardPerusahaan\ProdukJasaPerusahaan($container);
};


// Untuk Admin
$container['DashboardPerusahaan'] = function ($container) {
    return new \App\Controller\DashboardPerusahaan\DashboardPerusahaan($container);
};
$container['ProfilePerusahaan'] = function ($container) {
    return new \App\Controller\DashboardPerusahaan\ProfilePerusahaan($container);
};

// Dashboard Pencaker
$container['DashboardPencaker'] = function ($container) {
    return new \App\Controller\DashboardPencaker\DashboardPencaker($container);
};
$container['DashboardPencakerResume'] = function ($container) {
    return new \App\Controller\DashboardPencaker\Resume($container);
};
$container['Pendidikan'] = function ($container) {
    return new \App\Controller\Admin\Resume\Pendidikan($container);
};
$container['Pekerjaan'] = function ($container) {
    return new \App\Controller\Admin\Resume\Pekerjaan($container);
};
$container['PendidikanNonFormal'] = function ($container) {
    return new \App\Controller\Admin\Resume\PendidikanNonFormal($container);
};
$container['Kompetensi'] = function ($container) {
    return new \App\Controller\Admin\Resume\Kompetensi($container);
};

// DASHBOARD BKK
$container['DashboardBkk'] = function ($container) {
    return new \App\Controller\DashboardBkk\DashboardBkk($container);
};
$container['ProfileBursaKerjaKhusus'] = function ($container) {
    return new \App\Controller\DashboardBkk\ProfileBursaKerjaKhusus($container);
};
$container['JurusanBkk'] = function ($container) {
    return new \App\Controller\DashboardBkk\JurusanBkk($container);
};

// DASHBOARD LPK
$container['DashboardLpk'] = function ($container) {
    return new \App\Controller\DashboardLpk\DashboardLpk($container);
};
$container['ProfileLembagaPelatihanKerja'] = function ($container) {
    return new \App\Controller\DashboardLpk\ProfileLembagaPelatihanKerja($container);
};
$container['KeterampilanLpk'] = function ($container) {
    return new \App\Controller\DashboardLpk\KeterampilanLpk($container);
};

// DASHBOARD Perguruan Tinggi
$container['DashboardPerguruanTinggi'] = function ($container) {
    return new \App\Controller\DashboardPerguruanTinggi\DashboardPerguruanTinggi($container);
};
$container['ProfilePerguruanTinggi'] = function ($container) {
    return new \App\Controller\DashboardPerguruanTinggi\ProfilePerguruanTinggi($container);
};
$container['ProgramStudiDikti'] = function ($container) {
    return new \App\Controller\DashboardPerguruanTinggi\ProgramStudiDikti($container);
};

// Front Dashboard
$container['FrontBeritaDashboard'] = function ($container) {
    return new \App\Controller\Dashboard\Berita\BeritaDashboard($container);
};
$container['FrontAgendaDashboard'] = function ($container) {
    return new \App\Controller\Dashboard\Agenda\AgendaDashboard($container);
};
$container['FrontGalleryDashboard'] = function ($container) {
    return new \App\Controller\Dashboard\Gallery\GalleryDashboard($container);
};
$container['PengaturanAkun'] = function ($container) {
    return new \App\Controller\Dashboard\PengaturanAkun($container);
};



// Front Minisite
$container['MinisiteBkk'] = function ($container) {
    return new \App\Controller\Front\Minisite\Bkk($container);
};
$container['MinisiteLpk'] = function ($container) {
    return new \App\Controller\Front\Minisite\Lpk($container);
};
$container['MinisitePerusahaan'] = function ($container) {
    return new \App\Controller\Front\Minisite\Perusahaan($container);
};
$container['MinisitePemerintah'] = function ($container) {
    return new \App\Controller\Front\Minisite\Pemerintah($container);
};
$container['MinisitePerguruanTinggi'] = function ($container) {
    return new \App\Controller\Front\Minisite\PerguruanTinggi($container);
};
$container['MinisitePencariKerja'] = function ($container) {
    return new \App\Controller\Front\Minisite\PencariKerja($container);
};

$container['Minisite'] = function ($container) {
    return new \App\Controller\Front\Minisite\Minisite($container);
};

// Untuk Pemerintah
$container['DashboardPemerintah'] = function ($container) {
    return new \App\Controller\DashboardPemerintah\DashboardPemerintah($container);
};
$container['ProfilePemerintah'] = function ($container) {
    return new \App\Controller\DashboardPemerintah\ProfilePemerintah($container);
};

$container['DashboardLowongan'] = function ($container) {
    return new \App\Controller\Dashboard\Lowongan\Lowongan($container);
};
$container['DashboardPelamarLowongan'] = function ($container) {
    return new \App\Controller\Dashboard\Lowongan\PelamarLowongan($container);
};

$container['DashboardPelamarLowonganPemerintah'] = function ($container) {
    return new \App\Controller\Dashboard\Lowongan\PelamarLowonganPemerintah($container);
};

$container['DashboardPelatihan'] = function ($container) {
    return new \App\Controller\Dashboard\Pelatihan\Pelatihan($container);
};

$container['DashboardPelatihanPemerintah'] = function ($container) {
    return new \App\Controller\Dashboard\Pelatihan\PelatihanPemerintah($container);
};