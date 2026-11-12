## Лабораторна робота №5: JWT і ролі

### Laravel
```powershell
cd Laravel
composer install
php artisan migrate
php artisan app:create-user admin@test.com Admin12345 admin --name=Admin
```
У `Laravel/.env` додайте `JWT_SECRET` (щонайменше 32 символи). Його можна створити командою `php -r "echo bin2hex(random_bytes(32));"`.

### Symfony
У `php.ini` мають бути увімкнені `extension=sodium` і `extension=openssl`.
```powershell
cd Symfony
composer install
php bin/console lexik:jwt:generate-keypair
php bin/console doctrine:migrations:migrate
php bin/console app:create-user admin@test.com Admin12345 admin --name=Admin
```
Безпека налаштована в `config/packages/security.yaml` (firewall, `json_login`, `jwt`, `role_hierarchy`, `access_control`) і `config/packages/lexik_jwt_authentication.yaml`.

Якщо на Windows `generate-keypair` показує `error:80000003`, виконайте перед ним `$env:OPENSSL_CONF = "C:\php\extras\ssl\openssl.cnf"`.

### Ролі
| Роль | Перегляд | Додавання й редагування | Видалення та керування ролями |
|---|---|---|---|
| client | так | ні | ні |
| manager | так | так | ні |
| admin | так | так | так |

Ролі `manager` і `client` створюються тією самою командою `app:create-user`. Новий користувач після реєстрації отримує роль `client`.

### API
- `POST /api/login` з JSON `{"email": "...", "password": "..."}` повертає JWT.
- `GET /api/me` з заголовком `Authorization: Bearer <токен>` повертає поточного користувача.