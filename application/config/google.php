<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Google API Configuration
| -------------------------------------------------------------------
| 
| To get API details you have to create a Google Project
| at Google API Console (https://console.developers.google.com)
| 
|  client_id         string   Your Google API Client ID.
|  client_secret     string   Your Google API Client secret.
|  redirect_uri      string   URL to redirect back to after login.
|  application_name  string   Your Google application name.
|  api_key           string   Developer key.
|  scopes            string   Specify scopes
*/
$config['google']['client_id']        = 'Google_API_Client_ID';
$config['google']['client_secret']    = 'Google_API_Client_Secret';

$base_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
$base_url .= "://" . $_SERVER['HTTP_HOST'];
$base_url .= preg_replace('@/+$@', '', dirname($_SERVER['SCRIPT_NAME'])) . '/';

$config['google']['redirect_uri']     = $base_url . '/project_folder_name/user_authentication/';
$config['google']['application_name'] = 'NgeCatYuk By HAFA Studio';
$config['google']['api_key']          = '';
$config['google']['scopes']           = array();
