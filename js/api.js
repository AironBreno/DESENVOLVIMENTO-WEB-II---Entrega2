// frontend/js/api.js

// URL base do seu backend em PHP (Mantenha o mesmo valor usado nos arquivos PHP)
const BASE_URL = 'http://localhost/seu-diretorio-do-backend'; 

/**
 * Funções Auxiliares de Requisição (Métodos Genéricos)
 */

async function executarRequisicao(endpoint, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        },
    };

    if (data) {
        options.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(`${BASE_URL}${endpoint}`, options);
        const result = await response.json();

        if (!response.ok) {
            // Lança um erro com a mensagem retornada pelo PHP
            throw new Error(result.message || `Erro na requisição: ${response.status}`);
        }
        return result;
    } catch (error) {
        console.error(`Erro na requisição ${method} para ${endpoint}:`, error);
        throw error;
    }
}


/**
 * --------------------------------
 * FUNÇÕES CRUD - USER
 * --------------------------------
 */

// POST para /register.php
export function cadastrarUsuario(userData) {
    return executarRequisicao('/register.php', 'POST', userData);
}

// POST para /login.php
export function fazerLogin(email, senha) {
    return executarRequisicao('/login.php', 'POST', { email, senha });
}

/**
 * --------------------------------
 * FUNÇÕES CRUD - POSTS
 * --------------------------------
 */

// GET para /posts.php
export function buscarTodosPosts(limit = 20) {
    return executarRequisicao(`/posts.php?limit=${limit}`);
}

// GET para /posts.php?id=X
export function buscarPostPorID(postId) {
    return executarRequisicao(`/posts.php?id=${postId}`);
}

// POST para /posts.php
export function criarPostagem(postData) {
    return executarRequisicao('/posts.php', 'POST', postData);
}

// PUT para /posts.php?id=X
export function atualizarPost(postId, postData) {
    return executarRequisicao(`/posts.php?id=${postId}`, 'PUT', postData);
}

// DELETE para /posts.php?id=X
export function deletarPost(postId, userId) {
    // Passa o userId no corpo para que o backend verifique a permissão
    return executarRequisicao(`/posts.php?id=${postId}`, 'DELETE', { user_id: userId });
}

/**
 * --------------------------------
 * FUNÇÕES LIKES
 * --------------------------------
 */

// POST para /likes.php (Faz o toggle: curtir/descurtir)
export function atualizarLike(postId, userId) {
    return executarRequisicao('/likes.php', 'POST', { post_id: postId, user_id: userId });
}
