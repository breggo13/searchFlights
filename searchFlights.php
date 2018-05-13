<?php
/*******************************************************************
* TripStack Coding Assignment - Web Application
* Natasha Ukolova - May 12th, 2018
* PHP API with proper call "localhost/{Origin}/{Destination}"
********************************************************************/


$GLOBALS['dep'] = ""; /*origin*/
$GLOBALS['dest'] = ""; /*destination*/
$destarr = array (); /*full array of flights fitting description - destination array*/

/*Creating API Functionality*/
header("Content-Type:application/json");

if(!empty($_GET['dep']) && !empty($_GET['dest']))
{
	$GLOBALS['dep']=$_GET['dep'];
	$GLOBALS['dest']=$_GET['dest'];
}
else
{
	response(400,"Invalid Request",NULL);
}

/*Function: Creates a JSON object to be passed back to the front end
@param: int $status, string $status_message, array $data
@return: Echoes json object*/
function response($status,$status_message,$data)
{
	header("HTTP/1.1 ".$status);
	
	$response['status']=$status;
	$response['status_message']=$status_message;
	$response['data']=$data;
	
	$json_response = json_encode($response);
	echo $json_response;
}
	
/*Function: Adds all flights from a provider text file to main array, skips empty lines
@param: textfile name $textfile, array $destarr, char $arg
*/
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
/*Function: Filters flights based on origin and destination codes
@param: array $destarr
*/
function selectFlights(&$destarr)
{
	$temp = array();
	for($a=0;$a<count($destarr);$a++)
	{
		if(strcmp($destarr[$a][0],$GLOBALS['dep'])==0 && strcmp($destarr[$a][2],$GLOBALS['dest'])==0)
		{
			array_push($temp,$destarr[$a]);
		}
	}
	$destarr = $temp;
	changeDateTime($destarr);
	$destarr = array_unique($destarr,SORT_REGULAR);
}
/*Function: Change DateTime format to the standard necessary
@param: array $arr
*/
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
/*Function: Prints all flights in the right order
@param: array $destarr
*/
function printFlights($destarr)
{
	if(count($destarr)==0)
	{
		echo "No Flights Found for ".$GLOBALS['dep']." --> ".$GLOBALS['dest'];
	}
	else
	{
		usort($destarr, 'sortValue');
		foreach($destarr as $item)
		{
			echo $item[0]." --> ".$item[2]." (".$item[1]." --> ".$item[3].") - ".str_replace("\r\n","",$item[4])."\n";
		}
	}
}
/*Function: Helper Sorting function
@param: value $a, value $b
@return: int -1, 0 or 1
*/
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
/*reading in files*/
addProviderFlights("Provider1.txt",$destarr,",");
addProviderFlights("Provider2.txt",$destarr,",");
addProviderFlights("Provider3.txt",$destarr,"|");

/*filter array & print flights*/
selectFlights($destarr,$dest);
printFlights($destarr);
?>