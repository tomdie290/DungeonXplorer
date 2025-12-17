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
        class: HERO_CLASS
    };

    const monster = {
        id: MONSTER_ID,
        pv: parseInt(document.getElementById('monster-pv').innerText),
        strength: parseInt(document.getElementById('monster-strength').innerText),
        initiative: 8
    };

    const logBox = document.getElementById('combat-log');

    /* =====================
       UTILITAIRES
    ===================== */

    const d6 = () => Math.floor(Math.random() * 6) + 1;

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
    }


    function endCombat(result) {
        const btnAttack = document.getElementById('btn-attack');
        const btnPotion = document.getElementById('btn-potion');
        if (btnAttack) btnAttack.disabled = true;
        if (btnPotion) btnPotion.disabled = true;

        fetch('/DungeonXplorer/combat/end', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                result: result,
                hero_pv: hero.pv,
                hero_mana: hero.mana,
                chapter_id: CHAPTER_ID
            })
        }).then(() => {
            const actions = document.getElementById('post-combat-actions') || document.querySelector('.text-center.mt-4');
            if (!actions) return;

            if (result === 'win') {
                const advanceBtn = document.createElement('button');
                advanceBtn.className = 'btn btn-primary btn-lg mx-1';
                advanceBtn.textContent = NEXT_LINK_TEXT || '➡️ Avancer au chapitre suivant';
                actions.appendChild(advanceBtn);

                advanceBtn.addEventListener('click', () => {
                    if (NEXT_LINK_ID) {
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
                    } else if (NEXT_CHAPTER_ID) {
                        window.location.href = '/DungeonXplorer/chapter?id=' + NEXT_CHAPTER_ID;
                    } else {
                        window.location.href = '/DungeonXplorer/chapter';
                    }
                });

            } else {
                const deathBtn = document.createElement('button');
                deathBtn.className = 'btn btn-danger btn-lg mx-1';
                deathBtn.textContent = DEATH_LINK_TEXT || '☠️ Aller au chapitre de la mort';
                actions.appendChild(deathBtn);

                deathBtn.addEventListener('click', () => {
                    if (DEATH_LINK_ID) {
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
                    } else if (DEATH_CHAPTER_ID) {
                        window.location.href = '/DungeonXplorer/chapter?id=' + DEATH_CHAPTER_ID;
                    } else {
                        window.location.href = '/DungeonXplorer/chapter';
                    }
                });
            }
        });
    }


    let heroTurn = true;
    if (typeof RESUME_COMBAT !== 'undefined' && RESUME_COMBAT) {
        heroTurn = HERO_TURN_RESUME === 1;
        log("⏯️ Combat repris");
        if (!heroTurn) setTimeout(monsterAttack, 800);
    } else {
        const heroInit = d6() + hero.initiative;
        const monsterInit = d6() + monster.initiative;
        heroTurn = heroInit >= monsterInit;
        log(heroTurn ? "🟢 Vous commencez" : "🔴 Le monstre commence");
        if (!heroTurn) setTimeout(monsterAttack, 800);
    }

    function physicalAttack(attacker, defender, isHero = true) {
        const attack = d6() + attacker.strength;
        const defense = d6() + Math.floor(defender.strength / 2);
        const damage = Math.max(0, attack - defense);
        defender.pv = Math.max(0, defender.pv - damage);
        log(`${isHero ? 'Vous attaquez' : 'Le monstre attaque'} et inflige ${damage} dégâts`);
        updateUI();
    }

    function monsterAttack() {
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

    document.getElementById('btn-attack').addEventListener('click', () => {
        if (!heroTurn) return;
        physicalAttack(hero, monster, true);
        if (monster.pv <= 0) {
            monster.pv = 0;
            updateUI();
            log("🏆 Victoire !");
            endCombat('win');
            return;
        }
        heroTurn = false;
        setTimeout(monsterAttack, 800);
    });


    const style = document.createElement('style');
    style.innerHTML = `
    .overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.85); display:flex; justify-content:center; align-items:center; z-index:9999; }
    .box { background:#262626; border:2px solid #C4975E; border-radius:12px; padding:20px; max-width:800px; width:80%; color:white; }
    .cards { display:grid; grid-template-columns: repeat(auto-fill, minmax(220px,1fr)); gap:1rem; }
    .card { background:#1f1f1f; border:2px solid #C4975E; border-radius:10px; padding:12px; }
    .card h3 { color:#FFD700; }
    .card button { margin-top:8px; width:100%; }`;
    document.head.appendChild(style);

    document.getElementById('btn-potion').addEventListener('click', () => {
        if (!heroTurn) return;
        if (document.querySelector('.overlay')) return;

        const overlay = document.createElement('div');
        overlay.className = 'overlay';
        overlay.addEventListener('click', e => { if(e.target === overlay) overlay.remove(); });

        const box = document.createElement('div');
        box.className = 'box';

        const header = document.createElement('div');
        header.style.display = 'flex';
        header.style.justifyContent = 'space-between';
        header.style.alignItems = 'center';

        const title = document.createElement('h2');
        title.innerText = '🧪 Potions';

        const closeBtn = document.createElement('button');
        closeBtn.innerText = '✖';
        closeBtn.style.background = 'transparent';
        closeBtn.style.border = 'none';
        closeBtn.style.color = 'white';
        closeBtn.style.fontSize = '22px';
        closeBtn.style.cursor = 'pointer';
        closeBtn.onclick = () => overlay.remove();

        header.appendChild(title);
        header.appendChild(closeBtn);

        const content = document.createElement('div');
        content.className = 'cards';

        box.appendChild(header);
        box.appendChild(content);
        overlay.appendChild(box);
        document.body.appendChild(overlay);

        fetch(`/DungeonXplorer/inventory?hero=${hero.id}&onlyPotions=1`)
            .then(r => r.text())
            .then(html => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const items = doc.querySelectorAll('.inventory-item');

                if(items.length === 0) {
                    content.innerHTML = `<p style="color:white">Aucune potion</p>`;
                    return;
                }

                items.forEach(item => {
                    const id = item.dataset.id;
                    const type = item.dataset.type || 'pv';
                    const quantity = parseInt(item.dataset.quantity || 1);
                    let value = parseInt(item.dataset.value || 0);
                    if(value === 0){
                        const descText = item.querySelector('em')?.innerText ?? '';
                        const match = descText.match(/(\d+)\s*(PV|Mana)/i);
                        if(match) value = parseInt(match[1]);
                    }

                    const desc = item.querySelector('em')?.innerText ?? '';
                    const name = item.querySelector('h3')?.innerText ?? 'Potion';

                    const card = document.createElement('div');
                    card.className = 'card';

                    card.innerHTML = `
                        <h3>${name}</h3>
                        <p style="color:white">${desc}</p>
                        <p style="color:white">Quantité : ${quantity}</p>
                        <button class="btn btn-success">Utiliser</button>
                    `;

                    card.querySelector('button').onclick = () => {
                        if(quantity <= 0) return;

                        if(type === 'pv') {
                            hero.pv = Math.min(hero.pv + value, hero.pvMax);
                            log(`🧪 ${name} utilisée `);
                        } else if(type === 'mana') {
                            hero.mana = Math.min(hero.mana + value, hero.manaMax);
                            log(`✨ ${name} utilisée `);
                        }
                        updateUI();

                        fetch('/DungeonXplorer/inventory/use', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ item_id: id })
                        });

                        overlay.remove();
                        heroTurn = false;
                        setTimeout(monsterAttack, 800);
                    };

                    content.appendChild(card);
                });
            });
    });


    const quitForm = document.getElementById('quit-form');
    if (quitForm) {
        quitForm.addEventListener('submit', () => {
            document.getElementById('quit-hero-pv').value = hero.pv;
            document.getElementById('quit-hero-mana').value = hero.mana;
            document.getElementById('quit-monster-pv').value = monster.pv;
            document.getElementById('quit-hero-turn').value = heroTurn ? 1 : 0;
        });
    }

});
