-- Banco de Dados para Gestão de RH (Projeto 2º Bimestre)

CREATE DATABASE IF NOT EXISTS rh_db;
USE rh_db;

-- 1. Departamentos
CREATE TABLE IF NOT EXISTS departamentos (
    idDepartamento INT AUTO_INCREMENT PRIMARY KEY,
    nomeDepartamento VARCHAR(100) NOT NULL UNIQUE
) ENGINE = InnoDB;

-- 2. Cargos
CREATE TABLE IF NOT EXISTS cargos (
    idCargo INT AUTO_INCREMENT PRIMARY KEY,
    nomeCargo VARCHAR(100) NOT NULL UNIQUE,
    idDepartamento INT NOT NULL,
    CONSTRAINT fk_cargo_departamento FOREIGN KEY (idDepartamento) REFERENCES departamentos(idDepartamento) ON DELETE CASCADE
) ENGINE = InnoDB;

-- 3. Funcionários
CREATE TABLE IF NOT EXISTS funcionarios (
    idFuncionario INT AUTO_INCREMENT PRIMARY KEY,
    nomeFuncionario VARCHAR(150) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    recebeValeTransporte BOOLEAN DEFAULT FALSE,
    idCargo INT NOT NULL,
    CONSTRAINT fk_func_cargo FOREIGN KEY (idCargo) REFERENCES cargos(idCargo) ON DELETE RESTRICT
) ENGINE = InnoDB;

-- 4. Dependentes
CREATE TABLE IF NOT EXISTS dependentes (
    idDependente INT AUTO_INCREMENT PRIMARY KEY,
    nomeDependente VARCHAR(150) NOT NULL,
    parentesco VARCHAR(50),
    idFuncionario INT NOT NULL,
    CONSTRAINT fk_dep_func FOREIGN KEY (idFuncionario) REFERENCES funcionarios(idFuncionario) ON DELETE CASCADE
) ENGINE = InnoDB;

-- 5. Folha de Pagamento
CREATE TABLE IF NOT EXISTS folha_pagamento (
    idFolha INT AUTO_INCREMENT PRIMARY KEY,
    dataPagamento DATE NOT NULL,
    valorLiquido DECIMAL(10, 2) NOT NULL,
    idFuncionario INT NOT NULL,
    CONSTRAINT fk_folha_func FOREIGN KEY (idFuncionario) REFERENCES funcionarios(idFuncionario) ON DELETE CASCADE
) ENGINE = InnoDB;

-- Dados Iniciais para Testes
INSERT INTO departamentos (nomeDepartamento) VALUES ('Tecnologia'), ('Recursos Humanos'), ('Financeiro');
INSERT INTO cargos (nomeCargo, idDepartamento) VALUES ('Desenvolvedor', 1), ('Analista de RH', 2), ('Contador', 3);
INSERT INTO funcionarios (nomeFuncionario, email, senha, recebeValeTransporte, idCargo) VALUES ('João Silva', 'joao@email.com', '123456', 1, 1), ('Maria Santos', 'maria@email.com', '654321', 0, 2);
INSERT INTO dependentes (nomeDependente, parentesco, idFuncionario) VALUES ('Junior Silva', 'Filho', 1), ('Ana Santos', 'Cônjuge', 2);
INSERT INTO folha_pagamento (dataPagamento, valorLiquido, idFuncionario) VALUES ('2026-05-01', 5000.00, 1), ('2026-05-01', 4000.00, 2);
