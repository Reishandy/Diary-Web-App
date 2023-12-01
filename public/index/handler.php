<?php
/**
 * This is the web app's hub
 *
 * This handler.php file will do all the logic changing and the entire program's flow, it can redirect and handle all
 * interpret communication and action
 *
 * @author Reishandy (isthisruxury@gmail.com)
 */

use JetBrains\PhpStorm\NoReturn;

require_once "../../src/database/database-user.php";
require_once "../../src/database/database-diary.php";
require_once "../../src/session/session.php";
require_once "../../src/config/config.php";

if (session_status() == PHP_SESSION_DISABLED) {
    ini_set("session.gc_maxlifetime", TIMEOUT);
    session_set_cookie_params(0);
    session_start();
}

// Set timezone to Makassar (can be changed)
date_default_timezone_set("Asia/Makassar");

/**
 * Main hub, used to handle action redirection and session checker
 */
$action = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST["action"];
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET["action"];
}

switch ($action) {
    case "login":
        loginHandler();
        break;
    case "register":
        registerHandler();
        break;
    case "add":
        addHandler();
        break;
    case "modify":
        modifyHandler();
        break;
    case "delete":
        deleteHandler();
        break;
    case "":
        session();
        break;
    default:
        // TODO: redirect to 404 page
        echo "404";
}


/**
 * Action handler, collection of functions used to handle specific action
 */
/**
 * Function to handle login action
 *
 * This function will handle the login action by getting the username and password from the POST request, then it will
 * call the getUser() function from database-user.php to check if the username and password are correct or not.
 * If the username and password are correct.
 * Then it will redirect to the main menu, else it will redirect back to
 * log in page within an error message as the parameter to indicate the error type (username or password) and then
 * exit the program.
 * If the database is not connected, then it will call the databaseNotConnectedHandler() function to handle the error.
 *
 * @return void
 * @author Reishandy (isthisruxury@gmail.com)
 */
function loginHandler(): void
{
    $username = $_POST["username"];
    $password = $_POST["password"];

    $status = getUser($username, $password);

    switch ($status) {
        case 0:
            echo getUsername();
            header("Location: ../../public/main/main-prototype.php");
            break;
        case 1:
            header("Location: ../../public/authentication/auth.php?login=true&login_username_error=Cannot find user");
            exit();
        case 2:
            header("Location: ../../public/authentication/auth.php?login=true&login_password_error=Wrong password");
            exit();
        case -1:
            databaseNotConnectedHandler();
    }
}

/**
 * Function to handle register action
 *
 * This function will handle the register action by getting the username, password, and re-password from the POST
 * request, then it will call the addUser() function from database-user.php to add the user to the database.
 * If the username is already taken, then it will redirect back to register page within an error message as the
 * parameter to indicate the error type (username) and then exit the program. If the password is less than eight characters,
 * then it will redirect back to register page within an error message as the parameter to indicate the error type
 * (password) and then exit the program. If the re-password does not match with the password, then it will redirect back
 * to register page within an error message as the parameter to indicate the error type (re-password) and then exit the
 * program. If the database is not connected, then it will call the databaseNotConnectedHandler() function to handle the
 * error.
 *
 * @return void
 * @author Reishandy (isthisruxury@gmail.com)
 */
function registerHandler(): void
{
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $rePassword = filter_input(INPUT_POST, 're-password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (strlen($username) < 3) {
        header("Location: ../../public/authentication/auth.php?register_username_error=Must be more than 3 character");
        exit();
    }

    if (!preg_match("/^[a-zA-Z_]*$/", $username)) {
        header("Location: ../../public/authentication/auth.php?register_username_error=Username must be alphabetic and only contain underscore");
        exit();
    }

    if (strlen($password) < 8) {
        header("Location: ../../public/authentication/auth.php?register_password_error=Must be more than 8 character");
        exit();
    }

    if ($rePassword != $password) {
        header("Location: ../../public/authentication/auth.php?register_re-password_error=Password does not match");
        exit();
    }

    $status = addUser($username, $password);

    switch ($status) {
        case 0:
            header("Location: ../../public/authentication/auth.php?login=true");
            exit();
        case 1:
            header("Location: ../../public/authentication/auth.php?register_username_error=Username already taken");
            exit();
        case -1:
            databaseNotConnectedHandler();
    }
}

/**
 * Prototype
 */
function addHandler(): void
{
    $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $mood = filter_input(INPUT_POST, 'mood', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $tags = filter_input(INPUT_POST, 'tags', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $status = addDiary($content, $mood, $tags);

    switch ($status) {
        case 0:
            header("Location: ../../public/main/main-prototype.php");
            break;
        case -1:
            databaseNotConnectedHandler();
    }
}

function modifyHandler(): void
{
    $id = $_POST["id"];
    $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $mood = filter_input(INPUT_POST, 'mood', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $tags = filter_input(INPUT_POST, 'tags', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $status = modifyDiary($id, $content, $mood, $tags);

    switch ($status) {
        case 0:
            header("Location: ../../public/main/main-prototype.php");
            break;
        case -1:
            databaseNotConnectedHandler();
    }
}

function deleteHandler(): void
{
    $id = $_POST["id"];

    $status = deleteDiary($id);

    switch ($status) {
        case 0:
            header("Location: ../../public/main/main-prototype.php");
            break;
        case -1:
            databaseNotConnectedHandler();
    }
}


/**
 * Utility functions, collection of functions to support the main hub or action handler
 */
/**
 * Function to handle a database not connected error
 *
 * This function will handle the database not connected error by displaying a message and then exit the program.
 *
 * @return void
 * @author Reishandy (isthisruxury@gmail.com)
 */
#[NoReturn] function databaseNotConnectedHandler(): void
{
    die("<h1 style='text-align: center'>DATABASE NOT CONNECTED</h1>");
}

/**
 * Function to check if the current session is active or not
 *
 * This function uses "username" and "key" session variable to check if the session is active by using the checkSession()
 * function that returns true if both variables are set else it will return false. If the session is not active, then it
 * will destroy the current session and redirect to the login page.
 *
 * @return void
 * @author Reishandy (isthisruxury@gmail.com)
 */
function session(): void
{
    if (!checkSession()) {
        destroySession();
        header("Location: ../../public/authentication/auth.php");
        exit();
    }
}
