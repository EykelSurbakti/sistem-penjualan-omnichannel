<?php
$packagesFile = __DIR__ . '/bootstrap/cache/packages.php';
$servicesFile = __DIR__ . '/bootstrap/cache/services.php';

$devProviders = [
    'Laravel\\Pail\\PailServiceProvider',
    'Laravel\\Pao\\Laravel\\ServiceProvider',
    'NunoMaduro\\Collision\\Adapters\\Laravel\\CollisionServiceProvider',
    'Laravel\\Tinker\\TinkerServiceProvider'
];

$devPackages = [
    'laravel/pail',
    'laravel/pao',
    'nunomaduro/collision',
    'mockery/mockery',
    'phpunit/phpunit',
    'fakerphp/faker'
];

if (file_exists($packagesFile)) {
    $packages = include $packagesFile;
    foreach ($devPackages as $pkg) {
        unset($packages[$pkg]);
    }
    file_put_contents($packagesFile, "<?php return " . var_export($packages, true) . ";\n");
    echo "packages.php cleaned.\n";
}

if (file_exists($servicesFile)) {
    $services = include $servicesFile;
    if (isset($services['providers'])) {
        $services['providers'] = array_values(array_filter($services['providers'], function ($provider) use ($devProviders) {
            return !in_array($provider, $devProviders);
        }));
    }
    foreach (['eager', 'deferred'] as $key) {
        if (isset($services[$key]) && is_array($services[$key])) {
            foreach ($services[$key] as $provider => $arr) {
                if (in_array($provider, $devProviders)) {
                    unset($services[$key][$provider]);
                }
            }
        }
    }
    file_put_contents($servicesFile, "<?php return " . var_export($services, true) . ";\n");
    echo "services.php cleaned.\n";
}
