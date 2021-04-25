<?php


namespace App\Controller\DashboardPerusahaan\Job;
use App\Controller\Controller;
use Carbon\Carbon;
use App\Library\VideoParser as VP;
use App\Model\JobCategories;
use App\Model\JobTags;
use App\Model\JobPosts;
use App\Model\AgendaPosts;
use App\Model\JobPostsApply;
use App\Model\BkolPencariKerja;
use App\Library\Email as E;
use App\Model\JobPostsTags;
use App\Model\PendidikanPosts;
use App\Model\PendidikanNonFormalPosts;
use App\Model\PekerjaanPosts;
use App\Model\Users;
use JasonGrimes\Paginator;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class JobApply extends Controller
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
     public function Review(Request $request, Response $response, $routeArgs, $args)
     {
         $routeArgs =  $request->getAttribute('route')->getArguments();
         $args =  $request->getAttribute('route')->getArguments();
         $checkJobseeker = Users::where('username', $routeArgs['username'])->first();
         if (!$checkJobseeker) {
             $this->flash('warning', 'Pelamar Tidak Di Temukan.');
             return $this->redirect($response, 'dashboardperusahaan-pelamar');
         }
         $pelamar = new JobPostsApply;
         $pelamar = $pelamar->where('id', $args['id']);
         $userId = $this->auth->check()->id;
         $pelamar = $pelamar->whereHas(
             'post',
             function ($query) use ($userId) {
                 $query->where('user_id', '=', $userId);
             }
         );

         $pelamar = $pelamar->first();
         if ($pelamar) {
             $pelamar->status = 1;
             if ($pelamar->save()) {
                 // $this->flash('success', 'Pelamar Sudah Di review');
                 // return $this->redirect($response, 'dashboardperusahaan-pelamar');
             }
         }
         return $this->view->render(
             $response,
             'bkol/dashboard-perusahaan/pelamar/detail-lamaran.twig',
             array(
                 "pelamar" => $pelamar
             )
         );
    }
    public function apply(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('companies.view')) {
            return $check;
        }
        $user = $this->auth->check()->id;
        $pelamar = JobPostsApply::with([
            'post' => function ($query) {
                $query->select('id', 'nama_lowongan');
            }
        ]);
        $pelamar = $pelamar->whereHas(
            'post',
            function ($query) use ($user) {
                $query->where('user_id', '=', $user);
            }
        );
        return $this->view->render(
            $response,
            'bkol/dashboard-perusahaan/pelamar/pelamar.twig',
            array(
                "pelamar" => $pelamar->get()
            )
        );
    }

    public function DetailLamaran(Request $request, Response $response, $routeArgs, $args)
    {
      $routeArgs =  $request->getAttribute('route')->getArguments();
      $args =  $request->getAttribute('route')->getArguments();
      $checkJobseeker = Users::where('username', $routeArgs['username'])->first();
      if (!$checkJobseeker) {
          $this->flash('warning', 'Pelamar Tidak Di Temukan.');
          return $this->redirect($response, 'dashboardperusahaan-pelamar');
      }
      $pelamar = new JobPostsApply;
      $pelamar = $pelamar->where('id', $args['id']);
      $userId = $this->auth->check()->id;
      $pelamar = $pelamar->whereHas(
          'post',
          function ($query) use ($userId) {
              $query->where('user_id', '=', $userId);
          }
      );

      $pelamar = $pelamar->first();
      return $this->view->render(
          $response,
          'bkol/dashboard-perusahaan/pelamar/detail-lamaran.twig',
          array(
              "pelamar" => $pelamar
          )
      );
   }
    public function JadikanKandidat(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('companies.view')) {
            return $check;
        }
        $pelamar = new JobPostsApply;
        $pelamar = $pelamar->where('id', $request->getParam('apply'));
        $userId = $this->auth->check()->id;
        $pelamar = $pelamar->whereHas(
            'post',
            function ($query) use ($userId) {
                $query->where('user_id', '=', $userId);
            }
        );
        $pelamar = $pelamar->first();
        if ($pelamar) {
            $pelamar->status = 2;
            if ($pelamar->save()) {
                $this->flash('success', 'Pelamar Sudah Di Jadikan Kandidat.');
                return $this->redirect($response, 'dashboardperusahaan-pelamar');
            }
        }
        $this->flash('danger', 'Error.');
        return $this->redirect($response, 'dashboardperusahaan-pelamar');
    }

    public function UndangInterview(Request $request, Response $response, $routeArgs, $args)
    {
      $routeArgs =  $request->getAttribute('route')->getArguments();
      $args =  $request->getAttribute('route')->getArguments();

      $pelamar = new JobPostsApply;
      $pelamar = $pelamar->where('id', $args['id']);
      $userId = $this->auth->check()->id;
      $pelamar = $pelamar->whereHas(
          'post',
          function ($query) use ($userId) {
              $query->where('user_id', '=', $userId);
          }
      );
      $pelamar = $pelamar->first();
      $emailpelamar = $pelamar->user->id;
      if ($request->isPost()) {
            if ($this->validator->isValid()) {
              if ($pelamar) {
                $r = $this->request->getParams();
                $pelamar->status = 3;
                $pelamar->tanggal_interview = $r['tanggal_interview'];
                $pelamar->jam_interview = $r['jam_interview'];
                $pelamar->cp_inteview = $r['cp_inteview'];
                $pelamar->nomor_telepon = $r['nomor_telepon'];
                $pelamar->alamat_interview = $r['alamat_interview'];
                if ($pelamar->save()) {
                    $sendEmail = new E($this->container);
                    $sendEmail = $sendEmail->sendTemplate(
                        array(
                            $emailpelamar
                        ),
                        'undangan-interview',
                        array(
                          'nama_perusahaan' => $pelamar->post->authorCompanies->companyname,
                          'tanggal_interview' => $pelamar->tanggal_interview,
                          'jam_interview' => $pelamar->jam_interview,
                          'konfirmasi_interview' => $pelamar->cp_inteview,
                          'nomor_telepon' => $pelamar->nomor_telepon,
                          'alamat_interview' => $pelamar->alamat_interview
                        )
                    );
                    $this->flash('success', 'Undangan Interview Sudah Terkirim');
                    return $this->redirect($response, 'dashboardperusahaan-pelamar');
                }
              }
  	     }
         $this->flash('danger', 'Gagal Mengundang Interview.');
      }
      
      return $this->view->render(
          $response,
          'bkol/dashboard-perusahaan/pelamar/undang-interview.twig',
          array(
            'pelamar' => $pelamar
          )
      );
    }

    public function SudahInterview(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('companies.view')) {
            return $check;
        }
        $pelamar = new JobPostsApply;
        $pelamar = $pelamar->where('id', $request->getParam('apply'));
        $userId = $this->auth->check()->id;
        $pelamar = $pelamar->whereHas(
            'post',
            function ($query) use ($userId) {
                $query->where('user_id', '=', $userId);
            }
        );
        $pelamar = $pelamar->first();
        if ($pelamar) {
            $pelamar->status = 4;
            if ($pelamar->save()) {
                $this->flash('success', 'Pelamar Sudah Di Interview.');
                return $this->redirect($response, 'dashboardperusahaan-pelamar');
            }
        }
        $this->flash('danger', 'Error.');
        return $this->redirect($response, 'dashboardperusahaan-pelamar');
    }

    public function TerimaBekerja(Request $request, Response $response, $routeArgs, $args)
    {
      $routeArgs =  $request->getAttribute('route')->getArguments();
      $args =  $request->getAttribute('route')->getArguments();
      $pelamar = new JobPostsApply;
      $pelamar = $pelamar->where('id', $args['id']);
      $userId = $this->auth->check()->id;
      $pelamar = $pelamar->whereHas(
          'post',
          function ($query) use ($userId) {
              $query->where('user_id', '=', $userId);
          }
      );
      $pelamar = $pelamar->first();
      if ($request->isPost()) {
            if ($this->validator->isValid()) {
              if ($pelamar) {
                $r = $this->request->getParams();
                $pelamar->status = 5;
                $pencaker =  new BkolPencariKerja;
                $pencaker = $pencaker->where('user_id', $pelamar->user->datapencarikerja->user_id)->first();
                $pencaker->status_pekerjaan = 'Sudah Bekerja' ? 'Sudah Bekerja' : NULL;
                $pencaker->diterima_di = $pelamar->post->authorCompanies->companyname;
                $pencaker->domisili_perusahaan = $pelamar->post->authorCompanies->kabupatenkota->lokasi_nama;
                $pencaker->terhitung_mulai_tanggal = $r['terhitung_mulai_tanggal'];
                $pencaker->save();
                if ($pelamar->save()) {
                    $this->flash('success', 'Pelamar dengan Nama:  ' .$pelamar->user->datapencarikerja->nama_lengkap. ' Di Terima Bekerja');
                    return $this->redirect($response, 'dashboardperusahaan-pelamar');
                }
              }
  	     }
         $this->flash('danger', 'Error.');
      }
      return $this->view->render(
          $response,
          'bkol/dashboard-perusahaan/pelamar/terima-bekerja.twig',
          array(
            'pelamar' => $pelamar
          )
      );
    }

    public function BerhentiBekerja(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('companies.view')) {
            return $check;
        }
        $pelamar = new JobPostsApply;
        $pelamar = $pelamar->where('id', $request->getParam('apply'));
        $userId = $this->auth->check()->id;
        $pelamar = $pelamar->whereHas(
            'post',
            function ($query) use ($userId) {
                $query->where('user_id', '=', $userId);
            }
        );
        $pelamar = $pelamar->first();
        if ($pelamar) {
            $pelamar->status = 6;
            if ($pelamar->save()) {
                $this->flash('success', 'Pelamar dengan Nama:  ' .$pelamar->user->datapencarikerja->nama_lengkap. ' Berhenti Bekerja');
                return $this->redirect($response, 'dashboardperusahaan-pelamar');
            }
        }
        $this->flash('danger', 'Error.');
        return $this->redirect($response, 'dashboardperusahaan-pelamar');
    }

    public function HapusLamaran(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('companies.view')) {
            return $check;
        }
        $pelamar = new JobPostsApply;
        $pelamar = $pelamar->where('id', $request->getParam('apply'));
        $userId = $this->auth->check()->id;
        $pelamar = $pelamar->whereHas(
            'post',
            function ($query) use ($userId) {
                $query->where('user_id', '=', $userId);
            }
        );
        $pelamar = $pelamar->first();
        if ($pelamar) {
            if ($pelamar->delete()) {
                $this->flash('success', 'Pelamar dengan Nama:  ' .$pelamar->user->datapencarikerja->nama_lengkap. ' Berhasil Di Hapus');
                return $this->redirect($response, 'dashboardperusahaan-pelamar');
            }
        }
        $this->flash('danger', 'Error.');
        return $this->redirect($response, 'dashboardperusahaan-pelamar');
    }
}
