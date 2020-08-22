<!DOCTYPE html>
<!--
PHP code to demonstrate how to create a incident ticket with ServiceNow REST API.
This will create the ticket with following data,
1)short_description 2)category 3)subcategory 4)caller_id 5)assigned_to

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
		<title>ServiveNow REST - Create Incident Ticket</title>
	</head>
<body>

<h3>SERVICENOW PHP CLIENT - Create Incident Ticket</h3>
<!-- Form to receive an incident ticket details from UI -->
<form action="">
	<table>
            <tr>
                <td><label for="short_description">Short Description : </label></td>
                <td><input type="text" name="short_description" value="<?php echo isset($_GET['short_description']) ? $_GET['short_description'] : ''?>"/></td>
            </tr>
            <tr>
                <td><label for="category">Category : </label></td>
                <td><input type="text" name="category" value="<?php echo isset($_GET['category']) ? $_GET['category'] : ''?>"/></td>
            </tr>
            <tr>
                <td><label for="subcategory">Sub Category : </label></td>
                <td><input type="text" name="subcategory" value="<?php echo isset($_GET['subcategory']) ? $_GET['subcategory'] : ''?>"/></td>
            </tr>
            <tr>
                <td><label for="caller_id">Caller User ID: </label></td>
                <td><input type="text" name="caller_id" value="<?php echo isset($_GET['caller_id']) ? $_GET['caller_id'] : ''?>"/></td>
            </tr>
            <tr>
                <td><label for="assigned_to">Assigned to User ID : </label></td>
                <td><input type="text" name="assigned_to" value="<?php echo isset($_GET['assigned_to']) ? $_GET['assigned_to'] : ''?>"/></td>
            </tr>
            <tr>
                <td><button type="submit">Create Ticket</button></td>
            </tr>
    </table>
    
</form>

<?php
	//Specify the ServiceNow developer instance access details
	$servicenow_instance_id = 'dev19250';
	$servicenow_username = 'admin';
	$servicenow_password = 'Weblogic@123';

	$servicenow_table_name = 'incident';
	
	if(!empty($_GET['short_description'])){
        $short_description = $_GET['short_description'];
        $category = $_GET['category'];
        $subcategory = $_GET['subcategory'];
        $caller_id = $_GET['caller_id'];
        $assigned_to = $_GET['assigned_to'];

        //Get sys_id of user fields (from sys_user table)
        $caller_user_sys_id = getSysIDOfUserName($servicenow_instance_id, $servicenow_username, $servicenow_password, $caller_id);
        $assigned_to_user_sys_id = getSysIDOfUserName($servicenow_instance_id, $servicenow_username, $servicenow_password, $assigned_to);

        //Put all incident data in an array
        $incident_data_array = array();
        $incident_data_array["short_description"] = $short_description;
        $incident_data_array["category"] = $category;
        $incident_data_array["subcategory"] = $subcategory;
        $incident_data_array["caller_id"] = $caller_user_sys_id;
        $incident_data_array["assigned_to"] = $assigned_to_user_sys_id;

        $new_incident_number = createIncidentTicket($servicenow_instance_id, $servicenow_username, $servicenow_password, $incident_data_array);
        echo '<p>Incident ticket created successfully. Ticket Id : '. $new_incident_number .'</p>';        
        
        }
    
    //Function to create new incident ticket and return the ID of the same
    function createIncidentTicket($servicenow_instance_id, $servicenow_username, $servicenow_password, $incident_data_array){
        //Specifying the ServiceNow username & password
		$auth = base64_encode($servicenow_username . ':' . $servicenow_password);
		$context = stream_context_create([
			"http" => [
                //since it's a create operation the http method should be POST
                'method'  => 'POST', 
                "header" => array(
                    "Authorization: Basic $auth",
                    "Content-type: application/json"
                ),
                'content' => json_encode($incident_data_array)
			]
        ]);
        
        $rest_api_url = "https://". $servicenow_instance_id .".service-now.com/api/now/v1/table/incident";
        //Call REST Web Service URL. The response will be a JSON string
		$json_response = file_get_contents($rest_api_url, false, $context );
		//Convert JSON string to PHP array
		$response_array = json_decode($json_response, true);
		return $response_array['result']['number'];

    }
	
	//Function to retrieve the sys_id of user_name (from sys_user table)
	function getSysIDOfUserName($servicenow_instance_id, $servicenow_username, $servicenow_password, $user_name){
		//Specifying the ServiceNow username & password
		$auth = base64_encode($servicenow_username . ':' . $servicenow_password);
		$context = stream_context_create([
			"http" => [
                "header" => "Authorization: Basic $auth"
			]
		]);

		$rest_api_url = "https://". $servicenow_instance_id .".service-now.com/api/now/table/sys_user?sysparm_query=user_name=" . $user_name . "&sysparm_limit=1";
		//Call REST Web Service URL. The response will be a JSON string
		$json_response = file_get_contents($rest_api_url, false, $context );
		//Convert JSON string to PHP array
		$response_array = json_decode($json_response, true);
		return $response_array['result'][0]['sys_id'];
		
	}

?>

</body>
</html>