# Valorant Stats Tracker

<img width="1919" height="1079" alt="image" src="https://github.com/user-attachments/assets/b605d820-970b-44c1-a708-a0fd086c6c92" />

A comprehensive web application for tracking and displaying Valorant player statistics, rankings, and leaderboards. Built with PHP and powered by the HenrikDev Valorant API.

## Features

### 🎮 Player Statistics
- **Detailed Player Profiles**: Search for any player by name and tag
- **Current Rank Display**: View current competitive rank, Elo, and RR progress
- **Career Highlights**: See highest rank achieved and seasonal performance
- **Match History**: Browse recent competitive matches with detailed statistics
- **Performance Metrics**: Track K/D ratio, headshot percentage, and more

### 🏆 Leaderboards
- **Regional Leaderboards**: View top players across all regions (EU, NA, AP, KR, LATAM, BR)
- **Real-time Rankings**: Up-to-date leaderboard data
- **Player Comparison**: Easy access to top players' profiles

### 🎨 Beautiful UI
- **Valorant-themed Design**: Dark blue theme matching the game's aesthetic
- **Responsive Layout**: Works seamlessly on desktop and mobile devices
- **Modern Interface**: Clean, intuitive navigation and card-based layouts

## Tech Stack

- **Backend**: PHP
- **API**: HenrikDev Valorant API
- **Frontend**: HTML5, CSS3, JavaScript
- **Styling**: Custom CSS with dark theme

## Setup Instructions

### Prerequisites
- PHP 7.4 or higher
- cURL extension enabled
- Web server (Apache/Nginx) or PHP built-in server
- API key from HenrikDev (included in config)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/ValorantTracker.git
   cd ValorantTracker
   ```

2. **Configure API Key**
   - Open `config.php`
   - The API key is already included in the configuration
   - (Optional) Replace with your own API key from [HenrikDev](https://docs.henrikdev.xyz/)

3. **Start the server**
   
   **Using PHP built-in server:**
   ```bash
   php -S localhost:8000
   ```
   
   **Or use your preferred web server setup**

4. **Access the application**
   - Open your browser and navigate to `http://localhost:8000`

## File Structure

```
ValorantTracker/
├── api_handler.php      # API interaction functions
├── config.php           # Configuration and API key
├── index.php            # Home page
├── player_stats.php     # Player statistics page
├── leaderboard.php      # Leaderboard page
├── style.css            # Styling and themes
├── background.jpg       # Background image
└── README.md           # This file
```

## Usage

### Searching for Player Stats

1. Navigate to the "Player Stats" page
2. Enter player name in the format: `PlayerName#TAG`
3. Click "Search Stats" to view:
   - Current rank and Elo
   - Highest rank achieved
   - Seasonal performance
   - Recent match history
   - Performance statistics

### Viewing Leaderboards

1. Go to the "Leaderboard" page
2. Select your desired region from the dropdown
3. View the top players with their rankings and statistics

## API Credits

This project uses the [HenrikDev Valorant API](https://docs.henrikdev.xyz/). Special thanks to HenrikDev for providing this excellent API service.

## Features in Detail

### Player Stats Page
- **Overview Section**:
  - Player card with avatar
  - Current rank with badge
  - Elo and RR progress
  - Highest rank achieved
  - Seasonal performance breakdown
  - Current act statistics (K/D ratio, headshot percentage)

- **Match History Section**:
  - Recent competitive matches
  - Match details (map, date, result)
  - Personal performance (K/D/A)
  - Team composition and performance

### Leaderboard Page
- Regional filtering (EU, NA, AP, KR, LATAM, BR)
- Top players with rankings
- Ranked rating (RR)
- Number of wins
- Competitive tier

## Browser Compatibility

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Contributing

Contributions are welcome! Feel free to submit issues or pull requests.

## License

This project is open source and available under the MIT License.

## Acknowledgments

- HenrikDev for the Valorant API
- Riot Games for Valorant
- The Valorant community

---

**Note**: This application uses the HenrikDev API for data retrieval. Make sure to comply with the API's rate limits and terms of service.
