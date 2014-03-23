<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

class NoImageConversionPossibleException extends Exception {}

interface ImageConverterServiceInterface
{
	public function matchConverter($src_content_type, $acceptedFormats); /* returns mime of selected format */
	public function convert($imageData, $src_content_type, $dst_content_type);
}
