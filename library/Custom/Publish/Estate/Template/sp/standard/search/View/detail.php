<!DOCTYPE html>
<html lang="ja">
<head>
  <?php echo $view->api->head; // title, meta?>
  <?php include('header/meta.blade.php'); ?>
  <?php include('header/link.blade.php'); ?>
  <?php include('header/seoTag.blade.php'); ?>
  <?php include('header/stylesheet.blade.php'); ?>
  <?php include('header/script.blade.php'); ?>
  <script>var search_config = <?php echo $view->_config;?>;</script>
  <script src="/sp/js/pinchzoom.js"></script>
</head>
<body class="top btn-footer">

<div id="fb-root"></div>

<?php $header_error = $this->viewHelper->includeCommonFile("header", isset($view->api->header) ? $view->api->header : null, isset($view->api->head) ? $view->api->head : null); ?>
<?php 
if(!empty($_SERVER['HTTPS'])){
    $pattern = '~(rel=["\']photo-slider-group["\'] href=["\'])(https?://)~iU';
    $replacement = '$1https://';
    $view->api->content = preg_replace($pattern, $replacement, $view->api->content);
}
?>
<div class="contents-article">     <?php $header_error = $this->viewHelper->includeCommonFile("gnav"); ?><?php echo $view->api->content; ?> </div>

<?php $this->viewHelper->includeCommonFile("company_info"); ?>

<p class="pagetop"><a href="#top"><span>ページの先頭へ</span></a></p>

<?php $footer_error = $this->viewHelper->includeCommonFile("footer"); ?>

<div class="commission-rent-overlaywrap">
  <div class="box-overlay" style="display:none;"></div>
  <div class="floatbox" style="top:0px;display:none;">
    <div class="inner">
      <p class="floatbox-heading">仲介手数料とは</p>
      <div class="floatbox-tx">
        <p>貸主と借主の契約の仲立ちを行う不動産会社に支払う報酬です。宅地建物取引業法等（法第46条・建設省告示第1552号他）により取引態様ごとに受け取ることのできる報酬の上限額が定められています。
        <br> 取引態様が「貸主」の場合は不要です。
        <br> 「仲介」の場合、居住用の物件は月額賃料の0.55ヶ月分の範囲内とされています。なお、物件によって、月額賃料の1.1ヶ月分を上限とした範囲内で必要となる場合があります。
        <br> 「代理」の場合、月額賃料の1.1ヶ月分の範囲内とされています。
        <br>
        <br> ※宅地または居住用以外の建物で権利金がある物件に関しては、権利金の額を売買代金の額とみなして算出される場合があります。
        <br> 権利金を以下のように区分し、それぞれ定められた割合を乗じて得た金額の合計額が上限となります。
        <table class="commission-table">
          <tr>
            <td>200万円以下の金額</td>
            <td class="td1">5.5％</td>
          </tr>
          <tr>
            <td>200万円を超え400万円以下の金額</td>
            <td class="td1">4.4％</td>
          </tr>
          <tr>
            <td>400万円を超える金額</td>
            <td class="td1">3.3％</td>
          </tr>
        </table>
        　（賃貸借に係る消費税額を除外した額）
        <br>
        <br> 物件によって金額が異なります。
        <br> お問い合わせの際は十分ご確認ください。</p>
      </div>
      <p class="btn-modal-close">閉じる</p>
    </div>
  </div>
</div>

<div class="commission-buy-overlaywrap">
  <div class="box-overlay" style="display:none;"></div>
  <div class="floatbox" style="top:0px;display:none;">
    <div class="inner">
      <p class="floatbox-heading">仲介手数料とは</p>
      <div class="floatbox-tx">
        <p>売主と買主の契約の仲立ちを行う不動産会社に支払う報酬です。
          <br> 宅地建物取引業法等（法第46条・建設省告示第1552号他）により取引態様ごとに受け取ることのできる報酬の上限額が定められています。
          <br> 取引態様が「売主」の場合は不要です。
          <br> 「媒介（一般・専任・専属専任）」「仲介」の場合、売買代金を以下のように区分し、それぞれ定められた割合を乗じて得た金額の合計額が上限となります。
          <table class="commission-table">
            <tr>
              <td>200万円以下の金額</td>
              <td class="td1">5.5％</td>
            </tr>
            <tr>
              <td>200万円を超え400万円以下の金額</td>
              <td class="td1">4.4％</td>
            </tr>
            <tr>
              <td>400万円を超える金額</td>
              <td class="td1">3.3％</td>
            </tr>
          </table>
          　 （建物に係る消費税額を除外した額）
          <br>
          <br> ※参考　簡易計算方式（算出した金額が上限額となります。）
          <table class="commission-table">
            <tr>
              <td>200万円を超え400万円以下の場合</td>
              <td>4.4％+2.2万円</td>
            </tr>
            <tr>
              <td>400万円を超える場合</td>
              <td>3.3％+6.6万円</td>
            </tr>
          </table>
          <br> 「代理」の場合、一つの取引において、上記仲介の計算方法により算出した金額の2倍以内が上限となります。
          <br>
          <br>物件によって金額が異なります。
          <br>お問い合わせの際は十分ご確認ください。</p>
      </div>
      <p class="btn-modal-close">閉じる</p>
    </div>
  </div>
</div>

<div class="commission-building-overlaywrap">
  <div class="box-overlay" style="display:none;"></div>
  <div class="floatbox" style="top:0px;display:none;">
    <div class="inner">
      <p class="floatbox-heading">建築条件付き土地のこと</p>
      <div class="floatbox-tx">
        <p>売買契約の際、「一定期間内(概ね3ヶ月以上)に住宅の建築請負契約を締結する」ことを条件とするものです。<br>建築請負契約が成立しなかった場合、契約は白紙となり、支払った金額は返還されます。建物参考プランが公開されている場合、一例であって、そのプランを採用するか否かは土地購入者の自由な判断に委ねられます。<br>建築請負会社が指定されている場合と指定されていない場合があります。<br>建築条件付き土地の価格には、建物価格は含まれていません。</p>
      </div>
      <p class="btn-modal-close">閉じる</p>
    </div>
  </div>
</div>

<div class="photo-gallery-overlaywrap">
  <div class="box-overlay" style="display:none;"></div>
  <div class="floatbox gallery" style="top:0px;display:none;">
    <div class="modal-header">
      <p class="photo-slider-num">
        <span class="photo-slider-num-now">1</span> /
        <span class="photo-slider-num-total">6</span>
      </p>
      <p class="btn-modal-close"></p>
      <div class="btn-modal-list">
        <div class="list-icon"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></div>
        <p>リスト</p>
      </div> 
    </div>
    <p id="tx-fadeout" style="width: 50%;position: absolute;left: 50%;transform: translate(-50%, 0);z-index: 9999">ピンチアウトで拡大できます</p>
    <div  style="height: calc(100% - 30px)" >
        <div class="photo-zoom pinch-zoom" style="height: 100%;width: 100%;"">
            <img >
        </div>
    </div>
      <div class="photo-slider-info">
        <ul class="btn-move">
          <li class="prev"><a href="javascript:void(0)">前へ</a></li>
          <li class="next"><a href="javascript:void(0)">次へ</a></li>
        </ul>
      </div>
    <p class="tx-heading" style="text-align: center;">#</p>
    <p class="tx-caption" style="text-align: center;">#</p>
  </div>
</div>

<div class="around-overlaywrap">
  <div class="box-overlay" style="display:none;"></div>
  <div class="floatbox gallery" style="top:30px;display:none;">
    <p class="tx-heading" style="text-align: center;">#</p>
    <p class="photo-zoom">
      <img width="100%" class="around-photo">
    </p>
    <p class="tx-caption" style="text-align: center;">#</p>
    <p class="btn-modal-close">閉じる</p>
  </div>
</div>

<div class="photo-modal-list">
  <div class="box-overlay" style="display:none;"></div>
  <div class="floatbox gallery" style="top:0px;display:none;">
    <div class="modal-header">
      <p class="photo-slider-num">
        <span class="photo-slider-num-total">6</span>枚
      </p>
      <p class="btn-modal-close"></p>
    </div>
    <ul class="modal-list">
    </ul>
  </div>
</div>

</body>
</html>
