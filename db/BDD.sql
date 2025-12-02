DROP DATABASE IF EXISTS DungeonXplorer;
CREATE DATABASE DungeonXplorer;
USE DungeonXplorer;

-- Comptes
CREATE TABLE Account (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    creation_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    current_hero INT DEFAULT NULL
);

-- Classes
CREATE TABLE Class (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    base_pv INT NOT NULL,
    base_mana INT NOT NULL,
    strength INT NOT NULL,
    initiative INT NOT NULL,
    max_items INT NOT NULL
);

-- Items
CREATE TABLE Items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    item_type VARCHAR(50) NOT NULL
);

-- Monstres
CREATE TABLE Monster (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    pv INT NOT NULL,
    mana INT DEFAULT 0,
    strength INT NOT NULL,
    initiative INT NOT NULL,
    attack_text TEXT NOT NULL,
    xp_reward INT NOT NULL,
    image VARCHAR(255) DEFAULT NULL
);

-- Loot
CREATE TABLE Monster_Loot (
    id INT AUTO_INCREMENT PRIMARY KEY,
    monster_id INT,
    item_id INT,
    quantity INT NOT NULL DEFAULT 1,
    drop_rate DECIMAL(5,2) DEFAULT 1.0,
    FOREIGN KEY (monster_id) REFERENCES Monster(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES Items(id),
    UNIQUE (monster_id, item_id)
);

-- Héros
CREATE TABLE Hero (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    class_id INT,
    image VARCHAR(255),
    biography TEXT,
    pv INT NOT NULL,
    mana INT NOT NULL,
    strength INT NOT NULL,
    initiative INT NOT NULL,
    armor_item_id INT,
    primary_weapon_item_id INT,
    secondary_weapon_item_id INT,
    shield_item_id INT,
    spell_list TEXT,
    xp INT NOT NULL,
    current_level INT DEFAULT 1,
    FOREIGN KEY (account_id) REFERENCES Account(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES Class(id),
    FOREIGN KEY (armor_item_id) REFERENCES Items(id),
    FOREIGN KEY (primary_weapon_item_id) REFERENCES Items(id),
    FOREIGN KEY (secondary_weapon_item_id) REFERENCES Items(id),
    FOREIGN KEY (shield_item_id) REFERENCES Items(id)
);

-- Chapitres
CREATE TABLE Chapter (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(255)
);

-- Rencontres (Combats)
CREATE TABLE Encounter (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chapter_id INT,
    monster_id INT,
    FOREIGN KEY (chapter_id) REFERENCES Chapter(id) ON DELETE CASCADE,
    FOREIGN KEY (monster_id) REFERENCES Monster(id)
);

-- Liens entre chapitres
CREATE TABLE Links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chapter_id INT,
    next_chapter_id INT,
    description TEXT,
    FOREIGN KEY (chapter_id) REFERENCES Chapter(id) ON DELETE CASCADE,
    FOREIGN KEY (next_chapter_id) REFERENCES Chapter(id)
);

-- Inventaire
CREATE TABLE Inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hero_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    FOREIGN KEY (hero_id) REFERENCES Hero(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES Items(id),
    UNIQUE (hero_id, item_id)
);

-- Aventures
CREATE TABLE Adventure
(
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    hero_id            INT NOT NULL,
    start_date         DATETIME    DEFAULT CURRENT_TIMESTAMP,
    end_date           DATETIME,                         -- NULL = aventure en cours
    status             VARCHAR(20) DEFAULT 'InProgress', -- InProgress / Completed / Failed

    current_chapter_id INT         DEFAULT 1,            -- position actuelle de l’aventure

    FOREIGN KEY (hero_id) REFERENCES Hero (id) ON DELETE CASCADE,
    FOREIGN KEY (current_chapter_id) REFERENCES Chapter (id)
);

DELIMITER $$

CREATE TRIGGER trg_adventure_single_active_insert
    BEFORE INSERT
    ON Adventure
    FOR EACH ROW
BEGIN
    IF NEW.end_date IS NULL THEN
        IF (SELECT COUNT(*)
            FROM Adventure
            WHERE hero_id = NEW.hero_id
              AND end_date IS NULL) > 0 THEN

            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Un héros ne peut pas avoir plusieurs aventures en cours.';
        END IF;
    END IF;
END$$

CREATE TRIGGER trg_adventure_single_active_update
    BEFORE UPDATE
    ON Adventure
    FOR EACH ROW
BEGIN
    IF NEW.end_date IS NULL THEN
        IF (SELECT COUNT(*)
            FROM Adventure
            WHERE hero_id = NEW.hero_id
              AND end_date IS NULL
              AND id != NEW.id) > 0 THEN

            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Un héros ne peut pas avoir plusieurs aventures en cours.';
        END IF;
    END IF;
END$$

DELIMITER ;

-- Historique de progression
CREATE TABLE Adventure_Progress
(
    id           INT AUTO_INCREMENT PRIMARY KEY,
    adventure_id INT NOT NULL,
    chapter_id   INT NOT NULL,
    status       VARCHAR(20) DEFAULT 'Visited',
    visit_date   DATETIME    DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (adventure_id) REFERENCES Adventure (id) ON DELETE CASCADE,
    FOREIGN KEY (chapter_id) REFERENCES Chapter (id)
);

-- Insertion Classes
INSERT INTO Class (name, description, base_pv, base_mana, strength, initiative, max_items)
VALUES 
('Guerrier', 'Un combattant robuste et puissant', 100, 0, 15, 5, 5),
('Voleur', 'Rapide et agile', 80, 10, 10, 15, 5),
('Magicien', 'Puissant en magie', 60, 20, 5, 10, 5);

-- Monstres
INSERT INTO Monster (name, pv, mana, strength, initiative, attack_text, xp_reward, image)
VALUES 
('Sanglier Enragé', 50, 0, 12, 5, 'Le sanglier charge violemment !', 50, 'img/monsters/boar.png'),
('Loup Noir', 40, 0, 8, 12, 'Le loup bondit et tente de vous mordre !', 60, 'img/monsters/wolf.png'),
('Orc Guerrier', 70, 0, 15, 8, 'L’orc abat sa massue !', 120, 'img/monsters/orc.png');

-- Chapitres
INSERT INTO Chapter (title, description, image)
VALUES 
('Introduction', 'Le ciel est lourd ce soir...', 'img/Village01.jpg'),
('L''orée de la forêt', 'Vous franchissez la lisière...', 'img/BrambleTrails01.jpg'),
('L''arbre aux corbeaux', 'Un vieux chêne tordu...', 'img/DarkForest01.jpg'),
('Le sanglier enragé', 'Un sanglier fonce sur vous !', 'img/SavageBoard01.jpg'),
('Rencontre avec le paysan', 'Un vieux paysan...', 'img/OldMan01.jpg'),
('Le loup noir', 'Une silhouette sombre surgit...', 'img/Wolf01.jpg'),
('La clairière aux pierres anciennes', 'Une clairière brumeuse...', 'img/StoneWall01.jpg');

-- Liens entre chapitres
INSERT INTO Links (chapter_id, next_chapter_id, description)
VALUES
(2, 3, 'Emprunter le chemin sinueux'),
(2, 4, 'Prendre le sentier couvert de ronces'),
(3, 5, 'Rester prudent'),
(3, 6, 'Ignorer les bruits'),
(4, 7, 'Continuer l’aventure'),
(6, 7, 'Survivre au loup');

-- Combats
INSERT INTO Encounter (chapter_id, monster_id)
VALUES
(4, 1),
(6, 2),
(7, 3);
