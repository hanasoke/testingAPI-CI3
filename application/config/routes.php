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
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['users'] = 'api/api/users'; // Get all users
$route['user/(:num)'] = 'api/api/user/$1'; // Handle GET/PUT/PATCH here
$route['create_user'] = 'api/api/create_user'; // POST create user

$route['car/cars'] = 'car/cars'; // Get all cars
$route['car/car/(:num)'] = 'car/car/$1'; // Handle GET/PUT/PATCH here
$route['car/add_car'] = 'car/add_car'; // POST create car

$route['post_category'] = 'post_category_api/categories';
$route['post_category/(:num)'] = 'post_category_api/category/$1';
$route['post_category/add'] = 'post_category_api/add_category';

$route['motorcycles'] = 'motorcycle/all';
$route['motorcycle/(:num)'] = 'motorcycle/detail/$1';
$route['motorcycle/add'] = 'motorcycle/add_motorcycle';

$route['vga_cards'] = 'api/vga_cards/all'; 

// Handle GET/DELETE here
$route['vga_card/(:num)'] = 'api/vga_cards/detail/$1';

// Handle PUT/PATCH here
$route['vga_card/update_vgacard/(:num)'] = 'api/vga_cards/update_vgacard/$1';

$route['vga_card/update/(:num)'] = 'api/vga_cards/update/$1';

// POST create vga_card
$route['vga_card/add_vgacard'] = 'api/vga_cards/add_vgacard';
$route['vga_card/adding_vgacard'] = 'api/vga_cards/adding_vgacard';

// PSU ROUTES
$route['psus'] = 'api/psus/all';
$route['psu/(:num)'] = 'api/psus/detail/$1';
$route['psu/update/(:num)'] = 'api/psus/update_psu/$1';
// Delete
$route['psu/delete/(:num)'] = 'api/psus/delete_psu/$1';
// Add
$route['psu/add_psu'] = 'api/psus/add_psu';

// CPU Routes
$route['cpus'] = 'api/cpu/all';
$route['cpu/(:num)'] = 'api/cpu/detail/$1';
// Add
$route['cpu/add'] = 'api/cpu/add';