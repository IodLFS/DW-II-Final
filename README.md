# Projeto Sueca — Repositório Unificado

Este repositório contém dois subprojetos claramente separados:

- `portal/` — aplicação web (PHP) do portal.
- `sueca-api/` — API (Laravel) separada.

## Estrutura

- portal/
  - config/
  - controllers/
  - core/
  - models/
  - public/
  - views/

- sueca-api/
  - app/
  - routes/
  - database/
  - public/
  - ...

## Como configurar (resumo)

- API (Laravel):
  - cd sueca-api
  - composer install
  - cp .env.example .env && php artisan key:generate
  - php artisan migrate
  - php artisan db:seed --class=TestUserSeeder
  - php artisan serve

- Portal: colocar o `portal/public` como document root do servidor web.

---

Tag final de entrega: `v-final` (criada neste repositório).
# DW-II-Final
