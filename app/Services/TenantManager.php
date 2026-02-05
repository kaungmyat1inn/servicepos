<?php

namespace App\Services;

use App\Models\Central\Shop;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantManager
{
    protected ?Shop $shop = null;

    public function setShop(Shop $shop): void
    {
        $this->shop = $shop;

        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'host' => $shop->db_host,
            'port' => $shop->db_port,
            'database' => $shop->db_name,
            'username' => $shop->db_username,
            'password' => $shop->db_password,
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    public function generateApiKey(): string
    {
        return Str::random(40);
    }

    public function provisionDatabase(Shop $shop): void
    {
        $dbName = $shop->db_name;
        $dbUser = $shop->db_username;
        $dbPass = $shop->db_password;

        DB::connection('central')->statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        DB::connection('central')->statement("CREATE USER IF NOT EXISTS '{$dbUser}'@'%' IDENTIFIED BY '{$dbPass}'");
        DB::connection('central')->statement("GRANT ALL PRIVILEGES ON `{$dbName}`.* TO '{$dbUser}'@'%'");
        DB::connection('central')->statement('FLUSH PRIVILEGES');

        $this->setShop($shop);

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);

        // Seed default ticket statuses
        DB::connection('tenant')->table('ticket_statuses')->insert([
            ['name' => 'Pending', 'sort_order' => 1, 'is_closed' => false],
            ['name' => 'In Progress', 'sort_order' => 2, 'is_closed' => false],
            ['name' => 'Waiting for Parts', 'sort_order' => 3, 'is_closed' => false],
            ['name' => 'Ready for Pickup', 'sort_order' => 4, 'is_closed' => false],
            ['name' => 'Completed', 'sort_order' => 5, 'is_closed' => true],
            ['name' => 'Cancelled', 'sort_order' => 6, 'is_closed' => true],
        ]);
    }
}
