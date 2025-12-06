<?php
// backend/function.php

// Garante que o arquivo de conexão seja incluído
include_once 'banco.php';

// A variável $conn (conexão com o banco de dados) agora está disponível globalmente nas funções

/**
 * =================================
 * FUNÇÕES CRUD - USER
 * =================================
 */

// Busca um usuário pelo email (usado no Login)
function getUserByEmail(string $email) {
    global $conn;
    $stmt = $conn->prepare("SELECT id, nome, sobrenome, email, senha, biografia, avatar_url FROM user WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(); // Retorna o array associativo (ou false se não encontrar)
    return $user;
}

// Cria um novo usuário (usado no Cadastro)
function createUser(array $dados) {
    global $conn;
    
    // Verifica se o email já existe para evitar duplicidade
    if (getUserByEmail($dados['email'])) {
        return "Email já cadastrado.";
    }

    try {
        // 1. Hashear a Senha (Segurança!)
        $senhaHash = password_hash($dados['senha'], PASSWORD_DEFAULT);
        $dataRegistro = date('Y-m-d H:i:s'); // Usando datetime para maior precisão, ajuste conforme seu DB

        $sql = "INSERT INTO user (nome, sobrenome, senha, email, biografia, avatar_url, data_registro) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $dados['nome'],
            $dados['sobrenome'],
            $senhaHash,
            $dados['email'],
            $dados['biografia'] ?? null,
            $dados['avatar_url'] ?? null,
            $dataRegistro
        ]);

        // Retorna o ID do usuário recém-criado
        return $conn->lastInsertId(); 

    } catch (\PDOException $e) {
        // Logar o erro se necessário. Retorna uma mensagem de erro genérica.
        return "Erro interno ao cadastrar: " . $e->getMessage();
    }
}


/**
 * =================================
 * FUNÇÕES CRUD - POSTS
 * =================================
 */

// Busca todas as postagens (usado no Feed)
function getAllPosts(int $limit = 20, int $userIdLogado = null) {
    global $conn;

    // A query faz um JOIN com a tabela 'user' para pegar o nome do autor
    // e um LEFT JOIN com 'likes' para calcular a contagem total e verificar se o usuário logado curtiu.
    $sql = "SELECT 
                p.id, p.titulo, p.corpo, p.data_criacao, p.user_id,
                u.nome AS nome_autor, 
                COUNT(l.user_id) AS total_likes,
                (SELECT COUNT(id) FROM likes WHERE post_id = p.id AND user_id = :user_id_logado_sub) AS curtido_por_usuario
            FROM post p
            JOIN user u ON p.user_id = u.id
            LEFT JOIN likes l ON p.id = l.post_id
            GROUP BY p.id
            ORDER BY p.data_criacao DESC
            LIMIT :limit";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    // Bind do ID do usuário logado para a subquery (se for null, bind 0 para não falhar)
    $stmt->bindValue(':user_id_logado_sub', $userIdLogado ?? 0, PDO::PARAM_INT); 
    
    $stmt->execute();
    return $stmt->fetchAll();
}

// Busca uma postagem por ID
function getPostByID(int $postId, int $userIdLogado = null) {
    global $conn;

    $sql = "SELECT 
                p.id, p.titulo, p.corpo, p.data_criacao, p.user_id,
                u.nome AS nome_autor, 
                COUNT(l.user_id) AS total_likes,
                (SELECT COUNT(id) FROM likes WHERE post_id = :post_id_sub AND user_id = :user_id_logado_sub) AS curtido_por_usuario
            FROM post p
            JOIN user u ON p.user_id = u.id
            LEFT JOIN likes l ON p.id = l.post_id
            WHERE p.id = :post_id_main
            GROUP BY p.id";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':post_id_main', $postId, PDO::PARAM_INT);
    $stmt->bindValue(':post_id_sub', $postId, PDO::PARAM_INT);
    $stmt->bindValue(':user_id_logado_sub', $userIdLogado ?? 0, PDO::PARAM_INT); 
    
    $stmt->execute();
    return $stmt->fetch();
}

// Cria um novo post
function createPost(array $data) {
    global $conn;
    try {
        $sql = "INSERT INTO post (titulo, corpo, user_id, data_criacao) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $data['titulo'],
            $data['corpo'],
            $data['user_id']
        ]);
        return $conn->lastInsertId();
    } catch (\PDOException $e) {
        return false;
    }
}

// Atualiza um post (verifica se o user_id é o autor)
function updatePost(array $data) {
    global $conn;
    
    // 1. Verifica se o usuário é o autor do post
    $post = getPostByID($data['id']);
    if (!$post || (int)$post['user_id'] !== (int)$data['user_id']) {
        return false; // Falha na autorização
    }

    try {
        $sql = "UPDATE post SET titulo = ?, corpo = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$data['titulo'], $data['corpo'], $data['id']]);
        return $stmt->rowCount() > 0;
    } catch (\PDOException $e) {
        return false;
    }
}

// Deleta um post (verifica se o user_id é o autor)
function deletePost(int $postId, int $userId) {
    global $conn;

    // 1. Verifica se o usuário é o autor do post
    $post = getPostByID($postId);
    if (!$post || (int)$post['user_id'] !== (int)$userId) {
        return false; // Falha na autorização
    }

    try {
        // O MySQL/MariaDB deve estar configurado com ON DELETE CASCADE para apagar os likes associados
        $sql = "DELETE FROM post WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$postId]);
        return $stmt->rowCount() > 0;
    } catch (\PDOException $e) {
        return false;
    }
}

/**
 * =================================
 * FUNÇÕES LIKES
 * =================================
 */

// Função de toggle (se o like existe, remove; se não, adiciona)
function updateLikes(int $postId, int $userId) {
    global $conn;

    // 1. Verifica se o like já existe
    $sqlCheck = "SELECT id FROM likes WHERE post_id = ? AND user_id = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->execute([$postId, $userId]);
    $likeExistente = $stmtCheck->fetch();

    try {
        $conn->beginTransaction(); // Inicia uma transação para garantir atomicidade

        if ($likeExistente) {
            // 2. Se existe, remove (Descurtir)
            $sqlAction = "DELETE FROM likes WHERE id = ?";
            $stmtAction = $conn->prepare($sqlAction);
            $stmtAction->execute([$likeExistente['id']]);
            $acao = 'removido';
        } else {
            // 3. Se não existe, adiciona (Curtir)
            $sqlAction = "INSERT INTO likes (post_id, user_id) VALUES (?, ?)";
            $stmtAction = $conn->prepare($sqlAction);
            $stmtAction->execute([$postId, $userId]);
            $acao = 'adicionado';
        }
        
        // 4. Recalcula a contagem total de likes
        $sqlCount = "SELECT COUNT(id) FROM likes WHERE post_id = ?";
        $stmtCount = $conn->prepare($sqlCount);
        $stmtCount->execute([$postId]);
        $totalLikes = $stmtCount->fetchColumn();

        $conn->commit(); // Confirma as alterações

        // 5. Retorna o status da operação
        return [
            'acao' => $acao,
            'total_likes' => (int)$totalLikes
        ];

    } catch (\PDOException $e) {
        $conn->rollBack(); // Em caso de erro, desfaz as alterações
        return ['message' => "Erro no banco de dados: " . $e->getMessage()];
    }
}

// ... Outras funções (getPostByUser, getPostBySearch, etc.) podem ser implementadas aqui se necessário.

?>
