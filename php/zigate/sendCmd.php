<?php
include("includes/config.php");

function getChecksum($msgtype,$length,$datas)
{
	$temp = 0;

	$temp ^= hexdec($msgtype[0].$msgtype[1]) ;
	$temp ^= hexdec($msgtype[2].$msgtype[3]) ;
	$temp ^= hexdec($length[0].$length[1]) ;
	$temp ^= hexdec($length[2].$length[3]);
	for ($i=0;$i<=(strlen($datas));$i+=2)
	{
		$temp ^= hexdec($datas[$i].$datas[$i+1]);
	}
	return sprintf("%02X",$temp);
}

function transcode($datas)
{
	$mess="";
	if (strlen($datas)%2 !=0)
	{
		return -1;
	}
	for ($i=0;$i<(strlen($datas));$i+=2)
	{
		$byte = $datas[$i].$datas[$i+1];
		
		if ($byte>10)
		{
			 $mess.=$byte;
			
		}else{
			 $mess.="02".sprintf("%02X",(hexdec($byte) ^ 0x10));
		}
	}
	
	return $mess;
}

function sendCmd($cmd,$len,$datas)
{
	$f=fopen(COM,"w");
	
	fwrite($f,pack("H*","01"));
	fwrite($f,pack("H*",transcode($cmd))); //MSG TYPE 
	fwrite($f,pack("H*",transcode($len))); //LENGTH 
	if (!empty($datas))
	{
		fwrite($f,pack("H*",getChecksum($cmd,$len,$datas))); //checksum
		fwrite($f,pack("H*",transcode($datas))); //datas
	}else{
		fwrite($f,pack("H*",getChecksum($cmd,$len,"0"))); //checksum
	}
	fwrite($f,pack("H*","03"));
	fclose($f);
}



if (!empty($_POST))
{
	
	if ($_POST['getVersion']=="Version")
	{
		sendCmd("0010","0000","");
	}
	elseif ($_POST['SetMaskBtn']=="ok")
	{
		sendCmd("0021","0004","00000800");
	}elseif ($_POST['startNetwork']=="StartNetwork")
	{	
		sendCmd("0024","0000","");
	
	}elseif ($_POST['scan']=="Scan")
	{
		sendCmd("0025","0000",""); 
				
	}elseif ($_POST['reset']=="Reset")
	{	
		sendCmd("0011","0000","");
	
	}elseif ($_POST['getStatut']=="Statut")
	{
		sendCmd("0014","0000","");
	}elseif ($_POST['SetPermit']=="Inclusion")
	{
		sendCmd("0049","0004","FFFC1E"); //1E = 30 secondes
				
	}elseif ($_POST['erase']=="Erase")
	{
		sendCmd("0012","0000","");
				
	}elseif (!empty($_POST['activereq']))
	{
		if (!empty($_POST['addressAR']) && (strlen($_POST['addressAR'])==4))
		{
			sendCmd("0045","0002",$_POST['addressAR']);
		}else{
			echo "error parameter";
		}
				
	}elseif (!empty($_POST['simplereq']))
	{
		if (!empty($_POST['addressSR']) && (strlen($_POST['addressSR'])==4)&& (strlen($_POST['endpointSR'])==2)&& (!empty($_POST['endpointSR'])))
		{
			
			sendCmd("0043","0003",$_POST['addressSR'].$_POST['endpointSR']);
		}else{
			echo "error parameters";
		}
				
	}elseif (!empty($_POST['onoff']))
	{
		if (!empty($_POST['addressOnOff']) && (strlen($_POST['addressOnOff'])==4)&& (strlen($_POST['endpointOnOff'])==2)&& (!empty($_POST['endpointOnOff'])))
		{
			sendCmd("0092","0006","02".$_POST['addressOnOff']."01".$_POST['endpointOnOff'].$_POST['action']);
		}else{
			echo "error parameters";
		}
				
	}
}


?>
<form method='POST' >
	<input type="submit" name="getVersion" value="Version"><br><br>
	
	Channel : 
	<input type="text" name="SetMask" value="">
	<input type="submit" name="SetMaskBtn" value="ok"><br><br>
	<input type="submit" name="startNetwork" value="StartNetwork"><input type="submit" name="scan" value="ScanNetwork"><br><br>
	<input type="submit" name="SetPermit" value="Inclusion"><br><br>	
	<input type="submit" name="getStatut" value="Statut"><br><br>
	<input type="submit" name="reset" value="Reset"><br><br>
	<input type="submit" name="erase" value="Erase"><br><br>
	<hr>
	@:<input type="text" name="addressAR" placeholder="0000" value=""><input type="submit" name="activereq" value="Active Req"><br><br>
	@:<input type="text" name="addressSR" placeholder="0000" value="">ep:<input type="text"  placeholder="01" name="endpointSR" value=""><input type="submit" name="simplereq" value="Simple req"><br><br>
	<hr>
	Commande On/Off (pour les ampoules ou les prises pilotable)<br>
	<input type="submit" name="onoff" value="Go">@:<input type="text" name="addressOnOff" placeholder="0000" value="">ep:<input type="text"  placeholder="01" name="endpointOnOff" value="">cmd:<select name="action"><option value="00">OFF<option value="01">ON<option value="02">TOGGLE</select>
		
	

</form>