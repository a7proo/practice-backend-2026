# Преддипломная практика — Бэкенд-разработка

# Booking API

Система бронирования ресурсов (переговорки, рабочие места).

## Стек
- PHP 8.2+ / Laravel 10+
- MySQL 8.0+
- JWT-аутентификация (Laravel Sanctum)
- Docker / Docker Compose

# Чекпоинты
- 1_Step    Проектирование и старт
- 2_step	Авторизация и базовый CRUD
- 3_step	Основная бизнес-логика
- 4_step	Продвинутый функционал
- 5_step    Мелкие исправления файла readme добавление postman collection

# Структура репозитория
├── README.md
├── docs/
│   └── ER-diagram.png
├── ultra-project-mega-back-end/
│   └── # весь проект
└── postman collection
    └── My Collection.postman_collection #колекции из postman 

# Локальная установка

- 1 git clone <твой-репозиторий>
    cd booking-api

- 2 composer install

- 3 cp .env.example .env
    php artisan key:generate

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=booking_db
    DB_USERNAME=root
    DB_PASSWORD=

- 4 mysql -u root -e "CREATE DATABASE booking_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

- 5 php artisan migrate --seed

- 6 php artisan serve