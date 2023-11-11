<?php
/**
 * Database module containing user database related function for this project
 *
 * @author Reishandy (isthisruxury@gmail.com)
 */
require_once "../../src/config/config.php";
require_once "../../src/security/encryption.php";
require_once "../../src/session/session.php";

/**
 * Function to create a new user and insert it into the database
 *
 * This function will create a new user from the inputted username and password, the newly created user will have an
 * AES-256 main key and also salt and iv for securing the main key. The data that will be inserted into the database are
 * username, password hashed with argon2id, main key encrypted with encryptKey(), salt and iv for decrypting the main key.
 * After that is done, this function will create a new table for the user to store diary entries.
 *
 * @param string $username Username for the new user
 * @param string $password Password for the new user
 * @return int Return status indication => 0: success, 1: username already taken, -1: database not connected
 * @author Reishandy (isthisruxury@gmail.com)
 */
function addUser(string $username, string $password): int
{
    // Handle connection error
    try {
        $dbh = new mysqli(HOSTNAME, DB_USERNAME, DB_PASSWORD, DATABASE);
    } catch (mysqli_sql_exception) {
        return -1;
    }

    // Check if the username already exists by querying the database with inputted username
    $checkStatement = $dbh->prepare("SELECT * FROM users WHERE username = ?");
    $checkStatement->bind_param("s", $username);

    $checkStatement->execute();
    $checkResult = $checkStatement->get_result();

    // If the result exists (more than 1) then return 1 to signal the username is already taken
    if ($checkResult->num_rows > 0) {
        $dbh->close();
        return 1;
    }

    // Create key
    $key = generateKey();
    $encryptedKeyArray = encryptKey($key, $password);

    // Prepare data to be inserted into database
    $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
    $encryptedKey = $encryptedKeyArray[0];
    $salt = $encryptedKeyArray[1];
    $iv = $encryptedKeyArray[2];

    // Insert into database
    $statement = $dbh->prepare("INSERT INTO users (username, password, main_key, salt, iv) VALUES (?, ?, ?, ?, ?)");
    $statement->bind_param("sssss", $username, $hashedPassword, $encryptedKey, $salt, $iv);
    $statement->execute();

    // Create user's specific table for storing diary entries
    $tableStatement = $dbh->prepare("CREATE TABLE $username (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        date DATE NOT NULL,
        date_modified DATETIME NOT NULL,
        content TEXT NOT NULL,
        mood TEXT NOT NULL,
        tags TEXT NOT NULL,
        iv VARCHAR(24) NOT NULL
    )");
    $tableStatement->execute();

    $dbh->close();
    return 0;
}

/**
 * Function to authenticate user, get user data, and store user data to session
 *
 * This function is used to authenticate user by first checking if a user exists inside the database then verifying the
 * password with a stored and hashed password form database. Then this function will get and decrypt the main key with
 * the provided salt, iv, and password.
 * After that is done, the main key and username will be stored inside the current session.
 *
 * @param string $username User's username
 * @param string $password User's password
 * @return int Return status indication => 0: success, 1: user not found, 2: wrong password, -1: database not connected
 * @author Reishandy (isthisruxury@gmail.com)
 */
function getUser(string $username, string $password): int
{
    // Handle connection error
    try {
        $dbh = new mysqli(HOSTNAME, DB_USERNAME, DB_PASSWORD, DATABASE);
    } catch (mysqli_sql_exception) {
        return -1;
    }

    // Get user data from querying the username in database
    $statement = $dbh->prepare("SELECT * FROM users WHERE username = ?");
    $statement->bind_param("s", $username);

    $statement->execute();
    $result = $statement->get_result();

    // If there is a result, get the user data. If not, return 1 to signal that the user is not found
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $hashedPassword = $row["password"];
        $encryptedMainKey = $row["main_key"];
        $salt = $row["salt"];
        $iv = $row["iv"];

        $dbh->close();
    } else {
        $dbh->close();
        return 1;
    }

    // Check if the password matches, if not returns 2 to signal the password is not correct
    if (!password_verify($password, $hashedPassword)) {
        return 2;
    }

    // Decrypt the main key
    $mainKey = decryptKey($password, $encryptedMainKey, $salt, $iv);

    // Store key and username
    session_start();
    storeKey($mainKey);
    storeUsername($username);

    return 0;
}