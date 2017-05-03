<?php defined('ISHOP') or die ('Access denied');?>
<div class="catalog-index">
    <div class="kroshka">
    <?php if(count($brand_name)>1): //если подкатегория - (слайдер, моноблок...) ?>
        <a href="<?=PATH?>">Мобильные телефоны</a> / <a href="?view=cat&category=<?=$brand_name[0]['brand_id']?>"><?=$brand_name[0]['brand_name']?></a> / <span><?=$brand_name[1]['brand_name']?></span>
    <?php elseif(count($brand_name)==1): //если не лрчерняя категория ?>
         <a href="<?=PATH?>">Мобильные телефоны</a> / <span><?=$brand_name[0]['brand_name']?></span>
    <?php endif; ?>
    </div>
    <!--    .kroshka -->
    <div class="vid-sort">
		Вид: 
			<a href="#" id="grid" class="grid_list"><img src="<?=TEMPLATE?>images/vid-tabl.gif" alt="табличный вид" /></a> 
			<a href="#" id="list" class="grid_list"><img src="<?=TEMPLATE?>images/vid-line.gif" alt="линейный вид" /></a>  
			&nbsp;&nbsp;           
		Сортировать по:&nbsp;
        <a id="param_order" class="sort-top"><?=$order ?></a>
        <div class="sort-wrap">
        <?php foreach($order_p as $key=>$value): ?>
        <?php if($value[0]==$order) continue; ?>
           <a href="?view=cat&category=<?=$category?>&order=<?=$key?>&page=<?=$page?>" class="sort-bot"><?= $value[0] ?></a>
            <!-- <a href="#" class="sort-bot">дате добавления</a>
            <a href="#" class="sort-bot">названию от Я-А</a> -->
            <?php endforeach; ?>
        </div>
	</div> <!-- .vid-sort -->
    <!--    .vid-sort -->
   <?php if($products): // если получены товары категории ?>
   <?php if($pages_count > 1) pagination($page, $pages_count); ?>
   <?php foreach($products as $product): ?>
   <?php if(!isset($_COOKIE['display']) OR $_COOKIE['display'] == 'grid'): // если вид - сетка ?>
    <!--Табличный вид продуктов-->
     <div class="product-table">
      <h2><a href="?view=product&goods_id=<?=$product['goods_id'] ?>"><?=$product['name'] ?></a></h2>
          <div class="product-table-img-main">
              <div class="product-table-img">
                  <a href="?view=product&goods_id=<?=$product['goods_id'] ?>"><img src="<?=PRODUCT_IMG?><?=$product['img'] ?>" alt="" /></a>
                   <div>
         <?php if($product['hits']) echo '<img src="'.TEMPLATE.'images/ico-cat-lider.png" alt="лидеры продаж" />'; ?>
         <?php if($product['new']) echo '<img src="'.TEMPLATE.'images/ico-cat-new.png" alt="новинка" />'; ?>
         <?php if($product['sale']) echo '<img src="'.TEMPLATE.'images/ico-cat-sale.png" alt="распродажа" />'; ?>
          </div>
              </div>
          </div>
      <p class="cat-table-more"><a href="?view=product&goods_id=<?=$product['goods_id'] ?>">подробнее...</a></p>
      <p>Цена :  <span><?=$product['price'] ?></span></p>
      
      <a href="?view=addtocart&goods_id=<?=$product['goods_id'] ?>"><img class="addtocard-index" src="<?=TEMPLATE?>/images/addcard-table.jpg" alt="Добавить в корзину" /></a>
</div> <!-- .product-table -->
<!-- Табличный вид продуктов -->
<?php else: // если линейный вид ?>
<!-- Линейный вид продуктов -->
    <div class="product-line">
        <div class="product-line-img">
          <a href="?view=product&goods_id=<?=$product['goods_id'] ?>"><img src="<?=PRODUCT_IMG?><?=$product['img'] ?>" alt="" width="48px" /></a>
        </div>
        <div class="product-line-price">
          <p>Цена : <span><?=$product['price'] ?></span>uah</p>
          <a href="?view=addtocart&goods_id=<?=$product['goods_id'] ?>"><img class="addtocard-index" src="<?=TEMPLATE?>images/addcard-table.jpg" alt="Добавить в карзину" /></a>
         <!--Иконки -->
         <div>
         <?php if($product['hits']) echo '<img src="'.TEMPLATE.'images/ico-line-lider.jpg" alt="лидеры продаж" />'; ?>
         <?php if($product['new']) echo '<img src="'.TEMPLATE.'images/ico-line-new.jpg" alt="новинка" />'; ?>
         <?php if($product['sale']) echo '<img src="'.TEMPLATE.'images/ico-line-sale.jpg" alt="распродажа" />'; ?>
          </div>
          <!--Иконки -->
          <p class="cat-line-more"><a href="?view=product&goods_id=<?=$product['goods_id'] ?>">подробнее...</a></p>
        </div>
        <div class="product-line-opis">
          <h2><a href="?view=product&goods_id=<?=$product['goods_id'] ?>"><?=$product['name'] ?></a></h2>
          <p><?=$product['anons'] ?></p>
       </div>
    </div>
<!-- Линейный вид продуктов -->
<?php endif; // конец условия переключателя видов  ?>
<?php endforeach; ?>
<div class="clr"></div>
<?php if($pages_count > 1) pagination($page, $pages_count); ?>
<?php else: ?>
    <p>Здесь товаров пока нет!</p>
<?php endif; ?>	
<a name="nav"></a>			
</div> <!-- .catalog-index -->