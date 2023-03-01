<?php
$token = sha1(uniqid(mt_rand(), true));
$_SESSION['token'] = $token;
;?>
<input type="hidden" name="token" value="<?php echo $token ;?>"/>