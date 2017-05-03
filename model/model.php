<?php
defined('ISHOP') or die ('Access denied');
//модель';

//Каталог - получение массива
function Catalog(){
    $query="SELECT * FROM brands ORDER BY parent_id, brand_name";
    $res=mysql_query($query) or die (mysql_error());
    
    //массив категорий
    $cat=array();
    while($row=mysql_fetch_assoc($res)){
        if(!$row['parent_id']){
            $cat[$row['brand_id']][]=$row['brand_name'];
        }
        else{
            $cat[$row['parent_id']]['sub'][$row['brand_id']]=$row['brand_name'];
        }
    }
        return $cat;
}
    
//Информеры - получение массива информеров
function informer(){
    $query = "SELECT * FROM links
                INNER JOIN informers ON
                    links.parent_informer = informers.informer_id
                        ORDER BY informer_position, links_position";
    $res = mysql_query($query) or die(mysql_error());
    
    $informers = array();
    $name = ''; // флаг имени информера
    while($row = mysql_fetch_assoc($res)){
        if($row['informer_name'] != $name){ // если такого информера в массиве еще нет
            $informers[$row['informer_id']][] = $row['informer_name']; // добавляем информер в массив
            $name = $row['informer_name'];
        }
        $informers[$row['parent_informer']]['sub'][$row['link_id']] = $row['link_name']; // заносим страницы в информер
    }
    return $informers;
}
//Информеры - получение массива информеров

//получение текста информера
function get_text_informer($informer_id){
    $query = "SELECT link_id, link_name, text, informers.informer_id, informers.informer_name
                FROM links
                    LEFT JOIN informers ON informers.informer_id=links.parent_informer
                        WHERE link_id=$informer_id";
    $res = mysql_query($query) or die(mysql_error());
    
    $informer = array();
    $informer = mysql_fetch_assoc($res);
    return $informer;
}

//Айстоперы (новинки, лидеры, распродажа)

function eyestoper($eyestoper){
    $query="SELECT goods_id, name, img, price FROM goods
            WHERE visible='1' AND $eyestoper='1'";
    $res=mysql_query($query) or die(mysql_error());
    
    $eyestopers=array();
    while($row=mysql_fetch_assoc($res)){
        $eyestopers[]=$row;
    }
    return $eyestopers;
}
//Айстоперы

//Получение массива товаров по категориям
function products($category,$order_db, $star_pos, $perpage){
    $query="(SELECT goods_id, name, img, anons, price, new, hits, sale, date 
                FROM `goods` 
                    WHERE goods_brandid=$category AND visible='1')
             UNION       
            (SELECT goods_id, name, img, anons, price, new, hits, sale, date 
                FROM `goods` 
                    WHERE goods_brandid IN
                    (
                        SELECT brand_id FROM brands WHERE parent_id=$category
                    )AND visible='1') ORDER BY $order_db LIMIT $star_pos, $perpage ";
    $res=mysql_query($query) or die(mysql_error());
    
    $products=array();
    while($row=mysql_fetch_assoc($res)){
        $products[]=$row;
    }
    return $products;
}
//Получение массива товаров по категориям

//Получение массива для хлебных крох
function brand_name($category){
    $query="SELECT brand_id, brand_name FROM brands
                WHERE brand_id = 
                (SELECT parent_id FROM brands WHERE brand_id = $category)
            UNION
                SELECT brand_id, brand_name FROM brands WHERE brand_id = $category";
    $res=mysql_query($query) or die(mysql_error());
    $brand_name=array();
    while($row=mysql_fetch_assoc($res)){
        $brand_name[]=$row;
    }
    return $brand_name;
}


//выбор по параметрам
function filter($category, $startprice, $endprice){
    $products=array();
    if($category || $endprice){
        $predicat1 = "visible='1'";
        if($category){
            $predicat1 .= " AND goods_brandid IN($category)";
            $predicat2 = "UNION
                        (SELECT goods_id, name, img, price, hits, new, sale
                        FROM goods
                            WHERE goods_brandid IN
                            (
                                SELECT brand_id FROM brands WHERE parent_id IN($category)
                            ) AND visible='1'";
            if($endprice) $predicat2 .= " AND price BETWEEN $startprice AND $endprice";
            $predicat2 .= ")";
        }
        if($endprice){
            $predicat1 .= " AND price BETWEEN $startprice AND $endprice";
        }
        
        $query = "(SELECT goods_id, name, img, price, hits, new, sale
                    FROM goods
                        WHERE $predicat1)
                         $predicat2 ORDER BY name";
        $res=mysql_query($query) or die(mysql_error());
        if(mysql_num_rows($res)>0){
              while($row=mysql_fetch_assoc($res)){
              $products[]=$row;
            }
        }
        else{
            $products['notfound']="<div class='error'>По вашему запросу ничего не найдено</div>";
        }
        
    }
    else{
        $products['notfound']="<div class='error'>Не указаны параметры подбора</div>";
    }
    return $products;
}


//Получение количества товаров для навигации
 function count_rows($category)
{
     $query="(SELECT COUNT(goods_id) as count_rows
                FROM `goods` 
                    WHERE goods_brandid=$category AND visible='1')
             UNION       
            (SELECT COUNT(goods_id) as count_rows
                FROM `goods` 
                    WHERE goods_brandid IN
                    (
                        SELECT brand_id FROM brands WHERE parent_id=$category
                    )AND visible='1')";
    $res=mysql_query($query) or die(mysql_error());

     while($row=mysql_fetch_assoc($res)){
        if($row['count_rows']) $count_rows=$row['count_rows'];
    }
    return $count_rows;
}

//Сумма заказов в корзине + атрибуты товара
function total_sum($goods){
    $total_sum = 0;
    
    $str_goods = implode(',',array_keys($goods));
    
    $query = "SELECT goods_id, name, price, img
                FROM goods
                    WHERE goods_id IN ($str_goods)";
    $res = mysql_query($query) or die(mysql_error());
    
    while($row = mysql_fetch_assoc($res)){
        $_SESSION['cart'][$row['goods_id']]['name'] = $row['name'];
        $_SESSION['cart'][$row['goods_id']]['price'] = $row['price'];
        $_SESSION['cart'][$row['goods_id']]['img'] = $row['img'];
        $total_sum += $_SESSION['cart'][$row['goods_id']]['qty'] * $row['price'];
    }
    return $total_sum;
}

//регистрация
function registration(){
    $error='';//флаг проверки пустых полей
    $login=trim($_POST['login']);
    $pass=trim($_POST['pass']);
    $name=trim($_POST['name']);
    $email=trim($_POST['email']);
    $phone=trim($_POST['phone']);
    $address=trim($_POST['address']);
    
    if(empty($login)) $error .='<li>Не указан логин</li>';
    if(empty($pass)) $error .='<li>Не указан пароль</li>';
    if(empty($name)) $error .='<li>Не указан ФИО</li>';
    if(empty($email)) $error .='<li>Не указан емаил</li>';
    if(empty($phone)) $error .='<li>Не указан телефон</li>';
    if(empty($address)) $error .='<li>Не указан адрес</li>';
    
    if(empty($error)){
        //если все обязательные поля заполнены
        $query="SELECT customer_id FROM customers WHERE ".clear($login)." = '$login' LIMIT 1";
        $res = mysql_query($query) or die(mysql_error());
        $row = mysql_num_rows($res); //1 такой юзер уже есть 0 - такого нет
        if($row){
            //если такой логин уже есть
        $_SESSION['reg']['res']="<div class='error'>Пользователь с таким именем уже существует!!! </div>";
        $_SESSION['reg']['name']=$name;
        $_SESSION['reg']['email']=$email;
        $_SESSION['reg']['phone']=$phone;
        $_SESSION['reg']['address']=$address;
        }
        else{
            //если все ок - регистрируем
            $login=clear($login);
            $name=clear($name);
            $email=clear($email);
            $phone=clear($phone);
            $address=clear($address);
            $pass = md5($pass);
            $query = "INSERT INTO customers (name, email, phone, address, login, password)
                        VALUES ('$name', '$email', '$phone', '$address', '$login', '$pass' )";
            $res=mysql_query($query) or die(mysql_error());
            if(mysql_affected_rows()>0){
                //если запись добавлена
                 $_SESSION['reg']['res']="<div class='success'>Успешно зареган мразь </div>";
                $_SESSION['auth']['user']=$name;
                $_SESSION['auth']['customer_id']=mysql_insert_id();
                 $_SESSION['auth']['email']=$email;
            }
            else{
                 //если что то пошло не так
                 $_SESSION['reg']['res']="<div class='error'>Ошибка </div>";
                $_SESSION['auth']['user']=$name;
                $_SESSION['auth']['customer_id']=mysql_insert_id();
                 $_SESSION['auth']['email']=$email;
            }
        }
        
    }
    else{
        //если не заполнены необходимые поля
        $_SESSION['reg']['res']="<div class='error'>Не заполнены обязательные поля: <ul>$error</ul></div>";
        $_SESSION['reg']['login']=$login;
        $_SESSION['reg']['name']=$name;
        $_SESSION['reg']['email']=$email;
        $_SESSION['reg']['phone']=$phone;
        $_SESSION['reg']['address']=$address;
    }
}

//авторизация
function authorization(){
    $login=mysql_escape_string(trim($_POST['login']));
    $pass=mysql_escape_string(trim($_POST['pass']));
    
    if(empty($login)||empty($pass)){
        //если пусты поля логин и пароль
        $_SESSION['auth']['error']="Поля логин и пароль должны быть заполнены!";
    }
    else{
        //если получены данные из полей логин/пароль
        $pass=md5($pass);
        $query="SELECT customer_id, email, name FROM customers WHERE login='$login' AND password='$pass' LIMIT 1";
        $res = mysql_query($query) or die(mysql_error());
        if(mysql_num_rows($res)==1){
            //если авторизация успешна
            $row=mysql_fetch_row($res);
            $_SESSION['auth']['user']=$row[2];
            $_SESSION['auth']['customer_id']=$row[0];
            $_SESSION['auth']['email']=$row[1];
        }
        else{
            //если не верен логин и пароль
             $_SESSION['auth']['error']="Логин/пароль введены не верно!";
        }
    }
}

//Варианты доставки
function get_dostavka(){
    $query="SELECT * FROM dostavka";
    $res=mysql_query($query);
    
    $dostavka=array();
    while($row=mysql_fetch_assoc($res)){
        $dostavka[]=$row;
    }
    return $dostavka;
}

//добавление заказа
function add_order(){
    //получаем общие данные для всех(авторизованые и не авторизованые)
    $dostavka_id=(int)$_POST['dostavka'];
    if(!$dostavka_id){
        $dostavka_id=1;
    }
    $prim=trim($_POST['prim']);
    if($_SESSION['auth']['user'])$customer_id=$_SESSION['auth']['customer_id'];
    
    if(!$_SESSION['auth']['user']){
        
        $error='';//флаг проверки пустых полей

        $name=trim($_POST['name']);
        $email=trim($_POST['email']);
        $phone=trim($_POST['phone']);
        $address=trim($_POST['address']);
        
        if(empty($name)) $error .= '<li>Не указано ФИО</li>';
        if(empty($email)) $error .= '<li>Не указан Email</li>';
        if(empty($phone)) $error .= '<li>Не указан телефон</li>';
        if(empty($address)) $error .= '<li>Не указан адрес</li>';
        
        if(empty($error)){
            //добавляем гостя в заказчики (но без даных авторизации)
            $customer_id = add_customer($name, $email, $phone, $address);
            if(!$customer_id) return false;//прекращаем выполнение в случае возникновения ошибки добавления гостя заказчика
        }
    else{
        //если не заполнены необходимые поля
        $_SESSION['order']['res']="<div class='error'>Не заполнены обязательные поля: <ul>$error</ul></div>";
        $_SESSION['order']['name']=$name;
        $_SESSION['order']['email']=$email;
        $_SESSION['order']['phone']=$phone;
        $_SESSION['order']['address']=$address;
        return false;
        }
    }

$_SESSION['order']['email']=$email;
    save_order($customer_id, $dostavka_id, $prim);
    
    
}

//добавления гостя-заказчика
function add_customer($name, $email, $phone, $address){
    $name=clear($_POST['name']);
    $email=clear($_POST['email']);
    $phone=clear($_POST['phone']);
    $address=clear($_POST['address']);
    $query="INSERT INTO customers (name, email, phone, address )
                VALUES ('$name', '$email', '$phone', '$address')";
                
    $res = mysql_query($query) or die(mysql_error());
    if(mysql_affected_rows()>0){
        //если гость добавлен в заказчики - получаем его ИД
        return mysql_insert_id();
    }
    else{
        //если не добавлен новый заказчик
        $_SESSION['order']['res']="<div class='error'>Произошла ошибка при регистрации заказа</div>";
        $_SESSION['order']['login']=$login;
        $_SESSION['order']['name']=$name;
        $_SESSION['order']['email']=$email;
        $_SESSION['order']['phone']=$phone;
        $_SESSION['order']['address']=$address;
        $_SESSION['order']['prim'] = $prim;
        return false;
        }
}


//сохранение заказа в бд
function save_order($customer_id, $dostavka_id, $prim){
    $prim=clear($prim);
    $query = "INSERT INTO orders (`customer_id`, `date`, `dostavka_id`, `prim`)
                VALUES ($customer_id, NOW(), $dostavka_id, '$prim')";
    mysql_query($query) or die(mysql_error());
    
    if(mysql_affected_rows()==-1){
        //если не получилось сохранить заказ то мы удаляем заказчика
        mysql_query("DELETE FROM customers 
                        WHERE customer_id=$customer_id AND login = ''");
        return false;
    }
    $order_id=mysql_insert_id(); //ИД сохраненного заказа

    foreach($_SESSION['cart'] as $goods_id => $value){
      $val .= "($order_id, $goods_id, {$value['qty']}, '{$value['name']}', {$value['price']}),";    
     }
    $val = substr($val, 0, -1); // удаляем последнюю запятую
    
    $query = "INSERT INTO zakaz_tovar (orders_id, goods_id, quantity, name, price)
                VALUES $val";
    mysql_query($query) or die(mysql_error());
    if(mysql_affected_rows()==-1){
        //если не выгрузился заказ мы удаляем заказчика(из customers) и сам заказ(orders)
        mysql_query("DELETE FROM orders 
                        WHERE order_id=$order_id");
        mysql_query("DELETE FROM customers 
                        WHERE customer_id=$customer_id AND login = ''");
    return false;
    }
    if($_SESSION['auth']['email']) $email=$_SESSION['auth']['email'];
    else{
            $email= $_SESSION['order']['email'];
    }
    mail_order($order_id, $email);


    //если заказ выгрузился 
    unset($_SESSION['cart']);
    unset($_SESSION['total_sum']);
    unset($_SESSION['total_quantity']);
    $_SESSION['order']['res']="<div class='success'>Спасобо за Ваш заказ. 
                В ближащее время с Вами свяжится мененджр для согласования заказа</div>";
    return true;
}

//отправка уведомлений о заказе на емаил
 function mail_order($order_id, $email)
{
    //mail(to, sub, body, header);
    //тема письма
    $subject="Заказ в интернет магазине мразь";
    //заголовки
    $header .="Content-type: text/plain; charset=utf-8\r\n";
    $header .= "From: ISHOP";
    //ТЕЛО ПИСЬМА
    $mail_body = "Спасибо за Ваш заказ!\r\nНомер Вашего заказа - {$order_id}
    \r\n\r\nЗаказанные товары:\r\n";
    //получаем атрибуты товара
    foreach($_SESSION['cart'] as $goods_id=>$value){
        $mail_body .= "Наименование: {$value['name']}, Цена: {$value['price']} $, Количество: {$value['qty']} шт.\r\n";
    }
    $mail_body .= "\r\nИтого: {$_SESSION['total_quantity']} товара, на сумму {$_SESSION['total_sum']} $ \r\n";
    
    // отправка писем
    mail($email, $subject, $mail_body, $header);//отправка клиенту
    mail(ADMIN_EMAIL, $subject, $mail_body, $header);//отправка админу
}

//поиск
function search(){
    $search=clear($_GET['search']);
    $result_search=array();//результат поиска
    if(empty($search)){
        $result_search['notfound']="<div class='error'>Вы ничего не ввели</div>";
    }
    else{
        if(mb_strlen($search, 'UTF-8')<4){
            $result_search['notfound']="<div class='error'>Поисковая строка должна содержать минимум 4 символа</div>";
        }
        else
        {
            $query="SELECT goods_id, name, img, price, new, hits, sale 
                        FROM goods
                            WHERE MATCH(name) AGAINST('{$search}*' IN BOOLEAN MODE) AND visible='1'";
            $res=mysql_query($query) or die(mysql_errno());
            
            if(mysql_num_rows($res)>0){
                while($row_search=mysql_fetch_assoc($res)){
                    $result_search[]=$row_search;
                }
            }
            else{
                $result_search['notfound']="<div class='error'>По Вашему запросу ничего не найдено</div>";
            }
        }
    }

    return $result_search;
}

//отдельный товар
function get_goods($goods_id){
    $query="SELECT * FROM goods WHERE goods_id = $goods_id AND visible='1'";
    $res=mysql_query($query);

   $goods = array();
   $goods = mysql_fetch_assoc($res);
    if($goods['img_slide']){
        $goods['img_slide'] = explode("|", $goods['img_slide']);
    }
    
    return $goods;
}

//получение массива страниц
function pages(){
    $query="SELECT page_id, title, text FROM pages ORDER BY position";
    $res=mysql_query($query) or die(mysql_error());
    
    $pages=array();
    while($row=mysql_fetch_assoc($res)){
        $pages[]=$row;
    }
    return $pages;
}

//получение отдельных страниц
function get_page($page_id){
    return get_query("SELECT title, page_id, text FROM pages WHERE page_id=$page_id");
}

//получение заголовков новостей
function get_title_news(){    
    return get_query("SELECT news_id, title, date FROM news ORDER BY date DESC LIMIT 2");
}

//получение отдельной новости
function get_news_text($news_id){
    return get_query("SELECT title, text, date FROM news WHERE news_id=$news_id");
}

/* ===Архив новостей=== */
function get_all_news($start_pos, $perpage){
    $query = "SELECT news_id, title, anons, date FROM news ORDER BY date DESC LIMIT $start_pos, $perpage";
    $res = mysql_query($query);
    
    $all_news = array();
    while($row = mysql_fetch_assoc($res)){
        $all_news[] = $row;
    }
    return $all_news;
}
/* ===Архив новостей=== */

//количество новостей на странице
function count_news(){
    $query = "SELECT COUNT(news_id) FROM news";
    $res=mysql_query($query);
    
    $count_news=mysql_fetch_row($res);
    return $count_news[0];
}



?>