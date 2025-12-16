document.addEventListener('DOMContentLoaded', () => {
    console.log("combat.js chargé !");

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
        class: HERO_CLASS,
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
        document.getElementById('hero-pv').innerText = Math.max(0, hero.pv);
        document.getElementById('hero-mana').innerText = Math.max(0, hero.mana);
        document.getElementById('monster-pv').innerText = Math.max(0, monster.pv);
        console.log(`UI updated: hero pv=${hero.pv}, monster pv=${monster.pv}`);
    }

    function endCombat(result) {
        const btnAttack = document.getElementById('btn-attack');
        const btnMagic = document.getElementById('btn-magic');
        const btnPotion = document.getElementById('btn-potion');
        if (btnAttack) btnAttack.disabled = true;
        if (btnMagic) btnMagic.disabled = true;
        if (btnPotion) btnPotion.disabled = true;

        fetch('/DungeonXplorer/combat/end', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                result: result,
                hero_pv: hero.pv,
                hero_mana: hero.mana,
                chapter_id: CHAPTER_ID
            })
        }).then(() => {
            if (result === 'win') {
                // Affiche un bouton pour avancer vers le chapitre suivant
                const actions = document.getElementById('post-combat-actions') || document.querySelector('.text-center.mt-4');
                const advanceBtn = document.createElement('button');
                advanceBtn.id = 'btn-advance-chapter';
                advanceBtn.className = 'btn btn-primary btn-lg mx-1';
                advanceBtn.textContent = (typeof NEXT_LINK_TEXT === 'string' && NEXT_LINK_TEXT.trim().length > 0) ? NEXT_LINK_TEXT : '➡️ Avancer au chapitre suivant';
                if (actions) actions.appendChild(advanceBtn);

                advanceBtn.addEventListener('click', () => {
                    if (typeof NEXT_LINK_ID === 'number' && NEXT_LINK_ID > 0) {
                        // Soumet un formulaire POST comme dans view/chapter.php
                        const form = document.createElement('form');
                        form.method = 'post';
                        form.action = '/DungeonXplorer/chapter/choice';
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'choice_id';
                        input.value = NEXT_LINK_ID;
                        form.appendChild(input);
                        document.body.appendChild(form);
                        form.submit();
                    } else if (typeof NEXT_CHAPTER_ID === 'number' && NEXT_CHAPTER_ID > 0) {
                        // Fallback: redirection par query
                        window.location.href = '/DungeonXplorer/chapter?id=' + NEXT_CHAPTER_ID;
                    } else {
                        window.location.href = '/DungeonXplorer/chapter';
                    }
                });
            } else {
                // Affiche un bouton pour aller au lien de mort (comme les choix de chapitre)
                const actions = document.getElementById('post-combat-actions') || document.querySelector('.text-center.mt-4');
                const deathBtn = document.createElement('button');
                deathBtn.id = 'btn-death-link';
                deathBtn.className = 'btn btn-danger btn-lg mx-1';
                deathBtn.textContent = (typeof DEATH_LINK_TEXT === 'string' && DEATH_LINK_TEXT.trim().length > 0) ? DEATH_LINK_TEXT : '☠️ Aller au chapitre de la mort';
                if (actions) actions.appendChild(deathBtn);

                deathBtn.addEventListener('click', () => {
                    if (typeof DEATH_LINK_ID === 'number' && DEATH_LINK_ID > 0) {
                        const form = document.createElement('form');
                        form.method = 'post';
                        form.action = '/DungeonXplorer/chapter/choice';
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'choice_id';
                        input.value = DEATH_LINK_ID;
                        form.appendChild(input);
                        document.body.appendChild(form);
                        form.submit();
                    } else if (typeof DEATH_CHAPTER_ID === 'number' && DEATH_CHAPTER_ID > 0) {
                        window.location.href = '/DungeonXplorer/chapter?id=' + DEATH_CHAPTER_ID;
                    } else {
                        window.location.href = '/DungeonXplorer/chapter';
                    }
                });
            }
        });
    }

    /* =====================
       INITIATIVE (DÉBUT)
    ===================== */

    let heroTurn = false;
    if (typeof RESUME_COMBAT !== 'undefined' && RESUME_COMBAT) {
        heroTurn = (typeof HERO_TURN_RESUME !== 'undefined' && HERO_TURN_RESUME) ? true : false;
        log('⏯️ Reprise du combat — état restauré');
        if (!heroTurn) setTimeout(monsterAttack, 1000);
    } else {
        const heroInit = d6() + hero.initiative;
        const monsterInit = d6() + monster.initiative;
        console.log(`Initiative: hero ${heroInit}, monster ${monsterInit}`);

        if (heroInit > monsterInit || (heroInit === monsterInit && hero.class === 'Voleur')) {
            heroTurn = true;
            log(`🟢 Vous commencez le combat (initiative ${heroInit} vs ${monsterInit})`);
        } else {
            heroTurn = false;
            log(`🔴 Le monstre commence (initiative ${monsterInit} vs ${heroInit})`);
            setTimeout(monsterAttack, 1000);
        }
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
        defender.pv = Math.max(0, defender.pv);

        console.log(`${isHero ? 'Héros attaque' : 'Monstre attaque'}: attack=${attack}, defense=${defense}, damage=${damage}, defender pv=${defender.pv}`);
        log(`${isHero ? 'Vous attaquez' : 'Le monstre attaque'} et inflige ${damage} dégâts`);
        updateUI();
    }

    function monsterAttack() {
        console.log('monsterAttack called');
        if (monster.pv <= 0 || hero.pv <= 0) return;

        physicalAttack(monster, hero, false);

        if (hero.pv <= 0) {
            hero.pv = 0;
            updateUI();
            log("💀 Vous êtes mort...");
            endCombat('lose');
            return;
        }

        heroTurn = true;
    }

    /* =====================
       ACTIONS JOUEUR
    ===================== */

    document.getElementById('btn-attack').addEventListener('click', () => {
        console.log('Bouton Attaquer cliqué, heroTurn:', heroTurn);
        if (!heroTurn) return;

        physicalAttack(hero, monster, true);

        if (monster.pv <= 0) {
            monster.pv = 0;
            updateUI();
            log("🏆 Le monstre est vaincu !");
            endCombat('win');
            return;
        }

        heroTurn = false;
        setTimeout(monsterAttack, 1000);
    });

    if(document.getElementById('btn-magic') != null)
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
        monster.pv = Math.max(0, monster.pv);

        log(`✨ Vous lancez un sort et infligez ${damage} dégâts`);
        updateUI();

        if (monster.pv <= 0) {
            monster.pv = 0;
            updateUI();
            log("🏆 Le monstre est vaincu !");
            endCombat('win');
            return;
        }

        heroTurn = false;
        setTimeout(monsterAttack, 1000);
    });

    document.getElementById('btn-potion').addEventListener('click', () => {
        if (!heroTurn) return;

        const potionType = 'pv'; // Type de potion : 'pv' ou 'mana'
        const valeur = 20;

        if (potionType === 'pv') {
            hero.pv = Math.min(hero.pv + valeur, hero.pvMax);
            log(`🧪 Vous buvez une potion et récupérez ${valeur} PV`);
        } else if (potionType === 'mana') {
            hero.mana = Math.min(hero.mana + valeur, hero.manaMax);
            log(`🧪 Vous buvez une potion et récupérez ${valeur} Mana`);
        }

        updateUI();

        heroTurn = false;
        setTimeout(monsterAttack, 1000);
    });

    // Avant de quitter la page en cours (bouton Quitter), injecte l'état du combat dans le formulaire
    const quitForm = document.getElementById('quit-form');
    if (quitForm) {
        quitForm.addEventListener('submit', (e) => {
            const hPv = document.getElementById('quit-hero-pv');
            const hMana = document.getElementById('quit-hero-mana');
            const mId = document.getElementById('quit-monster-id');
            const mPv = document.getElementById('quit-monster-pv');
            const hTurn = document.getElementById('quit-hero-turn');
            if (hPv) hPv.value = Math.max(0, hero.pv);
            if (hMana) hMana.value = Math.max(0, hero.mana);
            if (mId) mId.value = monster.id || 0;
            if (mPv) mPv.value = Math.max(0, monster.pv);
            if (hTurn) hTurn.value = heroTurn ? 1 : 0;
        });
    }

});
