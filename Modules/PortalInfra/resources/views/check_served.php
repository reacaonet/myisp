<?php
$base = '/var/www/html/Modules/PortalInfra/resources/views';
$layoutsDir = $base . '/layouts';
$editor = $base . '/editor/index.blade.php';

echo "== LAYOUTS SERVED ==" . PHP_EOL;
$files = is_dir($layoutsDir) ? scandir($layoutsDir) : [];
foreach ($files as $f) {
    if (str_ends_with($f, '.blade.php')) {
        echo "* " . $f . " (" . filesize($layoutsDir . '/' . $f) . " bytes)" . PHP_EOL;
    }
}

echo PHP_EOL . "== EDITOR @extends ==" . PHP_EOL;
$content = file_get_contents($editor);
$lines = explode("\n", $content);
echo (isset($lines[0]) ? $lines[0] : '(vazio)') . PHP_EOL;

echo PHP_EOL . "== #map CSS ==" . PHP_EOL;
if (preg_match('/#map\s*\{[^}]*\}/', $content, $m)) {
    echo $m[0] . PHP_EOL;
} else {
    echo '(sem bloco #map{})' . PHP_EOL;
}

echo PHP_EOL . "== editor-master existe? ==" . PHP_EOL;
echo file_exists($layoutsDir . '/editor-master.blade.php') ? "SIM" : "NAO";
echo PHP_EOL;
