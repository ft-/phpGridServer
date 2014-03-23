<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

require_once("lib/interfaces/ImageConverterServiceInterface.php");

class GmagickConverterService implements ImageConverterServiceInterface
{
	private $formats = array("image/jpg" => "jpeg", "image/png" => "png", "image/x-j2c" => "jpeg200");

	public function matchConverter($src_content_type, $acceptedFormats)
	{
		foreach($acceptedFormats as $acceptedFormat)
		{
			trigger_error("gmagick: $src_content_type => $acceptedFormat");
			if(isset($this->formats[$acceptedFormat]))
			{
				trigger_error("found");
				return $acceptedFormat;
			}
		}
		throw new NoImageConversionPossibleException();
	}

	public function convert($imageData, $src_content_type, $dst_content_type)
	{
		$im = new Gmagick();
		$im->readimageblob($imageData);
		trigger_error("x");
		$im->setimageformat($formats[$dst_content_type]);
		trigger_error("y");
		return $im->getImageBlob();
	}
}

return new GmagickConverterService();
