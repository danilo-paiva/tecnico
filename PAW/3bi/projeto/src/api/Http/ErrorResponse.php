<?php

namespace Api\Http;

use Exception;

// Erro previsto da API: carrega o status HTTP e um detalhe opcional.
// O Server converte isso em JSON {success: false, message, error}.
class ErrorResponse extends Exception
{
    private int $httpCode;
    private $error;

    public function __construct(int $httpCode, string $message, $error = null)
    {
        parent::__construct($message);
        $this->httpCode = $httpCode;
        $this->error = $error;
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    public function getError()
    {
        return $this->error;
    }
}
