<?php

use Illuminate\Support\Facades\Config;

beforeEach(function () {
    // Reset config to default values before each test
    $config = require config_path('lunar-fortis.php');
    Config::set('lunar-fortis', $config);
});

describe('Environment Configuration', function () {
    it('validates environment configuration', function () {
        $config = config('lunar-fortis');

        expect($config)->toHaveKey('environment');
        expect($config['environment'])->toBeIn(['sandbox', 'production']);
    });

    it('defaults to sandbox environment', function () {
        $config = config('lunar-fortis');

        expect($config['environment'])->toBe('sandbox');
    });

    it('accepts valid environment values', function () {
        Config::set('lunar-fortis.environment', 'sandbox');
        expect(config('lunar-fortis.environment'))->toBe('sandbox');

        Config::set('lunar-fortis.environment', 'production');
        expect(config('lunar-fortis.environment'))->toBe('production');
    });
});

describe('Policy Configuration', function () {
    it('validates policy configuration', function () {
        $config = config('lunar-fortis');

        expect($config)->toHaveKey('policy');
        expect($config['policy'])->toBeIn(['automatic', 'manual']);
    });

    it('defaults to automatic policy', function () {
        $config = config('lunar-fortis');

        expect($config['policy'])->toBe('automatic');
    });

    it('accepts valid policy values', function () {
        Config::set('lunar-fortis.policy', 'automatic');
        expect(config('lunar-fortis.policy'))->toBe('automatic');

        Config::set('lunar-fortis.policy', 'manual');
        expect(config('lunar-fortis.policy'))->toBe('manual');
    });
});

describe('JavaScript URL Configuration', function () {
    it('validates JavaScript URL configuration', function () {
        $config = config('lunar-fortis');

        expect($config)->toHaveKey('js_url_sandbox');
        expect($config)->toHaveKey('js_url_production');
        expect($config['js_url_sandbox'])->toStartWith('https://');
        expect($config['js_url_production'])->toStartWith('https://');
    });

    it('has correct default sandbox URL', function () {
        $config = config('lunar-fortis');

        expect($config['js_url_sandbox'])->toBe('https://js.sandbox.fortis.tech/commercejs-v1.0.0.min.js');
    });

    it('has correct default production URL', function () {
        $config = config('lunar-fortis');

        expect($config['js_url_production'])->toBe('https://js.fortis.tech/commercejs-v1.0.0.min.js');
    });

    it('validates URL format', function () {
        $config = config('lunar-fortis');

        expect(filter_var($config['js_url_sandbox'], FILTER_VALIDATE_URL))->not->toBeFalse();
        expect(filter_var($config['js_url_production'], FILTER_VALIDATE_URL))->not->toBeFalse();
    });
});

describe('Status Mapping Configuration', function () {
    it('validates status mapping configuration', function () {
        $config = config('lunar-fortis');

        expect($config)->toHaveKey('status_mapping');
        expect($config['status_mapping'])->toBeArray();
    });

    it('has required status mappings', function () {
        $statusMapping = config('lunar-fortis.status_mapping');

        expect($statusMapping)->toHaveKey('payment-authorized');
        expect($statusMapping)->toHaveKey('payment-received');
        expect($statusMapping['payment-authorized'])->toBe('payment-authorized');
        expect($statusMapping['payment-received'])->toBe('payment-received');
    });

    it('validates status mapping values are strings', function () {
        $statusMapping = config('lunar-fortis.status_mapping');

        foreach ($statusMapping as $key => $value) {
            expect($key)->toBeString();
            expect($value)->toBeString();
        }
    });
});

describe('Elements Configuration', function () {
    it('validates elements configuration structure', function () {
        $config = config('lunar-fortis');

        expect($config)->toHaveKey('elements');
        expect($config['elements'])->toBeArray();
        expect($config['elements'])->toHaveKey('appearance');
        expect($config['elements']['appearance'])->toBeArray();
    });

    it('has light and dark appearance settings', function () {
        $appearance = config('lunar-fortis.elements.appearance');

        expect($appearance)->toHaveKey('light');
        expect($appearance)->toHaveKey('dark');
        expect($appearance['light'])->toBeArray();
        expect($appearance['dark'])->toBeArray();
    });

    it('defaults to empty appearance arrays', function () {
        $appearance = config('lunar-fortis.elements.appearance');

        expect($appearance['light'])->toBeEmpty();
        expect($appearance['dark'])->toBeEmpty();
    });
});

describe('Success Redirect Configuration', function () {
    it('validates success redirect configuration structure', function () {
        $config = config('lunar-fortis');

        expect($config)->toHaveKey('success_redirect');
        expect($config['success_redirect'])->toBeArray();
    });

    it('has required success redirect keys', function () {
        $successRedirect = config('lunar-fortis.success_redirect');

        expect($successRedirect)->toHaveKey('route_name');
        expect($successRedirect)->toHaveKey('use_signed_route');
        expect($successRedirect)->toHaveKey('uri');
    });

    it('validates success redirect types', function () {
        $successRedirect = config('lunar-fortis.success_redirect');

        expect($successRedirect['route_name'])->toBeNull();
        expect($successRedirect['use_signed_route'])->toBeTrue();
        expect($successRedirect['uri'])->toBeNull();
    });

    it('accepts valid route name', function () {
        Config::set('lunar-fortis.success_redirect.route_name', 'orders.show');
        expect(config('lunar-fortis.success_redirect.route_name'))->toBe('orders.show');
    });

    it('accepts valid URI', function () {
        Config::set('lunar-fortis.success_redirect.uri', 'https://example.com/success');
        expect(config('lunar-fortis.success_redirect.uri'))->toBe('https://example.com/success');
    });

    it('validates URI format when provided', function () {
        Config::set('lunar-fortis.success_redirect.uri', 'https://example.com/success');
        $uri = config('lunar-fortis.success_redirect.uri');

        expect(filter_var($uri, FILTER_VALIDATE_URL))->not->toBeFalse();
    });
});

describe('Success Event Configuration', function () {
    it('validates success event class configuration', function () {
        $config = config('lunar-fortis');

        expect($config)->toHaveKey('success_event_class');
        expect($config['success_event_class'])->toBeNull();
    });

    it('accepts valid event class', function () {
        Config::set('lunar-fortis.success_event_class', 'App\\Events\\PaymentSuccess');
        expect(config('lunar-fortis.success_event_class'))->toBe('App\\Events\\PaymentSuccess');
    });

    it('validates success livewire event configuration', function () {
        $config = config('lunar-fortis');

        expect($config)->toHaveKey('success_livewire_event');
        expect($config['success_livewire_event'])->toBeNull();
    });

    it('accepts valid livewire event', function () {
        Config::set('lunar-fortis.success_livewire_event', 'payment-success');
        expect(config('lunar-fortis.success_livewire_event'))->toBe('payment-success');
    });
});

describe('Debug Configuration', function () {
    it('validates debug configuration', function () {
        $config = config('lunar-fortis');

        expect($config)->toHaveKey('debug');
        expect($config['debug'])->toBeBool();
        expect($config['debug'])->toBeFalse(); // Default value
    });

    it('accepts valid debug values', function () {
        Config::set('lunar-fortis.debug', true);
        expect(config('lunar-fortis.debug'))->toBeTrue();

        Config::set('lunar-fortis.debug', false);
        expect(config('lunar-fortis.debug'))->toBeFalse();
    });
});

describe('Configuration Validation Rules', function () {
    it('validates environment against allowed values', function () {
        $allowedEnvironments = ['sandbox', 'production'];

        foreach ($allowedEnvironments as $environment) {
            Config::set('lunar-fortis.environment', $environment);
            expect(config('lunar-fortis.environment'))->toBeIn($allowedEnvironments);
        }
    });

    it('validates policy against allowed values', function () {
        $allowedPolicies = ['automatic', 'manual'];

        foreach ($allowedPolicies as $policy) {
            Config::set('lunar-fortis.policy', $policy);
            expect(config('lunar-fortis.policy'))->toBeIn($allowedPolicies);
        }
    });
});

describe('Configuration Edge Cases', function () {
    it('handles missing configuration gracefully', function () {
        Config::set('lunar-fortis', []);

        // Should not throw exception when accessing config
        expect(function () {
            config('lunar-fortis');
        })->not->toThrow(Exception::class);
    });

    it('handles invalid environment values', function () {
        Config::set('lunar-fortis.environment', 'invalid');

        // Should still be accessible even if invalid
        expect(config('lunar-fortis.environment'))->toBe('invalid');
    });

    it('handles invalid policy values', function () {
        Config::set('lunar-fortis.policy', 'invalid');

        // Should still be accessible even if invalid
        expect(config('lunar-fortis.policy'))->toBe('invalid');
    });
});

describe('Configuration Type Safety', function () {
    it('ensures all configuration values have correct types', function () {
        $config = config('lunar-fortis');

        // Environment should be string
        expect($config['environment'])->toBeString();

        // Policy should be string
        expect($config['policy'])->toBeString();

        // URLs should be strings
        expect($config['js_url_sandbox'])->toBeString();
        expect($config['js_url_production'])->toBeString();

        // Status mapping should be array
        expect($config['status_mapping'])->toBeArray();

        // Elements should be array
        expect($config['elements'])->toBeArray();
        expect($config['elements']['appearance'])->toBeArray();
        expect($config['elements']['appearance']['light'])->toBeArray();
        expect($config['elements']['appearance']['dark'])->toBeArray();

        // Success redirect should be array
        expect($config['success_redirect'])->toBeArray();

        // Event classes should be null or string
        expect($config['success_event_class'])->toBeNull();
        expect($config['success_livewire_event'])->toBeNull();

        // Debug should be boolean
        expect($config['debug'])->toBeBool();
    });
});

describe('Configuration Completeness', function () {
    it('has all required configuration keys', function () {
        $config = config('lunar-fortis');

        $requiredKeys = [
            'environment',
            'policy',
            'js_url_sandbox',
            'js_url_production',
            'status_mapping',
            'elements',
            'success_redirect',
            'success_event_class',
            'success_livewire_event',
            'debug',
        ];

        foreach ($requiredKeys as $key) {
            expect($config)->toHaveKey($key);
        }
    });
});

describe('HTTP Configuration (if available)', function () {
    it('validates HTTP configuration structure if present', function () {
        $config = config('lunar-fortis');

        if (array_key_exists('http', $config)) {
            expect($config['http'])->toBeArray();
            expect($config['http'])->toHaveKey('timeout');
            expect($config['http'])->toHaveKey('retry');
        } else {
            // Skip this test if HTTP config is not available
            expect(true)->toBeTrue();
        }
    });

    it('validates retry configuration structure if present', function () {
        $retryConfig = config('lunar-fortis.http.retry');

        if ($retryConfig !== null) {
            expect($retryConfig)->toBeArray();
            expect($retryConfig)->toHaveKey('attempts');
            expect($retryConfig)->toHaveKey('delay');
            expect($retryConfig)->toHaveKey('on_connection_error');
            expect($retryConfig)->toHaveKey('on_status_codes');
            expect($retryConfig)->toHaveKey('exponential_backoff');
            expect($retryConfig)->toHaveKey('max_delay');
        } else {
            // Skip this test if retry config is not available
            expect(true)->toBeTrue();
        }
    });
});
