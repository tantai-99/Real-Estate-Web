@extends('layouts.utility')

@section('title', __('お役立ち│ホームページ作成ツール'))

@section('style')
<link href="/css/utility.css" media="screen" rel="stylesheet" type="text/css">
@endsection

@section('script')
<script type="text/javascript" src="/js/libs/jquery-contained-sticky-scroll-min.js"></script>
<script type="text/javascript">
$(function () {
	$("#main-contents-body").addClass("contents-favicon");
    $('#js-side').containedStickyScroll({
        duration:300
    });
});
</script>
@endsection

@section('content')

      <h2><a href="../utility">お役立ちコンテンツ</a></h2>
      <h3 class="heading-lv1">ファビコン用・ウェブクリップアイコン用画像</h3>
      <p>
        ファビコンとは、Webブラウザのアドレスバーやタブに表示されるアイコンのことです。 <br>
        また、ウェブクリップアイコンとは、スマートフォンのホーム画面に置かれるアイコンのことです。
      </p>
      <p>
        画像を「右クリック」⇒「名前を付けて画像を保存」でご自分のフォルダに保存してご利用ください。
      </p>

      <div class="contents">
        <div class="material-contents">
          <h4 class="heading-lv2" id="house">ファビコン用画像（32×32ピクセル）</h4>
          <ul class="list-favicon">
            <li>
              <img src="/images/utility/favicon_house1.png" alt="">
            </li>
            <li>
              <img src="/images/utility/favicon_house2.png" alt="">
            </li>
            <li>
              <img src="/images/utility/favicon_house3.png" alt="">
            </li>
            <li>
              <img src="/images/utility/favicon_house4.png" alt="">
            </li>
            <li>
              <img src="/images/utility/favicon_house5.png" alt="">
            </li>
            <li>
              <img src="/images/utility/favicon_house6.png" alt="">
            </li>
            <li>
              <img src="/images/utility/favicon_house7.png" alt="">
            </li>
            <li>
              <img src="/images/utility/favicon_house8.png" alt="">
            </li>
            <li>
              <img src="/images/utility/favicon_house9.png" alt="">
            </li>
            <li>
              <img src="/images/utility/favicon_house10.png" alt="">
            </li>
            <li>
              <img src="/images/utility/favicon_house11.png" alt="">
            </li>
            <li>
              <img src="/images/utility/favicon_house12.png" alt="">
            </li>
            <li>
              <img src="/images/utility/favicon_house13.png" alt="">
            </li>
            <li>
              <img src="/images/utility/favicon_house14.png" alt="">
            </li>
            <li>
              <img src="/images/utility/favicon_house15.png" alt="">
            </li>
            <li>
              <img src="/images/utility/favicon_house16.png" alt="">
            </li>
            <li>
              <img src="/images/utility/favicon_mansion1.png" alt="">
            </li>
            <li>
              <img src="/images/utility/favicon_mansion2.png" alt="">
            </li>
            <li>
              <img src="/images/utility/favicon_mansion3.png" alt="">
            </li>
            <li>
              <img src="/images/utility/favicon_mansion4.png" alt="">
            </li>
            <li>
              <img src="/images/utility/favicon_mansion5.png" alt="">
            </li>
            <li>
              <img src="/images/utility/favicon_mansion6.png" alt="">
            </li>
            <li>
              <img src="/images/utility/favicon_mansion7.png" alt="">
            </li>
            <li>
              <img src="/images/utility/favicon_mansion8.png" alt="">
            </li>
            <li>
              <img src="/images/utility/favicon_mansion9.png" alt="">
            </li>
            <li>
              <img src="/images/utility/favicon_mansion10.png" alt="">
            </li>
          </ul>
          <h4 class="heading-lv2" id="mansion">ウェブクリップアイコン用画像（152×152ピクセル）</h4>
          <ul class="list-webclip">
            <li>
              <img src="/images/utility/webclip_house1.png" alt="">
            </li>
            <li>
              <img src="/images/utility/webclip_house2.png" alt="">
            </li>
            <li>
              <img src="/images/utility/webclip_house3.png" alt="">
            </li>
            <li>
              <img src="/images/utility/webclip_house4.png" alt="">
            </li>
            <li>
              <img src="/images/utility/webclip_house5.png" alt="">
            </li>
            <li>
              <img src="/images/utility/webclip_house6.png" alt="">
            </li>
            <li>
              <img src="/images/utility/webclip_house7.png" alt="">
            </li>
            <li>
              <img src="/images/utility/webclip_house8.png" alt="">
            </li>
            <li>
              <img src="/images/utility/webclip_house9.png" alt="">
            </li>
            <li>
              <img src="/images/utility/webclip_house10.png" alt="">
            </li>
            <li>
              <img src="/images/utility/webclip_house11.png" alt="">
            </li>
            <li>
              <img src="/images/utility/webclip_house12.png" alt="">
            </li>
            <li>
              <img src="/images/utility/webclip_house13.png" alt="">
            </li>
            <li>
              <img src="/images/utility/webclip_house14.png" alt="">
            </li>
            <li>
              <img src="/images/utility/webclip_house15.png" alt="">
            </li>
            <li>
              <img src="/images/utility/webclip_house16.png" alt="">
            </li>
            <li>
              <img src="/images/utility/webclip_mansion1.png" alt="">
            </li>
            <li>
              <img src="/images/utility/webclip_mansion2.png" alt="">
            </li>
            <li>
              <img src="/images/utility/webclip_mansion3.png" alt="">
            </li>
            <li>
              <img src="/images/utility/webclip_mansion4.png" alt="">
            </li>
            <li>
              <img src="/images/utility/webclip_mansion5.png" alt="">
            </li>
            <li>
              <img src="/images/utility/webclip_mansion6.png" alt="">
            </li>
            <li>
              <img src="/images/utility/webclip_mansion7.png" alt="">
            </li>
            <li>
              <img src="/images/utility/webclip_mansion8.png" alt="">
            </li>
            <li>
              <img src="/images/utility/webclip_mansion9.png" alt="">
            </li>
            <li>
              <img src="/images/utility/webclip_mansion10.png" alt="">
            </li>
          </ul>
        </div>

        <div class="side-contents" id="js-side">
          <!--
          <ul class="link-page-inner">
            <li>
              <a href="#house">一戸建て系</a>
            </li>
            <li>
              <a href="#mansion">マンション系</a>
            </li>
          </ul>
        -->
          <p class="link-toppage">
            <a href="../utility">お役立ち<br>コンテンツTOP</a>
          </p>
        </div>
      <!-- /contents --></div>
@endsection
