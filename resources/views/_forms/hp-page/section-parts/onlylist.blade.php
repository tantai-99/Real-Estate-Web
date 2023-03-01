<script type="text/javascript">
$(function () {
	'use strict';

  app.page.selectData = <?php echo json_encode($element->simpleSelectData('link_page_id'));?>;
  app.page.ToolTipTitle = <?php echo json_encode($view->toolTip('page_list_title'));?>;
  app.page.ToolTipUpdateDate = <?php echo json_encode($view->toolTip('page_list_update_date'));?>;
  app.page.ToolTipSearchSpecialLabel = <?php echo json_encode($view->toolTip('search_special_label'));?>;
  app.page.initUseLinkLoad($('.onlylist-link-page'));
});
</script>
<div class="section">
  <table class="form-basic form-detail-link">
    <?php if ($element->getElement('title')): ?>
      <tr>
        <th><span>ページタイトル</span></th>
        <td>
          <div class="inner">
                <span>
                    <?php echo $element->getElement('title')->getValue() ?>
                </span>
                <?php $element->simpleHidden('title') ?>
          </div>
          <div class="errors"></div>
              <div class="real">
                <span>※一覧タイトルに入力した内容が連動して表示されます。「サイトの公開/更新」画面などに表示されるページ名となり、公開されたホームページ上には表示されません。</span>
              </div>
        </td>
      </tr>
    <?php endif; ?>

    <?php if ($element->getElement('date')): ?>
      <tr class="<?php if ($element->getElement('date')->isRequired()): ?>is-require<?php endif; ?>">
        <th><span><?php echo $element->getElement('date')->getLabel()?><?php echo $view->toolTip('tdk_date')?></span></th>
        <td>
          <div class="inner w40per">
            <?php $element->form('date') ?>
          </div>
          <div class="errors"></div>
        </td>
      </tr>
    <?php endif; ?>

    <?php if ($element->getElement('list_title')): ?>
      <tr class="<?php if ($element->getElement('list_title')->isRequired()): ?>is-require<?php endif; ?>">
        <th><span><?php echo $element->getElement('list_title')->getLabel()?><?php echo $view->toolTip('tdk_list_title')?></span></th>
        <td>
          <div class="element-list-title">
          <?php $element->simpleText('list_title')?><span class="input-count">0</span>
          </div>
          <div class="errors"></div>
        </td>
      </tr>
    <?php endif; ?>

    </table>
    <div class="page-element-body" style="padding: 10px 0;background-color: #fff;">
    <div class="onlylist-link-page item-list">
        <div class="input-img-link">
            <label><?php $element->form('use_image') ?><?php echo $element->getElement('use_image')->getLabel(); ?></label>
            <div class="input-img-wrap" style="display:none;">
            <label><?php $element->form('link_target_blank')?><?php echo $element->getElement('link_target_blank')->getLabel()?></label>
                <?php $radios = explode("\n", trim($element->form('link_type', false)))?>
                <div class="search-btn link-wrapper">
                  <label class="select-page-radio">
                    <?php echo $radios[0]?>
                    <a class="btn-t-gray" href="javascript:;">ページを検索</a>
                  </label>
                  <ul>
                    <li class="page-name">
                      <?php echo $element->getSelectPageTitle('link_page_id'); ?>
                    </li>
                    <div class="is-hide select-page"><?php $element->simpleSelect('link_page_id')?></div>
                  </ul>
                  <div class="errors"></div>
                </div>
                <dl class="link-wrapper">
                    <dt><?php echo $radios[1]?></dt>
                    <dd>
                        <?php $element->simpleText('link_url')?><span class="input-count link-url-count"></span>
                        <div class="errors"></div>
                    </dd>
                </dl>
                <dl class="link-wrapper">
                  <div>
                    <dt style="padding-top: 10px"><?php echo $radios[2] ?></dt>
                    <dd>
                        <div class="select-file2">
                        <?php if( $file2Id = $element->getElement('file2')->getValue() ):?>
                            <?php $file2 = \App::make(\App\Repositories\HpFile2\HpFile2RepositoryInterface::class)->fetchFile2Information( $file2Id ); ?>
                            <a class="btn-t-gray" href="javascript:void(0);">ファイルを追加</a>
                            <p class="select-file2-title">選択中ファイル：<?php echo $file2['title'].'.'.$file2['extension']?></p>
                            <?php else:?>
                            <a class="btn-t-gray" href="javascript:void(0);">ファイルを追加</a>
                            <?php endif;?>
                            <?php $element->simpleHidden( 'file2' )?>
                        </div>
                    </dd>
                  </div>
					<div class="errors" style="position: relative;left: 23px;display: table-row;white-space: nowrap"></div>
                </dl>
                <?php if ($element->getElement('link_house')): ?>
                <dl class="link-wrapper">
                        <dt><?php echo $radios[3] ?></dt>
                        <div class="link-house-module link-house-module-edit">
                            <ul>
                                <li class="search-house-method">
                                <?php $radiosSearchHouseType = explode("<br />", trim($element->form('search_type', false)))?>
                                <?php echo $radiosSearchHouseType[0];?>
                                <?php echo $radiosSearchHouseType[1];?>
                                </li>
                                <li class="content-search-method">
                                    <div>
                                        <a class="btn-t-gray btn-search-all-house" href="javascript:;">物件を検索</a>
                                    </div>
                                    <div>
                                      <?php $element->simpleText( 'house_no' )?>
                                      <a class="btn-t-gray btn-search-house-no" href="javascript:;">検索</a>
                                    </div>
                                    <div class="error"></div>
                                </li>
                                <div class="error"></div>
                                <li class="display-house-title">
                                    <label>選択中の物件<?php echo $view->toolTip('display_house_title')?></label>
                                    <div class="house-title">
                                      <label></label>
                                      <a href="javascript:;" class="btn-p-pc btn-preview-link-house is-hide" data-type="pc"></a>
                                      <?php $element->simpleHidden( 'link_house' )?>
                                    </div>
                                </li>
                                <li class="member-no-info is-hide">
                                    <label></label> 
                                    <label class="display-house-no"></label>
                                    <?php $element->simpleHidden( 'link_house_type' )?>
                                </li>
                                <div class="errors"></div>
                            </ul>
                        </div>
                    </dl>
                <?php endif;?>
            </div>
        </div>
        </div>
    </div>
        <?php if ($element->getElement('notification_class')): ?>
        <table class="form-basic">
        <tr class="<?php if ($element->getElement('notification_class')->isRequired()): ?>is-require<?php endif; ?>">
            <th><span><?php echo $element->getElement('notification_class')->getLabel()?><?php echo $view->toolTip('tdk_notification_class')?></span></th>
            <td>
            <div class="inner w40per noti-select-ct">
                <?php $element->form('notification_class') ?>
            </div>
            <div class="errors"></div>
            </td>
        </tr>
        </table>
        <?php endif; ?>

</div>
