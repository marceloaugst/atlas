# 📚 Atlas

E-commerce de livros desenvolvido para estudo e portfólio, simulando uma loja virtual completa.

O projeto utiliza a **Google Books API** como fonte externa para consulta e importação de informações bibliográficas, enquanto toda a lógica de negócio do e-commerce é administrada pela aplicação.

O objetivo é praticar conceitos de desenvolvimento web moderno, incluindo:

- Laravel
- React
- Inertia.js
- MySQL
- APIs REST
- Autenticação
- Carrinho de compras
- Pedidos
- Estoque
- Cupons
- Avaliações
- Favoritos
- Pagamentos simulados
- Integração com API externa
- Jobs e filas
- Cache
- Testes automatizados
- Arquitetura de software

---

# 🚀 Tecnologias

## Backend

- PHP 8.3+
- Laravel 13+
- MySQL 8+
- Laravel Sanctum
- Laravel Queue
- Laravel Scheduler
- Laravel Cache

## Frontend

- React
- Inertia.js
- TypeScript
- Tailwind CSS
- Vite

## Integrações

- Google Books API

## Desenvolvimento

- Git
- GitHub
- Docker (opcional)
- PHPUnit / Pest

---

# ⚠️ PHP local: dois binários lado a lado

Esta máquina tem dois PHPs instalados e o `php` do PATH continua apontando para o **8.2.26** (usado por outros projetos). O Atlas exige **PHP 8.3+** (Laravel 13), então use o binário 8.5 explicitamente ao rodar Composer/Artisan aqui:

```bash
/c/php-8.5.6-Win32-vs17-x64/php.exe artisan migrate
/c/php-8.5.6-Win32-vs17-x64/php.exe "C:/ProgramData/ComposerSetup/bin/composer.phar" install
```

O PATH do sistema não foi alterado (decisão deliberada, para não afetar os outros projetos que ainda usam PHP 8.2).

---

# ⚙️ Redis e fila em desenvolvimento

Cache, sessão e fila usam Redis (via `predis/predis`, já que este PHP não tem a extensão `phpredis`). O serviço "Redis" do Windows precisa estar rodando. Sem um worker ativo, jobs (como o e-mail de confirmação de pedido) ficam apenas enfileirados:

```bash
/c/php-8.5.6-Win32-vs17-x64/php.exe artisan queue:work
```

E para rodar as tarefas agendadas (limpar carrinhos abandonados, desativar cupons expirados, atualizar estatísticas do dashboard) manualmente em vez de esperar o cron real:

```bash
/c/php-8.5.6-Win32-vs17-x64/php.exe artisan schedule:work
```

---

# 🎯 Objetivo do projeto

O Atlas tem como objetivo simular uma aplicação real de e-commerce.

O usuário poderá:

1. Criar uma conta
2. Navegar pelos livros
3. Pesquisar livros
4. Filtrar por categoria
5. Visualizar detalhes de um livro
6. Adicionar produtos ao carrinho
7. Alterar quantidades
8. Favoritar livros
9. Informar endereço
10. Aplicar cupons
11. Finalizar uma compra
12. Simular pagamento
13. Acompanhar pedidos
14. Avaliar produtos

Administradores poderão:

- cadastrar produtos
- importar livros da Google Books API
- alterar preços
- controlar estoque
- gerenciar categorias
- gerenciar pedidos
- criar cupons
- visualizar clientes
- acompanhar vendas
- visualizar métricas

---

# 📋 Roadmap de implementação

1. [x] Laravel + MySQL
2. [x] Produtos / Categorias
3. [x] React / Inertia
4. [x] Carrinho
5. [x] Checkout
6. [x] Pedidos
7. [x] Admin
8. [x] Google Books
9. [x] Redis / Jobs / Cache
10. [ ] RAG

---

# 📌 Regras importantes do projeto

Este projeto possui finalidade educacional e de portfólio.

O pagamento será exclusivamente simulado.

Informações obtidas de APIs externas deverão respeitar os respectivos termos de uso.

Conteúdo protegido por direitos autorais não deverá ser armazenado ou distribuído sem autorização.

---

# 👨‍💻 Autor

Projeto desenvolvido para fins de estudo, aprendizado e demonstração de conhecimentos em desenvolvimento de software.

---

# 📄 Licença

Este projeto poderá ser distribuído sob a licença MIT.
