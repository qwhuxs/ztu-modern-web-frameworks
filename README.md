# Лабораторна робота №4: фільтрація та пагінація

На сторінці кожної сутності є форма фільтрації за всіма полями таблиці та поле **Записів на сторінці** (`itemsPerPage`) зі значеннями 5, 10, 20 або 50.

Приклад адреси: `/patients?last_name=Шевченко&itemsPerPage=5&page=2`

## Вимоги

- PHP 8.2 або новіший з увімкненим розширенням `pdo_mysql`
- Composer
- MySQL 8.0

## Структура бази даних

| Таблиця | Що зберігає | Зв'язок |
|---|---|---|
| `patients` | Пацієнти | — |
| `doctors` | Лікарі | — |
| `appointments` | Прийоми | `patient_id` → `patients`, `doctor_id` → `doctors` |
| `diagnoses` | Діагнози | `appointment_id` → `appointments` |
| `treatments` | Лікування | `diagnosis_id` → `diagnoses` |

## 1. Створення баз даних

Виконайте в MySQL:

```sql
CREATE DATABASE medical_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE medical_symfony CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## 2. Запуск Laravel

```powershell
cd Laravel
composer install
copy .env.example .env
php artisan key:generate
```

У файлі `Laravel/.env` вкажіть дані для підключення:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=medical_laravel
DB_USERNAME=ваш_користувач
DB_PASSWORD=ваш_пароль
```

Створіть таблиці та запустіть сайт:

```powershell
php artisan migrate
php artisan serve
```

Сайт: http://127.0.0.1:8000/medical

## 3. Запуск Symfony

В окремому терміналі виконайте:

```powershell
cd Symfony
composer install
```

Створіть файл `Symfony/.env.local` і вкажіть дані для підключення та свою версію MySQL:

```env
DATABASE_URL="mysql://ваш_користувач:ваш_пароль@127.0.0.1:3306/medical_symfony?serverVersion=8.0.46&charset=utf8mb4"
```

Створіть таблиці та запустіть сайт:

```powershell
php bin/console doctrine:migrations:migrate
php -S 127.0.0.1:8001 -t public
```

Під час виконання міграції підтвердьте її, ввівши `yes`.

Сайт: http://127.0.0.1:8001/medical

## Порядок перевірки

Через зв'язки між таблицями записи треба додавати в такому порядку:

1. Пацієнт і лікар
2. Прийом
3. Діагноз
4. Лікування

Сторінки **Пацієнти**, **Лікарі**, **Прийоми**, **Діагнози** та **Лікування** дають змогу створювати, переглядати, редагувати й видаляти записи. Запис, з яким пов'язані інші дані, видалити не можна.