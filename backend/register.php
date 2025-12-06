<?php
// backend/register.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Inclui o arquivo de funções, que por sua vez inclui o banco.
include_once 'function.php'; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array("message" => "Método não permitido."));
    exit();
}

$data = json_decode(file_get_contents("php://input"), true); // true para array associativo

if (
    empty($data['nome']) ||
    empty($data['sobrenome']) ||
    empty($data['email']) ||
    empty($data['senha'])
) {
    http_response_code(400); 
    echo json_encode(array("message" => "Dados incompletos. Por favor, forneça nome, sobrenome, email e senha."));
    exit();
}

$result = createUser($data); 

if ($result && is_numeric($result)) {
    http_response_code(201); // Criado
    echo json_encode(array("message" => "Usuário criado com sucesso!", "id" => $result));
} else {
    // Se a função retornar uma string, é a mensagem de erro (ex: "Email já cadastrado.")
    http_response_code(400); 
    echo json_encode(array("message" => $result ?? "Erro desconhecido ao cadastrar usuário."));
}

?>
