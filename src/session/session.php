<?php
/**
 * Session module to handle any session-related request
 *
 * @author Reishandy (isthisruxury@gmail.com)
 */
session_start();

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
    session_start();
    if (isset($_SESSION["username"]) && isset($_SESSION["key"])) {
        return true;
    } else {
        return false;
    }
}

/**
 * Function to store the main key into session variable
 *
 * @param string $key Key to store must be in the form of bytes
 * @author Reishandy (isthisruxury@gmail.com)
 */
function storeKey(string $key): void
{
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
    return $_SESSION["username"];
}