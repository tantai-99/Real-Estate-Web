@extends('layouts.seo-advice')
@section('content')
<div class="section">
    <h2>メインコンテンツ</h2>
    <p>「メインコンテンツ」内項目を入力する際には、下記点に気を付けて入力しましょう。</p>
</div>
<div class="section">
    <h2>画像の説明</h2>
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
                <span class="bad-comment">画像に埋め検索エンジンは画像化されたテキストは認識することが出来ません。</span>
            </li>
            <li class="comment-only">&#9313; <span class="bad-comment">他サイトのコピーはユーザにとって有益でないと判断され評価されません。</span>
            </li>
            <li class="comment-only">&#9314; <span class="bad-comment">情報量の多いサイトは、ユーザにとってメリットの多いサイトとして評価されます。</span>
            </li>
        </ul>
    </div>
</div>
@endsection
