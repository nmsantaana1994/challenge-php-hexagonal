# Challenge PHP Hexagonal

API REST desarrollada con Laravel 12, arquitectura Hexagonal + DDD, autenticación OAuth 2.0 con Laravel Passport, integración con Giphy, persistencia de favoritos y auditoría automática de interacciones HTTP.

## Resumen

Este proyecto resuelve un challenge técnico cuyo objetivo es construir una API mantenible y desacoplada.

El alcance implementado hoy incluye:

- login con emisión de token
- búsqueda de GIFs por texto
- consulta de GIF por ID
- guardado de GIF favorito
- auditoría automática de requests y responses API
- redacción de datos sensibles en auditoría
- tests feature y unit
- colección Postman lista para importar
- Docker para app + MySQL
- diagramas técnicos mínimos del proyecto

## Stack

- PHP 8.4.19
- Laravel 12
- Laravel Passport 13.x
- MySQL o MariaDB
- SQLite en memoria para tests
- PHPUnit 11
- Docker / Docker Compose

## Arquitectura implementada

El proyecto está organizado con una separación hexagonal pragmática:

```text
app/
  Domain/
  Application/
    Contracts/
    DTOs/
    UseCases/
  Infrastructure/
    Auth/
    External/
      Giphy/
    Http/
      Controllers/
      Middleware/
      Requests/
    Persistence/
      Eloquent/
        Models/
        Repositories/
    Providers/
routes/
tests/
```

### Rol de cada capa

- `Domain`: reservado para conceptos y reglas de negocio puras.
- `Application`: contiene contratos, DTOs y casos de uso.
- `Infrastructure`: implementa adaptadores concretos para HTTP, Eloquent, Passport, Giphy y auditoría.

### Idea central del diseño

Los casos de uso no dependen directamente de Eloquent, Giphy ni Passport. En su lugar, dependen de puertos definidos en `Application/Contracts`, que luego se resuelven con adaptadores concretos desde `Infrastructure`.

## Funcionalidades implementadas

### Endpoints reales

- `POST /api/auth/login`
- `GET /api/gifs/search`
- `GET /api/gifs/{id}`
- `POST /api/favorites`

### Persistencia implementada

- `favorite_gifs`
- `api_logs`
- tablas OAuth de Passport

### Seguridad y trazabilidad

- autenticación con Passport
- protección de endpoints privados con `auth:api`
- auditoría automática en todas las rutas `/api/*`
- sanitización de claves sensibles antes de persistir auditoría

## Requisitos

Para correr el proyecto fuera de Docker:

- PHP 8.4 o superior
- Composer
- MySQL o MariaDB
- extensiones PHP necesarias para Laravel 12
- claves de Passport
- API key de Giphy

Para correrlo con Docker:

- Docker
- Docker Compose

## Instalación local

### 1. Instalar dependencias

```bash
composer install
```

### 2. Crear archivo de entorno

En Windows:

```bash
copy .env.example .env
```

En Linux/macOS:

```bash
cp .env.example .env
```

### 3. Generar la clave de aplicación

```bash
php artisan key:generate
```

### 4. Configurar `.env`

Ejemplo mínimo para entorno local:

```env
APP_NAME="Challenge PHP Hexagonal"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=challenge_php_hexagonal
DB_USERNAME=root
DB_PASSWORD=

GIPHY_API_KEY=your_giphy_api_key
GIPHY_BASE_URL=https://api.giphy.com/
GIPHY_TIMEOUT=10
```

## Variables de entorno relevantes

- `APP_URL`
- `APP_ENV`
- `APP_DEBUG`
- `DB_CONNECTION`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `GIPHY_API_KEY`
- `GIPHY_BASE_URL`
- `GIPHY_TIMEOUT`
- `PASSPORT_PRIVATE_KEY`
- `PASSPORT_PUBLIC_KEY`

## Base de datos y migraciones

Ejecutar migraciones:

```bash
php artisan migrate
```

Esto crea, entre otras, las tablas:

- `users`
- `favorite_gifs`
- `api_logs`
- tablas OAuth de Passport

## Passport

El proyecto utiliza Laravel Passport como implementación de OAuth 2.0.

### Qué está resuelto

- emisión de token desde `PassportTokenIssuer`
- guard `api` configurado con driver `passport`
- endpoints privados protegidos con `auth:api`

### Pasos manuales reales en un entorno nuevo

#### 1. Generar claves

```bash
php artisan passport:keys
```

#### 2. Crear personal access client si el entorno lo necesita

En un entorno nuevo puede ser necesario crear explícitamente el personal access client de Passport. Ese paso no se automatiza en cada arranque para mantener la solución simple y controlada.

#### 3. Crear usuario de prueba si querés probar login rápidamente

Tampoco se automatiza por defecto, para evitar decisiones de negocio o datos hardcodeados en cada arranque.

### Nota técnica

La capa de aplicación no depende directamente de Passport. La emisión de token está encapsulada en infraestructura, respetando la separación hexagonal.

## Giphy API Key

La integración con Giphy requiere una API key válida:

```env
GIPHY_API_KEY=your_giphy_api_key
```

Sin esa clave:

- `GET /api/gifs/search` responderá error de integración
- `GET /api/gifs/{id}` responderá error de integración

## Cómo correr la aplicación en local

```bash
php artisan serve
```

La API quedará disponible en:

```text
http://127.0.0.1:8000/api
```

## Cómo correr con Docker

### Levantar entorno

```bash
docker compose up -d --build
```

Servicios expuestos:

- App: `http://localhost:8000`
- MySQL: `localhost:3307`

### Comandos útiles iniciales

Generar clave de aplicación:

```bash
docker compose exec app php artisan key:generate
```

Ejecutar migraciones:

```bash
docker compose exec app php artisan migrate
```

Generar claves de Passport:

```bash
docker compose exec app php artisan passport:keys
```

### Qué queda resuelto automáticamente en Docker

El entrypoint del contenedor `app`:

- crea directorios de `storage/framework`
- asegura permisos de escritura sobre `storage` y `bootstrap/cache`
- corrige permisos de `storage/oauth-private.key` y `storage/oauth-public.key` si existen

### Qué sigue siendo manual y por qué

Para mantener una solución simple y defendible, no se automatiza en cada arranque:

- creación de usuario de prueba
- creación de personal access client

Esto evita meter datos o decisiones de negocio en el bootstrap del contenedor.

## Cómo correr los tests

La suite está preparada para correr de forma reproducible usando SQLite en memoria.

### Ejecutar toda la suite

```bash
php artisan test
```

### Ejecutar feature tests

```bash
php artisan test --testsuite=Feature
```

### Ejecutar unit tests

```bash
php artisan test --testsuite=Unit
```

## Cobertura actual de tests

### Feature tests

Actualmente cubren:

- login vacío -> `422`
- login inválido -> `401`
- login válido -> `200`
- búsqueda sin token -> `401`
- búsqueda con token -> `200`
- búsqueda sin `query` -> `422`
- GIF inexistente -> `404`
- guardar favorito sin token -> `401`
- guardar favorito válido -> `201`

Además verifican que la auditoría:

- no rompa el flujo
- inserte registros
- redacte datos sensibles en casos relevantes

### Unit tests

Actualmente cubren lógica aislada de casos de uso:

- `LoginUserUseCase`
- `SaveFavoriteGifUseCase`
- `LogApiInteractionUseCase`

## Endpoints disponibles

### Público

#### `POST /api/auth/login`

Body:

```json
{
  "email": "user@example.com",
  "password": "password"
}
```

Respuesta exitosa:

```json
{
  "data": {
    "access_token": "token",
    "token_type": "Bearer",
    "expires_in": 1800
  }
}
```

### Protegidos con Passport

Usar header:

```http
Authorization: Bearer <token>
```

#### `GET /api/gifs/search`

Query params:

- `query` requerido
- `limit` opcional
- `offset` opcional

#### `GET /api/gifs/{id}`

Devuelve:

- `200` si el GIF existe
- `404` si no existe o si Giphy responde un caso equivalente a GIF inválido/no encontrado

#### `POST /api/favorites`

Body:

```json
{
  "user_id": 1,
  "gif_id": "abc123",
  "alias": "Mi GIF favorito"
}
```

Respuesta esperada:

- `201 Created`

## Auditoría

Cada interacción relevante de `/api/*` se registra en `api_logs` con:

- `user_id`
- `service_name`
- `request_body`
- `response_code`
- `response_body`
- `ip_address`

La auditoría se implementa con middleware reutilizable en infraestructura y no altera la respuesta al cliente si el guardado falla.

### Redacción de datos sensibles

Antes de persistir auditoría, se reemplazan valores sensibles por:

```text
[REDACTED]
```

Claves sanitizadas actualmente:

- `password`
- `access_token`
- `token`
- `refresh_token`
- `authorization`
- `client_secret`

## Colección Postman

El repositorio incluye la colección:

- [postman/Challenge_PHP_Hexagonal.postman_collection.json](postman/Challenge_PHP_Hexagonal.postman_collection.json)

Incluye requests para:

- `POST /api/auth/login`
- `GET /api/gifs/search`
- `GET /api/gifs/{id}`
- `POST /api/favorites`

La request de login guarda automáticamente el token en el environment de Postman mediante script.

Variables contempladas:

- `base_url`
- `access_token`
- `gif_id`
- `email`
- `password`
- `query`
- `limit`
- `offset`
- `user_id`
- `favorite_alias`

## Diagramas incluidos

Los diagramas versionables del proyecto están en:

- [docs/diagrams/README.md](docs/diagrams/README.md)
- [docs/diagrams/architecture.mmd](docs/diagrams/architecture.mmd)
- [docs/diagrams/sequence-login.mmd](docs/diagrams/sequence-login.mmd)
- [docs/diagrams/der.mmd](docs/diagrams/der.mmd)

Incluyen:

- diagrama de arquitectura/componentes
- diagrama de secuencia del login
- DER básico del modelo de datos actual

## Decisiones técnicas importantes

### Laravel 12 + PHP 8.4

Laravel 12 soporta oficialmente PHP 8.2 a 8.5, por lo que PHP 8.4 es una decisión válida y actual para el challenge.

### Passport para OAuth 2.0

Se eligió Passport porque:

- la consigna pide explícitamente OAuth 2.0
- es la implementación oficial del ecosistema Laravel
- evita reimplementar una capa sensible de seguridad
- permite concentrar el challenge en arquitectura, integración y trazabilidad

### Arquitectura hexagonal pragmática

Se aplicó para que:

- los casos de uso dependan de contratos y DTOs
- infraestructura implemente adaptadores concretos
- Eloquent y HTTP no contaminen la capa de aplicación

### Auditoría en middleware

Se resolvió como middleware para:

- evitar duplicación en controllers
- mantener controllers delgados
- centralizar el registro de request/response

## Limitaciones actuales

Estado real del proyecto al momento de esta entrega:

- el dominio está modelado de forma liviana, priorizando separación de capas sobre complejidad de DDD
- la integración real con Giphy depende de una API key válida
- el setup inicial de Passport puede requerir pasos manuales en entornos nuevos
- la colección Postman no incluye un environment exportado, solo la colección

## Revisión rápida del entregable

Actualmente el proyecto incluye:

- código funcional de la API
- tests feature y unit
- colección Postman
- Dockerfile y `docker-compose.yml`
- diagramas Mermaid
- documentación principal en este README
