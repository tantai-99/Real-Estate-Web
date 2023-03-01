<div class="section sortable-item-container<?php if($element->getName()=='side'):?> side-content<?php endif;?>" id="section-<?php echo $element->getName()?>" data-section="<?php echo $element->getName()?>">
    <h2 class="top-original-clear"><?php echo h($element->getTitle())?><?php echo $view->toolTip('page_'.$element->getName().'_common_contents')?></h2>

    <?php foreach ($element->getSortedSideLayout() as $sort => $layout):?>
    <?php switch ($layout['id']):
        case config('constants.hp.SIDELAYOUT_ESTATE_RENT'):?>
            <div class="page-element-wrap sortable-item">
                <!-- 賃貸物件検索のリンク -->
                <div class="page-element element-announce">
                    <div class="page-element-header header-wrap">
                        <span class="announce-none-display js-side-common-display-item is-hide">非表示中</span>
                        <h3>賃貸物件検索のリンク</h3>
                        <input class="sort-value" type="hidden" name="sidelayout[<?php echo config('constants.hp.SIDELAYOUT_ESTATE_RENT')?>][sort]" value="<?php echo h($sort)?>"/>
                        <input class="js-side-common-display-value" type="hidden" name="sidelayout[<?php echo config('constants.hp.SIDELAYOUT_ESTATE_RENT')?>][display]" value="<?php echo h($layout['display'])?>"/>
                        <ul class="page-element-menu">
                            <li><a href="javascript:void(0);" class="up-btn"><i class="i-e-up">上へ移動</i></a></li>
                            <li><a href="javascript:void(0);" class="down-btn"><i class="i-e-down">下へ移動</i></a></li>
                            <li class="pull">
                                <a href="javascript:void(0);"><i class="i-e-set">操作</i></a>
                                <ul>
                                    <li><a href="javascript:void(0);" class="js-side-common-display-btn"><i class="i-e-close"></i>表示しない</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
          
                    <div class="page-element-body rent-announce filter-wrap">
                        <div class="filter-none-display js-side-common-display-item is-hide">非表示中</div>
                        <p>「基本設定」タブ内「物件検索設定」にて設定した賃貸物件種目へのリンクが設置されます。</p>
                    </div>
                </div>
                <!-- /賃貸物件検索のリンク -->
            </div>
        <?php break;?>

        <?php case config('constants.hp.SIDELAYOUT_ESTATE_PURCHASE'):?>
            <div class="page-element-wrap sortable-item">
                <!-- 売買物件検索のリンク -->
                <div class="page-element element-announce">
                    <div class="page-element-header header-wrap">
                        <span class="announce-none-display js-side-common-display-item is-hide">非表示中</span>
                        <h3>売買物件検索のリンク</h3>
                        <input class="sort-value" type="hidden" name="sidelayout[<?php echo config('constants.hp.SIDELAYOUT_ESTATE_PURCHASE')?>][sort]" value="<?php echo h($sort)?>"/>
                        <input class="js-side-common-display-value" type="hidden" name="sidelayout[<?php echo config('constants.hp.SIDELAYOUT_ESTATE_PURCHASE')?>][display]" value="<?php echo h($layout['display'])?>"/>
                        <ul class="page-element-menu">
                            <li><a href="javascript:void(0);" class="up-btn"><i class="i-e-up">上へ移動</i></a></li>
                            <li><a href="javascript:void(0);" class="down-btn"><i class="i-e-down">下へ移動</i></a></li>
                            <li class="pull">
                                <a href="javascript:void(0);"><i class="i-e-set">操作</i></a>
                                <ul>
                                    <li><a href="javascript:void(0);" class="js-side-common-display-btn"><i class="i-e-close"></i>表示しない</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
          
                    <div class="page-element-body sell-announce filter-wrap">
                        <div class="filter-none-display js-side-common-display-item is-hide">非表示中</div>
                        <p>「基本設定」タブ内「物件検索設定」にて設定した売買物件種目へのリンクが設置されます。</p>
                    </div>
                </div>
                <!-- /売買物件検索のリンク -->
            </div>
        <?php break;?>

        <?php case config('constants.hp.SIDELAYOUT_OTHER_LINK'):?>
            <div class="page-element-wrap sortable-item">
                <!-- その他のサイドリンク一覧 -->
                <div class="page-element element-announce">
                    <div class="page-element-header header-wrap">
                        <span class="announce-none-display js-side-common-display-item is-hide">非表示中</span>
                        <h3>その他のサイドリンク一覧</h3>
                        <input class="sort-value" type="hidden" name="sidelayout[<?php echo config('constants.hp.SIDELAYOUT_OTHER_LINK')?>][sort]" value="<?php echo h($sort)?>"/>
                        <input class="js-side-common-display-value" type="hidden" name="sidelayout[<?php echo config('constants.hp.SIDELAYOUT_OTHER_LINK')?>][display]" value="<?php echo h($layout['display'])?>"/>
                        <span class="announce-display btn-modal_announce js-side-common-otherlink-notify-btn"><img src="/images/common/icon_announe.png" alt=""></span>
                        <ul class="page-element-menu">
                            <li><a href="javascript:void(0);" class="up-btn"><i class="i-e-up">上へ移動</i></a></li>
                            <li><a href="javascript:void(0);" class="down-btn"><i class="i-e-down">下へ移動</i></a></li>
                            <li class="pull">
                                <a href="javascript:void(0);"><i class="i-e-set">操作</i></a>
                                <ul>
                                    <li><a href="javascript:void(0);" class="js-side-common-display-btn"><i class="i-e-close"></i>表示しない</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>

                    <div class="page-element-body link-announce filter-wrap">
                        <div class="item-header-wrap">
                            <dl class="item-header">
                                <dt>見出し<i class="i-l-require">必須</i></dt>
                                <dd>
                                    <input type="text" name="sidelayout[<?php echo config('constants.hp.SIDELAYOUT_OTHER_LINK')?>][title]" class="watch-input-count" data-maxlength="20" value="<?php echo h($layout['title'])?>"> 
                                    <span class="input-count"></span>
                                    <?php echo $element->form('title') ?>
                                </dd>
                            </dl>
                        </div>
                        <div class="errors"></div>
                        <div class="filter-none-display js-side-common-display-item is-hide">非表示中</div>
                        <div class="box">
                            <div class="box-innter">
                                <p>「ページ作成/更新」にてグローバルメニューの下部に設置しているページへのリンクと、その下層ページへのリンクが設置されます。</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /賃貸物件検索のリンク -->
            </div>
        <?php break;?>

        <?php case config('constants.hp.SIDELAYOUT_CUSTOMIZED_CONTENTS'):?>
            <div class="page-element-wrap sortable-item">
                <div class="page-element element-announce">
                    <div class="page-element-header header-wrap">
                        <span class="announce-none-display js-side-common-display-item is-hide">非表示中</span>
                        <h3>カスタマイズサイドコンテンツ</h3>
                        <input class="sort-value" type="hidden" name="sidelayout[<?php echo config('constants.hp.SIDELAYOUT_CUSTOMIZED_CONTENTS')?>][sort]" value="<?php echo h($sort)?>"/>
                        <input class="js-side-common-display-value" type="hidden" name="sidelayout[<?php echo config('constants.hp.SIDELAYOUT_CUSTOMIZED_CONTENTS')?>][display]" value="<?php echo h($layout['display'])?>"/>
                        <ul class="page-element-menu">
                            <li><a href="javascript:;" class="up-btn"><i class="i-e-up">上へ移動</i></a></li>
                            <li><a href="javascript:;" class="down-btn"><i class="i-e-down">下へ移動</i></a></li>
                            <li class="pull">
                                <a href="javascript:;"><i class="i-e-set">操作</i></a>
                                <ul>
                                    <li><a href="javascript:;" class="js-side-common-display-btn"><i class="i-e-close"></i>表示しない</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <div class="page-element-body filter-wrap sortable-item-container js-side-common">
                        <div class="filter-none-display js-side-common-display-item is-hide">非表示中</div>

                        <?php $subForms = $element->getSubForms()?>
                        <?php foreach ($subForms as $name => $parts):?>
                            <?php // echo $this->partial($parts->getTemplate(), array('element'=>$parts))?>
                            @include($parts->getTemplate(), [
                                'element' => $parts
                            ])
                        <?php endforeach;?>

                        <div class="select-element">
                            <h3>要素挿入<?php echo $view->toolTip('page_insert_parts')?></h3>
                            <div class="select-element-body">
                                <select>
                                    <option value="element1">選択してください</option>
                                </select>
                                <div class="btn-area">
                                    <a class="btn-t-blue size-s" href="javascript:;">追加</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php break;?>
        <?php case config('constants.hp.SIDELAYOUT_ARTICLE_LINK'):?>
            <div class="page-element-wrap sortable-item">
                <!-- その他のサイドリンク一覧 -->
                <div class="page-element element-announce">
                    <div class="page-element-header header-wrap">
                        <span class="announce-none-display js-side-common-display-item is-hide">非表示中</span>
                        <h3>不動産お役立ち情報のリンク</h3>
                        <input class="sort-value" type="hidden" name="sidelayout[<?php echo config('constants.hp.SIDELAYOUT_ARTICLE_LINK')?>][sort]" value="<?php echo h($sort)?>"/>
                        <input class="js-side-common-display-value" type="hidden" name="sidelayout[<?php echo config('constants.hp.SIDELAYOUT_ARTICLE_LINK')?>][display]" value="<?php echo h($layout['display'])?>"/>
                        <ul class="page-element-menu">
                            <li><a href="javascript:void(0);" class="up-btn"><i class="i-e-up">上へ移動</i></a></li>
                            <li><a href="javascript:void(0);" class="down-btn"><i class="i-e-down">下へ移動</i></a></li>
                            <li class="pull">
                                <a href="javascript:void(0);"><i class="i-e-set">操作</i></a>
                                <ul>
                                    <li><a href="javascript:void(0);" class="js-side-common-display-btn"><i class="i-e-close"></i>表示しない</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>

                    <div class="page-element-body link-announce filter-wrap">
                        <div class="item-header-wrap">
                            <dl class="item-header">
                                <dt>見出し<i class="i-l-require">必須</i></dt>
                                <dd>
                                    <input type="text" name="sidelayout[<?php echo config('constants.hp.SIDELAYOUT_ARTICLE_LINK')?>][title]" class="watch-input-count" data-maxlength="20" value="<?php echo h($layout['title'])?>"> 
                                    <span class="input-count"></span>
                                    <?php echo $element->form('article_title') ?>
                                </dd>
                            </dl>
                        </div>
                        <div class="errors"></div>
                        <div class="filter-none-display js-side-common-display-item is-hide">非表示中</div>
                        <div class="box" style="height: 110px;">
                            <div class="box-innter">
                                <p>「ページの作成/更新」内「不動産お役立ち情報」にて設定したお役立ちコンテンツへのリンクが設置されます。<br>
                                「不動産お役立ち情報」ページが公開されていない場合、公開されたサイトにリンクが表示されません。</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /賃貸物件検索のリンク -->
            </div>
        <?php break;?>
    <?php endswitch;?>
    <?php endforeach;?>
</div>
