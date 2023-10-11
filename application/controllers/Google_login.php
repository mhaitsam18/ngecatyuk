<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Google_login extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('google_login_model');
        date_default_timezone_set("Asia/Jakarta");
    }
    public function login()
    {
        require "vendor/autoload.php";
        $google_client = new Google_Client();
        // $google_client = new Google\Client();

        $google_client->setClientId('892439740462-4976rses6q7e4rjf10l2065rk40m67n2.apps.googleusercontent.com'); //Define your ClientID

        $google_client->setClientSecret('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'); //Define your Client Secret Key

        $google_client->setRedirectUri(base_url() . '/ngecatyuk/google_login/login'); //Define your Redirect Uri

        $google_client->addScope('email');

        $google_client->addScope('profile');

        if (isset($_GET["code"])) {
            $token = $google_client->fetchAccessTokenWithAuthCode($_GET["code"]);

            if (!isset($token["error"])) {
                $google_client->setAccessToken($token['access_token']);

                $this->session->set_userdata('access_token', $token['access_token']);

                $google_service = new Google\Service\Oauth2($google_client);

                $data = $google_service->userinfo->get();

                $current_datetime = date('Y-m-d H:i:s');

                if ($this->google_login_model->Is_already_register($data['id'])) {
                    //update data
                    $user_data = array(
                        'first_name' => $data['given_name'],
                        'last_name'  => $data['family_name'],
                        'email_address' => $data['email'],
                        'profile_picture' => $data['picture'],
                        'updated_at' => $current_datetime
                    );

                    $this->google_login_model->Update_user_data($user_data, $data['id']);
                } else {
                    //insert data
                    $user_data = array(
                        'login_oauth_uid' => $data['id'],
                        'first_name'  => $data['given_name'],
                        'last_name'   => $data['family_name'],
                        'email_address'  => $data['email'],
                        'profile_picture' => $data['picture'],
                        'created_at'  => $current_datetime
                    );

                    $this->google_login_model->Insert_user_data($user_data);
                }
                $this->session->set_userdata('user_data', $user_data);
            }
        }
        $login_button = '';
        if (!$this->session->userdata('access_token')) {
            $login_button = '<a href="' . $google_client->createAuthUrl() . '"><img src="' . base_url() . 'sign-with-google.png" /></a>';
            $data['login_button'] = $login_button;
            $this->load->view('google_login', $data);
        } else {
            $this->load->view('google_login', $data);
        }
    }


    public function logout()
    {
        $this->session->unset_userdata('access_token');
        $this->session->unset_userdata('user_data');
        redirect('google_login/login');
    }
}
