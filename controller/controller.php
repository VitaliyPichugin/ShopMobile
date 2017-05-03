<?php
defined('ISHOP') or die ('Access denied');
//контроллер';

session_start();//открываем сессию

//подключение модели
require_once MODEL;

//подключение библиотеки функция
require_once 'functions/functions.php';

//получение массива страниц
$pages=pages();

//получение новочтей
$news=get_title_news();

//получение массива Каталога
$cat=Catalog();

//регистрация
if($_POST['reg']){
    registration();
    redirect();
}

//авторизация
if($_POST['auth']){
 authorization();
 if($_SESSION['auth']['user']){
    //если пользователь авторизовался
    echo " <p>Добро пожаловать, {$_SESSION['auth']['user']}  </p>";
    exit;
 }
 else{
    //если авторизация не удачна
    echo $_SESSION['auth']['error'];
    unset($_SESSION['auth']);
    exit;
 }

}

//выход пользователя
if($_GET['do']=='logout'){
    logout();
    redirect();
}

//получение массива Информеров
$informers=informer();

//получение динамичной части шаблона #content
$view=empty($_GET['view'])? 'hits' : $_GET['view'];

switch($view){
    case('hits'):
        //лидеры продаж
        $eyestopers=eyestoper('hits');
         break;
    case('new'):
        //новинки
        $eyestopers=eyestoper('new');
        break;
    case('search'):
    //поиск
    $result_search=search();

    // параметры для навигации
    $perpage = 3; // кол-во товаров на страницу
    if(isset($_GET['page'])){
        $page = (int)$_GET['page'];
        if($page < 1) $page = 1;
    }else{
        $page = 1;
    }
    $count_rows = count($result_search); // общее кол-во товаров
    $pages_count = ceil($count_rows / $perpage); // кол-во страниц
    if(!$pages_count) $pages_count = 1; // минимум 1 страница
    if($page > $pages_count) $page = $pages_count; // если запрошенная страница больше максимума
    $start_pos = ($page - 1) * $perpage; // начальная позиция для запроса
    $endpos = $start_pos + $perpage; // до какого товара будет вывод на странице
    if($endpos > $count_rows) $endpos = $count_rows;
    
    break;
    case('sale'):
        //распродажа
        $eyestopers=eyestoper('sale');
                
        break;
    case('cat'):
        // товары категории
        $category = abs((int)$_GET['category']);


        //сортировка
        //массив параметров сортировки
        //ключи - то что передаем в GET параметром
        //значение то что показываем пользователю и часть SQL запроса который передаем в модель
        //
        $order_p=array(
                        'pricea'=>array('от дешевых к дорогим', 'price ASC'),
                        'priced'=>array('от дорогих к дешевым', 'price DESC'),
                        'datea'=>array('от старых к новым', 'date ASC'),
                        'dated'=>array('от новых к старым', 'date DESC'),
                        'namea'=>array('от А до Я', 'name ASC'),
                        'named'=>array('от Я до А', 'name DESC')
                      );
        $order_get=clear($_GET['order']);//получаем возможный параметр сортировки
        if(array_key_exists($order_get, $order_p)){
            $order=$order_p[$order_get][0];
            $order_db=$order_p[$order_get][1];
        }
        else{
            //по умолчанию сортиовка от А до Я
            $order=$order_p['namea'][0];
            $order_db=$order_p['namea'][1];
        }
        //сортировка
        
        
        //параметры для навигации
        $perpage= PERPAGE; //кол-во товаров на странице
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
        
        $brand_name=brand_name($category);//хлебные крохи
        $products = products($category, $order_db, $start_pos, $perpage); // получаем массив из модели

    break;
     case('addtocart'):
        // добавление в корзину
        $goods_id = abs((int)$_GET['goods_id']);
        addtocart($goods_id);

        $_SESSION['total_sum'] = total_sum($_SESSION['cart']);//сумма заказа

        // кол-во товара в корзине + защита от ввода несуществующего ID товара
        total_quantity();
        redirect();

    break;
    case('page'):
    //отдельная страница
    $page_id=abs((int)$_GET['page_id']);
    $get_page=get_page($page_id);
    break;
    
    //отдельная новость
    case('news'):
    $news_id=abs((int)$_GET['news_id']);
    $get_news=get_news_text($news_id);
    break;
    
    //архив новостей
    case('archive'):
       
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
    
    case("informer"):
    //текст информера
    $informer_id=abs((int)$_GET['informer_id']);
    $text_informer=get_text_informer($informer_id);
    
    break;
    
    case('cart'):
    //корзина

    //получение способов доставки
    $dostavka=get_dostavka();

    //пересчет товаров в корзине
    if(isset($_GET['id'], $_GET['qty'])){
        $goods_id=abs((int)$_GET['id']);
        $qty=abs((int)$_GET['qty']);

        $qty=$qty-$_SESSION['cart'][$goods_id]['qty'];
        addtocart($goods_id,$qty);

        $_SESSION['total_sum'] = total_sum($_SESSION['cart']);//сумма заказа

        total_quantity(); // кол-во товара в корзине + защита от ввода несуществующего ID товара
        redirect();
    }
    //удаление товара из корзины
    if(isset($_GET['delete'])){
        $id=abs((int)$_GET['delete']);
        if($id){
            delete_from_cart($id);
        }
        redirect();
    }

    if($_POST['order_x']){
        add_order();
        redirect();
    }

    break;
    case ('product'):
        $goods_id=abs((int)$_GET['goods_id']);
        if($goods_id){
            $goods=get_goods($goods_id);
            if($goods){
                $brand_name=brand_name($goods['goods_brandid']);//хлебные крошки
            }
        }
        break;
    case('reg'):
    //регистрация
    break;
    case('filter'):
    //выбор по параметрам
    $startprice=(int)$_GET['startprice'];
    $endprice=(int)$_GET['endprice'];
    $brand=array();
    
    if($_GET['brand']){
        foreach($_GET['brand'] as $value){
            $value=(int)$value;
            $brand[$value]=$value;
        }
    }
    if($brand){
        $category=implode(',', $brand);
    }
    $products=filter($category, $startprice, $endprice);
    break;

    default:
        //если передан неизвестный параметр
        $view='hits';
        $eyestopers=eyestoper('hits');

}

//подключение вида
require_once TEMPLATE.'index.php';
?>
