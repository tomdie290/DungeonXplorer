DROP DATABASE IF EXISTS DungeonXplorer;
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

CREATE TABLE Account
(
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    email         VARCHAR(100) UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    creation_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    current_hero  INT      DEFAULT NULL
);


CREATE TABLE Class
(
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(50) NOT NULL,
    description TEXT,
    base_pv     INT         NOT NULL,
    base_mana   INT         NOT NULL,
    strength    INT         NOT NULL,
    initiative  INT         NOT NULL,
    max_items   INT         NOT NULL
);

CREATE TABLE Items
(
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(50) NOT NULL,
    description TEXT,
    item_type   VARCHAR(50) NOT NULL
);

CREATE TABLE Monster
(
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(50) NOT NULL,
    pv         INT         NOT NULL,
    mana       INT,
    initiative INT         NOT NULL,
    strength   INT         NOT NULL,
    attack     TEXT,
    xp         INT         NOT NULL
);

CREATE TABLE Monster_Loot
(
    id         INT AUTO_INCREMENT PRIMARY KEY,
    monster_id INT,
    item_id    INT,
    quantity   INT NOT NULL  DEFAULT 1,
    drop_rate  DECIMAL(5, 2) DEFAULT 1.0,
    FOREIGN KEY (monster_id) REFERENCES Monster (id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES Items (id),
    UNIQUE (monster_id, item_id)
);

CREATE TABLE Hero
(
    id                       INT AUTO_INCREMENT PRIMARY KEY,
    account_id               INT         NOT NULL,
    name                     VARCHAR(50) NOT NULL,
    class_id                 INT,
    image                    VARCHAR(255),
    biography                TEXT,

    pv                       INT         NOT NULL,
    mana                     INT         NOT NULL,
    strength                 INT         NOT NULL,
    initiative               INT         NOT NULL,

    armor_item_id            INT,
    primary_weapon_item_id   INT,
    secondary_weapon_item_id INT,
    shield_item_id           INT,

    spell_list               TEXT,
    xp                       INT         NOT NULL,
    current_level            INT DEFAULT 1,

    FOREIGN KEY (account_id) REFERENCES Account (id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES Class (id),
    FOREIGN KEY (armor_item_id) REFERENCES Items (id),
    FOREIGN KEY (primary_weapon_item_id) REFERENCES Items (id),
    FOREIGN KEY (secondary_weapon_item_id) REFERENCES Items (id),
    FOREIGN KEY (shield_item_id) REFERENCES Items (id)
);


CREATE TABLE Level
(
    id               INT AUTO_INCREMENT PRIMARY KEY,
    class_id         INT,
    level            INT NOT NULL,
    required_xp      INT NOT NULL,
    pv_bonus         INT NOT NULL,
    mana_bonus       INT NOT NULL,
    strength_bonus   INT NOT NULL,
    initiative_bonus INT NOT NULL,
    FOREIGN KEY (class_id) REFERENCES Class (id)
);


CREATE TABLE Chapter
(
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       TEXT NOT NULL,
    description TEXT NOT NULL,
    image       VARCHAR(255)
);

CREATE TABLE Chapter_Treasure
(
    id         INT AUTO_INCREMENT PRIMARY KEY,
    chapter_id INT,
    item_id    INT,
    quantity   INT NOT NULL DEFAULT 1,
    FOREIGN KEY (chapter_id) REFERENCES Chapter (id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES Items (id),
    UNIQUE (chapter_id, item_id)
);

CREATE TABLE Encounter
(
    id         INT AUTO_INCREMENT PRIMARY KEY,
    chapter_id INT,
    monster_id INT,
    FOREIGN KEY (chapter_id) REFERENCES Chapter (id) ON DELETE CASCADE,
    FOREIGN KEY (monster_id) REFERENCES Monster (id)
);

CREATE TABLE Links
(
    id              INT AUTO_INCREMENT PRIMARY KEY,
    chapter_id      INT,
    next_chapter_id INT,
    description     TEXT,
    FOREIGN KEY (chapter_id) REFERENCES Chapter (id) ON DELETE CASCADE,
    FOREIGN KEY (next_chapter_id) REFERENCES Chapter (id)
);


CREATE TABLE Inventory
(
    id       INT AUTO_INCREMENT PRIMARY KEY,
    hero_id  INT NOT NULL,
    item_id  INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    FOREIGN KEY (hero_id) REFERENCES Hero (id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES Items (id),
    UNIQUE (hero_id, item_id)
);


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


INSERT INTO Class (name, description, base_pv, base_mana, strength, initiative, max_items)
VALUES ('Guerrier', 'Un combattant robuste et puissant', 100, 0, 15, 5, 5),
       ('Voleur', 'Rapide et agile, spécialisé dans les attaques surprises', 80, 10, 10, 15, 5),
       ('Magicien', 'Maître des sorts, faible en combat physique mais puissant en magie', 60, 20, 5, 10, 5);


INSERT INTO Chapter (title, description, image)
VALUES ('Introduction',
        'Le ciel est lourd ce soir sur le village du Val Perdu, dissimulé entre les montagnes. La petite taverne, dernier refuge avant l''immense forêt, est étrangement calme quand le bourgmestre s’approche de vous. Homme d’apparence usée par les années et les soucis, il vous adresse un regard désespéré. Vous sentez le poids de la mission qui s’annonce, et un frisson parcourt votre échine. Bientôt, la forêt s''ouvre devant vous, sombre et menaçante. La quête commence.',
        'img/Village01.jpg'),
       ('L''orée de la forêt',
        'Vous franchissez la lisière des arbres, la pénombre de la forêt avalant le sentier devant vous. Un vent froid glisse entre les troncs, et le bruissement des feuilles ressemble à un murmure menaçant.',
        'img/BrambleTrails01.jpg'),
       ('L''arbre aux corbeaux',
        'Votre choix vous mène devant un vieux chêne aux branches tordues, grouillant de corbeaux noirs qui vous observent en silence. À vos pieds, des traces de pas légers, probablement récents, mènent plus loin dans les bois. Soudain, un bruit de pas feutrés se fait entendre. Vous ressentez la présence d’un prédateur.',
        'img/Dark Forest01.jpg'),
       ('Le sanglier enragé',
        'En progressant, le calme de la forêt est soudain brisé par un grognement. Surgissant des buissons, un énorme sanglier au pelage épais et aux yeux injectés de sang se dirige vers vous. Sa rage est palpable et il semble prêt à en découdre.',
        'img/SavageBoard01.jpg'),
       ('Rencontre avec le paysan',
        'Tandis que vous progressez, une voix humaine s’élève, interrompant le silence de la forêt. Vous tombez sur un vieux paysan, accroupi près de champignons aux couleurs vives. Il sursaute en vous voyant puis se détend, vous souriant tristement.',
        'img/OldMan01.jpg'),
       ('Le loup noir',
        'À mesure que vous avancez, un bruissement attire votre attention. Une silhouette sombre s’élance soudainement devant vous : un loup noir aux yeux perçants. Son poil est hérissé et sa gueule laisse entrevoir des crocs acérés.',
        'img/Wolf01.jpg'),
       ('La clairière aux pierres anciennes',
        'Vous atteignez une clairière étrange, entourée de pierres dressées, comme un ancien autel oublié. Une brume légère rampe au sol, les ombres dansent sous la lune.',
        'img/StoneWall01.jpg'),
       ('Les murmures du ruisseau',
        'Vous arrivez près d’un ruisseau serpentant entre les arbres. Le chant de l’eau vous apaise, mais des murmures étranges proviennent de la rive. Une pierre moussue porte des inscriptions anciennes.',
        ''),
       ('Au pied du château',
        'La forêt s’écarte et laisse place à une colline escarpée. Au sommet, le château en ruines projette une ombre menaçante. Les murs effrités témoignent d’un passé sinistre.',
        'img/Castle01.jpg'),
       ('La lumière au bout du néant',
        'Le monde disparaît autour de vous, une obscurité glaciale vous enveloppe. Une lumière fragile apparaît, accompagnée d’une voix ancienne. Vous revenez à la vie, mais dépossédé de tout équipement.',
        ''),
       ('La curiosité tua le chat',
        'Qu’avez-vous fait, malheureux !',
        '');


INSERT INTO Links (chapter_id, next_chapter_id, description)
VALUES (2, 3, 'Emprunter le chemin sinueux'),
       (2, 4, 'Prendre le sentier couvert de ronces'),

       (3, 5, 'Rester prudent'),
       (3, 6, 'Ignorer les bruits et poursuivre'),

       (4, 8, 'Vaincre le sanglier'),
       (4, 10, 'Fuir ou être vaincu'),

       (5, 7, 'Continuer l''aventure'),

       (6, 7, 'Survivre au loup'),
       (6, 10, 'Le loup vous terrasse'),

       (7, 8, 'Prendre le sentier couvert de mousse'),
       (7, 9, 'Suivre le chemin tortueux à travers les racines'),

       (8, 11, 'Toucher la pierre gravée'),
       (8, 9, 'Ignorer la pierre et continuer'),

       (10, 1, 'Reprendre l''aventure depuis le début'),

       (11, 10, 'Retour au chapitre 10');
