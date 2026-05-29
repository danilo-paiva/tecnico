USE presenca_escolar;

-- Cursos (10)
INSERT INTO curso (nome) VALUES
('Informática'), ('Administração'), ('Direito'), ('Enfermagem'), ('Psicologia'),
('Engenharia Civil'), ('Arquitetura'), ('Medicina'), ('Odontologia'), ('Letras');

-- Períodos (10)
INSERT INTO periodo (nome) VALUES
('1º Semestre'), ('2º Semestre'), ('3º Semestre'), ('4º Semestre'), ('5º Semestre'),
('6º Semestre'), ('7º Semestre'), ('8º Semestre'), ('9º Semestre'), ('10º Semestre');

-- Salas (10)
INSERT INTO sala (nome, capacidade) VALUES
('SALA 101', 40), ('SALA 102', 30), ('SALA 201', 40), ('SALA 202', 30), ('SALA 301', 50),
('LAB 1', 20), ('LAB 2', 20), ('AUDITORIO A', 100), ('SALA 401', 30), ('SALA 402', 40);

-- Professores (10)
INSERT INTO professor (nome, especialidade) VALUES
('Dr. Alan Turing', 'Computação'), ('Dr. Ada Lovelace', 'Algoritmos'), ('Prof. Newton', 'Física'),
('Prof. Einstein', 'Relatividade'), ('Prof. Marie Curie', 'Química'), ('Prof. Darwin', 'Biologia'),
('Prof. Plato', 'Filosofia'), ('Prof. Aristotle', 'Lógica'), ('Prof. Socrates', 'Ética'), ('Prof. Hypatia', 'Matemática');

-- Disciplinas (10) - Removido id_professor para evitar redundância
INSERT INTO disciplina (nome) VALUES
('Banco de Dados'), ('Programação C'), ('Cálculo I'), ('Física Geral'), ('Química Orgânica'),
('Biologia Celular'), ('Introdução ao Direito'), ('Psicologia Social'), ('Ética Profissional'), ('Administração Financeira');

-- Alunos (10)
INSERT INTO aluno (nome, ra) VALUES
('Alice Silva', 'RA001'), ('Bob Johnson', 'RA002'), ('Carlos Lima', 'RA003'), ('Diana Prince', 'RA004'), ('Eduardo Costa', 'RA005'),
('Fernanda Souza', 'RA006'), ('Gabriel Moura', 'RA007'), ('Helena Luz', 'RA008'), ('Igor Santos', 'RA009'), ('Julia Meireles', 'RA010');

-- Turmas (5)
INSERT INTO turma (nome, id_curso, id_periodo) VALUES
('T1-INFO', 1, 1), ('T2-INFO', 1, 2), ('T1-ADM', 2, 1), ('T1-DIR', 3, 1), ('T1-ENF', 4, 1);

-- Matrículas (10)
INSERT INTO matricula (id_aluno, id_turma, data_matricula) VALUES
(1, 1, '2026-01-10'), (2, 1, '2026-01-10'), (3, 1, '2026-01-10'), (4, 2, '2026-01-11'), (5, 2, '2026-01-11'),
(6, 3, '2026-01-12'), (7, 3, '2026-01-12'), (8, 4, '2026-01-13'), (9, 4, '2026-01-13'), (10, 5, '2026-01-14');

-- Aulas (10)
INSERT INTO aula (data_aula, id_disciplina, id_turma, id_professor, id_sala) VALUES
('2026-02-01', 1, 1, 1, 6), ('2026-02-01', 2, 1, 2, 6), ('2026-02-02', 1, 1, 1, 6), ('2026-02-02', 2, 1, 2, 6),
('2026-02-03', 3, 1, 10, 1), ('2026-02-03', 1, 2, 1, 6), ('2026-02-04', 2, 2, 2, 6), ('2026-02-04', 4, 3, 3, 2),
('2026-02-05', 5, 3, 5, 2), ('2026-02-05', 7, 4, 8, 3);

-- Presenças (20+)
INSERT INTO presenca (id_aluno, id_aula, status) VALUES
(1, 1, 'Presente'), (2, 1, 'Presente'), (3, 1, 'Ausente'), (1, 2, 'Presente'), (2, 2, 'Presente'),
(3, 2, 'Presente'), (1, 3, 'Presente'), (2, 3, 'Ausente'), (3, 3, 'Presente'), (4, 6, 'Presente'),
(5, 6, 'Presente'), (4, 7, 'Ausente'), (5, 7, 'Presente'), (6, 8, 'Presente'), (7, 8, 'Presente'),
(6, 9, 'Ausente'), (7, 9, 'Presente'), (8, 10, 'Presente'), (9, 10, 'Presente'), (1, 4, 'Presente');
