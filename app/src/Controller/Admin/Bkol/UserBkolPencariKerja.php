<?php

namespace App\Controller\Admin\Bkol;
use App\Controller\Controller;
use App\Model\Roles;
use App\Library\Utils;
use App\Model\Users as U;
use App\Model\BkolPencariKerja;
use App\Model\JenisPendidikanModel;
use App\Model\Daerah;
use App\Model\NegaraModel;
use App\Model\MinatModel;
use App\Model\MinatUserModel;
use App\Model\GajiModel;
use App\Model\BakatModel;
use App\Model\BakatUserModel;
use App\Model\ActivationModel;
use App\Model\GolonganJabatanModel;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Library\Email as E;


/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class UserBkolPencariKerja extends Controller
{

  protected $parsedMinat;
  protected $parsedBakat;


  public function __construct(ContainerInterface $container)
  {
      parent::__construct($container);

      $this->parsedMinat = null;
      $this->parsedBakat = null;
  }
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function users(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('user.view')) {
            return $check;
        }
        $users = U::with('roles','activation')
            ->WhereHas('roles', function($q){$q->whereIn('name', ['jobseeker']);})
            ->get();

        return $this->view->render(
            $response,
            'dashboard/admin/bkol/bkolpencarikerja/bkolpencarikerja.twig',
            [
                "users" => $users
            ]
        );
    }

    public function usersAdd(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('user.create')) {
            return $check;
        }

        if ($request->isPost()) {
            $this->validateUserData();

            if ($this->validator->isValid()) {
                
                $role = $this->auth->findRoleByName('Jobseeker');
                $userDetails = [
                    'fullname' => $request->getParam('nama_lengkap'),
                    'email' => $request->getParam('email'),
                    'username' => $request->getParam('username'),
                    'password' => $request->getParam('password'),
                    'permissions' => [
                        'user.delete' => 0
                    ]
                ];

                if ($this->config['activation']) {

                    $user = $this->auth->register($userDetails);
                    $role->users()->attach($user);

                    $activations = $this->auth->getActivationRepository();

                    $activations = $activations->create($user);
                    $code = $activations->code;

                    $confirmUrl = "https://" . $this->config['domain-bkol'] . "/activate?code=" .
                        $code . "&email=" . $user->email;

                    $bkolpencarikerja = new BkolPencariKerja;
                    $bkolpencarikerja->user_id =  $user->id;
                    $bkolpencarikerja->id =  $user->id;
                    $bkolpencarikerja->nama_lengkap =  $request->getParam('nama_lengkap');
                    $bkolpencarikerja->alamat_lengkap =  $request->getParam('alamat_lengkap');
                    $bkolpencarikerja->save();

                    $sendEmail = new E($this->container);
                    $sendEmail = $sendEmail->sendTemplate(
                        array($user->id),
                        'activation',
                        array('confirm_url' => $confirmUrl)
                    );

                    if ($sendEmail['status'] == "error") {
                        $this->flash(
                            'danger',
                            'Terjadi kesalahan saat mengirim email aktivasi Anda. Silakan hubungi contact support.'
                        );
                        return $this->redirect($response, 'bkol-pencarikerja');
                    }

                    $this->flash(
                        'success',
                        'Petunjuk aktivasi akun telah dikirim ke: ' .
                            $request->getParam('email')
                    );
                    return $this->redirect($response, 'bkol-pencarikerja');
                }

                $this->addUser();
                $this->flash('success', $request->getParam('username').' has been added successfully.');
                return $this->redirect($response, 'bkol-pencarikerja');
            }
        }
        
        return $this->view->render(
            $response,
            'dashboard/admin/bkol/bkolpencarikerja/add.twig',[ 'form_state' => 'add']
        );
    }

    private function addUser()
    {
        $user = $this->auth->registerAndActivate([
            'email' => $this->request->getParam('email'),
            'username' => $this->request->getParam('username'),
            'password' => $this->request->getParam('password'),
            'permissions' => [
                'user.delete' => 0
            ]
        ]);
        $bkolpencarikerja = new BkolPencariKerja;
        $bkolpencarikerja->user_id =  $user->id;
        $bkolpencarikerja->id =  $user->id;
        $bkolpencarikerja->nama_lengkap =  $this->request->getParam('nama_lengkap');
        $bkolpencarikerja->alamat_lengkap =  $this->request->getParam('alamat_lengkap');
        $bkolpencarikerja->save();
        $role = $this->auth->findRoleByName('Jobseeker');
        $role->users()->attach($user);
    }

    private function validateUserData($user = null)
    {
        // Validate Form Data
        $validateData = array(

            'email' => array(
                'rules' => V::noWhitespace()->email(),
                'messages' => array(
                    'email' => 'Enter a valid email address.',
                    'noWhitespace' => 'Must not contain any spaces.'
                )
            ),
            'username' => array(
                'rules' => V::noWhitespace()->alnum(),
                'messages' => array(
                    'slug' => 'Must be alpha numeric with no spaces.',
                    'noWhitespace' => 'Must not contain any spaces.'
                )
            ),
            'nama_lengkap' => array(
                'rules' => V::length(6, 255),
                'messages' => array(
                    'length' => 'Must be between 6 and 255 characters.'
                )
            ),
            'alamat_lengkap' => array(
                'rules' => V::length(6, 255),
                'messages' => array(
                    'length' => 'Must be between 6 and 255 characters.'
                )
            )
        );

        if (!$user) {
            $validateData['password'] = array(
                'rules' => V::noWhitespace()->length(6, 25),
                'messages' => array(
                    'noWhitespace' => 'Must not contain spaces.',
                    'length' => 'Must be between 6 and 25 characters.'
                )
            );

            $validateData['password_confirm'] = array(
                'rules' => V::equals($this->request->getParam('password')),
                'messages' => array(
                    'equals' => 'Passwords do not match.'
                )
            );

            // Validate Username
            if ($this->auth->findByCredentials(['login' => $this->request->getParam('username')])) {
                $this->validator->addError('username', 'User already exists with this username.');
            }

            // Validate Email
            if ($this->auth->findByCredentials(['login' => $this->request->getParam('email')])) {
                $this->validator->addError('email', 'User already exists with this email.');
            }
        }
        $this->validator->validate($this->request, $validateData);

        if ($user) {
            // Validate Username
            if ($this->auth->findByCredentials(['login' => $this->request->getParam('username')]) &&
                $user->username != $this->request->getParam('username')) {
                $this->validator->addError('username', 'User already exists with this username.');
            }

            // Validate Email
            if ($this->auth->findByCredentials(['login' => $this->request->getParam('email')]) &&
                $user->email != $this->request->getParam('email')) {
                $this->validator->addError('email', 'User already exists with this email.');
            }
        }
    }

    private function editUser($user = null)
    {
        if (!$user) {
            return false;
        }
        $user->email = $this->request->getParam('email');
        $user->username = $this->request->getParam('username');
        $user->fullname = $this->request->getParam('nama_lengkap');
        if ($user->save()) {
            $bkolpencarikerja = new BkolPencariKerja;
            $bkolpencarikerja = $bkolpencarikerja->where('user_id',$user->id)->first();
            $bkolpencarikerja->nama_lengkap =  $this->request->getParam('nama_lengkap');
            $bkolpencarikerja->alamat_lengkap =  $this->request->getParam('alamat_lengkap');
            $bkolpencarikerja->save();
        }
        return true;
    }

    public function usersEdit(Request $request, Response $response, $userid)
    {
        if ($check = $this->sentinel->hasPerm('user.update')) {
            return $check;
        }

        $user = U::with('datapencarikerja')->find($userid);
        $bkolpencarikerja = BkolPencariKerja::find($userid);

        if (!$user) {
            $this->flash('danger', 'Sorry, that user was not found.');
            return $response->withRedirect($this->router->pathFor('bkol-pencarikerja'));
        }

        if ($request->isPost()) {
            if ($this->editUser($user)) {
                $this->flash('success', $user->username.' has been updated successfully.');
                return $this->redirect($response, 'bkol-pencarikerja');
            }


            $this->flash('danger', 'There was an error updating that user.');
            return $this->redirect($response, 'bkol-pencarikerja');
        }

        return $this->view->render($response,
         'dashboard/admin/bkol/bkolpencarikerja/edit.twig',
         [
                'form_state' => 'edit',
                'user' => $user,
                'profile' => $bkolpencarikerja
        ]);
    }

    


    private function editUserPeserta($user = null)
    {
      $this->processMinat();
      $this->processBakat();
      if (!$user) {
          return false;
      }
      if ($user->save()) {
          $bkolpencarikerja = new BkolPencariKerja;
          $bkolpencarikerja = $bkolpencarikerja->find($user->id);
          if ($user->save()) {
            //Delete Existing Post Tags
            MinatUserModel::where('user_id', $bkolpencarikerja->id)->delete();
            foreach ($this->parsedMinat as $minat) {
                $addTag = new MinatUserModel;
                $addTag->user_id = $bkolpencarikerja->id;
                $addTag->minat_id = $minat;
                $addTag->save();
            }

            BakatUserModel::where('user_id', $bkolpencarikerja->id)->delete();
            foreach ($this->parsedBakat as $bakat) {
                $addTag = new BakatUserModel;
                $addTag->user_id = $bkolpencarikerja->id;
                $addTag->bakat_id = $bakat;
                $addTag->save();
            }
          }
      }
      return true;
    }

    public function usersEditPeserta(Request $request, Response $response, $userid)
    {

        if ($check = $this->sentinel->hasPerm('user.update')) {
            return $check;
        }

        $user = U::with('datapencarikerja')->find($userid);
        $bkolpencarikerja = BkolPencariKerja::where('user_id',$userid)->first();

        if (!$user) {
            $this->flash('danger', 'Sorry, that user was not found.');
            return $response->withRedirect($this->router->pathFor('admin-pelatihan'));
        }

        if ($request->isPost()) {
            // $this->validateUserData($user);
            if ($this->validator->isValid()) {
                if ($this->editUserPeserta($user)) {
                    $this->flash('success', $user->username.' has been updated successfully.');
                    return $this->redirect($response, 'admin-pelatihan');
                }
            }
            $this->flash('danger', 'There was an error updating that user.');
            return $this->redirect($response, 'admin-pelatihan');
        }


        $currentMinat= $bkolpencarikerja->minat->pluck('id');
        $currentBakat= $bkolpencarikerja->bakat->pluck('id');
        return $this->view->render($response,
         'dashboard/admin/bkol/bkolpencarikerja/peserta-edit.twig',
         [
             'user' => $user,
             "currentMinat" => $currentMinat,
             "currentBakat" => $currentBakat,
             "minat" => MinatModel::get(),
             "bakat" => BakatModel::get()
        ]);
    }

    private function processMinat()
    {
        foreach ($this->container->request->getParam('minat') as $value) {
            // Check if Already Numeric
            if (is_numeric($value)) {
                $check = MinatModel::find($value);
                if ($check) {
                    $this->parsedMinat[] = $value;
                }
                continue;
            }

            // Check if slug already exists
            $slug = Utils::slugify($value);
            $slugCheck = MinatModel::where('slug', '=', $slug)->first();
            if ($slugCheck) {
                $this->parsedMinat[] = $slugCheck->id;
                continue;
            }

            // Add New Tag To Database
            $newMinat = new MinatModel;
            $newMinat->name = $value;
            $newMinat->slug = $slug;
            if ($newMinat->save()) {
                if ($newMinat->id) {
                    $this->parsedMinat[] = $newMinat->id;
                }
            }
        }
    }

    private function processBakat()
    {
        foreach ($this->container->request->getParam('bakat') as $value) {
            // Check if Already Numeric
            if (is_numeric($value)) {
                $check = BakatModel::find($value);
                if ($check) {
                    $this->parsedBakat[] = $value;
                }
                continue;
            }
            // Check if slug already exists
            $slug = Utils::slugify($value);
            $slugCheck = BakatModel::where('slug', '=', $slug)->first();
            if ($slugCheck) {
                $this->parsedBakat[] = $slugCheck->id;
                continue;
            }
            // Add New Tag To Database
            $newBakat = new BakatModel;
            $newBakat->name = $value;
            $newBakat->slug = $slug;
            if ($newBakat->save()) {
                if ($newBakat->id) {
                    $this->parsedBakat[] = $newBakat->id;
                }
            }
        }
    }

    public function usersDelete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('user.delete')) {
            return $check;
        }

        $user = $this->auth->findById($request->getParam('user_id'));

        if ($user->delete()) {
            $this->flash('success', 'User has been deleted successfully.');
            return $this->redirect($response, 'bkol-pencarikerja');
        }

        $this->flash('danger'.'There was an error deleting the user.');
        return $this->redirect($response, 'bkol-pencarikerja');
    }
    public function Aktipkan(Request $request, Response $response)
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
                    return $this->redirect($response, 'bkol-pencarikerja');
                } else {
                    return $this->redirect($response, 'bkol-pencarikerja');
                }
                
            }
        }
        return $this->redirect($response, 'bkol-pencarikerja');
    }
    public function NonAktipkan(Request $request, Response $response)
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
                return $this->redirect($response, 'bkol-pencarikerja');
                } else {
                    return $this->redirect($response, 'bkol-pencarikerja');
                }
                $this->flash('success', 'Akun Dengan Username ' .$user->user->username. ' Sudah Di Non Aktipkan');
                return $this->redirect($response, 'bkol-pencarikerja');
            }
        }
        return $this->redirect($response, 'bkol-pencarikerja');
    }

}
