<?php defined('ISHOP') or die ('Access denied'); ?>
<div class="catalog-index">
<h1>Выбор по параметрам</h1>
<?php if($products['notfound'])://если нет совпадение ?>
<?php echo $products['notfound']; ?>
<?php else: //если есть результаты выбора ?>
<?php foreach($products as $product): ?>
    <!--Табличный вид продуктов-->
     <div class="product-table">
      <h2><a href="?view=product&goods_id=<?=$product['goods_id'] ?>"><?=$product['name'] ?></a></h2>
          <div class="product-table-img-main">
              <div class="product-table-img">
                  <a href="?view=product&goods_id=<?=$product['goods_id'] ?>"><img src="<?=PRODUCT_IMG?><?=$product['img'] ?>" width="64" alt="" /></a>
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
<?php endforeach; ?>
<?php endif; // конец условия переключателя видов  ?>

</div>