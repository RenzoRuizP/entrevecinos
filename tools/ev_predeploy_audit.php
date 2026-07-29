#!/usr/bin/env php
<?php
declare(strict_types=1);

$root = realpath($argv[1] ?? dirname(__DIR__));
$packageMode = in_array('--package', $argv, true);
if (!$root || !is_dir($root)) {
    fwrite(STDERR, "Ruta de proyecto inválida.\n");
    exit(2);
}

$excludedDirs = ['vendor', 'uploads', '.git'];
$findings = [];
$patterns = [
    'fallback_local_hardcodeado' => '/\\|\\|\\s*[\'\"]\\/?entrevecinos\\/?[\'\"]/',
    'guard_base_invalido' => '/if\\s*\\([^\\n)]*!\\s*[A-Za-z0-9_]*(?:base|BASE)[A-Za-z0-9_]*[^\\n)]*\\)\\s*(?:\\{|return)/',
    'dominio_resources_invalido' => '#https?://resources(?:/|$)#i',
    'recurso_protocol_relative' => '#[\'\"]//resources/#',
];

$iterator = new RecursiveIteratorIterator(
    new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        static function (SplFileInfo $current) use ($excludedDirs): bool {
            return !$current->isDir() || !in_array($current->getFilename(), $excludedDirs, true);
        }
    )
);

foreach ($iterator as $file) {
    if (!$file->isFile()) continue;
    $name = $file->getFilename();
    if (str_contains($name, '.bak') || str_ends_with($name, '.log')) continue;
    if (!in_array(strtolower($file->getExtension()), ['php', 'js', 'html', 'css'], true)) continue;
    if (str_contains(str_replace('\\', '/', $file->getPathname()), '/resources/util/')) continue;

    $lines = @file($file->getPathname());
    if (!is_array($lines)) continue;

    foreach ($lines as $index => $line) {
        foreach ($patterns as $type => $pattern) {
            if (preg_match($pattern, $line)) {
                $findings[] = [
                    'type' => $type,
                    'file' => str_replace($root . DIRECTORY_SEPARATOR, '', $file->getPathname()),
                    'line' => $index + 1,
                    'text' => trim($line),
                ];
            }
        }
    }
}

if ($packageMode && is_file($root . '/.env')) {
    $findings[] = [
        'type' => 'secreto_en_paquete',
        'file' => '.env',
        'line' => 1,
        'text' => 'El paquete contiene .env; debe preservarse fuera del ZIP de promoción.',
    ];
} elseif (!$packageMode && is_file($root . '/.env')) {
    echo "AVISO: .env presente como configuración del entorno desplegado. No debe incorporarse al ZIP de promoción.\n";
}

if (!$findings) {
    echo "OK: auditoría preventiva EV sin hallazgos bloqueantes.\n";
    exit(0);
}

foreach ($findings as $finding) {
    printf("[%s] %s:%d %s\n", $finding['type'], $finding['file'], $finding['line'], $finding['text']);
}

fwrite(STDERR, sprintf("ERROR: %d hallazgo(s) bloqueante(s).\n", count($findings)));
exit(1);
