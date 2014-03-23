<?php
/******************************************************************************
 * phpGridServer
 *
 * GNU LESSER GENERAL PUBLIC LICENSE
 * Version 2.1, February 1999
 *
 */

class VisualParam
{
	public $VPIndex;
	public $ParamID;
	public $Name;
	public $Group;
	public $Wearable;
	public $Label;
	public $LabelMin;
	public $LabelMax;
	public $MinimumValue;
	public $MaximumValue;
	public $DefaultValue;
	public $IsBumpAttribute;
	public $Drivers;
	public $AlphaParams;
	public $ColorParams;
}

class Color4
{
	public $Red = 0;
	public $Green = 0;
	public $Blue = 0;
	public $Alpha = 0;
	
	public function __construct($red, $green, $blue, $alpha)
	{
		$this->Red = $red;
		$this->Green = $green;
		$this->Blue = $blue;
		$this->Alpha = $alpha;
	}
}

class VisualColorOperation
{
	const Add = 0;
	const Blend = 1;
	const Multiply = 2;
}

class VisualColorParam
{
	public $Operation = 0;
	public $Colors = array();
	public function __construct($operation, $colors)
	{
		$this->Operation = $operation;
		$this->Colors = $colors;
	}
}

class VisualAlphaParam
{
	public $Domain;
	public $TGAFile;
	public $SkipIfZero;
	public $MultiplyBlend;
	
	public function __construct($domain, $tgaFile, $skipIfZero, $multiplyBlend)
	{
		$this->Domain = $domain;
		$this->TGAFile = $tgaFile;
		$this->SkipIfZero = $skipIfZero;
		$this->MultiplyBlend = $multiplyBlend;
	}
}

class VisualParams
{
	private $vp_index;
	private $vp_paramid;
	public function __construct()
	{
		$this->addVisualParam(1, "Big_Brow", 0, "shape", "Brow Size", "Small", "Large", -0.3, -0.3, 2, 
				false, null, null, null);
		$this->addVisualParam(2, "Nose_Big_Out", 0, "shape", "Nose Size", "Small", "Large", -0.8, -0.8, 2.5, 
				false, null, null, null);
		$this->addVisualParam(4, "Broad_Nostrils", 0, "shape", "Nostril Width", "Narrow", "Broad", -0.5, -0.5, 1,
				false, null, null, null);
		$this->addVisualParam(5, "Cleft_Chin", 0, "shape", "Chin Cleft", "Round", "Cleft", -0.1, -0.1, 1, 
				false, null, null, null);
		$this->addVisualParam(6, "Bulbous_Nose_Tip", 0, "shape", "Nose Tip Shape", "Pointy", "Bulbous", -0.3, -0.3, 1,
				false, null, null, null);
		$this->addVisualParam(7, "Weak_Chin", 0, "shape", "Chin Angle", "Chin Out", "Chin In", -0.5, -0.5, 0.5, 
				false, null, null, null);
		$this->addVisualParam(8, "Double_Chin", 0, "shape", "Chin-Neck", "Tight Chin", "Double Chin", -0.5, -0.5, 1.5,
				false, null, null, null);
		$this->addVisualParam(10, "Sunken_Cheeks", 0, "shape", "Lower Cheeks", "Well-Fed", "Sunken", -1.5, -1.5, 3,
				false, null, null, null);
		$this->addVisualParam(11, "Noble_Nose_Bridge", 0, "shape", "Upper Bridge", "Low", "High", -0.5, -0.5, 1.5, 
				false, null, null, null);
		$this->addVisualParam(12, "Jowls", 0, "shape", "", "Less", "More", -0.5, -0.5, 2.5,
				false, null, null, null);
		$this->addVisualParam(13, "Cleft_Chin_Upper", 0, "shape", "Upper Chin Cleft", "Round", "Cleft", 0, 0, 1.5,
				false, null, null, null);
		$this->addVisualParam(14, "High_Cheek_Bones", 0, "shape", "Cheek Bones", "Low", "High", -0.5, -0.5, 1, 
				false, null, null, null);
		$this->addVisualParam(15, "Ears_Out", 0, "shape", "Ear Angle", "In", "Out", -0.5, -0.5, 1.5,
				false, null, null, null);
		$this->addVisualParam(16, "Pointy_Eyebrows", 0, "hair", "Eyebrow Points", "Smooth", "Pointy", -0.5, -0.5, 3,
				false, [870], null, null);
		$this->addVisualParam(17, "Square_Jaw", 0, "shape", "Jaw Shape", "Pointy", "Square", -0.5, -0.5, 1,
				false, null, null, null);
		$this->addVisualParam(18, "Puffy_Upper_Cheeks", 0, "shape", "Upper Cheeks", "Thin", "Puffy", -1.5, -1.5, 2.5,
				false, null, null, null);
		$this->addVisualParam(19, "Upturned_Nose_Tip", 0, "shape", "Nose Tip Angle", "Downturned", "Upturned", -1.5, -1.5, 1, 
				false, null, null, null);
		$this->addVisualParam(20, "Bulbous_Nose", 0, "shape", "Nose Thickness", "Thin Nose", "Bulbous Nose", -0.5, -0.5, 1.5, 
				false, null, null, null);
		$this->addVisualParam(21, "Upper_Eyelid_Fold", 0, "shape", "Upper Eyelid Fold", "Uncreased", "Creased", -0.2, -0.2, 1.3, 
				false, null, null, null);
		$this->addVisualParam(22, "Attached_Earlobes", 0, "shape", "Attached Earlobes", "Unattached", "Attached", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(23, "Baggy_Eyes", 0, "shape", "Eye Bags", "Smooth", "Baggy", -0.5, -0.5, 1.5,
				false, null, null, null);
		$this->addVisualParam(24, "Wide_Eyes", 0, "shape", "Eye Opening", "Narrow", "Wide", -1.5, -1.5, 2,
				false, null, null, null);
		$this->addVisualParam(25, "Wide_Lip_Cleft", 0, "shape", "Lip Cleft", "Narrow", "Wide", -0.8, -0.8, 1.5,
				false, null, null, null);
		$this->addVisualParam(26, "Lips_Thin", 1, "shape", "", "", "", 0, 0, 0.7,
				false, null, null, null);
		$this->addVisualParam(27, "Wide_Nose_Bridge", 0, "shape", "Bridge Width", "Narrow", "Wide", -1.3, -1.3, 1.2,
				false, null, null, null);
		$this->addVisualParam(28, "Lips_Fat", 1, "shape", "", "", "", 0, 0, 2,
				false, null, null, null);
		$this->addVisualParam(29, "Wide_Upper_Lip", 1, "shape", "", "", "", -0.7, -0.7, 1.3,
				false, null, null, null);
		$this->addVisualParam(30, "Wide_Lower_Lip", 1, "shape", "", "", "", -0.7, -0.7, 1.3,
				false, null, null, null);
		$this->addVisualParam(31, "Arced_Eyebrows", 0, "hair", "Eyebrow Arc", "Flat", "Arced", 0.5, 0, 2,
				false, array( 872 ), null, null);
		$this->addVisualParam(33, "Height", 0, "shape", "Height", "Short", "Tall", -2.3, -2.3, 2, 
				false, null, null, null);
		$this->addVisualParam(34, "Thickness", 0, "shape", "Body Thickness", "Body Thin", "Body Thick", -0.7, -0.7, 1.5,
				false, null, null, null);
		$this->addVisualParam(35, "Big_Ears", 0, "shape", "Ear Size", "Small", "Large", -1, -1, 2, 
				false, null, null, null);
		$this->addVisualParam(36, "Shoulders", 0, "shape", "Shoulders", "Narrow", "Broad", -0.5, -1.8, 1.4, 
				false, null, null, null);
		$this->addVisualParam(37, "Hip Width", 0, "shape", "Hip Width", "Narrow", "Wide", -3.2, -3.2, 2.8, 
				false, null, null, null);
		$this->addVisualParam(38, "Torso Length", 0, "shape", "", "Short Torso", "Long Torso", -1, -1, 1, 
				false, null, null, null);
		$this->addVisualParam(40, "Male_Head", 1, "shape", "", "", "", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(80, "male", 0, "shape", "", "", "", 0, 0, 1, 
				false, [32, 153, 40, 100, 857], null, null);
		$this->addVisualParam(93, "Glove Length", 0, "gloves", "", "Short", "Long", 0.8, 0.01, 1, 
				false, [1058, 1059], null, null);
		$this->addVisualParam(98, "Eye Lightness", 0, "eyes", "", "Darker", "Lighter", 0, 0, 1,
				false, null, null, 
				new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 0), new Color4(255, 255, 255, 255) )));
		$this->addVisualParam(99, "Eye Color", 0, "eyes", "", "Natural", "Unnatural", 0, 0, 1, 
				false, null, null, 
				new VisualColorParam(VisualColorOperation.Add, array( new Color4(50, 25, 5, 255), new Color4(109, 55, 15, 255), new Color4(150, 93, 49, 255), new Color4(152, 118, 25, 255), new Color4(95, 179, 107, 255), new Color4(87, 192, 191, 255), new Color4(95, 172, 179, 255), new Color4(128, 128, 128, 255), new Color4(0, 0, 0, 255), new Color4(255, 255, 0, 255), new Color4(0, 255, 0, 255), new Color4(0, 255, 255, 255), new Color4(0, 0, 255, 255), new Color4(255, 0, 255, 255), new Color4(255, 0, 0, 255) )));
		$this->addVisualParam(100, "Male_Torso", 1, "shape", "", "Male_Torso", "", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(104, "Big_Belly_Torso", 1, "shape", "", "", "", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(105, "Breast Size", 0, "shape", "", "Small", "Large", 0.5, 0, 1,
				false, array( 843, 627, 626 ), null, null);
		$this->addVisualParam(106, "Muscular_Torso", 1, "shape", "Torso Muscles", "Regular", "Muscular", 0, 0, 1.4,
				false, null, null, null);
		$this->addVisualParam(108, "Rainbow Color", 0, "skin", "", "None", "Wild", 0, 0, 1,
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(255, 0, 255, 255), new Color4(255, 0, 0, 255), new Color4(255, 255, 0, 255), new Color4(0, 255, 0, 255), new Color4(0, 255, 255, 255), new Color4(0, 0, 255, 255), new Color4(255, 0, 255, 255) )));
		$this->addVisualParam(110, "Red Skin", 0, "skin", "Ruddiness", "Pale", "Ruddy", 0, 0, 0.1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Blend, array( new Color4(218, 41, 37, 255) )));
		$this->addVisualParam(111, "Pigment", 0, "skin", "", "Light", "Dark", 0.5, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(252, 215, 200, 255), new Color4(240, 177, 112, 255), new Color4(90, 40, 16, 255), new Color4(29, 9, 6, 255) )));
		$this->addVisualParam(112, "Rainbow Color", 0, "hair", "", "None", "Wild", 0, 0, 1,
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(255, 0, 255, 255), new Color4(255, 0, 0, 255), new Color4(255, 255, 0, 255), new Color4(0, 255, 0, 255), new Color4(0, 255, 255, 255), new Color4(0, 0, 255, 255), new Color4(255, 0, 255, 255) )));
		$this->addVisualParam(113, "Red Hair", 0, "hair", "", "No Red", "Very Red", 0, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(118, 47, 19, 255) )));
		$this->addVisualParam(114, "Blonde Hair", 0, "hair", "", "Black", "Blonde", 0.5, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(22, 6, 6, 255), new Color4(29, 9, 6, 255), new Color4(45, 21, 11, 255), new Color4(78, 39, 11, 255), new Color4(90, 53, 16, 255), new Color4(136, 92, 21, 255), new Color4(150, 106, 33, 255), new Color4(198, 156, 74, 255), new Color4(233, 192, 103, 255), new Color4(238, 205, 136, 255) )));
		$this->addVisualParam(115, "White Hair", 0, "hair", "", "No White", "All White", 0, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(255, 255, 255, 255) )));
		$this->addVisualParam(116, "Rosy Complexion", 0, "skin", "", "Less Rosy", "More Rosy", 0, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(198, 71, 71, 0), new Color4(198, 71, 71, 255) )));
		$this->addVisualParam(117, "Lip Pinkness", 0, "skin", "", "Darker", "Pinker", 0, 0, 1,
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(220, 115, 115, 0), new Color4(220, 115, 115, 128) )));
		$this->addVisualParam(119, "Eyebrow Size", 0, "hair", "", "Thin Eyebrows", "Bushy Eyebrows", 0.5, 0, 1,
				false, array( 1000, 1001 ), null, null);
		$this->addVisualParam(130, "Front Fringe", 0, "hair", "", "Short", "Long", 0.45, 0, 1, 
				false, array( 144, 145 ), null, null);
		$this->addVisualParam(131, "Side Fringe", 0, "hair", "", "Short", "Long", 0.5, 0, 1,
				false, array( 146, 147 ), null, null);
		$this->addVisualParam(132, "Back Fringe", 0, "hair", "", "Short", "Long", 0.39, 0, 1,
				false, array( 148, 149 ), null, null);
		$this->addVisualParam(133, "Hair Front", 0, "hair", "", "Short", "Long", 0.25, 0, 1, 
				false, array( 172, 171 ), null, null);
		$this->addVisualParam(134, "Hair Sides", 0, "hair", "", "Short", "Long", 0.5, 0, 1,
				false, array( 174, 173 ), null, null);
		$this->addVisualParam(135, "Hair Back", 0, "hair", "", "Short", "Long", 0.55, 0, 1,
				false, array( 176, 175 ), null, null);
		$this->addVisualParam(136, "Hair Sweep", 0, "hair", "", "Sweep Forward", "Sweep Back", 0.5, 0, 1, 
				false, array( 179, 178 ), null, null);
		$this->addVisualParam(137, "Hair Tilt", 0, "hair", "", "Left", "Right", 0.5, 0, 1,
				false, array( 190, 191 ), null, null);
		$this->addVisualParam(140, "Hair_Part_Middle", 0, "hair", "Middle Part", "No Part", "Part", 0, 0, 2, 
				false, null, null, null);
		$this->addVisualParam(141, "Hair_Part_Right", 0, "hair", "Right Part", "No Part", "Part", 0, 0, 2,
				false, null, null, null);
		$this->addVisualParam(142, "Hair_Part_Left", 0, "hair", "Left Part", "No Part", "Part", 0, 0, 2, 
				false, null, null, null);
		$this->addVisualParam(143, "Hair_Sides_Full", 0, "hair", "Full Hair Sides", "Mowhawk", "Full Sides", 0.125, -4, 1.5, 
				false, null, null, null);
		$this->addVisualParam(144, "Bangs_Front_Up", 1, "hair", "Front Bangs Up", "Bangs", "Bangs Up", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(145, "Bangs_Front_Down", 1, "hair", "Front Bangs Down", "Bangs", "Bangs Down", 0, 0, 5,
				false, null, null, null);
		$this->addVisualParam(146, "Bangs_Sides_Up", 1, "hair", "Side Bangs Up", "Side Bangs", "Side Bangs Up", 0, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(147, "Bangs_Sides_Down", 1, "hair", "Side Bangs Down", "Side Bangs", "Side Bangs Down", 0, 0, 2,
				false, null, null, null);
		$this->addVisualParam(148, "Bangs_Back_Up", 1, "hair", "Back Bangs Up", "Back Bangs", "Back Bangs Up", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(149, "Bangs_Back_Down", 1, "hair", "Back Bangs Down", "Back Bangs", "Back Bangs Down", 0, 0, 2, 
				false, null, null, null);
		$this->addVisualParam(150, "Body Definition", 0, "skin", "", "Less", "More", 0, 0, 1,
				false, array( 125, 126, 160, 161, 874, 878 ), null, null);
		$this->addVisualParam(151, "Big_Butt_Legs", 1, "shape", "Butt Size", "Regular", "Large", 0, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(152, "Muscular_Legs", 1, "shape", "Leg Muscles", "Regular Muscles", "More Muscles", 0, 0, 1.5,
				false, null, null, null);
		$this->addVisualParam(153, "Male_Legs", 1, "shape", "", "", "", 0, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(155, "Lip Width", 0, "shape", "Lip Width", "Narrow Lips", "Wide Lips", 0, -0.9, 1.3, 
				false, array( 29, 30 ), null, null);
		$this->addVisualParam(156, "Big_Belly_Legs", 1, "shape", "", "", "", 0, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(157, "Belly Size", 0, "shape", "", "Small", "Big", 0, 0, 1, 
				false, array( 104, 156, 849 ), null, null);
		$this->addVisualParam(162, "Facial Definition", 0, "skin", "", "Less", "More", 0, 0, 1,
				false, array( 158, 159, 873 ), null, null);
		$this->addVisualParam(163, "Wrinkles", 0, "skin", "", "Less", "More", 0, 0, 1,
				false, array( 118 ), null, null);
		$this->addVisualParam(165, "Freckles", 0, "skin", "", "Less", "More", 0, 0, 1, 
				false, null, new VisualAlphaParam(0.5, "freckles_alpha.tga", true, false), null);
		$this->addVisualParam(166, "Sideburns", 0, "hair", "", "Short Sideburns", "Mutton Chops", 0, 0, 1,
				false, array( 1004, 1005 ), null, null);
		$this->addVisualParam(167, "Moustache", 0, "hair", "", "Chaplin", "Handlebars", 0, 0, 1,
				false, array( 1006, 1007 ), null, null);
		$this->addVisualParam(168, "Soulpatch", 0, "hair", "", "Less soul", "More soul", 0, 0, 1,
				false, array( 1008, 1009 ), null, null);
		$this->addVisualParam(169, "Chin Curtains", 0, "hair", "", "Less Curtains", "More Curtains", 0, 0, 1,
				false, array( 1010, 1011 ), null, null);
		$this->addVisualParam(171, "Hair_Front_Down", 1, "hair", "Front Hair Down", "Front Hair", "Front Hair Down", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(172, "Hair_Front_Up", 1, "hair", "Front Hair Up", "Front Hair", "Front Hair Up", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(173, "Hair_Sides_Down", 1, "hair", "Sides Hair Down", "Sides Hair", "Sides Hair Down", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(174, "Hair_Sides_Up", 1, "hair", "Sides Hair Up", "Sides Hair", "Sides Hair Up", 0, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(175, "Hair_Back_Down", 1, "hair", "Back Hair Down", "Back Hair", "Back Hair Down", 0, 0, 3,
				false, null, null, null);
		$this->addVisualParam(176, "Hair_Back_Up", 1, "hair", "Back Hair Up", "Back Hair", "Back Hair Up", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(177, "Hair_Rumpled", 0, "hair", "Rumpled Hair", "Smooth Hair", "Rumpled Hair", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(178, "Hair_Swept_Back", 1, "hair", "Swept Back Hair", "NotHair", "Swept Back", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(179, "Hair_Swept_Forward", 1, "hair", "Swept Forward Hair", "Hair", "Swept Forward", 0, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(180, "Hair_Volume", 1, "hair", "Hair Volume", "Less", "More", 0, 0, 1.3,
				false, null, null, null);
		$this->addVisualParam(181, "Hair_Big_Front", 0, "hair", "Big Hair Front", "Less", "More", 0.14, -1, 1, 
				false, null, null, null);
		$this->addVisualParam(182, "Hair_Big_Top", 0, "hair", "Big Hair Top", "Less", "More", 0.7, -1, 1,
				false, null, null, null);
		$this->addVisualParam(183, "Hair_Big_Back", 0, "hair", "Big Hair Back", "Less", "More", 0.05, -1, 1,
				false, null, null, null);
		$this->addVisualParam(184, "Hair_Spiked", 0, "hair", "Spiked Hair", "No Spikes", "Big Spikes", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(185, "Deep_Chin", 0, "shape", "Chin Depth", "Shallow", "Deep", -1, -1, 1,
				false, null, null, null);
		$this->addVisualParam(186, "Egg_Head", 1, "shape", "Egg Head", "Chin Heavy", "Forehead Heavy", -1.3, -1.3, 1,
				false, null, null, null);
		$this->addVisualParam(187, "Squash_Stretch_Head", 1, "shape", "Squash/Stretch Head", "Squash Head", "Stretch Head", -0.5, -0.5, 1,
				false, null, null, null);
		$this->addVisualParam(190, "Hair_Tilt_Right", 1, "hair", "Hair Tilted Right", "Hair", "Tilt Right", 0, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(191, "Hair_Tilt_Left", 1, "hair", "Hair Tilted Left", "Hair", "Tilt Left", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(192, "Bangs_Part_Middle", 0, "hair", "Part Bangs", "No Part", "Part Bangs", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(193, "Head Shape", 0, "shape", "Head Shape", "More Square", "More Round", 0.5, 0, 1,
				false, array( 188, 642, 189, 643 ), null, null);
		$this->addVisualParam(194, "Eye_Spread", 1, "shape", "", "Eyes Together", "Eyes Spread", -2, -2, 2,
				false, null, null, null);
		$this->addVisualParam(195, "EyeBone_Spread", 1, "shape", "", "Eyes Together", "Eyes Spread", -1, -1, 1,
				false, null, null, null);
		$this->addVisualParam(196, "Eye Spacing", 0, "shape", "Eye Spacing", "Close Set Eyes", "Far Set Eyes", 0, -2, 1, 
				false, array( 194, 195 ), null, null);
		$this->addVisualParam(197, "Shoe_Heels", 1, "shoes", "", "No Heels", "High Heels", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(198, "Heel Height", 0, "shoes", "", "Low Heels", "High Heels", 0, 0, 1, 
				false, array( 197, 500 ), null, null);
		$this->addVisualParam(400, "Displace_Hair_Facial", 1, "hair", "Hair Thickess", "Cropped Hair", "Bushy Hair", 0, 0, 2,
				false, null, null, null);
		$this->addVisualParam(500, "Shoe_Heel_Height", 1, "shoes", "Heel Height", "Low Heels", "High Heels", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(501, "Shoe_Platform_Height", 1, "shoes", "Platform Height", "Low Platforms", "High Platforms", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(502, "Shoe_Platform", 1, "shoes", "", "No Heels", "High Heels", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(503, "Platform Height", 0, "shoes", "", "Low Platforms", "High Platforms", 0, 0, 1,
				false, array( 501, 502 ), null, null);
		$this->addVisualParam(505, "Lip Thickness", 0, "shape", "", "Thin Lips", "Fat Lips", 0.5, 0, 1,
				false, array( 26, 28 ), null, null);
		$this->addVisualParam(506, "Mouth_Height", 0, "shape", "Mouth Position", "High", "Low", -2, -2, 2,
				false, null, null, null);
		$this->addVisualParam(507, "Breast_Gravity", 0, "shape", "Breast Buoyancy", "Less Gravity", "More Gravity", 0, -1.5, 2, 
				false, null, null, null);
		$this->addVisualParam(508, "Shoe_Platform_Width", 0, "shoes", "Platform Width", "Narrow", "Wide", -1, -1, 2, 
				false, null, null, null);
		$this->addVisualParam(509, "Shoe_Heel_Point", 1, "shoes", "Heel Shape", "Default Heels", "Pointy Heels", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(510, "Shoe_Heel_Thick", 1, "shoes", "Heel Shape", "default Heels", "Thick Heels", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(511, "Shoe_Toe_Point", 1, "shoes", "Toe Shape", "Default Toe", "Pointy Toe", 0, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(512, "Shoe_Toe_Square", 1, "shoes", "Toe Shape", "Default Toe", "Square Toe", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(513, "Heel Shape", 0, "shoes", "", "Pointy Heels", "Thick Heels", 0.5, 0, 1, 
				false, array( 509, 510 ), null, null);
		$this->addVisualParam(514, "Toe Shape", 0, "shoes", "", "Pointy", "Square", 0.5, 0, 1,
				false, array( 511, 512 ), null, null);
		$this->addVisualParam(515, "Foot_Size", 0, "shape", "Foot Size", "Small", "Big", -1, -1, 3,
				false, null, null, null);
		$this->addVisualParam(516, "Displace_Loose_Lowerbody", 1, "pants", "Pants Fit", "", "", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(517, "Wide_Nose", 0, "shape", "Nose Width", "Narrow", "Wide", -0.5, -0.5, 1,
				false, null, null, null);
		$this->addVisualParam(518, "Eyelashes_Long", 0, "shape", "Eyelash Length", "Short", "Long", -0.3, -0.3, 1.5,
				false, null, null, null);
		$this->addVisualParam(600, "Sleeve Length Cloth", 1, "shirt", "", "", "", 0.7, 0, 0.85,
				false, null, new VisualAlphaParam(0.01, "shirt_sleeve_alpha.tga", false, false), null);
		$this->addVisualParam(601, "Shirt Bottom Cloth", 1, "shirt", "", "", "", 0.8, 0, 1, 
				false, null, new VisualAlphaParam(0.05, "shirt_bottom_alpha.tga", false, true), null);
		$this->addVisualParam(602, "Collar Front Height Cloth", 1, "shirt", "", "", "", 0.8, 0, 1,
				false, null, new VisualAlphaParam(0.05, "shirt_collar_alpha.tga", false, true), null);
		$this->addVisualParam(603, "Sleeve Length", 0, "undershirt", "", "Short", "Long", 0.4, 0.01, 1,
				false, array( 1042, 1043 ), null, null);
		$this->addVisualParam(604, "Bottom", 0, "undershirt", "", "Short", "Long", 0.85, 0, 1,
				false, array( 1044, 1045 ), null, null);
		$this->addVisualParam(605, "Collar Front", 0, "undershirt", "", "Low", "High", 0.84, 0, 1, 
				false, array( 1046, 1047 ), null, null);
		$this->addVisualParam(606, "Sleeve Length", 0, "jacket", "", "Short", "Long", 0.8, 0, 1,
				false, array( 1019, 1039, 1020 ), null, null);
		$this->addVisualParam(607, "Collar Front", 0, "jacket", "", "Low", "High", 0.8, 0, 1, 
				false, array( 1021, 1040, 1022 ), null, null);
		$this->addVisualParam(608, "bottom length lower", 0, "jacket", "Jacket Length", "Short", "Long", 0.8, 0, 1,
				false, array( 620, 1025, 1037, 621, 1027, 1033 ), null, null);
		$this->addVisualParam(609, "open jacket", 0, "jacket", "Open Front", "Open", "Closed", 0.2, 0, 1,
				false, array( 622, 1026, 1038, 623, 1028, 1034 ), null, null);
		$this->addVisualParam(614, "Waist Height Cloth", 1, "pants", "", "", "", 0.8, 0, 1,
				false, null, new VisualAlphaParam(0.05, "pants_waist_alpha.tga", false, false), null);
		$this->addVisualParam(615, "Pants Length Cloth", 1, "pants", "", "", "", 0.8, 0, 1,
				false, null, new VisualAlphaParam(0.01, "pants_length_alpha.tga", false, false), null);
		$this->addVisualParam(616, "Shoe Height", 0, "shoes", "", "Short", "Tall", 0.1, 0, 1,
				false, array( 1052, 1053 ), null, null);
		$this->addVisualParam(617, "Socks Length", 0, "socks", "", "Short", "Long", 0.35, 0, 1,
				false, array( 1050, 1051 ), null, null);
		$this->addVisualParam(619, "Pants Length", 0, "underpants", "", "Short", "Long", 0.3, 0, 1,
				false, array( 1054, 1055 ), null, null);
		$this->addVisualParam(620, "bottom length upper", 1, "jacket", "", "hi cut", "low cut", 0.8, 0, 1,
				false, null, new VisualAlphaParam(0.01, "jacket_length_upper_alpha.tga", false, true), null);
		$this->addVisualParam(621, "bottom length lower", 1, "jacket", "", "hi cut", "low cut", 0.8, 0, 1,
				false, null, new VisualAlphaParam(0.01, "jacket_length_lower_alpha.tga", false, false), null);
		$this->addVisualParam(622, "open upper", 1, "jacket", "", "closed", "open", 0.8, 0, 1, 
				false, null, new VisualAlphaParam(0.01, "jacket_open_upper_alpha.tga", false, true), null);
		$this->addVisualParam(623, "open lower", 1, "jacket", "", "open", "closed", 0.8, 0, 1,
				false, null, new VisualAlphaParam(0.01, "jacket_open_lower_alpha.tga", false, true), null);
		$this->addVisualParam(624, "Pants Waist", 0, "underpants", "", "Low", "High", 0.8, 0, 1, 
				false, array( 1056, 1057 ), null, null);
		$this->addVisualParam(625, "Leg_Pantflair", 0, "pants", "Cuff Flare", "Tight Cuffs", "Flared Cuffs", 0, 0, 1.5,
				false, null, null, null);
		$this->addVisualParam(626, "Big_Chest", 1, "shape", "Chest Size", "Small", "Large", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(627, "Small_Chest", 1, "shape", "Chest Size", "Large", "Small", 0, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(628, "Displace_Loose_Upperbody", 1, "shirt", "Shirt Fit", "", "", 0, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(629, "Forehead Angle", 0, "shape", "", "More Vertical", "More Sloped", 0.5, 0, 1,
				false, array( 630, 644, 631, 645 ), null, null);
		$this->addVisualParam(633, "Fat_Head", 1, "shape", "Fat Head", "Skinny", "Fat", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(634, "Fat_Torso", 1, "shape", "Fat Torso", "skinny", "fat", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(635, "Fat_Legs", 1, "shape", "Fat Torso", "skinny", "fat", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(637, "Body Fat", 0, "shape", "", "Less Body Fat", "More Body Fat", 0, 0, 1,
				false, array( 633, 634, 635, 851 ), null, null);
		$this->addVisualParam(638, "Low_Crotch", 0, "pants", "Pants Crotch", "High and Tight", "Low and Loose", 0, 0, 1.3,
				false, null, null, null);
		$this->addVisualParam(640, "Hair_Egg_Head", 1, "hair", "", "", "", -1.3, -1.3, 1,
				false, null, null, null);
		$this->addVisualParam(641, "Hair_Squash_Stretch_Head", 1, "hair", "", "", "", -0.5, -0.5, 1,
				false, null, null, null);
		$this->addVisualParam(642, "Hair_Square_Head", 1, "hair", "", "", "", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(643, "Hair_Round_Head", 1, "hair", "", "", "", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(644, "Hair_Forehead_Round", 1, "hair", "", "", "", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(645, "Hair_Forehead_Slant", 1, "hair", "", "", "", 0, 0, 1,
				false, null, null, null);
		$this->addVisualParam(646, "Egg_Head", 0, "shape", "Egg Head", "Chin Heavy", "Forehead Heavy", 0, -1.3, 1, 
				false, array( 640, 186 ), null, null);
		$this->addVisualParam(647, "Squash_Stretch_Head", 0, "shape", "Head Stretch", "Squash Head", "Stretch Head", 0, -0.5, 1, 
				false, array( 641, 187 ), null, null);
		$this->addVisualParam(648, "Scrawny_Torso", 1, "shape", "Torso Muscles", "Regular", "Scrawny", 0, 0, 1.3,
				false, null, null, null);
		$this->addVisualParam(649, "Torso Muscles", 0, "shape", "Torso Muscles", "Less Muscular", "More Muscular", 0.5, 0, 1,
				false, array( 648, 106 ), null, null);
		$this->addVisualParam(650, "Eyelid_Corner_Up", 0, "shape", "Outer Eye Corner", "Corner Down", "Corner Up", -1.3, -1.3, 1.2, 
				false, null, null, null);
		$this->addVisualParam(651, "Scrawny_Legs", 1, "shape", "Scrawny Leg", "Regular Muscles", "Less Muscles", 0, 0, 1.5,
				false, null, null, null);
		$this->addVisualParam(652, "Leg Muscles", 0, "shape", "", "Less Muscular", "More Muscular", 0.5, 0, 1,
				false, array( 651, 152 ), null, null);
		$this->addVisualParam(653, "Tall_Lips", 0, "shape", "Lip Fullness", "Less Full", "More Full", -1, -1, 2,
				false, null, null, null);
		$this->addVisualParam(654, "Shoe_Toe_Thick", 0, "shoes", "Toe Thickness", "Flat Toe", "Thick Toe", 0, 0, 2, 
				false, null, null, null);
		$this->addVisualParam(655, "Head Size", 1, "shape", "Head Size", "Small Head", "Big Head", -0.25, -0.25, 0.1,
				false, null, null, null);
		$this->addVisualParam(656, "Crooked_Nose", 0, "shape", "Crooked Nose", "Nose Left", "Nose Right", -2, -2, 2, 
				false, null, null, null);
		$this->addVisualParam(657, "Smile_Mouth", 1, "shape", "Mouth Corner", "Corner Normal", "Corner Up", 0, 0, 1.4, 
				false, null, null, null);
		$this->addVisualParam(658, "Frown_Mouth", 1, "shape", "Mouth Corner", "Corner Normal", "Corner Down", 0, 0, 1.2, 
				false, null, null, null);
		$this->addVisualParam(659, "Mouth Corner", 0, "shape", "", "Corner Down", "Corner Up", 0.5, 0, 1,
				false, array( 658, 657 ), null, null);
		$this->addVisualParam(660, "Shear_Head", 1, "shape", "Shear Face", "Shear Left", "Shear Right", 0, -2, 2,
				false, null, null, null);
		$this->addVisualParam(661, "EyeBone_Head_Shear", 1, "shape", "", "Eyes Shear Left Up", "Eyes Shear Right Up", -2, -2, 2, 
				false, null, null, null);
		$this->addVisualParam(662, "Face Shear", 0, "shape", "", "Shear Right Up", "Shear Left Up", 0.5, 0, 1, 
				false, array( 660, 661, 774 ), null, null);
		$this->addVisualParam(663, "Shift_Mouth", 0, "shape", "Shift Mouth", "Shift Left", "Shift Right", 0, -2, 2, 
				false, null, null, null);
		$this->addVisualParam(664, "Pop_Eye", 0, "shape", "Eye Pop", "Pop Right Eye", "Pop Left Eye", 0, -1.3, 1.3, 
				false, null, null, null);
		$this->addVisualParam(665, "Jaw_Jut", 0, "shape", "Jaw Jut", "Overbite", "Underbite", 0, -2, 2, 
				false, null, null, null);
		$this->addVisualParam(674, "Hair_Shear_Back", 0, "hair", "Shear Back", "Full Back", "Sheared Back", -0.3, -1, 2, 
				false, null, null, null);
		$this->addVisualParam(675, "Hand Size", 0, "shape", "", "Small Hands", "Large Hands", -0.3, -0.3, 0.3, 
				false, null, null, null);
		$this->addVisualParam(676, "Love_Handles", 0, "shape", "Love Handles", "Less Love", "More Love", 0, -1, 2, 
				false, array( 855, 856 ), null, null);
		$this->addVisualParam(677, "Scrawny_Torso_Male", 1, "shape", "Torso Scrawny", "Regular", "Scrawny", 0, 0, 1.3,
				 false, null, null, null);
		$this->addVisualParam(678, "Torso Muscles", 0, "shape", "", "Less Muscular", "More Muscular", 0.5, 0, 1, 
				false, array( 677, 106 ), null, null);
		$this->addVisualParam(679, "Eyeball_Size", 1, "shape", "Eyeball Size", "small eye", "big eye", -0.25, -0.25, 0.1, 
				false, null, null, null);
		$this->addVisualParam(681, "Eyeball_Size", 1, "shape", "Eyeball Size", "small eye", "big eye", -0.25, -0.25, 0.1, 
				false, null, null, null);
		$this->addVisualParam(682, "Head Size", 0, "shape", "Head Size", "Small Head", "Big Head", 0.5, 0, 1,
				false, array( 679, 694, 680, 681, 655 ), null, null);
		$this->addVisualParam(683, "Neck Thickness", 0, "shape", "", "Skinny Neck", "Thick Neck", -0.15, -0.4, 0.2, 
				false, null, null, null);
		$this->addVisualParam(684, "Breast_Female_Cleavage", 0, "shape", "Breast Cleavage", "Separate", "Join", 0, -0.3, 1.3, 
				false, null, null, null);
		$this->addVisualParam(685, "Chest_Male_No_Pecs", 0, "shape", "Pectorals", "Big Pectorals", "Sunken Chest", 0, -0.5, 1.1,
				false, null, null, null);
		$this->addVisualParam(686, "Head_Eyes_Big", 1, "shape", "Eye Size", "Beady Eyes", "Anime Eyes", 0, -2, 2, 
				false, null, null, null);
		$this->addVisualParam(687, "Eyeball_Size", 1, "shape", "Big Eyeball", "small eye", "big eye", -0.25, -0.25, 0.25, 
				false, null, null, null);
		$this->addVisualParam(689, "EyeBone_Big_Eyes", 1, "shape", "", "Eyes Back", "Eyes Forward", -1, -1, 1, 
				false, null, null, null);
		$this->addVisualParam(690, "Eye Size", 0, "shape", "Eye Size", "Beady Eyes", "Anime Eyes", 0.5, 0, 1, 
				false, array( 686, 687, 695, 688, 691, 689 ), null, null);
		$this->addVisualParam(691, "Eyeball_Size", 1, "shape", "Big Eyeball", "small eye", "big eye", -0.25, -0.25, 0.25, 
				false, null, null, null);
		$this->addVisualParam(692, "Leg Length", 0, "shape", "", "Short Legs", "Long Legs", -1, -1, 1, 
				false, null, null, null);
		$this->addVisualParam(693, "Arm Length", 0, "shape", "", "Short Arms", "Long arms", 0.6, -1, 1, 
				false, null, null, null);
		$this->addVisualParam(694, "Eyeball_Size", 1, "shape", "Eyeball Size", "small eye", "big eye", -0.25, -0.25, 0.1, 
				false, null, null, null);
		$this->addVisualParam(695, "Eyeball_Size", 1, "shape", "Big Eyeball", "small eye", "big eye", -0.25, -0.25, 0.25, 
				false, null, null, null);
		$this->addVisualParam(700, "Lipstick Color", 0, "skin", "", "Pink", "Black", 0.25, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(245, 161, 177, 200), new Color4(216, 37, 67, 200), new Color4(178, 48, 76, 200), new Color4(68, 0, 11, 200), new Color4(252, 207, 184, 200), new Color4(241, 136, 106, 200), new Color4(208, 110, 85, 200), new Color4(106, 28, 18, 200), new Color4(58, 26, 49, 200), new Color4(14, 14, 14, 200) )));
		$this->addVisualParam(701, "Lipstick", 0, "skin", "", "No Lipstick", "More Lipstick", 0, 0, 0.9, 
				false, null, new VisualAlphaParam(0.05, "lipstick_alpha.tga", true, false), null);
		$this->addVisualParam(702, "Lipgloss", 0, "skin", "", "No Lipgloss", "Glossy", 0, 0, 1, 
				false, null, new VisualAlphaParam(0.2, "lipgloss_alpha.tga", true, false), null);
		$this->addVisualParam(703, "Eyeliner", 0, "skin", "", "No Eyeliner", "Full Eyeliner", 0, 0, 1, 
				false, null, new VisualAlphaParam(0.1, "eyeliner_alpha.tga", true, false), null);
		$this->addVisualParam(704, "Blush", 0, "skin", "", "No Blush", "More Blush", 0, 0, 0.9, 
				false, null, new VisualAlphaParam(0.3, "blush_alpha.tga", true, false), null);
		$this->addVisualParam(705, "Blush Color", 0, "skin", "", "Pink", "Orange", 0.5, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(253, 162, 193, 200), new Color4(247, 131, 152, 200), new Color4(213, 122, 140, 200), new Color4(253, 152, 144, 200), new Color4(236, 138, 103, 200), new Color4(195, 128, 122, 200), new Color4(148, 103, 100, 200), new Color4(168, 95, 62, 200) )));
		$this->addVisualParam(706, "Out Shdw Opacity", 0, "skin", "", "Clear", "Opaque", 0.6, 0.2, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Blend, array( new Color4(255, 255, 255, 0), new Color4(255, 255, 255, 255) )));
		$this->addVisualParam(707, "Outer Shadow", 0, "skin", "", "No Eyeshadow", "More Eyeshadow", 0, 0, 0.7, 
				false, null, new VisualAlphaParam(0.05, "eyeshadow_outer_alpha.tga", true, false), null);
		$this->addVisualParam(708, "Out Shdw Color", 0, "skin", "", "Light", "Dark", 0, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(252, 247, 246, 255), new Color4(255, 206, 206, 255), new Color4(233, 135, 149, 255), new Color4(220, 168, 192, 255), new Color4(228, 203, 232, 255), new Color4(255, 234, 195, 255), new Color4(230, 157, 101, 255), new Color4(255, 147, 86, 255), new Color4(228, 110, 89, 255), new Color4(228, 150, 120, 255), new Color4(223, 227, 213, 255), new Color4(96, 116, 87, 255), new Color4(88, 143, 107, 255), new Color4(194, 231, 223, 255), new Color4(207, 227, 234, 255), new Color4(41, 171, 212, 255), new Color4(180, 137, 130, 255), new Color4(173, 125, 105, 255), new Color4(144, 95, 98, 255), new Color4(115, 70, 77, 255), new Color4(155, 78, 47, 255), new Color4(239, 239, 239, 255), new Color4(194, 194, 194, 255), new Color4(120, 120, 120, 255), new Color4(10, 10, 10, 255) )));
		$this->addVisualParam(709, "Inner Shadow", 0, "skin", "", "No Eyeshadow", "More Eyeshadow", 0, 0, 1, 
				false, null, new VisualAlphaParam(0.2, "eyeshadow_inner_alpha.tga", true, false), null);
		$this->addVisualParam(710, "Nail Polish", 0, "skin", "", "No Polish", "Painted Nails", 0, 0, 1, 
				false, null, new VisualAlphaParam(0.1, "nailpolish_alpha.tga", true, false), null);
		$this->addVisualParam(711, "Blush Opacity", 0, "skin", "", "Clear", "Opaque", 0.5, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Blend, array( new Color4(255, 255, 255, 0), new Color4(255, 255, 255, 255) )));
		$this->addVisualParam(712, "In Shdw Color", 0, "skin", "", "Light", "Dark", 0, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(252, 247, 246, 255), new Color4(255, 206, 206, 255), new Color4(233, 135, 149, 255), new Color4(220, 168, 192, 255), new Color4(228, 203, 232, 255), new Color4(255, 234, 195, 255), new Color4(230, 157, 101, 255), new Color4(255, 147, 86, 255), new Color4(228, 110, 89, 255), new Color4(228, 150, 120, 255), new Color4(223, 227, 213, 255), new Color4(96, 116, 87, 255), new Color4(88, 143, 107, 255), new Color4(194, 231, 223, 255), new Color4(207, 227, 234, 255), new Color4(41, 171, 212, 255), new Color4(180, 137, 130, 255), new Color4(173, 125, 105, 255), new Color4(144, 95, 98, 255), new Color4(115, 70, 77, 255), new Color4(155, 78, 47, 255), new Color4(239, 239, 239, 255), new Color4(194, 194, 194, 255), new Color4(120, 120, 120, 255), new Color4(10, 10, 10, 255) )));
		$this->addVisualParam(713, "In Shdw Opacity", 0, "skin", "", "Clear", "Opaque", 0.7, 0.2, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Blend, array( new Color4(255, 255, 255, 0), new Color4(255, 255, 255, 255) )));
		$this->addVisualParam(714, "Eyeliner Color", 0, "skin", "", "Dark Green", "Black", 0, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(24, 98, 40, 250), new Color4(9, 100, 127, 250), new Color4(61, 93, 134, 250), new Color4(70, 29, 27, 250), new Color4(115, 75, 65, 250), new Color4(100, 100, 100, 250), new Color4(91, 80, 74, 250), new Color4(112, 42, 76, 250), new Color4(14, 14, 14, 250) )));
		$this->addVisualParam(715, "Nail Polish Color", 0, "skin", "", "Pink", "Black", 0, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(255, 187, 200, 255), new Color4(194, 102, 127, 255), new Color4(227, 34, 99, 255), new Color4(168, 41, 60, 255), new Color4(97, 28, 59, 255), new Color4(234, 115, 93, 255), new Color4(142, 58, 47, 255), new Color4(114, 30, 46, 255), new Color4(14, 14, 14, 255) )));
		$this->addVisualParam(750, "Eyebrow Density", 0, "hair", "", "Sparse", "Dense", 0.7, 0, 1, 
				false, array( 1002, 1003 ), null, null);
		$this->addVisualParam(751, "5 O'Clock Shadow", 1, "hair", "", "Dense hair", "Shadow hair", 0.7, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Blend, array( new Color4(255, 255, 255, 255), new Color4(255, 255, 255, 30) )));
		$this->addVisualParam(752, "Hair Thickness", 0, "hair", "", "5 O'Clock Shadow", "Bushy Hair", 0.5, 0, 1, 
				false, array( 751, 1012, 400 ), null, null);
		$this->addVisualParam(753, "Saddlebags", 0, "shape", "Saddle Bags", "Less Saddle", "More Saddle", 0, -0.5, 3, 
				false, array( 850, 854 ), null, null);
		$this->addVisualParam(754, "Hair_Taper_Back", 0, "hair", "Taper Back", "Wide Back", "Narrow Back", 0, -1, 2, 
				false, null, null, null);
		$this->addVisualParam(755, "Hair_Taper_Front", 0, "hair", "Taper Front", "Wide Front", "Narrow Front", 0.05, -1.5, 1.5, 
				false, null, null, null);
		$this->addVisualParam(756, "Neck Length", 0, "shape", "", "Short Neck", "Long Neck", 0, -1, 1, 
				false, null, null, null);
		$this->addVisualParam(757, "Lower_Eyebrows", 0, "hair", "Eyebrow Height", "Higher", "Lower", -1, -4, 2, 
				false, array( 871 ), null, null);
		$this->addVisualParam(758, "Lower_Bridge_Nose", 0, "shape", "Lower Bridge", "Low", "High", -1.5, -1.5, 1.5, 
				false, null, null, null);
		$this->addVisualParam(759, "Low_Septum_Nose", 0, "shape", "Nostril Division", "High", "Low", 0.5, -1, 1.5, 
				false, null, null, null);
		$this->addVisualParam(760, "Jaw_Angle", 0, "shape", "Jaw Angle", "Low Jaw", "High Jaw", 0, -1.2, 2, 
				false, null, null, null);
		$this->addVisualParam(761, "Hair_Volume_Small", 1, "hair", "Hair Volume", "Less", "More", 0, 0, 1.3, 
				false, null, null, null);
		$this->addVisualParam(762, "Hair_Shear_Front", 0, "hair", "Shear Front", "Full Front", "Sheared Front", 0, 0, 3, 
				false, null, null, null);
		$this->addVisualParam(763, "Hair Volume", 0, "hair", "", "Less Volume", "More Volume", 0.55, 0, 1, 
				false, array( 761, 180 ), null, null);
		$this->addVisualParam(764, "Lip_Cleft_Deep", 0, "shape", "Lip Cleft Depth", "Shallow", "Deep", -0.5, -0.5, 1.2, 
				false, null, null, null);
		$this->addVisualParam(765, "Puffy_Lower_Lids", 0, "shape", "Puffy Eyelids", "Flat", "Puffy", -0.3, -0.3, 2.5, 
				false, null, null, null);
		$this->addVisualParam(767, "Bug_Eyed_Head", 1, "shape", "Eye Depth", "Sunken Eyes", "Bug Eyes", 0, -2, 2, 
				false, null, null, null);
		$this->addVisualParam(768, "EyeBone_Bug", 1, "shape", "", "Eyes Sunken", "Eyes Bugged", -2, -2, 2, 
				false, null, null, null);
		$this->addVisualParam(769, "Eye Depth", 0, "shape", "", "Sunken Eyes", "Bugged Eyes", 0.5, 0, 1, 
				false, array( 767, 768 ), null, null);
		$this->addVisualParam(770, "Elongate_Head", 1, "shape", "Shear Face", "Flat Head", "Long Head", 0, -1, 1, 
				false, null, null, null);
		$this->addVisualParam(771, "Elongate_Head_Hair", 1, "hair", "", "", "", -1, -1, 1, 
				false, null, null, null);
		$this->addVisualParam(772, "EyeBone_Head_Elongate", 1, "shape", "", "Eyes Short Head", "Eyes Long Head", -1, -1, 1, 
				false, null, null, null);
		$this->addVisualParam(773, "Head Length", 0, "shape", "", "Flat Head", "Long Head", 0.5, 0, 1, 
				false, array( 770, 771, 772 ), null, null);
		$this->addVisualParam(774, "Shear_Head_Hair", 1, "hair", "", "", "", -2, -2, 2, 
				false, null, null, null);
		$this->addVisualParam(775, "Body Freckles", 0, "skin", "", "Less Freckles", "More Freckles", 0, 0, 1, 
				false, array( 776, 777 ), null, null);
		$this->addVisualParam(778, "Collar Back Height Cloth", 1, "shirt", "", "", "", 0.8, 0, 1, 
				false, null, new VisualAlphaParam(0.05, "shirt_collar_back_alpha.tga", false, true), null);
		$this->addVisualParam(779, "Collar Back", 0, "undershirt", "", "Low", "High", 0.84, 0, 1, 
				false, array( 1048, 1049 ), null, null);
		$this->addVisualParam(780, "Collar Back", 0, "jacket", "", "Low", "High", 0.8, 0, 1, 
				false, array( 1023, 1041, 1024 ), null, null);
		$this->addVisualParam(781, "Collar Back", 0, "shirt", "", "Low", "High", 0.78, 0, 1, 
				false, array( 778, 1016, 1032, 903 ), null, null);
		$this->addVisualParam(782, "Hair_Pigtails_Short", 1, "hair", "", "", "", 0, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(783, "Hair_Pigtails_Med", 1, "hair", "", "", "", 0, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(784, "Hair_Pigtails_Long", 1, "hair", "", "", "", 0, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(785, "Pigtails", 0, "hair", "", "Short Pigtails", "Long Pigtails", 0, 0, 1, 
				false, array( 782, 783, 790, 784 ), null, null);
		$this->addVisualParam(786, "Hair_Ponytail_Short", 1, "hair", "", "", "", 0, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(787, "Hair_Ponytail_Med", 1, "hair", "", "", "", 0, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(788, "Hair_Ponytail_Long", 1, "hair", "", "", "", 0, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(789, "Ponytail", 0, "hair", "", "Short Ponytail", "Long Ponytail", 0, 0, 1, 
				false, array( 786, 787, 788 ), null, null);
		$this->addVisualParam(790, "Hair_Pigtails_Medlong", 1, "hair", "", "", "", 0, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(793, "Leg_Longcuffs", 1, "pants", "Longcuffs", "", "", 0, 0, 3, 
				false, null, null, null);
		$this->addVisualParam(794, "Small_Butt", 1, "shape", "Butt Size", "Regular", "Small", 0, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(795, "Butt Size", 0, "shape", "Butt Size", "Flat Butt", "Big Butt", 0.25, 0, 1, 
				false, array( 867, 794, 151, 852 ), null, null);
		$this->addVisualParam(796, "Pointy_Ears", 0, "shape", "Ear Tips", "Flat", "Pointy", -0.4, -0.4, 3, 
				false, null, null, null);
		$this->addVisualParam(797, "Fat_Upper_Lip", 1, "shape", "Fat Upper Lip", "Normal Upper", "Fat Upper", 0, 0, 1.5, 
				false, null, null, null);
		$this->addVisualParam(798, "Fat_Lower_Lip", 1, "shape", "Fat Lower Lip", "Normal Lower", "Fat Lower", 0, 0, 1.5, 
				false, null, null, null);
		$this->addVisualParam(799, "Lip Ratio", 0, "shape", "Lip Ratio", "More Upper Lip", "More Lower Lip", 0.5, 0, 1, false, array( 797, 798 ), null, null);
		$this->addVisualParam(800, "Sleeve Length", 0, "shirt", "", "Short", "Long", 0.89, 0, 1, 
				false, array( 600, 1013, 1029, 900 ), null, null);
		$this->addVisualParam(801, "Shirt Bottom", 0, "shirt", "", "Short", "Long", 1, 0, 1, 
				false, array( 601, 1014, 1030, 901 ), null, null);
		$this->addVisualParam(802, "Collar Front", 0, "shirt", "", "Low", "High", 0.78, 0, 1, 
				false, array( 602, 1015, 1031, 902 ), null, null);
		$this->addVisualParam(803, "shirt_red", 0, "shirt", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(255, 0, 0, 255) )));
		$this->addVisualParam(804, "shirt_green", 0, "shirt", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(0, 255, 0, 255) )));
		$this->addVisualParam(805, "shirt_blue", 0, "shirt", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(0, 0, 255, 255) )));
		$this->addVisualParam(806, "pants_red", 0, "pants", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(255, 0, 0, 255) )));
		$this->addVisualParam(807, "pants_green", 0, "pants", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(0, 255, 0, 255) )));
		$this->addVisualParam(808, "pants_blue", 0, "pants", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(0, 0, 255, 255) )));
		$this->addVisualParam(809, "lower_jacket_red", 1, "jacket", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(255, 0, 0, 255) )));
		$this->addVisualParam(810, "lower_jacket_green", 1, "jacket", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(0, 255, 0, 255) )));
		$this->addVisualParam(811, "lower_jacket_blue", 1, "jacket", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(0, 0, 255, 255) )));
		$this->addVisualParam(812, "shoes_red", 0, "shoes", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(255, 0, 0, 255) )));
		$this->addVisualParam(813, "shoes_green", 0, "shoes", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(0, 255, 0, 255) )));
		$this->addVisualParam(814, "Waist Height", 0, "pants", "", "Low", "High", 1, 0, 1, 
				false, array( 614, 1017, 1035, 914 ), null, null);
		$this->addVisualParam(815, "Pants Length", 0, "pants", "", "Short", "Long", 0.8, 0, 1, 
				false, array( 615, 1018, 1036, 793, 915 ), null, null);
		$this->addVisualParam(816, "Loose Lower Clothing", 0, "pants", "Pants Fit", "Tight Pants", "Loose Pants", 0, 0, 1, 
				false, array( 516, 913 ), null, null);
		$this->addVisualParam(817, "shoes_blue", 0, "shoes", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(0, 0, 255, 255) )));
		$this->addVisualParam(818, "socks_red", 0, "socks", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(255, 0, 0, 255) )));
		$this->addVisualParam(819, "socks_green", 0, "socks", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(0, 255, 0, 255) )));
		$this->addVisualParam(820, "socks_blue", 0, "socks", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(0, 0, 255, 255) )));
		$this->addVisualParam(821, "undershirt_red", 0, "undershirt", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(255, 0, 0, 255) )));
		$this->addVisualParam(822, "undershirt_green", 0, "undershirt", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(0, 255, 0, 255) )));
		$this->addVisualParam(823, "undershirt_blue", 0, "undershirt", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(0, 0, 255, 255) )));
		$this->addVisualParam(824, "underpants_red", 0, "underpants", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(255, 0, 0, 255) )));
		$this->addVisualParam(825, "underpants_green", 0, "underpants", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(0, 255, 0, 255) )));
		$this->addVisualParam(826, "underpants_blue", 0, "underpants", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(0, 0, 255, 255) )));
		$this->addVisualParam(827, "gloves_red", 0, "gloves", "", "", "", 1, 0, 1,
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(255, 0, 0, 255) )));
		$this->addVisualParam(828, "Loose Upper Clothing", 0, "shirt", "Shirt Fit", "Tight Shirt", "Loose Shirt", 0, 0, 1, 
				false, array( 628, 899 ), null, null);
		$this->addVisualParam(829, "gloves_green", 0, "gloves", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(0, 255, 0, 255) )));
		$this->addVisualParam(830, "gloves_blue", 0, "gloves", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(0, 0, 255, 255) )));
		$this->addVisualParam(831, "upper_jacket_red", 1, "jacket", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(255, 0, 0, 255) )));
		$this->addVisualParam(832, "upper_jacket_green", 1, "jacket", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(0, 255, 0, 255) )));
		$this->addVisualParam(833, "upper_jacket_blue", 1, "jacket", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(0, 0, 255, 255) )));
		$this->addVisualParam(834, "jacket_red", 0, "jacket", "", "", "", 1, 0, 1, 
				false, array( 809, 831 ), null, null);
		$this->addVisualParam(835, "jacket_green", 0, "jacket", "", "", "", 1, 0, 1, 
				false, array( 810, 832 ), null, null);
		$this->addVisualParam(836, "jacket_blue", 0, "jacket", "", "", "", 1, 0, 1, 
				false, array( 811, 833 ), null, null);
		$this->addVisualParam(840, "Shirtsleeve_flair", 0, "shirt", "Sleeve Looseness", "Tight Sleeves", "Loose Sleeves", 0, 0, 1.5, 
				false, null, null, null);
		$this->addVisualParam(841, "Bowed_Legs", 0, "shape", "Knee Angle", "Knock Kneed", "Bow Legged", 0, -1, 1, 
				false, array( 853, 847 ), null, null);
		$this->addVisualParam(842, "Hip Length", 0, "shape", "", "Short hips", "Long Hips", -1, -1, 1, 
				false, null, null, null);
		$this->addVisualParam(843, "No_Chest", 1, "shape", "Chest Size", "Some", "None", 0, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(844, "Glove Fingers", 0, "gloves", "", "Fingerless", "Fingers", 1, 0.01, 1, 
				false, array( 1060, 1061 ), null, null);
		$this->addVisualParam(845, "skirt_poofy", 1, "skirt", "poofy skirt", "less poofy", "more poofy", 0, 0, 1.5, 
				false, null, null, null);
		$this->addVisualParam(846, "skirt_loose", 1, "skirt", "loose skirt", "form fitting", "loose", 0, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(847, "skirt_bowlegs", 1, "skirt", "legs skirt", "", "", 0, -1, 1, 
				false, null, null, null);
		$this->addVisualParam(848, "skirt_bustle", 0, "skirt", "bustle skirt", "no bustle", "more bustle", 0.2, 0, 2, 
				false, null, null, null);
		$this->addVisualParam(849, "skirt_belly", 1, "skirt", "big belly skirt", "", "", 0, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(850, "skirt_saddlebags", 1, "skirt", "", "", "", -0.5, -0.5, 3, 
				false, null, null, null);
		$this->addVisualParam(851, "skirt_chubby", 1, "skirt", "", "less", "more", 0, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(852, "skirt_bigbutt", 1, "skirt", "bigbutt skirt", "less", "more", 0, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(854, "Saddlebags", 1, "shape", "", "", "", -0.5, -0.5, 3, 
				false, null, null, null);
		$this->addVisualParam(855, "Love_Handles", 1, "shape", "", "", "", 0, -1, 2, 
				false, null, null, null);
		$this->addVisualParam(856, "skirt_lovehandles", 1, "skirt", "", "less", "more", 0, -1, 2, 
				false, null, null, null);
		$this->addVisualParam(857, "skirt_male", 1, "skirt", "", "", "", 0, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(858, "Skirt Length", 0, "skirt", "", "Short", "Long", 0.4, 0.01, 1, 
				false, null, new VisualAlphaParam(0, "skirt_length_alpha.tga", false, true), null);
		$this->addVisualParam(859, "Slit Front", 0, "skirt", "", "Open Front", "Closed Front", 1, 0, 1, 
				false, null, new VisualAlphaParam(0, "skirt_slit_front_alpha.tga", false, true), null);
		$this->addVisualParam(860, "Slit Back", 0, "skirt", "", "Open Back", "Closed Back", 1, 0, 1, 
				false, null, new VisualAlphaParam(0, "skirt_slit_back_alpha.tga", false, true), null);
		$this->addVisualParam(861, "Slit Left", 0, "skirt", "", "Open Left", "Closed Left", 1, 0, 1, 
				false, null, new VisualAlphaParam(0, "skirt_slit_left_alpha.tga", false, true), null);
		$this->addVisualParam(862, "Slit Right", 0, "skirt", "", "Open Right", "Closed Right", 1, 0, 1, 
				false, null, new VisualAlphaParam(0, "skirt_slit_right_alpha.tga", false, true), null);
		$this->addVisualParam(863, "skirt_looseness", 0, "skirt", "Skirt Fit", "Tight Skirt", "Poofy Skirt", 0.333, 0, 1, 
				false, array( 866, 846, 845 ), null, null);
		$this->addVisualParam(866, "skirt_tight", 1, "skirt", "tight skirt", "form fitting", "loose", 0, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(867, "skirt_smallbutt", 1, "skirt", "tight skirt", "form fitting", "loose", 0, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(868, "Shirt Wrinkles", 0, "shirt", "", "", "", 0, 0, 1, 
				true, null, null, null);
		$this->addVisualParam(869, "Pants Wrinkles", 0, "pants", "", "", "", 0, 0, 1, 
				true, null, null, null);
		$this->addVisualParam(870, "Pointy_Eyebrows", 1, "hair", "Eyebrow Points", "Smooth", "Pointy", -0.5, -0.5, 1, 
				false, null, null, null);
		$this->addVisualParam(871, "Lower_Eyebrows", 1, "hair", "Eyebrow Height", "Higher", "Lower", -2, -2, 2, 
				false, null, null, null);
		$this->addVisualParam(872, "Arced_Eyebrows", 1, "hair", "Eyebrow Arc", "Flat", "Arced", 0, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(873, "Bump base", 1, "skin", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0, "", false, false), null);
		$this->addVisualParam(874, "Bump upperdef", 1, "skin", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0, "", false, false), null);
		$this->addVisualParam(877, "Jacket Wrinkles", 0, "jacket", "Jacket Wrinkles", "No Wrinkles", "Wrinkles", 0, 0, 1, 
				false, array( 875, 876 ), null, null);
		$this->addVisualParam(878, "Bump upperdef", 1, "skin", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0, "", false, false), null);
		$this->addVisualParam(879, "Male_Package", 0, "shape", "Package", "Coin Purse", "Duffle Bag", 0, -0.5, 2, 
				false, null, null, null);
		$this->addVisualParam(880, "Eyelid_Inner_Corner_Up", 0, "shape", "Inner Eye Corner", "Corner Down", "Corner Up", -1.3, -1.3, 1.2, 
				false, null, null, null);
		$this->addVisualParam(899, "Upper Clothes Shading", 1, "shirt", "", "", "", 0, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 0), new Color4(0, 0, 0, 80) )));
		$this->addVisualParam(900, "Sleeve Length Shadow", 1, "shirt", "", "", "", 0.02, 0.02, 0.87, 
				false, null, new VisualAlphaParam(0.03, "shirt_sleeve_alpha.tga", true, false), null);
		$this->addVisualParam(901, "Shirt Shadow Bottom", 1, "shirt", "", "", "", 0.02, 0.02, 1, 
				false, null, new VisualAlphaParam(0.05, "shirt_bottom_alpha.tga", true, true), null);
		$this->addVisualParam(902, "Collar Front Shadow Height", 1, "shirt", "", "", "", 0.02, 0.02, 1, 
				false, null, new VisualAlphaParam(0.02, "shirt_collar_alpha.tga", true, true), null);
		$this->addVisualParam(903, "Collar Back Shadow Height", 1, "shirt", "", "", "", 0.02, 0.02, 1, 
				false, null, new VisualAlphaParam(0.02, "shirt_collar_back_alpha.tga", true, true), null);
		$this->addVisualParam(913, "Lower Clothes Shading", 1, "pants", "", "", "", 0, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 0), new Color4(0, 0, 0, 80) )));
		$this->addVisualParam(914, "Waist Height Shadow", 1, "pants", "", "", "", 0.02, 0.02, 1, 
				false, null, new VisualAlphaParam(0.04, "pants_waist_alpha.tga", true, false), null);
		$this->addVisualParam(915, "Pants Length Shadow", 1, "pants", "", "", "", 0.02, 0.02, 1, 
				false, null, new VisualAlphaParam(0.03, "pants_length_alpha.tga", true, false), null);
		$this->addVisualParam(921, "skirt_red", 0, "skirt", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(255, 0, 0, 255) )));
		$this->addVisualParam(922, "skirt_green", 0, "skirt", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(0, 255, 0, 255) )));
		$this->addVisualParam(923, "skirt_blue", 0, "skirt", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(0, 0, 255, 255) )));
		$this->addVisualParam(1000, "Eyebrow Size Bump", 1, "hair", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.1, "eyebrows_alpha.tga", false, false), null);
		$this->addVisualParam(1001, "Eyebrow Size", 1, "hair", "", "", "", 0.5, 0, 1, 
				false, null, new VisualAlphaParam(0.1, "eyebrows_alpha.tga", false, false), null);
		$this->addVisualParam(1002, "Eyebrow Density Bump", 1, "hair", "", "", "", 0, 0, 1, 
				true, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(255, 255, 255, 0), new Color4(255, 255, 255, 255) )));
		$this->addVisualParam(1003, "Eyebrow Density", 1, "hair", "", "", "", 0, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Blend, array( new Color4(255, 255, 255, 0), new Color4(255, 255, 255, 255) )));
		$this->addVisualParam(1004, "Sideburns bump", 1, "hair", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.05, "facehair_sideburns_alpha.tga", true, false), null);
		$this->addVisualParam(1005, "Sideburns", 1, "hair", "", "", "", 0, 0, 1, 
				false, null, new VisualAlphaParam(0.05, "facehair_sideburns_alpha.tga", true, false), null);
		$this->addVisualParam(1006, "Moustache bump", 1, "hair", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.05, "facehair_moustache_alpha.tga", true, false), null);
		$this->addVisualParam(1007, "Moustache", 1, "hair", "", "", "", 0, 0, 1, 
				false, null, new VisualAlphaParam(0.05, "facehair_moustache_alpha.tga", true, false), null);
		$this->addVisualParam(1008, "Soulpatch bump", 1, "hair", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.1, "facehair_soulpatch_alpha.tga", true, false), null);
		$this->addVisualParam(1009, "Soulpatch", 1, "hair", "", "", "", 0, 0, 1, 
				false, null, new VisualAlphaParam(0.1, "facehair_soulpatch_alpha.tga", true, false), null);
		$this->addVisualParam(1010, "Chin Curtains bump", 1, "hair", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.03, "facehair_chincurtains_alpha.tga", true, false), null);
		$this->addVisualParam(1011, "Chin Curtains", 1, "hair", "", "", "", 0, 0, 1, 
				false, null, new VisualAlphaParam(0.03, "facehair_chincurtains_alpha.tga", true, false), null);
		$this->addVisualParam(1012, "5 O'Clock Shadow bump", 1, "hair", "", "", "", 0, 0, 1, 
				true, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(255, 255, 255, 255), new Color4(255, 255, 255, 0) )));
		$this->addVisualParam(1013, "Sleeve Length Cloth", 1, "shirt", "", "", "", 0, 0, 0.85, 
				true, null, new VisualAlphaParam(0.01, "shirt_sleeve_alpha.tga", false, false), null);
		$this->addVisualParam(1014, "Shirt Bottom Cloth", 1, "shirt", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.05, "shirt_bottom_alpha.tga", false, true), null);
		$this->addVisualParam(1015, "Collar Front Height Cloth", 1, "shirt", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.05, "shirt_collar_alpha.tga", false, true), null);
		$this->addVisualParam(1016, "Collar Back Height Cloth", 1, "shirt", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.05, "shirt_collar_back_alpha.tga", false, true), null);
		$this->addVisualParam(1017, "Waist Height Cloth", 1, "pants", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.05, "pants_waist_alpha.tga", false, false), null);
		$this->addVisualParam(1018, "Pants Length Cloth", 1, "pants", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.01, "pants_length_alpha.tga", false, false), null);
		$this->addVisualParam(1019, "Jacket Sleeve Length bump", 1, "jacket", "", "", "", 0, 0, 1,
				true, null, new VisualAlphaParam(0.01, "shirt_sleeve_alpha.tga", false, false), null);
		$this->addVisualParam(1020, "jacket Sleeve Length", 1, "jacket", "", "", "", 0, 0, 1, 
				false, null, new VisualAlphaParam(0.01, "shirt_sleeve_alpha.tga", false, false), null);
		$this->addVisualParam(1021, "Jacket Collar Front bump", 1, "jacket", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.05, "shirt_collar_alpha.tga", false, true), null);
		$this->addVisualParam(1022, "jacket Collar Front", 1, "jacket", "", "", "", 0, 0, 1, 
				false, null, new VisualAlphaParam(0.05, "shirt_collar_alpha.tga", false, true), null);
		$this->addVisualParam(1023, "Jacket Collar Back bump", 1, "jacket", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.05, "shirt_collar_back_alpha.tga", false, true), null);
		$this->addVisualParam(1024, "jacket Collar Back", 1, "jacket", "", "", "", 0, 0, 1, 
				false, null, new VisualAlphaParam(0.05, "shirt_collar_back_alpha.tga", false, true), null);
		$this->addVisualParam(1025, "jacket bottom length upper bump", 1, "jacket", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.01, "jacket_length_upper_alpha.tga", false, true), null);
		$this->addVisualParam(1026, "jacket open upper bump", 1, "jacket", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.01, "jacket_open_upper_alpha.tga", false, true), null);
		$this->addVisualParam(1027, "jacket bottom length lower bump", 1, "jacket", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.01, "jacket_length_lower_alpha.tga", false, false), null);
		$this->addVisualParam(1028, "jacket open lower bump", 1, "jacket", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.01, "jacket_open_lower_alpha.tga", false, true), null);
		$this->addVisualParam(1029, "Sleeve Length Cloth", 1, "shirt", "", "", "", 0, 0, 0.85, 
				true, null, new VisualAlphaParam(0.01, "shirt_sleeve_alpha.tga", false, false), null);
		$this->addVisualParam(1030, "Shirt Bottom Cloth", 1, "shirt", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.05, "shirt_bottom_alpha.tga", false, true), null);
		$this->addVisualParam(1031, "Collar Front Height Cloth", 1, "shirt", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.05, "shirt_collar_alpha.tga", false, true), null);
		$this->addVisualParam(1032, "Collar Back Height Cloth", 1, "shirt", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.05, "shirt_collar_back_alpha.tga", false, true), null);
		$this->addVisualParam(1033, "jacket bottom length lower bump", 1, "jacket", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.01, "jacket_length_lower_alpha.tga", false, false), null);
		$this->addVisualParam(1034, "jacket open lower bump", 1, "jacket", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.01, "jacket_open_lower_alpha.tga", false, true), null);
		$this->addVisualParam(1035, "Waist Height Cloth", 1, "pants", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.05, "pants_waist_alpha.tga", false, false), null);
		$this->addVisualParam(1036, "Pants Length Cloth", 1, "pants", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.01, "pants_length_alpha.tga", false, false), null);
		$this->addVisualParam(1037, "jacket bottom length upper bump", 1, "jacket", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.01, "jacket_length_upper_alpha.tga", false, true), null);
		$this->addVisualParam(1038, "jacket open upper bump", 1, "jacket", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.01, "jacket_open_upper_alpha.tga", false, true), null);
		$this->addVisualParam(1039, "Jacket Sleeve Length bump", 1, "jacket", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.01, "shirt_sleeve_alpha.tga", false, false), null);
		$this->addVisualParam(1040, "Jacket Collar Front bump", 1, "jacket", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.05, "shirt_collar_alpha.tga", false, true), null);
		$this->addVisualParam(1041, "Jacket Collar Back bump", 1, "jacket", "", "", "", 0, 0, 1, 
				true, null, new VisualAlphaParam(0.05, "shirt_collar_back_alpha.tga", false, true), null);
		$this->addVisualParam(1042, "Sleeve Length", 1, "undershirt", "", "", "", 0.4, 0.01, 1, 
				false, null, new VisualAlphaParam(0.01, "shirt_sleeve_alpha.tga", false, false), null);
		$this->addVisualParam(1043, "Sleeve Length bump", 1, "undershirt", "", "", "", 0.4, 0.01, 1, 
				true, null, new VisualAlphaParam(0.01, "shirt_sleeve_alpha.tga", false, false), null);
		$this->addVisualParam(1044, "Bottom", 1, "undershirt", "", "", "", 0.8, 0, 1, 
				false, null, new VisualAlphaParam(0.05, "shirt_bottom_alpha.tga", false, true), null);
		$this->addVisualParam(1045, "Bottom bump", 1, "undershirt", "", "", "", 0.8, 0, 1,
				true, null, new VisualAlphaParam(0.05, "shirt_bottom_alpha.tga", false, true), null);
		$this->addVisualParam(1046, "Collar Front", 1, "undershirt", "", "", "", 0.8, 0, 1, 
				false, null, new VisualAlphaParam(0.05, "shirt_collar_alpha.tga", false, true), null);
		$this->addVisualParam(1047, "Collar Front bump", 1, "undershirt", "", "", "", 0.8, 0, 1,
				true, null, new VisualAlphaParam(0.05, "shirt_collar_alpha.tga", false, true), null);
		$this->addVisualParam(1048, "Collar Back", 1, "undershirt", "", "Low", "High", 0.8, 0, 1, 
				false, null, new VisualAlphaParam(0.05, "shirt_collar_back_alpha.tga", false, true), null);
		$this->addVisualParam(1049, "Collar Back bump", 1, "undershirt", "", "", "", 0.8, 0, 1, 
				true, null, new VisualAlphaParam(0.05, "shirt_collar_back_alpha.tga", false, true), null);
		$this->addVisualParam(1050, "Socks Length bump", 1, "socks", "", "", "", 0.35, 0, 1, 
				false, null, new VisualAlphaParam(0.01, "shoe_height_alpha.tga", false, false), null);
		$this->addVisualParam(1051, "Socks Length bump", 1, "socks", "", "", "", 0.35, 0, 1, 
				true, null, new VisualAlphaParam(0.01, "shoe_height_alpha.tga", false, false), null);
		$this->addVisualParam(1052, "Shoe Height", 1, "shoes", "", "", "", 0.1, 0, 1, 
				false, null, new VisualAlphaParam(0.01, "shoe_height_alpha.tga", false, false), null);
		$this->addVisualParam(1053, "Shoe Height bump", 1, "shoes", "", "", "", 0.1, 0, 1, 
				true, null, new VisualAlphaParam(0.01, "shoe_height_alpha.tga", false, false), null);
		$this->addVisualParam(1054, "Pants Length", 1, "underpants", "", "", "", 0.3, 0, 1, 
				false, null, new VisualAlphaParam(0.01, "pants_length_alpha.tga", false, false), null);
		$this->addVisualParam(1055, "Pants Length", 1, "underpants", "", "", "", 0.3, 0, 1, 
				true, null, new VisualAlphaParam(0.01, "pants_length_alpha.tga", false, false), null);
		$this->addVisualParam(1056, "Pants Waist", 1, "underpants", "", "", "", 0.8, 0, 1, 
				false, null, new VisualAlphaParam(0.05, "pants_waist_alpha.tga", false, false), null);
		$this->addVisualParam(1057, "Pants Waist", 1, "underpants", "", "", "", 0.8, 0, 1, 
				true, null, new VisualAlphaParam(0.05, "pants_waist_alpha.tga", false, false), null);
		$this->addVisualParam(1058, "Glove Length", 1, "gloves", "", "", "", 0.8, 0.01, 1, 
				false, null, new VisualAlphaParam(0.01, "glove_length_alpha.tga", false, false), null);
		$this->addVisualParam(1059, "Glove Length bump", 1, "gloves", "", "", "", 0.8, 0.01, 1, 
				true, null, new VisualAlphaParam(0.01, "glove_length_alpha.tga", false, false), null);
		$this->addVisualParam(1060, "Glove Fingers", 1, "gloves", "", "", "", 1, 0.01, 1, 
				false, null, new VisualAlphaParam(0.01, "gloves_fingers_alpha.tga", false, true), null);
		$this->addVisualParam(1061, "Glove Fingers bump", 1, "gloves", "", "", "", 1, 0.01, 1, 
				true, null, new VisualAlphaParam(0.01, "gloves_fingers_alpha.tga", false, true), null);
		$this->addVisualParam(1062, "tattoo_head_red", 1, "tattoo", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(255, 0, 0, 255) )));
		$this->addVisualParam(1063, "tattoo_head_green", 1, "tattoo", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(0, 255, 0, 255) )));
		$this->addVisualParam(1064, "tattoo_head_blue", 1, "tattoo", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(0, 0, 255, 255) )));
		$this->addVisualParam(1065, "tattoo_upper_red", 1, "tattoo", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(255, 0, 0, 255) )));
		$this->addVisualParam(1066, "tattoo_upper_green", 1, "tattoo", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(0, 255, 0, 255) )));
		$this->addVisualParam(1067, "tattoo_upper_blue", 1, "tattoo", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(0, 0, 255, 255) )));
		$this->addVisualParam(1068, "tattoo_lower_red", 1, "tattoo", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(255, 0, 0, 255) )));
		$this->addVisualParam(1069, "tattoo_lower_green", 1, "tattoo", "", "", "", 1, 0, 1,
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(0, 255, 0, 255) )));
		$this->addVisualParam(1070, "tattoo_lower_blue", 1, "tattoo", "", "", "", 1, 0, 1, 
				false, null, null, new VisualColorParam(VisualColorOperation.Add, array( new Color4(0, 0, 0, 255), new Color4(0, 0, 255, 255) )));
		$this->addVisualParam(1071, "tattoo_red", 2, "tattoo", "", "", "", 1, 0, 1,
				false, array( 1062, 1065, 1068 ), null, null);
		$this->addVisualParam(1072, "tattoo_green", 2, "tattoo", "", "", "", 1, 0, 1, 
				false, array( 1063, 1066, 1069 ), null, null);
		$this->addVisualParam(1073, "tattoo_blue", 2, "tattoo", "", "", "", 1, 0, 1, 
				false, array( 1064, 1067, 1070 ), null, null);
		$this->addVisualParam(1200, "Breast_Physics_UpDown_Driven", 1, "shape", "", "", "", 0, -3, 3, 
				false, null, null, null);
		$this->addVisualParam(1201, "Breast_Physics_InOut_Driven", 1, "shape", "", "", "", 0, -1.25, 1.25, 
				false, null, null, null);
		$this->addVisualParam(1202, "Belly_Physics_Legs_UpDown_Driven", 1, "physics", "", "", "", -1, -1, 1, 
				false, null, null, null);
		$this->addVisualParam(1203, "Belly_Physics_Skirt_UpDown_Driven", 1, "physics", "", "", "", 0, -1, 1, 
				false, null, null, null);
		$this->addVisualParam(1204, "Belly_Physics_Torso_UpDown_Driven", 1, "physics", "", "", "", 0, -1, 1, 
				false, null, null, null);
		$this->addVisualParam(1205, "Butt_Physics_UpDown_Driven", 1, "physics", "", "", "", 0, -1, 1, 
				false, null, null, null);
		$this->addVisualParam(1206, "Butt_Physics_LeftRight_Driven", 1, "physics", "", "", "", 0, -1, 1,
				 false, null, null, null);
		$this->addVisualParam(1207, "Breast_Physics_LeftRight_Driven", 1, "physics", "", "", "", 0, -2, 2, 
				false, null, null, null);
		$this->addVisualParam(10000, "Breast_Physics_Mass", 0, "physics", "Breast Physics Mass", "", "", 0.1, 0.1, 1, 
				false, null, null, null);
		$this->addVisualParam(10001, "Breast_Physics_Gravity", 0, "physics", "Breast Physics Gravity", "", "", 0, 0, 30, 
				false, null, null, null);
		$this->addVisualParam(10002, "Breast_Physics_Drag", 0, "physics", "Breast Physics Drag", "", "", 1, 0, 10, 
				false, null, null, null);
		$this->addVisualParam(10003, "Breast_Physics_UpDown_Max_Effect", 0, "physics", "Breast Physics UpDown Max Effect", "", "", 0, 0, 3, 
				false, null, null, null);
		$this->addVisualParam(10004, "Breast_Physics_UpDown_Spring", 0, "physics", "Breast Physics UpDown Spring", "", "", 10, 0, 100,
				false, null, null, null);
		$this->addVisualParam(10005, "Breast_Physics_UpDown_Gain", 0, "physics", "Breast Physics UpDown Gain", "", "", 10, 1, 100, 
				false, null, null, null);
		$this->addVisualParam(10006, "Breast_Physics_UpDown_Damping", 0, "physics", "Breast Physics UpDown Damping", "", "", 0.2, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(10007, "Breast_Physics_InOut_Max_Effect", 0, "physics", "Breast Physics InOut Max Effect", "", "", 0, 0, 3,
				false, null, null, null);
		$this->addVisualParam(10008, "Breast_Physics_InOut_Spring", 0, "physics", "Breast Physics InOut Spring", "", "", 10, 0, 100, 
				false, null, null, null);
		$this->addVisualParam(10009, "Breast_Physics_InOut_Gain", 0, "physics", "Breast Physics InOut Gain", "", "", 10, 1, 100, 
				false, null, null, null);
		$this->addVisualParam(10010, "Breast_Physics_InOut_Damping", 0, "physics", "Breast Physics InOut Damping", "", "", 0.2, 0, 1,
				false, null, null, null);
		$this->addVisualParam(10011, "Belly_Physics_Mass", 0, "physics", "Belly Physics Mass", "", "", 0.1, 0.1, 1, 
				false, null, null, null);
		$this->addVisualParam(10012, "Belly_Physics_Gravity", 0, "physics", "Belly Physics Gravity", "", "", 0, 0, 30, 
				false, null, null, null);
		$this->addVisualParam(10013, "Belly_Physics_Drag", 0, "physics", "Belly Physics Drag", "", "", 1, 0, 10,
				false, null, null, null);
		$this->addVisualParam(10014, "Belly_Physics_UpDown_Max_Effect", 0, "physics", "Belly Physics UpDown Max Effect", "", "", 0, 0, 3,
				false, null, null, null);
		$this->addVisualParam(10015, "Belly_Physics_UpDown_Spring", 0, "physics", "Belly Physics UpDown Spring", "", "", 10, 0, 100, 
				false, null, null, null);
		$this->addVisualParam(10016, "Belly_Physics_UpDown_Gain", 0, "physics", "Belly Physics UpDown Gain", "", "", 10, 1, 100, 
				false, null, null, null);
		$this->addVisualParam(10017, "Belly_Physics_UpDown_Damping", 0, "physics", "Belly Physics UpDown Damping", "", "", 0.2, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(10018, "Butt_Physics_Mass", 0, "physics", "Butt Physics Mass", "", "", 0.1, 0.1, 1,
				false, null, null, null);
		$this->addVisualParam(10019, "Butt_Physics_Gravity", 0, "physics", "Butt Physics Gravity", "", "", 0, 0, 30,
				false, null, null, null);
		$this->addVisualParam(10020, "Butt_Physics_Drag", 0, "physics", "Butt Physics Drag", "", "", 1, 0, 10,
				false, null, null, null);
		$this->addVisualParam(10021, "Butt_Physics_UpDown_Max_Effect", 0, "physics", "Butt Physics UpDown Max Effect", "", "", 0, 0, 3,
				false, null, null, null);
		$this->addVisualParam(10022, "Butt_Physics_UpDown_Spring", 0, "physics", "Butt Physics UpDown Spring", "", "", 10, 0, 100,
				false, null, null, null);
		$this->addVisualParam(10023, "Butt_Physics_UpDown_Gain", 0, "physics", "Butt Physics UpDown Gain", "", "", 10, 1, 100, 
				false, null, null, null);
		$this->addVisualParam(10024, "Butt_Physics_UpDown_Damping", 0, "physics", "Butt Physics UpDown Damping", "", "", 0.2, 0, 1, 
				false, null, null, null);
		$this->addVisualParam(10025, "Butt_Physics_LeftRight_Max_Effect", 0, "physics", "Butt Physics LeftRight Max Effect", "", "", 0, 0, 3,
				false, null, null, null);
		$this->addVisualParam(10026, "Butt_Physics_LeftRight_Spring", 0, "physics", "Butt Physics LeftRight Spring", "", "", 10, 0, 100,
				false, null, null, null);
		$this->addVisualParam(10027, "Butt_Physics_LeftRight_Gain", 0, "physics", "Butt Physics LeftRight Gain", "", "", 10, 1, 100,
				false, null, null, null);
		$this->addVisualParam(10028, "Butt_Physics_LeftRight_Damping", 0, "physics", "Butt Physics LeftRight Damping", "", "", 0.2, 0, 1,
				false, null, null, null);
		$this->addVisualParam(10029, "Breast_Physics_LeftRight_Max_Effect", 0, "physics", "Breast Physics LeftRight Max Effect", "", "", 0, 0, 3,
				false, null, null, null);
		$this->addVisualParam(10030, "Breast_Physics_LeftRight_Spring", 0, "physics", "Breast Physics LeftRight Spring", "", "", 10, 0, 100, 
				false, null, null, null);
		$this->addVisualParam(10031, "Breast_Physics_LeftRight_Gain", 0, "physics", "Breast Physics LeftRight Gain", "", "", 10, 1, 100, 
				false, null, null, null);
		$this->addVisualParam(10032, "Breast_Physics_LeftRight_Damping", 0, "physics", "Breast Physics LeftRight Damping", "", "", 0.2, 0, 1, 
				false, null, null, null);
	}
	

	private function addVisualParam(
							$paramid,
							$name, 
							$group, 
							$wearable,
							$label, 
							$labelmin, 
							$labelmax, 
							$defvalue, 
							$minvalue, 
							$maxvalue, 
							$isBumpAttribute, 
							$drivers, 
							$alpha, 
							$colorParams)
	{
		$vp = new VisualParam();
		$vp->VPIndex = -1;
		$vp->ParamID = $paramid;
		$vp->Name = $name;
		$vp->Group = $group;
		$vp->Wearable = $wearable;
		$vp->Label = $label;
		$vp->LabelMin = $labelmin;
		$vp->LabelMax = $labelmax;
		$vp->MinimumValue = floatval($minvalue);
		$vp->MaximumValue = floatval($maxvalue);
		$vp->DefaultValue = floatval($defvalue);
		$vp->IsBumpAttribute = $isBumpAttribute;
		$vp->Drivers = $drivers;
		$vp->AlphaParams = $alphaParams;
		$vp->ColorParams = $colorParams;
		
		/* register group 0 parameters to vp_index */
		if($vp->Group == 0)
		{
			$vp->VPIndex = count($this->vp_index);
			$this->vp_index[] = $vp;
		}
		
		/* register all parameters to our param id table */
		$vp->vp_paramid[$paramid] = $vp;
	}
	
	public buildVisualParams($wearables)
	{
		$visualParams = array();
		
		foreach($wearables as $wearable)
		{
			foreach($wearable->Params as $paraid => $paraval)
			{
				if(!isset($visualParams[$paraid]))
				{
					$visualParams[$paraid] = $paraval;
				}
			}
		}
		
		$byteVisualParams = array();
		
		foreach($this->vp_index as $vpidx => $vpobj)
		{
			if(isset($visualParams[$vpobj->ParamID]))
			{
				$value = $visualParams[$vpobj->ParamID];
			}
			else
			{
				$value = $vpobj->DefaultValue;
			}
		
			while(count($byteVisualParams) <= $vpidx)
			{
				$byteVisualParams[] = 0;
			}
			if($value > $vpobj->MaximumValue)
			{
				$out = 255;
			}
			else if($value < $vpobj->MinimumValue)
			{
				$out = 0;
			}
			else
			{
				$out = 255 * ($value - $vpobj->MinimumValue);
				$out /= ($vpobj->MaximumValue - $vpobj->MinimumValue);
				$out = intval($out);
			}
			$byteVisualParams[$vpidx] = $out;
		}
		return $byteVisualParams;
	}
}

$VisualParamsConfig = new VisualParams();
 
