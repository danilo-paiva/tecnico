-- Migracao: perfil de administrador (aula paw03x01).
-- Para quem JA importou o banco antes desta mudanca.
-- Quem for importar do zero pode usar direto o docs/banco.sql.
USE eventos_db;

ALTER TABLE participantes
  ADD COLUMN perfil ENUM('administrador','comum') NOT NULL DEFAULT 'comum'
  AFTER telefone;

UPDATE participantes SET perfil = 'administrador' WHERE email = 'ana@email.com';
