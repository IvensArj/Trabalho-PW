<?php

session_start();

if (!isset($_SESSION["usuario_id"])) {

    header("Location: ../crud_usuarios/login.php");

    exit;

}
