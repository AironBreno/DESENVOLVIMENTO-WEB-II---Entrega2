<?php
// backend/posts.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Permite todos os métodos necessários
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE"); 

include_once 'banco.php';
include_once 'function.php';

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true); // true para array associativo

switch ($method) {
    case 'GET':
        // Lógica para getPostByID (se tiver ?id=X na URL) ou getAllPosts
        $postId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        
        if ($postId) {
            // Chama getPostByID(postid)
            $post = getPostByID($postId); 
            
            if ($post) {
                http_response_code(200);
                echo json_encode($post);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Postagem não encontrada."));
            }
        } else {
            // Chama getAllPosts (com limite, se fornecido)
            $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?? 20; 
            $posts = getAllPosts($limit); 

            http_response_code(200);
            echo json_encode($posts);
        }
        break;

    case 'POST':
        // Lógica para createPost (criação de um novo post)
        if (empty($data['titulo']) || empty($data['corpo']) || empty($data['user_id'])) {
            http_response_code(400);
            echo json_encode(array("message" => "Dados do post incompletos."));
            exit;
        }
        
        // A data_criacao é preenchida dentro da função createPost()
        $result = createPost($data); 

        if ($result) {
            http_response_code(201);
            echo json_encode(array("message" => "Postagem criada com sucesso!", "id" => $result));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Falha ao criar postagem no banco de dados."));
        }
        break;

    case 'PUT':
        // Lógica para updatePost (atualização de um post)
        // O ID do post deve vir na URL (ex: posts.php?id=X)
        $postId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        
        if (!$postId || empty($data['titulo']) || empty($data['corpo']) || empty($data['user_id'])) {
            http_response_code(400);
            echo json_encode(array("message" => "Dados incompletos ou ID do post ausente."));
            exit;
        }

        // Você deve passar o ID do post, os dados, e o user_id para sua função updatePost()
        $data['id'] = $postId;
        $success = updatePost($data); 

        if ($success) {
            http_response_code(200);
            echo json_encode(array("message" => "Postagem atualizada com sucesso."));
        } else {
            http_response_code(403); // Proibido (se o user_id não for o autor)
            echo json_encode(array("message" => "Falha ao atualizar postagem. Verifique permissões."));
        }
        break;

    case 'DELETE':
        // Lógica para deletePost (exclusão de um post)
        // O ID do post deve vir na URL (ex: posts.php?id=X)
        $postId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$postId || empty($data['user_id'])) {
            http_response_code(400);
            echo json_encode(array("message" => "ID do post ou ID do usuário ausente."));
            exit;
        }
        
        // Você deve passar o ID do post e o user_id para sua função deletePost()
        $success = deletePost($postId, $data['user_id']); 

        if ($success) {
            http_response_code(200);
            echo json_encode(array("message" => "Postagem deletada com sucesso."));
        } else {
            http_response_code(403); // Proibido
            echo json_encode(array("message" => "Falha ao deletar postagem. Verifique permissões."));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array("message" => "Método HTTP não suportado."));
        break;
}

?>
