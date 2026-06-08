<?php

namespace Database\Seeders;

use App\Models\User;
use Closure;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class DatabaseSeeder extends Seeder
{
    private const DEFAULT_PASSWORD = 'password';

    public function run(): void
    {
        $this->safeSeed('roles', fn () => $this->seedRoles());
        $this->seedUsers();

        $this->safeSeed('site settings', fn () => $this->seedSettings());
        $this->safeSeed('comic categories', fn () => $this->seedTaxonomyTables());
        $this->safeSeed('comic statuses', fn () => $this->seedStatusTables());
    }

    private function seedRoles(): void
    {
        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            foreach ($this->roles() as $role) {
                \Spatie\Permission\Models\Role::findOrCreate($role);
            }

            return;
        }

        if (! Schema::hasTable('roles')) {
            return;
        }

        foreach ($this->roles() as $role) {
            $data = [
                'name' => $role,
                'slug' => $role,
                'guard_name' => 'web',
                'display_name' => Str::headline($role),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $this->upsertTable('roles', ['name' => $role], $data);
        }
    }

    private function seedUsers(): void
    {
        $accounts = [
            [
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'username' => 'admin',
                'role' => 'admin',
            ],
            [
                'name' => 'Poster',
                'email' => 'poster@example.com',
                'username' => 'poster',
                'role' => 'poster',
            ],
            [
                'name' => 'User',
                'email' => 'user@example.com',
                'username' => 'user',
                'role' => 'user',
            ],
        ];

        $table = (new User())->getTable();
        $hasRoleColumn = $this->hasColumn($table, 'role');
        $hasRoleIdColumn = $this->hasColumn($table, 'role_id');
        $hasTypeColumn = $this->hasColumn($table, 'type');
        $hasUsernameColumn = $this->hasColumn($table, 'username');
        $hasEmailVerifiedAtColumn = $this->hasColumn($table, 'email_verified_at');
        $hasIsAdminColumn = $this->hasColumn($table, 'is_admin');
        $password = $this->seedPassword();
        $resetPasswords = filter_var(env('RESET_SEEDED_PASSWORDS', false), FILTER_VALIDATE_BOOL);

        foreach ($accounts as $account) {
            $user = User::firstOrNew(['email' => $account['email']]);

            $attributes = [
                'name' => $account['name'],
            ];

            if (! $user->exists || $resetPasswords) {
                $attributes['password'] = Hash::make($password);
            }

            if ($hasUsernameColumn) {
                $attributes['username'] = $account['username'];
            }

            if ($hasEmailVerifiedAtColumn) {
                $attributes['email_verified_at'] = now();
            }

            if ($hasRoleColumn) {
                $attributes['role'] = $account['role'];
            }

            if ($hasRoleIdColumn && $roleId = $this->roleId($account['role'])) {
                $attributes['role_id'] = $roleId;
            }

            if ($hasTypeColumn) {
                $attributes['type'] = $account['role'];
            }

            if ($hasIsAdminColumn) {
                $attributes['is_admin'] = $account['role'] === 'admin';
            }

            $user->forceFill($attributes)->save();
            $this->assignRole($user, $account['role']);
        }
    }

    private function seedSettings(): void
    {
        $settings = [
            'site_name' => 'Truyen Tranh',
            'site_description' => 'Doc truyen tranh online',
            'site_keywords' => 'truyen tranh, manga, manhwa, manhua',
            'maintenance_mode' => '0',
        ];

        foreach (['settings', 'site_settings'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($settings as $key => $value) {
                if ($this->hasColumn($table, 'key')) {
                    $this->upsertTable($table, ['key' => $key], [
                        'key' => $key,
                        'name' => $key,
                        'value' => $value,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    continue;
                }

                if ($this->hasColumn($table, 'name')) {
                    $this->upsertTable($table, ['name' => $key], [
                        'name' => $key,
                        'value' => $value,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    private function seedTaxonomyTables(): void
    {
        $items = [
            ['name' => 'Action', 'slug' => 'action'],
            ['name' => 'Adventure', 'slug' => 'adventure'],
            ['name' => 'Comedy', 'slug' => 'comedy'],
            ['name' => 'Drama', 'slug' => 'drama'],
            ['name' => 'Fantasy', 'slug' => 'fantasy'],
            ['name' => 'Romance', 'slug' => 'romance'],
            ['name' => 'School Life', 'slug' => 'school-life'],
            ['name' => 'Slice of Life', 'slug' => 'slice-of-life'],
            ['name' => 'Supernatural', 'slug' => 'supernatural'],
        ];

        foreach (['categories', 'genres', 'tags'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($items as $item) {
                $keys = $this->hasColumn($table, 'slug')
                    ? ['slug' => $item['slug']]
                    : ['name' => $item['name']];

                $this->upsertTable($table, $keys, [
                    'name' => $item['name'],
                    'title' => $item['name'],
                    'slug' => $item['slug'],
                    'description' => null,
                    'is_active' => true,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedStatusTables(): void
    {
        $statuses = [
            ['name' => 'Ongoing', 'slug' => 'ongoing'],
            ['name' => 'Completed', 'slug' => 'completed'],
            ['name' => 'Paused', 'slug' => 'paused'],
        ];

        foreach (['statuses', 'comic_statuses', 'story_statuses', 'manga_statuses'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($statuses as $status) {
                $keys = $this->hasColumn($table, 'slug')
                    ? ['slug' => $status['slug']]
                    : ['name' => $status['name']];

                $this->upsertTable($table, $keys, [
                    'name' => $status['name'],
                    'title' => $status['name'],
                    'slug' => $status['slug'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function roleId(string $role): ?int
    {
        if (! Schema::hasTable('roles') || ! $this->hasColumn('roles', 'id')) {
            return null;
        }

        if (! $this->hasColumn('roles', 'name') && ! $this->hasColumn('roles', 'slug')) {
            return null;
        }

        $query = DB::table('roles');

        if ($this->hasColumn('roles', 'name')) {
            $query->orWhere('name', $role);
        }

        if ($this->hasColumn('roles', 'slug')) {
            $query->orWhere('slug', $role);
        }

        return $query->value('id');
    }

    private function assignRole(User $user, string $role): void
    {
        try {
            if (method_exists($user, 'syncRoles')) {
                $user->syncRoles([$role]);
                return;
            }

            if (method_exists($user, 'assignRole') && ! $user->hasRole($role)) {
                $user->assignRole($role);
            }
        } catch (Throwable $exception) {
            $this->warn("Skipped package role assignment for {$user->email}: {$exception->getMessage()}");
        }
    }

    private function upsertTable(string $table, array $keys, array $values): void
    {
        $keys = $this->onlyExistingColumns($table, $keys);
        $values = $this->onlyExistingColumns($table, $values);

        if ($keys === []) {
            return;
        }

        if (! $this->hasAnyRow($table, $keys)) {
            DB::table($table)->insert($values);
            return;
        }

        DB::table($table)->where($keys)->update($values);
    }

    private function hasAnyRow(string $table, array $keys): bool
    {
        $query = DB::table($table);

        foreach ($keys as $column => $value) {
            $query->where($column, $value);
        }

        return $query->exists();
    }

    private function onlyExistingColumns(string $table, array $values): array
    {
        return collect($values)
            ->filter(fn (mixed $value, string $column): bool => $this->hasColumn($table, $column))
            ->all();
    }

    private function hasColumn(string $table, string $column): bool
    {
        return Schema::hasTable($table) && Schema::hasColumn($table, $column);
    }

    private function roles(): array
    {
        return ['admin', 'poster', 'user'];
    }

    private function seedPassword(): string
    {
        $password = env('SEEDER_PASSWORD');

        if ($password) {
            return $password;
        }

        if (app()->environment('production')) {
            throw new RuntimeException('Set SEEDER_PASSWORD before running db:seed in production.');
        }

        return self::DEFAULT_PASSWORD;
    }

    private function safeSeed(string $name, Closure $callback): void
    {
        try {
            $callback();
        } catch (Throwable $exception) {
            $this->warn("Skipped {$name} seed: {$exception->getMessage()}");
        }
    }

    private function warn(string $message): void
    {
        if ($this->command) {
            $this->command->warn($message);
        }
    }
}
