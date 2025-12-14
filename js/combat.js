document.addEventListener('DOMContentLoaded', () => {
    console.log("combat.js");
    alert("combat.js chargé !");    

    /* =====================
       DONNÉES COMBATTANTS
    ===================== */

    const hero = {
        id: HERO_ID,
        pv: parseInt(document.getElementById('hero-pv').innerText),
        pvMax: parseInt(document.getElementById('hero-pv-max').innerText),
        mana: parseInt(document.getElementById('hero-mana').innerText),
        manaMax: parseInt(document.getElementById('hero-mana-max').innerText),
        strength: parseInt(document.getElementById('hero-strength').innerText),
        initiative: parseInt(document.getElementById('hero-initiative').innerText),
        class: 'Guerrier', // ⚠️ adapte si besoin
        armorBonus: 0,
        weaponBonus: 0
    };

    const monster = {
        id: MONSTER_ID,
        pv: parseInt(document.getElementById('monster-pv').innerText),
        strength: parseInt(document.getElementById('monster-strength').innerText),
        initiative: 8,
        armorBonus: 0
    };

    const logBox = document.getElementById('combat-log');

    /* =====================
       UTILITAIRES
    ===================== */

    function d6() {
        return Math.floor(Math.random() * 6) + 1;
    }

    function log(text) {
        const p = document.createElement('p');
        p.textContent = '• ' + text;
        p.style.color = 'white';
        logBox.appendChild(p);
        logBox.scrollTop = logBox.scrollHeight;
    }

    function updateUI() {
        document.getElementById('hero-pv').innerText = hero.pv;
        document.getElementById('hero-mana').innerText = hero.mana;
        document.getElementById('monster-pv').innerText = monster.pv;
    }

    /* =====================
       INITIATIVE (DÉBUT)
    ===================== */

    const heroInit = d6() + hero.initiative;
    const monsterInit = d6() + monster.initiative;

    let heroTurn = false;

    if (heroInit > monsterInit) {
        heroTurn = true;
        log(`🟢 Vous commencez le combat (initiative ${heroInit} vs ${monsterInit})`);
    } else {
        heroTurn = false;
        log(`🔴 Le monstre commence (initiative ${monsterInit} vs ${heroInit})`);
        setTimeout(monsterAttack, 1000);
    }

    /* =====================
       COMBAT
    ===================== */

    function physicalAttack(attacker, defender, isHero = true) {
        const attack = d6() + attacker.strength + (attacker.weaponBonus || 0);

        let defense;
        if (!isHero || defender.class !== 'Voleur') {
            defense = d6() + Math.floor(defender.strength / 2) + (defender.armorBonus || 0);
        } else {
            defense = d6() + Math.floor(defender.initiative / 2) + (defender.armorBonus || 0);
        }

        const damage = Math.max(0, attack - defense);
        defender.pv -= damage;

        log(`${isHero ? 'Vous attaquez' : 'Le monstre attaque'} et inflige ${damage} dégâts`);
        updateUI();
    }

    function monsterAttack() {
        if (monster.pv <= 0 || hero.pv <= 0) return;

        physicalAttack(monster, hero, false);

        if (hero.pv <= 0) {
            log("💀 Vous êtes mort...");
            return;
        }

        heroTurn = true;
    }

    /* =====================
       ACTIONS JOUEUR
    ===================== */

    document.getElementById('btn-attack').addEventListener('click', () => {
        if (!heroTurn) return;

        physicalAttack(hero, monster, true);

        if (monster.pv <= 0) {
            log("🏆 Le monstre est vaincu !");
            return;
        }

        heroTurn = false;
        setTimeout(monsterAttack, 1000);
    });

    document.getElementById('btn-magic').addEventListener('click', () => {
        if (!heroTurn) return;

        if (hero.mana < 5) {
            log("❌ Pas assez de mana !");
            return;
        }

        hero.mana -= 5;
        const attack = d6() + d6() + 5;
        const defense = d6() + Math.floor(monster.strength / 2);
        const damage = Math.max(0, attack - defense);

        monster.pv -= damage;

        log(`✨ Vous lancez un sort et infligez ${damage} dégâts`);
        updateUI();

        if (monster.pv <= 0) {
            log("🏆 Le monstre est vaincu !");
            return;
        }

        heroTurn = false;
        setTimeout(monsterAttack, 1000);
    });

    document.getElementById('btn-potion').addEventListener('click', () => {
        if (!heroTurn) return;

        const heal = 20;
        hero.pv = Math.min(hero.pv + heal, hero.pvMax);

        log(`🧪 Vous buvez une potion et récupérez ${heal} PV`);
        updateUI();

        heroTurn = false;
        setTimeout(monsterAttack, 1000);
    });

});
