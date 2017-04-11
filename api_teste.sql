CREATE SCHEMA api_teste;
USE api_teste;

CREATE TABLE dados (
	id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(80) NOT NULL,
    criado DATETIME NOT NULL
);


INSERT INTO dados (nome, criado) VALUES ('teste1', current_timestamp());
INSERT INTO dados (nome, criado) VALUES ('teste2', current_timestamp());
INSERT INTO dados (nome, criado) VALUES ('teste3', current_timestamp());
INSERT INTO dados (nome, criado) VALUES ('teste4', current_timestamp());