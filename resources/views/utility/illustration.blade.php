@extends('layouts.utility')

@section('title', __('お役立ち│ホームページ作成ツール'))

@section('style')
<link href="/css/utility.css" media="screen" rel="stylesheet" type="text/css">
@endsection

@section('script')
<script type="text/javascript" src="/js/libs/jquery-contained-sticky-scroll-min.js"></script>
<script type="text/javascript">
$(function () {
    $("#main-contents-body").addClass("contents-illustration");
    $('#js-side').containedStickyScroll({
        duration:300
    });
});

$(window).on('load', function(){
  var kind = '.kind-name';
  $(kind).click(function(){
    var valuekind = $(this).data('value');
    document.getElementById('kind-id').value = valuekind;
    $('#illustration').submit();
  });

  var paging_no = '.page-no';
  $(paging_no).click(function() {
    var valuepage = $(this).data('value');
    document.getElementById('paging-id').value = valuepage;
    $('#illustration').submit();
  });
});
</script>
@endsection

@section('content')

      <h2><a href="../utility">お役立ちコンテンツ</a></h2>

      <h3 class="heading-lv1">イラスト画像素材</h3>
      <p>
        画像を「右クリック」⇒「名前を付けて画像を保存」でご自分のパソコンに保存し、ご利用ください。
      </p>
      <div class="contents">
      <form action="" method="get" id="illustration">
        <div class="material-contents">
        <?php 
            $result = '<h4 class="heading-lv2">';
            $result .= $view->title;
            $result .='</h4><ul class="list-item">';
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
          <?php if ( $view->is_adv_std ):?>
          <ul class="link-page-inner">
          <input type="hidden" name="kind" id="kind-id" value="<?php echo $view->kind?>">
            <li>
              <a href="javascript:;" class="kind-name" data-value="person_a">人物A</a>
            </li>
            <li>
              <a href="javascript:;" class="kind-name" data-value="person_b">人物B</a>
            </li>
            <li>
              <a href="javascript:;" class="kind-name" data-value="person_c">人物C</a>
            </li>
            <li>
              <a href="javascript:;" class="kind-name" data-value="pet">ペット</a>
            </li>
            <li>
              <a href="javascript:;" class="kind-name" data-value="building_a">建物（平面）</a>
            </li>
            <li>
              <a href="javascript:;" class="kind-name" data-value="building_b">建物（立体１）</a>
            </li>
            <li>
              <a href="javascript:;" class="kind-name" data-value="building_c">建物（立体２）</a>
            </li>
            <li>
              <a href="javascript:;" class="kind-name" data-value="building_d">建物（室内）</a>
            </li>
            <li>
              <a href="javascript:;" class="kind-name" data-value="disaster">災害</a>
            </li>
            <li>
              <a href="javascript:;" class="kind-name" data-value="others">その他</a>
            </li>
          </ul>
          <?php endif; ?>
          <p class="link-toppage">
            <a href="../utility">お役立ち<br>コンテンツTOP</a>
          </p>
        </div>
      </form>
      <!-- /contents --></div>
@endsection
