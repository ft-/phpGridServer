<?php
/******************************************************************************
 * phpGridServer
*
* GNU LESSER GENERAL PUBLIC LICENSE
* Version 2.1, February 1999
*
*/

class TarFileReader
{
	private $File;
	public $Filename;
	public $Filelength;
	public $Filetype;

	public function __construct($file)
	{
		$this->File = $file;
	}

	public function readHeader()
	{
		$haveLongLink = false;
		do
		{
			$tarhdr = fread($this->File, 512);
			if(strlen($tarhdr) < 512)
			{
				return false;
			}
			$filelen = substr($tarhdr, 124, 12);
			$pos = strpos($filelen, "\0");
			if($pos !== False)
			{
				$filelen = substr($filelen, 0, $pos);
			}
			$this->Filelength = octdec($filelen);
			$this->Filetype = substr($tarhdr, 156, 1);
			if($this->Filetype == "L")
			{
				$this->Filename = $this->readFile();
				$haveLongLink = true;
			}
			else if(!$haveLongLink)
			{
				$this->Filename = substr($tarhdr, 0, 100);
				$pos = strpos($this->Filename, "\0");
				if($pos !== False)
				{
					$this->Filename = substr($this->Filename, 0, $pos);
				}
			}
		} while($this->Filetype == "L");
		return true;
	}

	public function readFile()
	{
		if($this->Filelength != 0)
		{
			$filedata = fread($this->File, $this->Filelength);
		}
		else
		{
			$filedata = "";
		}
		if(0 != ($this->Filelength % 512))
		{
			fseek($this->File, 512 - ($this->Filelength % 512), SEEK_CUR);
		}
		return $filedata;
	}

	public function skipFile()
	{
		if((($this->Filelength + 511) & (0xFFFFFE00)) != 0)
		{
			fseek($this->File, ($this->Filelength + 511) & (0xFFFFFE00), SEEK_CUR);
		}
	}
}
