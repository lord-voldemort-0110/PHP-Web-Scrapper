<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Include the simple_html_dom library
include('simple_html_dom.php');

// Set up database
$db = new SQLite3('articles.db');
$db->exec('CREATE TABLE IF NOT EXISTS articles (id INTEGER PRIMARY KEY AUTOINCREMENT, URL TEXT, headline TEXT, author TEXT, date TEXT)');

// Get today's date for filename
$date = date('dmY');

// Open CSV file for writing
$csv_file = fopen("$date\_verge.csv", 'w');
fputcsv($csv_file, array('id', 'URL', 'headline', 'author', 'date'));

// Get HTML content from The Verge
//$html = file_get_html('https://www.google.com/');
//echo $html->find('title', 0)->plaintext;
//exit;
$html = file_get_html('https://www.google.com/');
if (!$html) {
    echo "Failed to retrieve HTML content\n";
    exit;
}
// Find all article links
$article_links = $html->find('a.c-entry-box--compact__image-wrapper');

// Loop through each article
foreach ($article_links as $link) {
    // Get article URL and HTML content
    $url = $link->href;
    $article_html = file_get_html($url);

    // Get article title
    // Check if article HTML content was retrieved successfully
    if (!$article_html) {
        echo "Failed to retrieve article HTML content: $url\n";
        continue;
    }
    
    // Get article title
    $title_element = $article_html->find('h1.c-page-title', 0);
    if ($title_element) {
        $title = $title_element->plaintext;
    } else {
        echo "Failed to retrieve article title: $url\n";
        continue;
    }
    
    // Get article author
    $author_element = $article_html->find('a.c-byline__author', 0);
    if ($author_element) {
        $author = $author_element->plaintext;
    } else {
        echo "Failed to retrieve article author: $url\n";
        continue;
    }
    
    // Get article date
    $date_element = $article_html->find('time.c-byline__item', 0);
    if ($date_element) {
        $date = $date_element->datetime;
    } else {
        echo "Failed to retrieve article date: $url\n";
        continue;
    }
    // Write to CSV file
    fputcsv($csv_file, array(null, $url, $title, $author, $date));

    // Check if the article already exists in the database
    $stmt = $db->prepare('SELECT COUNT(*) FROM articles WHERE URL = :url');
    $stmt->bindValue(':url', $url);
    $result = $stmt->execute()->fetchArray(SQLITE3_NUM)[0];

    // If the article does not exist in the database, insert it
    if ($result === 0) {
        $stmt = $db->prepare('INSERT INTO articles (URL, headline, author, date) VALUES (:url, :title, :author, :date)');
        $stmt->bindValue(':url', $url);
        $stmt->bindValue(':title', $title);
        $stmt->bindValue(':author', $author);
        $stmt->bindValue(':date', $date);
        $stmt->execute();
    }
}

// Close CSV file
fclose($csv_file);

// Close database connection
$db->close();

// Test cases
// 1. Check if CSV file exists
if (!file_exists("$date\_verge.csv")) {
    echo "CSV file not found\n";
}

// 2. Check if CSV file is not empty
if (filesize("$date\_verge.csv") == 0) {
    echo "CSV file is empty\n";
}

// 3. Check if database exists
if (!file_exists('articles.db')) {
    echo "Database not found\n";
}
// 4. Check if database is not empty
$db = new SQLite3('articles.db');
$result = $db->query('SELECT COUNT(*) FROM articles')->fetchArray(SQLITE3_NUM)[0];
if ($result == 0) {
    echo "Database is empty\n";
}

?>
