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
    if (data.current_rank && data.current_rank_image) {
      const currentRankTemplate = document
        .getElementById("currentRankTemplate")
        .cloneNode(true);
      currentRankTemplate.classList.remove("d-none");
      currentRankTemplate.querySelector(".card-img-top").src =
        data.current_rank_image;
      currentRankTemplate.querySelector(".card-text.rank-name").textContent =
        data.current_rank;
      currentRankTemplate.querySelector(".card-elo").textContent =
        `ELO: ${data.current_elo}`;
      currentRankTemplate.querySelector(".card-rr").textContent =
        `${data.ranking_in_tier}/100 RR`;
      const mmrChangeEl = currentRankTemplate.querySelector(".card-mmr-change");
      mmrChangeEl.textContent =
        data.mmr_change >= 0
          ? `+${data.mmr_change} RR last game`
          : `${data.mmr_change} RR last game`;
      mmrChangeEl.style.color = data.mmr_change >= 0 ? "#00ff00" : "#ff4655";
      currentRankTemplate.querySelector(".stat-value.wins").textContent =
        data.season_wins;
      currentRankTemplate.querySelector(".stat-value.games").textContent =
        data.season_games;
      currentRankTemplate.querySelector(".stat-value.winrate").textContent =
        `${data.season_winrate}%`;
      rankInfoDiv.appendChild(currentRankTemplate);
    } else {
      rankInfoDiv.innerHTML += `<p class="col-12 text-center text-danger">Error: Rank data not found in API response</p>`;
    }
    if (data.highest_rank && data.highest_rank_image) {
      const highestRankTemplate = document
        .getElementById("highestRankTemplate")
        .cloneNode(true);
      highestRankTemplate.classList.remove("d-none");
      highestRankTemplate.querySelector(".card-img-top").src =
        data.highest_rank_image;
      highestRankTemplate.querySelector(".card-text.rank-name").textContent =
        data.highest_rank;
      highestRankTemplate.querySelector(".card-season").textContent =
        `Achieved in ${data.highest_rank_season}`;
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
