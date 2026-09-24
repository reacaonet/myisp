<?php

namespace Modules\CRM\Services;

class MikrotikApi
{
    private $socket;
    private $connected = false;
    private $errorNo;
    private $errorStr;

    private int $port = 8728;
    private int $timeout = 5;
    private int $attempts = 3;

    public string $lastError = '';

    public function connect(string $ip, string $login, string $password): bool
    {
        $this->lastError = '';

        foreach (['challenge', 'plain'] as $method) {
            for ($attempt = 1; $attempt <= $this->attempts; $attempt++) {
                if ($this->tryLogin($method, $ip, $login, $password)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function tryLogin(string $method, string $ip, string $login, string $password): bool
    {
        $this->connected = false;

        $socket = @fsockopen($ip, $this->port, $this->errorNo, $this->errorStr, $this->timeout);

        if (!$socket) {
            $this->lastError = 'TCP: servidor inalcancavel na porta ' . $this->port . ' (' . ($this->errorStr ?: 'timeout') . ')';
            usleep(500000);
            return false;
        }

        socket_set_timeout($socket, $this->timeout);

        $this->socket = $socket;

        $this->write('/login');
        $response = $this->readFrom($socket);

        if (!isset($response[0]) || $response[0] !== '!done') {
            $this->lastError = 'API: respostainesperada do servidor';
            fclose($socket);
            return false;
        }

        if ($method === 'challenge') {
            $challenge = '';

            foreach ($response as $word) {
                if (preg_match('/^=ret=(.{32})$/i', $word, $m)) {
                    $challenge = $m[1];
                }
            }

            if ($challenge) {
                $this->write('/login', false);
                $this->write('=name=' . $login, false);
                $this->write('=response=00' . md5(chr(0) . $password . pack('H*', $challenge)));
                $this->write('');

                $loginResponse = $this->readFrom($socket);

                if (isset($loginResponse[0]) && $loginResponse[0] === '!done') {
                    $this->socket = $socket;
                    $this->connected = true;
                    $this->lastError = '';
                    return true;
                }

                $this->lastError = 'autenticacao recusada (login/senha invalidos) - ' . $this->trapMessage($loginResponse);
                fclose($socket);
                return false;
            }

            fclose($socket);
            return false;
        }

        $this->write('/login', false);
        $this->write('=name=' . $login, false);
        $this->write('=password=' . $password);
        $this->write('');
        $loginResponse = $this->readFrom($socket);

        if (isset($loginResponse[0]) && $loginResponse[0] !== '!done') {
            $this->lastError = 'autenticacao recusada (login/senha invalidos) - ' . $this->trapMessage($loginResponse);
            fclose($socket);
            return false;
        }

        $this->socket = $socket;
        $this->connected = true;
        $this->lastError = '';
        return true;
    }

    private function trapMessage(array $response): string
    {
        foreach ($response as $word) {
            if (preg_match('/^=message=(.+)$/i', $word, $m)) {
                return $m[1];
            }
        }

        return 'sem detalhe';
    }

    public function disconnect(): void
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }
        $this->socket = null;
        $this->connected = false;
    }

    public function isConnected(): bool
    {
        return $this->connected;
    }

    public function write(string $command, bool $eol = true): void
    {
        $command = str_replace(array("\r", "\n"), '', $command);
        $buffer = $this->encodeLength(strlen($command)) . $command;

        if ($eol) {
            $buffer .= chr(0);
        }

        fwrite($this->socket, $buffer);
    }

    public function read(bool $retainBuffer = true): array
    {
        return $this->readFrom($this->socket);
    }

    private function readFrom($socket): array
    {
        $response = [];

        while (true) {
            $sentence = $this->readSentenceFrom($socket);

            if (empty($sentence)) {
                break;
            }

            foreach ($sentence as $word) {
                $response[] = $word;
            }

            $tag = $sentence[0] ?? '';

            if (in_array($tag, ['!done', '!trap', '!fatal', '!final'], true)) {
                break;
            }
        }

        return $response;
    }

    private function readSentence(): array
    {
        return $this->readSentenceFrom($this->socket);
    }

    private function readSentenceFrom($socket): array
    {
        $sentence = [];

        while (true) {
            $word = $this->readWordFrom($socket);

            if ($word === '') {
                break;
            }

            $sentence[] = $word;
        }

        return $sentence;
    }

    private function readWord(): string
    {
        return $this->readWordFrom($this->socket);
    }

    private function readWordFrom($socket): string
    {
        $first = $this->readByteFrom($socket);

        if ($first === null) {
            return '';
        }

        $first = ord($first);

        if ($first < 0x80) {
            $length = $first;
        } elseif ($first < 0xC0) {
            $length = (($first & 0x3F) << 8) | $this->readByteOrdFrom($socket);
        } elseif ($first < 0xE0) {
            $length = (($first & 0x1F) << 16) | ($this->readByteOrdFrom($socket) << 8) | $this->readByteOrdFrom($socket);
        } else {
            $length = (($first & 0x0F) << 24) | ($this->readByteOrdFrom($socket) << 16) | ($this->readByteOrdFrom($socket) << 8) | $this->readByteOrdFrom($socket);
        }

        if ($length === 0 || $length > 65535) {
            return '';
        }

        $word = '';

        while (strlen($word) < $length) {
            $chunk = fread($socket, $length - strlen($word));

            if ($chunk === false || $chunk === '') {
                break;
            }

            $word .= $chunk;
        }

        return $word;
    }

    private function readByte(): ?string
    {
        return $this->readByteFrom($this->socket);
    }

    private function readByteFrom($socket): ?string
    {
        $byte = fread($socket, 1);

        if ($byte === false || $byte === '') {
            return null;
        }

        return $byte;
    }

    private function readByteOrd(): int
    {
        return $this->readByteOrdFrom($this->socket);
    }

    private function readByteOrdFrom($socket): int
    {
        $byte = $this->readByteFrom($socket);

        return $byte === null ? 0 : ord($byte);
    }

    private function encodeLength(int $length): string
    {
        if ($length < 0x80) {
            return chr($length);
        } elseif ($length < 0x4000) {
            $length |= 0x8000;
            return chr(($length >> 8) & 0xFF) . chr($length & 0xFF);
        } elseif ($length < 0x200000) {
            $length |= 0xC00000;
            return chr(($length >> 16) & 0xFF) . chr(($length >> 8) & 0xFF) . chr($length & 0xFF);
        } else {
            $length |= 0xE0000000;
            return chr(($length >> 24) & 0xFF) . chr(($length >> 16) & 0xFF) . chr(($length >> 8) & 0xFF) . chr($length & 0xFF);
        }
    }

    public function comm(string $command, array $args = []): array
    {
        $this->write($command, false);

        foreach ($args as $key => $value) {
            if ($key !== '' && $key[0] === '?') {
                $this->write($key . '=' . $value, false);
            } else {
                $this->write('=' . $key . '=' . $value, false);
            }
        }

        $this->write('');

        return $this->parseRecords($this->read());
    }

    private function parseRecords(array $flat): array
    {
        $records = [];
        $current = null;

        foreach ($flat as $word) {
            if ($word === '!re' || $word === '!data') {
                if ($current !== null && !empty($current)) {
                    $records[] = $current;
                }
                $current = [];
                continue;
            }

            if (in_array($word, ['!done', '!trap', '!fatal', '!final'], true)) {
                if ($current !== null && !empty($current)) {
                    $records[] = $current;
                }
                $current = null;
                continue;
            }

            if ($current !== null && preg_match('/^=([^=]+)=(.*)$/s', $word, $m)) {
                $key = $m[1];
                $value = $m[2];

                if (array_key_exists($key, $current)) {
                    if (!is_array($current[$key])) {
                        $current[$key] = [$current[$key]];
                    }
                    $current[$key][] = $value;
                } else {
                    $current[$key] = $value;
                }
            }
        }

        if ($current !== null && !empty($current)) {
            $records[] = $current;
        }

        return $records;
    }
}
