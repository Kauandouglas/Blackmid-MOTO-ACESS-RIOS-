<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| GitHub Webhook Deploy (single file)
|--------------------------------------------------------------------------
| 1) Configure no servidor:
|    - GITHUB_WEBHOOK_SECRET
|    - DEPLOY_BRANCH (opcional, padrao: main)
|
| 2) Configure no GitHub Webhook:
|    - URL: https://seu-dominio/github-webhook-deploy.php
|    - Content type: application/json
|    - Secret: mesmo valor de GITHUB_WEBHOOK_SECRET
|    - Events: Just the push event
*/

header('Content-Type: application/json; charset=utf-8');

$projectRoot = dirname(__DIR__);
$logFile = $projectRoot . '/storage/logs/github-webhook-deploy.log';
$deployBranch = getenv('DEPLOY_BRANCH') ?: 'main';
$secret = getenv('GITHUB_WEBHOOK_SECRET') ?: '';

function respond(int $status, array $payload): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function appendLog(string $logFile, string $message): void
{
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    @file_put_contents($logFile, $line, FILE_APPEND);
}

function headerValue(string $name): ?string
{
    $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
    if (isset($_SERVER[$serverKey])) {
        return (string) $_SERVER[$serverKey];
    }

    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        foreach ($headers as $key => $value) {
            if (strcasecmp((string) $key, $name) === 0) {
                return (string) $value;
            }
        }
    }

    return null;
}

function runShell(string $command, string $cwd): array
{
    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open(['/bin/bash', '-lc', $command], $descriptorSpec, $pipes, $cwd);
    if (! is_resource($process)) {
        return [
            'exitCode' => 1,
            'output' => 'Falha ao iniciar processo de deploy.',
        ];
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    $exitCode = proc_close($process);

    return [
        'exitCode' => $exitCode,
        'output' => trim((string) $stdout . PHP_EOL . (string) $stderr),
    ];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['ok' => false, 'message' => 'Metodo nao permitido. Use POST.']);
}


$payloadRaw = file_get_contents('php://input') ?: '';
$signature = headerValue('X-Hub-Signature-256');
$event = headerValue('X-GitHub-Event') ?: 'unknown';

if (! $signature) {
    appendLog($logFile, 'Deploy bloqueado: assinatura ausente.');
    respond(401, ['ok' => false, 'message' => 'Assinatura ausente.']);
}

if ($event === 'ping') {
    respond(200, ['ok' => true, 'message' => 'pong']);
}

if ($event !== 'push') {
    respond(202, ['ok' => true, 'message' => 'Evento ignorado: ' . $event]);
}

$payload = json_decode($payloadRaw, true);
if (! is_array($payload)) {
    respond(400, ['ok' => false, 'message' => 'Payload JSON invalido.']);
}

$ref = (string) ($payload['ref'] ?? '');
$expectedRef = 'refs/heads/' . $deployBranch;

if ($ref !== $expectedRef) {
    respond(202, [
        'ok' => true,
        'message' => 'Push ignorado. Branch recebida: ' . $ref . ', branch esperada: ' . $expectedRef,
    ]);
}

$gitDir  = escapeshellarg($projectRoot . '/.git');
$gitWork = escapeshellarg($projectRoot);
$branch  = escapeshellarg($deployBranch);
$safeDir = 'safe.directory=' . $projectRoot;

$commands = [
    // 1. Garante HOME graveável para processos filhos
    'export HOME=/tmp',
    'export GIT_CONFIG_NOSYSTEM=1',
    'export GIT_CONFIG_GLOBAL=/tmp/.gitconfig-deploy',

    // 2. Tenta corrigir dono do .git (funciona se sudoers estiver configurado)
    'sudo chown -R "$(id -u):$(id -g)" ' . $gitDir . ' 2>/dev/null || true',

    // 3. Para em qualquer erro daqui em diante
    'set -e',

    // 4. git -c injeta safe.directory sem precisar escrever em .gitconfig
    'git -C ' . $gitWork . ' -c ' . escapeshellarg($safeDir) . ' fetch origin ' . $branch,
    'git -C ' . $gitWork . ' -c ' . escapeshellarg($safeDir) . ' checkout ' . $branch,
    'git -C ' . $gitWork . ' -c ' . escapeshellarg($safeDir) . ' reset --hard ' . escapeshellarg('origin/' . $deployBranch),

    // 5. Corrige permissões após o fetch para o próximo deploy já funcionar
    'find ' . $gitDir . ' -not -perm -u+w -exec chmod u+w {} + 2>/dev/null || true',

    // 6. Dependências e artisan
    'composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev',
    'php artisan migrate --force',
    'php artisan db:seed --class=BlogPostSeeder --force',
    'php artisan db:seed --class=NavigationSeeder --force',
    'php artisan optimize',
];

$result = runShell(implode(' && ', $commands), $projectRoot);

$logOutput = "event={$event} ref={$ref} exit={$result['exitCode']} output=\n{$result['output']}\n";
appendLog($logFile, $logOutput);

if ((int) $result['exitCode'] !== 0) {
    respond(500, [
        'ok' => false,
        'message' => 'Deploy falhou. Verifique o log em storage/logs/github-webhook-deploy.log',
        'exitCode' => $result['exitCode'],
    ]);
}

respond(200, [
    'ok' => true,
    'message' => 'Deploy concluido com sucesso.',
    'branch' => $deployBranch,
]);
