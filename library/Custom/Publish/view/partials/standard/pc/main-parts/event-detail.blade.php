<?php foreach ($view->element->elements->getSubForms() as $el): ?>
    <?php
    $event = (Object)$el->getValues();
    ?>
    <section>
        <?php echo $view->partial('main-parts/heading.blade.php', array('heading' => $event->heading, 'level' => 1, 'element' => null)) ?>
        <div class="element comment">
            <p class="element-tx"><?php echo $event->comment ?></p>
        </div>

        <?php echo $view->partial('main-parts/multi-images.blade.php', array('element' => $el)) ?>

        <div class="element">
            <table class="element-table element-table1">
                <?php if ($event->start || $event->end): ?>
                    <tr>
                        <th>
                            開催期間
                        </th>
                        <td class="date">
                            <?php
                            if ($event->start) {
                                echo h($event->start);
                            }
                            if ($event->start && $event->end) {
                                echo '〜';
                            }
                            if ($event->end) {
                                echo h($event->end);
                            }
                            ?>
                        </td>
                    </tr>
                <?php endif ?>
                <?php if($event->structure_type): ?>
                <tr>
                    <th>
                        物件種目
                    </th>
                    <td>
                        <?php echo implode('/', $view->optionValues($el->structure_type->getValueOptions(), $event->structure_type)) ?>
                    </td>
                </tr>
                <?php endif ?>
                <?php if ($event->adress): ?>
                    <tr>
                        <th>
                            所在地
                        </th>
                        <td class="adress">
                            <?php echo h($event->adress) ?>
                        </td>
                    </tr>
                <?php endif ?>
                <?php if ($event->price): ?>
                    <tr>
                        <th>
                            価格
                        </th>
                        <td>
                            <?php echo h($event->price) ?>円
                        </td>
                    </tr>
                <?php endif ?>
                <?php if ($event->url): ?>
                    <tr>
                        <th>
                            物件ページURL
                        </th>
                        <td>
                            <a href="<?php echo h($event->url) ?>"><?php echo h($event->url) ?></a>
                        </td>
                    </tr>
                <?php endif ?>
            </table>
        </div>
        <?php if (is_object($view->page->getParentRow())): ?>
            <div class="element">
                <p class="element-tx tar">
                    <a href="<?php echo $view->hpLink($view->page->getParentRow()->link_id) ?>" class="btn-lv2">一覧に戻る</a>
                </p>
            </div>
        <?php endif; ?>
    </section>
<?php endforeach ?>
