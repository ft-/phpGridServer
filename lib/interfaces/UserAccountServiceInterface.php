<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UserAccount.php");

class AccountNotFoundException extends Exception {}
class AccountStoreFailedException extends Exception {}

interface UserAccountIterator
{
	public function getUserAccount();
	public function free();
}

interface UserAccountServiceInterface
{
	public function getAccountByID($scopeID, $principalID);
	public function getAccountByName($scopeID, $firstName, $lastName);
	public function getAccountByEmail($scopeID, $email);
	public function getAccountByMinLevel($minlevel);
	public function setEverLoggedIn($scopeID, $principalID);
	public function getAccountsByName($scopeID, $name);	/* returns UserAccountIterator */
	public function getAccountsByFirstAndLastName($scopeID, $firstName, $lastName);	/* returns UserAccountIterator */
	public function getAllAccounts($scopeID); /* returns UserAccountIterator */
	public function storeAccount($userAccount);
	public function deleteAccount($scopeID, $principalID);
}
