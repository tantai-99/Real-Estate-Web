@extends('layouts.seo-advice')

@section('title', __('SEOアドバイス│ホームページ作成ツール'))

@section('content')
<div class="section">
    <h2>初期設定</h2>
    <p>「初期設定」内項目を入力する際には、下記点に気を付けて入力しましょう。</p>
    <div class="seo-example">
        <p>下記の不動産会社を参考にご説明致します。<br>
            社名：株式会社○○不動産　SEO対象ワード：「蒲田 不動産」<br>
            概要：蒲田を中心に不動産（賃貸・売買物件）を取り扱う地域密着型の不動産会社</p>
    </div>
</div>
<div class="section">
    <h2>サイトの概要</h2>
    <p>サイトの概要がわかる文章にしましょう。</p>

    <div class="text-block">
        <h3>ポイント</h3>
        <ul class="seo-number-list">
            <li>&#9312; 「完全一致（<a href="#one">※1</a>）」でワードを盛り込みましょう。</li>
            <li>&#9313; エリアや種目を1つに絞りましょう。</li>
            <li>&#9314; 文頭は基本設定の「サイト名」や「サイトの説明文」と異なるように作成しましょう。</li>
        </ul>
    </div>

    <div class="text-block">
        <h3>設定例</h3>
        <ul class="seo-good">
            <li>蒲田で不動産をお探しなら株式会社○○不動産にお任せください。</li>
        </ul>
    </div>

    <div class="text-block">
        <h3>ポイント詳細</h3>
        <ul class="seo-bad">
            <li>&#9312; NG事例：「蒲田で<span class="txt-red">不動産情報</span>をお探しなら株式会社○○不動産」
                <span class="bad-comment">"不動産"と"不動産物件"は別ワードとして認識されてしまう可能性が高いです。<br>対策したいワード（不動産）で盛り込むようにしましょう。</span>
            </li>

            <li>&#9313; NG事例：<span class="txt-red">蒲田・東京・大阪</span>の<span class="txt-blue">不動産・賃貸・売買</span>なら株式会社○○不動産
                <span class="bad-comment">サイトのコンテンツを表現するタイトルとすることが必要です。</span>
            </li>

            <li>&#9314; NG事例：基本設定の"サイト名"→「<span class="txt-red">蒲田の不動産</span>なら株式会社○○不動産」<br>
                基本設定の"サイトの説明"→「<span class="txt-red">蒲田の不動産</span>のことなら株式会社○○不動産に」<br>
                "サイトの概要"→「<span class="txt-red">蒲田の不動産</span>なら株式会社○○不動産にお任せください。」</p>
                <span class="bad-comment">文頭が重複しています。「蒲田<span class="txt-red">で</span>不動産をお探しなら・・・」のように助詞などを利用して文頭をユニーク化し異なる文章を作成しましょう。</span>
            </li>
        </ul>
    </div>
</div>


<div class="section">
    <h2>画像タイトル</h2>
    <p>画像が表示できない環境等のために正確に画像の内容を入力しましょう。</p>

    <div class="text-block">
        <h3>ポイント</h3>
        <ul class="seo-number-list">
            <li>&#9312; 画像の内容を端的に入力しましょう。</li>
        </ul>
    </div>

    <div class="text-block">
        <h3>設定例</h3>
        <ul class="seo-good">
            <li>桜の写真→「中央通りの桜の写真」</li>
        </ul>
    </div>

    <div class="text-block">
        <h3>ポイント詳細</h3>
        <ul class="seo-bad">
            <li>&#9312; NG事例：桜の写真→「蒲田の不動産・蒲田の賃貸物件」
                <span class="bad-comment">画像に関係のない内容を入力するのは避けましょう。</span>
            </li>
        </ul>
    </div>
</div>


<!--
<div class="section">
    <h2>コンテンツについて</h2>
    <p>コンテンツの充実化を図り、情報量が豊富なサイトにしましょう。</p>

    <div class="text-block">
        <h3>ポイント</h3>
        <ul class="seo-number-list">
            <li>&#9312; テキストで作成しましょう。</li>
            <li>&#9313; 他サイトのコピーではなくオリジナルなコンテンツを作成しましょう。</li>
            <li>&#9314; コンテンツをたくさん作成していきましょう。</li>
        </ul>
    </div>

    <div class="text-block">
        <h3>ポイント詳細</h3>
        <ul class="seo-bad">
            <li>&#9312; NG事例：テキストを画像化して挿入
                <span class="bad-comment">検索エンジンは画像化されたテキストは認識することが出来ません。</span>
            </li>
            <li class="comment-only">&#9313; <span class="bad-comment">他サイトのコピーはユーザにとって有益でないと判断され評価されません。</span></p>
            </li>
            <li class="comment-only">&#9314; <span class="bad-comment">情報量の多いサイトは、ユーザにとってメリットの多いサイトとして評価されます。</span></p>
            </li>
        </ul>
    </div>
</div>
-->

<div class="section">
    <ul class="seo-asterisk-list">
        <li id="one">※1 サイトの概要における「完全一致」とは...<br>
            「蒲田 不動産」で対策を行いたい場合は、"蒲田"+"助詞"+"不動産"で作成しましょう。<br>「蒲田の不動産なら・・・」や「蒲田で不動産を・・・」などにすることを推奨します。<br>「蒲田の不動産情報」や「蒲田で不動産物件を・・・」などは別ワードとして認識されてしまう可能性があります。
        </li>
    </ul>
</div>
@endsection