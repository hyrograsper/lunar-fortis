<?php

namespace Hyrograsper\LunarFortis\Tests;

use Hyrograsper\LunarFortis\LunarFortisServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set up basic database structure for tests
        $this->setUpDatabase();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            LunarFortisServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        // Load .env.testing file if it exists
        $envTestingFile = base_path('.env.testing');
        if (file_exists($envTestingFile)) {
            $dotenv = \Dotenv\Dotenv::createImmutable(base_path(), '.env.testing');
            $dotenv->safeLoad();
        }

        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set up test configuration
        config()->set('lunar-fortis.environment', 'sandbox');
        config()->set('lunar-fortis.debug', true);
        config()->set('services.fortis', [
            'userId' => 'test-user-id',
            'userApiKey' => 'test-api-key',
            'developerId' => 'test-developer-id',
            'locationId' => 'test-location-id',
            'productTransactionId' => 'test-product-id',
            'terminalProductTransactionId' => 'test-terminal-product-id',
        ]);
    }

    protected function setUpDatabase(): void
    {
        // Create basic tables needed for tests
        if (! $this->app['db']->getSchemaBuilder()->hasTable('fortis_terminals')) {
            $this->app['db']->getSchemaBuilder()->create('fortis_terminals', function ($table) {
                $table->id();
                $table->string('fortis_id')->nullable();
                $table->string('location_id')->nullable();
                $table->string('title')->nullable();
                $table->string('serial_number')->nullable();
                $table->string('terminal_application_id')->nullable();
                $table->string('terminal_manufacturer_code')->nullable();
                $table->string('default_product_transaction_id')->nullable();
                $table->boolean('active')->default(true);
                $table->datetime('fortis_created_at')->nullable();
                $table->datetime('fortis_modified_at')->nullable();
                $table->string('created_user_id')->nullable();
                $table->string('modified_user_id')->nullable();
                $table->datetime('synced_at')->nullable();
                $table->json('fortis_data')->nullable();
                $table->timestamps();
            });
        }
    }
}
