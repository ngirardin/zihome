<?php
include("includes/config.php");
include("includes/fifo.php");

function hex2str($hex) {
    $str = '';
    for($i=0;$i<strlen($hex);$i+=2) $str .= chr(hexdec(substr($hex,$i,2)));
    return $str;
}

function displayClusterId($cluster)
{
	$clusterTab["0000"]= " (General: Basic)";
	$clusterTab["0001"]= " (General: Power Config)";
	$clusterTab["0002"]= " (General: Temperature Config)";
	$clusterTab["0003"]= " (General: Identify)";
	$clusterTab["0004"]= " (General: Groups)";
	$clusterTab["0005"]= " (General: Scenes)";
	$clusterTab["0006"]= " (General: On/Off)";
	$clusterTab["0007"]= " (General: On/Off Config)";
	$clusterTab["0008"]= " (General: Level Control)";
	$clusterTab["0009"]= " (General: Alarms)";
	$clusterTab["000A"]= " (General: Time)";
	$clusterTab["000F"]= " (General: Binary Input Basic)";
	$clusterTab["0020"]= " (General: Poll Control)";
	$clusterTab["0019"]= " (General: OTA)";
	$clusterTab["0101"]= " (General: Door Lock";
	$clusterTab["0201"]= " (HVAC: Thermostat)";
	$clusterTab["0202"]= " (HVAC: Fan Control)";
	$clusterTab["0300"]= " (Lighting: Color Control)";
	$clusterTab["0400"]= " (Measurement: Illuminance)";
	$clusterTab["0402"]= " (Measurement: Temperature)";
	$clusterTab["0406"]= " (Measurement: Occupancy Sensing)";
	$clusterTab["0500"]= " (Security & Safety: IAS Zone)";
	$clusterTab["0702"]= " (Smart Energy: Metering)";
	$clusterTab["0B05"]= " (Misc: Diagnostics)";
	$clusterTab["1000"]= " (ZLL: Commissioning)";

	echo "  Cluster ID: " .$cluster;
	echo $clusterTab[$cluster]."\n";
	
}

function protocolDatas($datas)
{
	$tab="";
	$length=strlen($datas);
	if ($length>=12)
	{
		$crctmp = 0;
		//type de message
		$type=$datas[0].$datas[1].$datas[2].$datas[3];
		$crctmp= $crctmp ^ hexdec($datas[0].$datas[1]) ^ hexdec($datas[2].$datas[3]);
		//taille message
		$ln=$datas[4].$datas[5].$datas[6].$datas[7];
		$crctmp= $crctmp ^ hexdec($datas[4].$datas[5]) ^ hexdec($datas[6].$datas[7]);
		//acquisition du CRC
		$crc=$datas[8].$datas[9];
		//payload
		$payload="";
		for($i=0;$i<hexdec($ln);$i++)
		{
			$payload.=$datas[10+($i*2)].$datas[10+(($i*2)+1)];
			$crctmp= $crctmp ^ hexdec($datas[10+($i*2)].$datas[10+(($i*2)+1)]);
		}
		$quality = $datas[10+($i*2)-2].$datas[10+($i*2)-1];
		
		$payloadLength = strlen($payload)-2;
		
		//verification du CRC
		if ($crc == dechex($crctmp))
		{
			
			//Traitement PAYLOAD
			switch ($type)
			{
				
				case "8000" :
				
					echo " (Status)"."\n";
                    echo "  Length: ".substr($payload,0,4)."\n";
					echo "  Status: ".substr($payload,4,2)."\n";
					switch (substr($payload,4,2))
                    {
                        case "00":
                        {
                            echo " (Success)"."\n";
                        }
                        break;

                        case "01":
                        {
							echo " (Incorrect Parameters)"."\n";
                        }
                        break;

                        case "02":
                        {
							echo " (Unhandled Command)"."\n";
                        }
                        break;

                        case "03":
                        {
							echo " (Command Failed)"."\n";
                        }
                        break;

                        case "04":
                        {
							echo " (Busy)"."\n";
                        }
                        break;

                        case "05":
                        {
							echo " (Stack Already Started)"."\n";
                        }
                        break;

                        default:
                        {
							echo " (ZigBee Error Code)"."\n";
                        }
                        break;
                    }
					echo "SQN: : ".substr($payload,6,2)."\n";
                   
                    if (hexdec(substr($payload,0,4)) > 2)
                    {
						echo  "  Message: ";
                        echo hex2str(substr($payload,8,strlen($payload)-2))."\n";
                    }
				
				break;
				case "8001" :
					echo " (Log)";
                    echo  "\n";
                    echo "  Level: 0x".substr($payload,0,2);
                    echo "\n";
                    echo  "  Message: ";
					
                    echo hex2str(substr($payload,2,strlen($payload)-2))."\n";
				
				break;
				case "8010" :
					echo "(Version)\n";
					echo "Application : ".hexdec(substr($payload,0,4))."\n";
					echo "SDK : ".hexdec(substr($payload,4,4))."\n";
				
				break;
				case "8102" :
					echo "[".date("Y-m-d H:i:s")."]\n";
					echo "(Attribute Report)\n";
					echo "Src Addr : ".substr($payload,2,4)."\n";
					echo "Cluster ID : ".substr($payload,8,4)."\n";
					echo "Attr ID : ".substr($payload,12,4)."\n";
					echo "Attr Type : ".substr($payload,16,4)."\n";
					echo "Attr Size : ".substr($payload,20,4)."\n";
					echo "Quality : ".$quality."\n";
					if ((substr($payload,8,4)=="0000") && (substr($payload,12,4)=="0001"))
					{
						echo "DATAS: ".substr($payload,24,(strlen($payload)-24))."\n\n";
					}elseif ((substr($payload,8,4)=="0000") && (substr($payload,12,4)=="0005"))
					{
						echo "DATAS: ".hex2str(substr($payload,24,(substr($payload,20,4))*2))."\n\n";
					}else{
						echo "DATAS: ".substr($payload,24,(substr($payload,20,4))*2)."\n\n";
					}
					
				
				break;
				case "004d" :
				
					echo "(Device announce)\n";
					echo "Src Addr : ".substr($payload,0,4)."\n";
					echo "IEEE : ".substr($payload,4,8)."\n";
					echo "MAC capa : ".substr($payload,12,2)."\n";
					echo "Quality : ".$quality;
				
				break;
				case "8702" :
				
					echo "(APS Data Confirm Fail)\n";
					echo "Status : ".substr($payload,0,2)."\n";
					echo "Source Endpoint : ".substr($payload,2,2)."\n";
					echo "Destination Endpoint : ".substr($payload,4,2)."\n";
					echo "Destination Mode : ".substr($payload,6,2)."\n";
					echo "Destination Address : ".substr($payload,8,4)."\n";
					echo "SQN: : ".substr($payload,12,2)."\n";
									
				break;
				case "8101" :
				
					echo "(Default Response)\n";
					echo "SQN : ".substr($payload,0,2)."\n";
					echo "EndPoint : ".substr($payload,2,2)."\n";
					displayClusterId(substr($payload,4,4));
					echo "Command : ".substr($payload,8,2)."\n";
					echo "Status : ".substr($payload,10,2)."\n";
									
				break;
				case "8045" :
				
					echo "(Active Endpoints Response)\n";
					echo "SQN : ".substr($payload,0,2)."\n";
					echo "Status : ".substr($payload,2,2)."\n";
					echo "Short Address : ".substr($payload,4,4)."\n";
					echo "Endpoint Count : ".substr($payload,8,2)."\n";
					echo "Endpoint List :" ."\n";
					for ($i = 0; $i < (intval(substr($payload,8,2)) *2); $i+=2)
                    {
						echo "Endpoint : ".substr($payload,(8+$i),2)."\n";
					}
					
				
				break;
				case "8043" :
				
					echo "(Simple Descriptor Response)\n";
					echo "SQN : ".substr($payload,0,2)."\n";
					echo "Status : ".substr($payload,2,2)."\n";
					echo "Short Address : ".substr($payload,4,4)."\n";
					echo "Length : ".substr($payload,8,2)."\n";
					
					if (intval(substr($payload,8,2))>0)
                    {
						echo "Endpoint : ".substr($payload,10,2)."\n";
						
						//PAS FINI
					}
				
					
				
				
				break;
				default:
				
				break;
				
				
				
				
			}
			
		}else{
			$tab=-2;
		}

		
	}else{
		$tab=-1;
	}
	
	return $tab;
}


$fifoIN = new fifo( $in, 'r' );

while (true)
{
	//traitement de chaque trame;
	$data= $fifoIN->read();
	echo protocolDatas($data);
	usleep(1);
		
}

?>