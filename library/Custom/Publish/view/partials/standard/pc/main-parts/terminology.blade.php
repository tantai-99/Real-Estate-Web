<?php
$indexes = [];
foreach($view->element->getElementsBySyllabary() as $k => $v){
    if(!empty($v)){
        $indexes[$k] = str_replace('%', '', urlencode($k));
    }else{
        $indexes[$k] = null;
    }
}
?>
<div class="element element-firstletter">
    <section>
        <h3 class="element-firstletter-heading">用語の頭文字</h3>
        <ul>
            <?php foreach ($indexes as $index => $link): ?>
            <li>
              <?php if ($link): ?>
                  <a href="#<?php echo $link ?>"><?php echo $index ?>行</a>
              <?php else: ?>
                  <span><?php echo $index ?>行</span>
              <?php endif ?>
            </li>
            <?php endforeach ?>
        </ul>
    </section>
</div>

<?php foreach ($view->element->getElementsBySyllabary() as $index => $words): ?>
    <?php if (count($words) === 0) continue ?>
    <section>
        <?php echo $view->partial('main-parts/heading.blade.php', array('heading' => $index . '行', 'level' => 1, 'id' => str_replace('%', '', urlencode($index)), 'element' => null)) ?>
        <?php foreach ($words as $word): ?>
            <section>
                <?php echo $view->partial('main-parts/heading.blade.php', array('heading' => $word->getValue('word'), 'level' => 2, 'element' => null)) ?>
                <div class="element">
                    <?php if ($word->getValue('image')): ?>
                        <p class="element-img-right element-inline">
                            <img src="<?php echo $view->hpImage($word->getValue('image')) ?>"  alt="<?php echo h($word->getValue('image_title')) ?>"/>
                        </p>
                    <?php endif ?>
                    <p><?php echo $word->getValue('description') ?></p>
                </div>
            </section>
        <?php endforeach ?>
    </section>
<?php endforeach ?>
