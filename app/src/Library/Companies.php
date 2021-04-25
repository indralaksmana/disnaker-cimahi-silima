<?php

namespace App\Library;

use Carbon\Carbon;
use App\Library\Utils;
use App\Library\VideoParser as VP;
use App\Model\CompaniesProfile;
use App\Model\Users;
use Psr\Container\ContainerInterface;
use Respect\Validation\Validator as V;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class Companies extends Library
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    public function addCompanies()
    {
        $user = Users::with('companiesprofile')->find($this->auth->check()->id);

        if ($request->isPost()) {
            // Validate Data
            $validateData = array(
                'first_name' => array(
                    'rules' => V::length(2, 25)->alnum('\'?!@#,."'),
                    'messages' => array(
                        'length' => 'Must be between 2 and 25 characters.',
                        'alnum' => 'Contains an invalid character.'
                        )
                ),
                'last_name' => array(
                    'rules' => V::length(2, 25)->alnum('\'?!@#,."'),
                    'messages' => array(
                        'length' => 'Must be between 2 and 25 characters.',
                        'alnum' => 'Letters only and can contain \''
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

            //Check username
            $checkUsername = Users::where('id', '!=', $user->id)
                ->where('username', '=', $request->getParam('username'))
                ->first();
            if ($checkUsername) {
                $this->validator->addError('username', 'Username is already in use.');
            }

            //Check Email
            $checkEmail = Users::where('id', '!=', $user->id)
                ->where('email', '=', $request->getParam('email'))
                ->first();
            if ($checkEmail) {
                $this->validator->addError('email', 'Email address is already in use.');
            }

            $this->validator->validate($request, $validateData);

            if ($this->validator->isValid()) {
                if ($this->updateCompanies($request->getParams())) {
                    $this->flash('success', 'Your account has been updated successfully.');
                    return $this->redirect($response, 'my-companies');
                }
                $this->flashNow('danger', 'There was an error updating your account information.');
            }
        }
    }

    public function updateCompanies($postId)
    {
        $user = Users::with('companiesprofile')->find($this->auth->check()->id);

        $newInformation = [
            'first_name' => $requestParams['first_name'],
            'last_name' => $requestParams['last_name'],
            'email' => $requestParams['email'],
            'username' => $requestParams['username']
        ];

        $updateUser = $this->auth->update($user, $newInformation);

        $updateCompanies = new CompaniesProfile;
        $updateCompanies = $updateCompanies->find($user->id);
        if ($updateCompanies) {
            $updateCompanies->nama_perusahaan = strip_tags($requestParams['nama_perusahaan']);
            $updateCompanies->alamat_perusahaan = strip_tags($requestParams['alamat_perusahaan']);
            $updateCompanies->about = strip_tags($requestParams['about']);
            $updateCompanies->website = strip_tags($requestParams['website']);
            $updateCompanies->save();
        }
        if (!$updateCompanies) {
            $addCompanies = new CompaniesProfile;
            $addCompanies->user_id = $user->id;
            $addCompanies->nama_perusahaan = strip_tags($requestParams['nama_perusahaan']);
            $addCompanies->alamat_perusahaan = strip_tags($requestParams['alamat_perusahaan']);
            $addCompanies->about = strip_tags($requestParams['about']);
            $addCompanies->website = strip_tags($requestParams['website']);
            $addCompanies->save();
        }

        if ($updateUser && ($addCompanies || $updateCompanies)) {
            return true;
        }

        return false;
    }
}
