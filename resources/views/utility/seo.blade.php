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
$(document).ready(function () {
  var pagetop = $('.page-top');
    $(window).scroll(function () {
      if ($(this).scrollTop() > 50) {
        pagetop.fadeIn();
      } else {
        pagetop.fadeOut();
      }
    });
    pagetop.click(function () {
      $('body, html').animate({ scrollTop: 0 },300);
      return false;
    })
})
</script>
@endsection

@section('content')

      <h2><a href="../">お役立ちコンテンツ</a></h2>
      <h3 class="heading-lv1" id="side-head">SEOお悩み解決</h3>
      <p>
        ホームページを作成する際に、SEO観点で気を付けるべきポイントをお悩み解決の形式でご紹介します。<br>
        気軽にお読みいただき、作成・更新にお役立てください。<br>
        SEOに関する情報は、マニュアル第三章「SEO 内的施策アドバイス」にもありますので併せてご参照ください。
      </p>
      <p>
        なお、本資料の情報は、Googleが公表しているガイドラインをもとに、当社独自の分析・研究を踏まえた掲載時点の見解です。

      </p>

      <div class="contents">
        <div class="material-contents">
          <div class="page-top" style="display: none">
            <a href="#"><img src="/images/seo/page-top.png" alt="page-top"></a>
          </div>
          <div class="seo-img">
            <a href="/images/seo/SEOお悩み解決.zip" download="SEOお悩み解決.zip"><img src="/images/seo/pdf_all.png" alt="「SEOお悩み解決」すべてのPDFをダウンロードする" width="400" height="50"></a>
          </div>
          <div class="seo-contents" id="volumeup">
            <img src="/images/seo/seo_volumeup.jpg" alt="コンテンツ">
            <p class="seo-title volumeup">「コンテンツのボリュームアップ」</p>
            <div class="seo">
              <table class="seo-table1">
                <tr><th class="seo-volumeup">ブログもホームページ<br>作成ツールで更新しよう！</th></tr>
                <tr><td class="seo-img seo-capture"><a href="/utility/seo/pdf/seo_blog.pdf" target="_blank"><img src="/images/seo/seo_blog.png" width="210"></a></td></tr>
                <tr><td class="seo-introduction">ブログをせっかく更新しているのにSEO上もったいないケースが…！<br>同じドメインのブログを使うメリットとは？</td></tr>
                <tr><td class="seo-img"><a href="/utility/seo/pdf/seo_blog.pdf" download="SEOお悩み解決「ブログもホームページ作成ツールで更新しよう！」.pdf"><img src="/images/seo/pdf.png" alt="PDFをダウンロード" width="200" height="40"></a></td></tr>
              </table>
              <table class="seo-table2">
                <tr><th class="seo-volumeup">キャンペーンを実施しよう！</th></tr>
                <tr><td class="seo-img seo-capture"><a href="/utility/seo/pdf/seo_campaign.pdf" target="_blank"><img src="/images/seo/seo_campaign.png" width="210"></a></td></tr>
                <tr><td class="seo-introduction">繁忙期などに、ホームページをうまく販促に活用できていますか？<br>キャンペーン特設ページをつくってみましょう！</td></tr>
                <tr><td class="seo-img"><a href="/utility/seo/pdf/seo_campaign.pdf" download="SEOお悩み解決「キャンペーンを実施しよう！」.pdf"><img src="/images/seo/pdf.png" alt="PDFをダウンロード" width="200" height="40"></a></td></tr>
              </table>
              <table class="seo-table1">
                <tr><th class="seo-volumeup">バックナンバーを残さないと<br>もったいない！</th></tr>
                <tr><td class="seo-img seo-capture"><a href="/utility/seo/pdf/seo_backnumber.pdf" target="_blank"><img src="/images/seo/seo_backnumber.png" width="210"></a></td></tr>
                <tr><td class="seo-introduction">キャンペーンなどの期間限定の情報を、イベント終了後に削除するともったいない理由とは？</td></tr>
                <tr><td class="seo-img"><a href="/utility/seo/pdf/seo_backnumber.pdf" download="SEOお悩み解決「バックナンバーを残さないともったいない！」.pdf"><img src="/images/seo/pdf.png" alt="PDFをダウンロード" width="200" height="40"></a></td></tr>
              </table>
              <table class="seo-table2">
                <tr><th class="seo-volumeup">下書きや未作成のページが<br>残っていませんか？</th></tr>
                <tr><td class="seo-img seo-capture"><a href="/utility/seo/pdf/seo_newpage.pdf" target="_blank"><img src="/images/seo/seo_newpage.png" width="210"></a></td></tr>
                <tr><td class="seo-introduction">HP作成ツールにセットされているひな形は活用できていますか？下書きや未作成のページがないか見直してみましょう！</td></tr>
                <tr><td class="seo-img"><a href="/utility/seo/pdf/seo_newpage.pdf" download="SEOお悩み解決「下書きや未作成のページが残っていませんか？」.pdf"><img src="/images/seo/pdf.png" alt="PDFをダウンロード" width="200" height="40"></a></td></tr>
              </table>
              <table class="seo-table3">
                <tr><th class="seo-volumeup">一度つくったらもう終わり？</th></tr>
                <tr><td class="seo-img seo-capture"><a href="/utility/seo/pdf/seo_update.pdf"  target="_blank"><img src="/images/seo/seo_update.png" width="210"></a></td></tr>
                <tr><td class="seo-introduction">作成したホームページの情報が古いままになってしまっていませんか？最新情報を盛り込んで更新しましょう！</td></tr>
                <tr><td class="seo-img"><a href="/utility/seo/pdf/seo_update.pdf" download="SEOお悩み解決「一度つくったらもう終わり？」.pdf"><img src="/images/seo/pdf.png" alt="PDFをダウンロード" width="200" height="40"></a></td></tr>
              </table>
            </div>
          </div>
          <div class="seo-contents" id="originality">
          <p class="seo-title originality">「コンテンツのオリジナル性」</p>
            <div class="seo">
              <table class="seo-table1">
                <tr><th class="seo-originality">どんなページをつくればいいの？</th></tr>
                <tr><td class="seo-img seo-capture"><a href="/utility/seo/pdf/seo_originalpage.pdf" target="_blank"><img src="/images/seo/seo_originalpage.png" width="210"></a></td></tr>
                <tr><td class="seo-introduction">自社独自の内容になるようなテーマを考えてみましょう！</td></tr>
                <tr><td class="seo-img"><a href="/utility/seo/pdf/seo_originalpage.pdf" download="SEOお悩み解決「どんなページをつくればいいの？」.pdf"><img src="/images/seo/pdf.png" alt="PDFをダウンロード" width="200" height="40"></a></td></tr>
              </table>
              <table class="seo-table2">
                <tr><th class="seo-originality">1ページの量ってどのくらい？</th></tr>
                <tr><td class="seo-img seo-capture"><a href="/utility/seo/pdf/seo_pagevolume.pdf" target="_blank"><img src="/images/seo/seo_pagevolume.png" width="210"></a></td></tr>
                <tr><td class="seo-introduction">画像が1枚だけ貼ってあったり、文章が1文だけ設置されているページはありませんか？</td></tr>
                <tr><td class="seo-img"><a href="/utility/seo/pdf/seo_pagevolume.pdf" download="SEOお悩み解決「1ページの量ってどのくらい？」.pdf"><img src="/images/seo/pdf.png" alt="PDFをダウンロード" width="200" height="40"></a></td></tr>
              </table>
              <table class="seo-table3">
                <tr><th class="seo-originality">他社のサイトは<br>参考にしてもいいの？</th></tr>
                <tr><td class="seo-img seo-capture"><a href="/utility/seo/pdf/seo_othersites.pdf" target="_blank"><img src="/images/seo/seo_othersites.png" width="210"></a></td></tr>
                <tr><td class="seo-introduction">他のサイトからコピーした内容をそのまま掲載していませんか？</td></tr>
                <tr><td class="seo-img"><a href="/utility/seo/pdf/seo_othersites.pdf" download="SEOお悩み解決「他社のサイトは参考にしてもいいの？」.pdf"><img src="/images/seo/pdf.png" alt="PDFをダウンロード" width="200" height="40"></a></td></tr>
              </table>
            </div>
          </div>
          <div class="seo-contents" id="includetext">
          <p class="seo-title includetext">「テキストを盛り込む」</p>
            <div class="seo">
              <table class="seo-table1">
                <tr><th class="seo-includetext">どうやってテキストを<br>増やしたらいいの？</th></tr>
                <tr><td class="seo-img seo-capture"><a href="/utility/seo/pdf/seo_pageadd.pdf" target="_blank"><img src="/images/seo/seo_pageadd.png" width="210"></a></td></tr>
                <tr><td class="seo-introduction">デザインを損ねずにテキストを追加する方法はさまざま！テキストを増やせる場所がないか確認してみましょう！</td></tr>
                <tr><td class="seo-img"><a href="/utility/seo/pdf/seo_pageadd.pdf" download="SEOお悩み解決「どうやってテキストを増やしたらいいの？」.pdf"><img src="/images/seo/pdf.png" alt="PDFをダウンロード" width="200" height="40"></a></td></tr>
              </table>
              <table class="seo-table2">
                <tr><th class="seo-includetext">画像のリンクボタンは<br>ダメなの？</th></tr>
                <tr><td class="seo-img seo-capture"><a href="/utility/seo/pdf/seo_buttonlink.pdf" target="_blank"><img src="/images/seo/seo_buttonlink.png" width="210"></a></td></tr>
                <tr><td class="seo-introduction">リンクボタンに画像を使用していませんか？ちょっとした工夫でできるSEOの対策ポイントとは？</td></tr>
                <tr><td class="seo-img"><a href="/utility/seo/pdf/seo_buttonlink.pdf" download="SEOお悩み解決「画像のリンクボタンはダメなの？」"><img src="/images/seo/pdf.png" alt="PDFをダウンロード" width="200" height="40"></a></td></tr>
              </table>
              <table class="seo-table3">
                <tr><th class="seo-includetext">画像ばかりのページを<br>つくってはいけない？</th></tr>
                <tr><td class="seo-img seo-capture"><a href="/utility/seo/pdf/seo_searchengine.pdf" target="_blank"><img src="/images/seo/seo_searchengine.png" width="210"></a></td></tr>
                <tr><td class="seo-introduction">イベントやキャンペーンについてチラシを貼り付けるだけで終わっていませんか？</td></tr>
                <tr><td class="seo-img"><a href="/utility/seo/pdf/seo_searchengine.pdf" download="SEOお悩み解決「画像ばかりのページをつくってはいけない？」.pdf"><img src="/images/seo/pdf.png" alt="PDFをダウンロード" width="200" height="40"></a></td></tr>
              </table>
              <table>
                <tr><th class="seo-includetext">他のサイトへのリンクは<br>だめなの？</th></tr>
                <tr><td class="seo-img seo-capture"><a href="/utility/seo/pdf/seo_originaltext.pdf" target="_blank"><img src="/images/seo/seo_originaltext.png" width="210"></a></td></tr>
                <tr><td class="seo-introduction">他のサイトへリンクを張っただけのページはありませんか？さらに情報を追加できる場所がないか確認してみましょう！</td></tr>
                <tr><td class="seo-img"><a href="/utility/seo/pdf/seo_originaltext.pdf" download="SEOお悩み解決「他のサイトへのリンクはダメなの？」.pdf"><img src="/images/seo/pdf.png" alt="PDFをダウンロード" width="200" height="40"></a></td></tr>
              </table>
            </div>
          </div>
          <div class="seo-contents" id="sitestructure">
          <img src="/images/seo/seo_sitestructure.jpg" alt="サイト構造">
          <p class="seo-title sitestructure">「サイト構造」</p>
            <div class="seo">
              <table class="seo-table3">
                <tr><th class="seo-sitestructure">制作したフォルダは<br>整理整頓されている？</th></tr>
                <tr><td class="seo-img seo-capture"><a href="/utility/seo/pdf/seo_cleanup.pdf" target="_blank"><img src="/images/seo/seo_cleanup.png" width="210"></a></td></tr>
                <tr><td class="seo-introduction">テーマごとにページをカテゴリ分けできていますか？階層外に作っているページがないかも合わせて確認してみましょう！</td></tr>
                <tr><td class="seo-img"><a href="/utility/seo/pdf/seo_cleanup.pdf" download="SEOお悩み解決「制作したフォルダは整理整頓されている？」.pdf"><img src="/images/seo/pdf.png" alt="PDFをダウンロード" width="200" height="40"></a></td></tr>
              </table>
            </div>
          </div>
          <div class="seo-contents" id="link">
          <img src="/images/seo/seo_link.jpg" alt="リンクの扱い">
          <p class="seo-title link">「リンクの扱い」</p>

            <div class="seo">
              <table class="seo-table3">
                <tr><th class="seo-link">関連あるページを<br>行き来しやすく！</th></tr>
                <tr><td class="seo-img seo-capture"><a href="/utility/seo/pdf/seo_category.pdf" target="_blank"><img src="/images/seo/seo_category.png" width="210"></a></td></tr>
                <tr><td class="seo-introduction">関連ページへのリンクが設置してあると、ページを回遊しやすく便利！リンク設計について考えてみましょう。</td></tr>
                <tr><td class="seo-img"><a href="/utility/seo/pdf/seo_category.pdf" download="SEOお悩み解決「関連あるページを行き来しやすく！」.pdf"><img src="/images/seo/pdf.png" alt="PDFをダウンロード" width="200" height="40"></a></td></tr>
              </table>
              <table>
                <tr><th class="seo-link">リンクにひと工夫で<br>SEO効果アップ！</th></tr>
                <tr><td class="seo-img seo-capture"><a href="/utility/seo/pdf/seo_pagelink.pdf" target="_blank"><img src="/images/seo/seo_pagelink.png" width="210"></a></td></tr>
                <tr><td class="seo-introduction">URLにそのままリンクをつけていたり、「トップへ戻る」という文言でリンクをつけていませんか？</td></tr>
                <tr><td class="seo-img"><a href="/utility/seo/pdf/seo_pagelink.pdf" download="SEOお悩み解決「リンクにひと工夫でSEO効果アップ！」.pdf"><img src="/images/seo/pdf.png" alt="PDFをダウンロード" width="200" height="40"></a></td></tr>
              </table>
            </div>
          </div>
          <div class="seo-contents" id="tdk">
          <img src="/images/seo/seo_tdk.jpg" alt="TitleDescriptionKeywords">
          <p class="seo-title tdk">「Title、Description、Keywords」</p>
            <div class="seo">
              <table class="seo-table3">
                <tr><th class="seo-tdk">サイト名って<br>どうすればいいの？</th></tr>
                <tr><td class="seo-img seo-capture"><a href="/utility/seo/pdf/seo_sitename.pdf" target="_blank"><img src="/images/seo/seo_sitename.png" width="210"></a></td></tr>
                <tr><td class="seo-introduction">サイトタイトルが会社名だけになっていたり、逆にキーワードを不自然に盛り込みすぎて、わかりづらい内容になってはいませんか？</td></tr>
                <tr><td class="seo-img"><a href="/utility/seo/pdf/seo_sitename.pdf" download="SEOお悩み解決「サイト名ってどうすればいいの？」.pdf"><img src="/images/seo/pdf.png" alt="PDFをダウンロード" width="200" height="40"></a></td></tr>
              </table>
            </div>
          </div>
          <div class="seo-img seo-contents">
            <a href="/images/seo/SEOお悩み解決.zip" download="SEOお悩み解決.zip"><img src="/images/seo/pdf_all.png" alt="「SEOお悩み解決」すべてのPDFをダウンロードする" width="400" height="50"></a> 
          </div>
        </div>


        <div class="side-contents" id="js-side">
          <ul class="link-page-inner">
            <li>
              <a href="#volumeup">コンテンツ</a>
              <ul class="link-page-inner2">
                <li>
                  <a href="#volumeup">ボリュームアップ</a>
                </li>
                <li>
                  <a href="#originality">オリジナル性</a>
                </li>
                <li>
                  <a href="#includetext">テキストを盛込む</a>
                </li>
              </ul>
            </li>
            <li>
              <a href="#sitestructure">サイト構造</a>
            </li>
            <li>
              <a href="#link">リンク</a>
            </li>
            <li>
              <a href="#tdk">TDK</a>
            </li>
          </ul>
        <p class="link-toppage">
          <a href="../">お役立ち<br>コンテンツTOP</a>
        </p>
        </div>	

      <!-- /contents --></div>
@endsection


