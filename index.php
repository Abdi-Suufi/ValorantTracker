<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Valorant Tracker</title>
    <link rel="icon" href="favicon.png" type="image/png">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>

    </style>
</head>

<body>
    <div class="content-section masthead">
        <div id="main" class="container">
            <h1 class="mt-4 mb-4 text-center">Valorant Rank Tracker</h1>
            <div id="inputForm" class="mb-5">
                <form onsubmit="event.preventDefault(); fetchRank();">
                    <div class="text-center">
                        <div class="form-group">
                            <label for="usernameTag">Username#Tag:</label>
                            <input type="text" id="usernameTag" class="form-control" placeholder="Enter username#tag" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Get Rank</button>
                    </div>
                </form>
            </div>
            
            <div id="rankInfo" class="row mt-4">
                <p class="col-12 text-center">Enter your details above and click 'Get Rank'.</p>
            </div>

            <!-- Templates -->
            <div id="currentRankTemplate" class="d-none col-lg-6">
                <div class="card mb-4">
                    <div class="rank-header">
                        <h5 class="card-title mb-0">Current Rank</h5>
                    </div>
                    <img src="" class="card-img-top rank-img" alt="Rank Image">
                    <div class="card-body">
                        <div class="rank-info">
                            <p class="card-text rank-name mb-2"></p>
                            <p class="card-elo mb-2"></p>
                            <p class="card-rr mb-2"></p>
                            <p class="card-mmr-change mb-2"></p>
                        </div>
                        <div class="season-stats mt-4">
                            <h6>Current Season Stats</h6>
                            <div class="stats-grid">
                                <div class="stat-item">
                                    <span class="stat-label">Wins</span>
                                    <span class="stat-value wins"></span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">Games</span>
                                    <span class="stat-value games"></span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">Winrate</span>
                                    <span class="stat-value winrate"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="highestRankTemplate" class="d-none col-lg-6">
                <div class="card mb-4">
                    <div class="rank-header">
                        <h5 class="card-title mb-0">Peak Rank</h5>
                    </div>
                    <img src="" class="card-img-top rank-img" alt="Highest Rank Image">
                    <div class="card-body">
                        <div class="rank-info">
                            <p class="card-text rank-name mb-2"></p>
                            <p class="card-season mb-2"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="script.js"></script>
</body>

</html>