<?php

namespace App\Controller\Admin;

use App\Model\Roles;
use App\Model\Users as U;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Users extends Controller
{
    public function dataTables(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('user.view')) {
            return $check;
        }

        $totalData = U::count();

        $totalFiltered = $totalData;

        $limit = $request->getParam('length');
        $start = $request->getParam('start');
        $order = $request->getParam('columns')[$request->getParam('order')[0]['column']]['data'];
        $dir = $request->getParam('order')[0]['dir'];

        $users = U::select('id', 'first_name', 'last_name', 'email', 'username')
            ->with('roles', 'oauth2', 'oauth2.provider')
            ->skip($start)
            ->take($limit)
            ->orderBy($order, $dir);

        if (!empty($request->getParam('search')['value'])) {
            $search = $request->getParam('search')['value'];

            $users =  $users->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('username', 'LIKE', "%{$search}%")
                    ->orWhereHas(
                        'oauth2.provider',
                        function ($query) use ($search) {
                            $query->where('name', 'LIKE', "%{$search}%");
                        }
                    )
                    ->orWhereHas(
                        'roles',
                        function ($query) use ($search) {
                            $query->where('name', 'LIKE', "%{$search}%");
                        }
                    );

            $totalFiltered = U::where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('username', 'LIKE', "%{$search}%")
                    ->orWhereHas(
                        'roles',
                        function ($query) use ($search) {
                            $query->where('name', 'LIKE', "%{$search}%");
                        }
                    )
                    ->orWhereHas(
                        'roles',
                        function ($query) use ($search) {
                            $query->where('name', 'LIKE', "%{$search}%");
                        }
                    )
                    ->count();
        }

        $jsonData = array(
            "draw"            => intval($request->getParam('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $users->get()->toArray()
            );

        return $response->withJSON(
            $jsonData,
            200
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function users(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('user.view')) {
            return $check;
        }

        return $this->view->render(
            $response,
            'dashboard/users.twig',
            ["roles" => Roles::get()]
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
                $this->addUser();

                $this->flash('success', $request->getParam('username').' has been added successfully.');
                return $this->redirect($response, 'admin-users');
            }
        }

        return $this->view->render($response, 'dashboard/users-add.twig', ['roles' => Roles::get()]);
    }

    private function addUser()
    {

        // Get Permissions Array
        $permissionsArray = $this->getPermissionsArray();

        // Get Roles Array
        $rolesArray = $this->getRolesArray();

        $user = $this->auth->registerAndActivate([
            'first_name' => $this->request->getParam('first_name'),
            'last_name' => $this->request->getParam('last_name'),
            'email' => $this->request->getParam('email'),
            'username' => $this->request->getParam('username'),
            'password' => $this->request->getParam('password'),
            'permissions' => [
                'user.delete' => 0
            ]
        ]);

        $userPerms = $user;
        $userPerms->permissions = $permissionsArray;
        $userPerms->save();

        foreach (Roles::get() as $rolevalue) {
            if (!in_array($rolevalue['slug'], $rolesArray)) {
                if ($rolevalue['slug'] == 'admin' && $this->request->getParam('user_id') == 1) {
                    continue;
                }

                $role = $this->auth->findRoleBySlug($rolevalue['slug']);
                $role->users()->detach($user);
            }
        }

        foreach ($rolesArray as $ravalue) {
            if ($user->inRole($ravalue)) {
                continue;
            }

            $role = $this->auth->findRoleBySlug($ravalue);
            $role->users()->attach($user);
        }
    }

    private function getPermissionsArray()
    {
        $permissionsArray = array();
        if ($this->request->getParam('perm_name')) {
            foreach ($this->request->getParam('perm_name') as $pkey => $pvalue) {
                $val = false;
                if ($this->request->getParam('perm_value')[$pkey] == "true") {
                    $val = true;
                }
                $permissionsArray[$pvalue] = $val;
            }
        }
        return $permissionsArray;
    }

    private function getRolesArray()
    {
        $rolesArray = array();
        if ($this->request->getParam('roles')) {
            foreach ($this->request->getParam('roles') as $rvalue) {
                if (!$this->auth->findRoleBySlug($rvalue)) {
                    $this->validator->addError('roles', 'Role does not exist.');
                }
                $rolesArray[] = $rvalue;
            }
        }
        return $rolesArray;
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

        // Get Permissions Array
        $permissionsArray = $this->getPermissionsArray();

        // Get Roles Array
        $rolesArray = $this->getRolesArray();

        $user->first_name = $this->request->getParam('first_name');
        $user->last_name = $this->request->getParam('last_name');
        $user->email = $this->request->getParam('email');
        $user->username = $this->request->getParam('username');
        $user->save();

        $user->permissions = $permissionsArray;
        $user->save();

        foreach (Roles::get() as $rolevalue) {
            echo $rolevalue['slug'] . "<br>";

            if (!in_array($rolevalue['slug'], $rolesArray)) {
                if ($rolevalue['slug'] == 'admin' && $this->request->getParam('user_id') == 1) {
                    continue;
                }

                $role = $this->auth->findRoleBySlug($rolevalue['slug']);
                $role->users()->detach($user);
            }
        }

        foreach ($rolesArray as $ravalue) {
            if ($user->inRole($ravalue)) {
                continue;
            }

            $role = $this->auth->findRoleBySlug($ravalue);
            $role->users()->attach($user);
        }

        return true;
    }

    public function usersEdit(Request $request, Response $response, $userid)
    {
        if ($check = $this->sentinel->hasPerm('user.update')) {
            return $check;
        }

        $user = U::find($userid);

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

        return $this->view->render($response, 'dashboard/users-edit.twig', ['user' => $user, 'roles' => Roles::get()]);
    }

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
