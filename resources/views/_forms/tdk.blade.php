<div class="section">
  <h2>基本設定<a href="javascript:void(0)" onclick="window.open('<?php echo $this->url(array('controller' => 'seo-advice', 'action' => 'tdk',));?>', '', 'width=720,height=820,scrollbars=1');" class="i-s-seo">SEOアドバイス</a></h2>
  <table class="form-basic">
    <?php if ($this->element->title): ?>
      <tr class="<?php if ($this->element->title->isRequired()): ?>is-require<?php endif; ?>">
        <th><span>ページタイトル<a href="javascript:void(0);" class="i-s-tooltip"></a></span></th>
        <td>
          <div class="inner">
            <span><?php $this->element->form('title') ?></span>

            <p><?php echo $this->h($this->element->title->getDescription()) ?></p>
          </div>
          <span class="input-count">0/30</span>

          <div class="errors"></div>
        </td>
      </tr>
    <?php endif; ?>

    <?php if ($this->element->description): ?>
      <tr class="<?php if ($this->element->description->isRequired()) : ?>is-require<?php endif; ?>">
        <th><span>ページの説明<a href="javascript:void(0);" class="i-s-tooltip"></a></span></th>
        <td>
          <div class="inner">
            <span><?php $this->element->form('description') ?></span>
            <p><?php echo $this->h($this->element->description->getDescription()); ?></p>
            <div class="errors"></div>
          </div>
          <span class="input-count">0/30</span>
          <div class="errors"></div>
        </td>
      </tr>
    <?php endif; ?>

    <?php if ($this->element->keyword1): ?>
      <tr class="<?php if ($this->element->keyword1->isRequired()) : ?>is-require<?php endif; ?>">
        <th><span>ページのキーワード<a href="javascript:void(0);" class="i-s-tooltip"></a></span></th>
        <td>
          <div class="inner input-keyword">
            <?php foreach ($this->element as $name => $element) : ?>
              <?php if (strstr($name, 'keyword')) : ?>
                <span><?php $this->element->form($name); ?><span class="input-count">0/30</span></span>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
          <div class="common-keyword">
            <?php foreach (array_values(array_filter(explode(',', $this->element->keyword1->getDescription()), "strlen")) as $key => $keyword) : ?>
              <span><?php echo $this->h($keyword); ?></span>
            <?php endforeach; ?>
          </div>
          <div class="errors hide-multi-error"></div>
        </td>
      </tr>
    <?php endif; ?>

    <?php if ($this->element->filename): ?>
      <tr class="<?php if ($this->element->filename->isRequired()): ?>is-require<?php endif; ?>">
        <th><span>ページ名<small>（英語表記）</small><a href="javascript:void(0);" class="i-s-tooltip"></a></span></th>
        <td>
          <div class="inner w40per">
            <?php $this->element->form('filename') ?>
            <span class="input-count">0/30</span>
          </div>
          <div class="errors"></div>
        </td>
      </tr>
    <?php endif; ?>
  </table>
</div>
