// Fetching all 3-letter English words from a public API
fetch('https://api.allorigins.win/raw?url=https://www.wordexample.com/list/3-letter-words').then(response => response.text()).then(data => {
    const wordList = data.match(/[a-z]{3}/g);
    const botWords = [...new Set(wordList)]; // Remove duplicates
    let botWord = getRandomBotWord();
    let userWord;
    let timer;

    const botWordElement = document.getElementById('bot-word');
    const userWordInput = document.getElementById('user-word');
    const submitButton = document.getElementById('submit');
    const statusElement = document.getElementById('status');

    botWordElement.textContent = `Bot's word: ${botWord}`;

    submitButton.addEventListener('click', () => {
        userWord = userWordInput.value.trim().toLowerCase();
        if (userWord.length !== 3) {
            setStatus('Please enter a 3-letter word.');
            return;
        }
        if (!isValidWord(userWord)) {
            setStatus('Please enter a valid word.');
            return;
        }
        clearInterval(timer);
        if (!isWordValid(userWord, botWord)) {
            setStatus('You lose! Bot wins.');
            return;
        }
        setStatus('Bot\'s turn...');
        botTurn();
    });

    function botTurn() {
        botWord = getBotResponseWord(userWord);
        botWordElement.textContent = `Bot's word: ${botWord}`;
        timer = setTimeout(() => {
            setStatus('You win! Bot failed to respond.');
            clearInterval(timer);
        }, 60000); // 1 minute timer for bot's turn
    }

    function setStatus(message) {
        statusElement.textContent = message;
    }

    function getRandomBotWord() {
        return botWords[Math.floor(Math.random() * botWords.length)];
    }

    function isValidWord(word) {
        return /^[a-z]+$/.test(word) && word.length === 3 && botWords.includes(word);
    }

    function isWordValid(word, prevWord) {
        const differences = [...word].reduce((acc, char, i) => acc + (char !== prevWord[i]), 0);
        return differences === 1;
    }

    function getBotResponseWord(prevUserWord) {
        let newBotWord;
        for (let i = 2; i >= 0; i--) {
            newBotWord = prevUserWord.slice(0, i) + getRandomLetter() + prevUserWord.slice(i + 1);
            if (isWordValid(newBotWord, prevUserWord)) {
                return newBotWord;
            }
        }
        // Fallback: If no valid word found, return a random word from the bot's word list
        return getRandomBotWord();
    }

    function getRandomLetter() {
        const alphabet = 'abcdefghijklmnopqrstuvwxyz';
        return alphabet[Math.floor(Math.random() * alphabet.length)];
    }
});
