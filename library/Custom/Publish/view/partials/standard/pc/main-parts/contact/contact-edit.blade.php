      <div class="element">
        <p class="element-tx">
        お問い合わせは、下のフォームにご入力のうえ、「上記にご同意の上 確認画面へ進む」ボタンをクリックしてください。<br>
        なお、内容により返信できない場合や、返答までに日数を要する場合がありますので、予めご了承ください。    
        </p>
        <?php $hp = unserialize($this->viewHelper->getContentSettingFile('hp.txt')); ?>
        <?php if( in_array($hp['theme_name'], array('standard02_custom_color','natural02_custom_color','simple02_custom_color')) ) : ?>
        <p class="form-flow form-flow1">
        お問い合わせ内容入力
        </p>
        <?php else: ?>
        <div class="form-flow form-flow1">
          <ul>
            <li>入力</li><li>確認</li><li>送信完了</li>
          </ul>
        </div>
        <?php endif;?>
        <div class="form-error">
          <ul>
          </ul>
        </div>
          <form action="<?php echo '/'.$view->base['filename'].'/confirm/'?>" method="post">
          <?php if( in_array($hp['theme_name'], array('standard02_custom_color','natural02_custom_color','simple02_custom_color')) ) : ?>
          <div class="element element-form">
            <table class="form-table element-table6">
          <?php else: ?>
          <table class="form-table">
          <?php endif;?>
            <?php foreach($view->contactItems as $item): ?>

            <?php if($item['item_key']=='contact_info') continue; ?>
            <?php if ($item['item_key'] == 'hankyo_plus') continue; ?>
            <tr>
              <th <?php if($item['must_flg']): ?>class="form-must"<?php endif ?>><span><?php echo( htmlspecialchars($item['label']) ) ?></span></th>
              <td>
                <div class="form-error <?php echo htmlspecialchars($item['item_key']) ?>-err"></div>
                <?php // お問い合わせ内容 ?>
                <?php if($item['item_key']=='subject'): ?>
                <div class="form-error <?php echo htmlspecialchars($item['subject_more_item_key']) ?>-err"></div>
                <ul class="subject-input">
                  <?php $no=1; foreach($item['option'] as $option): ?>
                  <?php if(empty($option))continue; ?>
                  <li><input type="checkbox" name="<?php echo htmlspecialchars($item['item_key']) ?>[]" value="<?php echo $no?>" id="<?php echo htmlspecialchars($item['item_key']).$no?>" <?php if(in_array($no,$item['option_checked'])): ?> checked <?php endif ?> ><label for="<?php echo htmlspecialchars($item['item_key']).$no?>"><?php echo htmlspecialchars($option) ?></label></li>
                  <?php $no++; endforeach ?>
                </ul>
                <p class="tx-note">備考<br><textarea class="form-textarea <?php echo htmlspecialchars($item['subject_more_item_key']) ?>-input" name="<?php echo htmlspecialchars($item['subject_more_item_key']) ?>" maxlength="2000" validatelength="1000" label="備考"><?php echo htmlspecialchars($item['subject_more_item_value']) ?></textarea></p>

                <?php // リクエスト内容 ?>
                <?php elseif($item['item_key']=='request' && ($item['type'] == 'checkbox' || $item['type']=='radio' || $item['type']=='select')) : ?>
                  <?php if(isset($item['sub_item_key'])) : ?>
                    <div class="form-error <?php echo htmlspecialchars($item['request_more_item_key']) ?>-err"></div>
                  <?php endif ?>

                <?php // チェックボックス ?>
                <?php if($item['type']=='checkbox'): ?>
                <ul class="request-input">
                  <?php $no=1; foreach($item['option'] as $option): ?>
                  <?php if(empty($option))continue; ?>
                  <li><input type="checkbox" name="<?php echo $item['item_key'] ?>[]" value="<?php echo $no?>" id="<?php echo htmlspecialchars($item['item_key']).$no?>" <?php if(in_array($no,$item['option_checked'])): ?> checked <?php endif ?> ><label for="<?php echo htmlspecialchars($item['item_key']).$no?>"><?php echo htmlspecialchars($option) ?></label></li>
                  <?php $no++; endforeach ?>
                </ul>

                <?php // ラジオボタン ?>
                <?php elseif($item['type']=='radio'): ?>
                <ul class="list-radio request-input" >
                  <?php $no=1; foreach($item['option'] as $option): ?>
                  <?php if(empty($option))continue; ?>
                  <li><input type="radio" name="<?php echo htmlspecialchars($item['item_key']) ?>[]" value="<?php echo $no?>" id="<?php echo htmlspecialchars($item['item_key']).$no?>" <?php if(in_array($no,$item['option_checked'])): ?> checked <?php endif ?> ><label for="<?php echo htmlspecialchars($item['item_key']).$no?>"><?php echo htmlspecialchars($option) ?></label></li>
                  <?php $no++; endforeach ?>
                </ul>

                <?php // プルダウン ?>
                <?php elseif($item['type']=='select'): ?>
                <select name="<?php echo htmlspecialchars($item['item_key'])?>" class="request-input">
                  <?php $no=1; foreach($item['option'] as $option): ?>
                  <?php if(empty($option))continue; ?>
                  <option value="<?php echo $no?>" <?php if($no == $item['option_selected']): ?> selected <?php endif ?> ><?php echo htmlspecialchars($option) ?></option>
                  <?php $no++; endforeach ?>
                </select>
                <?php endif ?>
                <?php if(isset($item['sub_item_key'])) : ?>
                <p class="tx-note">リクエスト備考<br>
                  <textarea class="form-textarea <?php echo htmlspecialchars($item['request_more_item_key']) ?>-input" name="<?php echo htmlspecialchars($item['request_more_item_key']) ?>" maxlength="2000" validatelength="1000" label="リクエスト備考"><?php echo htmlspecialchars($item['request_more_item_value']) ?></textarea>
                </p>
                <?php endif ?>

                <?php // 連絡先 ?>
                <?php elseif($item['item_key']=='connection'): ?>
                    <?php if(count($item['items'])>=1): ?><?php if (!empty($item['annotation'])):?><p class="element-tx tx-annotation">※<?php echo htmlspecialchars($item['annotation'])?></p><?php endif ?><?php endif ?>
                    <dl class="form-address">
                      <?php $no=1; foreach($item['items'] as $connection_item): ?>
                      <div class="form-error <?php echo htmlspecialchars($connection_item['item_key']) ?>-err"></div>
                      <dt><?php echo htmlspecialchars($connection_item['label']) ?></dt>
                      <dd>
                        <?php if($connection_item['item_key'] === 'person_tel'): ?>
                          <div class="person-tel">
                          <?php for($i=1; $i<=3; $i++ ): ?>
                            <input type="text" name="<?php echo htmlspecialchars($connection_item['item_key']) . $i?>"
                              class="input-tx3 mr0 <?php echo htmlspecialchars($connection_item['item_key']) ?>-input"
                              value="<?php echo htmlspecialchars($connection_item['item_value_' . $i]) ?>" maxlength="<?php echo htmlspecialchars($connection_item['maxlength'][$i - 1]) ?>" validatelength="<?php echo htmlspecialchars($connection_item['validatelength'][$i - 1]) ?>" label="<?php echo htmlspecialchars($connection_item['label']) ?>">
                            <?php if($i !== 3): ?>
                              <span>-</span>
                            <?php endif; ?>
                          <?php endfor; ?>
                          </div>
                        <?php else: ?>
                          <input type="text" name="<?php echo htmlspecialchars($connection_item['item_key'])?>" 
                            class="input-tx1 <?php echo htmlspecialchars($connection_item['item_key']) ?>-input"
                            value="<?php echo htmlspecialchars($connection_item['item_value']) ?>" maxlength="<?php echo htmlspecialchars($connection_item['maxlength']) ?>" validatelength="<?php echo htmlspecialchars($connection_item['validatelength']) ?>" label="<?php echo htmlspecialchars($connection_item['label']) ?>"></dd>
                        <?php endif; ?>
                      </dd>
                      <?php $no++; endforeach ?>
                    </dl>
                <?php // 面積 ?>
                <?php elseif($item['item_key']=='property_exclusive_area' || $item['item_key']=='property_building_area' || $item['item_key']=='property_land_area'): ?>
                  <div class="form-error <?php echo htmlspecialchars($item['item_key']) ?>_sub-err"></div>
                  <div class="text-radio">
                  	<input type="text" name="<?php echo htmlspecialchars($item['item_key']) ?>" class="input-tx3 <?php echo htmlspecialchars($item['item_key']) ?>-input" value="<?php echo htmlspecialchars($item['item_value']) ?>" maxlength="<?php echo htmlspecialchars($item['maxlength']) ?>" validatelength="<?php echo htmlspecialchars($item['validatelength']) ?>" label="<?php echo htmlspecialchars($item['label']) ?>">
	                  <ul class="list-radio <?php echo htmlspecialchars($item['item_key']) ?>_sub-input" >
                    <?php $no=1; foreach($item['sub_option'] as $option): ?>
	                    <?php if(empty($option))continue; ?>
	                    <li><input type="radio" name="<?php echo htmlspecialchars($item['item_key'].'_sub') ?>[]" <?php if($option === '坪'): ?>checked="checked"<?php endif; ?> value="<?php echo $no?>" id="<?php echo htmlspecialchars($item['item_key'].'_sub').$no?>" <?php if(in_array($no,$item['sub_option_checked'])): ?> checked <?php endif ?> ><label for="<?php echo htmlspecialchars($item['item_key'].'_sub').$no?>"><?php echo $option ?></label></li>
	                    <?php $no++; endforeach ?>
	                  </ul>
                  </div>
                <?php // 築年数 ?>
                <?php elseif($item['item_key']=='property_age'): ?>
                  <div class="form-error <?php echo htmlspecialchars($item['item_key']) ?>_sub-err"></div>
                  <div class="text-radio">
	                  <ul class="list-radio <?php echo htmlspecialchars($item['item_key']) ?>_sub-input" >
	                    <?php $no=1; foreach($item['sub_option'] as $option): ?>
	                    <?php if(empty($option))continue; ?>
	                    <li><input type="radio" name="<?php echo htmlspecialchars($item['item_key'].'_sub') ?>[]" <?php if($option === '西暦'): ?>checked="checked"<?php endif; ?> value="<?php echo $no?>" id="<?php echo htmlspecialchars($item['item_key'].'_sub').$no?>" <?php if(in_array($no,$item['sub_option_checked'])): ?> checked <?php endif ?> ><label for="<?php echo htmlspecialchars($item['item_key'].'_sub').$no?>"><?php echo $option ?></label></li>
	                    <?php $no++; endforeach ?>
	                  </ul>
                    <input type="text" name="<?php echo htmlspecialchars($item['item_key']) ?>" class="input-tx3 <?php echo htmlspecialchars($item['item_key']) ?>-input" value="<?php echo htmlspecialchars($item['item_value']) ?>" maxlength="<?php echo htmlspecialchars($item['maxlength']) ?>" validatelength="<?php echo htmlspecialchars($item['validatelength']) ?>" label="<?php echo htmlspecialchars($item['label']) ?>"><?php echo $contact->getItemUnitWord($item['item_key']); ?>
                  </div>
                <?php // 間取り ?>
                <?php elseif($item['item_key']=='property_layout'): ?>
                  <div class="form-error <?php echo htmlspecialchars($item['item_key']) ?>_sub-err"></div>
	                <select name="<?php echo htmlspecialchars($item['item_key'])?>" class="<?php echo htmlspecialchars($item['item_key']) ?>-input">
	                  <?php $options = $item['option'];?>
	                  <?php $no=1; foreach($options as $option): ?>
	                  <?php if(empty($option))continue; ?>
	                  <option value="<?php echo $no?>" <?php if($no == $item['option_selected']): ?> selected <?php endif ?> ><?php echo htmlspecialchars($option) ?></option>
	                  <?php $no++; endforeach ?>
	                </select>
	                <?php $options = $item['sub_option'];?>
	                <select name="<?php echo htmlspecialchars($item['item_key'].'_sub')?>" class="<?php echo htmlspecialchars($item['item_key']) ?>_sub-input">
	                  <?php $no=1; foreach($options as $option): ?>
	                  <?php if(empty($option))continue; ?>
	                  <option value="<?php echo $no?>" <?php if($no == $item['sub_option_selected']): ?> selected <?php endif ?> ><?php echo htmlspecialchars($option) ?></option>
	                  <?php $no++; endforeach ?>
	                </select>

                <?php // テキスト ?>
                <?php elseif($item['type']=='text'): ?>
                  <input type="text" name="<?php echo htmlspecialchars($item['item_key']) ?>" class="input-tx1 <?php echo htmlspecialchars($item['item_key']) ?>-input" value="<?php echo htmlspecialchars($item['item_value']) ?>" maxlength="<?php echo htmlspecialchars($item['maxlength']) ?>" validatelength="<?php echo htmlspecialchars($item['validatelength']) ?>" label="<?php echo htmlspecialchars($item['label']) ?>"> <?php echo $contact->getItemUnitWord($item['item_key']); ?>

                <?php // テキストエリア ?>
                <?php elseif($item['type']=='textarea'): ?>
                  <textarea class="form-textarea <?php echo htmlspecialchars($item['item_key']) ?>-input" name="<?php echo htmlspecialchars($item['item_key']) ?>" maxlength="<?php echo htmlspecialchars($item['maxlength']) ?>" validatelength="<?php echo htmlspecialchars($item['validatelength']) ?>" label="<?php echo htmlspecialchars($item['label']) ?>"><?php echo htmlspecialchars($item['item_value']) ?></textarea>

                <?php // チェックボックス ?>
                <?php elseif($item['type']=='checkbox'): ?>
                  <ul class="<?php echo htmlspecialchars($item['item_key']) ?>-input">
                    <?php $no=1; foreach($item['option'] as $option): ?>
                    <li><input type="checkbox" name="<?php echo $item['item_key'] ?>[]" value="<?php echo $no?>" id="<?php echo htmlspecialchars($item['item_key']).$no?>" <?php if(in_array($no,$item['option_checked'])): ?> checked <?php endif ?> ><label for="<?php echo htmlspecialchars($item['item_key']).$no?>"><?php echo htmlspecialchars($option) ?></label></li>
                    <?php $no++; endforeach ?>
                  </ul>

                <?php // ラジオボタン ?>
                <?php elseif($item['type']=='radio'): ?>
                  <ul class="list-radio <?php echo htmlspecialchars($item['item_key']) ?>-input" >
                    <?php $no=1; foreach($item['option'] as $option): ?>
                    <li><input type="radio" name="<?php echo htmlspecialchars($item['item_key']) ?>[]" value="<?php echo $no?>" id="<?php echo htmlspecialchars($item['item_key']).$no?>" <?php if(in_array($no,$item['option_checked'])): ?> checked <?php endif ?> ><label for="<?php echo htmlspecialchars($item['item_key']).$no?>"><?php echo htmlspecialchars($option) ?></label></li>
                    <?php $no++; endforeach ?>
                  </ul>

                <?php // プルダウン ?>
                <?php elseif($item['type']=='select'): ?>
                  <select name="<?php echo htmlspecialchars($item['item_key'])?>" class="<?php echo htmlspecialchars($item['item_key']) ?>-input">
                    <?php $no=1; foreach($item['option'] as $option): ?>
                    <option value="<?php echo $no?>" <?php if($no == $item['option_selected']): ?> selected <?php endif ?> ><?php echo htmlspecialchars($option) ?></option>
                    <?php $no++; endforeach ?>
                  </select>
                <?php endif ?>
              </td>
            </tr>
            <?php endforeach ?>
          </table>
          <?php if( in_array($hp['theme_name'], array('standard02_custom_color','natural02_custom_color','simple02_custom_color')) ) : ?>
          </div>
          <?php endif ?>
          <p class="element-tx tx-annotation">
             ※営利目的・商用利用は固くお断りいたします。
          </p>
          [[privacypolicy]]

            <p class="element-tx">
                お問い合わせを行う前に、上記の「プライバシーポリシー」を必ずお読みください。<br>
                「プライバシーポリシー」に同意いただいた場合は「上記にご同意の上 確認画面へ進む」のボタンをクリックしてください。
            </p>

              <input type="hidden" name="next">
          <p class="tac"><input type="submit" value="上記にご同意の上 確認画面へ進む" name="next" class="btn-lv1"></p>
        </form>
      </div>
