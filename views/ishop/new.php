<?php defined('ISHOP') or die ('Access denied');?>
<div class="catalog-index">
		<h1>Новинки</h1>
        <?php if($eyestopers): ?>
        <?php foreach($eyestopers as $eyestoper): ?>
        <div class="product-index">
			<h2><a href="?view=product&goods_id=<?=$eyestoper['goods_id'] ?>"><?=$eyestoper['name'] ?></a></h2>
			<a href="?view=product&goods_id=<?=$eyestoper['goods_id'] ?>"><img src="<?=PRODUCT_IMG?><?=$eyestoper['img']?>" alt="" /></a>
			<p>Цена:  <span><?=$eyestoper['price'] ?></span></p>
			<a href="?view=addtocart&goods_id=<?=$eyestoper['goods_id'] ?>"><img class="addtocard-index" src="<?=TEMPLATE?>images/addcard-index.jpg" alt="Добавить в карзину" /></a>
            </div>
        <?php endforeach ?>
            <?php else: ?>
                <p>Здесь товаров нет</p>
        <?php endif ?>
        
  </div>