<!-- index.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Valorant Tracker</title>
    <link rel="icon" href="https://static-00.iconduck.com/assets.00/games-valorant-icon-1024x1024-qt8wlexf.png" type="image/png">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            background-image: url('https://wallpaper.forfun.com/fetch/90/90bcf5ee927d2ac4487970ebb937bef2.jpeg');
            background-size: cover;
            color: white;
        }

        #inputForm {
            max-width: 400px;
            margin: 0 auto;
        }

        #rankInfo {
            margin-top: 20px;
        }

        .card {
            margin-top: 20px;
        }

        .card-body {
            background-color: #525252;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="mt-4 mb-4 text-center">Valorant Rank Tracker</h1>
        <div id="inputForm">
            <div class="text-center">
                <div class="form-group">
                    <label for="usernameTag">Username#Tag:</label>
                    <input type="text" id="usernameTag" class="form-control" placeholder="Enter username#tag" required>
                </div>
                <button onclick="fetchRank()" class="btn btn-primary">Get Rank</button>
                <div id="rankInfo" class="row mt-4 text-center">
                    <p class="col-12">Enter your details above and click 'Get Rank'.</p>
                </div>
            </div>
        </div>
        <div id="currentRankTemplate" class="d-none">
            <div class="card mx-auto mb-4" style="width: 18rem;">
                <img src="" class="card-img-top" alt="Rank Image">
                <div class="card-body">
                    <h5 class="card-title">Current Rank</h5>
                    <p class="card-text"></p>
                    <p class="card-elo"></p>
                </div>
            </div>
        </div>
        <div id="highestRankTemplate" class="d-none">
            <div class="card mx-auto mb-4" style="width: 18rem;">
                <img src="" class="card-img-top" alt="Highest Rank Image">
                <div class="card-body">
                    <h5 class="card-title">Highest Rank</h5>
                    <p class="card-text"></p>
                </div>
            </div>
        </div>
    </div>
    <style>
        .rank-card {
            display: flex;
            justify-content: space-between;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        async function fetchRank() {
            const usernameTag = document.getElementById("usernameTag").value;
            try {
                const response = await fetch("get_rank.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({
                        usernameTag
                    }),
                });
                const data = await response.json();
                const rankInfoDiv = document.getElementById("rankInfo");
                rankInfoDiv.innerHTML = '';
                if (data.current_rank && data.current_rank_image) {
                    const currentRankTemplate = document.getElementById("currentRankTemplate").cloneNode(true);
                    currentRankTemplate.classList.remove("d-none");
                    currentRankTemplate.querySelector(".card-img-top").src = data.current_rank_image;
                    currentRankTemplate.querySelector(".card-text").textContent = data.current_rank;
                    currentRankTemplate.querySelector(".card-elo").textContent = "ELO/MMR: " + data.current_elo;
                    currentRankTemplate.classList.add("col-md-6");
                    rankInfoDiv.appendChild(currentRankTemplate);
                } else {
                    rankInfoDiv.innerHTML += `<p class="col-12 text-center text-danger">Error: Rank data not found in API response</p>`;
                }
                if (data.highest_rank && data.highest_rank_image) {
                    const highestRankTemplate = document.getElementById("highestRankTemplate").cloneNode(true);
                    highestRankTemplate.classList.remove("d-none");
                    highestRankTemplate.querySelector(".card-img-top").src = data.highest_rank_image;
                    highestRankTemplate.querySelector(".card-text").textContent = data.highest_rank;
                    highestRankTemplate.classList.add("col-md-6");
                    rankInfoDiv.appendChild(highestRankTemplate);
                } else {
                    rankInfoDiv.innerHTML += `<p class="col-12 text-center text-danger">Error: Highest rank data not found in API response</p>`;
                }
            } catch (error) {
                console.error("Error fetching data:", error);
                document.getElementById("rankInfo").innerHTML = `<p class="col-12 text-center text-danger">Error fetching data. Please try again later.</p>`;
            }
        }
    </script>
</body>

</html>