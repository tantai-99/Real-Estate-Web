<div class="page-element sortable-item element-text" data-name="<?php echo $element->getName()?>" data-type="<?php echo $element->getType()?>" data-type-name="<?php echo $element->getTypeName()?>" data-is-unique="<?php echo $element->isUnique()?>">
	@include('_forms.hp-page.parts.partials.header', ['element' => $element])
	<div class="page-element-body">
		@include('_forms.hp-page.parts.partials.heading', ['element' => $element])
		
		<?php if(get_class($element) == 'Library\Custom\Hp\Page\Parts\Privacypolicy') : ?>
			<div class="policy-note">下記に入力されている内容が、プライバシーポリシーとしてお問い合わせ画面に表示されます。</div>
		<?php endif?>
		<div class="btn-right">
			<a class="policy-sample" href="javascript:;">雛形選択</a>
			<div class="is-hide">
				<div class="modal-message">
					<p>雛形（定型文例）のご利用について</p>
					<p style="font-size:12px;">（1）本雛形は参考資料として提供しております。そのため、本雛形をそのまま利用することはできません。条文内容を確認のうえ、利用者の業務形態に応じて、適宜加筆、修正、編集のうえ利用してください。<br>
					（2）当社は本雛形の正確性、完全性、有用性について一切保証いたしません。<br>
					（3）本雛形の利用に際しては、利用者の責任においてご利用ください。当社は直接・間接を問わず、本雛形の利用により生じる一切の問題、トラブル等に対する責任を負いません。</p>
				</div>
				<div class="modal-policy" style="height:120px;"><?php echo $element->getSample()?></div>
			</div>
		</div>
		
		<div class="item-list">
			<?php $element->form('value')?><span class="input-count">0</span>
			<div class="errors"></div>
		</div>
		@include('_forms.hp-page.parts.partials.text-util')

		<?php if(get_class($element) == 'Library\Custom\Hp\Page\Parts\Privacypolicy') : ?>
		<div>
			本ツールで作成されたサイトは、Googleアナリティクスについての下記内容がプライバシーポリシーに必ず表示されます。
			<div class="select-element-ga">
				◇Googleアナリティクスの利用<br />
				本ウェブサイトは、サイトの閲覧状況を把握するために、Google,Inc.のGoogleアナリティクスを使用しています。<br />
				本ウェブサイトにアクセスすると、お使いのウェブブラウザはGoogle,Inc.に特定の情報（たとえば、アクセスしたページのウェブ アドレスや IP アドレスなど）を自動的に送信します。<br />
				これらの情報は、Google,Inc.による「ユーザーがGoogle パートナーのサイトやアプリを使用する際の Google によるデータ使用」（www.google.com/policies/privacy/partners/）に従い収集、処理されます。<br />
				また、Google,Inc.がお使いのブラウザに Cookie を設定したり、既存のCookie を読み取ったりする場合もあります。<br />
			</div>
		</div>
		<?php endif?>
	</div>
</div>

