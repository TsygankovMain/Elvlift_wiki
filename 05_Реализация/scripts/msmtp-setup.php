// Настройка отправки почты портала через SMTP Яндекс 360 (msmtp BitrixVM).
// Вставить в «Настройки → Инструменты → Командная PHP-строка», заполнить три строки ниже и выполнить.
// Пишет /home/bitrix/.msmtprc (права 600), прежний файл сохраняет рядом, затем шлёт тестовое письмо.
// Пароль — пароль приложения Яндекс 360 для ящика, не основной. В git его не коммитим.
$from     = 'bitrix@elvlift.ru';      // ящик-отправитель, совпадает с email_from главного модуля
$password = 'ПАРОЛЬ_ПРИЛОЖЕНИЯ';      // пароль приложения Яндекс 360
$testTo   = 'i@egor-tsygankov.ru';    // куда прислать тестовое письмо

$home = '/home/bitrix';
$rc   = "$home/.msmtprc";
$log  = "$home/msmtp_default.log";
$ca   = file_exists('/etc/pki/tls/certs/ca-bundle.crt') ? '/etc/pki/tls/certs/ca-bundle.crt' : '/etc/ssl/certs/ca-certificates.crt';

if ($password === 'ПАРОЛЬ_ПРИЛОЖЕНИЯ') { echo 'Заполните пароль приложения'; return; }
if (file_exists($rc)) { copy($rc, $rc . '.bak.' . date('Ymd-His')); }

$conf = "# smtp account configuration for default (Mainsoft, " . date('d.m.Y') . ")\n"
    . "account default\n"
    . "logfile $log\n"
    . "host smtp.yandex.ru\n"
    . "port 465\n"
    . "from $from\n"
    . "auth on\n"
    . "user $from\n"
    . "password $password\n"
    . "tls on\n"
    . "tls_starttls off\n"
    . "tls_certcheck on\n"
    . "tls_trust_file $ca\n";

$ok = file_put_contents($rc, $conf) !== false && chmod($rc, 0600);
echo $ok ? "Записан $rc\n" : "Не удалось записать $rc\n";

$sent = mail($testTo, 'Тест почты портала b24.elvlift.ru', 'Письмо отправлено ' . date('d.m.Y H:i') . ' через msmtp → smtp.yandex.ru', "From: $from");
echo 'mail(): ' . ($sent ? 'OK' : 'FAIL') . "\n";
echo "Журнал msmtp:\n" . htmlspecialcharsbx(implode('', array_slice(@file($log) ?: ['пусто'], -5)));
