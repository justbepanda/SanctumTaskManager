FROM php:8.4-cli

ARG UID=1000
ARG GID=1000

# Создаём системного пользователя приложения
RUN groupadd -g ${GID} app \
    && useradd -m -u ${UID} -g ${GID} app

# Системные зависимости
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Redis расширение
RUN pecl install redis \
    && docker-php-ext-enable redis

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Рабочая директория
WORKDIR /var/www

# Цветной bash prompt для пользователя
RUN echo "export PS1='\[\e[36m\]\u@\h\[\e[0m\]:\[\e[32m\]\w\[\e[0m\]\$ '" >> /home/app/.bashrc

USER app

# Открываем порт
EXPOSE 8000

# Запуск встроенного сервера Laravel
CMD php artisan serve --host=0.0.0.0 --port=8000
