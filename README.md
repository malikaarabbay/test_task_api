# Task API на Symfony

Простой REST API для управления задачами. Используются Symfony, Doctrine ORM и PostgreSQL.

## 📦 Установка

```bash
composer install
cp .env .env.local
# Сгенерируйте SSL-ключи для JWT
mkdir -p config/jwt
openssl genrsa -out config/jwt/private.pem -aes256 4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
# Укажите путь к ключам и пароль в .env.local
JWT_PASSPHRASE=ваш_пароль_к_ключу
# Установите параметры подключения к БД в .env.local
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
symfony server:start
```

## 🔧 API Маршруты

| Метод | URI              | Описание                    |
|-------|------------------|-----------------------------|
| POST  | /api/register    | Регистрация нового пользователя |
| POST  | /api/login_check | Получение JWT токена (авторизация) |
| GET   | /api/tasks       | Получить все задачи         |
| POST  | /api/tasks       | Создать новую задачу        |
| GET   | /api/tasks/{id}  | Получить задачу по ID       |
| PUT   | /api/tasks/{id}  | Обновить задачу по ID       |
| DELETE| /api/tasks/{id}  | Удалить задачу по ID        |

## 🧪 Примеры (Postman)

### POST /api/tasks
```json
{
  "title": "Изучить Symfony",
  "description": "Пройти туториал",
  "status": "new"
}
```

### Ответ
```json
{
  "id": 1,
  "title": "Изучить Symfony",
  "description": "Пройти туториал",
  "status": "new",
  "created_at": "2025-05-20T10:00:00+00:00",
  "updated_at": "2025-05-20T10:00:00+00:00"
}
```

## 🛡️ Бонусные задачи
- Пагинация: `GET /api/tasks?page=1&limit=10`
- Фильтрация: `GET /api/tasks?status=done`
- Аутентификация: JWT с LexikJWTAuthenticationBundle

---

## 📦 Postman Collection

Вы можете загрузить коллекцию из репозитория:

[📥 Скачать Postman Collection](./TaskApi.postman_collection.json)
