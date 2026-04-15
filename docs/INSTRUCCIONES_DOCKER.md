# Instrucciones Docker

## Levantar el entorno

```bash
docker compose up -d --build
```

La aplicación queda disponible en:

```text
http://localhost:8000
```

La base MySQL queda expuesta en:

```text
localhost:3307
```

## Comandos útiles iniciales

### Generar clave de aplicación

```bash
docker compose exec app php artisan key:generate
```

### Ejecutar migraciones

```bash
docker compose exec app php artisan migrate
```

### Generar claves de Passport

```bash
docker compose exec app php artisan passport:keys
```

## Qué queda resuelto automáticamente

Al arrancar el contenedor `app`, el entrypoint:

- crea los directorios necesarios dentro de `storage/framework`
- asegura permisos de escritura sobre `storage` y `bootstrap/cache`
- corrige permisos de `storage/oauth-private.key` y `storage/oauth-public.key` si existen

## Qué sigue siendo manual y deliberado

Para mantener una configuración simple y defendible, estos pasos no se automatizan en cada arranque:

- creación de usuario de prueba
- creación de personal access client

Si el entorno es nuevo, esos pasos deben ejecutarse de forma explícita según la necesidad del challenge.
