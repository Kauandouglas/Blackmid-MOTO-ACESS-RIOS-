<?php

/*
|--------------------------------------------------------------------------
| Corretor de permissões do .git — uso único
|--------------------------------------------------------------------------
| Acesse via navegador ou curl:
|   https://seu-dominio/git-fix-permissions.php?secret=SEU_WEBHOOK_SECRET
|
| APÓS USO: delete este arquivo ou bloqueie o acesso por .htaccess.
*/

header('Content-Type: text/plain; charset=utf-8');

$secret = getenv('GITHUB_WEBHOOK_SECRET') ?: '';
$token  = (string) ($_GET['secret'] ?? '');

if ($secret === '' || ! hash_equals($secret, $token)) {
    http_response_code(403);
    echo "Acesso negado. Forneça ?secret=SEU_WEBHOOK_SECRET na URL.\n";
    exit;
}

$projectRoot = dirname(__DIR__);
$gitDir      = $projectRoot . '/.git';

echo "=== Corretor de permissões do .git ===\n";
echo "Usuário do processo PHP: " . get_current_user() . " (uid=" . getmyuid() . ")\n";
echo "Diretório raiz: {$projectRoot}\n\n";

// --- Verifica existência do .git ---
if (! is_dir($gitDir)) {
    echo "ERRO: diretório .git não encontrado em {$gitDir}\n";
    exit;
}

// --- Lista arquivos sem permissão de escrita ---
$problemFiles = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($gitDir, FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $file) {
    if (! $file->isWritable()) {
        $problemFiles[] = $file->getPathname();
    }
}

if (empty($problemFiles)) {
    echo "✅ Todos os arquivos em .git já são graváveis pelo usuário atual. Nenhuma correção necessária.\n";
    exit;
}

echo count($problemFiles) . " arquivo(s) sem permissão de escrita encontrado(s).\n\n";

// --- Tenta corrigir via chmod (funciona se PHP é dono dos arquivos) ---
$fixed   = 0;
$failed  = [];

foreach ($problemFiles as $filePath) {
    $perms = fileperms($filePath);
    if (@chmod($filePath, $perms | 0600)) {
        $fixed++;
    } else {
        $failed[] = $filePath;
    }
}

echo "Corrigidos via chmod: {$fixed}\n";

if (! empty($failed)) {
    echo "Não foi possível corrigir " . count($failed) . " arquivo(s) (PHP não é dono):\n";
    foreach (array_slice($failed, 0, 10) as $f) {
        echo "  - " . basename(dirname($f)) . '/' . basename($f) . "\n";
    }
    if (count($failed) > 10) {
        echo "  ... e mais " . (count($failed) - 10) . " arquivo(s).\n";
    }

    // --- Tenta sudo chown como último recurso ---
    echo "\nTentando sudo chown...\n";
    $uid = getmyuid();
    $gid = getmygid();
    $cmd = 'sudo chown -R ' . escapeshellarg("{$uid}:{$gid}") . ' ' . escapeshellarg($gitDir) . ' 2>&1';
    $out = shell_exec($cmd);
    echo $out !== null ? $out : "(sem saída)\n";

    // --- Tenta via comando find + chmod a+rw (último recurso) ---
    echo "\nTentando find + chmod a+rw (amplo, use temporariamente)...\n";
    $cmd2 = 'find ' . escapeshellarg($gitDir) . ' ! -perm -u+w -exec chmod u+w {} + 2>&1';
    echo shell_exec($cmd2) ?? "(sem saída)\n";

    echo "\n--- SOLUÇÃO DEFINITIVA ---\n";
    echo "Acesse o servidor via SSH e execute:\n";
    echo "  sudo chown -R www-data:www-data {$gitDir}\n";
    echo "Ou, se o usuário do Apache for diferente:\n";
    echo "  sudo chown -R \$(ps aux | grep -E 'apache|nginx|php-fpm' | grep -v grep | head -1 | awk '{print \$1}') {$gitDir}\n";
} else {
    echo "\n✅ Todos os arquivos corrigidos com sucesso! O próximo deploy deve funcionar.\n";
    echo "Apague este arquivo agora: rm {$_SERVER['SCRIPT_FILENAME']}\n";
}
