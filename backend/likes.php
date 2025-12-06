<?php
// backend/likes.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once 'banco.php';
include_once 'function.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array("message" => "Método não permitido."));
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['post_id']) || empty($data['user_id'])) {
    http_response_code(400);
    echo json_encode(array("message" => "IDs de post e usuário são obrigatórios."));
    exit();
}

$postId = $data['post_id'];
$userId = $data['user_id'];

// Chama a função que adiciona ou remove o like (toggle)
// A função updateLikes() deve retornar um array ou objeto com o status da ação.
$resultado = updateLikes($postId, $userId); 

if ($resultado && isset($resultado['acao'])) {
    http_response_code(200);
    // Retorna o resultado para que o Frontend atualize o botão e a contagem
    echo json_encode($resultado); 
} else {
    http_response_code(500);
    echo json_encode(array("message" => $resultado['message'] ?? "Falha ao atualizar like."));
}

?>
