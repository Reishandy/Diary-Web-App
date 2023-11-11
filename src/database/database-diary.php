<?php
/**
 * Database module containing diary database related function for this project
 *
 * @author Reishandy (isthisruxury@gmail.com)
 */
require_once "../../src/config/config.php";
require_once "../../src/security/encryption.php";
require_once "../../src/session/session.php";

/**
 * Function to add a new diary entry into the database
 *
 * This function will add a new diary entry into the database. The data that will be inserted into the database are
 * date, date_modified, content, mood, tags, and iv. The content, mood, and tags will be encrypted with encryptDiary()
 * function and the iv will be generated with generateSecondaryKey() function. The date and date_modified will be
 * generated with date() function.
 *
 * @param string $content Content of the diary entry
 * @param string $mood Mood of the diary entry
 * @param string $tags Tags of the diary entry
 * @return int Return status indication => 0: success, -1: database not connected
 * @author Reishandy (isthisruxury@gmail.com)
 */
function addDiary(string $content, string $mood, string $tags): int
{
    // Handle connection error
    try {
        $dbh = new mysqli(HOSTNAME, DB_USERNAME, DB_PASSWORD, DATABASE);
    } catch (mysqli_sql_exception) {
        return -1;
    }

    // Prepare session variables to access the database and encrypt the content
    session_start();
    $username = getUsername();
    $key = getKey();

    // Encrypt the content
    $encryptedContentArray = encryptDiary($key, $content, $mood, $tags);
    $encryptedContent = $encryptedContentArray[0];
    $encryptedMood = $encryptedContentArray[1];
    $encryptedTags = $encryptedContentArray[2];
    $iv = $encryptedContentArray[3];

    // Prepare date
    $date = date("Y-m-d");
    $date_modified = date("Y-m-d H:i:s");

    // Insert into database
    $statement = $dbh->prepare("INSERT INTO $username (date, date_modified, content, mood, tags, iv) VALUES (?, ?, ?, ?, ?, ?)");
    $statement->bind_param("ssssss", $date, $date_modified, $encryptedContent, $encryptedMood, $encryptedTags, $iv);
    $statement->execute();

    $dbh->close();
    return 0;
}

/**
 * Function to update a diary entry in the database
 *
 * This function will update a diary entry in the database. The data that will be updated are content, mood, tags, and
 * iv. The content, mood, and tags will be encrypted with encryptDiary() function and the iv will be generated with
 * generateSecondaryKey() function. The date_modified will be generated with date() function. The id will be used to
 * identify which diary entry to update.
 *
 * @param int $id Diary entry id
 * @param string $content Content of the diary entry to be updated
 * @param string $mood Mood of the diary entry to be updated
 * @param string $tags Tags of the diary entry to be updated
 * @return int Return status indication => 0: success, 1: diary entry does not exist, -1: database not connected
 * @author Reishandy (isthisruxury@gmail.com)
 */
function modifyDiary(int $id, string $content, string $mood, string $tags): int
{
    // Handle connection error
    try {
        $dbh = new mysqli(HOSTNAME, DB_USERNAME, DB_PASSWORD, DATABASE);
    } catch (mysqli_sql_exception) {
        return -1;
    }

    // Prepare session variables to access the database and encrypt the content
    session_start();
    $username = getUsername();
    $key = getKey();

    // Check if the diary entry exists
    $checkStatement = $dbh->prepare("SELECT * FROM $username WHERE id = ?");
    $checkStatement->bind_param("i", $id);

    $checkStatement->execute();
    $checkResult = $checkStatement->get_result();

    // If the result does not exist, return 1 to signal the diary entry does not exist
    if ($checkResult->num_rows <= 0) {
        $dbh->close();
        return 1;
    }

    // Encrypt the content
    $encryptedContentArray = encryptDiary($key, $content, $mood, $tags);
    $encryptedContent = $encryptedContentArray[0];
    $encryptedMood = $encryptedContentArray[1];
    $encryptedTags = $encryptedContentArray[2];
    $iv = $encryptedContentArray[3];

    // Prepare date
    $date_modified = date("Y-m-d H:i:s");

    // Update the database
    $statement = $dbh->prepare("UPDATE $username SET date_modified = ?, content = ?, mood = ?, tags = ?, iv = ? WHERE id = ?");
    $statement->bind_param("sssssi", $date_modified, $encryptedContent, $encryptedMood, $encryptedTags, $iv, $id);
    $statement->execute();

    $dbh->close();
    return 0;
}

/**
 * Function to get all diary entries from the database
 *
 * This function will get all diary entries from the database. The data that will be returned are id, date,
 * date_modified, content, mood, and tags. The content, mood, and tags will be decrypted with decryptDiary() function.
 * The id, date, and date_modified will be returned as is. The return value will be a 2D array where the first entry is
 * the status indicator, and the rest is the diaries in the form of dictionary.
 *
 * @return array [0] => Return status indication => 0: success, 1: results are empty, -1: database not connected,
 *  [1] => [id, date, date_modified, content, mood, tags]
 *  [...] => [other entries' dictionary]
 * @author Reishandy (isthisruxury@gmail.com)
 */
function getDiaries(): array
{
    // Init return array. 2D array where the first entry is the status indicator,
    // and the rest is the diaries in the form of dictionary
    $diaries = [];

    // Handle connection error
    try {
        $dbh = new mysqli(HOSTNAME, DB_USERNAME, DB_PASSWORD, DATABASE);
    } catch (mysqli_sql_exception) {
        $diaries[0] = -1;
        return $diaries;
    }

    // Prepare session variables to access the database and encrypt the content
    session_start();
    $username = getUsername();
    $key = getKey();

    // Get all diary entries from the database
    $statement = $dbh->prepare("SELECT * FROM $username");
    $statement->execute();
    $result = $statement->get_result();

    // If the result is empty, set the status indicator to one and return
    if ($result->num_rows <= 0) {
        $diaries[0] = 1;
        return $diaries;
    } else {
        $diaries[0] = 0;
    }

    // Iterate through the result and decrypt the content
    while ($row = $result->fetch_assoc()) {
        $id = $row["id"];
        $date = $row["date"];
        $date_modified = $row["date_modified"];
        $encryptedContent = $row["content"];
        $encryptedMood = $row["mood"];
        $encryptedTags = $row["tags"];
        $iv = $row["iv"];

        $decryptedDiaryArray = decryptDiary($key, $encryptedContent, $encryptedMood, $encryptedTags, $iv);
        $content = $decryptedDiaryArray[0];
        $mood = $decryptedDiaryArray[1];
        $tags = $decryptedDiaryArray[2];

        $diaries[] = [
            "id" => $id,
            "date" => $date,
            "date_modified" => $date_modified,
            "content" => $content,
            "mood" => $mood,
            "tags" => $tags
        ];
    }

    $dbh->close();
    return $diaries;
}

/**
 * Function to delete a diary entry from the database
 *
 * @param string $id Diary entry id
 * @return int Return status indication => 0: success, 1: diary entry does not exist, -1: database not connected
 * @author Reishandy (isthisruxury@gmail.com)
 */
function deleteDiary(string $id): int
{
    // Handle connection error
    try {
        $dbh = new mysqli(HOSTNAME, DB_USERNAME, DB_PASSWORD, DATABASE);
    } catch (mysqli_sql_exception) {
        return -1;
    }

    // Prepare session variables to access the database and encrypt the content
    session_start();
    $username = getUsername();

    // Check if the diary entry exists
    $checkStatement = $dbh->prepare("SELECT * FROM $username WHERE id = ?");
    $checkStatement->bind_param("i", $id);

    $checkStatement->execute();
    $checkResult = $checkStatement->get_result();

    // If the result does not exist, return 1 to signal the diary entry does not exist
    if ($checkResult->num_rows <= 0) {
        $dbh->close();
        return 1;
    }

    // Delete the diary entry
    $statement = $dbh->prepare("DELETE FROM $username WHERE id = ?");
    $statement->bind_param("i", $id);
    $statement->execute();

    $dbh->close();
    return 0;
}