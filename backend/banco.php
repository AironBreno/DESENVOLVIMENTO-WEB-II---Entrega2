<?php
// backend/function.php
// Inclua o banco.php aqui se for necessário para a conexão ($conn)

// Exemplo da função mais crítica (Cadastro)
function createUser(array $dados) {
    global $conn; // Assumindo que $conn está disponível aqui

    // 1. Hashear a Senha
    $senhaHash = password_hash($dados['senha'], PASSWORD_DEFAULT);
    $dataRegistro = date('Y-m-d'); // Data atual

    // 2. Query de Inserção (Exemplo SQL)
    $sql = "INSERT INTO user (nome, sobrenome, senha, email, biografia, avatar_url, data_registro) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    // 3. Execução segura da query
    // ... use sua implementação para executar a query com os dados ...
    
    if (/* A query de inserção foi bem sucedida */) {
        // 4. Retorna o ID do novo usuário
        return $last_insert_id; // Sua implementação deve pegar o último ID inserido
    } else {
        // 5. Retorna uma mensagem de erro em caso de falha (ex: email duplicado)
        return "Erro: Email já cadastrado ou erro no banco."; 
    }
}

// Exemplo da função de Login
function getUserByEmail(string $email) {
    global $conn;
    
    // 1. Query de Busca (Exemplo SQL)
    $sql = "SELECT id, nome, senha, email, biografia FROM user WHERE email = ?";

    // 2. Execução segura da query
    // ... use sua implementação para executar e buscar o resultado ...

    if (/* Usuário encontrado */) {
        // 3. Retorna o array associativo completo do usuário (com a senha hasheada)
        return $usuario_encontrado_array;
    } else {
        return false;
    }
}

// ... Inclua todas as outras funções (getAllPosts, updateLikes, etc.) aqui ...

?>
