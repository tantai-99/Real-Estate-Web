<div class="page-element sortable-item element-glossary" data-name="<?php echo $element->getName()?>" data-type="<?php echo $element->getType()?>" data-type-name="<?php echo $element->getTypeName()?>" data-is-unique="<?php echo $element->isUnique()?>" data-has-element="1" data-max-element-count="<?php echo $element->getMaxElementCount()?>">
	@include('_forms.hp-page.parts.partials.header', ['element' => $element])
	<div class="page-element-body sortable-item-container">
		@include('_forms.hp-page.parts.partials.heading', ['element' => $element])
		
		<?php $subForms = $element->getSubForm('elements')->getSubForms()?>
		<?php foreach ($subForms as $key => $form):?>
		<div class="item-set sortable-item added-item" data-is-unique="<?php echo $form->isUnique()?>" data-type="<?php echo $form->getType()?>" data-is-preset="<?php echo $form->isPreset()?>" data-title="<?php echo $form->getTitle()?>" data-name="<?php echo $form->getName()?>">
			<?php $form->simpleHidden('type')?>
			<?php $form->simpleHidden('sort')?>
			
			<dl class="item-set-header is-require">
				<dt>
					<span>ファイル名</span>
				</dt>
				<dd>
					<?php echo $form->simpleText('file_title')?><span class="input-count"></span>
					<div class="errors"></div>
				</dd>
				<dd class="action">
					<a href="javascript:void(0);" class="i-e-up up-btn">上へ移動</a>
					<a href="javascript:void(0);" class="i-e-down down-btn">下へ移動</a>
					<a href="javascript:void(0);" class="i-e-delete delete-btn">削除</a>
				</dd>
			</dl>
			
			<div class="item-set-list">
				<dl>
					<dt><span>ファイル</span></dt>
					<dd>
						<div class="f-file-upload">
							<?php $form->form('file')?>
							<div class="up-img">
								<div class="up-btn">
									<input type="file" name="file" class="ignore-attrs">
								</div>
								<div class="up-area is-hide">または、ファイルをドロップしてください。</div>
								<small>pdf,xls,xlsx,doc,docx,ppt,pptx(2MBまで）</small>
							</div>
							<div class="up-preview">
								<a class="i-e-delete is-hide" href="javascript:;"></a>
							</div>
						</div>
						<div class="errors"></div>
					</dd>
				</dl>
			</div>
			
		
		</div>
		<?php endforeach;?>
		
		<div class="item-add">
			<a class="btn-t-blue size-s" href="javascript:void(0);">追加</a>
		</div>
		
	</div>
</div>