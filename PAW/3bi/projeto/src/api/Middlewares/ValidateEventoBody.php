<?php

declare(strict_types=1);

namespace Api\Middlewares;

use Api\Http\ErrorResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidateEventoBody implements MiddlewareInterface
{
    private const STATUS_PERMITIDOS = ['planejado', 'confirmado', 'cancelado', 'realizado', 'ativo', 'adiado', 'finalizado'];

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();
        if (!is_array($data)) {
            $raw = (string) $request->getBody();
            $decoded = json_decode($raw, true);
            $data = is_array($decoded) ? $decoded : [];
        }

        if (!isset($data['evento']) || !is_array($data['evento'])) {
            throw new ErrorResponse("Campo 'evento' é obrigatório no corpo da requisição.", 400);
        }

        $payload = $data['evento'];

        // titulo obrigatório
        if (!isset($payload['titulo']) || trim((string) $payload['titulo']) === '') {
            throw new ErrorResponse("Campo 'titulo' é obrigatório em 'evento'.", 400);
        }
        $titulo = trim((string) $payload['titulo']);
        if (mb_strlen($titulo) < 3) {
            throw new ErrorResponse("Título deve ter pelo menos 3 caracteres.", 400);
        }
        if (mb_strlen($titulo) > 200) {
            throw new ErrorResponse("Título deve ter no máximo 200 caracteres.", 400);
        }

        // dataEvento obrigatório
        if (!isset($payload['dataEvento']) || trim((string) $payload['dataEvento']) === '') {
            throw new ErrorResponse("Campo 'dataEvento' é obrigatório em 'evento'.", 400);
        }
        $dataEvento = trim((string) $payload['dataEvento']);
        $formatos = ['Y-m-d H:i:s', 'Y-m-d'];
        $valido = false;
        foreach ($formatos as $formato) {
            $dt = \DateTime::createFromFormat($formato, $dataEvento);
            if ($dt && $dt->format($formato) === $dataEvento) {
                $valido = true;
                break;
            }
        }
        if (!$valido) {
            throw new ErrorResponse("Data do evento inválida. Use o formato YYYY-MM-DD ou YYYY-MM-DD HH:MM:SS.", 400);
        }

        // idLocal obrigatório
        if (!isset($payload['idLocal']) || $payload['idLocal'] === '' || $payload['idLocal'] === null) {
            throw new ErrorResponse("Campo 'idLocal' é obrigatório em 'evento'.", 400);
        }
        if (!is_numeric((string) $payload['idLocal']) || (int) $payload['idLocal'] <= 0) {
            throw new ErrorResponse("ID do local deve ser um número inteiro maior que zero.", 400);
        }

        // status opcional
        if (isset($payload['status']) && $payload['status'] !== '' && $payload['status'] !== null) {
            $status = mb_strtolower(trim((string) $payload['status']));
            if (!in_array($status, self::STATUS_PERMITIDOS, true)) {
                throw new ErrorResponse("Status inválido. Valores permitidos: " . implode(', ', self::STATUS_PERMITIDOS) . ".", 400);
            }
        }

        // descricao opcional
        if (array_key_exists('descricao', $payload) && $payload['descricao'] !== null && trim((string) $payload['descricao']) !== '') {
            $descricao = trim((string) $payload['descricao']);
            if (mb_strlen($descricao) > 2000) {
                throw new ErrorResponse("Descrição deve ter no máximo 2000 caracteres.", 400);
            }
        }

        $request = $request->withParsedBody($payload);

        return $handler->handle($request);
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->process($request, $handler);
    }
}
