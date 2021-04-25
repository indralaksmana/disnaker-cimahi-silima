<?php

namespace App\Controller\Admin\UsersManagement;
use App\Controller\Controller;
use App\Model\Roles;
use App\Model\LpkModel;
use App\Model\Users as U;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class UsersLPK extends Controller
{

    public function usersAdd(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('user.create')) {
            return $check;
        }
        $lpk = LpkModel::get();
        if ($request->isPost()) {
            $this->validateUserData();
            if ($this->validator->isValid()) {
                $this->addUser();
                $this->flash('success', $request->getParam('username').' has been added successfully.');
                return $this->redirect($response, 'admin-users');
            }
        }
        return $this->view->render(
          $response,
         'dashboard/admin/usersmanagement/users/users-add-lpk.twig',
           ["lpk" => $lpk]
         );
    }

    public function usersIndex(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('user.create')) {
            return $check;
        }
        $lpk = LpkModel::get();
        return $this->view->render(
          $response,
         'dashboard/admin/usersmanagement/users/users-list-page.twig',
           ["lpk" => $lpk, 'type' => 'lpk']
         );
    }

    private function addUser()
    {

        $user = $this->auth->registerAndActivate([
            'first_name' => $this->request->getParam('first_name'),
            'last_name' => $this->request->getParam('last_name'),
            'email' => $this->request->getParam('email'),
            'username' => $this->request->getParam('username'),
            'password' => $this->request->getParam('password'),
            'lpk_id' => $this->request->getParam('lpk_id'),
            'permissions' => [
                'user.delete' => 0
            ]
        ]);

        $role = $this->auth->findRoleBySlug('lpk');
        $role->users()->attach($user);
    }

    private function validateUserData($user = null)
    {
        // Validate Form Data
        $validateData = array(
            'first_name' => array(
                'rules' => V::length(2, 25),
                'messages' => array(
                    'length' => 'Must be between 2 and 25 characters.'
                )
            ),
            'last_name' => array(
                'rules' => V::length(2, 25),
                'messages' => array(
                    'length' => 'Must be between 2 and 25 characters.'
                )
            ),
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
        $user->first_name = $this->request->getParam('first_name');
        $user->last_name = $this->request->getParam('last_name');
        $user->email = $this->request->getParam('email');
        $user->username = $this->request->getParam('username');
        $user->lpk_id = $this->request->getParam('lpk_id');
        $user->save();

        $role = $this->auth->findRoleBySlug('lpk');
        $role->users()->detach($user);

        $role = $this->auth->findRoleBySlug('lpk');
        $role->users()->attach($user);

        return true;
    }


    // Untuk Edit USer
    public function usersEdit(Request $request, Response $response, $userid)
    {
        if ($check = $this->sentinel->hasPerm('user.update')) {
            return $check;
        }

        $user = U::find($userid);
        $lpk = LpkModel::get();
        if (!$user) {
            $this->flash('danger', 'Sorry, that user was not found.');
            return $response->withRedirect($this->router->pathFor('admin-users'));
        }

        if ($request->isPost()) {
            $this->validateUserData($user);
            if ($this->validator->isValid()) {
                if ($this->editUser($user)) {
                    $this->flash('success', $user->username.' has been updated successfully.');
                    return $this->redirect($response, 'admin-users');
                }
            }

            $this->flash('danger', 'There was an error updating that user.');
            return $this->redirect($response, 'admin-users');
        }
        return $this->view->render(
          $response,
          'dashboard/admin/usersmanagement/users/users-edit-lpk.twig',
          [
            'user' => $user,
            'lpk' => $lpk
          ]
          );
    }
    // Untuk Menghapus Users
    public function usersDelete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('user.delete')) {
            return $check;
        }
        $user = $this->auth->findById($request->getParam('user_id'));
        if ($user->delete()) {
            $this->flash('success', 'User has been deleted successfully.');
            return $this->redirect($response, 'admin-users');
        }
        $this->flash('danger'.'There was an error deleting the user.');
        return $this->redirect($response, 'admin-users');
    }
}
