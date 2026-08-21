# Instalação (Ambiente Local)

## Pré-requisitos
- XAMPP (Apache + MySQL/MariaDB)
- PHP compatível com o projeto (ex.: 8.x conforme o teu ambiente)
- Composer
- Node/NPM (se necessário para assets)

## Passos
1. Copiar `.env.example` para `.env` e configurar:
   - `APP_URL`
   - credenciais de BD (`DB_*`)
2. Instalar dependências PHP:
   - `composer install`
3. Gerar chave:
   - `php artisan key:generate`
4. Migrar BD:
   - `php artisan migrate`
5. Storage (se for necessário expor ficheiros):
   - `php artisan storage:link`

## Notas
- Uploads no disk `local` ficam em `storage/app/`.
- Se houver erro de permissões, garantir que o Apache tem acesso de escrita em `storage/` e `bootstrap/cache/`.
