<?php
/**
 * Database module containing user database related function for this project
 *
 * @author Reishandy (isthisruxury@gmail.com)
 */
require_once "../../src/config/config.php";
require_once "../../src/session/session.php";
require_once "../../src/security/encryption.php";

/**
 * Function to create a new user and insert it into the database
 *
 * This function will create a new user from the inputted username and password, the newly created user will have an
 * AES-256 main key and also salt and iv for securing the main key. The data that will be inserted into the database are
 * username, password hashed with argon2id, main key encrypted with encryptKey(), salt and iv for decrypting the main key.
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

    $key = generateKey();
    $encryptedKeyArray = encryptKey($key, $password);

    $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
    $encryptedKey = $encryptedKeyArray[0];
    $salt = $encryptedKeyArray[1];
    $iv = $encryptedKeyArray[2];

    $statement = $dbh->prepare("INSERT INTO users (username, password, main_key, salt, iv) VALUES (?, ?, ?, ?, ?)");
    $statement->bind_param("sssss", $username, $hashedPassword, $encryptedKey, $salt, $iv);

    $statement->execute();
    $dbh->close();
    return 0;
}

/**
 *
 */
function getUser(string $username, string $password): int
{
    // TODO: init database connection

    // TODO: query all username and id
    //      - search username parameter in query list
    //      - return 1 if not found
    //      - if found continue and save the id

    // TODO: get all data from saved id
    //      - only one row

    // TODO: verify parameter password with hashed password form database
    //      - use hash_verify()
    //      - if doesn't match, return 2 for wrong password
    //      - else continue

    // TODO: decrypt the main key
    //      - use decryptKey() with salt and iv straight from database

    // TODO: store key and username to session
    //      - store to session with session.php storeKey()

    // TODO: close database connection
    return 0;
}

// TODO: remove test
$res = addUser("Rei", "Rei");
if ($res == 0) {
    echo "success";
} elseif ($res == 1) {
    echo "username taken";
} elseif ($res == -1) {
    echo "database not connected";
}

echo "<br><hr><br>";
