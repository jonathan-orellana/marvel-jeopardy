# 🦸 Marvel Jeopardy Game

A Marvel-themed Jeopardy-style trivia game built for **CS4640 – Programming Languages (Fall 2025)**.  
Players can register, create custom game boards, or play existing ones. Some trivia is pulled dynamically from the **Marvel API**, making each playthrough unique.

---

## 📖 Project Overview
- **Game Mode**: Jeopardy-style questions with Marvel categories.
- **Features**:
  - User registration and login
  - Create or join existing Jeopardy boards
  - Play solo or multiplayer
  - Fetch data from the official **Marvel API**
- **Tech Stack**:
  - **Frontend**: HTML, CSS, JavaScript
  - **Backend**: PHP (required by course setup)
  - **Database**: SQLite (lightweight, portable)  
  - **Containerization**: Docker for consistent deployment

---

## 🚀 Getting Started

### 1. Clone the Repository
```bash
git clone https://github.com/<your-username>/marvel-jeopardy-game.git
cd marvel-jeopardy-game

```
---

## 🔑 Environment Setup

This project requires a **Marvel API key** to fetch character and trivia data.  
We use an environment variable so the key is not hard-coded into the source code.

### 1. Get an API Key
- Create a free account at [developer.marvel.com](https://developer.marvel.com/).
- Generate a new **API Key**.

### 2. Create a `.env` File
In the **root of the project**, create a file named `.env` (with no filename, just `.env`).  
Inside, add your key:

```bash
MARVEL_API_KEY=your_real_api_key_here
```

⚠️ Do not commit this `.env` file to GitHub. It should stay private.

### 3. Install Dependencies
```bash
npm install
npm start
```

Visit the app at: `http://localhost:3000`

---

## 👨‍💻 Contributors

Carlos Orellana

David Nu Nu

---

## 📜 License

This project is for educational purposes under CS4640 at the University of Virginia.
Not affiliated with or endorsed by Marvel Entertainment.


