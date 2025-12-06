// frontend/js/criar_post.js

import { criarPostagem } from './api.js';

const postForm = document.getElementById('postForm');
const mensagemStatus = document.getElementById('mensagemStatus');

function exibirStatus(mensagem, tipo = 'sucesso') {
    mensagemStatus.textContent = mensagem;
    mensagemStatus.className = tipo;
    mensagemStatus.style.display = 'block';
}

postForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    mensagemStatus.textContent = '';
    
    const userId = localStorage.getItem('user_id');
    if (!userId) {
        exibirStatus('Você precisa estar logado para criar uma postagem.', 'erro');
        setTimeout(() => { window.location.href = 'index.html'; }, 1500); 
        return;
    }

    const postData = {
        titulo: document.getElementById('titulo').value,
        corpo: document.getElementById('corpo').value,
        user_id: userId 
    };

    try {
        await criarPostagem(postData);
        
        exibirStatus('✅ Postagem publicada com sucesso! Redirecionando...', 'sucesso');
        postForm.reset(); 
        
        setTimeout(() => {
            window.location.href = 'feed.html'; 
        }, 2000);

    } catch (error) {
        exibirStatus(error.message || 'Erro desconhecido ao publicar.', 'erro');
    }
});
