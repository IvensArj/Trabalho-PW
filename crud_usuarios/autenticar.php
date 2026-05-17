<?php

session_start();

require_once "../config/conexao.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $senha = trim($_POST["senha"]);

    if (empty($email) || empty($senha)) {

        die("Preencha todos os campos.");

    }

    $sql = "SELECT * FROM usuarios WHERE email = ?";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([$email]);

    $usuario = $stmt->fetch();

    if ($usuario) {

        if (password_verify($senha, $usuario->senha)) {

            $_SESSION["usuario_id"] = $usuario->id_user;
            $_SESSION["usuario_nome"] = $usuario->nome;
            $_SESSION["usuario_email"] = $usuario->email;
            $_SESSION["usuario_foto"] = $usuario->foto_perfil;

            header("Location: ../dashboard/index.php");

            exit;

        } else {

            die("Email ou senha invalidos.");

        }

    } else {

        die("Email ou senha invalidos.");

    }

}
