# Deploy

## Objetivo
Este documento descreve como colocar o sistema em produção de forma previsível.

## Checklist
- Configurar `.env` com valores de produção
- `APP_DEBUG=false`
- Configurar cache:
  - `php artisan config:cache`
  - `php artisan route:cache`
  - `php artisan view:cache`
- Garantir permissões:
  - `storage/`
  - `bootstrap/cache/`

## Uploads e documentos
- Confirmar que a pasta de `storage` em produção suporta escrita.
- Definir estratégia para backups dos documentos (alvarás e anexos).
