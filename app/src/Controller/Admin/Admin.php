<?php

namespace App\Controller\Admin;

use Carbon\Carbon;
use App\Model\ContactRequests;
use App\Model\Oauth2Providers;
use App\Model\JenisUsahaPost as JU;
use App\Model\Users;
use App\Model\Daerah;
use App\Model\UsersProfile;
use App\Model\BkolPerusahaan;
use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Respect\Validation\Validator as V;
use Slim\Views\PhpRenderer;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class Admin extends Controller
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function contact(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('contact.view')) {
            return $check;
        }

        return $this->view->render(
            $response,
            'dashboard/contact.twig',
            array("contactRequests" => ContactRequests::orderBy('created_at', 'desc')->get())
        );
    }

    public function contactDatatables(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('contact.view')) {
            return $check;
        }

        $totalData = ContactRequests::count();

        $totalFiltered = $totalData;

        $limit = $request->getParam('length');
        $start = $request->getParam('start');
        $order = $request->getParam('columns')[$request->getParam('order')[0]['column']]['data'];
        $dir = $request->getParam('order')[0]['dir'];

        $contact = ContactRequests::select('id', 'name', 'email', 'phone', 'comment', 'created_at')
            ->skip($start)
            ->take($limit)
            ->orderBy($order, $dir);

        if (!empty($request->getParam('search')['value'])) {
            $search = $request->getParam('search')['value'];

            $contact =  $contact->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('comment', 'LIKE', "%{$search}%");

            $totalFiltered = ContactRequests::where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('comment', 'LIKE', "%{$search}%")
                    ->count();
        }

        $jsonData = array(
            "draw"            => intval($request->getParam('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $contact->get()->toArray()
            );

        return $response->withJSON(
            $jsonData,
            200
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function dashboard(Request $request, Response $response)
    {
        // if ($check = $this->sentinel->hasPerm('settings.view')) {
        //     return $check;
        // }

        // Get Users By Month
        $users = Users::select(DB::raw('count(id) as total'), 'created_at')
        ->where('created_at', '>', Carbon::now()->subYear())
        ->get()
        ->groupBy(function ($date) {
            //return Carbon::parse($date->created_at)->format('Y'); // grouping by years
            return Carbon::parse($date->created_at)->format('Y-m'); // grouping by months
        });
        $usersByMonth = [];
        foreach ($users as $key => $value) {
            $usersByMonth[] = array("month" => $key, "total" => $value[0]->total);
        }
        $usersByMonth = json_encode($usersByMonth);

        // Get Users Last 90 Days
        $users2 = Users::select(DB::raw('count(id) as total'), 'created_at')
        ->where('created_at', '>', Carbon::now()->subDays(90))
        ->get()
        ->groupBy(function ($date) {
            //return Carbon::parse($date->created_at)->format('Y'); // grouping by years
            return Carbon::parse($date->created_at)->format('Y-m-d'); // grouping by months
        });
        $usersByDay = [];
        foreach ($users2 as $key => $value) {
            $usersByDay[] = array("date" => $key, "total" => $value[0]->total);
        }
        $usersByDay = json_encode($usersByDay);

        // Get Oauth2 Providers
        $providers = Oauth2Providers::withCount(['users'])->get();

        return $this->view->render(
            $response,
            'dashboard/dashboard.twig',
            array(
                "usersByMonth" => $usersByMonth,
                "usersByDay" => $usersByDay,
                "providers" => $providers
            )
        );
    }
    public function dashboardv2(Request $request, Response $response)
    {
        
        return $this->view->render(
            $response,
            'dashboardv2/index.twig'
        );
    }

    public function dashboardpencaker(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('jobseeker.view')) {
            return $check;
        }
        return $this->view->render(
            $response,
            'bkol/dashboard/dashboard.twig',
            array(
            )
        );
    }
    public function dashboardperusahaan(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('companies.view')) {
            return $check;
        }
        return $this->view->render(
            $response,
            'bkol/dashboard-perusahaan/dashboard.twig',
            array(
            )
        );
    }
    public function myCompanies(Request $request, Response $response)
    {
        if ($check = $this->sentinel->hasPerm('companies.account')) {
            return $check;
        }

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

        $daerahs = Daerah::where('lokasi_kabupatenkota', 0)
        ->where('lokasi_kecamatan', 0)
        ->where('lokasi_kelurahan', 0)
        ->orderBy('lokasi_nama')
        ->get();

        return $this->view->render(
            $response, 'dashboard/my-companies.twig',
            array(
                "jenisusaha" => JU::get(),
                "daerahs" => $daerahs
            )
        );
    }
    private function updateCompanies($requestParams)
    {
        $user = Users::with('companiesprofile')->find($this->auth->check()->id);

        $newInformation = [
            'first_name' => $requestParams['first_name'],
            'last_name' => $requestParams['last_name'],
            'email' => $requestParams['email'],
            'username' => $requestParams['username']
        ];

        $updateUser = $this->auth->update($user, $newInformation);

        $updateCompanies = new BkolPerusahaan;
        $updateCompanies = $updateCompanies->find($user->id);
        if ($updateCompanies) {
            $updateCompanies->companyname = strip_tags($requestParams['companyname']);
            $updateCompanies->companysaddress = strip_tags($requestParams['companysaddress']);
            $updateCompanies->logo = strip_tags($requestParams['logo']);
            $updateCompanies->about = strip_tags($requestParams['about']);
            $updateCompanies->website = strip_tags($requestParams['website']);
            $updateCompanies->industry = strip_tags($requestParams['industry']);
            $updateCompanies->phonenumber = strip_tags($requestParams['phonenumber']);
            $updateCompanies->companysize = strip_tags($requestParams['companysize']);
            $updateCompanies->workingtime = strip_tags($requestParams['workingtime']);
            $updateCompanies->fashionstyle = strip_tags($requestParams['fashionstyle']);
            $updateCompanies->languageused = strip_tags($requestParams['languageused']);
            $updateCompanies->whyjoinus = strip_tags($requestParams['whyjoinus']);
            $updateCompanies->provinsi_id = strip_tags($requestParams['provinsi_id']) ? strip_tags($requestParams['provinsi_id']) : NULL;
            $updateCompanies->kabupatenkota_id = strip_tags($requestParams['kabupatenkota_id']) ? strip_tags($requestParams['kabupatenkota_id']) : NULL;
            $updateCompanies->kecamatan_id = strip_tags($requestParams['kecamatan_id']) ? strip_tags($requestParams['kecamatan_id']) : NULL;
            $updateCompanies->kelurahan_id = strip_tags($requestParams['kelurahan_id']) ? strip_tags($requestParams['kelurahan_id']) : NULL;

            $updateCompanies->save();
        }
        if (!$updateCompanies) {
            $addCompanies = new BkolPerusahaan;
            $addCompanies->user_id = $user->id;
            $addCompanies->id = $user->id;
            $addCompanies->companyname = strip_tags($requestParams['companyname']);
            $addCompanies->companysaddress = strip_tags($requestParams['companysaddress']);
            $addCompanies->logo = strip_tags($requestParams['logo']);
            $addCompanies->about = strip_tags($requestParams['about']);
            $addCompanies->website = strip_tags($requestParams['website']);
            $addCompanies->industry = strip_tags($requestParams['industry']);
            $addCompanies->phonenumber = strip_tags($requestParams['phonenumber']);
            $addCompanies->companysize = strip_tags($requestParams['companysize']);
            $addCompanies->workingtime = strip_tags($requestParams['workingtime']);
            $addCompanies->fashionstyle = strip_tags($requestParams['fashionstyle']);
            $addCompanies->languageused = strip_tags($requestParams['languageused']);
            $addCompanies->whyjoinus = strip_tags($requestParams['whyjoinus']);
            $addCompanies->provinsi_id = strip_tags($requestParams['provinsi_id']) ? strip_tags($requestParams['provinsi_id']) : NULL;
            $addCompanies->kabupatenkota_id = strip_tags($requestParams['kabupatenkota_id']) ? strip_tags($requestParams['kabupatenkota_id']) : NULL;
            $addCompanies->kecamatan_id = strip_tags($requestParams['kecamatan_id']) ? strip_tags($requestParams['kecamatan_id']) : NULL;
            $addCompanies->kelurahan_id = strip_tags($requestParams['kelurahan_id']) ? strip_tags($requestParams['kelurahan_id']) : NULL;


            $addCompanies->save();


        }

        if ($updateUser && ($addCompanies || $updateCompanies)) {
            return true;
        }

        return false;


    }

    public function GantiPassword(Request $request, Response $response)
    {
        return $this->view->render(
            $response,
            'dashboard/ganti-password.twig'
        );
    }


}
