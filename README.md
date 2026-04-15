# Challenge PHP Hexagonal

API REST construida con Laravel 12, arquitectura Hexagonal + DDD, autenticación OAuth 2.0 mediante Laravel Passport, integración con Giphy, persistencia de favoritos y auditoría automática de interacciones HTTP.

## Resumen

Este proyecto resuelve un challenge técnico orientado a diseño de arquitectura, integración externa, autenticación y trazabilidad.

El foco principal fue:

- separar lógica de negocio de detalles de infraestructura
- evitar acoplamiento directo entre casos de uso, Eloquent y HTTP
- implementar una API defendible técnicamente para revisión

Estado actual implementado:

- login con emisión de token
- búsqueda de GIFs por texto
- consulta de GIF por ID
- guardado de GIF favorito
- auditoría automática de requests y responses API
- sanitización de datos sensibles en auditoría
- tests feature y unit tests mínimos
- colección Postman lista para entrega

## Stack

- PHP 8.4.19
- Laravel 12
- Laravel Passport 13.x
- MySQL o MariaDB
- SQLite en memoria para tests
- PHPUnit 11

## Arquitectura

El proyecto sigue una estructura Hexagonal + DDD adaptada a Laravel:

```text
app/
  Domain/
  Application/
    Contracts/
    DTOs/
    UseCases/
  Infrastructure/
    Http/
      Controllers/
      Requests/
      Middleware/
    Persistence/
      Eloquent/
        Models/
        Repositories/
    External/
      Giphy/
    Providers/
routes/
tests/
```

### Responsabilidades por capa

- `Domain`: reservado para reglas puras de dominio y conceptos del negocio.
- `Application`: contiene puertos, DTOs y casos de uso.
- `Infrastructure`: implementa adaptadores concretos como HTTP, Eloquent, Passport, Giphy y auditoría.

## Alcance implementado

### Endpoints funcionales

- `POST /api/auth/login`
- `GET /api/gifs/search`
- `GET /api/gifs/{id}`
- `POST /api/favorites`

### Persistencia

- tabla `favorite_gifs`
- tabla `api_logs`

### Seguridad y trazabilidad

- autenticación con Passport
- protección de endpoints privados mediante `auth:api`
- auditoría automática vía middleware en todas las rutas `/api/*`
- redacción de datos sensibles antes de persistir auditoría

## Requisitos

Para levantar el proyecto localmente se necesita:

- PHP 8.4 o superior
- Composer
- MySQL o MariaDB
- extensiones PHP compatibles con Laravel 12
- claves de Passport
- API key de Giphy

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

### 3. Generar la clave de la aplicación

```bash
php artisan key:generate
```

### 4. Configurar `.env`

Ejemplo mínimo:

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

## Migraciones y base de datos

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

### Generar claves

Si el entorno aún no tiene claves:

```bash
php artisan passport:keys
```

### Nota técnica

La capa de aplicación no depende de Passport directamente. La emisión de tokens se resuelve desde infraestructura a través del adaptador `PassportTokenIssuer`, respetando la separación hexagonal.

## Giphy API Key

La integración con Giphy requiere una API key válida:

```env
GIPHY_API_KEY=your_giphy_api_key
```

Sin esa clave:

- `GET /api/gifs/search` responderá error de integración
- `GET /api/gifs/{id}` responderá error de integración

## Cómo correr la aplicación

Levantar servidor local:

```bash
php artisan serve
```

La API quedará disponible en:

```text
http://127.0.0.1:8000/api
```

## Cómo correr los tests

Los tests están configurados para correr de forma reproducible usando SQLite en memoria.

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

Además validan que la auditoría:

- no rompa el flujo
- inserte registros
- redacte datos sensibles en casos críticos

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

La auditoría se implementa con middleware reutilizable en infraestructura y no altera la respuesta de la API si el guardado falla.

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

El repositorio incluye una colección lista para importar:

- [postman/Challenge_PHP_Hexagonal.postman_collection.json](/C:/laragon/www/challenge-php-hexagonal/postman/Challenge_PHP_Hexagonal.postman_collection.json:1)

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

## Decisiones técnicas

### Laravel 12 + PHP 8.4

Laravel 12 soporta oficialmente PHP 8.2 a 8.5, por lo que PHP 8.4 es una decisión válida y actual para el challenge.

### Passport para OAuth 2.0

Se eligió Passport porque:

- la consigna pide explícitamente OAuth 2.0
- es la implementación oficial del ecosistema Laravel
- evita reimplementar una capa sensible de seguridad
- permite concentrar el challenge en diseño, integración y trazabilidad

### Arquitectura hexagonal

Se aplicó para que:

- los casos de uso dependan de contratos y DTOs
- la infraestructura implemente adaptadores concretos
- Eloquent y HTTP no contaminen la capa de aplicación

### Auditoría en middleware

Se resolvió como middleware para:

- evitar duplicación en controllers
- mantener controllers delgados
- centralizar el registro de request/response

## Limitaciones actuales

Estado real del proyecto al momento de esta entrega:

- no incluye todavía Docker o Docker Compose
- no incluye diagramas ni DER dentro del repo
- no incluye colección Postman environment exportada, solo la colección
- la integración real con Giphy depende de una API key válida
- la suite actual prioriza cobertura útil mínima antes que cobertura exhaustiva

## Próximos pasos posibles

Si el challenge continuara, los siguientes pasos razonables serían:

- agregar Dockerfile y `docker-compose`
- incorporar diagramas de arquitectura y secuencia
- sumar más unit tests sobre adaptadores concretos
- agregar environment de Postman exportable
- reforzar documentación operativa de despliegue
