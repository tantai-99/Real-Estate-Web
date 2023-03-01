      <div class="element">
        <?php $hp = unserialize($this->viewHelper->getContentSettingFile('hp.txt')); ?>
        <?php if( in_array($hp['theme_name'], array('standard02_custom_color','natural02_custom_color','simple02_custom_color')) ) : ?>
        <p class="form-flow form-flow3">
          物件のお問い合わせ
        </p>
        <?php else: ?>
        <div class="form-flow form-flow3">
          <ul>
            <li>入力</li><li>確認</li><li>送信完了</li>
          </ul>
        </div>
        <?php endif;?>

        <p class="form-complete-tx">
        お問合せ頂きまして、誠にありがとうございます。<br>
        お客様からのお問合せに対し、3営業日以内にご連絡をするように努めておりますが、万が一3営業日を過ぎてもご連絡がない場合には、大変お手数ですが再度お問合せをお願い致します。
        </p>
        <p class="tac"><a href="/" class="btn-lv2">TOPに戻る</a></p>
      </div>
