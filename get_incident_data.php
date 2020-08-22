<!DOCTYPE html>
<!--
PHP code to demonstrate how to consume ServiceNow REST API
This PHP code will fetch some data from the ServiceNow incident table such as short_description, assigned_to, and state.
This will also fetch the required data from tables referenced by the incident table such as sys_user and sys_choice.
-->
<html>
	<head>
		<link href="https://fonts.googleapis.com/css2?family=Roboto+Mono&family=Roboto:ital,wght@0,100;1,300;1,400&display=swap" rel="stylesheet">
		<style>
			body{
					font-family: 'Roboto', sans-serif;
					font-family: 'Roboto Mono', monospace;
			}
		</style>
		<title>ServiveNow REST - Get Incident Details</title>
	</head>
<body>

<h3>SERVICENOW PHP CLIENT - GET INCIDENT DETAILS</h3>
<!-- Form to receive an incident ticket ID from UI -->
<form action="">
	<label for="ticket_id">Ticket ID : </label>
	<input type="text" name="ticket_id" value="<?php echo isset($_GET['ticket_id']) ? $_GET['ticket_id'] : ''?>"/>
	<button type="submit">Get Data</button>
</form>

<?php
	//Specify the ServiceNow developer instance access details
	$servicenow_instance_id = 'dev19250';
	$servicenow_username = 'admin';
	$servicenow_password = 'Weblogic@123';

	$servicenow_table_name = 'incident';
	
	if(!empty($_GET['ticket_id'])){
		$ticket_id = $_GET['ticket_id'];
		//Get data from incident table by ticket id
		$response_array = getServiceNowTableData($servicenow_instance_id, $servicenow_username, $servicenow_password, $servicenow_table_name, $ticket_id);

		if(count($response_array['result']) <= 0){
			echo '<p>Ticket ID not found !!!</p>';
		}
		else{
			$short_description = $response_array['result'][0]['short_description'];
			//The assigned to user name is available in the sys_user table which is referenced by the incident table. So we need to use the reference URL for fetching the user name.
			$assigned_to = callServiceNowRestWebService($response_array['result'][0]['assigned_to']['link'], $servicenow_username, $servicenow_password)['result']['name'];
			//The incident table has a state number. We need to query the sys_choice table for fetching the label text of the state.
			$status = getChoiceLabel($servicenow_instance_id, $servicenow_username, $servicenow_password, $servicenow_table_name, 'state', $response_array['result'][0]['state']);
?>
			<br>
			<table border='1'>
				<tr>
					<th>Ticket ID</th>
					<td><?php echo $ticket_id;?></td>
				</tr>
				<tr>
					<th>Short Description</th>
					<td><?php echo $short_description;?></td>
				</tr>
				<tr>
					<th>Assigned To</th>
					<td><?php echo $assigned_to;?></td>
				</tr>
				<tr>
					<th>Status</th>
					<td><?php echo $status;?></td>
				</tr>
			</table>
<?php
		}
	}
	
	//Function to get the details from the incident table by ticket id
	function getServiceNowTableData($servicenow_instance_id, $servicenow_username, $servicenow_password, $servicenow_table_name, $ticket_id){
		//Specifying the ServiceNow username & password
		$auth = base64_encode($servicenow_username . ':' . $servicenow_password);
		$context = stream_context_create([
			"http" => [
				"header" => "Authorization: Basic $auth"
			]
		]);

		$rest_api_url = "https://". $servicenow_instance_id .".service-now.com/api/now/table/". $servicenow_table_name ."?sysparm_query=number=". $ticket_id ."&sysparm_limit=1";
		//Call REST Web Service URL. The response will be a JSON string
		$json_response = file_get_contents($rest_api_url, false, $context );
		//Convert JSON string to PHP array
		$response_array = json_decode($json_response, true);
		return $response_array;
	}

	//Function to retrieve the Assigned To username of an incident ticket (From sys_user table)
	function callServiceNowRestWebService($rest_api_url, $servicenow_username, $servicenow_password){
		//Specifying the ServiceNow username & password
		$auth = base64_encode($servicenow_username . ':' . $servicenow_password);
		$context = stream_context_create([
			"http" => [
				"header" => "Authorization: Basic $auth"
			]
		]);
		//Call REST Web Service URL. The response will be a JSON string
		$json_response = file_get_contents($rest_api_url, false, $context );
		//Convert JSON string to PHP array
		$response_array = json_decode($json_response, true);
		return $response_array;
	}
	
	//Function to retrieve the Status label for an incident ticket (from sys_choice table)
	function getChoiceLabel($servicenow_instance_id, $servicenow_username, $servicenow_password, $servicenow_table_name, $element_name, $choice_value){
		//Specifying the ServiceNow username & password
		$auth = base64_encode($servicenow_username . ':' . $servicenow_password);
		$context = stream_context_create([
			"http" => [
				"header" => "Authorization: Basic $auth"
			]
		]);

		$rest_api_url = "https://". $servicenow_instance_id .".service-now.com/api/now/table/sys_choice?sysparm_query=name=" . $servicenow_table_name . "^element=" . $element_name . "^value=" . $choice_value;
		//Call REST Web Service URL. The response will be a JSON string
		$json_response = file_get_contents($rest_api_url, false, $context );
		//Convert JSON string to PHP array
		$response_array = json_decode($json_response, true);
		return $response_array['result'][0]['label'];
		
	}

?>

</body>
</html>