<?php


namespace App\Controller\Admin\Bkol;
use App\Controller\Controller;
use Psr\Container\ContainerInterface;
use Carbon\Carbon;
use App\Library\VideoParser as VP;
use App\Model\PerusahaanModel;
use App\Library\Perusahaan as B;
use App\Model\Users as U;
use App\Model\BkolPerusahaan as JU;
use App\Model\JobPosts;
use App\Model\ActivationModel;
use App\Model\JenisUsahaPost;
use App\Model\WaktuKerjaModel;
use App\Model\Daerah;
use App\Library\Utils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class BkolPerusahaan extends Controller
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $utils = new B($this->container);
        $this->utils = $utils;
    }

    public function index(Request $request, Response $response)
    {

        $perusahaan = JU::where('created_at', '<', Carbon::now())
            ->has('userperusahaan')
            ->orderBy('created_at')
            ->get();

        if (!$this->auth->check()->inRole('manager') && !$this->auth->check()->inRole('admin')) {
            $perusahaan = $perusahaan->where('user_id', $this->auth->check()->id);
        }
        return $this->view->render(
            $response,
            'dashboard/admin/bkol/bkolperusahaan/bkolperusahaan.twig',
            array(
                "perusahaan" => $perusahaan
            )
        );
    }
    // Add New
    public function add(Request $request, Response $response)
    {
        // if ($check = $this->sentinel->hasPerm('.create', 'dashboard', $this->config['-enabled'])) {
        //     return $check;
        // }

        if ($request->isPost()) {
            if ($this->utils->addPost()) {
                return $this->redirect($response, 'bkol-perusahaan');
            }
        }

        $daerahs = Daerah::where('lokasi_kabupatenkota', 0)
                            ->where('lokasi_kecamatan', 0)
                            ->where('lokasi_kelurahan', 0)
                            ->orderBy('lokasi_nama')
                            ->get();

        return $this->view->render(
            $response,
            'dashboard/admin/bkol/bkolperusahaan/add.twig',
             array(
                'form_state' => 'add'
            )
        );
    }

    public function edit(Request $request, Response $response, $bkolptId)
    {
        //die(var_dump($request->getAttribute('route')));
        // if ($check = $this->sentinel->hasPerm('blog.update', 'dashboard', $this->config['blog-enabled'])) {
        //     return $check;
        // }

        $pt = JU::where('id', $bkolptId)->first();
        $daerahs = Daerah::where('lokasi_kabupatenkota', 0)
        ->where('lokasi_kecamatan', 0)
        ->where('lokasi_kelurahan', 0)
        ->orderBy('lokasi_nama')
        ->get();

        if (!$pt) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'bkol-perusahaan');
        }

        if ($request->isPost()) {
            if ($this->utils->updatePost($pt->id)) {
                return $this->redirect($this->container->response, 'bkol-perusahaan');
            }
        }

        return $this->view->render(
            $response,
            'dashboard/admin/bkol/bkolperusahaan/edit.twig',
             ['post' => $pt, 'form_state' => 'edit']
        );
    }


    // Delete
    public function Delete(Request $request, Response $response)
    {


        $perusahaan = U::find($request->getParam('user_id'));

        if (!$perusahaan) {
            $this->flash('danger', 'Tidak ada User Perusahaan');
            return $this->redirect($response, 'bkol-perusahaan');
        }

        if ($perusahaan->delete()) {
            $this->flash('success', 'Data berhasil dihapus');
            return $this->redirect($response, 'bkol-perusahaan');
        }

        $this->flash('danger', 'There was a problem removing the User Perusahaan.');
        return $this->redirect($response, 'bkol-perusahaan');
    }

    // Detail
    public function DetailBkolPerusahaan(Request $request, Response $response, $bkolperusahaanId)
    {
        //die(var_dump($request->getAttribute('route')));
        // if ($check = $this->sentinel->hasPerm('blog.update', 'dashboard', $this->config['blog-enabled'])) {
        //     return $check;
        // }

        $perusahaan = JU::where('id', $bkolperusahaanId)->with('userperusahaan')->first();

        $jobs = JobPosts::where('status', 1)
            ->where('user_id', $bkolperusahaanId)
            ->where('publish_at', '<', Carbon::now())
            ->with('category', 'tags', 'authorCompanies')
            ->orderBy('publish_at', 'DESC');

        if (!$perusahaan) {
            $this->flash('danger', 'That post does not exist.');
            return $this->redirect($response, 'perusahaan');
        }

        if (!$this->auth->check()->inRole('manager')
            && !$this->auth->check()->inRole('admin')
            && $perusahaan->user_id != $this->auth->check()->id) {
            $this->flash('danger', 'You do not have permission to edit that post.');
            return $this->redirect($response, 'admin-blog');
        }

        $daerahs = Daerah::where('lokasi_kabupatenkota', 0)
                            ->where('lokasi_kecamatan', 0)
                            ->where('lokasi_kelurahan', 0)
                            ->orderBy('lokasi_nama')
                            ->get();

        return $this->view->render(
            $response,
            'dashboard/admin/bkol/bkolperusahaan/bkolperusahaan-detail.twig',
            [
                "bkolperusahaan" => $perusahaan,
                "daerahs" => $daerahs,
                "jobs" => $jobs->get()
            ]

        );
    }
    public function Aktifkan(Request $request, Response $response)
    {
        $user = ActivationModel::where('user_id', '=', $request->getParam('user_id'))->first();
        if ($user) {
            $user->completed = 1;

            if ($user->save()) {
                $mail = new PHPMailer;
                $mail->CharSet = 'UTF-8';
                $mail->setFrom($this->config['from-email']);
                $mail->addAddress($user->user->email);  
                $mail->isHTML(true);                                  		// Set email format to HTML
                $mail->Subject = 'Selamat Akun Anda Sunda Di Aktifkan';
                // Use email template
                $mail->Body = $this->view->fetch( 'bkol/email/aktifkan.twig',[ 
                    'title' => "Akun Anda Berhasilkan Di Aktifkan",
                    'judul_email' => "Akun Aktif",
                    'nama_lengkap' => $user->user->username,
                    'donotreply' => true, 	// show do not reply to this email
                ]);
                if ( $mail->send()) {
                    $this->flash('success', 'Akun Dengan Username ' .$user->user->username. ' Sudah Di Aktipkan');
                    return $this->redirect($response, 'bkol-perusahaan');
                } else {
                    return $this->redirect($response, 'bkol-perusahaan');
                }
                
            }
        }
        return $this->redirect($response, 'bkol-perusahaan');
    }
    public function NonAktifkan(Request $request, Response $response)
    {
        $user = ActivationModel::where('user_id', '=', $request->getParam('user_id'))->first();
        if ($user) {
            $user->completed = 0;
            if ($user->save()) {
                $mail = new PHPMailer;
                $mail->CharSet = 'UTF-8';
                $mail->setFrom($this->config['from-email']);
                $mail->addAddress($user->user->email);  
                $mail->isHTML(true);                                  		// Set email format to HTML
                $mail->Subject = 'Akun Anda di Non Aktifkan';
                // Use email template
                $mail->Body = $this->view->fetch( 'bkol/email/nonaktif.twig',[ 
                    'title' => "Akun Anda di Non Aktifkan",
                    'judul_email' => "Akun Non Aktif",
                    'nama_lengkap' => $user->user->username,
                    'donotreply' => true, 	// show do not reply to this email
                ]);
                if ($mail->send()) {
                    $this->flash('success', 'Akun Dengan Username ' .$user->user->username. ' Sudah Di Non Aktipkan');
                return $this->redirect($response, 'bkol-perusahaan');
                } else {
                    return $this->redirect($response, 'bkol-perusahaan');
                }
                $this->flash('success', 'Akun Dengan Username ' .$user->user->username. ' Sudah Di Non Aktipkan');
                return $this->redirect($response, 'bkol-perusahaan');
            }
        }
        return $this->redirect($response, 'bkol-perusahaan');
    }
}
