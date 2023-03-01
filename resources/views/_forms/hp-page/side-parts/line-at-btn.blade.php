<div class="page-element sortable-item element-text" data-name="<?php echo $element->getName()?>" data-type="<?php echo $element->getType()?>" data-type-name="<?php echo $element->getTypeName()?>" data-is-unique="<?php echo $element->isUnique()?>">
    @include('_forms.hp-page.side-parts.partials.header', ['element' => $element])
    <div class="page-element-body">
        <p>
            <?php echo $element->getTitle()?>が表示されます。<br>
            ※基本設定にある「LINE公式アカウント 友だち追加ボタン」にて埋め込みコードが登録されていない場合、表示されません。
        </p>
        <dl class="item-header">
            <dt>コメント</dt>
            <dd>
                <?php $element->form('comment')?>
                <span class="input-count"></span>
                <div class="errors"></div>
            </dd>
        </dl>

    </div>
</div>