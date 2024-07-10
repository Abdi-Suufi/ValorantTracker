<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Valorant Tracker</title>
    <link rel="icon" href="favicon.png" type="image/png">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>

    </style>
</head>

<body>
    <div class="content-section masthead">
        <div id="main">
            <h1 class="mt-4 mb-4 text-center">Valorant Rank Tracker</h1>
            <div id="inputForm">
                <form onsubmit="event.preventDefault(); fetchRank();">
                    <div class="text-center">
                        <div class="form-group">
                            <label for="usernameTag">Username#Tag:</label>
                            <input type="text" id="usernameTag" class="form-control" placeholder="Enter username#tag" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Get Rank</button>
                    </div>
                </form>
                <div id="rankInfo" class="row mt-4 text-center">
                    <p class="col-12">Enter your details above and click 'Get Rank'.</p>
                </div>
            </div>
            <div class="row">
                <div id="currentRankTemplate" class="d-none col-md-6">
                    <div class="card mb-4">
                        <img src="" class="card-img-top" alt="Rank Image">
                        <div class="card-body">
                            <h5 class="card-title">Current Rank:</h5>
                            <p class="card-text"></p>
                            <p class="card-elo"></p>
                        </div>
                    </div>
                </div>
                <div id="highestRankTemplate" class="d-none col-md-6">
                    <div class="card mb-4">
                        <img src="" class="card-img-top" alt="Highest Rank Image">
                        <div class="card-body">
                            <h5 class="card-title">Highest Rank:</h5>
                            <p class="card-text"></p>
                            <p class="card-season"></p>
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