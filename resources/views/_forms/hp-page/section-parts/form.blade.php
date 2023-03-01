<script type="text/javascript">
$(function () {
	'use strict';

	var $container = $('.element-contact');
    
	app.page.sortUpdate($container.find('table'));
	$container.on('change', '.option-type input', function () {
		var $optionContainer = $(this).closest('.option-type');
		var type = $optionContainer.find('input:checked').val();
		$optionContainer.next().toggleClass('is-hide', type === 'text' || type === 'textarea');
	});

	$container.on('change', '.option-status input', function () {
		var $optionContainer = $(this).closest('tr');
		var type = $optionContainer.find('input:checked').val();
		var $choice = $optionContainer.next();
		if ($choice.length) {
			$choice.toggleClass('is-hide', type === '3');
			$choice.find('input:first').prop('checked', true).trigger('change');
			if (type === '3') {
				// アコーディオンが閉じている時
				$optionContainer.find('th').attr('rowspan', '1');
			} else {
				// アコーディオンが開いている時
				$optionContainer.find('th').attr('rowspan', '2');
			}
		}
	});

	$container.find('.option-status').each(function () {
		var $optionContainer = $(this).closest('tr');
		var type = $optionContainer.find('input:checked').val();
		var $choice = $optionContainer.next();
		if ($choice.length) {
			$choice.toggleClass('is-hide', type === '3');
			$choice.find('input:first').trigger('change');
			if (type === '3') {
				// アコーディオンが閉じている時
				$optionContainer.find('th').attr('rowspan', '1');
			} else {
				// アコーディオンが開いている時
				$optionContainer.find('th').attr('rowspan', '2');
			}
		}
	});

	<?php if ( getInstanceUser('cms')->getProfile()->cms_plan != config('constants.cms_plan.CMS_PLAN_ADVANCE') ): ?>
		// 自動返信を無効にする
		$( '#form-autoreply_flg'						).prop( 'checked'	, false		) ;
		$( '#autoreply'	).find( '.watch-input-count'	).prop( 'disabled'	, true		) ;
		$( '#autoreply'	).find( '.watch-input-count'	).prop( 'value'		, ''		) ;
		$( '#autoreply'	).find( 'tr'					).removeClass(	'is-require'	) ;
		$( '#autoreply'	).find( 'span'					).addClass(		'is-disable'	) ;
		$( '#autoreply'	).addClass('is-disable'			) ;
		$( '#autoreply'	).hide() ;
		$( '#autoreply'	).find('input[type=hidden]').remove();
	<?php endif ; ?>
	
	// テスト送信
	var $mailTest = $('.send-test-mail');
	var $to = $('.test-mail-to input');
	$mailTest.on('click', function () {
		var to = [];
		$to.each(function () {
			var val = $(this).val();
			if (val) {
				to.push(val);
			}
		});
		
		if (!to.length) {
			app.modal.alert('', '宛先メールアドレスが設定されていません。');
			return;
		}
		
		app.api('/page/api-test-mail', {_token:'<?php echo csrf_token(false)?>', to: to}, function (data) {
			var message;
			if (data.error) {
				message = data.error;
			}
			else {
				message = 'テストメールを送信しました。';
			}
			app.modal.alert('', message);
		});
	});
});
</script>
<div class="section">
	<h2>メール設定</h2>
	<table class="form-basic">
		<?php if (\App\Repositories\HpPage\HpPageRepository::TYPE_FORM_CONTACT == $element->getPage()->page_type_code ||
			\App\Repositories\HpPage\HpPageRepository::TYPE_FORM_DOCUMENT == $element->getPage()->page_type_code ||
			\App\Repositories\HpPage\HpPageRepository::TYPE_FORM_ASSESSMENT == $element->getPage()->page_type_code  ||
			\App\Repositories\HpPage\HpPageRepository::TYPE_FORM_REQUEST_LIVINGLEASE == $element->getPage()->page_type_code ||
			\App\Repositories\HpPage\HpPageRepository::TYPE_FORM_REQUEST_OFFICELEASE == $element->getPage()->page_type_code ||
			\App\Repositories\HpPage\HpPageRepository::TYPE_FORM_REQUEST_LIVINGBUY == $element->getPage()->page_type_code ||
			\App\Repositories\HpPage\HpPageRepository::TYPE_FORM_REQUEST_OFFICEBUY == $element->getPage()->page_type_code):?>

			<?php for ($i = 1, $l = $element->getMailToCount(); $i <= $l; $i++):?>
			<?php $name = 'notification_to_'.$i?>
			<tr class="is-require">
				<?php if ($i==1):?>
				<th rowspan="<?php echo $l?>"><span>宛先メールアドレス<?php echo $view->toolTip('form_notification_to')?></span></th>
				<?php endif;?>
				<td class="test-mail-to">
					<span><?php $element->form($name) ?></span>
					<span class="input-count"></span>
					<div class="errors"></div>
				</td>
			</tr>
			<?php endfor;?>
		<?php else:?>
			<tr>
				<th><span>宛先メールアドレス<?php echo $view->toolTip('estate_form_notification_to')?></span></th>
				<td class="test-mail-to">
					<span>アットホーム会員情報のメールアドレス２（消費者向けアットホームサイト「反響のお知らせ」受信用）に登録されているメールアドレスになります。</span>
				</td>
			</tr>
		<?php endif;?>

		<tr class="is-require">
			<th><span>メールの件名<?php echo $view->toolTip('form_notification_subject')?></span></th>
			<td>
				<span><?php $element->form('notification_subject') ?></span>
				<span class="input-count">0/30</span>
				<div class="errors"></div>
			</td>
		</tr>
	</table>

		<?php if (\App\Repositories\HpPage\HpPageRepository::TYPE_FORM_CONTACT == $element->getPage()->page_type_code ||
			\App\Repositories\HpPage\HpPageRepository::TYPE_FORM_DOCUMENT == $element->getPage()->page_type_code ||
			\App\Repositories\HpPage\HpPageRepository::TYPE_FORM_ASSESSMENT == $element->getPage()->page_type_code || 
			\App\Repositories\HpPage\HpPageRepository::TYPE_FORM_REQUEST_LIVINGLEASE == $element->getPage()->page_type_code ||
			\App\Repositories\HpPage\HpPageRepository::TYPE_FORM_REQUEST_OFFICELEASE == $element->getPage()->page_type_code ||
			\App\Repositories\HpPage\HpPageRepository::TYPE_FORM_REQUEST_LIVINGBUY == $element->getPage()->page_type_code ||
			\App\Repositories\HpPage\HpPageRepository::TYPE_FORM_REQUEST_OFFICEBUY == $element->getPage()->page_type_code):?>
			
	<div class="btns mt20">
		<a class="btn-t-blue send-test-mail" href="javascript:;">テスト送信</a>
	</div>
	<?php endif;?>
</div>

<div id="autoreply" class="section">
	<h2>自動返信メールの設定</h2>
	<div class="mb10"><label><?php $element->form('autoreply_flg')?><?php echo $element->getElement('autoreply_flg')->getLabel()?></label><span><?php echo $view->toolTip('form_autoreply_flg')?></span></div>
	<table class="form-basic">
		<tr class="is-require">
			<th><span><?php echo $element->getElement('autoreply_from')->getLabel()?><?php echo $view->toolTip('form_autoreply_from')?></span></th>
			<td>
				<span><?php $element->simpleText('autoreply_from') ?></span>
				<span class="input-count">0/30</span>
				<div class="errors"></div>
			</td>
		</tr>
		
		<tr class="">
			<th><span><?php echo $element->getElement('autoreply_sender')->getLabel()?><?php echo $view->toolTip('form_autoreply_sender')?></span></th>
			<td>
				<span><?php $element->simpleText('autoreply_sender') ?></span>
				<span class="input-count">0/30</span>
				<div class="errors"></div>
			</td>
		</tr>
		
		<tr class="is-require">
			<th><span><?php echo $element->getElement('autoreply_subject')->getLabel()?><?php echo $view->toolTip('form_autoreply_subject')?></span></th>
			<td>
				<span><?php $element->simpleText('autoreply_subject') ?></span>
				<span class="input-count">0/30</span>
				<div class="errors"></div>
			</td>
		</tr>
		
		<tr class="is-require">
			<th><span><?php echo $element->getElement('autoreply_body')->getLabel()?><?php echo $view->toolTip('form_autoreply_body')?></span></th>
			<td>
				<span>
                    <?php $element->simpleText('autoreply_body') ?>
                    <span class="input-count"></span>
                </span>
				<div class="errors"></div>
			</td>
		</tr>
	</table>
</div>

<div class="section">
	<h2>フォーム設定</h2>
	<div class="page-element element-contact">
		<div class="page-element-header">
			<h3><?php echo $element->getFormTitle()?>フォーム<?php echo $view->toolTip('page_form_parts_'.$element->getTypeName())?></h3>
		</div>
		<div class="page-element-body">
			<table class="form-table sortable-item-container">
				<?php $subForms = $element->getSortedFormElements()?>
				<?php foreach ($subForms as $name => $form):?>
				<tbody class="sortable-item">
					<?php $form->simpleHidden('sort')?>
					<tr>
						<th<?php if($form instanceof Library\Custom\Hp\Page\SectionParts\Form\Element\Multi && $form->getFreeChoiceCount()):?> rowspan="2"<?php endif;?>>
							<?php if($form->getElement('item_title')):?>
							<?php $form->simpleText('item_title')?>
							<div class="errors"></div>
							<?php else:?>
							<?php echo $form->getTitle()?>
							<?php endif;?>
						</th>
						<td>
							<ul class="optional-contact option-status">
								<?php 
								$requiredTypeRadio = explode("</li>\n<li>", trim($form->form('required_type', false))); ?>
								<?php if($form->isRequired()):?>
								<li><?php echo $requiredTypeRadio[0]; ?></li>
								<li><label class="is-disable"><input type="radio" disabled="disabled">任意</label></li>
								<li><label class="is-disable"><input type="radio" disabled="disabled">非表示</label></li>
								<?php else:?>
								<li><?php $form->form('required_type')?></li>
								<?php endif;?>
							</ul>
							<div class="errors error-<?php echo $form->getElement('required_type')->getId()?>"></div>
						</td>
						<td class="action">
							<a class="i-e-up up-btn" href="javascript:void(0);">上へ移動</a>
							<a class="i-e-down down-btn" href="javascript:void(0);">下へ移動</a>
						</td>
					</tr>
					<?php if(($form instanceof Library\Custom\Hp\Page\SectionParts\Form\Element\Subject || $form instanceof  Library\Custom\Hp\Page\SectionParts\Form\Element\Multi || $form instanceof Library\Custom\Hp\Page\SectionParts\Form\Element\Free) && $form->getFreeChoiceCount()): ?>
					<tr>
						<td>
							<?php if(!$form->getElement('choices_type_code')):?>
							<p>チェックボックス</p>
							<?php else:?>
							<ul class="optional-contact mb10 option-type">
								<li><?php $form->form('choices_type_code')?></li>
							</ul>
							<?php endif;?>
							
							<ul class="choices-contact">
								<?php foreach($form->getPresetChoices() as $_i => $choice):?>
								<li>
									<label>選択肢<?php echo $_i + 1?></label>
									<?php echo $choice?>
								</li>
								<?php endforeach;?>
								<?php $presetChoiceCount = count($form->getPresetChoices())?>
								<?php for ($i=1,$l=$form->getFreeChoiceCount();$i<=$l;$i++):?>
								<li>
									<label>選択肢<?php echo $i + $presetChoiceCount?></label>
									<?php $form->form('choice_'.$i)?><span class="input-count"></span>
								</li>
								<?php endfor;?>

								<?php if(get_class($form) == "Library\Custom\Hp\Page\SectionParts\Form\Element\Request") : ?>
									<label><?php $form->form('detail_flg')?>備考を表示させる</label>
								<?php endif;?>

							</ul>
							<div class="errors"></div>
						</td>
						<td></td>
					</tr>
					<?php endif;?>
				</tbody>
				<?php endforeach;?>
			</table>
		</div>
	</div>
</div>