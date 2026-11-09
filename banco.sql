-- Execute em uma instalação nova. Não apaga bancos ou registros existentes.
CREATE DATABASE IF NOT EXISTS fitvoz CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fitvoz;
CREATE TABLE IF NOT EXISTS usuarios (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nome VARCHAR(80) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 senha VARCHAR(255) NOT NULL,
 criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS academias (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nome VARCHAR(100) NOT NULL,
 unidade VARCHAR(100) NOT NULL,
 perfil VARCHAR(80) NOT NULL,
 descricao VARCHAR(400) NOT NULL,
 comodidades VARCHAR(250) NOT NULL,
 UNIQUE KEY nome_unidade(nome,unidade)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS personais (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 academia_id INT UNSIGNED NOT NULL,
 nome VARCHAR(100) NOT NULL,
 FOREIGN KEY (academia_id) REFERENCES academias(id),
 UNIQUE KEY academia_nome(academia_id,nome)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS avaliacoes (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 usuario_id INT UNSIGNED NOT NULL,
 academia_id INT UNSIGNED NOT NULL,
 nota TINYINT UNSIGNED NOT NULL CHECK(nota BETWEEN 1 AND 5),
 atendimento TINYINT UNSIGNED NOT NULL CHECK(atendimento BETWEEN 1 AND 5),
 estrutura TINYINT UNSIGNED NOT NULL CHECK(estrutura BETWEEN 1 AND 5),
 personal_id INT UNSIGNED NULL,
 nota_personal TINYINT UNSIGNED NULL CHECK(nota_personal BETWEEN 1 AND 5),
 comentario VARCHAR(2000) NOT NULL,
 melhoria VARCHAR(1500) NOT NULL DEFAULT '',
 criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 FOREIGN KEY(usuario_id) REFERENCES usuarios(id),
 FOREIGN KEY(academia_id) REFERENCES academias(id),
 FOREIGN KEY(personal_id) REFERENCES personais(id),
 UNIQUE KEY uma_avaliacao_por_unidade(usuario_id,academia_id)
) ENGINE=InnoDB;
INSERT IGNORE INTO academias (id,nome,unidade,perfil,descricao,comodidades) VALUES
(1,'Forma Studio','Centro','Musculação','Seu espaço para evoluir, com equipamentos completos e acompanhamento próximo.','Musculação, Vestiário, Estacionamento'),
(2,'Movimento Club','Vila Nova','Bem-estar','Movimento, equilíbrio e aulas coletivas para uma rotina mais leve.','Pilates, Aulas coletivas, Acessibilidade'),
(3,'Pulso Training','Jardins','Performance','Treinos intensos e orientação para ir além dos seus próprios limites.','Funcional, Musculação, Estacionamento'),
(4,'Forma Studio','Zona Sul','Iniciantes','Um começo acolhedor, com orientação e treinos no seu ritmo.','Acompanhamento inicial, Acessibilidade, Vestiário');
INSERT IGNORE INTO personais(id,academia_id,nome) VALUES
(1,1,'Ana Lima'),(2,1,'Rafael Costa'),(3,2,'Bruna Alves'),(4,3,'Lucas Martins'),(5,4,'Marina Santos');
-- Academias e profissionais fictícios para atividade acadêmica.
-- Não são inseridas avaliações falsas: o ranking começa vazio.
