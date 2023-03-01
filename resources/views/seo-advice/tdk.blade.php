@extends('layouts.seo-advice')

@section('title', __('SEOアドバイス│ホームページ作成ツール |'))

@section('content')
<div class="section">
    <h2>基本設定</h2>
    <p>「基本設定」内項目を入力する際には、下記点に気を付けて入力しましょう。</p>
    <div class="seo-example">
        <p>下記の不動産会社を参考にご説明致します。<br>
            社名：株式会社○○不動産　SEO対象ワード：「蒲田 不動産」<br>
            概要：蒲田を中心に不動産（賃貸・売買物件）を取り扱う地域密着型の不動産会社</p>
    </div>
</div>
<div class="section">
    <h2>ページタイトル</h2>
    <p>ページの主題を記載しましょう。</p>

    <div class="text-block">
        <h3>ポイント</h3>
        <ul class="seo-number-list">
            <li>&#9312; 他ページと文頭の重複を避けながら各ページがそれぞれどう違うのかわかるように記載を行いましょう。</li>
        </ul>
    </div>

    <div class="text-block">
        <h3>設定例</h3>
        <ul class="seo-good">
            <li>会社紹介→　会社紹介<span class="txt-gray">|蒲田の不動産なら株式会社○○不動産</span><br>
                蒲田店の店舗詳細ページ→　 蒲田店の店舗紹介<span class="txt-gray">|蒲田の不動産なら株式会社○○不動産</span><br>
                大森店の店舗詳細ページ→　大森店の店舗紹介<span class="txt-gray">|蒲田の不動産なら株式会社○○不動産</span><br>
                ※「|」以降は自動的にTOPページの「サイト名」が引き継がれます。
            </li>
        </ul>
    </div>

    <div class="text-block">
        <h3>ポイント詳細</h3>
        <ul class="seo-bad">
            <li>&#9312; NG事例：蒲田店の店舗詳細ページ→「<span class="txt-red">店舗詳細</span>|蒲田の不動産なら株式会社○○不動産」<br>
                大森店の店舗詳細ページ→「<span class="txt-red">店舗詳細</span>|蒲田の不動産なら株式会社○○不動産」<br>
                <span class="bad-comment">文頭の重複を避けながら「蒲田店」や「大森店」などページごとに正しく記載しましょう。</span>
            </li>
        </ul>
    </div>
</div>


<div class="section">
    <h2>ページの説明</h2>
    <p>ページの概要を記載しましょう。</p>

    <div class="text-block">
        <h3>ポイント</h3>
        <ul class="seo-number-list">
            <li>&#9312; ワードを詰め込みすぎや羅列は避けましょう。</li>
            <li>&#9313; ページ内のテキストを使いまわさないようにしましょう。</li>
        </ul>
    </div>

    <div class="text-block">
        <h3>設定例</h3>
        <ul class="seo-good">
            <li>会社紹介[株式会社○○不動産の基本情報を紹介します。]<span class="txt-gray">：蒲田を中心に不動産のことなら株式会社○○不動産にお任せください。物件情報だけでなく蒲田の周辺情報やオススメスポットなどの情報も盛りだくさん。</span><br>※「：」以降は自動的にTOPページの「サイトの説明」が引き継がれます。
            </li>
        </ul>
    </div>

    <div class="text-block">
        <h3>ポイント詳細</h3>
        <ul class="seo-bad">
            <li>&#9312; NG事例：会社紹介[蒲田・大森・大森の不動産・賃貸・売買・ペット可・駅近]<span class="txt-gray">：蒲田を中心に不動産のことなら株式会社○○不動産にお任せください。物件情報だけでなく蒲田の周辺情報やオススメスポットなどの情報も盛りだくさん。</span>
                <span class="bad-comment">ワードの過度な盛り込みや不自然な羅列は検索エンジンからの評価が下がる可能性があります。</span>
            </li>
            <li class="comment-only">&#9313;
                <span class="bad-comment">ページ内で使用しているテキストをそのまま「サイトの説明」に流用するのはやめましょう。<br>品質の低下につながります。</span>
            </li>
        </ul>
    </div>
</div>


<div class="section">
    <h2>ページのキーワード</h2>
    <p>ページのメインとなるワードを記載しましょう。</p>

    <div class="text-block">
        <h3>ポイント</h3>
        <ul class="seo-number-list">
            <li>&#9312;「完全一致（<a href="#one">※1</a>）」でワードを盛り込みましょう。</li>
        </ul>
    </div>

    <div class="text-block">
        <h3>設定例</h3>
        <ul class="seo-good">
            <li>会社紹介,<span class="txt-gray">蒲田 不動産,株式会社○○不動産</span><br>
                ※TOPページの「キーワード」は自動的に引き継がれます。
            </li>
        </ul>
    </div>

    <div class="text-block">
        <h3>ポイント詳細</h3>
        <ul class="seo-bad">
            <li>&#9312; NG事例：「蒲田,不動産,株式会社○○不動産」
                <span class="bad-comment">"蒲田" "不動産"などそれぞれ独立したワードとして認識されてしまう可能性が高いです。</span>
            </li>
        </ul>
    </div>
</div>


<div class="section">
    <h2>ページ名</h2>
    <p>ページを表す名称を半角英数字で記載しましょう。</p>

    <div class="text-block">
        <h3>ポイント</h3>
        <ul class="seo-number-list">
            <li>&#9312; ページに合った名称を記載しましょう。</li>
        </ul>
    </div>

    <div class="text-block">
        <h3>設定例</h3>
        <ul class="seo-good">
            <li>会社紹介→「company」<br>蒲田店→「kamata」<br>蒲田の街情報→「kamatatown」<br>オーナー様向けページ→「owner」</li>
        </ul>
    </div>

    <div class="text-block">
        <h3>ポイント詳細</h3>
        <ul class="seo-bad">
            <li>&#9312; NG事例：会社紹介→「1」<br>蒲田店→「2」<br>蒲田の街情報→「3」<br>オーナー様向けページ→「4」
                <span class="bad-comment">番号などページの内容に合っていない名称は避けましょう。</span>
            </li>
        </ul>
    </div>
</div>

<div class="section">
    <ul class="seo-asterisk-list">
        <li id="one">※1 キーワードおける「完全一致」とは...<br>
            「蒲田 不動産」で対策を行いたい場合は、"蒲田"+"半角スペース"+"不動産"で作成しましょう。
        </li>
    </ul>
</div>
@endsection