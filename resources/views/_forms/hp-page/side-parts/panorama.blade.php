<div class="page-element sortable-item element-text" data-name="<?php echo $element->getName()?>" data-type="<?php echo $element->getType()?>" data-type-name="<?php echo $element->getTypeName()?>" data-is-unique="<?php echo $element->isUnique()?>">
	@include('_forms.hp-page.side-parts.partials.header', ['element' => $element])
	<div class="page-element-body">
		@include('_forms.hp-page.side-parts.partials.heading', ['element' => $element])

        <dl class="item-header">
            <dt>埋め込みコード</dt>
            <dd>
                <?php $element->form('value')?>
                <div class="errors"></div>
            </dd>
        </dl>
		<div class="side-parts__tx_annotation">
		「アットホーム VR内見・パノラマ」より「埋め込みコード」を取得して貼り付けてください。<br/>
		「埋め込みコード」取得時の推奨「画面サイズ」は、幅200px、高さ400pxとなります。<br/>
		※スマートフォンは指定の「画面サイズ」にかかわらず、幅100%、高さ400pxで表示されます。<br/>
		※1ページ内に複数（目安として5つ以上）の「アットホーム VR内見・パノラマ」埋め込みコードを登録すると、スマートフォンなどでページが正常に読み込まれない場合がありますのでご注意ください。
		</div>
	</div>

</div>
