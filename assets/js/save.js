// assets/js/save_players.js

document.addEventListener('DOMContentLoaded', function() {
    const weekId = new URLSearchParams(window.location.search).get('weekId');
    console.log('Week ID:', weekId);

    document.querySelectorAll('.add-player-btn').forEach(button => {
        button.addEventListener('click', function() {
            const playerId = this.dataset.playerId;
            const playerForename = this.dataset.playerForename;
            const playerName = this.dataset.playerName;

            console.log('Player ID:', playerId);
            console.log('Player Forename:', playerForename);
            console.log('Player Name:', playerName);

            fetch("/save-players?weekId=" + encodeURIComponent(weekId), {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ players: [{ id: playerId, forename: playerForename, name: playerName }] }),
            })
            .then(response => response.json())
            .then(data => {
                console.log('Response Data:', data);
                if (data.status === 'success') {
                    const deckPlayersList = document.getElementById('deckPlayers');
                    const newPlayerItem = document.createElement('li');
                    newPlayerItem.classList.add("list-group-item");
                    newPlayerItem.textContent = `${playerForename} ${playerName}`;
                    deckPlayersList.appendChild(newPlayerItem);
                } else {
                    console.error('Erreur: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
        });
    });
});
