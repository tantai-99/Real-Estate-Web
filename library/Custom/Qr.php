<?php
namespace Library\Custom;
require_once 'Qr/qrlib.php';
class Qr extends \QRcode {

    public static function pngBinary($url, $size='H', $square_size=5, $margin=2){
        // size form: L,M,Q,H. lowest: L

        ob_start();
        \QRcode::png($url, null, $size, $square_size, $margin);
        $binary = ob_get_contents();
        ob_end_clean();
        return $binary;
    }

}