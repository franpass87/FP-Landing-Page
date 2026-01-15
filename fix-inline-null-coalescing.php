<?php
/**
 * Script per convertire operatori ?? dentro tag PHP inline nei template HTML
 */

$file = 'src/Admin/LandingPageBuilder.php';

if (!file_exists($file)) {
    die("File non trovato: $file\n");
}

$content = file_get_contents($file);

// Pattern più specifici per i casi dentro tag PHP inline
// Gestisce: <?php echo esc_attr($data['key'] ?? ''); ?>
// Gestisce: <?php echo esc_textarea($data['key'] ?? ''); ?>
// Gestisce: <?php selected($data['key'] ?? 'default', 'value'); ?>

// Pattern per esc_attr($data['key'] ?? '')
$content = preg_replace(
    '/esc_attr\(\$data\[\'([^\']+)\'\]\s*\?\?\s*\'([^\']*)\'\)/',
    'esc_attr(isset($data[\'$1\']) ? $data[\'$1\'] : \'$2\')',
    $content
);

// Pattern per esc_textarea($data['key'] ?? '')
$content = preg_replace(
    '/esc_textarea\(\$data\[\'([^\']+)\'\]\s*\?\?\s*\'([^\']*)\'\)/',
    'esc_textarea(isset($data[\'$1\']) ? $data[\'$1\'] : \'$2\')',
    $content
);

// Pattern per selected($data['key'] ?? 'default', 'value')
$content = preg_replace(
    '/selected\(\$data\[\'([^\']+)\'\]\s*\?\?\s*\'([^\']*)\',/',
    'selected(isset($data[\'$1\']) ? $data[\'$1\'] : \'$2\',',
    $content
);

// Pattern per selected($data['key'] ?? '3', '2')
$content = preg_replace(
    '/selected\(\$data\[\'([^\']+)\'\]\s*\?\?\s*\'(\d+)\',/',
    'selected(isset($data[\'$1\']) ? $data[\'$1\'] : \'$2\',',
    $content
);

// Pattern per casi fuori dalle funzioni: $data['key'] ?? ''
$content = preg_replace(
    '/\$data\[\'([^\']+)\'\]\s*\?\?\s*\'([^\']*)\'/',
    '(isset($data[\'$1\']) ? $data[\'$1\'] : \'$2\')',
    $content
);

// Pattern per $data['key'] ?? '3' (stringhe numeriche)
$content = preg_replace(
    '/\$data\[\'([^\']+)\'\]\s*\?\?\s*\'(\d+)\'/',
    '(isset($data[\'$1\']) ? $data[\'$1\'] : \'$2\')',
    $content
);

file_put_contents($file, $content);
echo "Convertiti tutti gli operatori ?? inline in $file\n";

// Verifica se ne sono rimasti (usa pattern più semplice)
$remaining = preg_match_all('/\$[a-zA-Z_]+\[\'[^\']+\'\]\s*\?\?/', $content);
if ($remaining > 0) {
    echo "ATTENZIONE: Rimangono $remaining occorrenze di ?? non convertite!\n";
} else {
    echo "Fatto! Tutti gli operatori ?? sono stati convertiti.\n";
}
