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

    public function connect(string $ip, string $login, string $password): bool
    {
        for ($attempt = 1; $attempt <= $this->attempts; $attempt++) {
            $this->connected = false;

            $this->socket = @fsockopen($ip, $this->port, $this->errorNo, $this->errorStr, $this->timeout);

            if (!$this->socket) {
                usleep(500000);
                continue;
            }

            socket_set_timeout($this->socket, $this->timeout);

            $this->write('/login');
            $response = $this->read();

            if (!isset($response[0]) || $response[0] !== '!done') {
                $this->disconnect();
                continue;
            }

            if (isset($response[1]) && preg_match('/[^=]+/i', $response[1], $matches)) {
                if ($matches[0][0] === 'ret' && strlen($matches[0][1]) === 32) {
                    $this->write('/login', false);
                    $this->write('=name=' . $login, false);
                    $this->write('=response=00' . md5(chr(0) . $password . pack('H*', $matches[0][1])));
                    $this->write('');

                    $loginResponse = $this->read();

                    if (!isset($loginResponse[0]) || $loginResponse[0] !== '!done') {
                        $this->disconnect();
                        continue;
                    }
                } else {
                    $this->write('=name=' . $login, false);
                    $this->write('=password=' . $password);
                    $loginResponse = $this->read();

                    if (!isset($loginResponse[0]) || $loginResponse[0] !== '!done') {
                        $this->disconnect();
                        continue;
                    }
                }
            }

            $this->connected = true;
            return true;
        }

        return false;
    }

    public function disconnect(): void
    {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }
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
        $response = [];
        $buffer = '';

        while (true) {
            $this->readResponse($buffer);

            if (strlen($buffer) === 0) {
                break;
            }

            if ($buffer[0] === '!trap' || $buffer[0] === '!done') {
                if (isset($buffer[1])) {
                    $response[] = $buffer[1];
                }
                break;
            }

            if ($buffer[0] === '=') {
                $response[] = $buffer;
            }
        }

        return $response;
    }

    private function readResponse(&$buffer): void
    {
        $length = ord(fread($this->socket, 1));
        $buffer = '';

        if (($length & 0x80) === 0) {
            $buffer = fread($this->socket, $length);
        } elseif (($length & 0xC0) === 0x80) {
            $length = (($length & 0x3F) << 8) | ord(fread($this->socket, 1));
            $buffer = fread($this->socket, $length);
        } elseif (($length & 0xE0) === 0xC0) {
            $length = (($length & 0x1F) << 16) | (ord(fread($this->socket, 1)) << 8) | ord(fread($this->socket, 1));
            $buffer = fread($this->socket, $length);
        } elseif (($length & 0xF0) === 0xE0) {
            $length = (($length & 0x0F) << 24) | (ord(fread($this->socket, 1)) << 16) | (ord(fread($this->socket, 1)) << 8) | ord(fread($this->socket, 1));
            $buffer = fread($this->socket, $length);
        }

        $buffer = explode("\n", $buffer);
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
            if (is_array($value)) {
                $this->write($key . '=' . $value[0], false);
                $this->write($key . '=' . $value[1]);
            } else {
                $this->write('=' . $key . '=' . $value, empty($args));
            }
        }

        if (empty($args)) {
            $this->write('');
        }

        return $this->read();
    }
}
