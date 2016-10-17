CREATE TABLE cidades
(
    id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    estado_id INT(11),
    nome VARCHAR(64) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    CONSTRAINT FK_79B94AE79F5A440B FOREIGN KEY (estado_id) REFERENCES estados (id)
);
CREATE INDEX IDX_79B94AE79F5A440B ON cidades (estado_id);
CREATE TABLE ebd_aulas
(
    id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    igreja_id INT(11),
    data DATE,
    observacoes VARCHAR(4000),
    created_at DATETIME,
    updated_at DATETIME,
    CONSTRAINT fk_igreja FOREIGN KEY (igreja_id) REFERENCES igrejas (id)
);
CREATE INDEX fk_igreja_idx ON ebd_aulas (igreja_id);
CREATE UNIQUE INDEX id_UNIQUE ON ebd_aulas (id);
CREATE UNIQUE INDEX igreja_data_unique ON ebd_aulas (igreja_id, data);
CREATE TABLE ebd_matriculas
(
    id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    pessoa_id INT(11),
    sala_id INT(11),
    observacoes VARCHAR(255),
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    data_inicial DATE,
    data_final DATE,
    CONSTRAINT FK_123B86C9DF6FA0A5 FOREIGN KEY (pessoa_id) REFERENCES pessoas (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_123B86C9C51CDF3F FOREIGN KEY (sala_id) REFERENCES ebd_salas (id)
);
CREATE INDEX IDX_123B86C9C51CDF3F ON ebd_matriculas (sala_id);
CREATE UNIQUE INDEX UNIQ_123B86C9DF6FA0A5 ON ebd_matriculas (pessoa_id);
CREATE TABLE ebd_presencas
(
    id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    aula_id INT(11),
    pessoa_id INT(11),
    observacoes VARCHAR(255),
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    situacao BIT(1),
    CONSTRAINT FK_4F6DDB26DF6FA0A5 FOREIGN KEY (pessoa_id) REFERENCES pessoas (id)
);
CREATE INDEX IDX_4F6DDB26AD1A1255 ON ebd_presencas (aula_id);
CREATE INDEX IDX_4F6DDB26DF6FA0A5 ON ebd_presencas (pessoa_id);
CREATE TABLE ebd_professores
(
    id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    sala_id INT(11),
    pessoa_id INT(11),
    data_inicial DATE,
    data_final DATE,
    titular TINYINT(4) DEFAULT '0',
    observacoes VARCHAR(255),
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    CONSTRAINT FK_DD40449CC51CDF3F FOREIGN KEY (sala_id) REFERENCES ebd_salas (id) ON DELETE SET NULL,
    CONSTRAINT FK_DD40449C7D2D84D5 FOREIGN KEY (pessoa_id) REFERENCES pessoas (id) ON DELETE SET NULL
);
CREATE INDEX IDX_DD40449C7D2D84D5 ON ebd_professores (pessoa_id);
CREATE INDEX IDX_DD40449CC51CDF3F ON ebd_professores (sala_id);
CREATE TABLE ebd_salas
(
    id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    igreja_id INT(11),
    nome VARCHAR(64) NOT NULL,
    descricao VARCHAR(255),
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    CONSTRAINT FK_29DDE70A263136B0 FOREIGN KEY (igreja_id) REFERENCES igrejas (id)
);
CREATE UNIQUE INDEX ebd_salas_nome_uindex ON ebd_salas (nome);
CREATE INDEX IDX_29DDE70A263136B0 ON ebd_salas (igreja_id);
CREATE TABLE ebd_salas_aulas
(
    id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    sala_id INT(11),
    pessoa_id INT(11),
    aula_id INT(11) NOT NULL,
    assunto VARCHAR(255),
    quantidade_biblias INT(11),
    quantidade_visitantes INT(11),
    observacoes VARCHAR(255),
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    CONSTRAINT FK_EC86E320C51CDF3F FOREIGN KEY (sala_id) REFERENCES ebd_salas (id) ON DELETE CASCADE,
    CONSTRAINT FK_EC86E3207D2D84D5 FOREIGN KEY (pessoa_id) REFERENCES pessoas (id) ON DELETE CASCADE,
    CONSTRAINT fk_aula FOREIGN KEY (aula_id) REFERENCES ebd_aulas (id) ON DELETE CASCADE
);
CREATE INDEX fk_aulas_idx ON ebd_salas_aulas (aula_id);
CREATE INDEX IDX_EC86E3207D2D84D5 ON ebd_salas_aulas (pessoa_id);
CREATE INDEX IDX_EC86E320C51CDF3F ON ebd_salas_aulas (sala_id);
CREATE TABLE estados
(
    id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nome VARCHAR(64) NOT NULL,
    sigla VARCHAR(2) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME
);
CREATE UNIQUE INDEX UNIQ_222B212854BD530C ON estados (nome);
CREATE TABLE igrejas
(
    id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    presbiterio_id INT(11),
    nome VARCHAR(255) NOT NULL,
    slug VARCHAR(255),
    endereco VARCHAR(255),
    endereco_numero VARCHAR(32),
    endereco_bairro VARCHAR(64),
    endereco_cidade VARCHAR(64),
    endereco_uf VARCHAR(2),
    endereco_cep VARCHAR(16),
    telefone VARCHAR(16),
    email VARCHAR(128),
    created_at DATETIME,
    updated_at DATETIME,
    CONSTRAINT FK_E9F369A2CA8671BC FOREIGN KEY (presbiterio_id) REFERENCES presbiterios (id) ON DELETE SET NULL ON UPDATE CASCADE
);
CREATE INDEX IDX_E9F369A2CA8671BC ON igrejas (presbiterio_id);
CREATE UNIQUE INDEX idx_nome ON igrejas (nome);
CREATE TABLE membros
(
    id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    pessoa_id INT(11) NOT NULL,
    igreja_id INT(11),
    rol VARCHAR(32) DEFAULT 'C' NOT NULL,
    recepcao_data DATE,
    recepcao_modo VARCHAR(256),
    recepcao_ministro_id INT(11),
    recepcao_igreja_origem_id INT(11),
    saida_data DATE,
    saida_motivo VARCHAR(256),
    saida_ata VARCHAR(32),
    saida_igreja_destino_id INT(11),
    restauracao_data DATE,
    restauracao_ata VARCHAR(32),
    observacoes VARCHAR(255),
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    CONSTRAINT FK_A3A50B16DF6FA0A5 FOREIGN KEY (pessoa_id) REFERENCES pessoas (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_A3A50B16263136B0 FOREIGN KEY (igreja_id) REFERENCES igrejas (id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_igreja_origem FOREIGN KEY (recepcao_igreja_origem_id) REFERENCES igrejas (id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT FK_A3A50B162AD8C76E FOREIGN KEY (saida_igreja_destino_id) REFERENCES igrejas (id) ON DELETE SET NULL ON UPDATE CASCADE
);
CREATE INDEX fk_igreja_origem_idx ON membros (recepcao_igreja_origem_id);
CREATE INDEX IDX_A3A50B16263136B0 ON membros (igreja_id);
CREATE INDEX IDX_A3A50B162AD8C76E ON membros (saida_igreja_destino_id);
CREATE UNIQUE INDEX pessoa_id_UNIQUE ON membros (pessoa_id);
CREATE TABLE membros_funcoes
(
    id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    membro_id INT(11),
    funcao VARCHAR(128) NOT NULL,
    data_inicial DATE NOT NULL,
    data_final DATE NOT NULL,
    observacoes VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    CONSTRAINT FK_1B4705B524172E FOREIGN KEY (membro_id) REFERENCES membros (id)
);
CREATE INDEX IDX_1B4705B524172E ON membros_funcoes (membro_id);
CREATE TABLE pastores
(
    id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    pessoa_id INT(11),
    igreja_id INT(11),
    pastoreio_data_inicial DATE,
    pastoreio_data_final DATE,
    data_ordenacao DATE,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    CONSTRAINT FK_80E1D4C7DF6FA0A5 FOREIGN KEY (pessoa_id) REFERENCES pessoas (id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT FK_80E1D4C7263136B0 FOREIGN KEY (igreja_id) REFERENCES igrejas (id)
);
CREATE INDEX IDX_80E1D4C7263136B0 ON pastores (igreja_id);
CREATE UNIQUE INDEX UNIQ_80E1D4C7DF6FA0A5 ON pastores (pessoa_id);
CREATE TABLE permissoes_usuario_igreja
(
    id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    usuario_id INT(11) NOT NULL,
    igreja_id INT(11) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    CONSTRAINT usuario FOREIGN KEY (usuario_id) REFERENCES users (id),
    CONSTRAINT igreja FOREIGN KEY (igreja_id) REFERENCES igrejas (id) ON DELETE CASCADE
);
CREATE INDEX igreja_idx ON permissoes_usuario_igreja (igreja_id);
CREATE INDEX usuario_idx ON permissoes_usuario_igreja (usuario_id);
CREATE TABLE pessoas
(
    id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    pai_id INT(11),
    mae_id INT(11),
    conjuge_id INT(11),
    nome VARCHAR(128) NOT NULL,
    nacionalidade VARCHAR(64),
    sexo VARCHAR(1),
    estado_civil VARCHAR(32),
    profissao VARCHAR(128),
    data_de_nascimento DATE,
    religiao VARCHAR(128),
    endereco VARCHAR(256),
    endereco_cep VARCHAR(16),
    telefone VARCHAR(32),
    email VARCHAR(128),
    observacoes VARCHAR(1024),
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    cpf VARCHAR(15),
    instrucao VARCHAR(64),
    endereco_numero VARCHAR(32),
    endereco_bairro VARCHAR(64),
    endereco_cidade VARCHAR(128),
    endereco_uf VARCHAR(2),
    naturalidade_cidade VARCHAR(128),
    naturalidade_uf VARCHAR(128),
    CONSTRAINT FK_18A4F2ACC19C5634 FOREIGN KEY (pai_id) REFERENCES cidades (id),
    CONSTRAINT FK_18A4F2AC3402F8C9 FOREIGN KEY (mae_id) REFERENCES cidades (id),
    CONSTRAINT FK_18A4F2AC5522146E FOREIGN KEY (conjuge_id) REFERENCES cidades (id)
);
CREATE INDEX UNIQ_18A4F2AC3402F8C9 ON pessoas (mae_id);
CREATE INDEX UNIQ_18A4F2AC5522146E ON pessoas (conjuge_id);
CREATE INDEX UNIQ_18A4F2ACC19C5634 ON pessoas (pai_id);
CREATE TABLE presbiterios
(
    id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    nome VARCHAR(128) NOT NULL,
    sigla VARCHAR(16) NOT NULL,
    estado VARCHAR(64),
    created_at DATETIME NOT NULL,
    updated_at DATETIME
);
CREATE UNIQUE INDEX idx_nome ON presbiterios (nome, estado);
CREATE UNIQUE INDEX idx_sigla ON presbiterios (sigla, estado);
CREATE TABLE sociedades_internas
(
    id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    igreja_id INT(11),
    nome VARCHAR(64) NOT NULL,
    sigla VARCHAR(32) NOT NULL,
    data_instalacao DATE NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    CONSTRAINT FK_BA57E734263136B0 FOREIGN KEY (igreja_id) REFERENCES igrejas (id)
);
CREATE INDEX IDX_BA57E734263136B0 ON sociedades_internas (igreja_id);
CREATE TABLE sociedades_internas_diretorias
(
    id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    sociedade_interna_id INT(11),
    presidente_id INT(11),
    vice_presidente_id INT(11),
    secretario_1_id INT(11),
    secretario_2_id INT(11),
    tesoureiro_id INT(11),
    ano INT(11) NOT NULL,
    sigla VARCHAR(32) NOT NULL,
    data_instalacao DATE NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    CONSTRAINT FK_3B757B816573BB16 FOREIGN KEY (sociedade_interna_id) REFERENCES sociedades_internas (id),
    CONSTRAINT FK_3B757B8197B7E846 FOREIGN KEY (presidente_id) REFERENCES membros (id),
    CONSTRAINT FK_3B757B813A3027E1 FOREIGN KEY (vice_presidente_id) REFERENCES membros (id),
    CONSTRAINT FK_3B757B8144D0885A FOREIGN KEY (secretario_1_id) REFERENCES membros (id),
    CONSTRAINT FK_3B757B81566527B4 FOREIGN KEY (secretario_2_id) REFERENCES membros (id),
    CONSTRAINT FK_3B757B81598745F8 FOREIGN KEY (tesoureiro_id) REFERENCES membros (id)
);
CREATE INDEX IDX_3B757B813A3027E1 ON sociedades_internas_diretorias (vice_presidente_id);
CREATE INDEX IDX_3B757B8144D0885A ON sociedades_internas_diretorias (secretario_1_id);
CREATE INDEX IDX_3B757B81566527B4 ON sociedades_internas_diretorias (secretario_2_id);
CREATE INDEX IDX_3B757B81598745F8 ON sociedades_internas_diretorias (tesoureiro_id);
CREATE INDEX IDX_3B757B816573BB16 ON sociedades_internas_diretorias (sociedade_interna_id);
CREATE INDEX IDX_3B757B8197B7E846 ON sociedades_internas_diretorias (presidente_id);
CREATE TABLE user_custom_fields
(
    user_id INT(11) unsigned NOT NULL,
    attribute VARCHAR(50) DEFAULT '' NOT NULL,
    value VARCHAR(255),
    CONSTRAINT `PRIMARY` PRIMARY KEY (user_id, attribute)
);
CREATE TABLE users
(
    id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    email VARCHAR(100) DEFAULT '' NOT NULL,
    password VARCHAR(255),
    salt VARCHAR(255) DEFAULT '' NOT NULL,
    roles VARCHAR(255) DEFAULT '' NOT NULL,
    username VARCHAR(100),
    is_enabled TINYINT(4) DEFAULT '1' NOT NULL,
    confirmation_token VARCHAR(255),
    password_reset_request_date DATETIME,
    created_at DATETIME,
    updated_at DATETIME
);
CREATE UNIQUE INDEX unique_email ON users (email);
CREATE UNIQUE INDEX username ON users (username);