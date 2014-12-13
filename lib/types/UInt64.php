<?php

if(function_exists("gmp_init"))
{
	function uint64_init($val)
	{
		return gmp_init($val);
	}
	
	function uint64_mod($vala, $valb)
	{
		return gmp_mod($vala, $valb);
	}
	
	function uint64_mul($vala, $valb)
	{
		return gmp_mul($vala, $valb);
	}
	
	function uint64_div($vala, $valb)
	{
		return gmp_div($vala, $valb);
	}
	
	function uint64_add($vala, $valb)
	{
		return gmp_add($vala, $valb);
	}
	
	function uint64_and($vala, $valb)
	{
		/* only one bit testable */
		return gmp_cmp(gmp_and($vala, $valb), "0") != 0;
	}
	
	function uint64_pow($vala, $valb)
	{
		return gmp_pow($vala, $valb);
	}
	
	function uint64_strval($v)
	{
		return gmp_strval($v);
	}
	
	function uint64_intval($v)
	{
		return gmp_intval($v);
	}
	
	function uint64_or($vala, $valb)
	{
		return gmp_or($vala, $valb);
	}
}
else if(function_exists("bcmul"))
{
	function uint64_init($val)
	{
		return $val;
	}
	
	function uint64_mod($vala, $valb)
	{
		return bcmod($vala, $valb);
	}
	
	function uint64_mul($vala, $valb)
	{
		return bcmul($vala, $valb);
	}
	
	function uint64_div($vala, $valb)
	{
		return bcdiv($vala, $valb);
	}
	
	function uint64_add($vala, $valb)
	{
		return bcadd($vala, $valb);
	}
	
	function uint64_and($vala, $valb)
	{
		/* only one bit testable */
		return bcmod(bcdiv($vala, $valb), 2) != "0";
	}
	
	function uint64_pow($vala, $valb)
	{
		return bcpow($vala, $valb);
	}
	
	function uint64_strval($v)
	{
		return $v;
	}
	
	function uint64_intval($v)
	{
		return (int)$v;
	}

	function uint64_or($vala, $valb)
	{
		$mask = "1";
		$out = "0";
		$mul = "1";
		for($wordpos = 0; $wordpos < 64; $wordpos += 16)
		{
			$va = (int)bcmod($vala, "65536");
			$vb = (int)bcmod($valb, "65536");
			$vala = bcdiv($vala, "65536");
			$valb = bcdiv($valb, "65536");
			$v = $va | $vb;
                        $out = bcadd(bcmul($v, $mul), $out);
                        $mul = bcmul($mul, "65536");
 		}
		
		return $out;
	}
}
