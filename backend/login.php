<?php
// backend/login.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Inclui os arquivos necessários
include_once 'banco.php';
include_once 'function.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array("message" => "Método não permitido."));
    exit();
}

// 1. Recebe os dados JSON
$data = json_decode(file_get_contents("php://input"));

// 2. Validação básica
if (empty($data->email) || empty($data->senha)) {
    http_response_code(400);
    echo json_encode(array("message" => "Por favor, forneça email e senha."));
    exit();
}

$email = $data->email;
$senhaDigitada = $data->senha;

// 3. Chama a função para buscar o usuário por email
// OBS: Essa função deve retornar um array associativo com os dados do usuário, incluindo a senha hasheada.
$user = getUserByEmail($email); 

if ($user && isset($user['senha'])) {
    // 4. Verifica a senha hasheada
    if (password_verify($senhaDigitada, $user['senha'])) {
        
        // Remove a senha do array antes de enviar ao Frontend por segurança
        unset($user['senha']); 
        
        http_response_code(200); // OK
        echo json_encode(array(
            "message" => "Login realizado com sucesso!",
            "user" => $user,
            "id" => $user['id'] // Retorna o ID, que será salvo no localStorage do frontend
        ));
    } else {
        // Senha incorreta
        http_response_code(401); 
        echo json_encode(array("message" => "Credenciais inválidas: Senha incorreta."));
    }
} else {
    // Usuário não encontrado
    http_response_code(401); 
    echo json_encode(array("message" => "Credenciais inválidas: Usuário não encontrado."));
}

?>
