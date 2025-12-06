// frontend/js/detalhe_post.js

import { buscarPostPorID, atualizarPost, deletarPost } from './api.js';

const postDetalhe = document.getElementById('postDetalhe');
const edicaoForm = document.getElementById('edicaoForm');
const mensagemStatus = document.getElementById('mensagemStatus');
let currentPostId = null;

function getPostIdFromUrl() {
    const params = new URLSearchParams(window.location.search);
    return params.get('id');
}

function exibirStatus(mensagem, tipo = 'sucesso') {
    mensagemStatus.textContent = mensagem;
    mensagemStatus.className = tipo;
    mensagemStatus.style.display = 'block';
}

// ---------------- Funções de UI -----------------

function exibirPost(post) {
    edicaoForm.style.display = 'none';

    postDetalhe.innerHTML = `
        <h2>${post.titulo}</h2>
        <p class="meta">Postado por: ${post.nome_autor} em ${post.data_criacao}</p>
        <div class="corpo-post">${post.corpo}</div>
        <p class="likes-count">Likes: ${post.total_likes}</p>
        <hr>
    `;

    const userId = localStorage.getItem('user_id');
    // Verifica se o usuário logado é o autor do post
    if (userId && parseInt(userId) === parseInt(post.user_id)) {
        const actionsDiv = document.createElement('div');
        actionsDiv.classList.add('actions');
        
        const editBtn = document.createElement('button');
        editBtn.textContent = 'Editar';
        editBtn.addEventListener('click', () => iniciarEdicao(post));
        
        const deleteBtn = document.createElement('button');
        deleteBtn.textContent = 'Deletar';
        deleteBtn.classList.add('btn-delete');
        deleteBtn.addEventListener('click', () => confirmarDelecao(post.id, userId));
        
        actionsDiv.appendChild(editBtn);
        actionsDiv.appendChild(deleteBtn);
        postDetalhe.appendChild(actionsDiv);
    }
}

function iniciarEdicao(post) {
    document.getElementById('edit_titulo').value = post.titulo;
    document.getElementById('edit_corpo').value = post.corpo;
    postDetalhe.style.display = 'none'; 
    edicaoForm.style.display = 'block'; 
}

// ---------------- Funções de Ação (CRUD) -----------------

async function carregarPost() {
    currentPostId = getPostIdFromUrl();
    if (!currentPostId) {
        postDetalhe.innerHTML = '<p class="erro">ID da postagem não fornecido.</p>';
        return;
    }

    try {
        const post = await buscarPostPorID(currentPostId);
        exibirPost(post);
        document.getElementById('postTitle').textContent = post.titulo;

    } catch (error) {
        postDetalhe.innerHTML = `<p class="erro">${error.message}</p>`;
    }
}

async function confirmarDelecao(postId, userId) {
    if (confirm('Tem certeza que deseja deletar esta postagem?')) {
        try {
            await deletarPost(postId, userId);
            exibirStatus('Postagem deletada com sucesso! Redirecionando...', 'sucesso');
            setTimeout(() => { window.location.href = 'feed.html'; }, 1500);

        } catch (error) {
            exibirStatus(error.message, 'erro');
        }
    }
}

// ---------------- Event Listeners -----------------

edicaoForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const userId = localStorage.getItem('user_id');
    
    const dadosEdicao = {
        titulo: document.getElementById('edit_titulo').value,
        corpo: document.getElementById('edit_corpo').value,
        user_id: userId 
    };

    try {
        await atualizarPost(currentPostId, dadosEdicao);
        exibirStatus('Postagem atualizada com sucesso!', 'sucesso');
        
        // Recarrega o post para ver as mudanças (melhor experiência do usuário)
        setTimeout(() => carregarPost(), 500); 

    } catch (error) {
        exibirStatus(error.message, 'erro');
    }
});

document.getElementById('cancelarEdicaoBtn').addEventListener('click', () => {
    edicaoForm.style.display = 'none';
    postDetalhe.style.display = 'block';
    mensagemStatus.textContent = '';
});

// Inicia o carregamento da página
carregarPost();
