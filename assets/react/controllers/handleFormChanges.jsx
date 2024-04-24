import React from 'react';

export default async function handleFormChanges()  {
    
    let league = document.querySelector("#question_choice_week")
    league.addEventListener("change", async function() {
        document.querySelector("#question_choice_players").innerHTML = null
        let form = this.closest("form")
        const data = new URLSearchParams();
        for (const pair of new FormData(form)) {
            if (pair[0] !== 'question_choice[_token]') {
                data.append(pair[0], pair[1]);
            }
        }



        const response = await fetch(form.action, {
            method: form.method,
            body: data,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        });
        if (!response.ok) {
            console.error('Network response was not ok');
            return;
        }





        const html = await response.text();
        let content = document.createElement("html");
        content.innerHTML = html;
        let newSelect = content.querySelector("#question_choice_team");
        document.querySelector("#question_choice_team").replaceWith(newSelect);
        let team = document.querySelector("#question_choice_team");



        if (typeof team !== 'undefined' && team !== null ) {
            team.addEventListener("change", async function() {
                const data = new URLSearchParams();
                for (const pair of new FormData(form)) {
                    if (pair[0] !== 'question_choice[_token]'){
                        data.append(pair[0], pair[1]);
                    }
                }


                const response = await fetch(form.action, {
                    method: form.method,
                    body: data,
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                });
                if (!response.ok) {
                    console.error('Network response was not ok');
                    return;
                }



                
                const html = await response.text();
                content = document.createElement("html");
                content.innerHTML = html;
                let nouveauSelect = content.querySelector("#question_choice_players");
                document.querySelector("#question_choice_players").replaceWith(nouveauSelect);
            });
        }
    });
}