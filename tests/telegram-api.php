<?php
/** Tes transport HTTP dengan HTTPS palsu; tidak ada request ke Telegram. */
require __DIR__ . '/../app/config/database.php';
require __DIR__ . '/../app/telegram.php';
require __DIR__ . '/../app/telegram-api.php';
putenv('TELEGRAM_BOT_TOKEN=123:test');
putenv('TELEGRAM_BOT_USERNAME=putu_pbl_bot');

function expect(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

class FakeTelegramStream
{
    public $context;
    public static string $response = '';
    public static bool $available = true;
    public static array $options = [];
    private int $position = 0;
    public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
    {
        self::$options = stream_context_get_options($this->context);
        return self::$available;
    }
    public function stream_read(int $count): string
    {
        $chunk = substr(self::$response, $this->position, $count);
        $this->position += strlen($chunk);
        return $chunk;
    }
    public function stream_eof(): bool { return $this->position >= strlen(self::$response); }
    public function stream_stat(): array { return []; }
}

stream_wrapper_unregister('https');
stream_wrapper_register('https', FakeTelegramStream::class);
try {
    FakeTelegramStream::$response = '{"ok":true,"result":[]}';
    expect(telegram_api('getUpdates', ['timeout' => 25, 'allowed_updates' => ['message']]) === [], 'Parse hasil API.');
    $http = FakeTelegramStream::$options['http'];
    $request = json_decode($http['content'], true, 512, JSON_THROW_ON_ERROR);
    expect($http['method'] === 'POST' && $http['follow_location'] === 0, 'POST dan larang redirect token.');
    expect($request['allowed_updates'] === ['message'], 'Array nested harus tetap array JSON.');
    telegram_api('getMe');
    expect(FakeTelegramStream::$options['http']['content'] === '{}', 'Parameter kosong harus object JSON.');
    foreach ([['{"ok":false,"error_code":429,"parameters":{"retry_after":123},"description":"secret"}', 429],
        ['{"ok":false,"error_code":401}', 401], ['not json', 0], ['null', 0]] as [$body, $code]) {
        FakeTelegramStream::$response = $body;
        try {
            telegram_api('getMe');
            throw new LogicException('Respons gagal harus melempar error.');
        } catch (TelegramApiException $e) {
            expect($e->getCode() === $code, 'Kode API harus dipertahankan.');
            expect(!str_contains($e->getMessage(), 'secret') && !str_contains($e->getMessage(), '123:test'), 'Error tidak boleh membocorkan token atau respons mentah.');
            if ($code === 429) {
                expect($e->retryAfter === 123, 'Hormati retry_after.');
            }
        }
    }
    FakeTelegramStream::$available = false;
    try {
        telegram_api('getMe');
        throw new LogicException('Gangguan jaringan harus melempar error.');
    } catch (TelegramApiException $e) {
        expect($e->getCode() === 0, 'Gangguan transport memakai kode 0.');
    }
    echo "PASS: POST JSON, array updates, API errors, rate limit, malformed response, transport failure, dan token redaction.\n";
} finally {
    stream_wrapper_restore('https');
}
