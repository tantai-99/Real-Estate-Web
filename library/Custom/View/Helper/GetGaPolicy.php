<?php
namespace Library\Custom\View\Helper;

class GetGaPolicy extends  HelperAbstract {

    public function getGaPolicy() {

        return <<< EOM
◇Googleアナリティクスの利用<br />
本ウェブサイトは、サイトの閲覧状況を把握するために、Google,Inc.のGoogleアナリティクスを使用しています。<br />
本ウェブサイトにアクセスすると、お使いのウェブブラウザはGoogle,Inc.に特定の情報（たとえば、アクセスしたページのウェブ アドレスや IP アドレスなど）を自動的に送信します。<br />
これらの情報は、Google,Inc.による「ユーザーがGoogle パートナーのサイトやアプリを使用する際の Google によるデータ使用」（www.google.com/policies/privacy/partners/）に従い収集、処理されます。<br />
また、Google,Inc.がお使いのブラウザに Cookie を設定したり、既存のCookie を読み取ったりする場合もあります。<br />
EOM;
    }
}