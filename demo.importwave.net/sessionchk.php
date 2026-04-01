<?php
session_start();
if(!isset($_SESSION['user'])) {
    header('Location: denied.php');
    exit;
}
?>