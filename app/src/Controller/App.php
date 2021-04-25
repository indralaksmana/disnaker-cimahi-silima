<?php

namespace App\Controller;

use App\Library\Email as E;
use App\Library\FileResponse;
use App\Library\Recaptcha;
use App\Model\ContactRequests;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use App\Model\Users as U;
use Carbon\Carbon;
use App\Model\JobCategories;
use App\Model\JobPosts;
use App\Model\JobPostsComments;

use App\Model\JobTags;
use App\Library\Paginator;
/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class App extends Controller
{
    public function asset(Request $request, Response $response)
    {
        $assetPath = str_replace("\0", "", $request->getParam('path'));
        $assetPath = __DIR__ . "/../../views/" . str_replace("../", "", $assetPath);

        if (!is_file($assetPath)) {
            throw new NotFoundException($request, $response);
        }

        return FileResponse::getResponse($response, $assetPath);
    }

    public function contact(Request $request, Response $response)
    {
        if ($request->isPost()) {
            // Validate Form Data
            $validateData = array(
                'name' => array(
                    'rules' => V::length(2, 64)->alnum('\''),
                    'messages' => array(
                        'length' => 'Must be between 2 and 64 characters.',
                        'alnum' => 'Alphanumeric and can contain \''
                        )
                ),
                'email' => array(
                    'rules' => V::email(),
                    'messages' => array(
                        'email' => 'Enter a valid email.',
                        )
                ),
                'phone' => array(
                    'rules' => V::phone(),
                    'messages' => array(
                        'phone' => 'Enter a valid phone number.'
                        )
                ),
                'comment' => array(
                    'rules' => V::alnum('\'!@#$%^&:",.?/'),
                    'messages' => array(
                        'alnum' => 'Text and punctuation only.',
                        )
                )
            );
            $this->validator->validate($request, $validateData);

            // Validate Recaptcha
            $recaptcha = new Recaptcha($this->container);
            $recaptcha = $recaptcha->validate($request->getParam('g-recaptcha-response'));
            if (!$recaptcha) {
                $this->validator->addError('recaptcha', 'Recaptcha was invalid.');
            }

            if ($this->validator->isValid()) {
                $add = new ContactRequests;
                $add->name = $request->getParam("name");
                $add->email = $request->getParam("email");
                $add->phone = $request->getParam("phone");
                $add->comment = $request->getParam("comment");

                if ($add->save()) {
                    if ($this->config['contact-send-email']) {
                        $sendEmail = new E($this->container);
                        $sendEmail = $sendEmail->sendTemplate(
                            array(
                                $request->getParam("email")
                            ),
                            'contact-confirmation',
                            array(
                                'name' => $request->getParam('name'),
                                'phone' => $request->getParam('phone'),
                                'comment' => $request->getParam('comment')
                            )
                        );
                    }

                    $this->flash('success', 'Your contact request has been submitted successfully.');
                    return $this->redirect($response, 'contact');
                }
            }
            $this->flash(
                'danger',
                'An unknown error occured.  Please try again or email us at: ' .
                $this->config['contact-email']
            );
            return $this->redirect($response, 'contact');
        }

        return $this->view->render($response, 'disnaker/contact.twig', array("requestParams" => $request->getParams()));
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function csrf(Request $request, Response $response)
    {
        $csrf = array(
            "name_key" => $this->csrf->getTokenNameKey(),
            "name" => $this->csrf->getTokenName(),
            "value_key" => $this->csrf->getTokenValueKey(),
            "value" => $this->csrf->getTokenValue());

        echo json_encode($csrf);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function home(Request $request, Response $response)
    {
      // Get Page Number
      $page = 1;
      $routeArgs =  $request->getAttribute('route')->getArguments();
      if (isset($routeArgs['page']) && is_numeric($routeArgs['page'])) {
          $page = $routeArgs['page'];
      }
      if ($this->auth->check()) {
        $user_id = $this->auth->check()->id;
      } else {
        $user_id = null;
      }
      $jobs = JobPosts::where('status', 1)
      ->where('publish_at', '<', Carbon::now())
      ->where('tanggal_berakhir', '>', Carbon::now())
      ->orderBy('id', 'DESC')->with(['pelamar' => function ($pelamar) use ($user_id) {
          return $pelamar->whereHas('user', function ($user) use ($user_id) {
              $user->where('user_id', $user_id);
          })->get();
      }]);
      return $this->view->render(
          $response,
          'bkol/home.twig',
          array(
              "jobs" => $jobs->get(),
              "requestParams" => $request->getParams()
          )
      );
    }
    public function tentang(Request $request, Response $response)
    {
      return $this->view->render(
          $response,
          'bkol/page/tentang-bkol.twig'
      );
    }
    public function mobile(Request $request, Response $response)
    {
      return $this->view->render(
          $response,
          'mobile/app.twig'
      );
    }

    public function offline(Request $request, Response $response)
    {
      return $this->view->render(
          $response,
          'bkol/page/offline.twig'
      );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function maintenance(Request $request, Response $response)
    {
        return $this->view->render($response, 'disnaker/maintenance.twig');
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */


    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */


    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */

}
