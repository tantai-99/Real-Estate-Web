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
            <h3 class="heading-lv1">スマート申込 専用バナー画像素材のご利用にあたって</h3>
            <h4 class="heading-lv2">利用許諾</h4>
            <ol>
              <li>
                （1）この「スマート申込 専用バナー画像素材のご利用にあたって」（以下「ご利用にあたって」といいます。）に同意いただくと表示される画像（以下「画像素材」といいます。）は、当社が、画像素材を利用する方（以下「利用者」といいます。）の業務に役立てていただくことを目的として提供するものです。
              </li>
              <li>
                （2）当社は、ご利用にあたって同意いただいた方に対し、画像素材の⾮独占的、譲渡不可かつ第三者への再許諾不可の利用を許諾します。
              </li>
            </ol>

            <h4 class="heading-lv2">権利の帰属</h4>
            <ol>
              <li>
                （1）画像素材の著作権は、当社又は当社に画像を提供する者に帰属します。
              </li>
            </ol>

            <h4 class="heading-lv2">禁止事項</h4>
            <p style="text-indent: 10px;">画像素材について、以下の各号に掲げる事項を禁止します。</p>
            <ol>
                <li>
                    （1）画像素材を改変すること。
                </li>
                <li>
                    （2）画像素材のデータを譲渡、貸与、送信又は第三者に利用許諾等すること。
                </li>
                <li>
                    （3）公序良俗に反したり、当社又は第三者の名誉、信用等を害したり、その他法令に違反するような態様において利用すること。
                </li>
                <li>
                    （4）虚偽あるいは誹謗、中傷を目的として利用すること。
                </li>
            </ol>

            <h4 class="heading-lv2">免責</h4>
            <ol>
                <li>
                    （1）当社は予告なく、画像素材の提供を中止する場合があります。
                </li>
                <li>
                    （2）当社が画像素材の提供を中止したこと、又は利用者が画像素材を利用したことによって生じたトラブルに関して、当社は一切関知しません。また、画像素材を利用したこと又は利用できないことによって利用者に生じたいかなる損害（直接、間接、特別、派生、逸失利益、営業機会の損失等を含みます）に対しても当社は責任を負いません。
                </li>
            </ol>

            <h4 class="heading-lv2">違反</h4>
            <ol>
                <li>
                    （1）利用者がご利用にあたって違反した場合、当社は直ちに画像素材の利用許諾を終結させることがきるものとします。この場合、当該利用者は、直ちに画像素材の利用を中止し、画像素材のデータ及びそのすべての複製物等を破棄又は消去するものとします。
                </li>
            </ol>

            <h4 class="heading-lv2">ファイル形式</h4>
            <ol>
                <li>
                    （1）画像素材のファイル形式はPNG形式です。
                </li>
            </ol>

            <p class="btn-agree">
              <a href="<?php echo $view->route('smart-application')?>">同意して利用する</a>
            </p>
@endsection
