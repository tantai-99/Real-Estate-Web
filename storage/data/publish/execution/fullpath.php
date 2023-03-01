<?php
//echo __FILE__;
$result = [ "success" => true, "result" => __FILE__ ];
echo json_encode($result);
unlink(basename(__FILE__));
exit;
;?>