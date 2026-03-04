Sanctum Sandbox
===============

Учебный проект на **Laravel 12** для полного погружения в работу с Laravel Sanctum.

Проект реализует все основные сценарии аутентификации и авторизации, используемые в реальных production-приложениях.

Что реализовано
------------------

### API токены

*   Регистрация и логин

*   Выдача Personal Access Token

*   Logout текущего устройства

*   Logout со всех устройств

*   Просмотр и удаление конкретных токенов


### Granular permissions

*   Ability-based доступ:

    *   task:read

    *   task:create

    *   task:update

    *   task:delete

*   Проверка abilities через middleware

*   Ручная проверка tokenCan()


### SPA (cookie-based) авторизация

*   Stateful domains

*   CSRF cookie

*   Session-based login

*   Доступ к API без Bearer токена


### Multi-device логика

*   Токен на устройство

*   Хранение device\_name, last\_used\_at, user\_agent

*   Список активных устройств

*   Logout all except current


### Policies + Sanctum

*   Разделение аутентификации и авторизации

*   Пользователь может управлять только своими задачами

*   Комбинация ability + policy


### Expiring токены

*   Токены с TTL (например 7 дней)

*   Автоматическая очистка просроченных

*   Проверка expiration


### Тестирование

Покрыты сценарии:

*   Неавторизованный доступ

*   Токен без нужной ability

*   Просроченный токен

*   Удалённый токен


### Production-паттерны

*   Mobile токены с ограниченными правами

*   Internal service tokens

*   Service-to-service auth

*   Rate limiting (Sanctum + throttle)


Архитектура
--------------

Task Manager API:

*   User

*   Project

*   Task


Стек
-------

*   Laravel 12

*   Laravel Sanctum

*   PostgreSQL

*   PHPUnit


Установка
-----------
```Bash
make init
```


Команда выполняет:

*   установку зависимостей

*   настройку окружения

*   запуск миграций

*   подготовку проекта к работе
