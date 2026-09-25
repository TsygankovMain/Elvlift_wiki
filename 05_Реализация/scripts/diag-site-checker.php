// Диагностика «Проверки системы» b24.elvlift.ru — только чтение, ничего не меняет.
// Вставить в «Настройки → Инструменты → Командная PHP-строка» и выполнить.
// Секреты (пароли БД, ключи Push) не выводятся.

$host = 'b24.elvlift.ru';
$r = [];
$sh = function ($cmd) { return function_exists('shell_exec') ? trim((string)@shell_exec($cmd . ' 2>&1')) : 'shell_exec запрещён'; };

// 1. Что за сервер
$r['php'] = PHP_VERSION;
$r['user'] = function_exists('posix_geteuid') ? posix_getpwuid(posix_geteuid())['name'] : get_current_user();
$r['os'] = php_uname();
$r['disable_functions'] = ini_get('disable_functions');
$r['main_version'] = SM_VERSION;
$r['bitrixenv'] = [
    '/opt/webdir' => is_dir('/opt/webdir'),
    '/etc/ansible' => is_dir('/etc/ansible'),
    '/home/bitrix/dehydrated' => is_dir('/home/bitrix/dehydrated'),
    '/etc/letsencrypt' => is_dir('/etc/letsencrypt'),
];

// 2. Как сервер видит сам себя
$r['server_addr'] = $_SERVER['SERVER_ADDR'] ?? '';
$r['resolve'] = gethostbyname($host);
$r['etc_hosts'] = @file_get_contents('/etc/hosts') ?: 'не читается';
foreach ([
    ['tcp://127.0.0.1', 80], ['tcp://127.0.0.1', 443], ['tcp://127.0.0.1', 8893], ['tcp://127.0.0.1', 8894],
    ["tcp://$host", 80], ["tcp://$host", 443],
] as [$h, $p]) {
    $t = microtime(true);
    $s = @fsockopen($h, $p, $en, $es, 5);
    $r['sockets'][] = "$h:$p " . ($s ? 'OK' : "FAIL $en $es") . ' ' . round(microtime(true) - $t, 2) . 's';
    if ($s) fclose($s);
}
foreach (["http://$host/", "http://127.0.0.1/", "https://$host/"] as $url) {
    $ctx = stream_context_create([
        'http' => ['timeout' => 5, 'ignore_errors' => true, 'header' => "Host: $host\r\n", 'follow_location' => 0],
        'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
    ]);
    $http_response_header = null;
    $body = @file_get_contents($url, false, $ctx);
    $err = error_get_last();
    $r['http'][$url] = $http_response_header ? $http_response_header[0] . ' | MikroTik: ' . (stripos((string)$body, 'mikrotik') !== false || stripos((string)$body, 'webfig') !== false ? 'ДА' : 'нет') : 'FAIL ' . ($err['message'] ?? '');
}

// 3. Сертификат: где лежит и до какого числа
$certs = array_merge(glob('/etc/nginx/ssl/*.pem') ?: [], glob('/etc/nginx/ssl/*.crt') ?: [], glob('/home/bitrix/dehydrated/certs/*/cert.pem') ?: [], glob('/etc/letsencrypt/live/*/cert.pem') ?: []);
foreach ($certs as $f) {
    $x = @openssl_x509_parse(@file_get_contents($f));
    $r['cert_files'][$f] = $x ? ($x['subject']['CN'] ?? '?') . ' до ' . date('d.m.Y', $x['validTo_time_t']) : 'не читается';
}
$r['nginx_ssl_lines'] = $sh("grep -rhE 'ssl_certificate |listen|subws|bx_temp|acme' /etc/nginx/bx/site_enabled/ /etc/nginx/sites-enabled/ /etc/nginx/conf.d/ 2>/dev/null | sort -u | head -40");
$r['crontab'] = $sh('crontab -l');

// 4. Почта
$r['mail'] = [
    'sendmail_path' => ini_get('sendmail_path'),
    'msmtp' => file_exists('/usr/bin/msmtp'),
    'msmtprc_exists' => file_exists('/etc/msmtprc') || file_exists('/home/bitrix/.msmtprc'),
    'custom_mail' => function_exists('custom_mail'),
    'BX_CRONTAB_SUPPORT' => defined('BX_CRONTAB_SUPPORT') ? BX_CRONTAB_SUPPORT : 'не задано',
    'smtp_settings' => \Bitrix\Main\Config\Configuration::getValue('smtp'),
    'email_from' => \Bitrix\Main\Config\Option::get('main', 'email_from'),
];
$r['mail']['msmtp_log_tail'] = $sh('tail -5 /home/bitrix/msmtp*.log /var/log/msmtp*.log');

// 5. Push and Pull, Диск
$pull = \Bitrix\Main\Config\Option::getForModule('pull');
foreach ($pull as $k => $v) if (preg_match('/key|signature|secret/i', $k)) $pull[$k] = '***';
$r['pull'] = $pull;
$r['bx_fast_download'] = \Bitrix\Main\Config\Option::get('main', 'bx_fast_download', 'N');
$r['ntlm'] = \Bitrix\Main\Config\Option::get('ldap', 'use_ntlm', 'N');

echo '<pre>' . htmlspecialcharsbx(print_r($r, true)) . '</pre>';
