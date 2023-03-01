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

<h3 class="heading-lv1">スマート申込専用ページリンク用【専用バナー】素材</h3>
<p>仲介会社さまに対して「入居申込のWeb化」についてのご案内ができるページへのリンク用【専用バナー】をご用意いたしました。
    <br>
    「ホームページ用」「仲介会社専用ホームページ用」の2種類を用意しております。
    <br>
    画像を「右クリック」➡「名前を付けて画像を保存」でデスクトップに保存し、ご利用ください。
</p>
<div class="contents">
	<div class="material-contents">
		<h4 class="heading-lv2" id="smart-application">リンク先URL（入居申込Web化についてのご案内ページ）</h4>
        <p class="smart-application-desc">リンク先URLは、以下よりお好みのタイプと配色をご利用ください</p><br>
        <p class="smart-application-desc">
            タイプ１：『入居申込Web切替案内ページ』<br>
            　　　　　※ Faxによる申込受付をとりやめ全てWebからの受付に変更を告知
        </p>
        <ul class="smart-application-color">
            <a href="https://atbb.athome.jp/smart-mskm/introduction/color1.html" target="_blank" class="smart-application-href">　<u>ブルー</u> ・</a>
            <a href="https://atbb.athome.jp/smart-mskm/introduction/color2.html" target="_blank" class="smart-application-href"><u>グリーン</u> ・</a>
            <a href="https://atbb.athome.jp/smart-mskm/introduction/color3.html" target="_blank" class="smart-application-href"><u>グレー</u> ・</a>
            <a href="https://atbb.athome.jp/smart-mskm/introduction/color4.html" target="_blank" class="smart-application-href"><u>オレンジ</u> ・</a>
            <a href="https://atbb.athome.jp/smart-mskm/introduction/color5.html" target="_blank" class="smart-application-href"><u>ピンク</u> ・</a>
            <a href="https://atbb.athome.jp/smart-mskm/introduction/color6.html" target="_blank" class="smart-application-href"><u>レッド</u> ・</a>
            <a href="https://atbb.athome.jp/smart-mskm/introduction/color7.html" target="_blank" class="smart-application-href"><u>パープル</u></a>
        </ul>
        <p class="smart-application-desc">
            タイプ２：『入居申込Web受付案内ページ』<br>
            　　　　　※ Webからの申込みを受け付けていることを告知
        </p>
        <ul class="smart-application-color">
            <a href="https://atbb.athome.jp/smart-mskm/introduction_v2/color1.html" target="_blank" class="smart-application-href">　<u>ブルー</u> ・</a>
            <a href="https://atbb.athome.jp/smart-mskm/introduction_v2/color2.html" target="_blank" class="smart-application-href"><u>グリーン</u> ・</a>
            <a href="https://atbb.athome.jp/smart-mskm/introduction_v2/color3.html" target="_blank" class="smart-application-href"><u>グレー</u> ・</a>
            <a href="https://atbb.athome.jp/smart-mskm/introduction_v2/color4.html" target="_blank" class="smart-application-href"><u>オレンジ</u> ・</a>
            <a href="https://atbb.athome.jp/smart-mskm/introduction_v2/color5.html" target="_blank" class="smart-application-href"><u>ピンク</u> ・</a>
            <a href="https://atbb.athome.jp/smart-mskm/introduction_v2/color6.html" target="_blank" class="smart-application-href"><u>レッド</u> ・</a>
            <a href="https://atbb.athome.jp/smart-mskm/introduction_v2/color7.html" target="_blank" class="smart-application-href"><u>パープル</u></a>
        </ul>
        <h4 class="heading-lv2" id="homepage-banner">ホームページ用【専用バナー】</h4>
		<h5 class="heading-lv3">1列用</h5>
		<ul class="contents-banner-list">
			<li><img src="/images/utility/smart_application/blue_718_89_a.png" alt=""></li>
			<li><img src="/images/utility/smart_application/green_718_89_a.png" alt=""></li>
			<li><img src="/images/utility/smart_application/gray_718_89_a.png" alt=""></li>
            <li><img src="/images/utility/smart_application/orange_718_89_a.png" alt=""></li>
            <li><img src="/images/utility/smart_application/pink_718_89_a.png" alt=""></li>
            <li><img src="/images/utility/smart_application/red_718_89_a.png" alt=""></li>
            <li><img src="/images/utility/smart_application/violet_718_89_a.png" alt=""></li>
		</ul>
        <hr class="horizon">
        <h5 class="heading-lv3">2列用</h5>
		<ul class="contents-banner-list two-column-img-wrap">
            <div class="two-column-img">
                <li><img src="/images/utility/smart_application/blue_658_166_a.png" alt=""></li>
                <li><img src="/images/utility/smart_application/green_658_166_a.png" alt=""></li>
            </div>
            <div class="two-column-img">
			    <li><img src="/images/utility/smart_application/gray_658_166_a.png" alt=""></li>
                <li><img src="/images/utility/smart_application/orange_658_166_a.png" alt=""></li>
            </div>
            <div class="two-column-img">
                <li><img src="/images/utility/smart_application/pink_658_166_a.png" alt=""></li>
                <li><img src="/images/utility/smart_application/red_658_166_a.png" alt=""></li>
            </div>
            <div class="two-column-img">
                <li><img src="/images/utility/smart_application/violet_658_166_a.png" alt=""></li>
            </div>
		</ul>
        <hr class="horizon">
        <h5 class="heading-lv3">3列用</h5>
		<ul class="contents-banner-list three-column-img-wrap">
            <div class="three-column-img">
                <li><img src="/images/utility/smart_application/blue_432_165_a.png" alt=""></li>
                <li><img src="/images/utility/smart_application/green_432_165_a.png" alt=""></li>
                <li><img src="/images/utility/smart_application/gray_432_165_a.png" alt=""></li>
            </div>
            <div class="three-column-img">
                <li><img src="/images/utility/smart_application/orange_432_165_a.png" alt=""></li>
                <li><img src="/images/utility/smart_application/pink_432_165_a.png" alt=""></li>
                <li><img src="/images/utility/smart_application/red_432_165_a.png" alt=""></li>
            </div>
            <div class="three-column-img">
                <li><img src="/images/utility/smart_application/violet_432_165_a.png" alt=""></li>
            </div>
        </ul>
        <hr class="horizon">
        <h5 class="heading-lv3">サイドコンテンツ用</h5>
		<ul class="contents-banner-list">
            <li><img src="/images/utility/smart_application/blue_440_165_a.png" alt=""></li>
            <li><img src="/images/utility/smart_application/green_440_165_a.png" alt=""></li>
            <li><img src="/images/utility/smart_application/gray_440_165_a.png" alt=""></li>
            <li><img src="/images/utility/smart_application/orange_440_165_a.png" alt=""></li>
            <li><img src="/images/utility/smart_application/pink_440_165_a.png" alt=""></li>
            <li><img src="/images/utility/smart_application/red_440_165_a.png" alt=""></li>
            <li><img src="/images/utility/smart_application/violet_440_165_a.png" alt=""></li>
		</ul>

        <h4 class="heading-lv2" id="mediation-homepage-banner">仲介会社専用ホームページ用【専用バナー】</h4>
        <h5 class="heading-lv3">1列用</h5>
        <ul class="contents-banner-list">
            <li><img src="/images/utility/smart_application/blue_718_89_b.png" alt=""></li>
            <li><img src="/images/utility/smart_application/green_718_89_b.png" alt=""></li>
            <li><img src="/images/utility/smart_application/gray_718_89_b.png" alt=""></li>
            <li><img src="/images/utility/smart_application/orange_718_89_b.png" alt=""></li>
            <li><img src="/images/utility/smart_application/pink_718_89_b.png" alt=""></li>
            <li><img src="/images/utility/smart_application/red_718_89_b.png" alt=""></li>
            <li><img src="/images/utility/smart_application/violet_718_89_b.png" alt=""></li>
        </ul>
        <hr class="horizon">
        <h5 class="heading-lv3">2列用</h5>
        <ul class="contents-banner-list two-column-img-wrap">
            <div class="two-column-img">
                <li><img src="/images/utility/smart_application/blue_658_166_b.png" alt=""></li>
                <li><img src="/images/utility/smart_application/green_658_166_b.png" alt=""></li>
            </div>
            <div class="two-column-img">
                <li><img src="/images/utility/smart_application/gray_658_166_b.png" alt=""></li>
                <li><img src="/images/utility/smart_application/orange_658_166_b.png" alt=""></li>
            </div>
            <div class="two-column-img">
                <li><img src="/images/utility/smart_application/pink_658_166_b.png" alt=""></li>
                <li><img src="/images/utility/smart_application/red_658_166_b.png" alt=""></li>
            </div>
            <div class="two-column-img">
                <li><img src="/images/utility/smart_application/violet_658_166_b.png" alt=""></li>
            </div>
        </ul>
        <hr class="horizon">
        <h5 class="heading-lv3">3列用</h5>
        <ul class="contents-banner-list three-column-img-wrap">
            <div class="three-column-img">
                <li><img src="/images/utility/smart_application/blue_432_165_b.png" alt=""></li>
                <li><img src="/images/utility/smart_application/green_432_165_b.png" alt=""></li>
                <li><img src="/images/utility/smart_application/gray_432_165_b.png" alt=""></li>
            </div>
            <div class="three-column-img">
                <li><img src="/images/utility/smart_application/orange_432_165_b.png" alt=""></li>
                <li><img src="/images/utility/smart_application/pink_432_165_b.png" alt=""></li>
                <li><img src="/images/utility/smart_application/red_432_165_b.png" alt=""></li>
            </div>
            <div class="three-column-img">
                <li><img src="/images/utility/smart_application/violet_432_165_b.png" alt=""></li>
            </div>
        </ul>
        <hr class="horizon">
        <h5 class="heading-lv3">サイドコンテンツ用</h5>
        <ul class="contents-banner-list">
            <li><img src="/images/utility/smart_application/blue_440_165_b.png" alt=""></li>
            <li><img src="/images/utility/smart_application/green_440_165_b.png" alt=""></li>
            <li><img src="/images/utility/smart_application/gray_440_165_b.png" alt=""></li>
            <li><img src="/images/utility/smart_application/orange_440_165_b.png" alt=""></li>
            <li><img src="/images/utility/smart_application/pink_440_165_b.png" alt=""></li>
            <li><img src="/images/utility/smart_application/red_440_165_b.png" alt=""></li>
            <li><img src="/images/utility/smart_application/violet_440_165_b.png" alt=""></li>
        </ul>
	</div>

	<div class="side-contents" id="js-side">
        <ul class="link-page-inner">
            <li>
                <a href="#smart-application">リンク先URL</a>
            </li>
            <li>
                <a href="#homepage-banner">ホームページ用バナー</a>
            </li>
            <li>
                <a href="#mediation-homepage-banner">仲介会社専用ホームページ用バナー</a>
            </li>
        </ul>
		<p class="link-toppage">
			<a href="../utility">お役立ち<br>コンテンツTOP</a>
		</p>
	</div>
	<!-- /contents --></div>
@endsection
