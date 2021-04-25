<?php

namespace App\Controller\Admin;

use App\Model\RoleUsers;
use App\Model\RolesPermissionsMenus;
use App\Model\RolesPermissions;
use App\Model\Roles as R;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Roles extends Controller
{
    // LIST ROLES
    public function roles(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('user.view')) {
            return $check;
        }

        return $this->view->render(
            $response,
            'dashboard/admin/usersmanagement/roles/roles.twig',
            [
              "roles" => R::get()

            ]
        );
    }
    // TAMBAH ROLES
    public function rolesAdd(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('role.create')) {
            return $check;
        }

        if ($request->isPost()) {
            $this->validator->validate($request, [
                'role_name' => V::length(2, 25)->alpha('\''),
                'role_slug' => V::slug()
            ]);

            if ($this->validator->isValid()) {
                $role = $this->auth->getRoleRepository()->createModel()->create([
                    'name' => $request->getParam('role_name'),
                    'slug' => $request->getParam('role_slug'),
                    'permissions' => []
                ]);

                if ($role) {
                    $this->flash('success', 'Role has been successfully added.');
                    return $this->redirect($response, 'admin-roles');
                }
            }
        }

        $this->flash('danger', 'There was a problem adding the role.');
        return $this->redirect($response, 'admin-roles');
    }

    public function rolesDelete(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('role.delete')) {
            return $check;
        }

        $role = R::find($request->getParam('role_id'));

        if ($role && $role->id != 1) {

            RoleUsers::where('role_id', '=', $request->getParam('role_id'))->delete();

            $removeRole = R::find($request->getParam('role_id'));
            if ($removeRole->delete()) {
                $this->flash('success', 'Role has been removed.');
                return $this->redirect($response, 'admin-roles');
            }
        }

        $this->flash('danger', 'There was a problem removing the role.');
        return $this->redirect($response, 'admin-roles');
    }

    public function rolesEdit(Request $request, Response $response, $roleid)
    {
      if ($check = $this->sentinel->hasPerm('role.update', 'dashboard')) {
          return $check;
      }

      $role = R::find($roleid);

        if ($role) {
          if ($request->isPost()) {
              // Validate Data
              $validateData = array(
                  'role_name' => array(
                      'rules' => V::length(2, 25)->alpha('\''),
                      'messages' => array(
                          'length' => 'Must be between 2 and 25 characters.',
                          'alpha' => 'Letters only and can contain \''
                          )
                  ),
                  'role_slug' => array(
                      'rules' => V::slug(),
                      'messages' => array(
                          'slug' => 'May only contain lowercase letters, numbers and hyphens.'
                          )
                  )
              );

              $this->validator->validate($request, $validateData);

              //Validate Role Name
              $checkName = $role->where('id', '!=', $role->id)
                  ->where('name', '=', $role->name)
                  ->first();
              if ($checkName) {
                  $this->validator->addError('role_name', 'Nama Role Sudah Tersedia');
              }

              //Validate Role Name
              $checkSlug = $role->where('id', '!=', $role->id)
                  ->where('slug', '=', $role->name)
                  ->first();
              if ($checkSlug) {
                  $this->validator->addError('role_slug', 'Role slug sudah Tersedia.');
              }

              // Create Permissions Array
              $permissionsArray = array();
              foreach ($request->getParam('perm_name') as $pkey => $pvalue) {
                  $val = true;
                  if ($request->getParam('perm_value')[$pkey] == "true") {
                      $val = true;
                  }
                  $permissionsArray[$pvalue] = $val;
              }



              if ($this->validator->isValid()) {
                  $updateRole = $role;
                  $updateRole->name = $request->getParam('role_name');
                  $updateRole->slug = $request->getParam('role_slug');
                  $updateRole->save();

                  $rolePerms = $this->auth->findRoleById($updateRole->id);
                  $rolePerms->permissions = $permissionsArray;
                  $rolePerms->save();

                  $this->flash('success', 'Roles Berhasil Di Update');
                  return $this->redirect($response, 'admin-roles');
              }
          }

            return $this->view->render($response, 'dashboard/admin/usersmanagement/roles/roles-edit.twig',
            [
              'role' => $role,
              "permissions_menu" => RolesPermissionsMenus::orderBy('slug')->get(),
              "permissions" => RolesPermissions::orderBy('slug')->get()
            ]);
        }

        $this->flash('danger', 'Sorry, that role was not found.');
        return $response->withRedirect($this->router->pathFor('admin-roles'));
    }
}
