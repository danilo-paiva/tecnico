CREATE DATABASE sistemaCondominio;
USE sistemaCondominio;


CREATE TABLE unidades(
    id_unidade INT PRIMARY KEY AUTO_INCREMENT,
    numero_apartamento VARCHAR(255) NOT NULL,
    status_unidade VARCHAR(255) NOT NULL
);

CREATE TABLE moradores(
    id_morador INT PRIMARY KEY AUTO_INCREMENT,
    nome_morador VARCHAR(255) NOT NULL,
    cpf_morador VARCHAR(14) NOT NULL UNIQUE,
    email_morador VARCHAR(255) NOT NULL
);

CREATE TABLE morador_unidade(
    id_morador_unidade INT PRIMARY KEY AUTO_INCREMENT,
    id_morador INT NOT NULL,
    id_unidade INT NOT NULL,
    morador_responsavel BOOL NOT NULL,
    vinculo_morador VARCHAR(255),
    data_inicio DATE NOT NULL,
    data_fim DATE,
    FOREIGN KEY(id_morador) REFERENCES moradores (id_morador),
    FOREIGN KEY(id_unidade) REFERENCES unidades (id_unidade)
);

CREATE TABLE funcionarios(
    id_funcionario INT PRIMARY KEY AUTO_INCREMENT,
    nome_funcionario VARCHAR(255) NOT NULL,
    cpf_funcionario VARCHAR(14) NOT NULL UNIQUE,
    funcao_funcionario VARCHAR(255) NOT NULL
);

CREATE TABLE ocorrencias(
    id_ocorrencia INT PRIMARY KEY AUTO_INCREMENT,
    id_unidade INT NOT NULL,
    id_funcionario INT NOT NULL,
    motivo_ocorrencia VARCHAR(255) NOT NULL,
    data_ocorrencia DATETIME,
    FOREIGN KEY(id_unidade) REFERENCES unidades (id_unidade),
    FOREIGN KEY(id_funcionario) REFERENCES funcionarios (id_funcionario)
);

CREATE TABLE locais(
    id_local INT PRIMARY KEY AUTO_INCREMENT,
    nome_local VARCHAR(255) NOT NULL
);

CREATE TABLE reservas(
    id_reserva INT PRIMARY KEY AUTO_INCREMENT,
    id_unidade INT NOT NULL,
    id_local INT NOT NULL,
    inicio_reserva DATETIME NOT NULL,
    fim_reserva DATETIME NOT NULL,
    FOREIGN KEY (id_unidade) REFERENCES unidades (id_unidade),
    FOREIGN KEY (id_local) REFERENCES locais (id_local)
);

CREATE TABLE cobrancas(
    id_pagamento INT PRIMARY KEY AUTO_INCREMENT,
    id_unidade INT NOT NULL,
    valor FLOAT NOT NULL,
    data_vencimento DATE NOT NULL,
    data_pagamento DATE,
    status_pagamento VARCHAR(255) NOT NULL,
    FOREIGN KEY (id_unidade) REFERENCES unidades (id_unidade)
);