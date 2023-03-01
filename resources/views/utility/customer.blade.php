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

<h2><a href="../">お役立ちコンテンツ</a></h2>
      <h3 class="heading-lv1">お客様サイト用　リンク素材</h3>
      <p>ホームページ上で「お客様サイト」コンテンツへのリンクをご希望の際には、バナーの上で「右クリック」⇒「名前を付けて画像を保存」でご自分のパソコンに保存し、ご利用ください。</p>
      <div class="contents">
        <div class="material-contents">
          <h4 class="heading-lv2">新規登録画面用</h4>
          <h5 class="heading-lv3">メインイメージ用(720×320ピクセル)</h5>
          <ul class="list-customer-l">
            <li><img src="/images/utility/advance-main-blue-B.jpg" alt=""></li>
            <li><img src="/images/utility/advance-main-red-B.jpg" alt=""></li>
            <li><img src="/images/utility/advance-main-blue-A.jpg" alt=""></li>
            <li><img src="/images/utility/advance-main-red-A.jpg" alt=""></li>
          </ul>
          <h5 class="heading-lv3">サイドコンテンツ用(176×132ピクセル)</h5>
          <ul class="list-customer">
            <li><img src="/images/utility/advance-side-blue-D.jpg" alt=""></li>
            <li><img src="/images/utility/advance-side-green-D.jpg" alt=""></li>
            <li><img src="/images/utility/advance-side-orange-D.jpg" alt=""></li>
          </ul>
          <h5 class="heading-lv3">サイドコンテンツ用(176×52ピクセル)</h5>
          <ul class="list-customer">
            <li><img src="/images/utility/advance-side-blue-A.jpg" alt=""></li>
            <li><img src="/images/utility/advance-side-green-A.jpg" alt=""></li>
            <li><img src="/images/utility/advance-side-orange-A.jpg" alt=""></li>
          </ul>
          <h4 class="heading-lv2">ログイン画面(新規登録画面へのリンクなし)用</h4>
          <h5 class="heading-lv3">サイドコンテンツ用(176×52ピクセル)</h5>
          <ul class="list-customer">
            <li><img src="/images/utility/advance-side-blue-B.jpg" alt=""></li>
            <li><img src="/images/utility/advance-side-green-B.jpg" alt=""></li>
            <li><img src="/images/utility/advance-side-orange-B.jpg" alt=""></li>
          </ul>
          <h4 class="heading-lv2">ログイン画面(新規登録画面へのリンクあり)用</h4>
          <h5 class="heading-lv3">サイドコンテンツ用(176×132ピクセル)</h5>
          <ul class="list-customer">
            <li><img src="/images/utility/advance-side-blue-E.jpg" alt=""></li>
            <li><img src="/images/utility/advance-side-green-E.jpg" alt=""></li>
            <li><img src="/images/utility/advance-side-orange-E.jpg" alt=""></li>
          </ul>
          <h5 class="heading-lv3">サイドコンテンツ用(176×52ピクセル)</h5>
          <ul class="list-customer">
            <li><img src="/images/utility/advance-side-blue-C.jpg" alt=""></li>
            <li><img src="/images/utility/advance-side-green-C.jpg" alt=""></li>
            <li><img src="/images/utility/advance-side-orange-C.jpg" alt=""></li>
          </ul>
          <div class="annotation">
            <p>※留意点</p>
            <ul>
              <li>・「お客さまサイト」用バナーを掲載される際、バナーサイズや色味等のデザインを改変することはお断りしております。</li>
              <li>・コンテンツ内容は予告なく変更する場合がありますので、予めご了承くださいますようお願い申し上げます。</li>
            </ul>
          </div>
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
            <a href="../">お役立ち<br>コンテンツTOP</a>
          </p>
        </div>
      <!-- /contents --></div>
@endsection
