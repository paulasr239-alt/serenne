CREATE DATABASE IF NOT EXISTS serenne
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE serenne;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('cliente', 'admin') DEFAULT 'cliente'
);

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
);

CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    estoque INT NOT NULL,
    imagem VARCHAR(255) NOT NULL,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    data_pedido DATETIME DEFAULT CURRENT_TIMESTAMP,
    forma_pagamento ENUM('PIX', 'Boleto', 'Cartão') NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE itens_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

INSERT INTO categorias (nome) VALUES
('Anéis'),
('Colares'),
('Brincos'),
('Pulseiras');

INSERT INTO produtos
(categoria_id, nome, descricao, preco, estoque, imagem)
VALUES

(1, 'Anel Solitário',
'Anel delicado com pedra central brilhante.',
129.90, 10, 'solitario.jpg'),

(1, 'Anel Aparador',
'Anel elegante com pequenos detalhes brilhantes.',
99.90, 12, 'aparador.jpg'),

(1, 'Anel Pérola',
'Anel sofisticado com detalhe de pérola.',
119.90, 8, 'anel-perola.jpg'),

(1, 'Anel Entrelaçado',
'Anel dourado com design entrelaçado e moderno.',
139.90, 10, 'entrelacado.jpg'),

(2, 'Colar Ponto de Luz',
'Colar delicado com pingente brilhante.',
119.90, 10, 'ponto-de-luz.jpg'),

(2, 'Colar Coração',
'Colar dourado com pingente em formato de coração.',
149.90, 8, 'colar-coracao.jpg'),

(2, 'Colar Borboleta',
'Colar delicado com pingente de borboleta.',
129.90, 10, 'borboleta.jpg'),

(2, 'Colar Esmeralda',
'Colar sofisticado com pedra de tonalidade verde esmeralda.',
189.90, 6, 'esmeralda.jpg'),

(3, 'Argola Dourada',
'Brincos de argola com acabamento dourado.',
89.90, 15, 'argola.jpg'),

(3, 'Brinco Pérola',
'Brincos clássicos com detalhes de pérola.',
89.90, 12, 'brinco-perola.jpg'),

(3, 'Brinco Gota',
'Brincos elegantes em formato de gota.',
109.90, 10, 'gota.jpg'),

(3, 'Brinco Zircônia',
'Brincos delicados com pedras brilhantes de zircônia.',
119.90, 8, 'zirconia.jpg'),

(4, 'Pulseira Riviera',
'Pulseira sofisticada com sequência de pedras brilhantes.',
189.90, 6, 'riviera.jpg'),

(4, 'Pulseira Elo Português',
'Pulseira dourada com elos clássicos.',
139.90, 10, 'elo-portugues.jpg'),

(4, 'Pulseira Coração',
'Pulseira delicada com pingente de coração.',
119.90, 12, 'pulseira-coracao.jpg'),

(4, 'Bracelete Dourado',
'Bracelete moderno com acabamento dourado.',
159.90, 8, 'bracelete.jpg');