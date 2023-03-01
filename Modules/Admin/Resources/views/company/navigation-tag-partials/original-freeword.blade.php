<?php
$freeword_type_list_default = [
    [ 'type_no' => 0, 'display_flg' => true, 'display_name' => '選択してください', 'place_holder' => '種別を選択してください' ],
    [ 'type_no' => 1, 'display_flg' => true, 'display_name' => '居住用賃貸', 'place_holder' => '例：12.2万円以下 和室' ],
    [ 'type_no' => 2, 'display_flg' => true, 'display_name' => '事業用賃貸', 'place_holder' => '例：12.2万円以下 駐車場あり' ],
    [ 'type_no' => 3, 'display_flg' => true, 'display_name' => '居住用売買', 'place_holder' => '例：2000万円以下 南向き' ],
    [ 'type_no' => 4, 'display_flg' => true, 'display_name' => '事業用売買', 'place_holder' => '例：2000万円以下 駐車場あり' ],
];
if(!empty($freeword_type_list)) {
    $freeword_type_list = $freeword_type_list;
} else {
    $freeword_type_list = $freeword_type_list_default;
}
foreach($freeword_type_list as $type) {
	if($type['type_no'] == 0) {
        $freeword_type_0 = $type;
    }
}

?>
<div class="section notification">
    <div class="section-title">フリーワード検索</div>

    <dl>
    <dt>■カスタム検索フォーム</dt>
    </dl>

    <form id="freeword_setting" name="freeword_setting" enctype="application/x-www-form-urlencoded" method="post" action="">

    <div style="height:1px;">
    <?php echo $freeword_setting->form('company_id');?>
    <?php echo $freeword_setting->form('hd_id');?>
    </div>

    <table class="tb-basic tb-th-2 tb-body-bordered">
        <thead>
            <tr>
                <th style="width:600px">フォーム生成：種目表示・並べ替え制御</th>
                <th >プレースホルダ―</th>
            </tr>
        </thead>
        <tbody id="fw-shubetsu-sort">
            <tr>
                <td colspan="2">
                    <div style="float:left;">種別選択　　<?php //echo $freeword_setting->submit; ?></div>
                    <input type="submit" name="freeword_setting[submit]" id="top_freeword_submit" value="<?php echo $submit; ?>" class="btn-t-blue">
                    <div style="float:right;">
                        <input type="button" value="初期文章に戻す" class="btn-t-blue reset-placeholder">
                    </div>
                    <div style="clear:both;"/>
                </td>
            </tr>
            <tr>
                <td>
                    <dl>
                        <input type="hidden" name="freeword_setting[type_no][]" value="0"/>
                        <input type="hidden" name="freeword_setting[display_flg][]" value="1"/>
                        <dt style="margin-left:72px;margin-top:6px;float:left;width:130px;" shubetsuno="0">0.<?php echo $freeword_type_list_default[0]['display_name'];?></dt>
                        <dd style="margin-top:6px;float:left;margin-left:20px">
                            <input type="text" name="freeword_setting[display_name][]" placeholder="<?php echo $freeword_type_list_default[0]['display_name'];?>" value="<?php echo htmlspecialchars($freeword_type_0['display_name']);?>" class="watch-input-count" data-maxlength="50" size="17"/>
                            <span class="input-count">0/50</span>
                        </dd>
                        <dd style="clear:both;"/>
                    </dl>
                </td>
                <td>
                    <dl>
                        <dd style="margin-top:6px;margin-left:20px">
                            <input type="text" name="freeword_setting[place_holder][]" placeholder="<?php echo $freeword_type_list_default[0]['place_holder'];?>" value="<?php echo htmlspecialchars($freeword_type_0['place_holder']);?>" class="watch-input-count" data-maxlength="100"/>
                            <span class="input-count">0/100</span>
                       </dd>
                    </dl>
                </td>
            </tr>

<?php foreach($freeword_type_list as $type): ?>

<?php
    if($type['type_no'] == 0) continue;
    switch($type['type_no']) {
        case 1:
        case 2:
        case 3:
        case 4:
            $defNo = intval($type['type_no']);
            $alias_name = $freeword_type_list_default[ $defNo ]['display_name'];
            $default_placeholder = $freeword_type_list_default[ $defNo ]['place_holder'];
            break;
        default:
            continue 2;
            break;
    }
?>
            <tr>
                <td>
                    <dl>
                        <input type="hidden" name="freeword_setting[type_no][]" value="<?php echo $type['type_no'];?>"/>
                        <?php if(isset($type['display_flg']) && $type['display_flg'] == 1) { ?>
						<input type="hidden" name="freeword_setting[display_flg][]" value="1"/>
                        <?php } else { ?>
						<input type="hidden" name="freeword_setting[display_flg][]" value="0"/>
                        <?php } ?>
                        <dd style="margin-top:6px;float:left;">
                            <span class="updown" style="margin-left:5px;margin-right:10px;">
                            <a href="javascript:void(0);" class="fw-up-btn"><i class="i-e-up" style="display:inline-flex;">上へ移動</i></a>
                            <a href="javascript:void(0);" class="fw-down-btn"><i class="i-e-down" style="display:inline-flex;">下へ移動</i></a>
                            </span>
                            <div style="display:inline;width:10px;">
                            <input type="checkbox" name="" class="ctl_display_flg" <?php if(isset($type['display_flg']) && $type['display_flg'] == 1) { echo 'checked'; } ?>/>
                            </div>
                        </dd>
                        <dt style="margin-top:6px;float:left;width:130px;" shubetsuno="<?php echo $type['type_no'];?>"><?php echo $type['type_no'];?>.<?php echo $alias_name;?></dt>
                        <dd style="margin-top:6px;float:left;margin-left:20px">
                            <input type="text" name="freeword_setting[display_name][]" placeholder="<?php echo $alias_name;?>" value="<?php echo htmlspecialchars($type['display_name']);?>" class="watch-input-count" data-maxlength="50" size="17"/>
                            <span class="input-count">0/50</span>
                        </dd>
                        <dd style="clear:both;"/>
                    </dl>
                </td>
                <td>
                   <dl>
                        <dd style="margin-top:6px;margin-left:20px">
                            <input type="text" name="freeword_setting[place_holder][]" placeholder="<?php echo $default_placeholder;?>" value="<?php echo htmlspecialchars($type['place_holder']);?>" class="watch-input-count" data-maxlength="100"/>
                            <span class="input-count">0/100</span>
                        </dd>
                    </dl>
                </td>
            </tr>
<?php endforeach; ?>

        </tbody>
    </table>
    </form>

    <br/>
    <dl>
    <dt>■フォームテンプレート</dt>
    </dl>

    <div>
        <span>
        <textarea id="originalFormTag" style="width:80%;display:inline;overflow:hidden;" rows="4" readonly>
<form class="top_freewords_wrap">


</form></textarea>
        </span>
        <input type="hidden" value="" id="originalFormTagHidden" readonly>
        <span class="copy-icon"><i class="fa fa-files-o" aria-hidden="true"></i></span>
    </div>

    <br/>

        <dl>
        <dt>■オリジナル検索用scriptタグ</dt>
        </dl>
    <div>
        <span>
        <textarea id="originalSearchScript" style="width:80%;display:inline;overflow:hidden;" rows="1" readonly>
<script type="text/javascript" src="/top/js/top-search.js" defer></script></textarea>
        </span>
        <input type="hidden" value="" id="originalSearchScriptHidden">
        <span class="copy-icon"><i class="fa fa-files-o" aria-hidden="true"></i></span>

    <br/>
        <a href="dl-top-search-js">ダウンロード:top-search.js</a>
        <div style="padding:5px;width:80%;border:solid 1px #c0c0c0">
            <dl>
            <dt>●手順</dt>
            <dd>1. top-search.js をダウンロードします。</dd>
            <dd>2. 『編集ファイル一覧』のtop_jsにtop-search.jsをアップロードします。</dd>
            <dd>3. トップページに検索機能を設置する場合は、jQuery本体の読込み記述後に『オリジナル検索用scriptタグ』を記述します。</dd>
            <dd>4. ヘッダー/フッターに検索機能を設置する場合にはヘッダー/フッターのいずれかのファイルに『オリジナル検索用scriptタグ』を記述します。</dd>
            </dl>
        </div>
</div>

    <br/>

    <dl>
    <dt>■埋め込みタグ</dt>
    </dl>

    <table class="tb-basic tb-th-2 tb-body-bordered">
        <thead>
        <tr>
            <th>要素名</th>
            <th>オリジナルタグ</th>

            <th >要素名</th>
            <th >オリジナルタグ</th>

            <th >要素名</th>
            <th >オリジナルタグ</th>
        </tr>
        </thead>
        <tbody>

        <?php foreach($ptags as $arr): ?>
            <tr>
                <?php foreach($arr as $tag => $tagTitle): ?>
                    <td><?php echo $tagTitle;?></td>
                    <td>
                        <input type="hidden" value="{<?php echo $tag;?>}" />
                        <span><?php echo $tag;?></span>
                        <span class="copy-icon"><i class="fa fa-files-o" aria-hidden="true"></i></span>
                    </td>
                <?php endforeach;?>
            </tr>
        <?php endforeach; ?>

        </tbody>
    </table>
</div>

<div class="clearfix"></div>
