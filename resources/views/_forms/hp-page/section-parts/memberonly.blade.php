<div class="section">
  <h2>ID・パスワード設定</h2>
  <p>※会員さま専用ページのID・パスワードは全会員さまで共通です。</p>
  <table class="form-basic">
      <tr class="is-require">
        <th><span>ID<?php echo $view->toolTip('page_memberonly_id')?></span></th>
        <td>
            <span><?php $element->form('member_id') ?></span>
            <span class="input-count">0/30</span>
            <div class="errors"></div>
        </td>
      </tr>

      <tr class="is-require">
        <th><span>パスワード<?php echo $view->toolTip('page_memberonly_password')?></span></th>
        <td>
            <span><?php $element->form('member_password') ?></span>
            <span class="input-count">0/30</span>
            <div class="errors"></div>
        </td>
      </tr>

      <tr class="is-require">
        <th><span>パスワード（確認）<?php echo $view->toolTip('page_memberonly_password_confirm')?></span></th>
        <td>
            <span><?php $element->form('member_password_confirm') ?></span>
            <span class="input-count">0/30</span>
            <div class="errors"></div>
        </td>
      </tr>
      
    </table>
</div>