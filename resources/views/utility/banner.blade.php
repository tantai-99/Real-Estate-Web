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

      <h2><a href="../utility">お役立ちコンテンツ</a></h2>

      <h3 class="heading-lv1">住宅ローンシミュレーションバナーリンク素材</h3>
      <p>ホームページ上で「住宅ローンシミュレーション」コンテンツへのリンクをご希望の際には、バナーの上で「右クリック」⇒「名前を付けて画像を保存」でご自分のパソコンに保存し、下記URLをリンク先として設置してください。</p>
      <div class="contents">
          <div class="material-contents">
              <h4 class="heading-lv2">リンク先URL</h4>
              <p>https://www.athome.co.jp/contents/shikin/</p>
              <h4 class="heading-lv2">メインコンテンツ用バナー（1列用）
              </h4>
              <ul class="contents-banner-list">
                <li><img src="/images/utility/banner/loan_bn2_718×150_2.jpg" alt=""></li>
                <li><img src="/images/utility/banner/loan_bn2_718×150.jpg" alt=""></li>
                <li><img src="/images/utility/banner/loan_bn1_718_150_blue.jpg" alt=""></li>
                <li><img src="/images/utility/banner/loan_bn1_718_150_gray.jpg" alt=""></li>
                <li><img src="/images/utility/banner/loan_bn3_718_110.jpg" alt=""></li>
              </ul>
              <h4 class="heading-lv2">メインコンテンツ用バナー（2列用）
              </h4>
              <ul class="contents-banner-list">
                <li><img src="/images/utility/banner/loan_bn2_658×200.jpg" alt=""></li>
                <li><img src="/images/utility/banner/loan_bn2_658×300.jpg" alt=""></li>
                <li><img src="/images/utility/banner/loan_bn1_658_300_blue.jpg" alt=""></li>
                <li><img src="/images/utility/banner/loan_bn1_658_300_gray.jpg" alt=""></li>
                <li><img src="/images/utility/banner/loan_bn3_658_200.jpg" alt=""></li>
              </ul>
              <h4 class="heading-lv2">メインコンテンツ用バナー（3列用）
              </h4>
              <ul class="contents-banner-list">
                <li><img src="/images/utility/banner/loan_bn2_432×300.jpg" alt=""></li>
                <li><img src="/images/utility/banner/loan_bn1_432_200_blue.jpg" alt=""></li>
                <li><img src="/images/utility/banner/loan_bn1_432_200_gray.jpg" alt=""></li>
                <li><img src="/images/utility/banner/loan_bn3_432_200.jpg" alt=""></li>
              </ul>
              <h4 class="heading-lv2">サイドエリア用バナー</h4>
              <ul class="contents-banner-list">
                <li><img src="/images/utility/banner/loan_bn2_440×400.jpg" alt=""></li>
                <li><img src="/images/utility/banner/loan_bn1_440_300_blue.jpg" alt=""></li>
                <li><img src="/images/utility/banner/loan_bn1_440_300_gray.jpg" alt=""></li>
                <li><img src="/images/utility/banner/loan_bn3_440_250.jpg" alt=""></li>
              </ul>
              <div class="annotation">
                  <p>※留意点</p>
                  <ul>
                      <li>・「住宅ローンシミュレーション」バナーを掲載される際、バナーサイズや色味等のデザインを改変することはお断りしております。</li>
                      <li>・コンテンツ内容は予告なく変更する場合がありますので、予めご了承くださいますようお願い申し上げます。</li>
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
                  <a href="../utility">お役立ち<br>コンテンツTOP</a>
              </p>
          </div>
      <!-- /contents --></div>
@endsection
