// frontend/js/main.js

import { fazerLogin } from './api.js';

const loginForm = document.getElementById('loginForm');
const mensagemErro = document.getElementById('mensagemErro');

function limparErro() {
    mensagemErro.textContent = '';
    mensagemErro.style.display = 'none';
}

function exibirErro(mensagem) {
    mensagemErro.textContent = mensagem;
    mensagemErro.style.display = 'block';
    mensagemErro.className = 'erro';
}

loginForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    limparErro();

    const email = document.getElementById('email').value;
    const senha = document.getElementById('senha').value;

    if (!email || !senha) {
        exibirErro('Por favor, preencha todos os campos.');
        return;
    }

    try {
        const resultado = await fazerLogin(email, senha);
        
        // O resultado deve conter o 'id' do usuário logado
        localStorage.setItem('user_id', resultado.id); 
        
        // Redireciona para o feed
        window.location.href = 'feed.html'; 

    } catch (error) {
        exibirErro(error.message || 'Erro de conexão ou credenciais inválidas.');
    }
});
