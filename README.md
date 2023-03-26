# PHP-Web-Scrapper
This a PHP web scrapper for a website consisting of Articles

The script performs the following:
- Reads the headline, gets the link of the article, the author, and the date of each of the articles
found on "theverge.com"
- Stores these in a CSV file titled `ddmmyyy_verge.csv`, with the following header `id, URL,
headline, author, date`.
- Creates an SQLite database to store the same data, and make sure that the id is the primary
key
- You can run this script on a cloud service (preferably AWS)

