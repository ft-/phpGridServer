<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/types/UUID.php");

class ClassifiedNotFoundException extends Exception {}
class ClassifiedUpdateFailedException extends Exception {}
class ClassifiedDeleteFailedException extends Exception {}

class UserPickNotFoundException extends Exception {}
class UserPickUpdateFailedException extends Exception {}
class UserPickDeleteFailedException extends Exception {}

class UserPropertiesUpdateFailedException extends Exception {}
class UserPreferencesUpdateFailedException extends Exception {}

class UserNoteNotFoundException extends Exception {}
class UserNoteUpdateFailedException extends Exception {}

class UserAppDataNotFoundException extends Exception {}
class UserAppDataUpdateFailedException extends Exception {}

interface ProfileClassifiedIterator
{
	public function getClassified();
	public function free();
}

interface ProfilePickIterator
{
	public function getPick();
	public function free();
}

interface ProfileUserAppDataIterator
{
	public function getUserAppData();
	public function free();
}

interface ProfileServiceInterface
{
	public function getClassifieds($creatorID);
	public function getClassified($classifiedID);
	public function updateClassified($classifiedRecord);
	public function deleteClassified($recordID);

	public function getPicks($userID);
	public function getPick($userID, $pickID);
	public function updatePick($pick);
	public function deletePick($pickID);

	public function getUserNote($userID, $targetID);
	public function updateUserNote($userNote);
	public function deleteUserNote($userID, $targetID);

	public function getUserProperties($userID);
	public function updateUserProperties($userProperties);
	public function updateUserInterests($userProperties);

	public function getUserImageAssets($userID);

	public function getUserPreferences($userID);
	public function setUserPreferences($userPreferences);

	public function getUserAppDatas($userID);
	public function getUserAppData($userID, $tagID);
	public function setUserAppData($userAppData);
	
	public function searchClassifieds($text, $flags, $category, $query_start, $limit);
}
