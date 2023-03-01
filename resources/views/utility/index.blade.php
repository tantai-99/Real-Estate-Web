@extends('layouts.utility')

@section('title', __('お役立ち│ホームページ作成ツール'))

@section('style')
<link href="/css/utility.css" media="screen" rel="stylesheet" type="text/css">
@endsection

@section('content')
<h2>お役立ちコンテンツ</h2>
<p>ホームページ作成ツールを利用する際に役立つ色々なコンテンツです。</p>
<ul class="link-list">
    <li>
    <a href="<?php echo $view->route('manual')?>" target="_blank">
            <h3 class="i-s-link">マニュアル</h3>
            <p>ホームページ作成ツールのマニュアルをご覧いただけます。</p>
            <div class="help-img">
                <img src="/images/help/help_01.png" alt="マニュアル">
            </div>
        </a>
    </li>
    <li>
    <a href="<?php echo $view->route('manual_toppageoriginal')?>" target="_blank">
            <h3 class="i-s-link">TOPページオリジナル制作用マニュアル</h3>
            <p>TOPページオリジナル制作用のマニュアルをご覧いただけます。</p>
            <div class="help-img">
                <img src="/images/help/help_01.png" alt="TOPページオリジナル制作用マニュアル">
            </div>
        </a>
    </li>
    <li>
        <a href="<?php echo $view->route('main-image-guideline')?>">
            <h3 class="i-s-link">メインイメージ画像</h3>
            <p>トップページメインイメージ用の素材集をご利用いただけます。</p>
            <div class="help-img">
                <img src="/images/help/help_02.png" alt="メインイメージ画像">
            </div>
        </a>
    </li>
    <li class="icon">
        <a href="<?php echo $view->route('favicon')?>">
            <h3 class="i-s-link">ファビコン用・ウェブクリップアイコン用画像</h3>
            <p>ファビコン・ウェブクリップアイコンの素材集をご利用いただけます。</p>
            <div class="help-img">
                <img src="/images/help/help_03.png" alt="ファビコン用画像">
            </div>
        </a>
    </li>
    <li class="icon">
        <?php if ( $view->decoration ): ?>
        <a href="<?php echo $view->route( 'decoration-guideline')?>"	>
        <?php else : ?>
        <a href="<?php echo $view->route( 'decoration-image'	)?>"	>
        <?php endif ; ?>
            <h3 class="i-s-link">ボタン画像素材</h3>
            <p>特集・リンクのボタン素材やアイコン・線・装飾の素材集をご利用いただけます。</p>
            <div class="help-img">
                <img src="/images/help/help_04.png" alt="ボタン画像素材">
            </div>
        </a>
    </li>
    <li>
        <a href="<?php echo $view->route('illustration-guideline')?>">
            <h3 class="i-s-link">イラスト画像</h3>
            <p>イラスト画像集をご利用いただけます。</p>
            <div class="help-img">
                <img src="/images/help/help_07.png" alt="イラスト画像">
            </div>
        </a>
    </li>
    <li class="help-loan">
        <a href="/utility/banner">
            <h3 class="i-s-link"><span>住宅ローンシミュレーション<br>バナーリンク</span></h3>
            <p>不動産総合情報サイト「アットホーム」内コンテンツへのバナーリンクをご利用いただけます。</p>
            <div class="help-img">
                <img src="/images/help/help_05.png" alt="ローンシミュレーションバナーリンク">
            </div>
        </a>
    </li>
    <li>
        <a href="/utility/customer/">
            <h3 class="i-s-link">お客様サイト用画像</h3>
            <p>「お客様サイト」へのバナー画像をご利用いただけます。</p>
            <div class="help-img">
                <img src="/images/help/help_08.png" alt="お客様サイト">
            </div>
        </a>
    </li>
    <li>
        <a href="/utility/athome-banner">
            <h3 class="i-s-link">athomeバナー</h3>
            <p>アットホームサイトや会員ページへリンクするためのバナー画像をご利用いただけます。</p>
            <div class="help-img">
                <img src="/images/help/help_10.png" alt="athomeバナー">
            </div>
        </a>
    </li>
    <li class="smart-application">
        <a href="<?php echo $view->route('smart-application-guideline')?>">
            <h3 class="i-s-link"><span>スマート申込専用ページ<br>＜入居申込Web化のご案内＞</span></h3>
            <p>仲介会社さま向けにWebでの入居申込をご案内する際にご利用いただけます。</p>
            <div class="help-img smart-application-img">
                <img src="/images/help/help_11.png" alt="スマート申込専用ページ">
            </div>
        </a>
    </li>
    <li>
        <a href="/utility/seo/">
            <h3 class="i-s-link">SEOお悩み解決</h3>
            <p>ホームページ作成時に、SEO観点で気を付けるポイントを紹介します。</p>
            <div class="help-img">
                <img src="/images/help/help_09.png" alt="SEOお悩み解決">
            </div>
        </a>
    </li>
    <li>
        <a href="<?php echo $view->route('usepoint')?>" target="_blank">
            <h3 class="i-s-link">利用要領</h3>
            <p>ホームページ作成ツール利用要領をご覧いただけます。</p>
            <div class="help-img">
                <img src="/images/help/help_06.png" alt="利用要領">
            </div>
        </a>
    </li>
</ul>
<div class="alert-normal">
    <p>本コンテンツは当社が提供するホームページ作成ツール(アドバンス/スタンダード/ライト)を利用して作成するWebサイトにのみ、無料でご利用いただけます。</p>
</div>
@endsection