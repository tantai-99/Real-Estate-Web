@extends('layouts.utility')

@section('title', __('お役立ち│ホームページ作成ツール'))

@section('style')
<link href="/css/utility.css" media="screen" rel="stylesheet" type="text/css">
@endsection

@section('script')
<script type="text/javascript" src="/js/libs/jquery-contained-sticky-scroll-min.js"></script>
<script type="text/javascript">
$(function () {
  $("#main-contents-body").addClass("contents-mainimg");
  $('#js-side').containedStickyScroll({
      duration:300
  });
});

$(window).on('load', function(){
  // 画像サイズ変更
  var select_img = '.select-size input[name=imgsize]';

  $(select_img).change(function(){
    $('#main-image').submit();
  });

  var kind = '.kind-name';
  $(kind).click(function(){
    var valuekind = $(this).data('value');
    document.getElementById('kind-id').value = valuekind;
    $('#main-image').submit();
  });

  var paging_no = '.page-no';
  $(paging_no).click(function() {
    var valuepage = $(this).data('value');
    document.getElementById('paging-id').value = valuepage;
    $('#main-image').submit();
  });
});
</script>
@endsection

@section('content')

  <h2><a href="../utility">お役立ちコンテンツ</a></h2>
  <h3 class="heading-lv1">メインイメージ画像素材</h3>
  <p>画像を「右クリック」⇒「名前を付けて画像を保存」でご自分のパソコンに保存し、ご利用ください。</p>

  <div class="contents">
    <form action="" method="get" id="main-image">
      <div class="material-contents">
      <?php if ( $view->plan >= config('constants.cms_plan.CMS_PLAN_ADVANCE') ) :?>
        <dl class="select-size">
          <dt>横幅</dt>
          <dd>
            <ul>
              <li>
                <label>
                  <input type="radio" name="imgsize" value="720" <?= ( $view->imgsize == 720 ) ? 'checked' : ''  ?>>720px
                </label>
              </li>
              <li>
                <label>
                  <input type="radio" name="imgsize" value="980" <?= ( $view->imgsize == 980 ) ? 'checked' : ''  ?>>980px
                </label>
              </li>
            </ul>
          </dd>
        </dl>
      <?php endif ; ?>

      <?php 
        $result = '<h4 class="heading-lv2">';
        $result .= $view->title;
        $result .='</h4><ul class="list-mainimg mainimg">';
        foreach ( $view->result as $path ) {
          $result .= '<li><img src="' .$path. '" alt=""></li>';
        }
        $result .='</ul>';
        
        echo $result;
      ?>
      <div class="pagination">
        <ul>
          <input type="hidden" name="paging" id="paging-id" value="">
          <?php 
            for ($no = 1; $no <= $view->page ; $no++ ) {
              if ($view->pagingNo == $no) {
                $result = '<li>' .$no. '</li>';
              }else{
                $result = '<li><a href="javascript:;" class="page-no" data-value="' .$no. '">' .$no. '</a></li>';
              }
              echo $result;
            }
          ?>
        </ul>
      </div>

    </div>

    <div class="side-contents" id="js-side">
      <ul class="link-page-inner">
      <input type="hidden" name="kind" id="kind-id" value="<?php echo $view->kind?>">
        <li>
          <a href="javascript:;" class="kind-name" data-value="interior">インテリア</a>
        </li>
        <li>
          <a href="javascript:;" class="kind-name" data-value="person">人物</a>
        </li>
        <li>
          <a href="javascript:;" class="kind-name" data-value="landscape">風景</a>
        </li>
        <li>
          <a href="javascript:;" class="kind-name" data-value="building">建物</a>
        </li>
        <?php if ( $view->plan >=  config('constants.cms_plan.CMS_PLAN_ADVANCE') ) :?>
          <li>
            <a href="javascript:;" class="kind-name" data-value="traffic">交通</a>
          </li>
        <?php endif; ?>
        <li>
          <a href="javascript:;" class="kind-name" data-value="pet">ペット</a>
        </li>
        <?php if ( $view->plan >=  config('constants.cms_plan.CMS_PLAN_ADVANCE') ) :?>
          <li>
            <a href="javascript:;" class="kind-name" data-value="miniature">ミニチュア</a>
          </li>
        <?php endif; ?>
        <li>
          <a href="javascript:;" class="kind-name" data-value="other">その他</a>
        </li>
      </ul>
      <p class="link-toppage">
        <a href="../utility">お役立ち<br>コンテンツTOP</a>
      </p>
    </div>
    </form>
  <!-- /contents --></div>
@endsection