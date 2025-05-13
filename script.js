async function fetchRank() {
  const usernameTag = document.getElementById("usernameTag").value;
  if (!usernameTag) {
    alert("Please enter a username#tag.");
    return;
  }
  try {
    const response = await fetch("get_rank.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        usernameTag,
      }),
    });
    const data = await response.json();
    const rankInfoDiv = document.getElementById("rankInfo");
    rankInfoDiv.innerHTML = "";

    if (data.error) {
      rankInfoDiv.innerHTML = `<p class="col-12 text-center text-danger">${data.error}</p>`;
      return;
    }

    if (data.current_rank && data.current_rank_image) {
      const currentRankTemplate = document
        .getElementById("currentRankTemplate")
        .cloneNode(true);
      currentRankTemplate.classList.remove("d-none");
      
      // Set current rank info
      currentRankTemplate.querySelector(".card-img-top").src = data.current_rank_image;
      currentRankTemplate.querySelector(".card-text.rank-name").textContent = data.current_rank;
      currentRankTemplate.querySelector(".card-elo").textContent = `ELO: ${data.current_elo}`;
      
      // Add RR and MMR change
      const rrText = `${data.ranking_in_tier}/100 RR`;
      const mmrChangeText = data.mmr_change >= 0 
        ? `+${data.mmr_change} RR last game` 
        : `${data.mmr_change} RR last game`;
      
      currentRankTemplate.querySelector(".card-rr").textContent = rrText;
      const mmrChangeEl = currentRankTemplate.querySelector(".card-mmr-change");
      mmrChangeEl.textContent = mmrChangeText;
      mmrChangeEl.style.color = data.mmr_change >= 0 ? '#00ff00' : '#ff4655';

      // Set season stats
      const winsEl = currentRankTemplate.querySelector(".stat-value.wins");
      const gamesEl = currentRankTemplate.querySelector(".stat-value.games");
      const winrateEl = currentRankTemplate.querySelector(".stat-value.winrate");

      if (data.season_games > 0) {
        winsEl.textContent = data.season_wins;
        gamesEl.textContent = data.season_games;
        winrateEl.textContent = `${data.season_winrate}%`;
      } else {
        winsEl.textContent = '0';
        gamesEl.textContent = '0';
        winrateEl.textContent = '0%';
      }

      rankInfoDiv.appendChild(currentRankTemplate);
    } else {
      rankInfoDiv.innerHTML += `<p class="col-12 text-center text-danger">Error: Rank data not found in API response</p>`;
    }

    if (data.highest_rank && data.highest_rank_image) {
      const highestRankTemplate = document
        .getElementById("highestRankTemplate")
        .cloneNode(true);
      highestRankTemplate.classList.remove("d-none");
      
      // Set peak rank info
      highestRankTemplate.querySelector(".card-img-top").src = data.highest_rank_image;
      highestRankTemplate.querySelector(".card-text.rank-name").textContent = data.highest_rank;
      highestRankTemplate.querySelector(".card-season").textContent = `Achieved in ${data.highest_rank_season}`;

      rankInfoDiv.appendChild(highestRankTemplate);
    } else {
      rankInfoDiv.innerHTML += `<p class="col-12 text-center text-danger">Error: Highest rank data not found in API response</p>`;
    }
  } catch (error) {
    console.error("Error fetching data:", error);
    document.getElementById(
      "rankInfo"
    ).innerHTML = `<p class="col-12 text-center text-danger">Error fetching data. Please try again later.</p>`;
  }
}
