# Visão Geral

## Contexto
O projeto **BNG Angola** é uma aplicação web para gestão e operação de serviços relacionados com farmácias, com áreas separadas para:
- Administração (Admin)
- Farmácia (normal / matriz / filial)
- Cliente

## Perfis (alto nível)
- **Admin**: gere utilizadores, farmácias, filiais, documentos (ex.: alvará) e parâmetros administrativos.
- **Farmácia**:
  - **Normal**: opera como unidade única.
  - **Matriz**: pode criar e gerir **filiais**.
  - **Filial**: subordinada a uma Matriz.
- **Cliente**: navega, regista-se e realiza interações com o sistema.

## Tecnologias
- Backend: **Laravel 8** (PHP)
- Frontend: Blade + Bootstrap (existente no projeto)
- Base de dados: MySQL/MariaDB (XAMPP)

## Estrutura de pastas (essencial)
- `app/` (models, controllers, services)
- `routes/` (rotas web)
- `resources/views/` (Blade)
- `storage/app/` (ficheiros guardados pelo sistema)

## Links úteis (locais)
- Aplicação: `/public/`
- Admin (exemplo): `/public/admin/farmacias`
