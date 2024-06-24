<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Valorant Tracker</title>
    <!-- Bootstrap CSS link -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Additional custom styles */
        body {
            padding: 20px;
        }

        #inputForm {
            max-width: 400px;
            margin: 0 auto;
        }

        #rankInfo {
            margin-top: 20px;
        }

        body {
            background-image: url('background.jpg');
            background-size: cover;
        }

        .card {
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1 class="mt-4 mb-4 text-center">Valorant Rank Tracker</h1>

        <div id="inputForm">
            <div class="text-center">
                <div class="form-group">
                    <label for="region">Region:</label>
                    <select id="region" class="form-control">
                        <option value="na">North America</option>
                        <option value="eu">Europe</option>
                        <option value="ap">Asia Pacific</option>
                        <!-- Add more options as needed -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" class="form-control" placeholder="Enter username" required>
                </div>
                <div class="form-group">
                    <label for="tag">Tag:</label>
                    <input type="text" id="tag" class="form-control" placeholder="Enter tag" required>
                </div>
                <button onclick="fetchRank()" class="btn btn-primary">Get Rank</button>
                <div id="rankInfo" class="row mt-4 text-center">
                    <p class="col-12">Enter your details above and click 'Get Rank'.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        async function fetchRank() {
            const region = document.getElementById("region").value;
            const username = document.getElementById("username").value;
            const tag = document.getElementById("tag").value;

            try {
                const response = await fetch("get_rank.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({
                        region,
                        username,
                        tag
                    }),
                });

                const data = await response.json();

                let rankInfoContent = '';

                if (data.current_rank && data.current_rank_image) {
                    const currentRank = data.current_rank;
                    const currentRankImage = data.current_rank_image;
                    rankInfoContent += `
                        <div class="col-md-6">
                            <div class="card mx-auto mb-4" style="width: 18rem;">
                                <img src="${currentRankImage}" class="card-img-top" alt="Rank Image">
                                <div class="card-body">
                                    <h5 class="card-title">Current Rank</h5>
                                    <p class="card-text">${currentRank}</p>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    rankInfoContent += `<p class="col-12 text-center text-danger">Error: Rank data not found in API response</p>`;
                }

                if (data.highest_rank && data.highest_rank_image) {
                    const highestRank = data.highest_rank;
                    const highestRankImage = data.highest_rank_image;
                    rankInfoContent += `
                        <div class="col-md-6">
                            <div class="card mx-auto mb-4" style="width: 18rem;">
                                <img src="${highestRankImage}" class="card-img-top" alt="Highest Rank Image">
                                <div class="card-body">
                                    <h5 class="card-title">Highest Rank</h5>
                                    <p class="card-text">${highestRank}</p>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    rankInfoContent += `<p class="col-12 text-center text-danger">Error: Highest rank data not found in API response</p>`;
                }

                document.getElementById("rankInfo").innerHTML = rankInfoContent;

            } catch (error) {
                console.error("Error fetching data:", error);
                document.getElementById("rankInfo").innerHTML = `<p class="col-12 text-center text-danger">Error fetching data. Please try again later.</p>`;
            }
        }
    </script>

</body>

</html>