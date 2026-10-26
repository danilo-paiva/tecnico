-- =====================================================================
-- PAW 4º Bimestre - Projeto Integrador
-- Banco: paw4bi_eventos  (MySQL 8 / MariaDB)
--
-- Tema: gestão de eventos (mesmo contexto do projeto do 3º bimestre,
-- com um conjunto novo de 3 tabelas relacionadas):
--   organizadores 1 --- N eventos 1 --- N lotes
--
-- Todas as senhas do seed são 123456 (hash bcrypt).
-- Importe com:  mysql -u root -p < docs/banco.sql
-- =====================================================================

DROP DATABASE IF EXISTS paw4bi_eventos;
CREATE DATABASE paw4bi_eventos
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE paw4bi_eventos;

-- ---------------------------------------------------------------------
-- Tabela 1: organizadores (também é a tabela de autenticação da API)
-- ---------------------------------------------------------------------
CREATE TABLE organizadores (
  id_organizador INT          NOT NULL AUTO_INCREMENT,
  nome           VARCHAR(120) NOT NULL,
  email          VARCHAR(150) NOT NULL,
  senha          VARCHAR(255) NOT NULL,
  telefone       VARCHAR(20)  DEFAULT NULL,
  perfil         ENUM('administrador','comum') NOT NULL DEFAULT 'comum',
  created_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_organizador),
  UNIQUE KEY uq_organizador_email (email)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabela 2: eventos (N:1 com organizadores)
-- ---------------------------------------------------------------------
CREATE TABLE eventos (
  id_evento      INT          NOT NULL AUTO_INCREMENT,
  titulo         VARCHAR(150) NOT NULL,
  descricao      TEXT         DEFAULT NULL,
  data_evento    DATETIME     NOT NULL,
  status         ENUM('planejado','confirmado','realizado','cancelado')
                              NOT NULL DEFAULT 'planejado',
  id_organizador INT          NOT NULL,
  created_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_evento),
  UNIQUE KEY uq_evento_titulo_data (titulo, data_evento),
  KEY idx_evento_organizador (id_organizador),
  CONSTRAINT fk_evento_organizador
    FOREIGN KEY (id_organizador) REFERENCES organizadores (id_organizador)
    ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabela 3: lotes de ingresso (N:1 com eventos)
-- ---------------------------------------------------------------------
CREATE TABLE lotes (
  id_lote                INT           NOT NULL AUTO_INCREMENT,
  nome                   VARCHAR(80)   NOT NULL,
  preco                  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  quantidade_total       INT           NOT NULL,
  quantidade_disponivel  INT           NOT NULL,
  id_evento              INT           NOT NULL,
  created_at             TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_lote),
  UNIQUE KEY uq_lote_nome_evento (nome, id_evento),
  KEY idx_lote_evento (id_evento),
  CONSTRAINT fk_lote_evento
    FOREIGN KEY (id_evento) REFERENCES eventos (id_evento)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT ck_lote_preco CHECK (preco >= 0),
  CONSTRAINT ck_lote_quantidade_total CHECK (quantidade_total > 0),
  CONSTRAINT ck_lote_disponivel CHECK (quantidade_disponivel >= 0),
  CONSTRAINT ck_lote_disponivel_total CHECK (quantidade_disponivel <= quantidade_total)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Seed
-- Senha de todos: 123456
--   ana@email.com     -> administrador
--   bruno@email.com   -> comum
--   carla@email.com   -> comum
-- ---------------------------------------------------------------------
INSERT INTO organizadores (nome, email, senha, telefone, perfil) VALUES
('Ana Souza',   'ana@email.com',   '$2b$10$YaOJUUmkr1QHd9KxMz9yS.R.7E795o12ro5rsYuTB/J.OyPhCV.Y.', '(47) 99911-2233', 'administrador'),
('Bruno Lima',  'bruno@email.com', '$2b$10$YaOJUUmkr1QHd9KxMz9yS.R.7E795o12ro5rsYuTB/J.OyPhCV.Y.', '(47) 98822-3344', 'comum'),
('Carla Mendes', 'carla@email.com', '$2b$10$YaOJUUmkr1QHd9KxMz9yS.R.7E795o12ro5rsYuTB/J.OyPhCV.Y.', '(47) 97733-4455', 'comum');

INSERT INTO eventos (titulo, descricao, data_evento, status, id_organizador) VALUES
('Semana de Tecnologia 2027', 'Palestras e workshops de PAW, BdD e POOI', '2027-03-15 08:00:00', 'confirmado', 1),
('Festival de Música Local',  'Shows de bandas regionais no palco principal', '2027-04-20 19:00:00', 'planejado', 2),
('Expo Inovação',             'Feira de startups e projetos estudantis', '2027-05-05 09:00:00', 'planejado', 1),
('Encontro de Startups',      'Rodada de negócios e mentorias', '2027-06-12 14:00:00', 'confirmado', 3);

INSERT INTO lotes (nome, preco, quantidade_total, quantidade_disponivel, id_evento) VALUES
('1º lote',       80.00, 200, 200, 1),
('2º lote',      120.00, 100,  96, 1),
('Pista',         90.00, 300, 280, 2),
('Camarote',     250.00,  50,  50, 2),
('Credenciamento', 0.00, 400, 400, 3),
('VIP',          150.00,  60,  58, 3),
('Lote único',    60.00, 150, 150, 4);
