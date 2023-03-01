<?php foreach ($view->element->elements->getSubForms() as $city): ?>
    <?php
    $categoryName = $city->category->getValueOption($city->getValue('category'));
    $institutions = $city->elements->getValues();
    ?>
    <section>
        <?php if ($city->getValue('category')) echo $view->partial('main-parts/heading.blade.php', array('heading' => $categoryName, 'level' => 1, 'element' => null)) ?>

        <?php foreach ($institutions['elements'] as $institution): ?>
            <div class="element element-tximg5">
                <?php if ($institution['image']): ?>
                    <p class="element-left">
                        <img src="<?php echo $view->hpImage($institution['image']) ?>" alt="<?php echo h($institution['image_title']); ?>">
                    </p>
                <?php else: ?>
                    <p class="element-left">
                        <img src="<?php $view->src('imgs/img_nowprinting_l.png') ;?>" alt="now printing">
                    </p>
                <?php endif ?>

                <div class="element-right">
                    <table class="element-table element-table5">
                        <tr>
                            <th colspan="2" class="element-table-heading">
                                <?php echo h($institution['name']) ?>
                            </th>
                        </tr>
                        <?php if ($institution['walking_minutes']): ?>
                            <tr>
                                <th>徒歩分数</th>
                                <td><?php echo h($institution['walking_minutes']) ?></td>
                            </tr>
                        <?php endif ?>
                        <?php if ($institution['description']): ?>
                            <tr>
                                <th>説明</th>
                                <td><?php echo $institution['description'] ?></td>
                            </tr>
                        <?php endif ?>
                        <?php if ($institution['link']): ?>
                            <tr>
                                <th>リンク</th>
                                <td>
                                    <a href="<?php echo h($institution['link']) ?>" target="_blank"><?php echo h($institution['link']) ?></a>
                                </td>
                            </tr>
                        <?php endif ?>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
<?php endforeach; ?>
