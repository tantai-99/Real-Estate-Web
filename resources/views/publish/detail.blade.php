@extends('layouts.default')

@section('title', __('公開設定(詳細設定)'))

@section('style')
<link href="/js/libs/themes/blue/style.css" media="screen" rel="stylesheet" type="text/css">
<link href="/js/libs/themes/jquery-ui/jquery-ui.min.css" media="screen" rel="stylesheet" type="text/css">
<link href="/js/libs/timepicker/jquery-ui-timepicker-addon.min.css" media="screen" rel="stylesheet" type="text/css">
<link href="/css/publish.css" media="screen" rel="stylesheet" type="text/css">
<style>
    .publish-refine {
        float: left;
    }

    .populate-testsite {
        float: right;
    }
    form {
        clear:both;
    }
    .must-publish {
        color: #00b1c6;
    }
        .td-check dt {
    display: none;
    }
</style>
@endsection
@section('script')
<script type="text/javascript" src="/js/libs/jquery-ui.min.js"></script>
<script type="text/javascript" src="/js/libs/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="/js/libs/themes/jquery-ui/jquery.ui.datepicker-ja.js"></script>
<script type="text/javascript" src="/js/libs/jquery-ui-timepicker-addon.min.js"></script>
<script type="text/javascript" src="/js/publish.js"></script>
<script type="text/javascript">
var all_upload_flg = <?php echo $view->hp->all_upload_flg; ?>;
var pages = <?php echo json_encode($view->pages);?>;
var largeCategoryAllPage = <?php echo json_encode($view->largeCategoryAllPage);?>;
var categories = <?php echo json_encode($view->categories);?>;
var prereservedPages = <?php echo json_encode($view->prereservedPages);?>;

var $exclusive_error_msg = "<?php echo str_replace("\n", "\\n", getConfigs('publish')->publish->exclusive_error_msg);?>";
var has_form_request_livinglease = <?php echo isset($view->hasFormRequestLivinglease) ? $view->hasFormRequestLivinglease : 'false'; ?>;
var has_form_request_officelease = <?php echo isset($view->hasFormRequestOfficelease) ? $view->hasFormRequestOfficelease : 'false'; ?>;
var has_form_request_livingbuy = <?php echo isset($view->hasFormRequestLivingbuy) ? $view->hasFormRequestLivingbuy : 'false'; ?>;
var has_form_request_officebuy = <?php echo isset($view->hasFormRequestOfficebuy) ? $view->hasFormRequestOfficebuy : 'false'; ?>;
</script>
@endsection

<?php
use App\Repositories\HpPage\HpPageRepository;
?>

@section('content')

<div class="main-contents publish">

    <h1>公開設定<span>(詳細設定)</span><a href="javascript:void(0)" class="btn-t-gray">簡易設定</a></h1>

    <div class="main-contents-body">
        <div class="section">

            <?php if ($view->hasUpdate || $view->hp->all_upload_flg || $view->hasAutoUpdatePage) : ?>
				<?php if (isset($view->plan_date) && $view->plan_date ) : ?>
					<div class="alert-strong">
						プラン変更が <?= $view->plan_date ?> より適用されます。<br />
						下記の未反映ページの内容がプラン変更適用後は消去されてしまうので適用日までにページの公開・もしくは削除をしてください。
					</div>
				<?php endif; ?>

                <?php if ($view->hasUpdate) : ?>
                    <div class="alert-strong">
                        <span class="has-diff-msg">以下のページが本番に未反映です。</span>
                    </div>
                <?php endif; ?>

                <?php if ($view->hp->all_upload_flg) : ?>
                    <div class="alert-strong">公開中のすべてのページに「共通設定」の変更を反映する必要があるため、公開中のページはチェックを外せません。</div>
                <?php endif; ?>

                <?php if ($view->hasAutoUpdatePage && !$view->hp->all_upload_flg) : ?>
                    <div class="alert-strong">「※自動更新」と表示されたページは公開が必要となるため、チェックを外せません。</div>
                <?php endif; ?>

                <?php if($view->displayEstateRequestFlg) : ?>
                    <div class="alert-strong">
                        「物件検索設定」にて「物件リクエストを利用する」を選択しています。<br>
                        「ページの作成/更新」にて該当の物件種別の「物件リクエスト」フォームを作成してください。
                        <input type="hidden" id="no_request_flg" value="1">
                    </div>
                <?php endif; ?>

            <?php endif; ?>

            <?php if (count($view->pages) > 0 || count($view->specialRowset) > 0) : ?>
                <div class="errors" id="error-top"></div>
                <ul class="publish-refine">
                    <li class="page-diff-tab is-active"><a href="javascript:void(0);">新規作成/修正</a></li>
                    <li class="page-all-tab"><a href="javascript:void(0);">すべて</a></li>
                </ul>

                <?php if ($view->hasPrereserved) : ?>
                    <div class="populate-testsite">
                        <a class="i-s-link" href="/publish/detail?testsite=1">前回テストサイト更新時と同じ設定にする</a>
                    </div>
                <?php endif; ?>

                <form data-api-action="{{ route('default.publish.api_publish') }}" method="post" id="publish-form">

                    @csrf
                    <table id="table-publish" class="tb-basic publish-detail tablesorter">
                        <thead>
                        <tr>
                            <th class="reflection-title"><input type="checkbox" class="all-check"/></th>
                            <th>ページ名（英語表記）</th>
                            <th>新規/修正</th>
                            <th>現在</th>
                            <th>更新後</th>
                            <th>設定</th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php if (count($view->pages) > 0) : ?>
                            <?php foreach ($view->pages as $page): ?>
                                <?php $autoPublish = $page['public_flg'] && ($view->hpPage->hasPagination($page['page_type_code'])); ?>
                                <?php 
                                $class = '';
                                if (in_array($page['page_category_code'], $view->notArticleDisplay) || $view->hpPage->isLinkArticle($page)) {
                                    $class = 'is-hide no-display-article no-diff';
                                }
                                elseif ($page['label'] == 'no_diff' ) {
                                    $class = 'is-hide no-diff';
                                } else {
                                    $class = 'has-diff';
                                }
                                if ($autoPublish) {
                                    $class .= ' auto-publish';
                                }
                                ?>
                                <tr class="<?php echo $class;?>">
                                    <td class="td-check">
                                        <dt>&nbsp;</dt>
                                        <dd>
                                        <?php if (in_array($page['page_category_code'], $view->notArticleDisplay) || $view->hpPage->isLinkArticle($page)) :?>
                                            <input type="hidden" id="page_<?php echo $page['id']?>_update" name="page[<?php echo $page['id'];?>][update]" value="0" class="update_flg">
                                        <?php else :?>
                                           <?php echo $view->form->form("page_{$page['id']}_update");?>
                                        <?php endif ;?>
                                        </dd>
                                    </td>
                                    <td>
                                        <?php echo h($view->publishTitle($page)); ?>
                                        <?php if ($autoPublish) : ?>
                                            <span class="must-publish">※自動更新</span>
                                        <?php endif; ?>
                                        <div class="errors"></div>
                                    </td>
                                    <td class="alC">
                                        <?php if ($page['label'] === 'new') : ?>
                                            <i class="i-l-new">新規</i>
                                        <?php elseif ($page['label'] === 'update') : ?>
                                            <i class="i-l-update">修正</i>
                                        <?php endif; ?>
                                    </td>
                                    <td class="publish-release current-release alC">
                                    <span class="current-status <?php if ($page['public_flg']) : ?>status-public<?php else : ?>status-draft<?php endif; ?>">
                                        <?php if ($page['public_flg']) : ?>公開<?php else : ?>下書き<?php endif; ?>
                                    </span>
                                        <?php if (isset($page['current_release_at']) && $page['current_release_at']) : ?>
                                            <span class="watch"><?php if($page['label'] === 'new') :?>公開予定<?php else :?>修正反映<?php endif ;?>
                                                <span class="current-release-at"><?php echo $page['current_release_at']; ?></span>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (isset($page['current_close_at']) && $page['current_close_at']) : ?>
                                            <span class="watch">公開終了
                                                <span class="current-close-at"><?php echo $page['current_close_at']; ?></span>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="publish-release new-release alC">
                                        <div class="hidden-params-area is-hide">
                                            <?php foreach (['new_release_flg', 'new_release_at', 'new_close_flg', 'new_close_at'] as $name)  : ?>
                                                <?php echo $view->form->form("page_{$page['id']}_{$name}"); ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    <td class="alC">
                                        <a href="javascript:void(0);" class="btn-t-gray size-s update-setting-btn">変更</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if ($view->articlePage): ?>
                            <tr class="row-article <?php echo count(array_intersect($view->articlePage['label'], array('new', 'update', 'check'))) > 0 ? 'has-diff' : 'is-hide no-diff';?>">
                                <td class="td-check">
                                    <dt></dt>
                                    <dd>
                                        <input type="checkbox" name="page[<?php echo $view->articlePage['id'];?>][update]" class="update_flg" id="page_article_update">
                                    </dd>
                                </td>
                                <td>
                                    <?php echo $view->articlePage['title'];?>
                                    <div class="errors">
                                        <?php if ($view->errorArticle):?>
                                            <p>関連するいずれかのページが未選択です。「変更」ボタンから公開したいカテゴリーと記事を選択してください。</p>
                                        <?php endif;?>
                                    </div>
                                </td>
                                <td  class="alC">
                                    <?php if (in_array('new', $view->articlePage['label'])) : ?>
                                        <i class="i-l-new">新規</i>
                                    <?php endif; ?>
                                    <?php if (in_array('update', $view->articlePage['label'])) : ?>
                                        <i class="i-l-update">修正</i>
                                    <?php endif; ?>
                                </td>
                                <td class="publish-release current-release alC">
                                    <span class="current-status <?php if ($view->articlePage['public_flg']) : ?>status-public<?php else : ?>status-draft<?php endif; ?>">-</span>
                                </td>
                                <td class="publish-release new-release alC">
                                    <span>-</span>
                                </td>
                                <td class="alC">
                                    <a href="javascript:void(0);" class="btn-t-gray size-s update-setting-article-btn">変更</a>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <!--特集-->
                        <?php if (count($view->specialRowset) > 0) : ?>
                            <?php $specialForm = $view->form->getSubForm('special');?>
                            <?php foreach ($view->specialRowset as $row): ?>

                                <tr class="<?php if ($row->publishStatus === config('constants.special_estate.row.PUBLISH_STATUS_NO_DIFF')) : ?>is-hide no-diff<?php else : ?>has-diff<?php endif; ?>">
                                    <td class="td-check">
                                        <dt></dt>
                                        <dd>
                                            <?php echo $specialForm->form("special_{$row->id}_update");?>
                                        </dd>
                                    </td>
                                    <td>
                                        <?php echo h("$row->title ($row->filename)"); ?>
                                        <div class="errors"></div>
                                    </td>
                                    <td class="alC">
                                        <?php if ($row->publishStatus === config('constants.special_estate.row.PUBLISH_STATUS_NEW')) : ?>
                                            <i class="i-l-new">新規</i>
                                        <?php elseif ($row->publishStatus === config('constants.special_estate.row.PUBLISH_STATUS_UPDATE')) : ?>
                                            <i class="i-l-update">修正</i>
                                        <?php endif; ?>
                                    </td>
                                    <td class="publish-release current-release alC">
                                    <span class="current-status <?php if ($row->is_public) : ?>status-public<?php else : ?>status-draft<?php endif; ?>">
                                        <?php if ($row->is_public) : ?>公開<?php else : ?>下書き<?php endif; ?>
                                    </span>
                                      <?php $specialReserve = $specialForm->getElement("special_{$row->id}_new_release_at");?>
                                      <?php if ($specialReserve && $specialReserve->getValue()) : ?>
                                        <span class="watch"><?php if($page['label'] === 'new') :?>公開予定<?php else :?>修正反映<?php endif ;?>
                                          <span class="new-release-at"><?php echo $specialReserve->getValue(); ?></span>
                                        </span>
                                      <?php endif; ?>
                                      <?php $specialReserve = $specialForm->getElement("special_{$row->id}_new_close_at");?>
                                      <?php if ($specialReserve && $specialReserve->getValue()) : ?>
                                        <span class="watch">公開終了
                                          <span class="new-close-at"><?php echo $specialReserve->getValue(); ?></span>
                                        </span>
                                      <?php endif; ?>
                                    </td>
                                    <td class="publish-release new-release alC">
                                        <div class="hidden-params-area is-hide">
                                            <?php foreach (['new_release_flg', 'new_release_at', 'new_close_flg', 'new_close_at'] as $name)  : ?>
                                                <?php echo $specialForm->form("special_{$row->id}_{$name}"); ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    <td class="alC">
                                        <a href="javascript:void(0);" class="btn-t-gray size-s update-setting-btn">変更</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>

                    <!--初期設定・デザイン・物件検索設定の反映 -->
                    <?php if ($view->hp->all_upload_flg) : ?>
                        <p class="table-heading publish-detail">
                            共通設定の反映
                        </p>
                        <table class="tb-basic publish-detail"<?php if ($view->displayEstateSettingFlg) :?> style="margin-bottom:20px;"<?php endif ;?>>
                            <tbody>
                                <?php $all_upload_parts = json_decode($view->hp->all_upload_parts); ?>
                                <?php if ($all_upload_parts->initial) :?>
                                <tr class="bg">
                                    <td>初期設定</td>
                                </tr>
                                <?php endif ;?>
                                <?php if ($all_upload_parts->design) :?>
                                <tr class="bg">
                                    <td>デザイン</td>
                                </tr>
                                <?php endif ;?>
                                <?php if ($all_upload_parts->topside) :?>
                                <tr class="bg">
                                    <td>トップページ サイドコンテンツ（全ページ共通）</td>
                                </tr>
                                <?php endif ;?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                    <?php if ($view->displayEstateSettingFlg) : ?>
                        <p class="table-heading publish-detail">
                            物件検索設定の反映
                        </p>
                        <table class="tb-basic publish-detail">
                            <tbody>
                                <tr class="bg">
                                    <td>
                                    物件検索設定（物件検索トップ、各物件種目トップ、物件問い合わせ）
                                    <?php if( $view->displayEstateRequestFlg ) : ?><br>
                                        <font class="request_bottom_error" color="red" style="display: none;">
                                            ・「物件検索設定」にて「物件リクエストを利用する」を選択しています。<br>
                                            　「ページの作成/更新」にて該当の物件種別の「物件リクエスト」フォームを作成してください。
                                        </font>
                                    <?php endif; ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    <?php endif; ?>

                    <div class="publish-btn" style="position:relative">
                        <?php if (!$view->subsutitute) : ?>
                            <a href="javascript:void(0);" class="btn-t-blue size-l submit-testsite">テストサイトの更新処理に進む</a>
                        <?php endif; ?>
                        <a href="javascript:void(0);" class="btn-t-blue size-l submit-<?php if (!$view->subsutitute) : ?>publish<?php else : ?>publish-subsutitute<?php endif; ?>"><?php if (!$view->subsutitute) : ?>本番サイト<?php else : ?>代行作成テストサイト<?php endif; ?>の公開/更新</a>
                        <p class="i-s-link" style="cursor:pointer;width:100%;text-align:right;position:absolute;top:-40px;"><a class="all2publish">すべてのページを即時公開に変更する</a></p>
                    </div>

                    <?php if (!$view->subsutitute) : ?>
                    <div class="alert-normal">
                        <strong>テストサイトについて</strong><br/>
                        <p>テストサイトとは、本番サイトで公開する前にサイト全体の内容を確認することのできる確認用ホームページとなります。<br/>
                            「テストサイトの更新処理に進む」をクリックすると、テストサイトの公開・更新ができます。<br/>
                            テストサイトの閲覧にはID,パスワードが必要です。<br/>
                            IDは本ツールのユーザーIDと同じです。パスワードは初期設定で設定したパスワードです。パスワードは初期設定で変更できます。<br/><br/>
                            <?php $siteDomain = getInstanceUser('cms')->getProfile()->getSiteDomain() ?>
                            テストサイト：<a target="_blank" href="http://test.<?php echo h($siteDomain) ?>">http://test.<?php echo h($siteDomain) ?></a>
                        </p>
                    </div>
                    <div class="page-element-body">
                        <div class="btn-right">
                            <a class="btn-t-gray site-delete" href="javascript:void(0);" data-api-action="<?php echo urlSimple('site-delete', 'publish');?>">本番サイトを非公開にする</a>
                        </div>
                    </div>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <div id="template">
        <div class="modal-content-publish js-scroll-container" data-scroll-container-max-height="550">
            <?php if ($view->hp->all_upload_flg) : ?>
                <div class="alert-strong">公開中のすべてのページに「共通設定」の変更を反映する必要があるため、公開中のページはチェックを外せません。</div>
            <?php else : ?>
                <div class="alert-strong is-hide"></div>
            <?php endif; ?>
            <div class="modal-publish-header">
                <h2>公開／非公開の選択<?php echo $view->toolTip('modal_publish_option')?></h2>
                <div class="publish-option">
                    <label><input type="radio" name="publish_option" value="release">公開（更新）する</label>
                    <label><input type="radio" name="publish_option" value="close">非公開（下書き）にする</label>
                </div>
            </div>
            <form data-api-action="{{ route('default.publish.api_publish') }}" method="post" id="setting-article-form">

            @csrf
            <div class="content-publish">
                <h2>対象ページの選択<?php echo $view->toolTip('modal_publish_page')?></h2>
                <div class="list-large-catagory">
                    <ul>
                    <?php foreach($view->largeCategoryAllPage as $large) :?>
                        <?php $largePage = $view->hpPage->filterPageByType($view->pages, $large);?>
                        <?php if ($large == HpPageRepository::TYPE_LARGE_ORIGINAL) : ?>
                            <?php for ($i = 1; $i <= HpPageRepository::MAX_ORIGINAL_LARGE; $i++) : ?>
                            <?php $dataId = isset($largePage[$i -1]) ? $largePage[$i -1]['id'] : 0;?>
                            <li class="item-large"><a data-id="<?php echo $dataId;?>"><?php echo $view->hpPage->getCategories()[HpPageRepository::CATEGORY_LARGE].$i;?></a></li>
                            <?php endfor; ?>
                        <?php else :?>
                        <?php if(array_key_exists($large, $view->pageMapArticle)) : ?>
                            <?php $dataId = $largePage ? $largePage[0]['id'] : 0;?>
                            <li class="item-large"><a data-id="<?php echo $dataId;?>"><?php echo $view->hpPage->getTypeNameJp($large);?></a></li>
                        <?php else : ?>
                            <li class="not-used item-large"><span><?php echo $view->hpPage->getTypeNameJp($large);?></span></li>
                        <?php endif; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </ul>
                </div>
                <table class="tb-basic list-category-article publish-modal">
                    <thead>
                        <tr>
                            <th><input type="checkbox" class="all-check"></th>
                            <th>分類<?php echo $view->toolTip('category_classification_legend')?></th>
                            <th>ページ名（英語表記）</th>
                            <th>新規/修正</th>
                            <th>現在</th>
                        </tr>
                    </thead>
                    <tbody class="body-article-top">
                        <?php $issetPage = false;?>
                        <?php $listLinkArticleTop = array();?>
                        <?php foreach($view->pages as $page) :?>
                        <?php if ($page['page_type_code'] == HpPageRepository::TYPE_USEFUL_REAL_ESTATE_INFORMATION) :?>
                        <?php $issetPage = true;?>
                        <tr>
                            <td>
                                <input type="checkbox" id="page_<?php echo $page['id']?>_update" class="update_flg">
                                <input type="hidden" name="page[<?php echo $page['id']?>][update]" class="update-hidden">
                                <div class="hidden-params-area is-hide">
                                <?php foreach (['new_release_flg', 'new_release_at', 'new_close_flg', 'new_close_at'] as $name)  : ?>
                                    <?php echo $view->form->form("page_{$page['id']}_{$name}"); ?>
                                <?php endforeach; ?>
                                </div>
                            </td>
                            <td>
                                <span class="icon-article-top">トップ</span>
                            </td>
                            <td>
                                <?php echo h($view->publishTitle($page)); ?><?php echo $view->toolTip('modal_publish_page_article_top')?>
                                <div class="errors"></div>
                            </td>
                            <td class="alC">
                                <?php if ($page['label'] === 'new') : ?>
                                    <i class="i-l-new">新規</i>
                                <?php elseif ($page['label'] === 'update') : ?>
                                    <i class="i-l-update">修正</i>
                                <?php endif; ?>
                            </td>
                            <?php if ($page['public_flg']) : ?>
                            <?php $statusClass = 'status-public';?>
                            <?php else :?>
                            <?php $statusClass = 'status-draf';?>
                            <?php endif; ?>
                            <td class="<?php echo $statusClass;?>"><?php if ($page['public_flg']) : ?>公開<?php else : ?>下書き<?php endif; ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($view->hpPage->isLinkArticle($page, true)) :?>
                            <?php $listLinkArticleTop[] = $page;?>
                        <?php endif; ?>
                        <?php endforeach; ?>
                        <?php if (count($listLinkArticleTop) > 0) :?>
                            <?php foreach($listLinkArticleTop as $page) :?>
                            <?php $issetPage = true;?>
                            <tr>
                                <td>
                                    <input type="checkbox" id="page_<?php echo $page['id']?>_update" class="update_flg">
                                    <input type="hidden" name="page[<?php echo $page['id']?>][update]" class="update-hidden">
                                    <div class="hidden-params-area is-hide">
                                    <?php foreach (['new_release_flg', 'new_release_at', 'new_close_flg', 'new_close_at'] as $name)  : ?>
                                        <?php echo $view->form->form("page_{$page['id']}_{$name}"); ?>
                                    <?php endforeach; ?>
                                    </div>
                                </td>
                                <td>
                                <span class="icon-article-top">トップ</span>
                                </td>
                                <td>
                                    <?php echo h($view->publishTitle($page)); ?>
                                    <div class="errors"></div>
                                </td>
                                <td class="alC">
                                    <?php if ($page['label'] === 'new') : ?>
                                        <i class="i-l-new">新規</i>
                                    <?php elseif ($page['label'] === 'update') : ?>
                                        <i class="i-l-update">修正</i>
                                    <?php endif; ?>
                                </td>
                                <?php if ($page['public_flg']) : ?>
                            <?php $statusClass = 'status-public';?>
                            <?php else :?>
                            <?php $statusClass = 'status-draf';?>
                            <?php endif; ?>
                            <td class="<?php echo $statusClass;?>"><?php if ($page['public_flg']) : ?>公開<?php else : ?>下書き<?php endif; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        <?php if (!$issetPage):?>
                            <tr>
                                <td colspan="5">
                                    以下記事の上位カテゴリーページが作成されておりません。「ページの作成/更新」内「不動産お役立ち情報」にてカテゴリーページを作成してください。
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <thead>
                        <tr>
                            <th><input type="checkbox" class="all-check"></th>
                            <th>分類<?php echo $view->toolTip('category_classification_legend')?></th>
                            <th>ページ名（英語表記）</th>
                            <th>新規/修正</th>
                            <th>現在</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <input type="hidden" name="clickBtn" value="setting-publish-article">
            </form>
        </div>
        <div class="btns-modal">
            <a class="btn-t-gray btn-cancel" href="javascript:;">キャンセル</a>
            <a class="btn-t-blue btn-ok" href="javascript:;">決定する</a>
        </div>
    </div>
</div>
@endsection
