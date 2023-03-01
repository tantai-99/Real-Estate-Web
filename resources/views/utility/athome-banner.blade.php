@extends('layouts.utility')

@section('title', __('お役立ち│ホームページ作成ツール'))

@section('style')
<link href="/css/utility.css" media="screen" rel="stylesheet" type="text/css">
@endsection

@section('script')
<script type="text/javascript" src="/js/libs/jquery-contained-sticky-scroll-min.js"></script>
<script type="text/javascript">
$(function () {
    $("#main-contents-body").addClass("contents-banner");
    $('#js-side').containedStickyScroll({
        duration:300
    });
});
</script>
@endsection

@section('content')

    <h2><a href="../">お役立ちコンテンツ</a></h2>
    <h3 class="heading-lv1">アットホームバナー素材</h3>
    <p>画像を「右クリック」⇒「名前を付けて画像を保存」でご自分のパソコンに保存し、ご利用ください。</p>

      <div class="contents">
        <div class="material-contents">
            <h4 class="heading-lv2" id="athome_site">不動産情報サイトアットホーム</h4>
            <p>リンクはアットホームサイトのトップページにお願いいたします。
            <br>URL <a href="https://www.athome.co.jp/" target="_brank">https://www.athome.co.jp/</a></p>
            
            <h4 class="banner-line">1列用</h4> 
            <ul class="contents-banner-list athome-banner">
                <li><img src="/images/utility/athome_banner/athome_site_718_01.png" alt=""></li>
                <li><img src="/images/utility/athome_banner/athome_site_718_02.png" alt=""></li>
                <li><img src="/images/utility/athome_banner/athome_site_718_03.png" alt=""></li>
                <li><img src="/images/utility/athome_banner/athome_site_718_04.png" alt=""></li>
            </ul>
            <h4 class="banner-line">2列用</h4>
            <ul class="contents-banner-list athome-banner">
                <li><img src="/images/utility/athome_banner/athome_site_658_01.png" alt=""></li>
                <li><img src="/images/utility/athome_banner/athome_site_658_02.png" alt=""></li>
                <li><img src="/images/utility/athome_banner/athome_site_658_03.png" alt=""></li>
                <li><img src="/images/utility/athome_banner/athome_site_658_04.png" alt=""></li>
            </ul>
            <h4 class="banner-line">3列用</h4>
            <ul class="contents-banner-list athome-banner-l">
                <li><img src="/images/utility/athome_banner/athome_site_432_01.png" alt=""></li>
                <li><img src="/images/utility/athome_banner/athome_site_432_02.png" alt=""></li>
                <li><img src="/images/utility/athome_banner/athome_site_432_03.png" alt=""></li>
                <li><img src="/images/utility/athome_banner/athome_site_432_04.png" alt=""></li>
            </ul>
            <h4 class="banner-line">サイドエリア用</h4>
            <ul class="contents-banner-list athome-banner-l">
                <li><img src="/images/utility/athome_banner/athome_site_440_01.png" alt=""></li>
                <li><img src="/images/utility/athome_banner/athome_site_440_02.png" alt=""></li>
                <li><img src="/images/utility/athome_banner/athome_site_440_03.png" alt=""></li>
                <li><img src="/images/utility/athome_banner/athome_site_440_04.png" alt=""></li>
            </ul>

            <h4 class="heading-lv2" id="athome_kameiten">会員ページリンク用</h4>
            <h4 class="banner-line">1列用</h4> 
            <ul class="contents-banner-list athome-banner">
                <li><img src="/images/utility/athome_banner/athome_kameiten_718_01.png" alt=""></li>
                <li><img src="/images/utility/athome_banner/athome_kameiten_718_02.png" alt=""></li>
                <li><img src="/images/utility/athome_banner/athome_kameiten_718_03.png" alt=""></li>
                <li><img src="/images/utility/athome_banner/athome_kameiten_718_04.png" alt=""></li>
            </ul>
            <h4 class="banner-line">2列用</h4>
            <ul class="contents-banner-list athome-banner-l">
                <li><img src="/images/utility/athome_banner/athome_kameiten_658_01.png" alt=""></li>
                <li><img src="/images/utility/athome_banner/athome_kameiten_658_02.png" alt=""></li>
                <li><img src="/images/utility/athome_banner/athome_kameiten_658_03.png" alt=""></li>
                <li><img src="/images/utility/athome_banner/athome_kameiten_658_04.png" alt=""></li>
            </ul>
            <h4 class="banner-line">3列用</h4>
            <ul class="contents-banner-list athome-banner-l">
                <li><img src="/images/utility/athome_banner/athome_kameiten_432_01.png" alt=""></li>
                <li><img src="/images/utility/athome_banner/athome_kameiten_432_02.png" alt=""></li>
                <li><img src="/images/utility/athome_banner/athome_kameiten_432_03.png" alt=""></li>
                <li><img src="/images/utility/athome_banner/athome_kameiten_432_04.png" alt=""></li>
            </ul>
            <h4 class="banner-line">サイドエリア用</h4>
            <ul class="contents-banner-list athome-banner-l">
                <li><img src="/images/utility/athome_banner/athome_kameiten_440_01.png" alt=""></li>
                <li><img src="/images/utility/athome_banner/athome_kameiten_440_02.png" alt=""></li>
                <li><img src="/images/utility/athome_banner/athome_kameiten_440_03.png" alt=""></li>
                <li><img src="/images/utility/athome_banner/athome_kameiten_440_04.png" alt=""></li>
            </ul>
          </div>



        <div class="side-contents" id="js-side">

          <ul class="link-page-inner">
            <li>
              <a href="#athome_site">不動産サイトアットホーム用</a>
            </li>
            <li>
              <a href="#athome_kameiten">会員ページ用</a>
            </li>
          </ul>

          <p class="link-toppage">
            <a href="../utility">お役立ち<br>コンテンツTOP</a>
          </p>
        </div>
      <!-- /contents --></div>
@endsection