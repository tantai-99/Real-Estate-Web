<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($_SESSION['token'] != $_POST['token']) {

        return  array('不正なアクセスです。');
    };

    $viewHelper = new ViewHelper($this->_view);
    include_once($viewHelper->_view->scriptPath.'/'.'Auth.php');
    $auth = new Auth($viewHelper);

    if ($auth->verify($_POST['id'], $_POST['pass'])) {

        $auth->redirect();
    }
    else {

        return array('IDとパスワードをご確認ください。');
    }
}

return array();

?>