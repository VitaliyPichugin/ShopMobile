<?php defined('ISHOP')or die('Acces denied');

//фильтрация входящих данных админки
function clear_admin($var){
    $var=mysql_real_escape_string($var);
    return $var;
}

//подсвечивание активного пункта меню
function active_url($str = 'view=pages'){
    $uri = $_SERVER['QUERY_STRING']; //получаем параметры
    if(!$uri) $uri="view=pages"; //параметр по умолчанию
    $uri = explode("&", $uri); //разбиваем строку по разделителям
    if(preg_match("#page=#", end($uri))) array_pop($uri);//если есть параметра пагинации(page) то мы его удаляем...удаление последнего индекса массива(последнего параметра в строке браузера)
    if(in_array($str, $uri)){
        //если в массиве параметров есть строка то это активный элемент меню
         return "class='nav-activ'";
    }
}
//подсвечивание активного пункта меню

/* ===Ресайз картинок=== */
function resize($target, $dest, $wmax, $hmax, $ext){
    /*
    $target - путь к оригинальному файлу
    $dest - путь сохранения обработанного файла
    $wmax - максимальная ширина
    $hmax - максимальная высота
    $ext - расширение файла
    */
    list($w_orig, $h_orig) = getimagesize($target);
    $ratio = $w_orig / $h_orig; // =1 - квадрат, <1 - альбомная, >1 - книжная

    if(($wmax / $hmax) > $ratio){
        $wmax = $hmax * $ratio;
    }else{
        $hmax = $wmax / $ratio;
    }
    
    $img = "";
    // imagecreatefromjpeg | imagecreatefromgif | imagecreatefrompng
    switch($ext){
        case("gif"):
            $img = imagecreatefromgif($target);
            break;
        case("png"):
            $img = imagecreatefrompng($target);
            break;
        default:
            $img = imagecreatefromjpeg($target);    
    }
    $newImg = imagecreatetruecolor($wmax, $hmax); // создаем оболочку для новой картинки
    
    if($ext == "png"){
        imagesavealpha($newImg, true); // сохранение альфа канала
        $transPng = imagecolorallocatealpha($newImg,0,0,0,127); // добавляем прозрачность
        imagefill($newImg, 0, 0, $transPng); // заливка  
    }
    
    imagecopyresampled($newImg, $img, 0, 0, 0, 0, $wmax, $hmax, $w_orig, $h_orig); // копируем и ресайзим изображение
    switch($ext){
        case("gif"):
            imagegif($newImg, $dest);
            break;
        case("png"):
            imagepng($newImg, $dest);
            break;
        default:
            imagejpeg($newImg, $dest);    
    }
    imagedestroy($newImg);
}
/* ===Ресайз картинок=== */

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

/* ===Страницы=== */
function pages(){
    $query = "SELECT page_id, title, position FROM pages ORDER BY position";
    $res = mysql_query($query);
    
    $pages = array();
    while($row = mysql_fetch_assoc($res)){
        $pages[] = $row;
    }
    return $pages;
}
/* ===Страницы=== */

/* ===Отдельная страница=== */
function get_page($page_id){
    $query = "SELECT * FROM pages WHERE page_id = $page_id";
    $res = mysql_query($query);
    
    $page = array();
    $page = mysql_fetch_assoc($res);
    
    return $page;
}
/* ===Отдельная страница=== */


/* ===Редактирование страницы=== */
function edit_page($page_id){
    
    $title = trim($_POST['title']);
    $keywords = trim($_POST['keywords']);
    $description = trim($_POST['description']);
    $position = (int)$_POST['position'];
    $text = trim($_POST['text']);
    
    if(empty($title)){
        // если нет названия
        $_SESSION['edit_page']['res'] = "<div class='error'>Должно быть название страницы!</div>";
        return false;
    }else{
        $title = clear_admin($title);
        $keywords = clear_admin($keywords);
        $description = clear_admin($description);
        $text = clear_admin($text);
        
        $query = "UPDATE pages SET
                    title = '$title',
                    keywords = '$keywords',
                    description = '$description',
                    position = $position,
                    text = '$text'
                        WHERE page_id = $page_id";
        $res = mysql_query($query) or die(mysql_error());
        
        if(mysql_affected_rows() > 0){
            $_SESSION['answer'] = "<div class='success'>Страница обновлена!</div>";
            return true;
        }else{
            $_SESSION['edit_page']['res'] = "<div class='error'>Ошибка или Вы ничего не меняли!</div>";
            return false;
        }
    }
}
/* ===Редактирование страницы=== */

//Добавление страниц
function add_page(){
    $title = trim($_POST['title']);
    $keywords = trim($_POST['keywords']);
    $description = trim($_POST['description']);
    $position = (int)$_POST['position'];
    $text = trim($_POST['text']);
    if(empty($title)){
    // если нет названия
    $_SESSION['add_page']['res'] = "<div class='error'>Должно быть название страницы!</div>";
    $_SESSION['add_page']['keywords']=$keywords;
    $_SESSION['add_page']['description']=$description;
    $_SESSION['add_page']['position']=$position;
    $_SESSION['add_page']['text']=$text;
    return false;
    }
    else{
        $title = clear_admin($title);
        $keywords = clear_admin($keywords);
        $description = clear_admin($description);
        $text = clear_admin($text);
        
        $query="INSERT INTO pages (title, keywords, description, position, text)
                    VALUES ('$title', '$keywords', '$description', $position, '$text')";
        $res=mysql_query($query);
        
        if(mysql_affected_rows() > 0){
            $_SESSION['answer'] = "<div class='success'>Страница добавлена!</div>";
            return true;
        }else{
            $_SESSION['add_page']['res'] = "<div class='error'>Ошибка при добавлении страници!</div>";
            return false;
        }
        
    }
   
}

//удаление страници
function del_page($page_id){
    $query="DELETE FROM pages WHERE page_id = $page_id";
    $res=mysql_query($query);
    
     if(mysql_affected_rows() > 0){
        $_SESSION['answer'] = "<div class='success'>Страница удалена!</div>";
        return true;
     }else{
        $_SESSION['answer'] = "<div class='error'>Ошибка удаления страници!</div>";
        return false;
     }
}

//количество новостей на странице
function count_news(){
    $query = "SELECT COUNT(news_id) FROM news";
    $res=mysql_query($query);
    
    $count_news=mysql_fetch_row($res);
    return $count_news[0];
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

//добавление новости
function add_news(){
    $title = trim($_POST['title']);
    $keywords = trim($_POST['keywords']);
    $description = trim($_POST['description']);
    $anons = trim($_POST['anons']);
    $text = trim($_POST['text']);
        if(empty($title)){
    // если нет названия
    $_SESSION['add_news']['res'] = "<div class='error'>Должно быть название новости!</div>";
    $_SESSION['add_news']['keywords']=$keywords;
    $_SESSION['add_news']['description']=$description;
    $_SESSION['add_news']['anons']=$anons;
    $_SESSION['add_news']['text']=$text;
    return false;
    }
    else{
        $title = clear_admin($title);
        $keywords = clear_admin($keywords);
        $description = clear_admin($description);
        $anons = clear_admin($anons);
        $text = clear_admin($text);
        $date = date('Y-m-d');
        
        $query="INSERT INTO news (title, keywords, description, date, anons, text)
                    VALUES ('$title', '$keywords', '$description', '$date', '$anons', '$text')";
        $res=mysql_query($query);
        
        if(mysql_affected_rows() > 0){
            $_SESSION['answer'] = "<div class='success'>Новость добавлена!</div>";
            return true;
        }else{
            $_SESSION['add_page']['res'] = "<div class='error'>Ошибка при добавлении новости!</div>";
            return false;
        }
        
    }
}
//отдельная новость
function get_news($news_id){
    $query = "SELECT * FROM news WHERE news_id = $news_id";
    $res = mysql_query($query);
    
    $news = array();
    $news = mysql_fetch_assoc($res);
    
    return $news;
}

//редактирование новости
function edit_news($news_id){
    $title = trim($_POST['title']);
    $keywords = trim($_POST['keywords']);
    $description = trim($_POST['description']);
    $date = trim($_POST['date']);
    $anons = trim($_POST['anons']);
    $text = trim($_POST['text']);
     if(empty($title)){
    // если нет названия
    $_SESSION['edit_news']['res'] = "<div class='error'>Должно быть название новости!</div>";
    return false;
    }
    else{
        $title = clear_admin($title);
        $keywords = clear_admin($keywords);
        $description = clear_admin($description);
        $date = clear_admin($date);
        $anons = clear_admin($anons);
        $text = clear_admin($text);
        
    $query = "UPDATE news SET
                title = '$title',
                keywords = '$keywords',
                description = '$description',
                anons = '$anons',
                text = '$text',
                date = '$date'
                    WHERE news_id = $news_id";
    $res = mysql_query($query) or die (mysql_error());
    if(mysql_affected_rows() > 0){
            $_SESSION['answer'] = "<div class='success'>Новость обновлена!</div>";
            return true;
        }else{
            $_SESSION['edit_news']['res'] = "<div class='error'>Ошибка или Вы ничего не меняли!</div>";
            return false;
        }
                    
}
}

/* ===Удаление новости=== */
function del_news($news_id){
    $query = "DELETE FROM news WHERE news_id = $news_id";
    $res = mysql_query($query);
    
    if(mysql_affected_rows() > 0){
        $_SESSION['answer'] = "<div class='success'>Новость удалена.</div>";
        return true;
    }else{
        $_SESSION['answer'] = "<div class='error'>Ошибка удаления новости!</div>";
        return false;
    }
}
/* ===Удаление новости=== */

/* ===Информеры - получение массива=== */
function informer(){
    $query = "SELECT * FROM links
                RIGHT JOIN informers ON
                    links.parent_informer = informers.informer_id
                        ORDER BY informer_position, links_position";
    $res = mysql_query($query) or die(mysql_query());
    
    $informers = array();
    $name = ''; // флаг имени информера
    while($row = mysql_fetch_assoc($res)){
        if($row['informer_name'] != $name){ // если такого информера в массиве еще нет
            $informers[$row['informer_id']][] = $row['informer_name']; // добавляем информер в массив
            $informers[$row['informer_id']]['position'] = $row['informer_position'];
            $informers[$row['informer_id']]['informer_id'] = $row['informer_id'];
            $name = $row['informer_name'];
        }
        if($informers[$row['parent_informer']])
        $informers[$row['parent_informer']]['sub'][$row['link_id']] = $row['link_name']; // заносим страницы в информер
    }
    return $informers;
}
/* ===Информеры - получение массива=== */

//массив информеров для списка
function get_informers(){
    $query = "SELECT * FROM informers";
    $res = mysql_query($query);
    
    $informers = array();
    while($row = mysql_fetch_assoc($res)){
        $informers[] = $row;
    }
    return $informers;
}

//добавление страници информера
function add_link(){
    $link_name = trim($_POST['link_name']);
    $keywords = trim($_POST['keywords']);
    $description = trim($_POST['description']);
    $links_position = (int)$_POST['links_position'];
    $parent_informer = (int)$_POST['parent_informer'];
    $text = trim($_POST['text']);
    //если поле имя пустое
    if(empty($link_name)){
        $_SESSION['add_link']['res']="<div class='error'>Должно быть название страници информера!</div>";
        $_SESSION['add_link']['keywords']=$keywords;
        $_SESSION['add_link']['description']=$description;
        $_SESSION['add_link']['links_position']=$links_position;
        //$_SESSION['add_link']['parent_informer']=$parent_informer;
        $_SESSION['add_link']['text']=$text;
        return false;
    }else{
        $link_name = clear_admin($link_name);
        $keywords = clear_admin($keywords);
        $description = clear_admin($description);
        $text = clear_admin($text);
        
        $query = "INSERT INTO links (link_name, keywords, description, parent_informer, links_position, text)
                    VALUES ('$link_name', '$keywords', '$description', $parent_informer, $links_position, '$text')";
        $res = mysql_query($query) or die(mysql_error());
        
        if(mysql_affected_rows() > 0){
            $_SESSION['answer'] = "<div class='success'>Информер добавлен.</div>";
            return true;
        }else{
            $_SESSION['add_link']['res'] = "<div class='error'>Ошибка добавления информера!</div>";
            return false;
        }
    }
}

//получение данных страници информера
function get_link($link_id){
    $query="SELECT * FROM links WHERE link_id=$link_id";
    $res=mysql_query($query);
    
    $link=array();
    $link=mysql_fetch_assoc($res);
    return $link;
}

//редактирование новости 
function edit_link($link_id){
    $link_name = trim($_POST['link_name']);
    $keywords = trim($_POST['keywords']);
    $description = trim($_POST['description']);
    $links_position = (int)$_POST['links_position'];
    $parent_informer = (int)$_POST['parent_informer'];
    $text = trim($_POST['text']);
     if(empty($link_name)){
    // если нет названия
    $_SESSION['edit_link']['res'] = "<div class='error'>Должно быть название информера!</div>";
    return false;
    }
    else{
        $link_name = clear_admin($link_name);
        $keywords = clear_admin($keywords);
        $description = clear_admin($description);
        $text = clear_admin($text);
        
    $query = "UPDATE links SET
                link_name = '$link_name',
                keywords = '$keywords',
                description = '$description',
                parent_informer = $parent_informer,
                links_position = $links_position,   
                text = '$text'
                    WHERE link_id = $link_id";
    $res = mysql_query($query) or die (mysql_error());
    if(mysql_affected_rows() > 0){
            $_SESSION['answer'] = "<div class='success'>Информер обновлен!</div>";
            return true;
        }else{
            $_SESSION['edit_link']['res'] = "<div class='error'>Ошибка или Вы ничего не меняли!</div>";
            return false;
        }
                    
}
}

//удаление информера
function del_link($link_id){
    $query = "DELETE FROM links WHERE link_id = $link_id";
    $res = mysql_query($query);
    
    if(mysql_affected_rows() > 0){
        $_SESSION['answer'] = "<div class='success'>Информер удален.</div>";
        return true;
    }else{
        $_SESSION['answer'] = "<div class='error'>Ошибка удаления информера!</div>";
        return false;
    }
}

//добавление информера
function add_informer(){
    $informer_name = clear_admin(trim($_POST['informer_name']));
    $informer_position = (int)$_POST['informer_position'];
    
    if(empty($informer_name)){
        $_SESSION['add_informer']['res'] = "<div class='error'>У информера должно быть имя!</div>";
        return false;
    }
    else{
        $query="INSERT INTO informers (informer_name, informer_position)
                 VALUES ('$informer_name', $informer_position)";
        $res=mysql_query($query);
        
        if(mysql_affected_rows() > 0){
            $_SESSION['answer'] = "<div class='success'>Информер добавлен!</div>";
            return true;
        }else{
            $_SESSION['add_informer']['res'] = "<div class='error'>Ошибка при лобавлении информера!</div>";
            return false;
        }
    
    }
   
}

//удаление информера
function del_informer($informer_id){
    //удаление страницы информера
    mysql_query("DELETE FROM links WHERE parent_informer=$informer_id");
    
    //удаляем сам информер
    mysql_query("DELETE FROM informers WHERE informer_id=$informer_id");
    
     if(mysql_affected_rows() > 0){
        $_SESSION['answer'] = "<div class='success'>Информер удален!</div>";
    }else{
        $_SESSION['answer'] = "<div class='error'>Ошибка!</div>";
    }
}

//получение данных информера
function get_informer($informer_id){
    $query = "SELECT * FROM informers WHERE informer_id = $informer_id";
    $res = mysql_query($query);
    
    $informers = array();
    $informers = mysql_fetch_assoc($res);
    return $informers;
}

//изменение информера
function edit_informer($informer_id){
    $informer_name = clear_admin(trim($_POST['informer_name']));
    $informer_position = (int)$_POST['informer_position'];
    
    if(empty($informer_name)){
        $_SESSION['edit_informer']['res'] = "<div class='error'>У информера должно быть имя!</div>";
        return false;
    }
    else{
        $query="UPDATE informers SET 
        informer_name = '$informer_name', 
        informer_position = $informer_position
                 WHERE informer_id = $informer_id";
        $res=mysql_query($query);
        
        if(mysql_affected_rows() > 0){
            $_SESSION['answer'] = "<div class='success'>Информер изменен!</div>";
            return true;
        }else{
            $_SESSION['add_informer']['res'] = "<div class='error'>Ошибка при изминении информера!</div>";
            return false;
        }
    
    }
}
//добавление категории
function add_brand(){
    $brand_name = clear_admin(trim($_POST['brand_name']));
    $parent_id = (int)$_POST['parent_id'];
    
    if(empty($brand_name)){
        $_SESSION['add_brand']['res'] = "<div class='error'>У категории должно быть имя!</div>";
        return false;
    }else{
        $query = "SELECT brand_id, brand_name FROM brands WHERE brand_name = '$brand_name' AND parent_id = $parent_id";
        $res = mysql_query($query);
        
        if(mysql_num_rows($res)>0){
            $_SESSION['add_brand']['res'] = "<div class='error'>Категория с таким названием уже существует</div>";
            return false;
        }else{
            $query = "INSERT INTO brands (brand_name, parent_id)
                        VALUES ('$brand_name', $parent_id)";
            $res = mysql_query($query);
            if(mysql_affected_rows() > 0){
            $_SESSION['answer'] = "<div class='success'>Категория добавлена!</div>";
            return true;
        }else{
            $_SESSION['add_brand']['res'] = "<div class='error'>Ошибка при добавлении категории!</div>";
            return false;
        }
        }
        
    }
}

//редактирование категории
function edit_brand($brand_id){
    $brand_name = clear_admin(trim($_POST['brand_name']));
    $parent_id = (int)$_POST['parent_id'];
    
    if(empty($brand_name)){
        $_SESSION['edit_brand']['res'] = "<div class='error'>У категории должно быть имя!</div>";
        return false;
    }
     $query = "SELECT brand_id, brand_name FROM brands WHERE brand_name = '$brand_name' AND parent_id = $parent_id";
     $res = mysql_query($query);
      if(mysql_num_rows($res)>0){
            $_SESSION['edit_brand']['res'] = "<div class='error'>Категория с таким названием уже существует</div>";
            return false;
    }else{
        $query = "UPDATE brands SET
                   brand_name =  '$brand_name',
                   parent_id = $parent_id
                    WHERE brand_id = $brand_id";
        $res = mysql_query($query);
          if(mysql_affected_rows() > 0){
            $_SESSION['answer'] = "<div class='success'>Категория обновлена!</div>";
            return true;
        }else{
            $_SESSION['edit_brand']['res'] = "<div class='error'>Ошибка при обновлении категории!</div>";
            return false;
        }
    }
}

/* ===Удаление категории=== */
function del_brand($brand_id){
    $query = "SELECT COUNT(*) FROM brands WHERE parent_id = $brand_id";
    $res = mysql_query($query);
    $row = mysql_fetch_row($res);
    if($row[0]){
        $_SESSION['answer'] = "<div class='error'>Категория имеет подкатегории! Удалите сначала их или переместите в другую категорию.</div>";
    }else{
        mysql_query("DELETE FROM goods WHERE goods_brandid = $brand_id");
        mysql_query("DELETE FROM brands WHERE brand_id = $brand_id");
        $_SESSION['answer'] = "<div class='error'>Категория удалена.</div>";
    }
}
/* ===Удаление категории=== */

//Получение количества товаров для навигации
 function count_rows($category)
{
     $query="(SELECT COUNT(goods_id) as count_rows
                FROM `goods` 
                    WHERE goods_brandid=$category)
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

//Получение массива товаров по категориям
function products($category, $star_pos, $perpage){
    $query="(SELECT goods_id, name, img, anons, price, new, hits, sale, date, visible 
                FROM `goods` 
                    WHERE goods_brandid=$category)
             UNION       
            (SELECT goods_id, name, img, anons, price, new, hits, sale, date, visible 
                FROM `goods` 
                    WHERE goods_brandid IN
                    (
                        SELECT brand_id FROM brands WHERE parent_id=$category
                    )
                    ) LIMIT $star_pos, $perpage";
    $res=mysql_query($query) or die(mysql_error());
    
    $products=array();
    while($row=mysql_fetch_assoc($res)){
        $products[]=$row;
    }
    return $products;
}
//Получение массива товаров по категориям

//редактирование товара
function edit_product($id){
    $name = trim($_POST['name']);
    $price = round(floatval(preg_replace("#,#", ".", $_POST['price'])),2);
    $keywords = trim($_POST['keywords']);
    $description = trim($_POST['description']);
    $goods_brandid = (int)$_POST['category'];
    $anons = trim($_POST['anons']);
    $content = trim($_POST['content']);
    $new = (int)$_POST['new'];
    $hits = (int)$_POST['hits'];
    $sale = (int)$_POST['sale'];
    $visible = (int)$_POST['visible'];
    $date = date("Y-m-d");
    
    if(empty($name)){
        $_SESSION['edit_product']['res'] = "<div class='error'>Имя не должно быть пустым.</div>";
        return false;
    }else{
        $name = clear_admin($name);
        $keywords = clear_admin($keywords); 
        $description = clear_admin($description);
        $anons = clear_admin($anons);
        $content = clear_admin($content);
        
        $query = "UPDATE goods SET
                    name = '$name',
                    keywords = '$keywords',
                    description = '$description',
                    goods_brandid = '$goods_brandid',
                    anons = '$anons',
                    content = '$content',
                    new = '$new',
                    hits = '$hits',
                    sale = '$sale',
                    visible = '$visible',
                    price = '$price'
                        WHERE goods_id = $id";
                        
        $res = mysql_query($query) or die (mysql_error());
        
        $types = array("image/gif","image/png", "image/jpeg", "image/pjpeg", "image/x-png");//массив допустимых расширений(майнтипы)
         if($_FILES['baseimg']['name']){
            $baseimgExt = strtolower(preg_replace("#.+\.([a-z]+)$#i", "$1", $_FILES['baseimg']['name']));//расширение картинуи
            $baseimgName = "{$id}.{$baseimgExt}";//новое имя картинки
            $baseimgTmpName = $_FILES['baseimg']['tmp_name']; //временное имя картинки
            $baseimgSize = $_FILES['baseimg']['size'];//размер
            $baseimgType = $_FILES['baseimg']['type'];//тип файла
            $baseimgError = $_FILES['baseimg']['error'];//0 - все ок, иначе ошибка
            
            $error = "";
            if(!in_array($baseimgType, $types)) $error .="Допустимое расширение .gif, .jpg, .png <br/>";
            if($baseimgSize > SIZE) $error .="Допустимый размер 1 мб <br/>";
            if($baseimgError) $error .="Произошла ошибка при загрузке файла <br/>";
            
            if(!empty($error)) $_SESSION['answer'] = "<div class='error'>Ошибка при загрузке картинки товара! <br/> {$error}</div>";
            
            //если нет ошибок
            if(empty($error)){
                if(@move_uploaded_file($baseimgTmpName, "../userfiles/product_img/tmp/$baseimgName")){
                    resize("../userfiles/product_img/tmp/$baseimgName", "../userfiles/product_img/baseimg/$baseimgName", 120, 185, $baseimgExt); 
                    @unlink("../userfiles/product_img/tmp/$baseimgName");
                    mysql_query("UPDATE goods SET img = '$baseimgName' WHERE goods_id = $id");
                }else{
                    $_SESSION['answer'] .= "<div class='error'>Не удалось переместить загруженую картинку.</div>";
                }
            }
            //базовая картинка
            $_SESSION['answer'] .= "<div class='success'>Товар обновлен</div>";
            return true;
         }
    }
}
//редактирование товара

//добавление товара
function add_product(){
    $name = trim($_POST['name']);
    $price = round(floatval(preg_replace("#,#", ".", $_POST['price'])),2);
    $keywords = trim($_POST['keywords']);
    $description = trim($_POST['description']);
    $goods_brandid = (int)$_POST['category'];
    $anons = trim($_POST['anons']);
    $content = trim($_POST['content']);
    $new = (int)$_POST['new'];
    $hits = (int)$_POST['hits'];
    $sale = (int)$_POST['sale'];
    $visible = (int)$_POST['visible'];
    $date = date("Y-m-d");
    
    if(empty($name)){
        $_SESSION['add_product']['res'] = "<div class='error'>Имя не должно быть пустым.</div>";
        $_SESSION['add_product']['price'] = $price;
        $_SESSION['add_product']['keywords'] = $keywords;
        $_SESSION['add_product']['description'] = $description;
        $_SESSION['add_product']['anons'] = $anons;
        $_SESSION['add_product']['content'] = $content;
        return false;
    }else{
        $name = clear_admin($name);
        $keywords = clear_admin($keywords); 
        $description = clear_admin($description);
        $anons = clear_admin($anons);
        $content = clear_admin($content);
        
        $query = "INSERT INTO goods (name, keywords, description, goods_brandid, anons, content, hits, new, sale, price, visible, date )
                    VALUES('$name', '$keywords', '$description', $goods_brandid, '$anons', '$content', '$hits', '$new', '$sale', $price, '$visible', '$date')";
        $res = mysql_query($query) or die(mysql_errno());
        if(mysql_affected_rows()>0){
        
        $id = mysql_insert_id();//ид сохраненного товара
        $types = array("image/gif","image/png", "image/jpeg", "image/pjpeg", "image/x-png");//массив допустимых расширений(майнтипы)
        //базовая картинка
        if($_FILES['baseimg']['name']){
            $baseimgExt = strtolower(preg_replace("#.+\.([a-z]+)$#i", "$1", $_FILES['baseimg']['name']));//расширение картинуи
            $baseimgName = "{$id}.{$baseimgExt}";//новое имя картинки
            $baseimgTmpName = $_FILES['baseimg']['tmp_name']; //временное имя картинки
            $baseimgSize = $_FILES['baseimg']['size'];//размер
            $baseimgType = $_FILES['baseimg']['type'];//тип файла
            $baseimgError = $_FILES['baseimg']['error'];//0 - все ок, иначе ошибка
            
            $error = "";
            
            if(!in_array($baseimgType, $types)) $error .="Допустимое расширение .gif, .jpg, .png <br/>";
            if($baseimgSize > SIZE) $error .="Допустимый размер 1 мб <br/>";
            if($baseimgError) $error .="Произошла ошибка при загрузке файла <br/>";
            
            if(!empty($error)) $_SESSION['answer'] = "<div class='error'>Ошибка при загрузке картинки товара! <br/> {$error}</div>";
            
            //если нет ошибок
            if(empty($error)){
                if(@move_uploaded_file($baseimgTmpName, "../userfiles/product_img/tmp/$baseimgName")){
                    resize("../userfiles/product_img/tmp/$baseimgName", "../userfiles/product_img/baseimg/$baseimgName", 120, 185, $baseimgExt); 
                    @unlink("../userfiles/product_img/tmp/$baseimgName");
                    mysql_query("UPDATE goods SET img = '$baseimgName' WHERE goods_id = $id");
                }else{
                    $_SESSION['answer'] .= "<div class='error'>Не удалось переместить загруженую картинку.</div>";
                }
            }
        }
        //базовая картинка
        //-------------------------------------------------------------//
        //блок картинки галереии
        if($_FILES["galleryimg"]["name"][0]){
            for($i=0; $i<count($_FILES["galleryimg"]["name"]); $i++){
                $error="";
                if($_FILES["galleryimg"]["name"][$i]){
                        //если есть файл
                        $GimgExt = strtolower(preg_replace("#.+\.([a-z]+)$#i", "$1", $_FILES['galleryimg']['name'][$i]));//расширение картинуи
                        $GimgName = "{$id}_{$i}.{$GimgExt}";//новое имя картинки
                        $GimgTmpName = $_FILES['galleryimg']['tmp_name'][$i]; //временное имя картинки
                        $GimgSize = $_FILES['galleryimg']['size'][$i];//размер
                        $GimgType = $_FILES['galleryimg']['type'][$i];//тип файла
                        $GimgError = $_FILES['galleryimg']['error'][$i];//0 - все ок, иначе ошибка
                        
                        if(!in_array($GimgType, $types)){
                            $error .="Допустимое расширение .gif, .jpg, .png <br/>";
                            $_SESSION['answer'] .= "<div class='error'>Ошибка при загрузке картинки {$_FILES["galleryimg"]["name"][$i]} <br/> {$error}</div>";
                            continue;
                        } 
                        if($GimgSize > SIZE){
                            $error .="Допустимый размер 1 мб <br/>";
                            $_SESSION['answer'] .= "<div class='error'>Ошибка при загрузке картинки размер - {$_FILES["galleryimg"]["size"][$i]} <br/> {$error}</div>";
                            continue;
                        } 
                        if($GimgError){
                            $error .="Произошла ошибка при загрузке файла <br/>";
                            $_SESSION['answer'] .= "<div class='error'>Ошибка при загрузке картинки  - {$_FILES["galleryimg"]["size"][$i]} <br/> {$error}</div>";
                            continue;
                        } 
                        //если нет ошибок
                        if(empty($error)){
                            if(@move_uploaded_file($GimgTmpName, "../userfiles/product_img/photos/$GimgName")){
                                resize("../userfiles/product_img/photos/$GimgName", "../userfiles/product_img/thumbs/$GimgName", 45, 45, $GimgExt); 
                                //@unlink("../userfiles/product_img/photos/$GimgName");
                                //mysql_query("UPDATE goods SET img = '$GimgName' WHERE goods_id = $id");
                                if(!isset($galleryfiles)){
                                    $galleryfiles=$GimgName;
                                }else{
                                    $galleryfiles .= "|{$GimgName}";
                                }
                            }else{
                                $_SESSION['answer'] .= "<div class='error'>Не удалось переместить загруженую картинку.</div>";
                            }
                    }
                }
            }
            if(isset($galleryfiles)){
                mysql_query("UPDATE goods SET img_slide = '$galleryfiles' WHERE goods_id = $id");
            }
        }
        //блок картинки галереии
            $_SESSION['answer'] .= "<div class='success'>Товар добавлен.</div>";
            return true;
        }else{
            $_SESSION['add_product']['res'] = "<div class='error'>Ошибка при добавлении товара.</div>";
            return false;
        }
    }
}
//добавление товара

//получение данных товара
function get_product($goods_id){
    $query = "SELECT * FROM goods WHERE goods_id = $goods_id";
    $res = mysql_query($query);
    
    $products = array();
    $products = mysql_fetch_assoc($res);
    
    return $products;
}
//получение данных товара

//Ajaxupload - загрузка картинок галереи
function upload_gallery_img($id){
    $uploaddir = '../userfiles/product_img/photos/';
    $types = array("image/gif","image/png", "image/jpeg", "image/pjpeg", "image/x-png");
    $file = $_FILES['userfile']['name'];
    $ext = strtolower(preg_replace("#.+\.([a-z]+)$#i", "$1", $file));//расширение картинуи
    
    if($_FILES['userfile']['size'] >SIZE ){
        $res = array("answer" => "Ошибка максимальный вес файла 1мб");
        exit(json_encode($res));
    }
    if($_FILES['userfile']['error'] == 1  ){
        $res = array("answer" => "Ошибка возможно файл слишком большой");
        exit(json_encode($res));
    }
    if(!in_array($_FILES['userfile']['type'], $types)){
        $res = array("answer" => "Допустимое расширение .gif, .jpg, .png <br/>");
        exit(json_encode($res));
    } 
    
    $query ="SELECT img_slide FROM goods WHERE goods_id = $id";
    $res = mysql_query($query);
    $row = mysql_fetch_assoc($res);
    if($row['img_slide']){
        //если есть картинки в галереии
        $images = explode("|", $row['img_slide']);
        $lastimg = end($images);
        //получаем номер последней картинки
        $lastnum = preg_replace("#\d+_(\d)\.\w+#", "$1", $lastimg); //1_1.ext (jpg...)
        $lastnum +=1;
        $newimg = "{$id}_{$lastnum}.{$ext}"; //имя новой картинки
        $images = "{$row['img_slide']}|{$newimg}"; //строка для записи в БД
    }else{
        $newimg = "{$id}_0.{$ext}"; //имя новой картинки 
        $images = $newimg; //строка для записи в БД
    }
    
    $uploadfile = $uploaddir.$newimg; //путь загрузки картинки
    if(@move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)){
        resize($uploadfile, "../userfiles/product_img/thumbs/$newimg", 45, 45, $ext);
        mysql_query("UPDATE goods SET img_slide = '$images' WHERE goods_id = $id");
        $res = array("answer" => "OK", "file" => $newimg);
        exit(json_encode($res)); 
    }
}
//Ajaxupload - загрузка картинок галереи

//удаление картинок
function del_img(){
    $goods_id = (int)$_POST['goods_id'];
    $img = clear_admin($_POST['img']);
    $rel = (int)$_POST['rel'];
    
    if(!$rel){
        //если удаляется основная картинка
        mysql_query("UPDATE goods SET img = 'no_image.jpg' WHERE goods_id = $goods_id");
        if(mysql_affected_rows()>0){
            return '<input type="file" name="baseimg" />';
        }else{
            return false;
        }
    }else{
        //если удаляется картинка галереии
        $res = mysql_query("SELECT img_slide FROM goods WHERE goods_id = $goods_id");
        $row = mysql_fetch_assoc($res);
        // получаем картинки в массив
        $images = explode("|", $row['img_slide']);
        foreach($images as $item){
            //пропускаем удаляемую картинку
            if($item == $img) continue;
            //формирум строку с картинками
            if(!isset($galleryfiles)){
                $galleryfiles=$item;
            }else{
                $galleryfiles .= "|$item";
            }
           
        }
         mysql_query("UPDATE goods SET img_slide = '$galleryfiles' WHERE goods_id = $goods_id");
         
         if(mysql_affected_rows()>0){
            return true;
        }else{
            return false;
        }
    }
}
//удаление картинок

//получение количества не обработаных заказов
function count_new_orders(){
    $query = "SELECT COUNT(*) AS count FROM orders WHERE status = '0'";
    $res = mysql_query($query);
    $row = mysql_fetch_assoc($res);
    return $row['count'];
}
//получение количества не обработаных заказов

/* ===Получение необработанные заказы=== */
function orders($status, $start_pos, $perpage){
    $query = "SELECT orders.order_id, orders.date, orders.status, customers.name
                FROM orders
                LEFT JOIN customers
                    ON customers.customer_id = orders.customer_id".$status." ORDER BY date DESC LIMIT $start_pos, $perpage";
                    //exit($query);
    $res = mysql_query($query);
    $orders = array();
    while($row = mysql_fetch_assoc($res)){
        $orders[] = $row;
    }
    return $orders;
}
/* ===Получение необработанные заказы=== */

//просмотр заказа 
function show_order($id){
    //zakaz_tovar: name, price, quantity
    //orders: date, prim
    //customers: name, email, phone, address
    //dostavka: name
    $query = "SELECT zakaz_tovar.name, zakaz_tovar.price, zakaz_tovar.quantity,
                orders.date, orders.prim, orders.status,
                customers.name AS customer, customers.email, customers.phone, customers.address,
                dostavka.name AS sposob
                    FROM zakaz_tovar
              LEFT JOIN orders
                ON zakaz_tovar.orders_id = orders.order_id
              LEFT JOIN customers
                ON customers.customer_id = orders.customer_id
              LEFT JOIN dostavka
                ON dostavka.dostavka_id = orders.dostavka_id
                    WHERE zakaz_tovar.orders_id = $id";
    $res = mysql_query($query);
    $show_order = array();
    while($row=mysql_fetch_assoc($res)){
        $show_order[] = $row;
    }
    return $show_order;
    //get_query($query);
}
//просмотр заказа 

//функция поддтверждения заказа
function confirm_order($order_id){
    $query = "UPDATE orders SET status = '1' WHERE order_id=$order_id";
    mysql_query($query);
    if(mysql_affected_rows()>0){
        return true;
    }else{
        return false;
    }
}
//функция поддтверждения заказа

//Получение количества товаров для навигации
 function count_orders()
{
     $query="SELECT COUNT(order_id) AS count_order FROM `orders`";

    $res=mysql_query($query) or die(mysql_error());

     while($row=mysql_fetch_assoc($res)){
        if($row['count_order']) $count_orders=$row['count_order'];
    }
    return $count_orders;
}
//Получение количества товаров для навигации

//количество пользователей
 function count_users()
{
    $query = "SELECT COUNT(customer_id) FROM customers WHERE login IS NOT NULL";
    $res=mysql_query($query);
    
    $count_news=mysql_fetch_row($res);
    return $count_news[0];
}
//количество пользователей

//получение списка пользователей
function get_users($start_pos, $perpage){
    $query = "SELECT customer_id, name, login, email, name_role
            FROM customers
            LEFT JOIN roles
                ON customers.id_role = roles.id_role
            WHERE login IS NOT NULL LIMIT $start_pos, $perpage";
    $res = mysql_query($query);
    $users = array();
    while($row = mysql_fetch_assoc($res)){
        $users[]=$row;
    }
    return $users;
}

//получение списка пользователей

//получение списка полей пользователей
function get_roles(){
    $query = "SELECT id_role, name_role FROM roles";
    $res = mysql_query($query);
    $roles=array();
    while($row=mysql_fetch_assoc($res)){
        $roles[]=$row;
    }
    return $roles;
}
//получение списка полей пользователей

//добавление пользователя
function add_user(){
    
    /*$error = ''; // флаг проверки пустых полей
    
    $login = trim($_POST['login']);
    $password = trim($_POST['password']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $id_role = (int)$_POST['id_role'];
    
    if(empty($login)) $error .= '<li>Не указан логин</li>';
    if(empty($password)) $error .= '<li>Не указан пароль</li>';
    if(empty($name)) $error .= '<li>Не указано ФИО</li>';
    if(empty($email)) $error .= '<li>Не указан Email</li>';
    
    if(empty($error)){
        // если все поля заполнены
        // проверяем нет ли такого юзера в БД
        $query = "SELECT customer_id FROM customers WHERE login = '" .clear($login). "' LIMIT 1";
        $res = mysql_query($query) or die(mysql_error());
        $row = mysql_num_rows($res); // 1 - такой юзер есть, 0 - нет
        if($row){
            // если такой логин уже есть
            $_SESSION['add_user']['res'] = "<div class='error'>Пользователь с таким логином уже зарегистрирован на сайте. Введите другой логин.</div>";
            $_SESSION['add_user']['name'] = $name;
            $_SESSION['add_user']['email'] = $email;
            $_SESSION['add_user']['password'] = $password;
            return false;
        }else{
            // если все ок - регистрируем
            $login = clear($login);
            $name = clear($name);
            $email = clear($email);
            $pass = md5($password);
            
            $query = "INSERT INTO customers (name, email, login, password, id_role)
                        VALUES ('$name', '$email', '$login', '$pass', $id_role)";
            $res = mysql_query($query) or die(mysql_error());
            if(mysql_affected_rows() > 0){
                // если запись добавлена
                $_SESSION['answer'] = "<div class='success'>Пользователь добавлен.</div>";
                return true;
            }else{
                $_SESSION['add_user']['res'] = "<div class='error'>Ошибка!</div>";
                $_SESSION['add_user']['login'] = $login;
                $_SESSION['add_user']['name'] = $name;
                $_SESSION['add_user']['email'] = $email;
                $_SESSION['add_user']['password'] = $password;
                return false;
            }
        }
    }else{
        // если не заполнены обязательные поля
        $_SESSION['add_user']['res'] = "<div class='error'>Не заполнены обязательные поля: <ul> $error </ul></div>";
        $_SESSION['add_user']['login'] = $login;
        $_SESSION['add_user']['name'] = $name;
        $_SESSION['add_user']['email'] = $email;
        $_SESSION['add_user']['password'] = $password;
        return false;
    }*/
     $error='';//флаг проверки пустых полей
    $login=trim($_POST['login']);
    $passwords=trim($_POST['password']);
    $name=trim($_POST['name']);
    $email=trim($_POST['email']);
    $id_role = (int)($_POST['id_role']);
    
    if(empty($login)) $error .='<li>Не указан логин</li>';
    if(empty($passwords)) $error .='<li>Не указан пароль</li>';
    if(empty($name)) $error .='<li>Не указан ФИО</li>';
    if(empty($email)) $error .='<li>Не указан емаил</li>';
    
     if(empty($error)){
        //если все обязательные поля заполнены
        $query="SELECT customer_id FROM customers WHERE login = '" .clear($login). "' LIMIT 1";
        $res = mysql_query($query) or die(mysql_error());
        $row = mysql_num_rows($res); //1 такой юзер уже есть 0 - такого нет
        if($row){
            //если такой логин уже есть
        $_SESSION['add_user']['res']="<div class='error'>Пользователь с таким именем уже существует!!! </div>";
        $_SESSION['add_user']['name']=$name;
        $_SESSION['add_user']['email']=$email;
        $_SESSION['add_user']['password']=$passwords;
        return false;

        }
        else{
            //если все ок - регистрируем
            $login=clear($login);
            $name=clear($name);
            $email=clear($email);
            $pass = md5($passwords);
            /*$query = "INSERT INTO customers (name, email, login, password, id_role)
                        VALUES ('$name', '$email', '$login', '$pass' $id_role)";*/
                        $query = "INSERT INTO customers (name, email, login, password, id_role)
                        VALUES ('$name', '$email', '$login', '$pass', $id_role)";
            $res=mysql_query($query) or die(mysql_error());
            if(mysql_affected_rows()>0){
                //если запись добавлена
                 $_SESSION['answer']="<div class='success'>Успешно добавлен мразь </div>";
                return true;
            }
            else{
                 //если что то пошло не так
                 $_SESSION['add_user']['res']="<div class='error'>Ошибка </div>";
                $_SESSION['add_user']['login']=$login;
                 $_SESSION['add_user']['name']=$name;
                $_SESSION['add_user']['email']=$email;
                 $_SESSION['add_user']['password']=$passwords;
                 return false;
            }
        }
        
    }
    else{
        //если не заполнены необходимые поля
        $_SESSION['add_user']['res']="<div class='error'>Не заполнены обязательные поля: <ul>$error</ul></div>";
        $_SESSION['add_user']['login']=$login;
        $_SESSION['add_user']['name']=$name;
        $_SESSION['add_user']['email']=$email;
        $_SESSION['add_user']['phone']=$phone;
        $_SESSION['add_user']['password']=$passwords;
        return false;

    }

}
//добавление пользователя

//получение данных пользователя
function get_user($user_id){
    $query = "SELECT name, email, phone, address, login, id_role FROM customers WHERE customer_id = $user_id";
    $res = mysql_query($query);
    $user = array();
    $user = mysql_fetch_assoc($res);
    return $user;
}

//редактирование пользователя
function edit_user($user_id){
    foreach($_POST as $key => $val){
        if($key == "x" || $key == "y") continue;
        if($key == "password"){
            $val=trim($val);
            if(!empty($val)){
                $val = md5($val);
            }else{
                continue;
            }
        }else{
            $val=clear($val);
        }
        $data[$key]=$val;
    }
    
    $files = array_keys($data);
    $values = array_values($data);
    
    for($i=0; $i<count($files); $i++){
        $str .= "{$files[$i]} = '{$values[$i]}', ";
    }
    $str = substr($str, 0, -2);
    $query = "UPDATE customers SET {$str} WHERE customer_id = $user_id";
    $res = mysql_query($query);
    if(mysql_affected_rows()>0){
        $_SESSION['answer'] = "<div class='success'>Данные обновлены </div>";
        if($user_id == $_SESSION['auth']['user_id'] ){
            $_SESSION['auth']['admin']=htmlspecialchars($_POST['name']);
        }
        return true;
    }else{
        $_SESSION['edit_user']['res'] = "<div class='error'>Ошибка!!! </div>";
        return false;
    }
}

//удаление пользователь
function del_user($user_id){
    if($_SESSION['auth']['user_id']==$user_id){
         $_SESSION['answer'] = "<div class='error'>Ты ахуел бля ты не можеш удалить сам себя </div>";
         //return false;
    }else{
        $query = "DELETE FROM customers WHERE customer_id = $user_id";
        mysql_query($query);
        if(mysql_affected_rows()>0){
             $_SESSION['answer'] = "<div class='success'>Удален юзер </div>";
        }else{
             $_SESSION['answer'] = "<div class='error'>ошибочка </div>";
        }
    }
}

?>