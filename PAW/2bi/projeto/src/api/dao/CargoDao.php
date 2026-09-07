<?php

namespace Api\DAO;

use Api\Models\Cargo;
use Api\Database\MysqlDatabase;
use Exception;

/**
 * Classe responsável pelo acesso aos dados da entidade Cargo.
 *
 * Camadas:
 * Controller -> Service -> DAO -> Banco de Dados
 *
 * Objetivo:
 * Centralizar todas as operações SQL relacionadas à tabela cargo.
 */
class CargoDAO
{
    /**
     * Instância de conexão com banco de dados.
     *
     * @var MysqlDatabase
     */
    private MysqlDatabase $database;

    /**
     * Recebe a conexão via injeção de dependência.
     *
     * @param MysqlDatabase $databaseInstance
     */
    public function __construct(MysqlDatabase $databaseInstance)
    {
        $this->database = $databaseInstance;

        error_log("CargoDAO::__construct()");
    }

    /**
     * Insere um novo cargo no banco.
     *
     * @param Cargo $objCargo
     * @return Cargo gerado
     * @throws Exception
     */
    public function create(Cargo $objCargo): Cargo
    {
        error_log("CargoDAO::create()");

        /**
         * SQL de inserção.
         */
        $sql = "
            INSERT INTO cargos (nomeCargo, idDepartamento)
            VALUES (:nomeCargo, :idDepartamento)
        ";

        /**
         * Valores da query.
         */
        $parametros = [
            ':nomeCargo' => $objCargo->getNomeCargo(),
            ':idDepartamento' => $objCargo->getIdDepartamento()
        ];

        /**
         * Prepara e executa.
         */
        $stmt = $this->database->getConnection()->prepare($sql);

        if (!$stmt->execute($parametros)) {
            throw new Exception("Erro ao cadastrar cargo.");
        }

        /**
         * Retorna ID criado.
         */
        $novoID = (int) $this->database->getConnection()->lastInsertId();
        $objCargo->setIdCargo($novoID);
        return $objCargo;
    }

    /**
     * Remove um cargo pelo ID.
     *
     * @param Cargo $objCargoModel
     * @return bool
     */
    public function delete(Cargo $objCargoModel): bool
    {
        error_log("CargoDAO::delete()");

        /**
         * SQL de exclusão.
         */
        $sql = "
            DELETE FROM cargos
            WHERE idCargo = :idCargo
        ";

        /**
         * Valores da query.
         */
        $parametros = [
            ':idCargo' => $objCargoModel->getIdCargo()
        ];

        /**
         * Executa exclusão.
         */
        $stmt = $this->database->getConnection()->prepare($sql);
        $stmt->execute($parametros);

        /**
         * True se removeu registro.
         */
        return $stmt->rowCount() > 0;
    }

    /**
     * Atualiza um cargo existente.
     *
     * @param Cargo $objCargoModel
     * @return bool
     */
    public function update(Cargo $objCargoModel): bool
    {
        error_log("CargoDAO::update()");

        /**
         * SQL de atualização.
         */
        $sql = "
            UPDATE cargos
            SET nomeCargo = :nomeCargo, idDepartamento = :idDepartamento
            WHERE idCargo = :idCargo
        ";

        /**
         * Valores da query.
         */
        $parametros = [
            ':nomeCargo' => $objCargoModel->getNomeCargo(),
            ':idDepartamento' => $objCargoModel->getIdDepartamento(),
            ':idCargo' => $objCargoModel->getIdCargo()
        ];

        /**
         * Executa atualização.
         */
        $stmt = $this->database->getConnection()->prepare($sql);
        $stmt->execute($parametros);

        /**
         * True se alterou registro.
         */
        return $stmt->rowCount() > 0;
    }

    /**
     * Retorna todos os cargos cadastrados.
     *
     * @return array
     */
    public function findAll(): array
    {
        error_log("CargoDAO::findAll()");

        /**
         * Consulta todos os registros.
         */
        $sql = "SELECT * FROM cargos";

        /**
         * Executa consulta.
         */
        $stmt = $this->database->getConnection()->query($sql);

        /**
         * Matriz de arrays.
         */
        $matrizArrays = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        /**
         * Lista final de objetos Cargo.
         */
        $cargos = [];

        /**
         * Converte cada linha em objeto Cargo.
         */
        foreach ($matrizArrays as $linhaMatriz) {
            $cargo = new Cargo();

            $cargo->setIdCargo((int) $linhaMatriz['idCargo']);
            $cargo->setNomeCargo($linhaMatriz['nomeCargo']);
            $cargo->setIdDepartamento((int) $linhaMatriz['idDepartamento']);

            $cargos[] = $cargo;
        }

        /**
         * Retorna lista pronta.
         */
        return $cargos;
    }

    /**
     * Retorna total de cargos cadastrados.
     *
     * @return int
     */
    public function count(): int
    {
        error_log("CargoDAO::count()");

        /**
         * SQL de contagem.
         */
        $sql = "SELECT COUNT(*) AS qtd FROM cargos";

        /**
         * Executa consulta.
         */
        $stmt = $this->database->getConnection()->query($sql);

        /**
         * Resultado único.
         */
        $linhaMatriz = $stmt->fetch(\PDO::FETCH_ASSOC);

        /**
         * Retorna total.
         */
        return (int) $linhaMatriz['qtd'];
    }

    /**
     * Busca cargo pelo ID.
     *
     * @param int $idCargo
     * @return Cargo|null
     */
    public function findById(int $idCargo): ?Cargo
    {
        error_log("CargoDAO::findById()");

        /**
         * Busca reutilizando método genérico.
         */
        $resultado = $this->findByField('idCargo', $idCargo);

        /**
         * Se encontrou registro.
         */
        if (!empty($resultado)) {
            return $resultado[0];
        }

        /**
         * Não encontrado.
         */
        return null;
    }

    /**
     * Busca por campo específico.
     *
     * @param string $field
     * @param mixed $value
     * @return array
     * @throws Exception
     */
    public function findByField(string $field, $value): array
    {
        error_log("CargoDAO::findByField()");

        /**
         * Campos permitidos.
         */
        $camposPermitidos = [
            'idCargo',
            'nomeCargo'
        ];

        /**
         * Valida campo informado.
         */
        if (!in_array($field, $camposPermitidos)) {
            throw new Exception("Campo inválido.");
        }

        /**
         * SQL dinâmica segura.
         */
        $sql = "SELECT * FROM cargos WHERE $field = :value";

        /**
         * Prepara consulta.
         */
        $stmt = $this->database->getConnection()->prepare($sql);

        /**
         * Executa busca.
         */
        $stmt->execute([
            ':value' => $value
        ]);

        /**
         * Matriz retornada.
         */
        $matrizArrays = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        /**
         * Lista final de objetos Cargo.
         */
        $cargos = [];

        /**
         * Converte linhas em objetos.
         */
        foreach ($matrizArrays as $linhaMatriz) {
            $cargo = new Cargo();

            $cargo->setIdCargo((int) $linhaMatriz['idCargo']);
            $cargo->setNomeCargo($linhaMatriz['nomeCargo']);
            $cargo->setIdDepartamento((int) $linhaMatriz['idDepartamento']);

            $cargos[] = $cargo;
        }

        /**
         * Retorna lista.
         */
        return $cargos;
    }
}