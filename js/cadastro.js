// frontend/js/cadastro.js

import { cadastrarUsuario } from './api.js';

const cadastroForm = document.getElementById('cadastroForm');
const mensagemStatus = document.getElementById('mensagemStatus');

function limparStatus() {
    mensagemStatus.textContent = '';
    mensagemStatus.className = '';
}

function exibirStatus(mensagem, tipo = 'sucesso') {
    mensagemStatus.textContent = mensagem;
    mensagemStatus.className = tipo;
    mensagemStatus.style.display = 'block';
}

cadastroForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    limparStatus();

    const userData = {
        nome: document.getElementById('nome').value,
        sobrenome: document.getElementById('sobrenome').value,
        email: document.getElementById('email').value,
        senha: document.getElementById('senha').value,
        biografia: document.getElementById('biografia').value,
        avatar_url: document.getElementById('avatar_url').value,
    };

    if (!userData.nome || !userData.email || !userData.senha) {
        exibirStatus('Preencha pelo menos nome, e-mail e senha.', 'erro');
        return;
    }

    try {
        await cadastrarUsuario(userData);
        
        exibirStatus('🎉 Cadastro realizado com sucesso! Redirecionando para o login...', 'sucesso');
        cadastroForm.reset(); 
        
        setTimeout(() => {
            window.location.href = 'index.html'; 
        }, 2000);

    } catch (error) {
        exibirStatus(error.message || 'Erro desconhecido ao cadastrar.', 'erro');
    }
});
