<?php

// запрет прямого обращения
define('ISHOP', TRUE);

//запускаем сессию
session_start();

if($_GET['do']=='logout'){
    unset($_SESSION['auth']);
}

if(!$_SESSION['auth']['admin']){
    //подключение авторизации
    include $_SERVER['DOCUMENT_ROOT'].'/admin/auth/index.php';
}

// подключение файла конфигурации
require_once '../config.php';

//подключение файла ф-ций
require_once '../functions/functions.php';

//подключение файлв ф-ций админ части
require_once 'functions/functions.php';

//получение количества не обработаных заказов
$count_new_orders = count_new_orders();

//загрузка картинок ajaxupload
if($_POST['id']){
    $id = (int)$_POST['id'];
    upload_gallery_img($id);
}

//удаление картинок
if($_POST['img']){
    $res = del_img();
    exit($res);
}

//получение массива Каталога
$cat=Catalog();

active_url();
// получение динамичной части шаблона #content
$view = empty($_GET['view']) ? 'pages' : $_GET['view'];

switch($view){
    case('pages'):
        // страницы
        $pages=pages();
    break;
    
    case('informers'):
        // информеры
        $informers=informer();
    break;
    case('edit_page'):
    //редактирование страници
    $page_id=(int)$_GET['page_id'];
    $get_page=get_page($page_id);
    
    if($_POST){
         if(edit_page($page_id)) redirect("?view=pages");
         else redirect();
    } 
    break;
    //
    case('add_page'):
    if($_POST){
         if(add_page()) redirect("?view=pages");
          else redirect();
         }     
    break;
    case('del_page'):
    $page_id=(int)$_GET['page_id'];
    del_page($page_id);
    redirect();
    break;
    case('news'):
     //параметры для навигации
    $perpage= 2; //кол-во товаров на странице
    if(isset($_GET['page'])){
      $page = (int)$_GET['page'];
      if($page < 1 ) $page = 1;
    }
     else{
          $page = 1;
      }
    $count_rows = count_news(); // общее кол-во товаров
    $pages_count = ceil($count_rows / $perpage); // кол-во страниц
    if(!$pages_count) $pages_count = 1; // минимум 1 страница
    if($page > $pages_count) $page = $pages_count; // если запрошенная страница больше максимума
    $start_pos = ($page - 1) * $perpage; // начальная позиция для запроса
    
    $all_news=get_all_news($start_pos, $perpage);    
    break;
    case('add_news'):
     if($_POST){
         if(add_news()) redirect("?view=news");
          else redirect();
         }     
    break;
    case('edit_news'):
    //редактирование страници
    $news_id=(int)$_GET['news_id'];
    $get_news=get_news($news_id);
    
    if($_POST){
         if(edit_news($news_id)) redirect("?view=news");
         else redirect();
    } 
    break;
    
    case('del_news'):
    //удаление новости
    $news_id=(int)$_GET['news_id'];
    del_news($news_id);
    redirect();
    break;
    
    case("add_link"):
    //добавление информера
    $informer_id=(int)$_GET['informer_id'];
    $informers = get_informers(); //список информеров
    if($_POST){
         if(add_link()) redirect("?view=informers");
          else redirect();
         }    
    break;
    
    case('edit_link'):
    $link_id = (int)$_GET['link_id'];
    $get_link = get_link($link_id); 
    $informers = get_informers();//список информеров
    if($_POST){
         if(edit_link($link_id)) redirect("?view=informers");
          else redirect();
         }    
    break;
    
    case('del_link'):
     //удаление линка информера
    $link_id = (int)$_GET['link_id'];
    del_link($link_id);
    redirect();
    break;
    
    case('add_informer'):
    if($_POST){
        if(add_informer()) redirect('?view=informers');
        else redirect();
    }
    break;
    
    case('del_informer'):
        //удаление самого информера
        $informer_id=(int)$_GET['informer_id'];
        del_informer($informer_id);
        redirect();
    break;
    
    case('edit_informer'):
        $informer_id = (int)$_GET['informer_id'];
        $get_informer = get_informer($informer_id); 
      //  $informer = get_informer();//список информеров
        if($_POST){
             if(edit_informer($informer_id)) redirect("?view=informers");
              else redirect();
             }    
    break;
    
    case('brands'):
    
    break;
    
    case('add_brand'):
        if($_POST){
            if(add_brand()) redirect('?view=brands');
                else redirect();
        }
    break;
    
    case('edit_brand'):
        $brand_id = (int)$_GET['brand_id'];
        $parent_id = (int)$_GET['parent_id'];
        
        if($parent_id == $brand_id || !$parent_id){
            $cat_name = $cat[$brand_id][0];
        }else{
            $cat_name = $cat[$parent_id]['sub'][$brand_id];
        }
        //$cat_name = $cat[$brand_id][0];
        if($_POST){
            if($parent_id && edit_brand($brand_id)){
                redirect("?view=cat&category=$brand_id");
            }elseif(edit_brand($brand_id)){
                redirect("?view=brands");
            }else{
                redirect();
            }
        }
    break;
    
    case('del_brand'):
        $brand_id = (int)$_GET['brand_id'];
        del_brand($brand_id);
        redirect();
    break;
    
    case("cat"):
        $category = (int)$_GET['category'];
    
        //параметры для навигации
        $perpage= 2; //кол-во товаров на странице
        if(isset($_GET['page'])){
          $page = (int)$_GET['page'];
          if($page < 1 ) $page = 1;
        }
         else{
              $page = 1;
          }
        $count_rows = count_rows($category); // общее кол-во товаров
        $pages_count = ceil($count_rows / $perpage); // кол-во страниц
        if(!$pages_count) $pages_count = 1; // минимум 1 страница
        if($page > $pages_count) $page = $pages_count; // если запрошенная страница больше максимума
        $start_pos = ($page - 1) * $perpage; // начальная позиция для запроса
        //параметры для навигации
        $brand_name=brand_name($category);//хлебные крохи
        $products = products($category, $start_pos, $perpage); // получаем массив из модели
    break;
    
    case("add_product"):
        $brand_id = (int)$_GET['brand_id'];
        if($_POST){
            if(add_product()) redirect("?view=cat&category=$brand_id");
            else redirect();
        }
    break;
    
    case("edit_product"):
        $goods_id = (int)$_GET['goods_id'];
        $get_product = get_product($goods_id);
        $brand_id = $get_product['goods_brandid'];
        //если есть базовая картинка
        if($get_product['img'] != "no_image.jpg"){
            $baseimg = '<img class="delimg" width="48" rel="0" src="'.PRODUCT_IMG.$get_product['img'].'" alt="'.$get_product['img'].'">';
        }else{
            $baseimg = '<input type="file" name="baseimg" />';
        }
        //если есть картинки галереи
        $img_slide="";
        if($get_product['img_slide']){
            $images = explode("|", $get_product['img_slide']);
            foreach($images as $img){
                $img_slide .= "<img class='delimg' rel='1' alt='{$img}' src='" .GALLERYIMG. "thumbs/{$img}'>";
            }
        }
        if($_POST){
            if(edit_product($goods_id))redirect("?view=cat&category=$brand_id");
            else redirect();
        }
    break;
    
    case('orders'):
        if(isset($_GET['confirm'])){
            $order_id = (int)$_GET['confirm'];
            if(confirm_order($order_id)){
                $_SESSION['answer'] = "<div class='success'>Статус заказа №{$order_id} изменен.</div>";
                redirect("?view=orders&status=0");
            }else{
                $_SESSION['answer'] = "<div class='error'>Статус заказа №{$order_id} нихуя не изменен.</div>";
                redirect("?view=orders&status=0");
            }
        }
        if($_GET['status'] === '0'){
            $status = " WHERE orders.status = '0'";
        }else{
            $status = null;
        }
        
        
        //параметры для навигации
        $perpage= 2; //кол-во товаров на странице
        if(isset($_GET['page'])){
          $page = (int)$_GET['page'];
          if($page < 1 ) $page = 1;
        }
         else{
              $page = 1;
          }
        $count_orders = count_orders(); // общее кол-во товаров
        $pages_count = ceil($count_orders / $perpage); // кол-во страниц
        if(!$pages_count) $pages_count = 1; // минимум 1 страница
        if($page > $pages_count) $page = $pages_count; // если запрошенная страница больше максимума
        $start_pos = ($page - 1) * $perpage; // начальная позиция для запроса
        //параметры для навигации
        $orders = orders($status, $start_pos, $perpage);
    break;
    
        case('show_order'):
        $order_id = (int)$_GET['order_id'];
        $show_order = show_order($order_id);
        if($show_order[0]['status']){
            $state = "обработан";
        }else{
            $state = "не обработан";
        }
        
    break;
    
    case('users'):
        //параметры для навигации
        $perpage= 6; //кол-во товаров на странице
        if(isset($_GET['page'])){
          $page = (int)$_GET['page'];
          if($page < 1 ) $page = 1;
        }
         else{
              $page = 1;
          }
        $count_rows = count_users(); // общее кол-во userov
        $pages_count = ceil($count_rows / $perpage); // кол-во страниц
        if(!$pages_count) $pages_count = 1; // минимум 1 страница
        if($page > $pages_count) $page = $pages_count; // если запрошенная страница больше максимума
        $start_pos = ($page - 1) * $perpage; // начальная позиция для запроса
        //параметры для навигации
        
        $users = get_users($start_pos, $perpage);
    break;
    
    case('add_user'):
    $roles = get_roles();
        if($_POST){
             if(add_user()) redirect("?view=user");
        else redirect();
        }
       
    break;
    
    case('edit_user'):
        $user_id = (int)$_GET['user_id'];
        $get_user = get_user($user_id);
        $roles = get_roles();
        if($_POST){
            if(edit_user($user_id)) redirect("?view=users");
            else redirect();
        }
    break;
    
    case('del_user'):
        $user_id = (int)$_GET['user_id'];
        del_user($user_id);
        redirect();
    break;
    
    default:
    // если из адресной строки получено имя несуществующего вида
    $view = 'pages';
    $pages=pages();
}

// HEADER
include ADMIN_TEMPLATE.'header.php';

// LEFTBAR
include ADMIN_TEMPLATE.'leftbar.php';

// CONTENT
include ADMIN_TEMPLATE.$view.'.php';

?>