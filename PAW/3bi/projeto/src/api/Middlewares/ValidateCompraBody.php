<?php

declare(strict_types=1);

namespace Api\Middlewares;

use Api\Http\ErrorResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidateCompraBody implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();
        if (!is_array($data)) {
            $raw = (string) $request->getBody();
            $decoded = json_decode($raw, true);
            $data = is_array($decoded) ? $decoded : [];
        }

        if (!isset($data['compra']) || !is_array($data['compra'])) {
            throw new ErrorResponse("Campo 'compra' é obrigatório no corpo da requisição.", 400);
        }

        $payload = $data['compra'];

        // quantidade obrigatório
        if (!isset($payload['quantidade']) || $payload['quantidade'] === '' || $payload['quantidade'] === null) {
            throw new ErrorResponse("Campo 'quantidade' é obrigatório em 'compra'.", 400);
        }
        if (!is_numeric((string) $payload['quantidade']) || (int) $payload['quantidade'] <= 0) {
            throw new ErrorResponse("Quantidade deve ser maior que zero.", 400);
        }
        if ((int) $payload['quantidade'] > 1000) {
            throw new ErrorResponse("Quantidade excede o limite permitido por compra.", 400);
        }

        // idParticipante obrigatório
        if (!isset($payload['idParticipante']) || $payload['idParticipante'] === '' || $payload['idParticipante'] === null) {
            throw new ErrorResponse("Campo 'idParticipante' é obrigatório em 'compra'.", 400);
        }
        if (!is_numeric((string) $payload['idParticipante']) || (int) $payload['idParticipante'] <= 0) {
            throw new ErrorResponse("ID do participante deve ser um número inteiro maior que zero.", 400);
        }

        // idIngresso obrigatório
        if (!isset($payload['idIngresso']) || $payload['idIngresso'] === '' || $payload['idIngresso'] === null) {
            throw new ErrorResponse("Campo 'idIngresso' é obrigatório em 'compra'.", 400);
        }
        if (!is_numeric((string) $payload['idIngresso']) || (int) $payload['idIngresso'] <= 0) {
            throw new ErrorResponse("ID do ingresso deve ser um número inteiro maior que zero.", 400);
        }

        // dataCompra opcional
        if (isset($payload['dataCompra']) && $payload['dataCompra'] !== '' && $payload['dataCompra'] !== null) {
            $dataCompra = trim((string) $payload['dataCompra']);
            $formatos = ['Y-m-d H:i:s', 'Y-m-d'];
            $valido = false;
            foreach ($formatos as $formato) {
                $dt = \DateTime::createFromFormat($formato, $dataCompra);
                if ($dt && $dt->format($formato) === $dataCompra) {
                    $valido = true;
                    break;
                }
            }
            if (!$valido) {
                throw new ErrorResponse("Data da compra inválida. Use o formato YYYY-MM-DD ou YYYY-MM-DD HH:MM:SS.", 400);
            }
            try {
                $dataObj = new \DateTime($dataCompra);
                $agora = new \DateTime();
                if ($dataObj > $agora) {
                    throw new ErrorResponse("Data da compra não pode ser no futuro.", 400);
                }
            } catch (ErrorResponse $e) {
                throw $e;
            } catch (\Throwable) {
                throw new ErrorResponse("Data da compra inválida.", 400);
            }
        }

        // valorTotal opcional - se informado validar
        if (isset($payload['valorTotal']) && $payload['valorTotal'] !== '' && $payload['valorTotal'] !== null) {
            if (!is_numeric((string) $payload['valorTotal']) || (float) $payload['valorTotal'] < 0) {
                throw new ErrorResponse("Valor total não pode ser negativo.", 400);
            }
            if ((float) $payload['valorTotal'] > 1000000) {
                throw new ErrorResponse("Valor total excede o limite permitido.", 400);
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
