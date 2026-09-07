<?php

declare(strict_types=1);

namespace Api\Services;

use Api\Dao\CompraDAO;
use Api\Dao\IngressoDAO;
use Api\Dao\ParticipanteDAO;
use Api\Database\MysqlDatabase;
use Api\Http\ErrorResponse;
use Api\Models\Compra;
use InvalidArgumentException;
use PDO;
use Throwable;

class CompraService
{
    private CompraDAO $compraDAO;
    private ParticipanteDAO $participanteDAO;
    private IngressoDAO $ingressoDAO;
    private MysqlDatabase $db;

    public function __construct(
        CompraDAO $compraDAO,
        ParticipanteDAO $participanteDAO,
        IngressoDAO $ingressoDAO,
        MysqlDatabase $db
    ) {
        $this->compraDAO = $compraDAO;
        $this->participanteDAO = $participanteDAO;
        $this->ingressoDAO = $ingressoDAO;
        $this->db = $db;
    }

    private function getPdo(): PDO
    {
        return $this->db->getConnection();
    }

    /**
     * @return Compra[]
     */
    public function findAll(): array
    {
        return $this->compraDAO->getAll();
    }

    public function findById(int $id): Compra
    {
        if ($id <= 0) {
            throw new ErrorResponse('ID da compra inválido.', 400);
        }
        $compra = $this->compraDAO->getById($id);
        if ($compra === null) {
            throw new ErrorResponse('Compra não encontrada.', 404);
        }
        return $compra;
    }

    public function create(array $data): Compra
    {
        // Validacao basica de quantidade antes de modelo
        if (!isset($data['quantidade']) || (int) $data['quantidade'] <= 0) {
            throw new ErrorResponse('Quantidade deve ser maior que zero.', 400);
        }

        $idParticipante = (int) ($data['idParticipante'] ?? 0);
        $idIngresso = (int) ($data['idIngresso'] ?? 0);

        $this->assertParticipanteExiste($idParticipante);
        $ingresso = $this->assertIngressoExiste($idIngresso);

        $quantidade = (int) $data['quantidade'];

        if ($ingresso->getQuantidadeDisponivel() < $quantidade) {
            throw new ErrorResponse('Estoque insuficiente para a quantidade solicitada.', 409);
        }

        $valorTotal = round($quantidade * $ingresso->getPreco(), 2);
        $data['valorTotal'] = $valorTotal;

        if (empty($data['dataCompra'])) {
            $data['dataCompra'] = date('Y-m-d H:i:s');
        }

        try {
            $compra = Compra::fromArray($data);
        } catch (InvalidArgumentException $e) {
            throw new ErrorResponse($e->getMessage(), 400);
        }

        // Garante valor calculado
        $compra->setValorTotal($valorTotal);

        $pdo = $this->getPdo();
        try {
            $pdo->beginTransaction();

            // Decrementa estoque com lock implicito na transacao
            $this->decrementarEstoque($idIngresso, $quantidade);

            $id = $this->compraDAO->create($compra);

            $pdo->commit();

            $criada = $this->compraDAO->getById($id);
            if ($criada === null) {
                throw new ErrorResponse('Falha ao criar compra.', 400);
            }
            return $criada;
        } catch (ErrorResponse $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw new ErrorResponse('Erro ao processar compra: ' . $e->getMessage(), 400);
        }
    }

    public function update(int $id, array $data): Compra
    {
        $existente = $this->findById($id);

        $oldQuantidade = $existente->getQuantidade();
        $oldIdIngresso = $existente->getIdIngresso();

        $merged = array_merge($existente->toArray(), $data);
        $merged['idCompra'] = $id;

        $newQuantidade = (int) ($merged['quantidade'] ?? $oldQuantidade);
        $newIdIngresso = (int) ($merged['idParticipante'] ?? $existente->getIdParticipante()); // placeholder, corrigido abaixo
        $newIdIngresso = (int) ($merged['idIngresso'] ?? $oldIdIngresso);
        $newIdParticipante = (int) ($merged['idParticipante'] ?? $existente->getIdParticipante());

        if ($newQuantidade <= 0) {
            throw new ErrorResponse('Quantidade deve ser maior que zero.', 400);
        }

        $this->assertParticipanteExiste($newIdParticipante);
        $novoIngresso = $this->assertIngressoExiste($newIdIngresso);

        // Calcula novo valor_total
        $novoValorTotal = round($newQuantidade * $novoIngresso->getPreco(), 2);
        $merged['valorTotal'] = $novoValorTotal;
        $merged['quantidade'] = $newQuantidade;
        $merged['idIngresso'] = $newIdIngresso;
        $merged['idParticipante'] = $newIdParticipante;

        try {
            $compra = Compra::fromArray($merged);
        } catch (InvalidArgumentException $e) {
            throw new ErrorResponse($e->getMessage(), 400);
        }
        $compra->setValorTotal($novoValorTotal);

        $pdo = $this->getPdo();
        try {
            $pdo->beginTransaction();

            if ($newIdIngresso === $oldIdIngresso) {
                // Mesmo ingresso: ajusta diferenca
                $diff = $newQuantidade - $oldQuantidade;
                if ($diff > 0) {
                    if ($novoIngresso->getQuantidadeDisponivel() < $diff) {
                        throw new ErrorResponse('Estoque insuficiente para aumentar a quantidade.', 409);
                    }
                    $this->decrementarEstoque($newIdIngresso, $diff);
                } elseif ($diff < 0) {
                    $this->incrementarEstoque($newIdIngresso, abs($diff));
                }
            } else {
                // Ingresso trocado: devolve no antigo, decrementa no novo
                $antigoIngresso = $this->assertIngressoExiste($oldIdIngresso);
                // Devolve
                $this->incrementarEstoque($oldIdIngresso, $oldQuantidade);
                // Verifica estoque do novo
                // Recarrega apos possivel alteracao concorrente
                $novoAtual = $this->ingressoDAO->getById($newIdIngresso);
                if ($novoAtual === null) {
                    throw new ErrorResponse('Ingresso informado não existe.', 404);
                }
                if ($novoAtual->getQuantidadeDisponivel() < $newQuantidade) {
                    throw new ErrorResponse('Estoque insuficiente no novo ingresso.', 409);
                }
                $this->decrementarEstoque($newIdIngresso, $newQuantidade);
            }

            $this->compraDAO->update($compra);

            $pdo->commit();
            return $this->findById($id);
        } catch (ErrorResponse $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw new ErrorResponse('Erro ao atualizar compra: ' . $e->getMessage(), 400);
        }
    }

    public function delete(int $id): void
    {
        $existente = $this->findById($id);
        $pdo = $this->getPdo();
        try {
            $pdo->beginTransaction();

            $this->incrementarEstoque($existente->getIdIngresso(), $existente->getQuantidade());

            $ok = $this->compraDAO->delete($id);
            if (!$ok) {
                throw new ErrorResponse('Falha ao deletar compra.', 400);
            }

            $pdo->commit();
        } catch (ErrorResponse $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw new ErrorResponse('Erro ao deletar compra: ' . $e->getMessage(), 400);
        }
    }

    public function count(): int
    {
        return $this->compraDAO->count();
    }

    /**
     * @return Compra[]
     */
    public function getByParticipante(int $idParticipante): array
    {
        if ($idParticipante <= 0) {
            throw new ErrorResponse('ID do participante inválido.', 400);
        }
        $this->assertParticipanteExiste($idParticipante);
        return $this->compraDAO->getByParticipante($idParticipante);
    }

    /**
     * @return Compra[]
     */
    public function getByIngresso(int $idIngresso): array
    {
        if ($idIngresso <= 0) {
            throw new ErrorResponse('ID do ingresso inválido.', 400);
        }
        $this->assertIngressoExiste($idIngresso);
        return $this->compraDAO->getByIngresso($idIngresso);
    }

    private function assertParticipanteExiste(int $idParticipante): void
    {
        if ($idParticipante <= 0) {
            throw new ErrorResponse('ID do participante é obrigatório.', 400);
        }
        $p = $this->participanteDAO->getById($idParticipante);
        if ($p === null) {
            throw new ErrorResponse('Participante informado não existe.', 404);
        }
    }

    private function assertIngressoExiste(int $idIngresso): \Api\Models\Ingresso
    {
        if ($idIngresso <= 0) {
            throw new ErrorResponse('ID do ingresso é obrigatório.', 400);
        }
        $ingresso = $this->ingressoDAO->getById($idIngresso);
        if ($ingresso === null) {
            throw new ErrorResponse('Ingresso informado não existe.', 404);
        }
        return $ingresso;
    }

    private function decrementarEstoque(int $idIngresso, int $quantidade): void
    {
        $pdo = $this->getPdo();
        $sql = 'UPDATE ingressos SET quantidade_disponivel = quantidade_disponivel - :qtd WHERE id_ingresso = :id AND quantidade_disponivel >= :qtd2';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':qtd' => $quantidade, ':id' => $idIngresso, ':qtd2' => $quantidade]);
        if ($stmt->rowCount() === 0) {
            throw new ErrorResponse('Estoque insuficiente ou ingresso não encontrado ao decrementar.', 409);
        }
    }

    private function incrementarEstoque(int $idIngresso, int $quantidade): void
    {
        $pdo = $this->getPdo();
        // Garante que nao ultrapassa quantidade_total
        $sql = 'UPDATE ingressos SET quantidade_disponivel = LEAST(quantidade_total, quantidade_disponivel + :qtd) WHERE id_ingresso = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':qtd' => $quantidade, ':id' => $idIngresso]);
        if ($stmt->rowCount() === 0) {
            throw new ErrorResponse('Falha ao devolver estoque do ingresso.', 400);
        }
    }
}
