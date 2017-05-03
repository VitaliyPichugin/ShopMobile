<?php defined('ISHOP') or die ('Access denied'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript" src="<?=TEMPLATE?>js/functions.js"></script>
<script type="text/javascript" src="<?=TEMPLATE?>js/jquery.js"></script>
<script type="text/javascript" src="<?=TEMPLATE?>js/jquery_accordion.js"></script>
<script type="text/javascript" src="<?=TEMPLATE?>js/jquery.cookie.js"></script>
<script type="text/javascript" src="<?=TEMPLATE?>js/workscripts.js"></script>
<!-- Fancybox -->
<script type="text/javascript" src="./fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
<script type="text/javascript" src="./fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<link rel="stylesheet" type="text/css" href="./fancybox/jquery.fancybox-1.3.4.css" media="screen" />
<!-- Fancybox -->

<!--[if lt IE 9]>
<script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js"></script>
<![endif]-->
<link rel="stylesheet" type="text/css" href="<?=TEMPLATE?>css/style.css" />
<title><?=TITLE?></title>
</head>
<body>
<script type="text/javascript">
    $(document).ready(function(){
    if($.cookie("display")==null){
        $.cookie("display", "grid");
    }
    $(".grid_list").click(function(){
        var display=$(this).attr("id");//получаем значение переключателя вида
        display=(display=="grid")? "grid":"list"; //допустимое значение
        if(display==$.cookie("display")){
            //ЕСЛИ ЗНАЧЕНИЕ СОВПАДАЕТ С КУКОЙ ТО НИЧЕГО НЕ ДЕЛАЕМ
            return false;
        }
        else{
            //иначе установим куку с новым значение вида
            $.cookie("display", display);
            window.location="?<?=$_SERVER['QUERY_STRING'] ?>";
            return false;
        }
        //alert(display);
        return false;
    });
});
</script>
<script>
	$(document).ready(function(){
		var openItem = false;
		if($.cookie("openItem") && $.cookie("openItem") != 'false'){
			var openItem = parseInt($.cookie("openItem"));
		}
		$("#accordion").accordion({
			active: openItem,
			collapsible: true,
			header: 'h3',
			autoHeight: false
		});
		$("#accordion h3").click(function(){
			$.cookie("openItem", $("#accordion").accordion("option", "active"));
		});
		$("#accordion > li").click(function(){
			$.cookie("openItem", null);
			var link = $(this).find('a').attr('href');
			window.location = link;
		});
});
</script>
    <div class="main">
        <div class="header">
      <a href="/"><img class="logo" src="<?=TEMPLATE?>images/logo.jpg" alt="Интернет магазин сотовых телефонов" /></a>
        <img class="slogan" src="<?=TEMPLATE?>images/slogan.jpg" alt="Интернет магазин сотовых телефонов" />
        <div class="head-contact">
          <p><strong>Телефон:</strong><br />
  <span>8 (800) 700-00-01</span></p>
            <p><strong>Режим работы:</strong><br />
  Будние дни: с 9:00 до 18:00 <br />
  Суббота, Воскресенье - выходные  </p>
        </div>
        <form method="get" >
        <input type="hidden" name="view" value="search" />
          <ul class="search-head">
            <li>
                <input type="text" name="search" id="quickquery" placeholder="Что вы хотите купить?" />
                <script type="text/javascript">
                //<![CDATA[
                placeholderSetup('quickquery');
                //]]>
                </script>
            </li>
            <li>
                <input type="image" class="serch-btn" src="<?=TEMPLATE?>images/searc-btn.jpg" />
            </li>
          </ul>
        </form>
    </div>
    <ul class="menu">
      <li><a href="<?=PATH?>">Главная</a></li>
      <?php if($pages): ?>
      <?php foreach($pages as $item): ?>
        <li><a href="?view=page&amp;page_id=<?=$item['page_id']?>"><?=$item['title']?></a></li>
      <?php endforeach; ?>
      <?php endif; ?>
    </ul>