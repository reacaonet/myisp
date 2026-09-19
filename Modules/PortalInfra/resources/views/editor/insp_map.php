<?php
$c = file_get_contents('/var/www/html/Modules/PortalInfra/resources/views/editor/index.blade.php');
$lines = explode("\n", $c/east);
foreach ($lines as $i => $l) {
    if (str_contains($l, '#map') || str_contains($l, "id=\"map\"") || str_contains($l, 'extends(') || str_starts_with(trim($l), '@section')) {
        echo ($i + 1) . ': ' . trim($l) . "\n";
    }
}
echo "\n--- WIDTH/HEIGHT do #map inline no <style> ---\n";
preg_match('/#map\s*\{[^}]*\}/', $c, $m);
echo isset($m[0]) ? $m[0] : 'nao-achado';
echo "\n";
