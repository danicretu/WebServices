<?php
	require("dbsettings.php");

	function head () {
		header ("{$_SERVER['SERVER_PROTOCOL']} 200 OK");
		header ('ContentType: text/html');
		header ('AccessControlAllowOrigin:*');
	}

	function unique_id($l = 5) {
	    return substr(md5(uniqid(mt_rand(), true)), 0, $l);
	}

	function rest_get ($request, $data) {
        try {
		    $db = new PDO("mysql:host=" . $GLOBALS['host'] .
            	";dbname=" . $GLOBALS['dbname'], $GLOBALS['username'], $GLOBALS['password']);
		    $db->exec("set names utf8");
		    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch(PDOException $e) {
		    echo "Error: " . $e->getMessage();
		}

		$db->query('CREATE TABLE IF NOT EXISTS urls (id INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT, url VARCHAR(100) NOT NULL UNIQUE, shorturl VARCHAR(10) NOT NULL UNIQUE)');

        if(!isset($_GET['short'])) {
        	$query = "SELECT * FROM urls";
			// to avoid sql injection
			$statement = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	        $statement->execute();
	        $urls = $statement->fetchAll();
	        if($urls) {
	        	echo json_encode($urls);
	        }
	        return;
        }

        else {
        	// to avoid XSS
			$short = htmlspecialchars($_GET['short'], ENT_QUOTES, 'UTF-8');

        	$query = "SELECT url FROM urls WHERE shorturl=:short";
			// to avoid sql injection
			$statement = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	        $statement->execute(array(':short' => $short));
	        $result = $statement->fetchAll();

	        if($result) {
	        	header('Location: ' . $result[0][0], true, 301);
   				die();
	        }
        }
		
		header("HTTP/1.1 404");		
	}

	function rest_post ($request, $data) {
		if(!isset($_POST['url'])) {
			header("HTTP/1.1 400");
			echo "Invalid URL " . $url;
			die();
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

		$db->query('CREATE TABLE IF NOT EXISTS urls (id INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT, url VARCHAR(100) NOT NULL UNIQUE, shorturl VARCHAR(10) NOT NULL UNIQUE)');

		$url = htmlspecialchars($_POST['url'], ENT_QUOTES, 'UTF-8');

		if(filter_var($url, FILTER_VALIDATE_URL)) {
			$query = "SELECT shorturl FROM urls WHERE url=:url";
			$statement = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	        $statement->execute(array(':url' => $url));
			$result = $statement->fetchAll();

			// if there is no such url in the db yet
			if(!$result) {
				$shorturl = unique_id();
				$querytemp = $db->prepare("INSERT INTO urls (url, shorturl) VALUES(:url, :shorturl)");
				$querytemp->bindParam(':url', $url);
				$querytemp->bindParam(':shorturl', $shorturl);
				
				// insert the url, in case of failure return 500 response msg
				if(!$querytemp->execute()) {
					header("HTTP/1.1 500");
					die();
				}

				// get newly created url
				$statement = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		        $statement->execute(array(':url' => $url));
				$result = $statement->fetchAll();
				if(count($result) === 0) {
					header("HTTP/1.1 500");
					die();
				}
			}

			header("HTTP/1.1 201");
			echo json_encode($result[0][0]);
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

		$db->query('CREATE TABLE IF NOT EXISTS urls (id INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT, url VARCHAR(100) NOT NULL UNIQUE, shorturl VARCHAR(10) NOT NULL UNIQUE)');

		if(!isset($_GET['short'])) {
			$query = "DELETE FROM urls";
			$statement = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	        if(!$statement->execute()) {
				header("HTTP/1.1 500");
				die();
			}
	        header("HTTP/1.1 204");
			die();
		}

		else {
			$short = htmlspecialchars($_GET['short'], ENT_QUOTES, 'UTF-8');
			$query = "SELECT * FROM urls WHERE shorturl=:short";
			$statement = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	        $statement->execute(array(':short' => $short));
			$result = $statement->fetchAll();

			if($result) {
				$querytemp = "DELETE FROM urls WHERE shorturl=:short";
				$statementtemp = $db->prepare($querytemp, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
				// delete the url, in case of failure return 500 response msg
				if(!$statementtemp->execute(array(':short' => $short))) {
					header("HTTP/1.1 500");
					die();
				}
				header("HTTP/1.1 204");
				die();
			}

			else {
				header("HTTP/1.1 404");
				die();
			}
		}		
	}

	function rest_put ($request, $data) {
		if(!isset($_GET['short'])) {
			rest_error($request);
    		return;
		}
		if(!isset($_GET['url'])) {
			header("HTTP/1.1 400 Invalid URL");
			echo "Invalid URL";
			die();
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

		$db->query('CREATE TABLE IF NOT EXISTS urls (id INTEGER PRIMARY KEY NOT NULL AUTO_INCREMENT, url VARCHAR(100) NOT NULL UNIQUE, shorturl VARCHAR(10) NOT NULL UNIQUE)');

		$url = htmlspecialchars($_GET['url'], ENT_QUOTES, 'UTF-8');
		$short = htmlspecialchars($_GET['short'], ENT_QUOTES, 'UTF-8');
		$query = "SELECT * FROM urls WHERE shorturl=:short";
		$statement = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $statement->execute(array(':short' => $short));
		$result = $statement->fetchAll();

		if($result) {
			if(filter_var($_GET['url'], FILTER_VALIDATE_URL)) {
				$querytemp = $db->prepare('UPDATE urls
                                     SET url = :url
                                    WHERE shorturl = :short');
		        $querytemp->bindParam(':url', $url);
		        $querytemp->bindParam(':short', $short);

				if(!$querytemp->execute()) {
					header("HTTP/1.1 500");
					die();
				}

				header("HTTP/1.1 200");
				die();
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