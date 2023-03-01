<?php
/**
 * application/modules/default/controllers/PageController.php
 * line 166
 */
$disableTitle = false;
if(isset($view->disableTitle)){
    $disableTitle = $view->disableTitle;
}
$isSeo = true;
if (isset($view->isSeo)) {
    $isSeo = $view->isSeo;
}
$isArticlePageTop = false;
$isArticlePage = false;
if ($view->page->getType() == config('constants.hp_page.TYPE_USEFUL_REAL_ESTATE_INFORMATION')) {
    $isArticlePageTop = true;
} elseif ($view->page->isArticlePage()) {
    $isArticlePage = true;
}
?>
<div class="section">
  <h2>基本設定<?php if ($isSeo): ?><a href="javascript:void(0)" onclick="window.open('<?php echo route('default.seo-advice.tdk') ?>', '', 'width=720,height=820,scrollbars=1');" class="i-s-seo">SEOアドバイス</a><?php endif; ?></h2>
  <table class="form-basic">
    <?php if ($element->getElement('title')): ?>
      <tr class="<?php if ($element->getElement('title')->isRequired() && !$disableTitle): ?>is-require<?php endif; ?>">
        <th><span>ページタイトル<?php echo $view->toolTip('tdk_title')?></span></th>
        <td>
          <div class="inner">
              <?php if($disableTitle):?>
                  <span>
                      <?php echo $element->getElement('title')->getValue() ?>
                      <?php $element->simpleHidden('title') ?>
                  </span>
              <?php else: ?>
                 <span><?php $element->form('title') ?><span class="input-count">0/30</span></span>
              <?php endif;?>
          </div>
          <div class="errors"></div>

          <div class="real">
              <span class="real-heading">
                  <span>実際の表示</span>
              </span>
              <span class="real-body">
                  <span><?php if($element->getElement('title')->getValue() != "") {
                                  echo h($element->getElement('title')->getValue());
                              }else{
                                 echo "<入力内容が入ります>";
                              } ?></span> | <?php echo h($element->getElement('title')->getDescription()); ?>
              </span>
          </div>
          <?php if($disableTitle):?>
              <div class="real">
                <span><?php echo config('constants.original.TITLE_DISABLE'); ?></span>
              </div>
          <?php endif; ?>
        </td>
      </tr>
    <?php endif; ?>

    <?php if ($element->getElement('description')): ?>
      <tr class="<?php if ($element->getElement('description')->isRequired()) : ?>is-require<?php endif; ?>">
        <th><span>ページの説明<?php echo $view->toolTip('tdk_description')?></span></th>
        <td>
          <div class="inner">
            <span><?php $element->form('description') ?><span class="input-count">0/30</span></span>
          </div>
          <div class="errors"></div>
          <div class="real">
              <span class="real-heading">
                  <span>実際の表示</span>
              </span>
              <span class="real-body">
                  <span><?php if($element->getElement('description')->getValue() != "") {
                                  echo h($element->getElement('description')->getValue());
                              }else{
                                 echo "<入力内容が入ります>";
                              } ?></span> : <?php echo h($element->getElement('description')->getDescription()); ?>
              </span>
          </div>

        </td>
      </tr>
    <?php endif; ?>

    <?php if ($element->getElement('keyword1')): ?>
      <tr class="is-require only-first">
        <th><span>ページのキーワード<?php echo $view->toolTip('tdk_keyword')?></a></span></th>
        <td>
          <div class="inner input-keyword">
            <?php foreach ($element->getElements() as $name => $elem) : ?>
              <?php if (strstr($name, 'keyword')) : ?>
                <span><?php $element->form($name); ?><span class="input-count">0/30</span></span>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>

          <div class="real">
              <span class="real-heading">
                  <span>実際の表示</span>
               </span>
               <span class="real-body">
                   <div class="common-keyword">
                   <?php foreach ($element->getElements() as $name => $elem) : ?>
                       <?php if (strstr($name, 'keyword')) : ?>
                           <?php if($element->getElement($name)->getValue() != "") : ?>
                           <span id="view_<?php echo $element->getElement($name)->getId()?>"><?php echo $element->getElement($name)->getValue(); ?></span>
                           <?php else : ?>
                           <span id="view_<?php echo $element->getElement($name)->getId()?>"><入力内容が入ります></span>
                           <?php endif; ?>
                       <?php endif; ?>
                   <?php endforeach; ?>
                    <?php foreach (explode(',', $element->getElement('keyword1')->getDescription()) as $key => $keyword) : ?>
                      <?php if($keyword != "") :?><span><?php echo h($keyword); ?></span><?php endif;?>
                    <?php endforeach; ?>
                   </div>
               </span>
          </div>

          <div class="errors hide-multi-error"></div>
        </td>
      </tr>
    <?php endif; ?>

    <?php if ($element->getElement('filename')): ?>
      <tr class="<?php if ($element->getElement('filename')->isRequired() && !$disableTitle): ?>is-require<?php endif; ?>">
        <?php $tooltipName = $isArticlePageTop ? 'tdk_filename_article_top' : ($isArticlePage ? 'tdk_filename_article' : 'tdk_filename');?>
        <th><span>ページ名<small>（英語表記）</small><?php echo $view->toolTip($tooltipName)?></span></th>
        <td>
          <div class="inner w40per">
              <?php if($disableTitle || $isArticlePageTop):?>
                  <?php echo $element->getElement('filename')->getValue() ?>
                  <?php $element->simpleHidden('filename') ?>
              <?php else: ?>
                  <?php $element->form('filename') ?>
                  <span class="input-count">0/30</span>
              <?php endif;?>
          </div>
          <div class="errors"></div>

          <div class="real">
              <span class="real-heading">
                  <span>実際のURL</span>
              </span>
              <span class="real-body">
https://www.<?php echo \Library\Custom\User\UserAbstract::factory(getModuleName())->getProfile()->domain; ?>
<?php
if($view->page->getRow()->parent_page_id > 0) {
$urls = $view->page->getRow()->getPageUrl($view->page->getRow()->parent_page_id);
if(count($urls) > 0) {
$urls = array_reverse($urls);
foreach($urls as $key => $val) {
if($val == null) {
echo "/<ページ名が設定されていません>";
}else{
echo "/".$val;
}
}
}
}
?>
<span>/<?php if($element->getElement('filename')->getValue() != "") :?><?php echo trim(h($element->getElement('filename')->getValue())) ."/";?>
<?php else : ?><?php echo "<入力内容が入ります>/";?>
<?php endif; ?></span>
          </div>
          <?php if($disableTitle):?>
              <div class="real">
                <span><?php echo config('constants.original.PAGE_NAME_DISABLE'); ?></span>
              </div>
          <?php endif; ?>
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
          <div class="element-list-title has-copy">
          <?php $element->form('list_title')?><span class="input-count">0</span>
          </div>
          <div class="errors"></div>
        </td>
      </tr>
    <?php endif; ?>

    <?php if ($element->getElement('notification_class')): ?>
      <tr class="<?php if ($element->getElement('notification_class')->isRequired()): ?>is-require<?php endif; ?>">
        <th><span><?php echo $element->getElement('notification_class')->getLabel()?><?php echo $view->toolTip('tdk_notification_class')?></span></th>
        <td>
          <div class="inner w40per noti-select-ct">
            <?php $element->form('notification_class') ?>
          </div>
          <div class="errors"></div>
        </td>
      </tr>
    <?php endif; ?>

    </table>
</div>
<?php if ($element->getElement('new_mark')) :?>
<div class="section section-new-mark">
    <h2><?php echo $element->getElement('new_mark')->getLabel()?><?php echo $view->toolTip('tdk_new_mark')?></h2>
    <div class="option-new-mark">
        <?php $element->form('new_mark') ?>
    </div>
    <div class="errors"></div>
</div>
<?php endif; ?>
<?php if ($element->getElement('news_detail_type')) {
    $element->form('news_detail_type');
}
?>
