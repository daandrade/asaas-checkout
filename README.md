# Asaas Checkout – Backend

Este é o backend do sistema de pagamentos integrado com a API do [Asaas](https://asaas.com/), desenvolvido em **Laravel**. A API gerencia clientes e pagamentos via boleto, Pix e cartão, funcionando com autenticação básica e endpoints RESTful.

---

## 🚀 Tecnologias Utilizadas

- [Laravel](https://laravel.com/)
- PHP 8.x
- [Asaas API](https://docs.asaas.com/)
- MySQL ou SQLite
- Laravel Sanctum (opcional)
- Laravel HTTP Client (para integração externa)

---

## ⚙️ Instalação e Execução

### 1. Clonar o repositório

```bash
git clone https://github.com/daandrade/asaas-backend.git
cd asaas-backend

2. Instalar dependências
composer install

3. Criar e configurar o arquivo .env
cp .env.example .env

4. Rodar as migrations
php artisan migrate

5. Iniciar o servidor local
php artisan serve


