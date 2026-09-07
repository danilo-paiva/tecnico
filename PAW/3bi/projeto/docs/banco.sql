-- Banco de Dados: Gestão de Eventos & Ingressos
-- Projeto 2º BIM (base nova para 3º BIM) - Domínio DISTINTO do projeto RH do 2º bi
-- 5 tabelas relacionadas: locais, eventos, participantes, ingressos, compras

CREATE DATABASE IF NOT EXISTS eventos_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eventos_db;

-- 1. Locais (onde os eventos acontecem)
CREATE TABLE IF NOT EXISTS locais (
    id_local INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL UNIQUE,
    endereco VARCHAR(255) NOT NULL,
    capacidade INT NOT NULL CHECK (capacidade > 0),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Eventos (pertence a um Local)
CREATE TABLE IF NOT EXISTS eventos (
    id_evento INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descricao TEXT,
    data_evento DATETIME NOT NULL,
    status ENUM('planejado','confirmado','cancelado','realizado') NOT NULL DEFAULT 'planejado',
    id_local INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_evento_local FOREIGN KEY (id_local) REFERENCES locais(id_local) ON DELETE RESTRICT ON UPDATE CASCADE,
    UNIQUE KEY uq_evento_titulo_data (titulo, data_evento)
) ENGINE=InnoDB;

-- 3. Participantes (clientes que compram ingressos)
CREATE TABLE IF NOT EXISTS participantes (
    id_participante INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    telefone VARCHAR(20),
    senha VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. Ingressos (lotes por evento)
CREATE TABLE IF NOT EXISTS ingressos (
    id_ingresso INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(80) NOT NULL,
    preco DECIMAL(10,2) NOT NULL CHECK (preco >= 0),
    quantidade_total INT NOT NULL CHECK (quantidade_total > 0),
    quantidade_disponivel INT NOT NULL CHECK (quantidade_disponivel >= 0),
    id_evento INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ingresso_evento FOREIGN KEY (id_evento) REFERENCES eventos(id_evento) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uq_ingresso_tipo_evento (tipo, id_evento),
    CHECK (quantidade_disponivel <= quantidade_total)
) ENGINE=InnoDB;

-- 5. Compras (participante compra ingresso)
CREATE TABLE IF NOT EXISTS compras (
    id_compra INT AUTO_INCREMENT PRIMARY KEY,
    data_compra DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    quantidade INT NOT NULL CHECK (quantidade > 0),
    valor_total DECIMAL(10,2) NOT NULL CHECK (valor_total >= 0),
    id_participante INT NOT NULL,
    id_ingresso INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_compra_participante FOREIGN KEY (id_participante) REFERENCES participantes(id_participante) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_compra_ingresso FOREIGN KEY (id_ingresso) REFERENCES ingressos(id_ingresso) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Dados de exemplo para testes / apresentação
INSERT INTO locais (nome, endereco, capacidade) VALUES
('Centro de Convenções Helio', 'Av. Principal 1000, Centro', 500),
('Teatro Municipal', 'Rua das Artes 55, Centro', 300),
('Arena Tech', 'Rod. BR 101 km 12, Distrito Industrial', 1200);

INSERT INTO eventos (titulo, descricao, data_evento, status, id_local) VALUES
('Semana de Tecnologia 2026', 'Palestras e workshops de PAW e BdD', '2026-09-15 08:00:00', 'confirmado', 1),
('Festival de Música Local', 'Shows de bandas regionais', '2026-10-20 19:00:00', 'planejado', 2),
('Expo Inovação', 'Feira de startups e projetos estudantis', '2026-11-05 09:00:00', 'planejado', 3);

INSERT INTO participantes (nome, email, cpf, telefone, senha) VALUES
('Ana Souza', 'ana@email.com', '111.222.333-44', '(47) 99911-2233', '$2y$10$jGL72jRfPyBWaNZQ3LLOSe1GTXXE.Ez66f0L4ATfk3kkKP6axcNK6'), -- senha: Senha@123
('Bruno Lima', 'bruno@email.com', '222.333.444-55', '(47) 98822-3344', '$2y$10$RFGReC7hvd/4NGpKJkIoveJCzb70XkbLCA7jTmvgGGuUSBrs9A/K6'), -- senha: Senha@123
('Carla Mendes', 'carla@email.com', '333.444.555-66', '(47) 97733-4455', '$2y$10$og17MZA9awRmVV3qLJ6zcO4r.FdR2yF1sFHI5uwFXSE6ufPoIr7G.'); -- senha: Senha@123

INSERT INTO ingressos (tipo, preco, quantidade_total, quantidade_disponivel, id_evento) VALUES
('Inteira', 80.00, 200, 200, 1),
('Meia-entrada', 40.00, 100, 100, 1),
('Pista', 120.00, 300, 300, 2),
('VIP', 250.00, 50, 50, 3),
('Estudante', 30.00, 400, 400, 3);

INSERT INTO compras (data_compra, quantidade, valor_total, id_participante, id_ingresso) VALUES
('2026-08-20 10:00:00', 2, 160.00, 1, 1),
('2026-08-21 14:30:00', 1, 40.00, 2, 2),
('2026-08-22 09:15:00', 3, 360.00, 3, 3);
