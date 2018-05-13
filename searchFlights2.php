<?php
$GLOBALS['dep'] = ""; /*origin*/
$GLOBALS['dest'] = ""; /*destination*/
$destarr = array (); /*full array of flights fitting description - destination array*/

/*Creating API Functionality*/
header("Content-Type:application/json");

if(!empty($_GET['dep']) && !empty($_GET['dest']))
{
	$GLOBALS['dep']=$_GET['dep'];
	$GLOBALS['dest']=$_GET['dest'];
	addProviderFlights("Provider1.txt",$destarr,",");
	addProviderFlights("Provider2.txt",$destarr,",");
	addProviderFlights("Provider3.txt",$destarr,"|");
	selectFlights($destarr);
	printFlights($destarr);
	
	if(count($destarr)==0)
	{
		response(200,"Flights not found",NULL);
	}
	else
	{
		$data = $destarr;
		response(200,"Flights Found",$data);
	}
}
else
{
	response(400,"Invalid Request",NULL);
}

function response($status,$status_message,$data)
{
	header("HTTP/1.1 ".$status);
	
	$response['status']=$status;
	$response['status_message']=$status_message;
	$response['data']=$data;
	
	$json_response = json_encode($response);
	echo $json_response;
}
	
/*Function: Adds all flights from a provider text file to main array, skips empty lines*/
function addProviderFlights($textfile,&$destarr,$arg)
{
	$provider = fopen($textfile,"r");
	while(!feof($provider))
{
	$temp = explode($arg,fgets($provider));
	if(count($temp)>0)
	{
		array_push($destarr,$temp);
	}	
}
fclose($provider);	
}
/*Function: Filters flights based on origin and destination codes*/
function selectFlights(&$destarr)
{
	$temp = array();
	for($a=0;$a<count($destarr);$a++)
	{
		if(strcmp($destarr[$a][0],$GLOBALS['dep'])==0 && strcmp($destarr[$a][2],$GLOBALS['dest'])==0)
		{
			$destarr[$a][4] = str_replace("\r\n", "", $destarr[$a][4]);
			array_push($temp,$destarr[$a]);
		}
	}
	$destarr = $temp;
	changeDateTime($destarr);
	$destarr = array_unique($destarr,SORT_REGULAR);
}
/*Function: Change DateTime format to the standard necessary*/
function changeDateTime(&$arr)
{
	for($a=0;$a<count($arr);$a++)
	{
		$temp = explode('-',$arr[$a][1]);
		if(count($temp)==3)
			$arr[$a][1] = $temp[0]."/".$temp[1]."/".$temp[2];
		$temp = explode('-',$arr[$a][3]);
		if(count($temp)==3)
			$arr[$a][3] = $temp[0]."/".$temp[1]."/".$temp[2];
	}
}
/*Prints all flights in the right order*/
function printFlights(&$destarr)
{
		usort($destarr, 'sortValue');
		$temp=array();
		for($a=0;$a<count($destarr);$a++)
		{
			str_replace("\r\n","",$destarr[$a][4]);
			array_push($temp,array('origin' => $destarr[$a][0],'departure' => $destarr[$a][1],'destination' => $destarr[$a][2],'arrival' => $destarr[$a][3],'price' => $destarr[$a][4]));
		}
		$destarr = $temp;
}
/*Function: Helper Sorting function*/
function sortValue($a,$b)
{	
	if(floatval(str_replace('$','',$a[4])) == floatval(str_replace('$','',$b[4])))
	{	if($a[1] < $b[1])
			{return -1;}
		else if($a[1] > $b[1])
			{return 1;}
		else
			{return 0;}}
	else if (floatval(str_replace('$','',$a[4])) < floatval(str_replace('$','',$b[4])))
		{return -1;}
	else
		{return 1;}
}
?>