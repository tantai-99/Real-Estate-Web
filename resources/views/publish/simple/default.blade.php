@extends('layouts.default')

@section('title', __('公開設定(簡易設定)'))

@section('style')
<link href="/js/libs/themes/blue/style.css" media="screen" rel="stylesheet" type="text/css">
<style>
    .populate-testsite {
        width: 760px;
        margin: 0 auto;
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
<script type="text/javascript" src="/js/libs/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="/js/publish.js"></script>
<script type="text/javascript">
var $exclusive_error_msg = "<?php echo str_replace("\n", "\\n", getConfigs('publish')->publish->exclusive_error_msg);?>";
var all_upload_flg = <?php echo $view->hp->all_upload_flg; ?>;
var pages = <?php echo json_encode($view->pages); ?>;
</script>
@endsection

<?php
use App\Repositories\HpPage\HpPageRepository;
?>

@section('content')
<div class="main-contents publish">
    <h1>
        公開設定<span>(簡易設定)</span><a href="javascript:void(0)" class="btn-t-gray">詳細設定</a>
    </h1>

    <div class="main-contents-body">
        <div class="section">

            <?php if ($view->hp->all_upload_flg || $view->pages) : ?>
				<?php if (isset($view->plan_date) && $view->plan_date ) : ?>
					<div class="alert-strong">
						プラン変更が <?= $view->plan_date ?> より適用されます。<br />
						下記の未反映ページの内容がプラン変更適用後は消去されてしまうので適用日までにページの公開・もしくは削除をしてください。
					</div>
				<?php endif; ?>

                <?php if ($view->pages): ?>
                    <div class="alert-strong">以下のページが本番に未反映です。</div>
                    <?php if ($view->hasAutoUpdatePage && !$view->hp->all_upload_flg) : ?>
                    <div class="alert-strong">「※自動更新」と表示されたページは公開が必要となるため、チェックを外せません。</div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($view->hp->all_upload_flg) : ?>
                    <div class="alert-strong">公開中のすべてのページに「共通設定」の変更を反映する必要があるため、公開中のページはチェックを外せません。</div>
                <?php endif; ?>

                <?php if($view->displayEstateRequestFlg) : ?>
                    <div class="alert-strong">
                        <span class="request_top_error">
                        「物件検索設定」にて「物件リクエストを利用する」を選択しています。<br>
                        「ページの作成/更新」にて該当の物件種別の「物件リクエスト」フォームを作成してください。
                        </span>
                        <input type="hidden" id="no_request_flg" value="1">
                    </div>
                <?php endif; ?>

            <?php endif; ?>

            <?php if ($view->hasPrereserved) : ?>
                <div class="populate-testsite">
                    <a class="i-s-link" href="/publish/detail?testsite=1">前回テストサイト更新時と同じ設定にする（詳細設定に移動します）</a>
                </div>
            <?php endif; ?>
            <div class="errors" id="error-top"></div>

                <form data-api-action="<?php echo route('default.publish.api_publish'); ?>" method="post" id="publish-form">
                    @csrf
                    <dt id="submit_from-label">&nbsp;</dt>
                    <dd id="submit_from-element">
                        <?php echo $view->form->form('submit_from'); ?>
                    </dd>   
                    <!--通常ページ-->
                    <table class="tb-basic publish-easy" id="table-publish">
                        <thead>
                        <tr>
                            <th class="reflection-title">公開<br><input type="checkbox" class="all-check" <?php echo $view->errorArticle ? '' : 'checked'?>/></th>
                            <th>ページ名（英語表記）</th>
                            <th>新規/修正</th>
							<th>現在</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($view->pages) : ?>
                        <?php foreach ($view->pages as $page) : ?>
                            <?php
                            $autoPublish = false;
                            if ($page['public_flg'] && ($view->hpPageRepository->hasPagination($page['page_type_code']) /*||  $table->isDetailPageType($page['page_type_code'])*/ )) {
                                $autoPublish = true;
                            }; ?>
                            <?php 
                                $class = '';
                                if (in_array($page['page_category_code'], $view->notArticleDisplay) || $view->hpPageRepository->isLinkArticle($page)) {
                                    $class = 'is-hide no-display-article';
                                    if ($page['label'] == 'no_diff') {
                                        $class .= ' no-diff-simple';
                                    }
                                }
                                elseif ($page['label'] == 'no_diff' ) {
                                    $class = 'is-hide';
                                }
                                if ($autoPublish) {
                                    $class .= ' auto-publish';
                                }
                                if ($page['page_type_code'] == HpPageRepository::TYPE_USEFUL_REAL_ESTATE_INFORMATION && $page['label'] == ['check']) {
                                    $class .= ' not-uncheck';
                                }
                            ?>
                            <tr class="<?php echo $class; ?>">
                                <td class="td-check">
                                    <dt>&nbsp;</dt>
                                    <dd>
                                        <?php echo $view->form->form('page_'.$page['id'].'_update'); ?>
                                    </dd>
                                    <span class="is-hide">
                                    <?php echo $view->form->form('page_'.$page['id'].'_new_release_flg'); ?>
                                    <?php echo $view->form->form('page_'.$page['id'].'_new_release_at'); ?>
                                    <?php echo $view->form->form('page_'.$page['id'].'_new_close_flg'); ?>
                                    <?php echo $view->form->form('page_'.$page['id'].'_new_close_at'); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo h($view->publishTitle($page)); ?>
                                    <?php if ($autoPublish) : ?>
                                        <span class="must-publish">※自動更新</span><?php endif; ?>
                                    <div class="errors"></div>
                                </td>
                                <td class="alC">
                                    <?php if ($page['label'] == 'new') : ?><i class="i-l-new">新規</i><?php endif; ?>
                                    <?php if ($page['label'] == 'update') : ?><i class="i-l-update">修正</i><?php endif; ?>
                                    <?php if (!$page['public_flg']):?><i class="is-draft"/><?php endif; ?>
                                </td>
                                <td class="alC">
									<?php if ($page['public_flg']) : ?>公開<?php else : ?>下書き<?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php endif ;?>
                        <?php if ($view->articlePage && isset($view->articlePage['label']) && count(array_intersect($view->articlePage['label'], array('new', 'update'))) > 0 || $view->errorArticle): ?>
                            <tr class="row-article<?php echo $view->mustPublishArticle ? ' auto-publish' : '';?>">
                                <td class="td-check">
                                    <dt></dt>
                                    <dd>
                                        <input type="checkbox" class="update_flg" id="page_article_update" <?php echo $view->errorArticle ? 'disabled="disabled"' : 'checked="checked"';?>>
                                    </dd>
                                </td>
                                <td>
                                    <?php echo $view->articlePage['title'];?>
                                    <div class="errors-article">
                                        <?php if ($view->errorArticle):?>
                                            <p>関連するいずれかのページが未選択です。詳細設定にて設定を変更してください。</p>
                                        <?php endif;?>
                                    </div>
                                    <div class="errors"></div>
                                </td>
                                <td class="alC">
                                    <?php if (in_array('new', $view->articlePage['label'])): ?><i class="i-l-new">新規</i><?php endif; ?>
                                    <?php if (in_array('update', $view->articlePage['label'])) : ?><i class="i-l-update">修正</i><?php endif; ?>
                                    <?php if (!$view->articlePage['public_flg_ar']):?><i class="is-draft"/><?php endif; ?>
                                </td>
                                <td class="alC">
                                    <?php if ($view->articlePage['public_flg']) : ?>公開<?php else : ?>下書き<?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <!--特集-->
                        <?php if (count($view->specialRowset) > 0) : ?>
                        <?php foreach ($view->specialRowset as $row) : ?>
                            <tr>
                                <td class="td-check">
                                    <dt></dt>
                                    <dd>
                                    <?php echo $view->form->getSubForm('special')->form("special_{$row->id}_update"); ?>
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
                                    <?php if (!$row->is_public) : ?><i class="is-draft"/><?php endif; ?>
                                </td>
                                <td class="alC">
									<?php if ($row->is_public) : ?>公開<?php else : ?>下書き<?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php endif ;?>
                        </tbody>
                    </table>

                    <!--初期設定・デザイン・物件検索設定の反映 -->
                    <?php if ($view->hp->all_upload_flg) :?>
                        <p class="table-heading">
                            共通設定の反映
                        </p>
                        <table class="tb-basic publish-easy"<?php if ($view->displayEstateSettingFlg) :?> style="margin-bottom:20px;"<?php endif ;?>>
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
                        </table>
                    <?php endif ;?>
                    <?php if ($view->displayEstateSettingFlg) :?>
                        <p class="table-heading">
                            物件検索設定の反映
                        </p>
                        <table class="tb-basic publish-easy">
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
                    <?php endif ;?>

                    <div class="publish-btn">
                        <?php if (!$view->subsutitute) : ?>
                            <a href="javascript:void(0);" class="btn-t-blue size-l submit-testsite">テストサイトの更新処理に進む</a>
                        <?php endif; ?>
                        <a href="javascript:void(0);" class="btn-t-blue size-l submit-<?php if (!$view->subsutitute) : ?>publish<?php else : ?>publish-subsutitute<?php endif; ?>"><?php if (!$view->subsutitute) : ?>本番サイト<?php else : ?>代行作成テストサイト<?php endif; ?>の公開/更新</a>
                    </div>

                    <?php if (!$view->subsutitute) : ?>
                    <div class="alert-normal">
                        <strong>テストサイトについて</strong><br />
                            <p>テストサイトとは、本番サイトで公開する前にサイト全体の内容を確認することのできる確認用ホームページとなります。<br />
                            「テストサイトの更新処理に進む」をクリックすると、テストサイトの公開・更新ができます。<br />
                            テストサイトの閲覧にはID,パスワードが必要です。<br />
                            IDは本ツールのユーザーIDと同じです。パスワードは初期設定で設定したパスワードです。パスワードは初期設定で変更できます。<br /><br />
                            <?php $siteDomain = getInstanceUser('cms')->getProfile()->getSiteDomain()?>
                            テストサイト：<a target="_blank" href="http://test.<?php echo h($siteDomain)?>">http://test.<?php echo h($siteDomain)?></a></p>
                    </div>
                    <?php endif; ?>
                </form>

        </div>
    </div>
</div>
@endsection
