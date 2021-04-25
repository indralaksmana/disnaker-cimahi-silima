<?php

namespace App\Controller\Admin;

use App\Library\Email as E;
use App\Library\Sentinel as S;
use App\Model\Emails;
use App\Model\EmailsTemplates;
use App\Model\Users;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Email extends Controller
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $email = new E($this->container);
        $this->email = $email;
    }

    public function dataTables(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('email.view')) {
            return $check;
        }

        $totalData = Emails::count();

        $totalFiltered = $totalData;

        $limit = $request->getParam('length');
        $start = $request->getParam('start');
        $order = $request->getParam('columns')[$request->getParam('order')[0]['column']]['data'];
        $dir = $request->getParam('order')[0]['dir'];

        $emails = Emails::select('id', 'send_to', 'subject', 'created_at')
            ->skip($start)
            ->take($limit)
            ->orderBy($order, $dir);

        if (!empty($request->getParam('search')['value'])) {
            $search = $request->getParam('search')['value'];

            $emails =  $emails->where('send_to', 'LIKE', "%{$search}%")
                    ->orWhere('subject', 'LIKE', "%{$search}%");

            $totalFiltered = Emails::where('send_to', 'LIKE', "%{$search}%")
                    ->orWhere('subject', 'LIKE', "%{$search}%")
                    ->count();
        }

        $jsonData = array(
            "draw"            => intval($request->getParam('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $emails->get()->toArray()
            );

        return $response->withJSON(
            $jsonData,
            200
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function email(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('email.view', 'dashboard')) {
            return $check;
        }

        $emails = Emails::take(200)->get();

        return $this->view->render($response, 'dashboard/admin/emails/emails.twig', array("emails" => $emails));
    }

    public function emailDetails(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('email.details', 'dashboard')) {
            return $check;
        }

        $routeArgs =  $request->getAttribute('route')->getArguments();

        $email = Emails::find($routeArgs['email']);

        if (!$email) {
            $this->flash('danger', 'There was a problem finding that email in the database.');
            return $this->redirect($response, 'admin-email');
        }

        $user = Users::where('email', $email->send_to)->first();

        return $this->view->render($response, 'dashboard/admin/emails/emails-details.twig', array("email" => $email, "user" => $user));
    }

    public function testEmail(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('email.test', 'dashboard')) {
            return $check;
        }

        $user = $this->auth->check();

        $email = new E($this->container);
        $email = $email->sendEmail(
            array($user->id),
            $request->getParam('subject'),
            $request->getParam('html'),
            $request->getParam('plain_text')
        );

        return $response->write(json_encode($email), 201);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function templates(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('email.templates', 'dashboard')) {
            return $check;
        }

        $templates = EmailsTemplates::take(200)->get();

        return $this->view->render($response, 'dashboard/admin/emails/emails-templates.twig', array("templates" => $templates));
    }

    public function templatesAdd(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('email.templates', 'dashboard')) {
            return $check;
        }

        $placeholders = $this->email->getPlaceholders();

        if ($request->isPost()) {
            if ($this->email->addTemplate()) {
                $this->flash('success', 'Template has been successfully added.');
                return $this->redirect($response, 'admin-email-template');
            }
            $this->flashNow('danger', 'There was an error adding your template.');
        }

        return $this->view->render($response, 'dashboard/admin/emails/emails-templates-add.twig', array("placeholders" => $placeholders));
    }

    public function templatesEdit(Request $request, Response $response, $templateId)
    {
        if ($check = $this->sentinel->hasPerm('email.templates', 'dashboard')) {
            return $check;
        }

        $placeholders = $this->email->getPlaceholders();

        $template = EmailsTemplates::find($templateId);

        if (!$template) {
            $this->flash('danger', 'Template not found.');
            return $this->redirect($response, 'admin-email-template');
        }

        if ($request->isPost()) {
            if ($this->email->updateTemplate($templateId)) {
                $this->flash('success', 'Template has been successfully updated.');
                return $this->redirect($response, 'admin-email-template');
            }
            $this->flashNow('danger', 'There was an error updating your template.');
        }

        return $this->view->render(
            $response,
            'dashboard/admin/emails/emails-templates-edit.twig',
            array(
                "template" => $template,
                "placeholders" => $placeholders
            )
        );
    }

    public function emailNew(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('email.create', 'dashboard')) {
            return $check;
        }

        $placeholders = $this->email->getPlaceholders();

        $requestParams = $request->getParams();

        $users = Users::orderBy('first_name')
            ->orderBy('last_name')
            ->select('first_name', 'last_name', 'id', 'email')
            ->get();

        if ($request->isPost()) {
            // Validate Text Fields
            $this->validator->validate(
                $request,
                array(
                    'subject' => array(
                        'rules' => V::notEmpty(),
                        'messages' => array(
                            'notEmpty' => 'Cannot be empty.'
                        )
                    ),
                    'html' => array(
                        'rules' => V::notEmpty(),
                        'messages' => array(
                            'notEmpty' => 'Cannot be empty.'
                        )
                    ),
                    'plain_text' => array(
                        'rules' => V::notEmpty(),
                        'messages' => array(
                            'notEmpty' => 'Cannot be empty.'
                        )
                    )
                )
            );

            // Check user
            $userCheck = Users::find($requestParams['send_to']);
            if (!$userCheck) {
                $this->validator->addError('slug', 'User does not exist.');
            }

            // Check Plain Text for HTML
            if (strip_tags($requestParams['plain_text']) != $requestParams['plain_text']) {
                $this->validator->addError('plain_text', 'Plain Text cannot contain HTML.');
            }

            if ($this->validator->isValid()) {
                $email = new E($this->container);
                $email = $email->sendEmail(
                    array($userCheck->id),
                    $request->getParam('subject'),
                    $request->getParam('html'),
                    $request->getParam('plain_text')
                );

                if ($email['results']['success']) {
                    $this->flash('success', 'Email has been successfully sent.');
                    return $this->redirect($response, 'admin-email');
                }

                $this->flashNow('danger', 'There was a problem sending your email.');
            }
        }

        return $this->view->render(
            $response,
            'dashboard/admin/emails/emails-new.twig',
            array(
                "placeholders" => $placeholders,
                "users" => $users
            )
        );
    }
}
