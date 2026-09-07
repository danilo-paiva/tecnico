<?php

namespace Api\Http;

use Exception;

/**
 * ErrorResponse
 * Exceção controlada da API — carrega HTTP code + payload opcional.
 * O Server captura e transforma em JSON padronizado.
 */
class ErrorResponse extends Exception
{
    private int $httpCode;
    private mixed $details;

    public function __construct(string $message, int $httpCode = 400, mixed $details = null)
    {
        parent::__construct($message, $httpCode);
        $this->httpCode = $httpCode;
        $this->details  = $details;
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    public function getError(): mixed
    {
        return $this->details;
    }
}
