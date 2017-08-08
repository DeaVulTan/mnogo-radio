<?php
session_start();

if ( md5(md5($_SESSION['password'])) != '332f6acb1f146a41b6bcccad38d5138b' ) //turuhtan
{
    $_SESSION['error']='Неверный пароль!';
    header('Location: /admin/login.php');
    exit();
}
?>