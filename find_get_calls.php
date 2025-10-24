<?php
$files = glob('app/**/*.php', GLOB_BRACE);
foreach($files as $file) {
    $content = file_get_contents($file);
    if(preg_match('/->get\(\)/', $content)) {
        echo $file . PHP_EOL;
    }
}
