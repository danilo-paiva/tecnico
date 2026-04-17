use sistemaCondominio;

INSERT INTO unidades (numero_apartamento, status_unidade) VALUES 
('101A', 'Ocupada'),
('102A', 'Ocupada'),
('201A', 'Ocupada'),
('202A', 'Vazia'),
('301A', 'Ocupada'),
('302A', 'Em Reforma'),
('101B', 'Ocupada'),
('102B', 'Ocupada'),
('201B', 'Ocupada'),
('202B', 'Vazia');

INSERT INTO moradores (nome_morador, cpf_morador, email_morador) VALUES 
('Carlos Silva', '111.222.333-44', 'carlos@email.com'),
('Ana Paula Souza', '222.333.444-55', 'ana@email.com'),
('Roberto Almeida', '333.444.555-66', 'roberto@email.com'),
('Fernanda Costa', '444.555.666-77', 'fernanda@email.com'),
('Lucas Pereira', '555.666.777-88', 'lucas@email.com'),
('Juliana Martins', '666.777.888-99', 'juliana@email.com'),
('Marcos Rocha', '777.888.999-00', 'marcos@email.com'),
('Beatriz Lima', '888.999.000-11', 'beatriz@email.com'),
('João Gomes', '999.000.111-22', 'joao@email.com'),
('Mariana Alves', '000.111.222-33', 'mariana@email.com');


INSERT INTO morador_unidade (id_morador, id_unidade, morador_responsavel, vinculo_morador, data_inicio, data_fim) VALUES 
(1, 4, TRUE, 'Inquilino', '2024-01-10', '2025-12-15'),  
(1, 1, TRUE, 'Proprietário', '2025-12-20', NULL),        
(2, 1, FALSE, 'Morador', '2025-12-20', NULL),            
(3, 2, TRUE, 'Proprietário', '2020-05-14', NULL),
(4, 3, TRUE, 'Inquilino', '2023-08-01', NULL),
(5, 5, TRUE, 'Proprietário', '2019-11-25', NULL),
(6, 6, TRUE, 'Proprietário', '2021-02-10', '2026-03-01'), 
(7, 7, TRUE, 'Inquilino', '2025-06-05', NULL),
(8, 7, FALSE, 'Morador', '2025-06-05', NULL),              
(9, 8, TRUE, 'Proprietário', '2015-09-30', NULL),
(10, 9, TRUE, 'Inquilino', '2026-01-15', NULL);

INSERT INTO funcionarios (nome_funcionario, cpf_funcionario, funcao_funcionario) VALUES 
('Jorge Mendes', '123.123.123-12', 'Síndico'),
('Antônio Carlos', '234.234.234-23', 'Zelador'),
('Marta Rodrigues', '345.345.345-34', 'Faxineira'),
('Pedro Santos', '456.456.456-45', 'Porteiro');

INSERT INTO locais (nome_local) VALUES 
('Salão de Festas'),
('Churrasqueira'),
('Piscina'),
('Quadra');

INSERT INTO reservas (id_unidade, id_local, inicio_reserva, fim_reserva) VALUES 
(1, 1, '2026-04-20 18:00:00', '2026-04-20 23:00:00'), 
(2, 1, '2026-04-20 18:00:00', '2026-04-20 23:00:00'),
(3, 2, '2026-04-25 10:00:00', '2026-04-25 15:00:00'), 
(5, 4, '2026-04-26 16:00:00', '2026-04-26 18:00:00'), 
(8, 1, '2026-05-10 19:00:00', '2026-05-10 23:59:00'), 
(9, 2, '2026-04-25 16:00:00', '2026-04-25 21:00:00'); 


INSERT INTO ocorrencias (id_unidade, id_funcionario, motivo_ocorrencia, data_ocorrencia) VALUES 
(1, 4, 'Barulho excessivo após as 22h', '2026-03-15 22:45:00'),
(3, 2, 'Vazamento de água no corredor', '2026-04-02 09:30:00'),
(8, 4, 'Mudança fora do horário permitido', '2026-04-10 19:15:00'),
(5, 2, 'Lixo deixado na área comum', '2026-04-12 14:20:00'),
(7, 1, 'Cachorro solto sem coleira', '2026-04-15 08:10:00');


INSERT INTO cobrancas (id_unidade, valor, data_vencimento, data_pagamento, status_pagamento) VALUES 
(1, 550.00, '2026-03-10', '2026-03-08', 'Pago'),
(2, 550.00, '2026-03-10', '2026-03-10', 'Pago'),
(3, 550.00, '2026-03-10', '2026-03-09', 'Pago'),
(1, 550.00, '2026-04-10', '2026-04-05', 'Pago'),          
(2, 550.00, '2026-04-10', NULL, 'Atrasado'),               
(3, 550.00, '2026-04-10', '2026-04-15', 'Pago com Atraso'), 
(5, 550.00, '2026-04-10', NULL, 'Atrasado'),                
(7, 550.00, '2026-04-10', '2026-04-09', 'Pago'),            
(8, 550.00, '2026-04-10', '2026-04-10', 'Pago'),           
(9, 550.00, '2026-04-10', NULL, 'Atrasado');      
      