// Диагностика «выкидывает из портала» — только чтение, ничего не меняет.
// Вставить в «Настройки → Инструменты → Командная PHP-строка» и выполнить.
$r = [];
$db = \Bitrix\Main\Application::getConnection();

// 1. Время: сервер, PHP, MySQL (расхождение ломает сроки сессий и «запомнить меня»)
$r['time'] = [
    'php' => date('Y-m-d H:i:s T'),
    'php_tz' => date_default_timezone_get(),
    'mysql' => $db->query('SELECT NOW() AS n, @@session.time_zone AS tz')->fetch(),
];

// 2. Сессии PHP
foreach (['session.save_handler','session.save_path','session.gc_maxlifetime','session.gc_probability','session.gc_divisor',
          'session.cookie_lifetime','session.cookie_secure','session.cookie_samesite','session.cookie_domain','session.use_strict_mode'] as $k)
    $r['php_session'][$k] = ini_get($k);

// 3. Сессии и cookies Битрикса (.settings.php)
$r['bx_session'] = \Bitrix\Main\Config\Configuration::getValue('session');
$r['bx_cookies'] = \Bitrix\Main\Config\Configuration::getValue('cookies');
foreach (['session_expand','session_show_message','session_auth_only','store_password','use_secure_password_cookies',
          'auth_multisite','cookie_name','use_digest_auth','allow_socserv_authorization'] as $k)
    $r['main_options'][$k] = \Bitrix\Main\Config\Option::get('main', $k, '—');

// 4. Проактивная защита: защита сессий, смена ID сессии
if (\Bitrix\Main\Loader::includeModule('security')) {
    foreach (\Bitrix\Main\Config\Option::getForModule('security') as $k => $v)
        if (preg_match('/sess|sid|ip|otp/i', $k)) $r['security'][$k] = $v;
} else $r['security'] = 'модуль не установлен';

// 5. Политика безопасности групп (таймаут сессии, привязка к IP)
$res = \Bitrix\Main\GroupTable::getList(['select' => ['ID', 'NAME', 'SECURITY_POLICY']]);
while ($g = $res->fetch()) {
    $p = $g['SECURITY_POLICY'] ? @unserialize($g['SECURITY_POLICY'], ['allowed_classes' => false]) : null;
    if (!$p) continue;
    $r['group_policy'][$g['ID'] . ' ' . $g['NAME']] = array_intersect_key($p, array_flip(
        ['SESSION_TIMEOUT', 'SESSION_IP_MASK', 'MAX_STORE_NUM', 'STORE_IP_MASK', 'STORE_TIMEOUT', 'CHECKWORD_TIMEOUT', 'PASSWORD_CHANGE_DAYS']));
}
$r['group_policy'] = $r['group_policy'] ?? 'политики групп не заданы (по умолчанию)';

// 6. Папка сессий и её чистка
$sp = ini_get('session.save_path');
$sp = $sp ? preg_replace('/^.*;/', '', $sp) : '';
if ($sp && is_dir($sp)) {
    $f = glob($sp . '/sess_*') ?: [];
    $t = array_map('filemtime', $f);
    $r['session_dir'] = [$sp, 'файлов' => count($f), 'старейший' => $t ? date('d.m H:i', min($t)) : '-', 'свежайший' => $t ? date('d.m H:i', max($t)) : '-'];
}
$r['tmpfiles_tmp'] = @file_get_contents('/usr/lib/tmpfiles.d/tmp.conf') ?: 'нет';

// 7. Журнал событий за 7 дней: входы, выходы, срабатывания защиты
$r['event_log_7d'] = [];
$q = $db->query("SELECT AUDIT_TYPE_ID, COUNT(*) C, MAX(TIMESTAMP_X) LAST FROM b_event_log
                 WHERE TIMESTAMP_X > NOW() - INTERVAL 7 DAY
                   AND (AUDIT_TYPE_ID LIKE 'USER_%' OR AUDIT_TYPE_ID LIKE 'SECURITY_%')
                 GROUP BY AUDIT_TYPE_ID ORDER BY C DESC");
while ($e = $q->fetch()) $r['event_log_7d'][$e['AUDIT_TYPE_ID']] = $e['C'] . ' (последнее ' . $e['LAST'] . ')';
$r['stored_auth'] = $db->query('SELECT COUNT(*) C, MAX(LAST_AUTH) L FROM b_user_stored_auth')->fetch();

echo '<pre>' . htmlspecialcharsbx(print_r($r, true)) . '</pre>';
