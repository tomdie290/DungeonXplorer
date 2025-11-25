CREATE DATABASE DungeonXplorer;
USE DungeonXplorer;

DROP TABLE IF EXISTS Adventure_Progress;
DROP TABLE IF EXISTS Adventure;
DROP TABLE IF EXISTS Inventory;
DROP TABLE IF EXISTS Links;
DROP TABLE IF EXISTS Encounter;
DROP TABLE IF EXISTS Chapter_Treasure;
DROP TABLE IF EXISTS Chapter;
DROP TABLE IF EXISTS Level;
DROP TABLE IF EXISTS Hero;
DROP TABLE IF EXISTS Monster_Loot;
DROP TABLE IF EXISTS Monster;
DROP TABLE IF EXISTS Items;
DROP TABLE IF EXISTS Class;
DROP TABLE IF EXISTS Account;

CREATE TABLE Account (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    creation_date DATETIME DEFAULT CURRENT_TIMESTAMP
);


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

CREATE TABLE Items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    item_type VARCHAR(50) NOT NULL
);

CREATE TABLE Monster (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    pv INT NOT NULL,
    mana INT,
    initiative INT NOT NULL,
    strength INT NOT NULL,
    attack TEXT,
    xp INT NOT NULL
);

CREATE TABLE Monster_Loot (
    id INT AUTO_INCREMENT PRIMARY KEY,
    monster_id INT,
    item_id INT,
    quantity INT NOT NULL DEFAULT 1,
    drop_rate DECIMAL(5,2) DEFAULT 1.0,
    FOREIGN KEY (monster_id) REFERENCES Monster(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES Items(id),
    UNIQUE(monster_id, item_id)
);

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


CREATE TABLE Level (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT,
    level INT NOT NULL,
    required_xp INT NOT NULL,
    pv_bonus INT NOT NULL,
    mana_bonus INT NOT NULL,
    strength_bonus INT NOT NULL,
    initiative_bonus INT NOT NULL,
    FOREIGN KEY (class_id) REFERENCES Class(id)
);


CREATE TABLE Chapter (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    image VARCHAR(255)
);

CREATE TABLE Chapter_Treasure (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chapter_id INT,
    item_id INT,
    quantity INT NOT NULL DEFAULT 1,
    FOREIGN KEY (chapter_id) REFERENCES Chapter(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES Items(id),
    UNIQUE(chapter_id, item_id)
);

CREATE TABLE Encounter (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chapter_id INT,
    monster_id INT,
    FOREIGN KEY (chapter_id) REFERENCES Chapter(id) ON DELETE CASCADE,
    FOREIGN KEY (monster_id) REFERENCES Monster(id)
);

CREATE TABLE Links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chapter_id INT,
    next_chapter_id INT,
    description TEXT,
    FOREIGN KEY (chapter_id) REFERENCES Chapter(id) ON DELETE CASCADE,
    FOREIGN KEY (next_chapter_id) REFERENCES Chapter(id)
);


CREATE TABLE Inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hero_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    FOREIGN KEY (hero_id) REFERENCES Hero(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES Items(id),
    UNIQUE(hero_id, item_id)
);


CREATE TABLE Adventure (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hero_id INT NOT NULL,
    start_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    end_date DATETIME,        -- NULL = aventure en cours
    status VARCHAR(20) DEFAULT 'InProgress', -- InProgress / Completed / Failed

    current_chapter_id INT,   -- position actuelle de l’aventure

    FOREIGN KEY (hero_id) REFERENCES Hero(id) ON DELETE CASCADE,
    FOREIGN KEY (current_chapter_id) REFERENCES Chapter(id)
);

-- Empêcher plusieurs aventures en parallèle
ALTER TABLE Adventure
ADD CONSTRAINT one_active_adventure_per_hero
CHECK (
    NOT (
        end_date IS NULL
        AND hero_id IN (
            SELECT hero_id FROM Adventure
            WHERE end_date IS NULL
            GROUP BY hero_id HAVING COUNT(*) > 1
        )
    )
);

CREATE TABLE Adventure_Progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    adventure_id INT NOT NULL,
    chapter_id INT NOT NULL,
    status VARCHAR(20) DEFAULT 'Visited',
    visit_date DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (adventure_id) REFERENCES Adventure(id) ON DELETE CASCADE,
    FOREIGN KEY (chapter_id) REFERENCES Chapter(id)
);
