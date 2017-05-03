<?php defined('ISHOP') or die('Access denied'); ?>

<div class="kroshka">
	<a href="<?=PATH?>">Главная</a> / <a href="<?=PATH?>?view=archive">Все новости</a> / <span><?=$get_news['title']?></span>
</div>

<div class="content-txt">
    <?php if($get_news): ?>
        <h1><?=$get_news['title']?></h1>
        <span class="news_date"><?=$get_news['date']?></span>
        <br /><br />
        <?=$get_news['text']?>
    <?php else: ?>
        <p>Такой новости нет!</p>
    <?php endif; ?>
</div> <!-- .content-txt -->