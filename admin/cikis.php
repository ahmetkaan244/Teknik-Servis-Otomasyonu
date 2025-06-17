<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/config/config.php';
session_destroy();
yonlendir('admin/giris.php');
?> 