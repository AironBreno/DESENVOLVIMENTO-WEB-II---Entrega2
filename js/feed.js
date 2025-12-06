// frontend/js/feed.js

import { buscarTodosPosts, atualizarLike } from './api.js';

const feedContainer = document.getElementById('feedContainer');

// ===========================================
// FUNÇÕES DE MANIPULAÇÃO DE LIKES
// ===========================================

/**
 * Função para lidar com o clique no botão de like (toggle).
 */
async function handleLikeToggle(event) {
    const postId = event.currentTarget.getAttribute('data-post-id');
    const userId = localStorage.getItem('user_id'); 

    if (!userId) {
        alert('Você precisa estar logado para curtir uma postagem!');
        return;
    }

    try {
        const resultado = await atualizarLike(postId, userId);
        
        const likeBtn = document.querySelector(`.like-btn[data-post-id="${postId}"]`);
        const likesCountSpan = document.getElementById(`likes-count-${postId}`);

        if (resultado.acao === 'adicionado') {
            likeBtn.textContent = 'Descurtir';
            likeBtn.setAttribute('data-is-liked', 'true');
        } else if (resultado.acao === 'removido') {
            likeBtn.textContent = 'Curtir';
            likeBtn.setAttribute('data-is-liked', 'false');
        }

        if (resultado.total_likes !== undefined) {
             likesCountSpan.textContent = `${resultado.total_likes} Likes`;
        } 

    } catch (error) {
        alert('Erro ao processar sua curtida: ' + error.message);
    }
}


// ===========================================
// FUNÇÕES DE RENDERIZAÇÃO
// ===========================================

/**
 * Função que cria o elemento HTML para um post.
 */
function criarElementoPost(post) {
    const postDiv = document.createElement('article');
    postDiv.classList.add('post');
    postDiv.innerHTML = `
        <h3>${post.titulo}</h3>
        <p class="meta">Postado por: ${post.nome_autor || 'Usuário Desconhecido'} em ${post.data_criacao}</p>
        <div class="corpo-post">
            ${post.corpo.substring(0, 200)}... 
        </div>
        <div class="actions">
            <button class="like-btn" data-post-id="${post.id}" data-is-liked="${post.curtido_por_usuario || false}">
                ${post.curtido_por_usuario ? 'Descurtir' : 'Curtir'}
            </button>
            <span id="likes-count-${post.id}" class="likes-count">${post.total_likes || 0} Likes</span> 
            <a href="detalhe_post.html?id=${post.id}">Ler Mais</a>
        </div>
        <hr>
    `;
    
    // Adiciona o listener de clique no botão "Curtir"
    const likeButton = postDiv.querySelector('.like-btn');
    likeButton.addEventListener('click', handleLikeToggle);
    
    return postDiv;
}


// ===========================================
// FUNÇÃO PRINCIPAL DE CARREGAMENTO
// ===========================================

/**
 * Função principal para carregar e exibir o feed.
 */
async function carregarFeed() {
    try {
        const posts = await buscarTodosPosts(20); 
        
        feedContainer.innerHTML = ''; // Limpa o "Carregando..."
        
        if (posts && posts.length > 0) {
            posts.forEach(post => {
                const postElement = criarElementoPost(post);
                feedContainer.appendChild(postElement);
            });
        } else {
            feedContainer.innerHTML = '<p>Nenhuma postagem encontrada.</p>';
        }

    } catch (error) {
        feedContainer.innerHTML = `<p class="erro">Erro ao carregar o feed: ${error.message}</p>`;
    }
}


// Inicializa o carregamento do feed quando o script é executado
carregarFeed();
