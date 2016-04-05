<?php
	require("dbsettings.php");

	function head () {
		header ("{$_SERVER['SERVER_PROTOCOL']} 200 OK");
		header ('ContentType: text/html');
		header ('AccessControlAllowOrigin:*');
	}

	function rest_get ($request, $data) {
		if(!isset($_GET['id'])) {
        	rest_error($request);
        	return;
        }

        try {
		    $db = new PDO("mysql:host=" . $GLOBALS['host'] .
            	";dbname=" . $GLOBALS['dbname'], $GLOBALS['username'], $GLOBALS['password']);
		    $db->exec("set names utf8");
		    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch(PDOException $e) {
		    echo "Error: " . $e->getMessage();
		}

		$db->query('CREATE TABLE IF NOT EXISTS urls (id INTEGER PRIMARY KEY NOT NULL url VARCHAR(100) NOT NULL UNIQUE)');

        $id = base_convert($_GET['id'], 36, 10);
		$query = "SELECT * FROM urls ";
		$query .= "WHERE id=:id";
		// to avoid sql injection
		$statement = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $statement->execute(array(':id' => $id));

		if($statement) {
			$result = "";
			while ($entry = $statement->fetchAll()) {
				if(isset($_GET['id'])) {
					header("HTTP/1.1 301 Moved");
					header("Location: " . $entry['url']);
					die();
				}
				$result .= base_convert($entry['id'], 10, 36) .  ",";
			}
			if(strlen($result) > 0) {
				echo substr($result, 0, -1);
				return;
			}
		}
		
		header("HTTP/1.1 404");		
	}

	function rest_post ($request, $data) {
		try {
		    $db = new PDO("mysql:host=" . $GLOBALS['host'] .
            	";dbname=" . $GLOBALS['dbname'], $GLOBALS['username'], $GLOBALS['password']);
		    $db->exec("set names utf8");
		    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch(PDOException $e) {
		    echo "Error: " . $e->getMessage();
		}

		$db->query('CREATE TABLE IF NOT EXISTS urls (id INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT, url VARCHAR(100) NOT NULL UNIQUE)');

		// to avoid XSS
		$url = htmlspecialchars($_POST['url'], ENT_QUOTES, 'UTF-8');

		if(isset($url) && filter_var($url, FILTER_VALIDATE_URL)) {
			$query = "SELECT id FROM urls ";
			$query .= "WHERE url=:url";
			$statement = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	        $statement->execute(array(':url' => $url));
			$result = $statement->fetchAll();

			// if there is no such url in the db yet
			if(!$result) {
				$querytemp = "INSERT INTO urls (url) VALUES(:url)";
				$statementtemp = $db->prepare($querytemp, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				
				// insert the url, in case of failure return 500 response msg
				if(!$statementtemp->execute(array(':url' => $url))) {
					header("HTTP/1.1 500");
					die();
				}

				// get newly created url
				$statement = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		        $statement->execute(array(':url' => $url));
				$result = $statement->fetchAll();
				if(!$result) {
					header("HTTP/1.1 500");
					die();
				}
			}

			header("HTTP/1.1 201");
			echo json_encode(base_convert($result[0][0], 10, 36));
		}

		else {
			header("HTTP/1.1 400");
			echo "Invalid URL " . $url;
		}
	}

	function rest_delete ($request, $data) {
		try {
		    $db = new PDO("mysql:host=" . $GLOBALS['host'] .
            	";dbname=" . $GLOBALS['dbname'], $GLOBALS['username'], $GLOBALS['password']);
		    $db->exec("set names utf8");
		    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch(PDOException $e) {
		    echo "Error: " . $e->getMessage();
		}

		$db->query('CREATE TABLE IF NOT EXISTS urls (id INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT, url VARCHAR(100) NOT NULL UNIQUE)');

		if(isset($data['id'])) {
			$tmp = htmlspecialchars($data['id'], ENT_QUOTES, 'UTF-8');
			$id = intval(base_convert(htmlspecialchars($tmp, 36, 10)));
			$query = "SELECT id FROM urls WHERE id=:id";
			$statement = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	        $statement->execute(array(':id' => $id));
			$result = $statement->fetchAll();

			if($result) {
				$querytemp = "DELETE FROM urls WHERE id=:id";
				$statementtemp = $db->prepare($querytemp, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				// delete the url, in case of failure return 500 response msg
				if(!$statementtemp->execute(array(':id' => $id))) {
					header("HTTP/1.1 500");
					die();
				}
			}

			else {
				header("HTTP/1.1 404");
			}
		}			
	}

	function rest_put ($request, $data) {
		try {
		    $db = new PDO("mysql:host=" . $GLOBALS['host'] .
            	";dbname=" . $GLOBALS['dbname'], $GLOBALS['username'], $GLOBALS['password']);
		    $db->exec("set names utf8");
		    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch(PDOException $e) {
		    echo "Error: " . $e->getMessage();
		}

		$db->query('CREATE TABLE IF NOT EXISTS urls (id INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT, url VARCHAR(100) NOT NULL UNIQUE)');

		if(!isset($data['id'])) {
			rest_error($request);
    		return;
		}

		$tmp = htmlspecialchars($data['id'], ENT_QUOTES, 'UTF-8');
		$url = htmlspecialchars($data['url'], ENT_QUOTES, 'UTF-8');
		$id = intval(base_convert($tmp, 36, 10));
		$query = "SELECT id FROM urls WHERE id=:id";
		$statement = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $statement->execute(array(':id' => $id));
		$result = $statement->fetchAll();

		if($result) {
			if(isset($data['url']) && filter_var($data['url'], FILTER_VALIDATE_URL)) {
				$querytemp = $db->prepare('UPDATE urls
                                     SET url = :url
                                    WHERE id = :id');
		        $statementtemp->bindParam(':url', $url);

				$statementtemp = $db->prepare($querytemp, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				if(!$statementtemp->execute(array(':id' => $id))) {
					header("HTTP/1.1 500");
					die();
				}
			}

			else {
				header("HTTP/1.1 400 Invalid URL");
				echo "Invalid URL";
			}
		}

		else {
			header("HTTP/1.1 404");
		}
	}
	
	function rest_error ($request) { 
		$msg = array("error"=>"Missing id");
    	echo json_encode($msg);
	}
	
	$method = $_SERVER['REQUEST_METHOD'];
	$request = $_SERVER['REQUEST_URI'];
	switch ($method) {
		case 'PUT':
			parse_str (file_get_contents('php://input'), $put_vars);
			head (); $data = $put_vars; rest_put($request, $data); break;
		case 'POST':
			head (); $data = $_POST; rest_post($request, $data); break;
		case 'GET':
			head (); $data = $_GET; rest_get($request, $data); break;
		case 'DELETE':
            parse_str (file_get_contents('php://input'), $del_vars);
			head (); $data = $del_vars; rest_delete($request, $data); break;
		default :
			header("{$_SERVER ['SERVER_PROTOCOL']} 404 Not Found");
			rest_error($request); break;
	}
?>