<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'voting';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['admin'] = 'admin/index';
$route['admin/login'] = 'admin/login';
$route['admin/authenticate'] = 'admin/authenticate';
$route['admin/logout'] = 'admin/logout';
$route['admin/candidates'] = 'admin/candidates';
$route['admin/candidate_save'] = 'admin/candidate_save';
$route['admin/candidate_toggle/(:num)'] = 'admin/candidate_toggle/$1';
$route['admin/candidate_delete/(:num)'] = 'admin/candidate_delete/$1';
$route['admin/voters'] = 'admin/voters';
$route['admin/voter_save'] = 'admin/voter_save';
$route['admin/voter_toggle/(:num)'] = 'admin/voter_toggle/$1';
$route['admin/voter_reset/(:num)'] = 'admin/voter_reset/$1';
$route['admin/verify_tamper'] = 'admin/verify_tamper';
$route['admin/settings'] = 'admin/settings';
$route['admin/settings_save'] = 'admin/settings_save';
$route['admin/export_results'] = 'admin/export_results';
