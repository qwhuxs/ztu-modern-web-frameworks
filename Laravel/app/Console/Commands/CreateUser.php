<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateUser extends Command
{
    protected $signature = 'app:create-user {email} {password} {role=client} {--name=Користувач}';

    protected $description = 'Створює користувача або оновлює пароль і роль наявного';

    /**
     * Створює або оновлює користувача з указаною роллю.
     *
     * @return int Код завершення команди
     */
    public function handle(): int
    {
        $role = $this->argument('role');

        if (!array_key_exists($role, User::ROLE_LEVELS)) {
            $this->error('Роль має бути: client, manager або admin.');

            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => $this->argument('email')],
            ['name' => $this->option('name'), 'password' => $this->argument('password'), 'role' => $role]
        );

        $this->info("Готово: {$user->email} має роль {$user->role}.");

        return self::SUCCESS;
    }
}