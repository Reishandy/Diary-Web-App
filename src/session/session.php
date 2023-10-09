<?php
/**
 * Session module to handle any session-related request
 *
 * @author Reishandy (isthisruxury@gmail.com)
 */
require_once "../../src/config/config.php";

/**
 * Function to set login timestamp for session timeout purposes
 *
 * @author Reishandy (isthisruxury@gmail.com)
 */
function setTimeStamp(): void
{
    $_SESSION["login_time_stamp"] = time();
}

/**
 * Function to check if the current session is active or not
 *
 * This function uses "username" and "key" session variable to check if the session is active by using the isset()
 * function that returns true if both variables are set else it will return false
 *
 * @return bool Return true if session is active and false if not
 * @author Reishandy (isthisruxury@gmail.com)
 */
function checkSession(): bool
{
    if (isset($_SESSION["username"]) && isset($_SESSION["key"]) && isset($_SESSION["login_time_stamp"])) {
        if (time() - $_SESSION["login_time_stamp"] > TIMEOUT) {
            destroySession();
            return false;
        } else {
            return true;
        }
    } else {
        destroySession();
        return false;
    }
}

/**
 * Function to unset variables and destroy current session
 *
 * @author Reishandy (isthisruxury@gmail.com)
 */
function destroySession(): void
{
    session_start();
    session_unset();
    session_destroy();
}

/**
 * Function to store the main key into session variable
 *
 * @param string $key Key to store must be in the form of bytes
 * @author Reishandy (isthisruxury@gmail.com)
 */
function storeKey(string $key): void
{
    setTimeStamp();
    $_SESSION["key"] = $key;
}

/**
 * Function to get the main key from session variable
 *
 * @return string Returns the main key from session variable in the form of string bytes
 * @author Reishandy (isthisruxury@gmail.com)
 */
function getKey(): string
{
    setTimeStamp();
    return $_SESSION["key"];
}

/**
 * Function to store username into session variable
 *
 * @param string $username Username to store
 * @author Reishandy (isthisruxury@gmail.com)
 */
function storeUsername(string $username): void
{
    setTimeStamp();
    $_SESSION["username"] = $username;
}

/**
 * Function to get username from session variable
 *
 * @return string Returns username from session variable
 * @author Reishandy (isthisruxury@gmail.com)
 */
function getUsername(): string
{
    setTimeStamp();
    return $_SESSION["username"];
}