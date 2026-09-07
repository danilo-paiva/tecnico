<?php

declare(strict_types=1);

namespace Api\Models;

use InvalidArgumentException;
use JsonSerializable;

/**
 * Local
 * Representa o espaço físico onde eventos são realizados.
 */
class Local implements JsonSerializable
{
    private ?int $idLocal;
    private string $nome;
    private string $endereco;
    private int $capacidade;

    public function __construct(?int $idLocal = null, string $nome = '', string $endereco = '', int $capacidade = 0)
    {
        $this->idLocal = null;
        $this->nome = '';
        $this->endereco = '';
        $this->capacidade = 0;

        if ($idLocal !== null) {
            $this->setIdLocal($idLocal);
        }
        if ($nome !== '') {
            $this->setNome($nome);
        }
        if ($endereco !== '') {
            $this->setEndereco($endereco);
        }
        if ($capacidade !== 0) {
            $this->setCapacidade($capacidade);
        }
    }

    public function getIdLocal(): ?int
    {
        return $this->idLocal;
    }

    public function setIdLocal(?int $idLocal): void
    {
        if ($idLocal !== null && $idLocal <= 0) {
            throw new InvalidArgumentException('ID do local deve ser um número positivo.');
        }
        $this->idLocal = $idLocal;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $nome): void
    {
        $nome = trim($nome);
        if ($nome === '') {
            throw new InvalidArgumentException('Nome do local é obrigatório.');
        }
        if (mb_strlen($nome) < 3) {
            throw new InvalidArgumentException('Nome do local deve ter pelo menos 3 caracteres.');
        }
        if (mb_strlen($nome) > 150) {
            throw new InvalidArgumentException('Nome do local deve ter no máximo 150 caracteres.');
        }
        $this->nome = $nome;
    }

    public function getEndereco(): string
    {
        return $this->endereco;
    }

    public function setEndereco(string $endereco): void
    {
        $endereco = trim($endereco);
        if ($endereco === '') {
            throw new InvalidArgumentException('Endereço do local é obrigatório.');
        }
        if (mb_strlen($endereco) < 5) {
            throw new InvalidArgumentException('Endereço deve ter pelo menos 5 caracteres.');
        }
        if (mb_strlen($endereco) > 255) {
            throw new InvalidArgumentException('Endereço deve ter no máximo 255 caracteres.');
        }
        $this->endereco = $endereco;
    }

    public function getCapacidade(): int
    {
        return $this->capacidade;
    }

    public function setCapacidade(int $capacidade): void
    {
        if ($capacidade <= 0) {
            throw new InvalidArgumentException('Capacidade deve ser maior que zero.');
        }
        if ($capacidade > 1000000) {
            throw new InvalidArgumentException('Capacidade excede o limite permitido.');
        }
        $this->capacidade = $capacidade;
    }

    /**
     * Cria instância a partir de array associativo (ex: linha do banco).
     */
    public static function fromArray(array $dados): self
    {
        $instancia = new self();
        if (isset($dados['idLocal']) && $dados['idLocal'] !== null && $dados['idLocal'] !== '') {
            $instancia->setIdLocal((int) $dados['idLocal']);
        }
        if (isset($dados['nome'])) {
            $instancia->setNome((string) $dados['nome']);
        }
        if (isset($dados['endereco'])) {
            $instancia->setEndereco((string) $dados['endereco']);
        }
        if (isset($dados['capacidade'])) {
            $instancia->setCapacidade((int) $dados['capacidade']);
        }
        return $instancia;
    }

    public function toArray(): array
    {
        return [
            'idLocal'    => $this->idLocal,
            'nome'       => $this->nome,
            'endereco'   => $this->endereco,
            'capacidade' => $this->capacidade,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
