<?php

require_once "../config/conexao.php";
require_once "../includes/funcoes.php";

iniciarSessaoSegura();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    validarCsrf();

    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $senha = trim($_POST["senha"]);

    // VALIDACOES

    if (empty($nome) || empty($email) || empty($senha)) {

        redirecionarComFlash("login.php?tab=criar", "error", "Preencha todos os campos.");

    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        redirecionarComFlash("login.php?tab=criar", "error", "Email invalido.");

    }

    if (strlen($senha) < 6) {

        redirecionarComFlash("login.php?tab=criar", "error", "A senha deve ter no minimo 6 caracteres.");

    }

    // VERIFICAR EMAIL EXISTENTE

    $sql = "SELECT id_user FROM usuarios WHERE email = ?";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {

        redirecionarComFlash("login.php?tab=criar", "error", "Este email ja esta cadastrado.");

    }

    // CRIPTOGRAFAR SENHA

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $fotoPerfil = "default.png";

    if (!empty($_POST["avatar_desenho"])) {

        $base64 = preg_replace(
            '#^data:image/\w+;base64,#i',
            '',
            $_POST["avatar_desenho"]
        );

        $dadosImagem = base64_decode($base64);

        if ($dadosImagem !== false) {

            $diretorio = "../uploads/avatares/";

            if (!is_dir($diretorio)) {
                mkdir($diretorio, 0777, true);
            }

            $nomeArquivo =
                "avatar_" . uniqid() . ".png";

            $caminho =
                $diretorio . $nomeArquivo;

            if (
                file_put_contents(
                    $caminho,
                    $dadosImagem
                ) !== false
            ) {
                $fotoPerfil = $nomeArquivo;
            }
        }
    }

    // INSERT

    $sql = "INSERT INTO usuarios
        (
            nome,
            email,
            senha,
            foto_perfil
        )
        VALUES (?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $nome,
        $email,
        $senhaHash,
        $fotoPerfil
    ]);

    unset($_SESSION["csrf_token"]);
    header("Location: login.php");

    exit;
}
