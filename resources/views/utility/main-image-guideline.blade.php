@extends('layouts.utility')

@section('title', __('お役立ち│ホームページ作成ツール'))

@section('style')
<link href="/css/utility.css" media="screen" rel="stylesheet" type="text/css">
@endsection

@section('script')
<script type="text/javascript">
$(function () {
	$("#main-contents-body").addClass("contents-guideline");
});
</script>
@endsection

@section('content')

<h2><a href="../utility">お役立ちコンテンツ</a></h2>
  <h3 class="heading-lv1">メインイメージ画像素材のご利用にあたって</h3>
  <h4 class="heading-lv2">使用許諾</h4>
  <ol>
    <li>
      （1）画像素材は、当社が提供するホームページ作成ツールを利用して作成するWebサイトにのみ、無料でご利用いただけます。
    </li>
    <li>
      （2）画像素材は、加工を施してご利用いただけます。ただし「禁止事項」に該当する行為は、これを禁じます。
    </li>
  </ol>

  <h4 class="heading-lv2">禁止事項</h4>
  <ol>
    <li>
      （1）使用許諾以外の方式、目的による画像素材の使用はできません。
    </li>
    <li>
      （2）画像素材の著作権は、当社又は当社に画像を提供する者に帰属するものであって、当社に無断で再配布、転売、又は二次利用することはできません。
    </li>
    <li>
      （3）公序良俗に反したり、当社又は第三者の名誉、信用等を害したり、その他法令に違反するような態様において、使用はできません。
    </li>
    <li>
      （4）画像データの被写体（人物、物品、風景など一切を指します）の特徴、品位、名誉又は信用を害する態様での使用はできません。また、被写体が特定の営業又は商品を使用、推奨しているかのような印象を与える使用はできません。
    </li>
    <li>
      （5）虚偽あるいは誹謗、中傷を内容とする使用はできません。
    </li>
  </ol>

  <h4 class="heading-lv2">免責</h4>
  <ol>
    <li>
      （1）当社は予告なく、画像素材の提供を中止する場合があります。
    </li>
    <li>
      （2）当社が提供する画像素材を使用したことによって生じたトラブルに関して、当社は一切関知しません。また、画像素材を使用したことによって生じたいかなる損害（直接、間接、特別、派生、逸失利益、営業機会の損失等を含みます）に対しても当社は責任を負いません。
    </li>
  </ol>

  <h4 class="heading-lv2">画像素材</h4>
  <ol>
    <li>
      （1）ファイル形式はJPEG画像形式です。
    </li>
  </ol>
  <p class="btn-agree">
    <a href="<?php echo $view->route('main-image')?>">同意して利用する</a>
  </p>
@endsection
