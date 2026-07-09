Flor de Cerezo — Landing + Admin

Esta versión incluye:
- Landing pública con saludo por nombre y acceso de login.
- Login para usuarios y administradores.
- Dashboard para administrar usuarios y ver ventas diarias.
- Base de datos MySQL con tablas para usuarios y ventas.

Requisitos
- PHP 8+
- MySQL
- Railway account para despliegue

Instalación local
1) Crea una base de datos MySQL llamada `landing_db`.
2) Ejecuta el script en [database/schema.sql](database/schema.sql).
3) Define estas variables de entorno:
   - DB_HOST
   - DB_PORT
   - DB_NAME
   - DB_USERNAME
   - DB_PASSWORD
4) Sirve el proyecto con PHP:

```bash
php -S 0.0.0.0:8000 -t .
```

Luego abre http://localhost:8000/index.php

Credenciales iniciales
- Correo: admin@tuempresa.com
- Contraseña: Admin123!

Despliegue en Railway
1) Sube este proyecto a GitHub.
2) Crea un nuevo servicio en Railway usando el repositorio.
3) Añade las variables de entorno de MySQL.
4) Usa el comando de inicio recomendado por Railway o el archivo Procfile incluido.

Notas
- El login redirige a dashboard si el usuario es admin, o a la landing si es user.
- El dashboard permite crear y editar usuarios.
- Las ventas diarias se consultan desde la tabla `sales`.
