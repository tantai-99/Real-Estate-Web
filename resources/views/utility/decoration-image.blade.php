@extends('layouts.utility')

@section('title', __('お役立ち'))

@section('style')
  <link media="screen" rel="stylesheet" type="text/css" href='/css/common.css'>
  <link media="screen" rel="stylesheet" type="text/css" href='/css/utility.css'>
  <link media="screen" rel="stylesheet" type="text/css" href='/css/page-element.css'>
@endsection
@section('script')
  <script type="text/javascript" src="/js/libs/jquery-1.11.2.min.js"> </script>
  <script type="text/javascript" src="/js/libs/jquery.ah-placeholder.js"> </script>
  <script type="text/javascript" src="/js/libs/jquery.flexslider-min.js"></script>
  <script type="text/javascript" src="/js/app.js"> </script>
  <script type="text/javascript" src="/js/common.js"> </script>
@endsection
@section('content')
    <div class="main-contents">
      <div class="main-contents-body contents-guideline">
        <h2><a href="../utility">お役立ちコンテンツ</a></h2>
        <img src="/images/utility/decoration_standard.jpg" alt="ホームページ作成ツール用　ボタン画像素材のご紹介　ホームページ作成ツールをご契約いただくことで、400個以上のボタン素材がご利用いただけます。">
        <p class="btn-agree">
          <a href="../utility/">お役立ちコンテンツに戻る</a>
        </p>
      </div>
    </div>
@endsection