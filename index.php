<?php
session_start();
require 'vendor/autoload.php';

use Cocur\Slugify\Slugify;

$slugify = new Slugify();

$router = new AltoRouter();

// import config
require 'config.php';

// import database;
require 'connectDb.php';

// import model
require 'bootstrap.php';

// import controller
require ABSPATH_SITE . 'load.php';

// Trang chủ
$router->map('GET', '/',  ['HomeController', 'index'], 'home');

// Trang danh sách sản phẩm
$router->map('GET', '/san-pham',  ['ProductController', 'index'], 'product');

// Trang chính sách
$router->map('GET', '/chinh-sach-doi-tra.html',  ['InformationController', 'returnPolicy'], 'returnPolicy');

$router->map('GET', '/chinh-sach-giao-hang.html',  ['InformationController', 'deliveryPolicy'], 'deliveryPolicy');

$router->map('GET', '/chinh-sach-thanh-toan.html',  ['InformationController', 'paymentPolicy'], 'paymentPolicy');

$router->map('GET', '/lien-he.html',  ['ContactController', 'form'], 'contact');

// Trang chi tiết sản phẩm
// san-pham/kem-danh-rang-2.html
// slug: kem-danh-rang
// id: 2
$router->map('GET', '/san-pham/[*:slug]-[i:id].html', function ($slug, $id) {
    call_user_func_array(['ProductController', 'detail'], [$id]);
}, 'productDetail');

// Trang danh mục sản phẩm
//danh-muc/kem-trang-da-3
$router->map('GET', '/danh-muc/[*:slug]-[i:categoryId]', function ($slug, $categoryId) {
    call_user_func_array(['ProductController', 'index'], [$categoryId]);
}, 'category');

// khoang-gia/0-100000
$router->map('GET', '/khoang-gia/[*:priceRange]', function ($priceRange) {
    call_user_func_array(['ProductController', 'index'], [null, $priceRange]);
}, 'priceRange');

// Tìm kiếm
// /search?search=....
$router->map('GET', '/search', ['ProductController', 'index'], 'search');

// match current request url
$match = $router->match();
$routeName = $match['name'];


// call closure or throw 404 status
if (is_array($match) && is_callable($match['target'])) {
    call_user_func_array($match['target'], $match['params']);
} else {
    // no route was matched
    // echo 'Trang không tồn tại';
    // header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
    // // router
    // http://godashop.com/index?c=cart&a=add...
    $c = $_GET['c'] ?? 'home';
    $a = $_GET['a'] ?? 'index';
    $str = ucfirst($c); //vd: Home
    $controllerName = $str . 'Controller'; //HomeController
    $controller = new $controllerName(); //new HomeController();
    $controller->$a();//$controller->create();

}