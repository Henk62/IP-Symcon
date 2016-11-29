<?
// WPCU_Installation.ips.php   Henk Wijgerde  09-10-2016

IPSUtils_Include("IPSInstaller.inc.php",			"IPSLibrary::install::IPSInstaller");

$cData = CreateCategoryPath('Program.IPSLibrary.data.hardware.warmtepomp'); 					// Modbus devices
$cMobile = CreateCategoryPath('Visualization.Mobile.Binnenklimaat.Warmtepomp'); 				// Dummy instances
$ArchiveId = IPS_GetInstanceListByModuleID ('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0]; // Archive Handler

// Create connection to SER2NET deamon and setup a ModBus Gateway.
$cs=CreateClientSocket("Waterkotte Modbus", "192.168.1.66", "7979");
$mg=CreateModBusGateway("Waterkotte Modbus Gateway");
IPS_DisconnectInstance($mg);
IPS_ConnectInstance($mg,$cs);

$iFast  	= 180000;	// High poll frequency: 3 minutes
$iSlow  	= 900000;	// Low poll frequency: 15 minutes

// Create variable profiles
If (IPS_VariableProfileExists('WPCU_ManualOff.Reversed') == false) {
	IPS_CreateVariableProfile('WPCU_ManualOff.Reversed',0);
	IPS_SetVariableProfileIcon("WPCU_ManualOff.Reversed","Power");
	IPS_SetVariableProfileAssociation('WPCU_ManualOff.Reversed',0,'On','',0x00FF00);
	IPS_SetVariableProfileAssociation('WPCU_ManualOff.Reversed',1,'Off','',0x00FF00);
}
If (IPS_VariableProfileExists('WPCU_OperatingTime') == false) {
	IPS_CreateVariableProfile('WPCU_OperatingTime',2);
	IPS_SetVariableProfileDigits("WPCU_OperatingTime", 1);
	IPS_SetVariableProfileText('WPCU_OperatingTime',""," H");
	IPS_SetVariableProfileIcon("WPCU_OperatingTime",  "Clock");
}
If (IPS_VariableProfileExists('WPCU_Percentage') == false) {
	IPS_CreateVariableProfile('WPCU_Percentage',2);
	IPS_SetVariableProfileText('WPCU_Percentage',""," %");
	IPS_SetVariableProfileDigits("WPCU_Percentage", 1);
	IPS_SetVariableProfileIcon("WPCU_Percentage",  "HollowArrowUp");
}
If (IPS_VariableProfileExists('WPCU_Temperature') == false) {
	IPS_CreateVariableProfile('WPCU_Temperature',2);
	IPS_SetVariableProfileText('WPCU_Temperature',""," °C");
	IPS_SetVariableProfileDigits("WPCU_Temperature", 1);
	IPS_SetVariableProfileValues('WPCU_Temperature',15,22,0.1);
	IPS_SetVariableProfileIcon("WPCU_Temperature","Gauge");
}
If (IPS_VariableProfileExists('WPCU_HWTemperature') == false) {
	IPS_CreateVariableProfile('WPCU_HWTemperature',2);
	IPS_SetVariableProfileText('WPCU_HWTemperature',""," °C");
	IPS_SetVariableProfileDigits('WPCU_HWTemperature', 0);
	IPS_SetVariableProfileValues('WPCU_HWTemperature',45,60,1);
	IPS_SetVariableProfileIcon("WPCU_HWTemperature","Gauge");
}


// Create Modbus devices
$mOutdoorTemp					= CreateModbusDevice("mOutdoorTemp", 								 433, 0, $iFast, 7, '~Temperature', true);
$mOutdoorTemp1h				= CreateModbusDevice("mOutdoorTemp1h", 							 465, 0, $iFast, 7, '~Temperature', true);
$mOutdoorTemp24h				= CreateModbusDevice("mOutdoorTemp24h", 							 497, 0, $iFast, 7, '~Temperature', true);

$mEvaporationTemp				= CreateModbusDevice("mEvaporationTemp"			, 				 593, 0, $iFast, 7, '~Temperature', true);
$mSuctionGasTemp				= CreateModbusDevice("mSuctionGasTemp",				 			 625, 0, $iFast, 7, '~Temperature', true);
$mEvaporationPress			= CreateModbusDevice("mEvaporationPress", 						 657, 0, $iFast, 7, '~AirPressure.F', true);
$mCondensationTemp			= CreateModbusDevice("mCondensationTemp", 						 785, 0, $iFast, 7, '~Temperature', true);
$mCondensationPress			= CreateModbusDevice("mCondensationPress", 						 817, 0, $iFast, 7, '~AirPressure.F', true);
$mFlowTemp						= CreateModbusDevice("mFlowTemp", 									 753, 0, $iFast, 7, '~Temperature', true);
$mReturnTemp					= CreateModbusDevice("mReturnTemp", 								 721, 0, $iFast, 7, '~Temperature', true);
$mReturnTempNominal			= CreateModbusDevice("mReturnTempNominal", 						 689, 0, $iFast, 7, '~Temperature', true);
$mHeatSourceIn					= CreateModbusDevice("mHeatSourceIn", 								 529, 0, $iFast, 7, '~Temperature', true);
$mHeatSourceOut				= CreateModbusDevice("mHeatSourceOut", 							 561, 0, $iFast, 7, '~Temperature', true);
//$DomesticWaterTemp			= CreateModbusDevice("DomesticWaterTemp", 						 913, 0, $iFast, 7, '~Temperature', false);

$HeatOff							= CreateModbusDevice("HeatOff", 										1009, 1009, $iSlow, 0, 'WPCU_ManualOff.Reversed', true);
$HeatSetPoint					= CreateModbusDevice("HeatCharacteristicSetPoint", 			1057, 1057, $iSlow, 7, 'WPCU_Temperature', true);
$HeatSetPointBaseTemp		= CreateModbusDevice("HeatCharacteristicSetPointBaseTemp",	1089, 0, $iSlow, 7, '~Temperature', true);
$HeatGradient					= CreateModbusDevice("HeatCharacteristicGradient", 			1121, 0, $iSlow, 7, 'WPCU_Percentage', true);
//$HeatLimit					= CreateModbusDevice("HeatCharacteristicLimit", 				1153, 0, $iSlow, 7, '~Temperature', true);
$HeatReturnTemp				= CreateModbusDevice("HeatReturnTemp", 							1185, 0, $iFast, 7, '~Temperature', true);
$HeatReturnTempNominal		= CreateModbusDevice("HeatReturnTempNominal", 					1217, 0, $iFast, 7, '~Temperature', true);
$HeatTempHyst					= CreateModbusDevice("HeatTempHyst", 								1249, 0, $iSlow, 7, '~Temperature.Difference', true);

$CoolOff							= CreateModbusDevice("CoolOff", 										1457, 1457, $iSlow, 0, 'WPCU_ManualOff.Reversed', true);
$CoolCharacteristicSetPoint= CreateModbusDevice("CoolCharacteristicSetPoint", 			1505, 1505, $iSlow, 7, 'WPCU_Temperature', true);
$CoolReturnTemp				= CreateModbusDevice("CoolReturnTemp", 							1537, 0, $iFast, 7, '~Temperature', true);
$CoolReturnTempNominal		= CreateModbusDevice("CoolReturnTempNominal", 					1569, 0, $iSlow, 7, '~Temperature', true);
$CoolReturnTempHyst			= CreateModbusDevice("CoolReturnTempHyst", 						1601, 0, $iSlow, 7, '~Temperature.Difference', true);

$DomesticWaterOff				= CreateModbusDevice("DomesticWaterOff", 							1633, 1633, $iSlow, 0, 'WPCU_ManualOff.Reversed', true);
$DomesticWaterTempActual	= CreateModbusDevice("DomesticWaterTempActual", 				1681, 0, $iFast, 7, '~Temperature', true);
$DomesticWaterTempNominal	= CreateModbusDevice("DomesticWaterTempNominal", 				1713, 1713, $iSlow, 7, 'WPCU_HWTemperature', true);
$DomesticWaterTempHyst		= CreateModbusDevice("DomesticWaterTempHyst", 					1745, 0, $iSlow, 7, '~Temperature.Difference', true);

$OHCooling						= CreateModbusDevice("OHCooling", 									2753, 0, $iSlow, 7, 'WPCU_OperatingTime', false);
$OHTotalCompressor			= CreateModbusDevice("OHTotalCompressor", 						2625, 0, $iSlow, 7, 'WPCU_OperatingTime', false);
$OHHeatingCompressor			= CreateModbusDevice("OHHeatingCompressor", 						2689, 0, $iSlow, 7, 'WPCU_OperatingTime', false);
$OHWaterCompressor			= CreateModbusDevice("OHDomesticWaterCompressor", 				2785, 0, $iSlow, 7, 'WPCU_OperatingTime', false);

// Create Visualization instances and links for Mobile interface.
$module1	 = CreateDummyInstance('Buitentemperatuur', $cMobile , 1);
CreateLink("Actueel", 				@IPS_GetVariableIDByName("Value",$mOutdoorTemp), 				$module1,1);
CreateLink("Uur", 					@IPS_GetVariableIDByName("Value",$mOutdoorTemp1h), 			$module1,2);
CreateLink("Dag", 					@IPS_GetVariableIDByName("Value",$mOutdoorTemp24h), 			$module1,3);

$module2	 = CreateDummyInstance('Verwarming', $cMobile , 2);
CreateLink("Status", 				@IPS_GetVariableIDByName("Value",$HeatOff), 						$module2,1);
CreateLink("Buiten inschakelen", @IPS_GetVariableIDByName("Value",$HeatSetPoint), 				$module2,2);
CreateLink("Retour inschakelen", @IPS_GetVariableIDByName("Value",$HeatSetPointBaseTemp), 	$module2,3);
CreateLink("Stijlheid", 			@IPS_GetVariableIDByName("Value",$HeatGradient), 				$module2,4);
CreateLink("Hysterese", 			@IPS_GetVariableIDByName("Value",$HeatTempHyst), 				$module2,5);
CreateLink("Retour doel", 			@IPS_GetVariableIDByName("Value",$HeatReturnTempNominal), 	$module2,6);
CreateLink("Retour actueel",		@IPS_GetVariableIDByName("Value",$HeatReturnTemp), 			$module2,7);

$module3	 = CreateDummyInstance('Boiler', $cMobile , 3);
CreateLink("Status", 				@IPS_GetVariableIDByName("Value",$DomesticWaterOff), 			$module3,1);
CreateLink("Hysterese", 			@IPS_GetVariableIDByName("Value",$DomesticWaterTempHyst), 	$module3,2);
CreateLink("Doel", 					@IPS_GetVariableIDByName("Value",$DomesticWaterTempNominal),$module3,3);
CreateLink("Actueel",				@IPS_GetVariableIDByName("Value",$DomesticWaterTempActual), $module3,4);

$module4	 = CreateDummyInstance('Koeling', $cMobile , 4);
CreateLink("Status", 				@IPS_GetVariableIDByName("Value",$CoolOff), 						$module4,1);
CreateLink("Buiten inschakelen",	@IPS_GetVariableIDByName("Value",$CoolCharacteristicSetPoint),$module4,2);
CreateLink("Hysterese", 			@IPS_GetVariableIDByName("Value",$CoolReturnTempHyst), 		$module4,3);
CreateLink("Retour doel",			@IPS_GetVariableIDByName("Value",$CoolReturnTempNominal),	$module4,4);
CreateLink("Retour Actueel",		@IPS_GetVariableIDByName("Value",$CoolReturnTemp), 			$module4,5);

$module5	 = CreateDummyInstance('Bedrijfsuren', $cMobile , 6);
CreateLink("Compressor", 			@IPS_GetVariableIDByName("Value",$OHTotalCompressor), 		$module5,1);
CreateLink("Verwarming", 			@IPS_GetVariableIDByName("Value",$OHHeatingCompressor),	 	$module5,2);
CreateLink("Boiler", 				@IPS_GetVariableIDByName("Value",$OHWaterCompressor), 		$module5,3);
CreateLink("Koeling", 				@IPS_GetVariableIDByName("Value",$OHCooling), 					$module5,4);

$module6	 = CreateDummyInstance('Compressor', $cMobile , 5);
CreateLink("Bron in", 				@IPS_GetVariableIDByName("Value",$mHeatSourceIn), 				$module6,1);
CreateLink("Bron uit", 				@IPS_GetVariableIDByName("Value",$mHeatSourceOut), 			$module6,2);
CreateLink("Condensor",				@IPS_GetVariableIDByName("Value",$mCondensationTemp), 		$module6,3);
CreateLink("Condensor ",			@IPS_GetVariableIDByName("Value",$mCondensationPress), 		$module6,4);
CreateLink("Verdamper", 			@IPS_GetVariableIDByName("Value",$mEvaporationTemp), 			$module6,5);
CreateLink("Verdamper ",			@IPS_GetVariableIDByName("Value",$mEvaporationPress),		 	$module6,6);
CreateLink("Zuiggasleiding",	 	@IPS_GetVariableIDByName("Value",$mSuctionGasTemp), 			$module6,7);
CreateLink("CV in", 					@IPS_GetVariableIDByName("Value",$mFlowTemp), 					$module6,8);
CreateLink("CV doel", 				@IPS_GetVariableIDByName("Value",$mReturnTempNominal), 		$module6,9);
CreateLink("CV uit", 				@IPS_GetVariableIDByName("Value",$mReturnTemp), 			   $module6,10);


function CreateModbusDevice($Name, $rAddr, $wAddr, $interval, $Type, $Profile, $Logging) {
	GLOBAL $ArchiveId;
	GLOBAL $cData;
	$id = @IPS_GetInstanceIDByName($Name,$cData);
   if ($id == false) {
		Echo "\nCreate Modbus device ".$Name;
	   $id = IPS_CreateInstance("{CB197E50-273D-4535-8C91-BB35273E3CA5}");
	   IPS_SetName($id, $Name);
		IPS_SetParent($id, $cData);
	   IPS_SetProperty($id,'ReadAddress',$rAddr);
	   IPS_SetProperty($id,'WriteAddress',$wAddr);
	   IPS_SetProperty($id,'Poller',$interval);
	   if ($wAddr > 0)
		   IPS_SetProperty($id,'ReadOnly',false);
		else
		   IPS_SetProperty($id,'ReadOnly',true);
	   IPS_SetProperty($id,'Factor',0);
	   IPS_SetProperty($id,'EmulateStatus',true);
	   IPS_SetProperty($id,'DataType',$Type); // 0=Bit, 1=Byte, 2=Word, 3=DWord, 4=ShortInt, 5=SmallInt, 6=Integer, 7=Real
	   IPS_ApplyChanges($id);
	   $vd = @IPS_GetVariableIDByName("Value",$id);
	   IPS_SetVariableCustomProfile ($vd,$Profile);
		AC_SetLoggingStatus($ArchiveId, $vd, $Logging);
		IPS_ApplyChanges($ArchiveId);
	   }
	else
		Echo "\nExisting Modbus device ".$Name;
	return $id;
}

function CreateClientSocket($Name, $IPaddress, $Port,$Position=0) {
		$id = CreateInstance($Name, 0, "{3CFF0FD9-E306-41DB-9B5A-9D06D38576C3}",$Position);
		IPS_SetProperty($id,'Host',$IPaddress);
		IPS_SetProperty($id,'Port',$Port);
		IPS_SetProperty($id,'Open',true);
		if (!@IPS_ApplyChanges($id)) {
			//Error ("Error applying Changes to client socket Instance ".$Name);
			Echo "Error applying Changes to client socket Instance ".$Name;
		};
		return $id;
}

function CreateModBusGateway($Name, $Position=0) {
		$id = CreateInstance($Name, 0, "{A5F663AB-C400-4FE5-B207-4D67CC030564}",$Position);
		IPS_SetProperty($id,'GatewayMode',2); 	//Modbus RTU
		IPS_SetProperty($id,'SwapWords',true); // Swap LSW/MSW
		if (!@IPS_ApplyChanges($id)) {
			//Error ("Error applying Changes to client ModBus Gateway ".$Name);
			Echo "Error applying Changes to ModBus Gateway ".$Name;
		};
		return $id;
}

?>
