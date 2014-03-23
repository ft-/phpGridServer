<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/ImageConverterServiceInterface.php");

class NoImageConverterService implements ImageConverterServiceInterface
{
	public function matchConverter($src_content_type, $acceptedFormats)
	{
		throw new NoImageConversionPossibleException();
	}

	public function convert($imageData, $src_content_type, $dst_content_type)
	{
		throw new NoImageConversionPossibleException();
	}
}

return new NoImageConverterService();
