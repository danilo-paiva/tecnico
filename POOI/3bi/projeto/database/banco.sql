CREATE DATABASE pooi3bi;

USE pooi3bi;

CREATE TABLE TURMAS(
	id_turma INT PRIMARY KEY,
    curso VARCHAR(255) NOT NULL
	);
    
CREATE TABLE ALUNOS(
	matricula INT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    id_turma INT NOT NULL,
    FOREIGN KEY (id_turma) REFERENCES TURMAS(id_turma)
    );

DELIMITER //
CREATE PROCEDURE sp_validar_curso(
    IN p_curso VARCHAR(255),
    OUT resultado INT
)
BEGIN
    IF p_curso IN ('INFORMATICA', 'ELETRONICA', 'PUBLICIDADE',
                   'ANALISE CLINICA', 'ADMINISTRACAO', 'QUIMICA') THEN
        SET resultado = 1;
    ELSE
        SET resultado = 0;
    END IF;
END //
DELIMITER ;


DELIMITER //
CREATE PROCEDURE sp_verificar_turma(
    IN p_id_turma INT,
    OUT p_existe INT
)
BEGIN
    -- retorna 1 se a turma existir, 0 se nao existir
    SELECT COUNT(*) INTO p_existe
    FROM TURMAS
    WHERE id_turma = p_id_turma;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE sp_turma_tem_alunos(
    IN p_id_turma INT,
    OUT p_tem_alunos INT
)
BEGIN
    -- retorna 1 se a turma tiver alunos, 0 se estiver vazia
    SELECT EXISTS(
        SELECT 1 FROM ALUNOS WHERE id_turma = p_id_turma
    ) INTO p_tem_alunos;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE sp_verificar_matricula(
    IN p_matricula INT,
    OUT p_existe INT
)
BEGIN
    -- retorna 1 se a matricula ja existir, 0 se nao existir
    SELECT EXISTS(
        SELECT 1 FROM ALUNOS WHERE matricula = p_matricula
    ) INTO p_existe;
END //
DELIMITER ;

-- ============ CRUD (reutilizando as SPs de validacao) ============

DELIMITER //
CREATE PROCEDURE sp_cadastrar_turma(
    IN p_id_turma INT,
    IN p_curso VARCHAR(255)
)
BEGIN
    DECLARE v_valido INT;
    DECLARE v_existe INT;

    CALL sp_validar_curso(p_curso, v_valido);
    IF v_valido = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Curso invalido!';
    END IF;

    CALL sp_verificar_turma(p_id_turma, v_existe);
    IF v_existe = 1 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Ja existe turma com esse ID!';
    END IF;

    INSERT INTO TURMAS (id_turma, curso) VALUES (p_id_turma, p_curso);
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE sp_alterar_turma(
    IN p_id_turma INT,
    IN p_curso VARCHAR(255)
)
BEGIN
    DECLARE v_valido INT;
    DECLARE v_existe INT;
    DECLARE v_tem_alunos INT;

    CALL sp_verificar_turma(p_id_turma, v_existe);
    IF v_existe = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Turma inexistente!';
    END IF;

    CALL sp_turma_tem_alunos(p_id_turma, v_tem_alunos);
    IF v_tem_alunos = 1 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Nao permitida a acao: alunos vinculados a esta turma!';
    END IF;

    CALL sp_validar_curso(p_curso, v_valido);
    IF v_valido = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Curso invalido!';
    END IF;

    UPDATE TURMAS SET curso = p_curso WHERE id_turma = p_id_turma;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE sp_excluir_turma(
    IN p_id_turma INT
)
BEGIN
    DECLARE v_existe INT;
    DECLARE v_tem_alunos INT;

    CALL sp_verificar_turma(p_id_turma, v_existe);
    IF v_existe = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Turma inexistente!';
    END IF;

    CALL sp_turma_tem_alunos(p_id_turma, v_tem_alunos);
    IF v_tem_alunos = 1 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Nao permitida a acao: alunos vinculados a esta turma!';
    END IF;

    DELETE FROM TURMAS WHERE id_turma = p_id_turma;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE sp_cadastrar_aluno(
    IN p_matricula INT,
    IN p_nome VARCHAR(255),
    IN p_id_turma INT
)
BEGIN
    DECLARE v_existe INT;

    CALL sp_verificar_matricula(p_matricula, v_existe);
    IF v_existe = 1 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Aluno ja cadastrado!';
    END IF;

    CALL sp_verificar_turma(p_id_turma, v_existe);
    IF v_existe = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Turma inexistente!';
    END IF;

    INSERT INTO ALUNOS (matricula, nome, id_turma) VALUES (p_matricula, p_nome, p_id_turma);
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE sp_alterar_aluno(
    IN p_matricula INT,
    IN p_nome VARCHAR(255),
    IN p_id_turma INT
)
BEGIN
    DECLARE v_existe INT;

    CALL sp_verificar_matricula(p_matricula, v_existe);
    IF v_existe = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Aluno nao encontrado!';
    END IF;

    CALL sp_verificar_turma(p_id_turma, v_existe);
    IF v_existe = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Turma inexistente!';
    END IF;

    UPDATE ALUNOS SET nome = p_nome, id_turma = p_id_turma WHERE matricula = p_matricula;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE sp_excluir_aluno(
    IN p_matricula INT
)
BEGIN
    DECLARE v_existe INT;

    CALL sp_verificar_matricula(p_matricula, v_existe);
    IF v_existe = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Aluno nao encontrado!';
    END IF;

    DELETE FROM ALUNOS WHERE matricula = p_matricula;
END //
DELIMITER ;







    




