<?php
namespace Api\DAO;

use Api\Models\Funcionario;
use Api\Models\Cargo;
use Api\Database\MysqlDatabase;
use Exception;

class FuncionarioDAO
{
    private MysqlDatabase $database;

    public function __construct(MysqlDatabase $databaseInstance)
    {
        $this->database = $databaseInstance;
    }

    public function create(Funcionario $funcionario): int
    {
        $senhaHash = password_hash($funcionario->getSenha(), PASSWORD_BCRYPT, ['cost' => 12]);
        $sql = "INSERT INTO funcionarios (nomeFuncionario, email, senha, recebeValeTransporte, idCargo) VALUES (?, ?, ?, ?, ?)";
        $params = [
            $funcionario->getNomeFuncionario(),
            $funcionario->getEmail(),
            $senhaHash,
            $funcionario->getRecebeValeTransporte(),
            $funcionario->getCargo()->getIdCargo(),
        ];
        $pdo = $this->database->getConnection();
        $stmt = $pdo->prepare($sql);
        if (!$stmt->execute($params)) {
            error_log("Falha crítica ao inserir funcionário no banco: " . $funcionario->getEmail());
            throw new Exception("Erro interno ao processar cadastro de funcionário.");
        }
        $insertId = $pdo->lastInsertId();
        if (!$insertId) throw new Exception("Falha ao recuperar ID do funcionário inserido");
        return (int) $insertId;
    }

    /**
     * Remove um funcionário do banco.
     */
    public function delete(Funcionario $funcionario): bool
    {
        $sql = "DELETE FROM funcionarios WHERE idFuncionario = ?";
        $pdo = $this->database->getConnection();
        $stmt = $pdo->prepare($sql);
        if (!$stmt->execute([$funcionario->getIdFuncionario()])) {
            error_log("Erro ao deletar funcionário ID: " . $funcionario->getIdFuncionario());
            return false;
        }
        return $stmt->rowCount() > 0;
    }

    /**
     * Atualiza dados do funcionário. Trata a senha separadamente para evitar sobrescrever com vazio.
     */
    public function update(Funcionario $funcionario): bool
    {
        $pdo = $this->database->getConnection();
        if (!empty($funcionario->getSenha())) {
            $senhaHash = password_hash($funcionario->getSenha(), PASSWORD_BCRYPT, ['cost' => 12]);
            $sql = "UPDATE funcionarios SET nomeFuncionario=?, email=?, senha=?, recebeValeTransporte=?, idCargo=? WHERE idFuncionario=?";
            $params = [
                $funcionario->getNomeFuncionario(),
                $funcionario->getEmail(),
                $senhaHash,
                $funcionario->getRecebeValeTransporte(),
                $funcionario->getCargo()->getIdCargo(),
                $funcionario->getIdFuncionario(),
            ];
        } else {
            $sql = "UPDATE funcionarios SET nomeFuncionario=?, email=?, recebeValeTransporte=?, idCargo=? WHERE idFuncionario=?";
            $params = [
                $funcionario->getNomeFuncionario(),
                $funcionario->getEmail(),
                $funcionario->getRecebeValeTransporte(),
                $funcionario->getCargo()->getIdCargo(),
                $funcionario->getIdFuncionario(),
            ];
        }
        $stmt = $pdo->prepare($sql);
        if (!$stmt->execute($params)) {
            error_log("Erro ao atualizar funcionário ID: " . $funcionario->getIdFuncionario());
            return false;
        }
        return $stmt->rowCount() > 0;
    }

    public function findAll(): array
    {
        $sql = "SELECT idFuncionario, nomeFuncionario, email, recebeValeTransporte, funcionarios.idCargo, cargos.nomeCargo, cargos.idDepartamento FROM funcionarios JOIN cargos ON funcionarios.idCargo = cargos.idCargo";
        $pdo = $this->database->getConnection();
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $row) {
            $cargo = new Cargo();
            $cargo->setIdCargo((int) $row['idCargo']);
            $cargo->setNomeCargo($row['nomeCargo']);
            $cargo->setIdDepartamento((int) $row['idDepartamento']);
            $funcionario = new Funcionario();
            $funcionario->setIdFuncionario((int) $row['idFuncionario']);
            $funcionario->setNomeFuncionario($row['nomeFuncionario']);
            $funcionario->setEmail($row['email']);
            $funcionario->setRecebeValeTransporte((int) $row['recebeValeTransporte']);
            $funcionario->setCargo($cargo);
            $result[] = $funcionario;
        }
        return $result;
    }

    public function count(): int
    {
        $sql = "SELECT COUNT(*) AS qtd FROM funcionarios";
        $pdo = $this->database->getConnection();
        $stmt = $pdo->query($sql);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int) $row['qtd'];
    }

    public function findById(int $idFuncionario): ?Funcionario
    {
        $result = $this->findByField('idFuncionario', $idFuncionario);
        return $result[0] ?? null;
    }

    public function findByField(string $field, $value): array
    {
        $allowedFields = ['idFuncionario', 'nomeFuncionario', 'email', 'senha', 'recebeValeTransporte', 'idCargo'];
        if (!in_array($field, $allowedFields)) throw new Exception("Campo inválido para busca");
        $sql = "SELECT funcionarios.*, cargos.nomeCargo, cargos.idDepartamento FROM funcionarios JOIN cargos ON funcionarios.idCargo = cargos.idCargo WHERE funcionarios.$field = ?";
        $pdo = $this->database->getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$value]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $row) {
            $cargo = new Cargo();
            $cargo->setIdCargo((int) $row['idCargo']);
            $cargo->setNomeCargo($row['nomeCargo']);
            $cargo->setIdDepartamento((int) $row['idDepartamento']);
            $funcionario = new Funcionario();
            $funcionario->setIdFuncionario((int) $row['idFuncionario']);
            $funcionario->setNomeFuncionario($row['nomeFuncionario']);
            $funcionario->setEmail($row['email']);
            $funcionario->setRecebeValeTransporte((int) $row['recebeValeTransporte']);
            $funcionario->setCargo($cargo);
            $result[] = $funcionario;
        }
        return $result;
    }

    public function login(Funcionario $funcionario): ?Funcionario
    {
        $sql = "SELECT idFuncionario, nomeFuncionario, email, senha, recebeValeTransporte, funcionarios.idCargo, cargos.nomeCargo, cargos.idDepartamento FROM funcionarios JOIN cargos ON funcionarios.idCargo = cargos.idCargo WHERE email = ?";
        $pdo = $this->database->getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$funcionario->getEmail()]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row || !password_verify($funcionario->getSenha(), $row['senha'])) return null;
        $cargo = new Cargo();
        $cargo->setIdCargo((int) $row['idCargo']);
        $cargo->setNomeCargo($row['nomeCargo']);
        $cargo->setIdDepartamento((int) $row['idDepartamento']);
        $func = new Funcionario();
        $func->setIdFuncionario((int) $row['idFuncionario']);
        $func->setNomeFuncionario($row['nomeFuncionario']);
        $func->setEmail($row['email']);
        $func->setRecebeValeTransporte((int) $row['recebeValeTransporte']);
        $func->setCargo($cargo);
        return $func;
    }
}
