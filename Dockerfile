FROM php:8.2-cli

# Instalar extensiones necesarias
RUN docker-php-ext-install pdo pdo_mysql

# Establecer directorio de trabajo
WORKDIR /app

# Copiar archivos
COPY . .

# Exponer puerto
EXPOSE 8080

# Comando para iniciar el servidor PHP nativo
CMD ["php", "-S", "0.0.0.0:8080"]
