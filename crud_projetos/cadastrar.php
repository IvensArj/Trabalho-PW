<?php

require_once "../includes/verificar_login.php";
require_once "../config/conexao.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    validarCsrf();

    $titulo = trim($_POST["titulo"] ?? "");
    $descricao = trim($_POST["descricao"] ?? "");
    $idUser = $_SESSION["usuario_id"];
    $dataEntrega = $_POST["data_entrega"] ?? null;
    $dataCriacao = date("Y-m-d H:i:s");

    if (empty($titulo) || empty($dataEntrega)) {
        redirecionarComFlash("cadastro.php", "error", "Preencha todos os campos.");
    }

    $foto_perfil = "default.png";

    if (!empty($_POST["avatar_desenho"])) {

        $base64 = preg_replace(
            '#^data:image/\w+;base64,#i',
            '',
            $_POST["avatar_desenho"]
        );

        $dadosImagem = base64_decode($base64);

        if ($dadosImagem !== false) {

            $nomeArquivo =
                "avatar_" . uniqid() . ".png";

            $diretorio =
                "../uploads/avatares/";

            if (!is_dir($diretorio)) {
                mkdir($diretorio, 0777, true);
            }

            $caminhoCompleto =
                $diretorio . $nomeArquivo;

            file_put_contents(
                $caminhoCompleto,
                $dadosImagem
            );
        }
    }

    $sql = "INSERT INTO projetos
            (
                titulo,
                descricao,
                id_user,
                data_entrega,
                data_criacao
            )
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $titulo,
        $descricao,
        $idUser,
        $dataEntrega,
        $dataCriacao
    ]);

    header("Location: ../dashboard/index.php");
    exit;
}

header("Location: cadastro.php");
exit;
