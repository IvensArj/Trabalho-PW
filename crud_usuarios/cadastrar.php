<?php

require_once "../config/conexao.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $senha = trim($_POST["senha"]);
    $confirmarSenha = trim($_POST["confirmar_senha"]);

    // VALIDACOES

    if (empty($nome) || empty($email) || empty($senha) || empty($confirmarSenha)) {

        die("Preencha todos os campos.");

    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        die("Email invalido.");

    }

    if ($senha !== $confirmarSenha) {

        die("As senhas nao coincidem.");

    }

    if (strlen($senha) < 6) {

        die("A senha deve ter no minimo 6 caracteres.");

    }

    // VERIFICAR EMAIL EXISTENTE

    $sql = "SELECT id_user FROM usuarios WHERE email = ?";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {

        die("Este email ja esta cadastrado.");

    }

    // CRIPTOGRAFAR SENHA

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    // INSERT

    $sql = "INSERT INTO usuarios (nome, email, senha)
            VALUES (?, ?, ?)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $nome,
        $email,
        $senhaHash
    ]);

    header("Location: login.php");

    exit;

}
