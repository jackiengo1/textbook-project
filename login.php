<?php

	returnUserInfo("jngo42@uwo.ca");

	if (isset($_POST['action'])) {
	    switch ($_POST['action']) {
	        case "login":
	            verifyUser($_POST['email'], $_POST['password']);
	            break;
	        case "register":
	        	registerUser($_POST['email'], $_POST['password'], $_POST['firstName'], $_POST['lastName'], $_POST['phoneNum'], $_POST['school']);
	        	break;
	    }
	}

	function connect()
	{
		$servername = "localhost";
		$username = "root";
		$password = "textbookproject";
		$database = "kriativejatabase";

		// Create connection
		$myConnection = new mysqli($servername, $username, $password);

		// Check connection
		if ($myConnection->connect_error)
			die("Connection failed: " . $myConnection->connect_error);

		debug_to_console("Connected successfully<br/>");

		$sql = "USE kriativejatabase;";

		if ($myConnection->query($sql) === TRUE)
			debug_to_console("Using the database!");
		else
			debug_to_console("Error using database: " . $myConnection->error);

		return $myConnection;
	}

	function verifyUser($email, $password)
	{
		$myConnection = connect();

		$sql = "SELECT userEmail, password
				FROM User
				WHERE userEmail = '" . $email . "' AND password = '" . $password . "';";

		$result = $myConnection->query($sql);

		if ($result->num_rows > 0)
			echo "TRUE";
		else
			echo "FALSE";

		$myConnection->close();
	}

	function registerUser($email, $password, $firstName, $lastName, $phoneNum, $schoolName)
	{
		$myConnection = connect();

		$sql = "SELECT userEmail, password
				FROM User
				WHERE userEmail = '" . $email . "';";

		$result = $myConnection->query($sql);

		if ($result->num_rows > 0)
		{
			echo "FALSE";
		}
		else
		{
			$schoolIdQuery = "SELECT schoolID
							  FROM School
							  WHERE schoolName = '" . $schoolName . "';";

			$schoolID = $myConnection->query($schoolIdQuery);
			$schoolID = $schoolID->fetch_assoc();

			$insertUserQuery = "INSERT INTO User(userEmail, firstName, lastName, phoneNum, password, schoolID)
								VALUES('" . $email . "', '" . $firstName . "', '" . $lastName . "', '" . $phoneNum . "', '" . $password . "', " . $schoolID['schoolID'] . ");";

			$myConnection->query($insertUserQuery);

			$insertUserSchoolQuery = "INSERT INTO UserSchool(userEmail, schoolName)
									  SELECT u.userEmail, s.schoolName
									  FROM User u, School s
									  WHERE u.schoolID = s.schoolID AND
									  		u.userEmail NOT IN (SELECT userEmail
									  	  						FROM UserSchool);";

			$myConnection->query($insertUserSchoolQuery);
			
			echo "TRUE";
		}

		$myConnection->close();
	}

	function returnUserInfo($email)
	{
		$myConnection = connect();

		$getUserInfoQuery = "SELECT u.firstName, u.lastName, u.phoneNum, s.schoolName
							 FROM User u, School s
							 WHERE u.userEmail = '" . $email . "' AND
							 	   u.schoolID = s.schoolID;";

		$userInfo = $myConnection->query($getUserInfoQuery);
		$userInfo = $userInfo->fetch_assoc();

		$data = array('firstName'=> $userInfo['firstName'], 'lastName' => $userInfo['lastName'], 'phoneNum' => $userInfo['phoneNum'], 'schoolName' => $userInfo['schoolName']);
		header('Content-Type: application/json');
		echo json_encode($data);

		$myConnection->close();
	}

	function debug_to_console( $data )
	{
	    if ( is_array( $data ) )
	        $output = "<script>console.log( 'Debug Objects: " . implode( ',', $data) . "' );</script>";
	    else
	        $output = "<script>console.log( 'Debug Objects: " . $data . "' );</script>";

	    //echo $output;
	}

?>