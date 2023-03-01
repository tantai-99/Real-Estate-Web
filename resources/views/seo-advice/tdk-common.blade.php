@extends('layouts.seo-advice')

@section('title', __('SEOアドバイス│ホームページ作成ツール'))

@section('content')
<div class="section">
    <h2>サイト名・サイトの説明・キーワード（全ページ共通）</h2>
    <p>「サイト名・サイトの説明・キーワード（全ページ共通）」内項目を入力する際には、下記の点に気を付けて入力しましょう。</p>
    <div class="seo-example">
        <p>下記の不動産会社を参考にご説明致します。<br>
            社名：株式会社○○不動産　SEO対象ワード：「蒲田 不動産」<br>
            概要：蒲田を中心に不動産（賃貸・売買物件）を取り扱う地域密着型の不動産会社</p>
    </div>
</div>
<div class="section">
    <h2>サイト名</h2>
    <p>サイトの主題を記載しましょう。</p>

    <div class="text-block">
        <h3>ポイント</h3>
        <ul class="seo-number-list">
            <li>&#9312; 「完全一致（<a href="#one">※1</a>）」でワードを盛り込みましょう。</li>
            <li>&#9313; エリアや種目を1つに絞りましょう。</li>
            <li>&#9314; 同一ワードを過度に盛り込まないようにしましょう。</li>
        </ul>
    </div>

    <div class="text-block">
        <h3>設定例</h3>
        <ul class="seo-good">
            <li>蒲田の不動産なら株式会社○○不動産</li>
        </ul>
    </div>

    <div class="text-block">
        <h3>ポイント詳細</h3>
        <ul class="seo-bad">
            <li>&#9312; NG事例：「蒲田の<span class="txt-red">不動産情報</span>なら株式会社○○不動産」
                <span class="bad-comment">"不動産"と"不動産情報"は別ワードとして認識されてしまう可能性があります。<br>対策したいワード（例で言えば　"不動産"）で盛り込むようにしましょう。</span>
            </li>

            <li>&#9313; NG事例：<span class="txt-red">蒲田・東京・名古屋・大阪</span>の<span class="txt-blue">不動産・賃貸・売買</span>なら株式会社○○不動産
                <span class="bad-comment">サイトのメインコンテンツを表現する文章とすることが必要です。</span>
            </li>

            <li>&#9314; NG事例：「<span class="txt-red">蒲田</span>
                <span class="txt-blue">不動産</span>、<span class="txt-red">蒲田</span>の<span class="txt-blue">不動産</span>情報｜<span class="txt-red">蒲田</span>の<span class="txt-blue">不動産</span>なら株式会社○○不動産」
                <span class="bad-comment">同一ワードを何度も記載した文章にすることはやめましょう。</span>
            </li>
        </ul>
    </div>
</div>


<div class="section">
    <h2>サイトの説明</h2>
    <p>サイトの紹介文を記載しましょう。</p>

    <div class="text-block">
        <h3>ポイント</h3>
        <ul class="seo-number-list">
            <li>&#9312; 「完全一致（<a href="#one">※1</a>）」でワードを盛り込みましょう。</li>
            <li>&#9313; ワードの詰め込みすぎや羅列は避けましょう。</li>
            <li>&#9314; ページ内のテキストを使いまわさないようにしましょう。</li>
        </ul>
    </div>

    <div class="text-block">
        <h3>設定例</h3>
        <ul class="seo-good">
            <li>
                蒲田を中心に不動産のことなら株式会社○○不動産にお任せください。物件情報だけでなく蒲田の周辺情報やおすすめスポットなどの情報も盛りだくさん。
            </li>
        </ul>
    </div>

    <div class="text-block">
        <h3>ポイント詳細</h3>
        <ul class="seo-bad">
            <li>&#9312; NG事例：「○○不動産は蒲田の不動産情報を中心に...」
                <span class="bad-comment">"不動産"と"不動産情報"が別ワードとして認識されてしまう可能性が高いです。<br>対策したいワードで盛り込むようにしましょう。</span>
            </li>

            <li>&#9313; NG事例：<span class="txt-red">蒲田</span>の<span class="txt-blue">不動産</span> マンション・<span class="txt-red">蒲田</span>の<span class="txt-blue">不動産</span> アパートをお探しなら○○不動産。<span class="txt-red">蒲田</span>の<span class="txt-blue">不動産</span> マンション・<span class="txt-red">蒲田</span>の<span class="txt-blue">不動産</span>アパートに関する気になる情報も満載。<span class="txt-red">蒲田</span>の部屋探しは○○不動産。
                <span class="bad-comment">ワードの過度な盛り込みや不自然な羅列は検索エンジンからの評価が下がる可能性があります。</span>
            </li>

            <li class="comment-only">&#9314; <span class="bad-comment">サイト内で使用している文章をそのまま「サイトの説明」に流用するのではなく、サイトの概要をまとめた文章にしましょう。</span>
            </li>
        </ul>
    </div>
</div>


<div class="section">
    <h2>キーワード</h2>
    <p>サイトのメインとなるワードや社名を入力しましょう。</p>

    <div class="text-block">
        <h3>ポイント</h3>
        <ul class="seo-number-list">
            <li>&#9312; 「完全一致（<a href="#two">※2</a>）」でワードを盛り込みましょう。</li>
        </ul>
    </div>

    <div class="text-block">
        <h3>設定例</h3>
        <ul class="seo-good">
            <li>蒲田 不動産,株式会社○○不動産</li>
        </ul>
    </div>

    <div class="text-block">
        <h3>ポイント詳細</h3>
        <ul class="seo-bad">
            <li>&#9312; NG事例：「蒲田,不動産,株式会社○○不動産」
                <span class="bad-comment">"蒲田" "不動産"などそれぞれ独立したワードとして認識されてしまう可能性が高いです。<br>サイトに適したワードを完全一致で設置しましょう。</span>
        </ul>
    </div>
</div>

<div class="section">
    <ul class="seo-asterisk-list">
        <li id="one">※1 サイト名、サイトの説明における「完全一致」とは...<br>
            「蒲田 不動産」で対策を行いたい場合は、"蒲田"+"助詞"+"不動産"で作成しましょう。<br>
            「蒲田の不動産なら・・・」や「蒲田で不動産を・・・」などにすることを推奨します。<br>
            「蒲田の不動産<span class="txt-red">情報</span>」や「蒲田で不動産<span class="txt-red">物件</span>を・・・」などは別ワードとして認識されてしまう可能性があります。
        </li>

        <li id="two">※2 キーワードおける「完全一致」とは...<br>
            「蒲田 不動産」で対策を行いたい場合は、"蒲田"+"半角スペース"+"不動産"で作成しましょう。
        </li>
    </ul>
</div>
@endsection