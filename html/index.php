<?php
  // Check the validity of the given domain
  function domain_validate($domain) {
    $j = 0;

    for ($j = 0; $j < strlen($domain); $j++) {
      $ch = $domain[$j];
      if (!isalpha($ch) && !isdigit($ch) && $ch != '.') {
        return $null;
      }
    }

    return $domain;
  }

  $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
  if (!isset($ip)) {
      $ip = $_SERVER["REMOTE_ADDR"];
  }

  $domain_name = $_GET['domain'];

  // Validate the domain name
  // $domain = domain_validate($domain);

  if (! isset($domain_name)) {
    exit("A valid ?domain=name key is required.");
  }
  else {
    // Read the configuration file 
    $conf = parse_ini_file("/etc/update-ip.ini", false);

    // Check the API Key
    $api_key = $conf['key'];
    
    if ($api_key != $_GET['key']) {
        exit("Incorrect API key given");
    }

    try {
	$host = $conf['host'];
        $database = $conf['database'];

        $conn = new PDO ("mysql:host=$host;dbname=$database", 
                         $conf['user'], $conf['password']);
    } catch (PDOException $e) {
        exit('Error connecting to database: ' . $e->getMessage());
    }
    // $conn = mysqli_connect($conf['host'], $conf['user'], 
    //                       $conf['password'], $conf['database']);
    
    // Obtain the old value of the IP for the given domain
    $stmt = $conn->prepare("SELECT content FROM records WHERE name = :domain");
    $stmt->bindParam(":domain", $domain_name, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        exit("Unable to find the specified domain in the database");
    }
    else {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $current_ip = $row['content'];

        if ($current_ip == $ip) {
            exit("IP already in the database, skipping update");
        }

        // Compute the change_date used to update the timestamp.
        $ts = intval(floor(100 * (date('G') + date('i') / 60.0) / 24));
        $ts   =  100 * intval(date('Ymd')) + $ts;

        // Perform the update
        $stmt = $conn->prepare(
            "UPDATE records SET content = :content, change_date = :timestamp WHERE name = :domain");
        $stmt->bindParam(":content", $ip);
        $stmt->bindParam(":timestamp", $ts);
        $stmt->bindParam(":domain", $domain_name);

        if (! $stmt->execute()) {
            exit("Error performing the update: " . $conn->error);
        }
        else {
            echo "Address update for domain '$domain_name' to '$ip' \n";
        }
    }
  }

?>
