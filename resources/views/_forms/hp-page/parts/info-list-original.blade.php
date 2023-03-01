@push('style')
<link rel="stylesheet" href="/css/admin/common-top-original.css">
@endpush
@push('script')
<script type="text/javascript" src="/js/admin/common.js"></script>
@endpush
<div class="section">
    <div class="page-area">
        <div class="top-original-noti">
            <div>お知らせ<?php echo $element->getElement('notification_type')->getValue();?>表示件数の設定</div>
            <div class="errors is-hide"></div>
            <div>
                <?php $element->simpleHidden('id')?>
                <?php $element->simpleHidden('parts_type_code')?>
                <?php $element->simpleHidden('heading')?>
                <?php $element->simpleHidden('heading_type')?>
                <table class="tb-basic tb-centered tb-select tb-bordered">
                    <thead>
                    <tr>
                        <th class="top-original-border-right">設定中の表示件数</th>
                        <?php if (!getInstanceUser('cms')->isNerfedTop() || 0 == $element->getElement('cms_disable')->getValue()): ?>
                            <th>表示件数の変更</th>
                        <?php endif ?>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="top-original-border-right top-original-select">
                            <span><?php echo ($element->getElement('page_size')->getValue()) ? $element->getElement('page_size')->getValue() : 1 ?></span>
                        </td>
                        <?php if (!getInstanceUser('cms')->isNerfedTop() || 0 == $element->getElement('cms_disable')->getValue()): ?>
                            <td class="top-original-select-items">
                                <div class="select-custom-noti select-ct">
                                    <?php $element->simpleSelect('page_size')?>
                                    <div class="sel-custom-agency"><?php echo ($element->getElement('page_size')->getValue()) ? $element->getElement('page_size')->getValue() : 1 ?></div>
                                </div>
                                <div class="errors"></div>
                            </td>
                        <?php else:
                        $element->simpleHidden('page_size');
                        endif ?>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>