<?php
// // router
// session_start();
// // import config và connect database
// require '../config.php';
// require '../connectDb.php';

// // import model
// require '../bootstrap.php';

// // import các thư viện
// require '../vendor/autoload.php';

// // router
// //vd: http://godashop.com/site/?c=product
// // $c = isset($_GET['c']) ? $_GET['c'] : 'home'
// $c = $_GET['c'] ?? 'home';
// // $a = isset($_GET['a']) ? $_GET['a'] : 'index'
// $a = $_GET['a'] ?? 'index';

// $str = ucfirst($c); //vd: Home
// $controllerName = $str . 'Controller'; //HomeController
// $str = "controller/$controllerName.php";
// require $str; //vd: require controller/HomeController.php
// $controller = new $controllerName(); //new HomeController();
// $controller->$a();//$controller->create();