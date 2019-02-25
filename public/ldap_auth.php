
<?php
	header("Access-Control-Allow-Origin: *");
	define('BASE_URL', 'infosec.apps.cs.ipb.ac.id');

	if($_SERVER['REQUEST_METHOD'] == "GET"){
		$d = $_GET;
		if(empty($d['username']) || empty($d['password'])){
			echo '{"status":false,"message":"empty parameter"}';
			die();
		}
		$url = "http://".BASE_URL."/ldap.php?u=".$d['username']."&p=".$d['password'];

		$url = json_decode(file_get_contents($url), true);

		$data = [
			'username' => $url['uid'][0],
			'name' => $url['cn'][0],
			'nim' => $url['nrp'][0],
			'angkatan' => $url['angkatan'][0],
		];
		// print_r($data);
		echo json_encode($data);
	}
?>