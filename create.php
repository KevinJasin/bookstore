<?php

require_once("connect.php");

$stmt = $pdo->query('SELECT * FROM authors');

if (isset($_POST["submit"])) {
    $title = $_POST["title"];
    $price = $_POST["price"];
    $type = $_POST["type"];
    $language = $_POST["language"];
    $pages = $_POST["pages"];

    // ✅ Extract only the year for YEAR column
    if (!empty($_POST["release_date"]) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST["release_date"])) {
        $release_date = date('Y', strtotime($_POST["release_date"]));
    } else {
        die("❌ Invalid release date. Please use YYYY-MM-DD format.");
    }

    $author_id = $_POST["author_id"];

    if ($author_id === "new") {
        $author_first_name = $_POST["author_first_name"];
        $author_last_name = $_POST["author_last_name"];

        $stmt = $pdo->prepare('INSERT INTO authors (first_name, last_name) VALUES (:first_name, :last_name)');
        $stmt->execute(['first_name' => $author_first_name, 'last_name' => $author_last_name]);

        $author_id = $pdo->lastInsertId();
    }

    $stmt = $pdo->prepare('INSERT INTO books (title, release_date, price, type, language, pages) 
                           VALUES (:title, :release_date, :price, :type, :language, :pages)');
    $stmt->execute([
        'title' => $title,
        'release_date' => $release_date,
        'price' => $price,
        'type' => $type,
        'language' => $language,
        'pages' => $pages
    ]);

    $book_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare('INSERT INTO book_authors (book_id, author_id) VALUES (:book_id, :author_id)');
    $stmt->execute(['book_id' => $book_id, 'author_id' => $author_id]);

    header("Location: index.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Book</title>
</head>
<body>
    <h1>Create Book</h1>

    <form method="POST">
        <label for="title">Title:</label><br>
        <input type="text" name="title"><br><br>

        <label for="release_date">Release Date:</label><br>
        <input type="date" name="release_date" required><br><br>

        <label for="price">Price:</label><br>
        <input type="number" name="price" step="0.01"><br><br>

        <label for="type">Type:</label><br>
        <select name="type">
            <option value="ebook">Ebook</option>
            <option value="used">Used</option>
            <option value="new">New</option>
        </select><br><br>

        <label for="language">Language:</label><br>
        <select name="language">
            <option value="English">English</option>
            <option value="Estonian">Estonian</option>
            <option value="Other">Other</option>
        </select><br><br>

        <label for="pages">Pages:</label><br>
<input type="number" name="pages"><br><br>

        <label for="author_id">Author:</label><br>
        <select name="author_id">
            <option value="new">Create New Author</option>
<?php while ($row = $stmt->fetch()): ?>
            <option value="<?= $row["id"]; ?>"><?= $row["first_name"]; ?> <?= $row["last_name"]; ?></option>
<?php endwhile; ?>
        </select><br><br>

        <div id="new-author-fields" style="display: none;">
            <label for="author_first_name">Author First Name:</label><br>
            <input type="text" name="author_first_name"><br><br>

            <label for="author_last_name">Author Last Name:</label><br>
            <input type="text" name="author_last_name"><br><br>
        </div>

        <input type="submit" name="submit" value="Create">
    </form>

    <a href="index.php">Go Back</a>

    <script>
        const authorSelect = document.querySelector('select[name="author_id"]');
        const newAuthorFields = document.querySelector('#new-author-fields');

        authorSelect.addEventListener('change', () => {
            newAuthorFields.style.display = authorSelect.value === 'new' ? 'block' : 'none';
        });
    </script>
</body>
</html>
