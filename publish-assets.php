<?php
/**
 * Copia os assets do Livewire e Filament diretamente do vendor para o public.
 * Roda durante o docker build sem precisar de .env ou banco de dados.
 */

$base   = __DIR__;
$public = $base . '/public';

function copyDir(string $src, string $dst): int {
    if (!is_dir($src)) return 0;
    if (!is_dir($dst)) mkdir($dst, 0755, true);
    $count = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS)) as $file) {
        $dest = $dst . '/' . substr($file->getPathname(), strlen($src) + 1);
        $dir  = dirname($dest);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        copy($file->getPathname(), $dest);
        $count++;
    }
    return $count;
}

// Livewire assets
$livewireSrc = $base . '/vendor/livewire/livewire/dist';
$livewireDst = $public . '/livewire';
$n = copyDir($livewireSrc, $livewireDst);
echo "Livewire: {$n} arquivos copiados para public/livewire\n";

// Filament — descobre todos os pacotes filament com assets
$filamentVendors = [
    'filament/filament'      => 'filament/filament',
    'filament/support'       => 'filament/support',
    'filament/actions'       => 'filament/actions',
    'filament/forms'         => 'filament/forms',
    'filament/tables'        => 'filament/tables',
    'filament/notifications' => 'filament/notifications',
    'filament/widgets'       => 'filament/widgets',
    'filament/infolists'     => 'filament/infolists',
];

foreach ($filamentVendors as $vendor => $dstName) {
    foreach (['dist', 'resources/dist'] as $distDir) {
        $src = $base . '/vendor/' . $vendor . '/' . $distDir;
        if (!is_dir($src)) continue;

        // Determina pasta de destino baseada no tipo de arquivo
        foreach (new DirectoryIterator($src) as $file) {
            if ($file->isDot()) continue;
            $ext = $file->getExtension();
            if (in_array($ext, ['css'])) {
                $dst = $public . '/css/' . $dstName;
            } elseif (in_array($ext, ['js', 'map'])) {
                $dst = $public . '/js/' . $dstName;
            } else {
                continue;
            }
            if (!is_dir($dst)) mkdir($dst, 0755, true);
            copy($file->getPathname(), $dst . '/' . $file->getFilename());
        }
        $n = iterator_count(new FilesystemIterator($src));
        echo "Filament {$vendor}: {$n} arquivos copiados\n";
    }
}

echo "✅ Assets publicados com sucesso!\n";
