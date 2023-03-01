<?php
    $topPage = get_class($page) == "Library\Custom\Hp\Page\Top";
    $serviceTop = getInstanceUser('cms')->checkHasTopOriginal();
    // Create info list in top page
    $createList = function($subForms,$part_type_code) {
        $data = array();
        foreach ($subForms as $name => $area) {
            $areaName = $area->getElementBelongsTo();
            $parts = $area->getPartsByColumn();
            if(!isset($parts[1])) continue;
            if(!isset($parts[1][0])) continue;
            $thisClass = get_class($parts[1][0]);
            $partName = '[parts][' . $parts[1][0]->getName() . ']';
            foreach ($parts[1][0]->getElements() as $name => $element) {
                $element->setBelongsTo($areaName.$partName);
            }
            if ($thisClass != $part_type_code) continue;
            array_push($data, $area);
        }
        return $data;
    };

    // Check display list koma in top page
    $boolKoma = function($subForms) {
        $koma = false;
        foreach ($subForms as $name => $area) {
            $cols = $area->getPartsByColumn();
            foreach ($cols as $key => $col) {
                foreach ($col as $key => $parts) {
                    if ($parts->getElement('display_flg')->getValue()) {
                        $koma = true;
                    }
                }
            }
        }
        return $koma;
    };
    $isSeo = true;
	if (isset($view->isSeo)) {
	    $isSeo = $view->isSeo;
	}
?>
<div class="section sortable-item-container<?php if($element->getName()=='side'):?> side-content<?php endif;?>" id="section-<?php echo $element->getName()?>" data-section="<?php echo $element->getName()?>" data-page-type="<?php echo $page->getType();?>">
	<?php if(strstr(get_class($page), "Library\Custom\Hp\Page\Form") == false) : ?>
    <?php if (!$serviceTop || (!$topPage && $serviceTop)) :?>
	<h2><?php echo h($element->getTitle())?><?php echo $view->toolTip('page_'.$element->getName().'_contents')?><?php if ($element->getTitle() == 'メインコンテンツ' && $isSeo):?><a href="javascript:void(0)" onclick="window.open('<?php echo route('default.seo-advice.content') ?>', '', 'width=720,height=820,scrollbars=1');" class="i-s-seo">SEOアドバイス</a><?php endif ;?></h2>
    <?php endif; ?>
	<?php endif;?>
	<?php if ($element->getName() == 'main'):?>

		<?php $subForms = $element->getSubForms();
        if ($topPage && $serviceTop):
            $mainParts = App::make(\App\Repositories\HpMainParts\HpMainPartsRepositoryInterface::class);
            $infoList = $createList($subForms, 'Library\Custom\Hp\Page\Parts\InfoList');
            $komaList = $createList($subForms, 'Library\Custom\Hp\Page\Parts\EstateKoma'); ?>
            <h2>お知らせ設定</h2>
            <?php foreach ($infoList as $name => $area) :?>
            <div class="page-area page-area-notification-setting sortable-item column<?php echo $area->getColumnCount()?>" data-name="<?php echo $area->getName()?>">
            <?php $area->form('column_type_code')?>
            <?php $area->form('sort')?>
                <div class="errors is-hide"></div>
                <?php $cols = $area->getPartsByColumn()?>
                <?php foreach ($cols as $key => $col):?>
                    <?php foreach ($col as $key => $parts):?>
                        <?php //echo $this->partial($parts->getTemplate(), array('element'=>$parts))?>
                        @include($parts->getTemplate(), [
			                'element' => $parts
			            ])
                    <?php endforeach;?>
                <?php endforeach;?>
            </div>
            <?php endforeach;
            if (!empty($komaList)) :?>
                <h2>特集の物件コマ表示制御</h2>
                <?php foreach ($komaList as $name => $area) {?>
                <div class="page-area sortable-item column<?php echo $area->getColumnCount()?>" data-name="<?php echo $area->getName()?>">
                <?php $area->form('column_type_code')?>
                <?php $area->form('sort')?>
                    <div class="errors is-hide"></div>
                    <?php $cols = $area->getPartsByColumn()?>
                    <?php foreach ($cols as $key => $col):?>
                        <?php foreach ($col as $key => $parts):?>
                            <?php if ($parts->getElement('display_flg')->getValue()) {
                                //echo $this->partial($parts->getTemplate(), array('element'=>$parts, 'page'=>$this->page));
                                ?>
                                @include($parts->getTemplate(), [
					                'element' => $parts,
					                'page'=>$page
					            ])
                            <?php } ?>
                        <?php endforeach;?>
                    <?php endforeach;?>
                </div>
                <?php }?>
            <?php endif; ?>
        <?php else :?>
		<?php foreach ($subForms as $name => $area):?>
		<?php $areaName = $area->getElementBelongsTo(); ?>
		<div class="page-area sortable-item column<?php echo $area->getColumnCount()?>" data-name="<?php echo $area->getName()?>">
			<?php $area->form('column_type_code')?>
			<?php $area->form('sort')?>
			<div class="errors is-hide"></div>

			<?php $cols = $area->getPartsByColumn()?>
			<?php foreach ($cols as $key => $col):?>
				<?php if ($area->getColumnCount() > 1):?>
				<div class="col sortable-item-container">
				<?php endif;?>

				<?php foreach ($col as $key => $parts):?>
					<?php $partsName = '[parts][' .$parts->getName().']';
					//echo $this->partial($parts->getTemplate(), array('element'=>$parts))
					if ($parts instanceof Library\Custom\Hp\Page\Parts\ForCorporationReview || $parts instanceof Library\Custom\Hp\Page\Parts\ForOwnerReview || $parts instanceof Library\Custom\Hp\Page\Parts\CustomervoiceDetail || $parts instanceof Library\Custom\Hp\Page\Parts\EventDetail || $parts instanceof Library\Custom\Hp\Page\Parts\Terminology) {
						$parts->setElementsBelongsTo($areaName . $partsName, null);
					}
					?>
					@include($parts->getTemplate(), [
		                'element' => $parts
		            ])
				<?php endforeach;?>

				<?php if ($area->getColumnCount() > 1):?>
				</div>
				<?php endif;?>
			<?php endforeach;?>

			<?php if ($area->getColumnCount() > 1):?>
			<div class="col-action">
				<a class="i-e-up up-btn" href="javascript:void(0);">上へ移動</a>
				<a class="i-e-down down-btn" href="javascript:void(0);">下へ移動</a>
				<a class="i-e-delete delete-btn" href="javascript:void(0);">削除</a>
			</div>
			<?php endif;?>

		</div>
		<?php endforeach;?>
		<?php endif; ?>

        <?php if (!$serviceTop || (!$topPage && $serviceTop)): ?>
		<div class="page-element-add">
			<h3>エリア挿入<small>表示例を選択してください。</small></h3>
			<ul class="select-column">
				<?php foreach (App::make(\App\Repositories\HpArea\HpAreaRepositoryInterface::class)::getColumnTypes() as $col => $label):?>
				<li class="<?php if($col == 1):?>is-selected<?php endif;?>" data-column-type-code="<?php echo $col?>">
					<span class="column<?php echo $col?>"></span>
					<label><?php echo $label?></label>
				</li>
				<?php endforeach;?>
			</ul>
			<div class="btn-area">
				<a class="btn-t-blue" href="javascript:;">追加</a>
			</div>
		</div>
        <?php endif; ?>

	<?php else:?>

		<?php
		//Library\Custom\Hp\Page\FormDocument 資料請求
		//Library\Custom\Hp\Page\FormContact お問い合わせ
		//Library\Custom\Hp\Page\FormAssessment 査定依頼
		//お問い合わせ形については、サイドコンテンツを表示させない
		?>
		<?php if(strstr(get_class($page), "Library\Custom\Hp\Page\Form") == false) : ?>

		<?php $subForms = $element->getSubForms()?>
		<?php foreach ($subForms as $name => $parts):?>
			<?php //echo $this->partial($parts->getTemplate(), array('element'=>$parts))?>
			@include($parts->getTemplate(), [
                'element' => $parts
            ])
		<?php endforeach;?>

		<div class="select-element">
			<h3>要素挿入<?php echo $view->toolTip('page_insert_parts')?></h3>
			<div class="select-element-body">
				<select>
					<option value="element1">選択してください</option>
				</select>
				<div class="btn-area">
					<a class="btn-t-blue size-s" href="javascript:;">追加</a>
				</div>
			</div>
		</div>
		<?php endif;?>
	<?php endif;?>
</div>
