//FUNÇÕES GET/POST (busca e retorna algo):

// realizar a procura de usuário por email e retornar o id de usuário ou
os dados de usuário

function getUserByEmail(string $email): ?array {
    $pdo = getConnection();
    $stmt = $pdo->prepare('SELECT * FROM "user" WHERE email = :email LIMIT 1;');
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch();
    return $user !== false ? $user : null;
}



// realizar a procura de usuário por ID e retornar os dados de usuário

function getUserByID(int $userId): ?array {
    $pdo = getConnection();
    $stmt = $pdo->prepare('SELECT * FROM "user" WHERE id = :id LIMIT 1;');
    $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch();
    return $user !== false ? $user : null;
}


// buscar todas as postagens e retornar dentro do limite

function getAllPosts(int $limit = 50): array {
    if ($limit < 1) { $limit = 1; }
    $pdo = getConnection();
    $sql = <<<SQL
        SELECT
            p.*,
            u.nome           AS autor_nome,
            u.sobrenome  AS autor_sobrenome,
            u.email      AS autor_email
        FROM post p
        JOIN "user" u ON u.id = p.user_id
        ORDER BY datetime(p.data_criacao) DESC, p.id DESC
        LIMIT :limit;
    SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}


// realizar a procura por uma postagem específica (postid) e retornar os
dados desta postagem

function getPostByID(int $postId): ?array {
    $pdo = getConnection();
    $sql = <<<SQL
            SELECT
                   p.*,
                   u.nome           AS autor_nome,
                   u.sobrenome  AS autor_sobrenome,
                   u.email           AS autor_email
            FROM post p
            JOIN "user" u ON u.id = p.user_id
            WHERE p.id = :postID;
    SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':postID', $postID, PDO::PARAM_INT);
    $stmt->execute();
    $post = $stmt->fetch();

    return $post !== false ? $post : null;
}

// buscar todas as postagens realizadas por um usuário e retornar os
dados da postagem

function getAllPostByUser(int $userId): array {
    $pdo = getConnection();
    $sql = <<<SQL
        SELECT
                   p.*,
                   u.nome          AS autor_nome,
                   u.sobrenome AS autor_sobrenome
        FROM post p
        JOIN "user" u ON u.id = p.user_id
        WHERE p.user_id = :user_id
        ORDER BY datetime(p.data_criacao) DESC;
    SQL;
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

// realizar uma busca por similaridade da string com o 'corpo' das postagens
// retornar as postagens que se enquadrem

function getPostBySearchContent(string $searchTerm): array {
    $pdo = getConnection();
    // Adiciona os curingas (%) para buscar em qualquer parte do texto
    $likeTerm = '%' . $searchTerm . '%';
    
    $sql = 'SELECT * FROM post WHERE corpo LIKE :search ORDER BY data_criacao DESC;';
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':search', $likeTerm, PDO::PARAM_STR);
    $stmt->execute();

    return $stmt->fetchAll();
}


// realizar uma busca por similaridade da string com o 'título' das postagens
// retornar as postagens que se enquadrem

function getPostBySearchTitle(string $searchTerm): array {
    $pdo = getConnection();
    $likeTerm = '%' . $searchTerm . '%';

    $sql = 'SELECT * FROM post WHERE titulo LIKE :search ORDER BY data_criacao DESC;';
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':search', $likeTerm, PDO::PARAM_STR);
    $stmt->execute();

    return $stmt->fetchAll();
}

// procurar as postagens curtidas pelo usuário

function getPostBySearchTitle(string $searchTerm): array {
    $pdo = getConnection();
    $likeTerm = '%' . $searchTerm . '%';

    $sql = 'SELECT * FROM post WHERE titulo LIKE :search ORDER BY data_criacao DESC;';
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':search', $likeTerm, PDO::PARAM_STR);
    $stmt->execute();

    return $stmt->fetchAll();
}

// buscar e contar os likes de uma determinada postagem, retornar a contagem

function getPostLikes(int $postId): int {
    $pdo = getConnection();
    $sql = 'SELECT COUNT(*) FROM likes WHERE id_post = :post_id;';
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
    $stmt->execute();

    // fetchColumn() é ideal para buscar um único valor de uma coluna
    return (int) $stmt->fetchColumn();
}



//FUNÇÕES CREATE/UPDATE (atualiza ou cria registro no banco de dados):



// Insere os dados de usuário no banco criando um novo usuário

function createUser(array $userData): int {
    $pdo = getConnection();
    $sql = <<<SQL
        INSERT INTO "user" (nome, sobrenome, senha, email, biografia, avatar_url)
        VALUES (:nome, :sobrenome, :senha, :email, :biografia, :avatar_url);
    SQL;
    
    $stmt = $pdo->prepare($sql);
    
    $hashedPassword = password_hash($userData['senha'], PASSWORD_DEFAULT);

    $stmt->bindValue(':nome', $userData['nome'], PDO::PARAM_STR);
    $stmt->bindValue(':sobrenome', $userData['sobrenome'], PDO::PARAM_STR);
    $stmt->bindValue(':senha', $hashedPassword, PDO::PARAM_STR);
    $stmt->bindValue(':email', $userData['email'], PDO::PARAM_STR);
    $stmt->bindValue(':biografia', $userData['biografia'] ?? null, PDO::PARAM_STR);
    $stmt->bindValue(':avatar_url', $userData['avatar_url'] ?? null, PDO::PARAM_STR);
    
    $stmt->execute();
    
    // Retorna o ID do último usuário inserido
    return (int) $pdo->lastInsertId();
}



// atualiza informações do usuário

function updateUser(int $userId, array $userData): bool {
    $pdo = getConnection();
    $sql = <<<SQL
        UPDATE "user" SET
            nome = :nome,
            sobrenome = :sobrenome,
            email = :email,
            biografia = :biografia,
            avatar_url = :avatar_url
        WHERE id = :id;
    SQL;

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(':nome', $userData['nome'], PDO::PARAM_STR);
    $stmt->bindValue(':sobrenome', $userData['sobrenome'], PDO::PARAM_STR);
    $stmt->bindValue(':email', $userData['email'], PDO::PARAM_STR);
    $stmt->bindValue(':biografia', $userData['biografia'] ?? null, PDO::PARAM_STR);
    $stmt->bindValue(':avatar_url', $userData['avatar_url'] ?? null, PDO::PARAM_STR);
    $stmt->bindValue(':id', $userId, PDO::PARAM_INT);

    return $stmt->execute();
}



// apagar um usuário do banco

function deleteUser(int $userId): bool {
    $pdo = getConnection();
    $sql = 'DELETE FROM "user" WHERE id = :id;';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
    return $stmt->execute();
}



// Insere um novo post com vínculo ao usuário

function createPost(array $postData, int $userId): int {
    $pdo = getConnection();
    $sql = 'INSERT INTO post (titulo, corpo, user_id) VALUES (:titulo, :corpo, :user_id);';
    $stmt = $pdo->prepare($sql);
    
    $stmt->bindValue(':titulo', $postData['titulo'], PDO::PARAM_STR);
    $stmt->bindValue(':corpo', $postData['corpo'], PDO::PARAM_STR);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    
    $stmt->execute();
    
    return (int) $pdo->lastInsertId();
}



// atualiza os dados de um post

function updatePost(array $postData, int $postId, int $userId): bool {
    $pdo = getConnection();
    $sql = <<<SQL
        UPDATE post SET
            titulo = :titulo,
            corpo = :corpo
        WHERE id = :id AND user_id = :user_id;
    SQL;

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(':titulo', $postData['titulo'], PDO::PARAM_STR);
    $stmt->bindValue(':corpo', $postData['corpo'], PDO::PARAM_STR);
    $stmt->bindValue(':id', $postId, PDO::PARAM_INT);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);

    return $stmt->execute();
}


// apagar um post do banco

function deletePost(int $postId): bool {
    $pdo = getConnection();
    $sql = 'DELETE FROM post WHERE id = :id;';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $postId, PDO::PARAM_INT);
    return $stmt->execute();
}

// função dois em 1, se não houver um like adiciona, se houver o like
remove (toggle)
// retorna true ou false (conforme a execução foi bem sucedida)
// ou cria um função para cada e lida com a existência de like no
programa

function updateLikes(int $postId, int $userId): ?bool {
    $pdo = getConnection();


    $pdo->beginTransaction();

    try {
        // 1. Verifica se o like já existe
        $sqlCheck = 'SELECT COUNT(*) FROM likes WHERE id_user = :user_id AND id_post = :post_id;';
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmtCheck->bindValue(':post_id', $postId, PDO::PARAM_INT);
        $stmtCheck->execute();
        $likeExists = $stmtCheck->fetchColumn() > 0;

        if ($likeExists) {
            // 2a. Se existe, remove (unlike)
            $sqlToggle = 'DELETE FROM likes WHERE id_user = :user_id AND id_post = :post_id;';
            $result = false; // O like foi removido
        } else {
            // 2b. Se não existe, adiciona (like)
            $sqlToggle = 'INSERT INTO likes (id_user, id_post) VALUES (:user_id, :post_id);';
            $result = true; // O like foi adicionado
        }
        
        $stmtToggle = $pdo->prepare($sqlToggle);
        $stmtToggle->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmtToggle->bindValue(':post_id', $postId, PDO::PARAM_INT);
        $stmtToggle->execute();

        // Se tudo deu certo, confirma as alterações
        $pdo->commit();

        return $result;

    } catch (PDOException $e) {
        // Se algo deu errado, desfaz tudo
        $pdo->rollBack();
        // Opcional: registrar o erro $e->getMessage()
        return null; // Indica que houve um erro na operação
    }
}















