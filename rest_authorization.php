<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

if($_SERVER["REQUEST_METHOD"] != "POST")
{
	exit;
}

if(function_exists("apache_request_headers"))
{
	$headers = apache_request_headers();
	if(isset($headers["X-SecondLife-Shard"]))
	{
		http_response_code("400");
		exit;
	}
}

$dom = new DOMDocument;
$dom->loadXML(file_get_contents("php://input"));
function getNode($node, $name)
{
	$nodelist = $node->getElementsByTagName($name);
	return $nodelist->item(0);
}
function getTextFromNode($Node, $Text = "") {
	if (@$Node->tagName == null)
		return $Text.$Node->textContent;

	$Node = $Node->firstChild;
	if ($Node != null)
		$Text = getTextFromNode($Node, $Text);

		while(@$Node->nextSibling != null) {
			$Text = getTextFromNode($Node->nextSibling, $Text);
			$Node = $Node->nextSibling;
		}
	return $Text;
} 
$node = getNode($dom, "AuthorizationRequest");
if(!$node)
{
	http_response_code("400");
	exit;
}

$id_node = getNode($node, "ID");
$uuid = getTextFromNode($id_node);

/* Nodes in AuthorizationRequest: ID, FirstName, SurName, Email, RegionName, RegionID */
header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
echo "<AuthorizationResponse>";
echo "<IsAuthorized>true</IsAuthorized>";
echo "<Message>".htmlentities($uuid)." has been authorized</Message>";
echo "</AuthorizationResponse>";
