<?php

defined('ISHOP') or die ('Access denied');

//домен
define('PATH', 'http://ishop/');

//модель
define('MODEL', 'model/model.php');

//контроллер
define('CONTROLLER', 'controller/controller.php');

//вид
define('VIEW', 'views/');

//активный шаблон
define('TEMPLATE', VIEW.'ishop/');

//папка с картинками контента
define('PRODUCT_IMG', PATH.'userfiles/product_img/baseimg/');

//папка с картинками контента галереии
define('GALLERYIMG', PATH.'userfiles/product_img/');

//максимально допустимый вес загружаемых картинок - 1мб
define('SIZE', 1048576);

//сервер БД
define('HOST','localhost');

//пользователь
define('USER','mysql');

//пароль
define('PASS','mysql');

//имя БД
define('DB','ishop');

//ПОЧТА АДМИНА
define('ADMIN_EMAIL', 'vexel1992@gmail.com');

//НАЗВАНИЕ МАГАЗИНА
define('TITLE', 'Интернет магазин сотовых телефонов');

//Количество товаров на страницу
define('PERPAGE', 9);

//папка шаблонов административной части
define('ADMIN_TEMPLATE', 'templates/');

//подключение к БД
mysql_connect(HOST, USER, PASS) or die('No connect to server');
mysql_select_db(DB) or die('No connect to DB');
mysql_query("SET NAMES 'UTF8'") or die('Cant set charset');
?>