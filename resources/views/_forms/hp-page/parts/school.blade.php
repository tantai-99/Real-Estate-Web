<div class="page-element sortable-item element-list" data-name="<?php echo $element->getName()?>" data-type="<?php echo $element->getType()?>" data-type-name="<?php echo $element->getTypeName()?>" data-is-unique="<?php echo $element->isUnique()?>" data-has-element="1">
	@include('_forms.hp-page.parts.partials.header', ['element' => $element])
	<div class="page-element-body sortable-item-container">
		@include('_forms.hp-page.parts.partials.heading', ['element' => $element])
		
		<?php $subForms = $element->getSubForm('elements')->getSubForms()?>
		<?php foreach ($subForms as $key => $form):?>
		<div class="item-set sortable-item added-item sub-parts" data-is-unique="<?php echo $form->isUnique()?>" data-type="<?php echo $form->getType()?>" data-is-preset="<?php echo $form->isPreset()?>" data-title="<?php echo $form->getTitle()?>" data-name="<?php echo $form->getName()?>">
			<?php $form->simpleHidden('type')?>
			<?php $form->simpleHidden('sort')?>
			
			<dl class="item-set-header">
				<dt><span>カテゴリ</span></dt>
				<dd>
					<?php echo $form->simpleText('category')?><span class="input-count"></span>
					<div class="errors"></div>
				</dd>
				<dd class="action">
					<a class="i-e-up up-btn" href="javascript:void(0);">上へ移動</a>
					<a class="i-e-down down-btn" href="javascript:void(0);">下へ移動</a>
					<a class="i-e-delete delete-btn" href="javascript:void(0);">削除</a>
				</dd>
			</dl>
			<div class="item-set-list2 sub-elements sortable-item-container">
				
				<?php $subElements = $form->getSubForm('elements')->getSubForms()?>
				<?php foreach ($subElements as $key2 => $element):?>
				<div class="item sortable-item added-item" data-name="<?php echo $key2?>" data-type="<?php echo $element->getType()?>" data-is-preset="<?php echo $element->isPreset()?>" data-title="<?php echo $element->getTitle()?>" data-is-unique="<?php echo $element->isUnique()?>">
					<?php $element->simpleHidden('type');?>
					<?php $element->simpleHidden('sort');?>
					
					<dl>
						<dt><span>学校名</span></dt>
						<dd>
							<?php echo $element->simpleText('name')?><span class="input-count"></span>
							<div class="errors"></div>
						</dd>
						<dd class="action">
							<a class="i-e-up up-btn" href="javascript:void(0);">上へ移動</a>
							<a class="i-e-down down-btn" href="javascript:void(0);">下へ移動</a>
							<a class="i-e-delete delete-btn" href="javascript:void(0);">削除</a>
						</dd>
					</dl>
					<dl>
						<dt style="vertical-align: top;"><span>通学区域</span></dt>
						<dd>
							<?php echo $element->simpleText('school_zoning')?>
							<div class="input-count"></div>
							<div class="errors"></div>
						</dd>
					</dl>
				</div>
				<?php endforeach;?>
				
				<div class="item-add">
					<a href="javascript:void(0);" class="btn-t-blue size-s">施設を追加</a>
				</div>
				
			</div>
		</div>
		<?php endforeach;?>
		
		<div class="item-add">
			<a href="javascript:void(0);" class="btn-t-blue size-s">エリアを追加</a>
		</div>
	</div>
</div>