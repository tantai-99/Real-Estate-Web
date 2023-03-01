<div class="page-element sortable-item element-text" data-name="<?php echo $element->getName()?>" data-type="<?php echo $element->getType()?>" data-type-name="<?php echo $element->getTypeName()?>" data-is-unique="<?php echo $element->isUnique()?>">
	@include('_forms.hp-page.parts.partials.header', ['element' => $element])
	<div class="page-element-body">
		@include('_forms.hp-page.parts.partials.heading', ['element' => $element])

		<div class="item-list">
			<dl>
				<dt>埋め込みコード</dt>
				<dd>
					<?php echo $element->form('code')?>
			<div class="errors"></div>
			<div class="main-parts__tx_annotation" style="margin-top: 15px;">
						<p>「アットホーム VR内見・パノラマ」より「埋め込みコード」を取得して貼り付けてください。</p>
						<p>「埋め込みコード」取得時の推奨「画面サイズ」は以下となります。</p>
						<ul>
							<li>エリア挿入1列の場合：幅640px、高さ400px</li>
							<li>エリア挿入2列の場合：幅300px、高さ400px</li>
							<li>エリア挿入3列の場合：幅200px、高さ400px</li>
						</ul>
						<p>※スマートフォンは指定の「画面サイズ」にかかわらず、幅100%、高さ400pxで表示されます。</p>
					</div>
				</dd>
			</dl>
		</div>
		<div class="main-parts__tx_annotation">
			<p>※1ページ内に複数（目安として5つ以上）の「アットホーム VR内見・パノラマ」埋め込みコードを登録すると、スマートフォンなどでページが正常に読み込まれない場合がありますのでご注意ください。</p>
		</div>
	</div>
</div>
