USE presenca_escolar;

-- =============================================================================
-- 1. CONSULTAS SIMPLES (10)
-- =============================================================================
SELECT * FROM curso;
SELECT nome FROM professor;
SELECT * FROM aluno;
SELECT nome, ra FROM aluno;
SELECT * FROM disciplina;
SELECT nome FROM sala;
SELECT * FROM periodo;
SELECT nome FROM turma;
SELECT * FROM aula;
SELECT status FROM presenca;

-- =============================================================================
-- 2. CONSULTAS COM DUAS TABELAS (10)
-- =============================================================================
-- 1. Alunos e suas Turmas
SELECT aluno.nome, turma.nome FROM aluno, matricula, turma WHERE aluno.id_aluno = matricula.id_aluno AND matricula.id_turma = turma.id_turma;
-- 2. Turmas e seus Cursos
SELECT turma.nome, curso.nome FROM turma, curso WHERE turma.id_curso = curso.id_curso;
-- 3. Turmas e seus Períodos
SELECT turma.nome, periodo.nome FROM turma, periodo WHERE turma.id_periodo = periodo.id_periodo;
-- 4. Disciplinas e seus Professores
SELECT disciplina.nome, professor.nome FROM disciplina, professor WHERE disciplina.id_professor = professor.id_professor;
-- 5. Aulas e suas Disciplinas
SELECT aula.data_aula, disciplina.nome FROM aula, disciplina WHERE aula.id_disciplina = disciplina.id_disciplina;
-- 6. Aulas e suas Salas
SELECT aula.data_aula, sala.nome FROM aula, sala WHERE aula.id_sala = sala.id_sala;
-- 7. Aulas e seus Professores
SELECT aula.data_aula, professor.nome FROM aula, professor WHERE aula.id_professor = professor.id_professor;
-- 8. Presenças e Alunos
SELECT presenca.status, aluno.nome FROM presenca, aluno WHERE presenca.id_aluno = aluno.id_aluno;
-- 9. Presenças e Aulas
SELECT presenca.status, aula.data_aula FROM presenca, aula WHERE presenca.id_aula = aula.id_aula;
-- 10. Matrículas e Alunos
SELECT matricula.data_matricula, aluno.nome FROM matricula, aluno WHERE matricula.id_aluno = aluno.id_aluno;

-- =============================================================================
-- 3. CONSULTAS COM TRÊS OU MAIS TABELAS (10)
-- =============================================================================
-- 1. Alunos, Turmas e Cursos
SELECT aluno.nome, turma.nome, curso.nome FROM aluno, matricula, turma, curso WHERE aluno.id_aluno = matricula.id_aluno AND matricula.id_turma = turma.id_turma AND turma.id_curso = curso.id_curso;
-- 2. Presença, Alunos e Aulas
SELECT aluno.nome, aula.data_aula, presenca.status FROM aluno, aula, presenca WHERE aluno.id_aluno = presenca.id_aluno AND aula.id_aula = presenca.id_aula;
-- 3. Professor, Disciplina e Turma
SELECT professor.nome, disciplina.nome, turma.nome FROM professor, disciplina, turma, aula WHERE professor.id_professor = aula.id_professor AND disciplina.id_disciplina = aula.id_disciplina AND turma.id_turma = aula.id_turma;
-- 4. Alunos, Matrículas e Períodos
SELECT aluno.nome, periodo.nome FROM aluno, matricula, turma, periodo WHERE aluno.id_aluno = matricula.id_aluno AND matricula.id_turma = turma.id_turma AND turma.id_periodo = periodo.id_periodo;
-- 5. Aula, Disciplina, Sala e Professor
SELECT aula.data_aula, disciplina.nome, sala.nome, professor.nome FROM aula, disciplina, sala, professor WHERE aula.id_disciplina = disciplina.id_disciplina AND aula.id_sala = sala.id_sala AND aula.id_professor = professor.id_professor;
-- 6. Aluno, Aula e Disciplina via Presença
SELECT aluno.nome, disciplina.nome FROM aluno, presenca, aula, disciplina WHERE aluno.id_aluno = presenca.id_aluno AND presenca.id_aula = aula.id_aula AND aula.id_disciplina = disciplina.id_disciplina;
-- 7. Turma, Curso, Período e Sala (via aula)
SELECT turma.nome, curso.nome, periodo.nome, sala.nome FROM turma, curso, periodo, aula, sala WHERE turma.id_curso = curso.id_curso AND turma.id_periodo = periodo.id_periodo AND aula.id_turma = turma.id_turma AND aula.id_sala = sala.id_sala;
-- 8. Aluno, Disciplina e Professor (via presenca e aula)
SELECT aluno.nome, disciplina.nome, professor.nome FROM aluno, presenca, aula, disciplina, professor WHERE aluno.id_aluno = presenca.id_aluno AND presenca.id_aula = aula.id_aula AND aula.id_disciplina = disciplina.id_disciplina AND aula.id_professor = professor.id_professor;
-- 9. Presença, Sala, Data e Aluno
SELECT aluno.nome, sala.nome, aula.data_aula, presenca.status FROM aluno, presenca, aula, sala WHERE aluno.id_aluno = presenca.id_aluno AND presenca.id_aula = aula.id_aula AND aula.id_sala = sala.id_sala;
-- 10. Matrícula, Aluno, Turma e Curso
SELECT matricula.id_matricula, aluno.nome, turma.nome, curso.nome FROM matricula, aluno, turma, curso WHERE matricula.id_aluno = aluno.id_aluno AND matricula.id_turma = turma.id_turma AND turma.id_curso = curso.id_curso;

-- =============================================================================
-- 4. CONSULTAS COM FILTROS WHERE (5)
-- =============================================================================
SELECT * FROM aluno WHERE ra = 'RA001';
SELECT * FROM professor WHERE especialidade = 'Computação';
SELECT * FROM presenca WHERE status = 'Ausente';
SELECT * FROM aula WHERE data_aula = '2026-02-01';
SELECT * FROM sala WHERE capacidade >= 40;

-- =============================================================================
-- 5. CONSULTAS COM ORDER BY (5)
-- =============================================================================
SELECT * FROM aluno ORDER BY nome ASC;
SELECT * FROM professor ORDER BY nome DESC;
SELECT * FROM disciplina ORDER BY nome ASC;
SELECT * FROM aula ORDER BY data_aula DESC;
SELECT * FROM sala ORDER BY capacidade DESC;

-- =============================================================================
-- 6. CONSULTAS COM GROUP BY (5)
-- =============================================================================
SELECT id_turma, COUNT(*) as qtd_alunos FROM matricula GROUP BY id_turma;
SELECT id_professor, COUNT(*) as qtd_disciplinas FROM disciplina GROUP BY id_professor;
SELECT id_aula, COUNT(*) as total_presencas FROM presenca WHERE status = 'Presente' GROUP BY id_aula;
SELECT status, COUNT(*) as total FROM presenca GROUP BY status;
SELECT id_curso, COUNT(*) as qtd_turmas FROM turma GROUP BY id_curso;
