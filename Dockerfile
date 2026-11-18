FROM php:8.3-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
    libcurl4-openssl-dev libonig-dev libxml2-dev \
    && docker-php-ext-install curl dom mbstring \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY . .
EXPOSE 8080
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t public"]
