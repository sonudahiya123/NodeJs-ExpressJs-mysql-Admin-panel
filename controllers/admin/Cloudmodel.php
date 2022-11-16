<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once('callapi.php');

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

class Cloudmodel extends CI_Model 
{
public $s3_bucket;
	
	/*
	 
	 * Default constructor

	 */
	
public function __construct() {


		
		// Call the Model constructor
	parent::__construct();

	    if(!$this->session->userdata('logged_in')){
	    		 $session_key = SESSION_KEY_NOTLOGGEDIN;
	    }else{
	    		 $session_key = $_SESSION['token'];
	    }
	    	
	    /*Client id and client secret passing in api*/
	    $client_id  = CLIENT_ID;
	    $client_secret = CLIENT_SECRET;
	    
		$this->obj = new Rest($session_key,$client_id,$client_secret);

		$bucketName = S3_BUCKET;
			// Connect to AWS
		try {
				// You may need to change the region. It will say in the URL when the bucket is open
				// and on creation.
			$this->s3_bucket = S3Client::factory(
					array(
						'credentials' => 
							array(
								'key' => S3_BUCKET_KEY,
								'secret' => S3_BUCKET_SECRET
					),
					'version' => 'latest',
					'region'  => 'ap-south-1'
				)
				
			);

		} catch (Exception $e) {
				// We use a die, so if this fails. It stops here. Typically this is a REST call so this would
				// return a json object.
				$response["status"] = "failed";
				$response["message"] = "There seems to be an issue connecting to your cloud directory.";
				$response["response"] = 0;

				return $response;
		}
}


/*

* Add Directory

*/
function adddefaultdirectory($data)
{
	
	try
	{
		$totalCountArray = count($data);
		$result = '';
		$DefaultFolderName = '';

		$result = $this->s3_bucket->putObject(array( 
			'Bucket'	=> S3_BUCKET, // Defines name of Bucket
			'Key'		=> 'test/', //Defines Folder name
			'Body'		=> "",
			'ACL'		=> 'private' // Defines Permission to that folder, default: public-read
		 ));

		 for($i=0; $i<$totalCountArray; $i++)
		{
			$result = $this->s3_bucket->putObject(array( 
				'Bucket'	=> S3_BUCKET, // Defines name of Bucket
				'Key'		=> base64_decode($data[$i]['dir_key']), //Defines Folder name
				'Body'		=> "",
				'ACL'		=> 'private' // Defines Permission to that folder, default: public-read
		 	));
		 
		 	$DefaultFolderName .= $data[$i]['name'].', ';
		 	$user_id = $data[$i]['user_id'];
		 	$user_account_no = $data[$i]['user_account_no'];
		}

		if($result)
		{ 
			$description = $DefaultFolderName;
			
			// Create Cloud Logs
			self::CloudlogMsg($user_id, $user_account_no, 0, 'AWS Cloud S3', $description, S3_BUCKET, 'Create');

			if($this->db->insert_batch('cloud_directory', $data)){
				$response["status"] = "success";
				$response["message"] = 'Create default folders with: '.$DefaultFolderName.' successfully.';
				$response["response"] = 1;
				return $response;
			}
			else{
				$response["status"] = "failed";
				$response["message"] = "There seems to be an issue manage to your cloud directory record.";
				$response["response"] = 0;
				return $response;
			}
		}
		else{
			$response["status"] = "failed";
			$response["message"] = "There seems to be an issue creating to your directory at AWS.";
			$response["response"] = 0;
			return $response;
		}
	} catch (Exception $e) {
		$response["status"] = "failed";
		$response["message"] = $e->getMessage();
		$response["response"] = 0;
		return $response;
	}
}

/*

* Create a Directory/Folder

*/
function adddirectory($data)
{
	// Check if Dir already exist in record.
	$parant_id = 0;

	if(!empty($data['parent_dir_id'])){
		$parant_id = self::getdirectorykey($data['parent_dir_id']);
		$parant_id = $parant_id[0]['parent_dir_id'];
	}

	
	$checkName = self::getdirectorybyname($data['user_id'], $data['name'], $data['parent_dir_id'], $parant_id[0]['parent_dir_id']);
	$description = $data['name'];

	if($checkName == 0)
	{
		$result = $this->s3_bucket->putObject(array( 
			'Bucket' => S3_BUCKET, // Defines name of Bucket
			'Key'  => base64_decode($data['dir_key']), //Defines Folder name
			'Body' => "",
			'ACL'  => 'private' // Defines Permission to that folder, default: public-read
	 	));

	 if($result)
		{
			if($this->db->insert('cloud_directory', $data)){
				$cloud_directory_id = $this->db->insert_id();
				$description = base64_decode($data['dir_key']);

				$response["status"] = "success";
				$response["message"] = "success";
				$response["id"] = $cloud_directory_id;
				$response["response"] = 1;
				self::CloudlogMsg($data['user_id'], $data['user_account_no'], $cloud_directory_id, 'AWS Cloud S3', $description, S3_BUCKET, 'Create');
				return $response;
			}
			else{
				$response["status"] = "failed";
				$response["message"] = "Some problem while creating directory record.";
				$response["response"] = 0;
				return $response;
			}
		}
		else{
			$response["status"] = "failed";
			$response["message"] = "Some problem while creating directory at AWS.";
			$response["response"] = 0;
			return $response;
		}
	}
	else{
		$response["status"] = "failed";
		$response["message"] = "This directory/folder name already exist in record.";
		$response["response"] = 0;
		return $response;
	}
}




/*
function getdirectory($userid){
		$sql = "SELECT `name`, `id` FROM `cloud_directory` WHERE user_id =".$userid;
		return $this->db->query($sql)->result_array();
}
*/

/*

* Get Directory/Folder list with file size if exist according to dir id

*/
function getdirectory($userid){
	/*
	$sql = "SELECT cd.name, cd.id, cf.file_size FROM `cloud_directory` cd ";
	$sql .= "LEFT JOIN `cloud_file` cf ON cf.cloud_directory_id = cd.id AND cf.is_deleted = 0 ";
	$sql .= "WHERE cd.user_id =".$userid." AND cd.parent_dir_id = 0 ORDER BY cd.id";
	*/

	/*$sql = "SELECT cd.name, cd.parent_dir_id, cd.id, cf.file_size FROM `cloud_directory` cd ";
	$sql .= "LEFT JOIN `cloud_file` cf ON cf.cloud_directory_id = cd.id AND cf.is_deleted = 0 ";
	$sql .= "WHERE cd.user_id =".$userid." ORDER BY cd.id";
*/
	$sql = "SELECT `name`, `id` FROM `cloud_directory` WHERE `user_id` =".$userid." AND `parent_dir_id` = 0 ORDER BY `id`";
	
	return $this->db->query($sql)->result_array();
}

function getdirectoryfilesize($cloud_directory_id, $user_id){
	$sql = "SELECT (CASE WHEN SUM(`file_size`) = '' OR SUM(`file_size`) IS NULL THEN 0 ELSE SUM(`file_size`) END) as file_size FROM `cloud_file` WHERE `user_id`= ".$user_id." AND (`cloud_directory_id`=".$cloud_directory_id." OR `parent_dir_id`=".$cloud_directory_id.") AND `is_deleted` = 0 ";	
	return $this->db->query($sql)->result_array();
}


function getsubdirectory($userid, $dirid){
	$sql = "SELECT cd.name, cd.id, cf.file_size FROM `cloud_directory` cd ";
	$sql .= "LEFT JOIN `cloud_file` cf ON cf.cloud_directory_id = cd.id AND cf.is_deleted = 0 ";
	$sql .= "WHERE cd.user_id = ".$userid." AND cd.parent_dir_id = ".$dirid." ORDER BY cd.id";
	return $this->db->query($sql)->result_array();
}

public function getdirectorykey($id){
	$sql = "SELECT cd.dir_key, cd.name, cd.parent_dir_id FROM `cloud_directory` cd ";
	$sql .= "WHERE cd.id = ".$id." ORDER BY cd.id Limit 0,1";
	return $this->db->query($sql)->result_array();
}


/*

* Get Total File Size according to dir id

*/
function getfilesize($id){
	$sql = "SELECT `cloud_directory_id`, SUM(`file_size`) as file_size FROM `cloud_file` WHERE cloud_directory_id =".$id;
	return $this->db->query($sql)->result_array();
}

/*

* Get Directory/Folder id according to user_id and name

*/
function getdirectorybyname($userid, $name, $cloud_directory_id = '', $parent_dir_id){
	if($cloud_directory_id && $cloud_directory_id != '') {
		 $sql = "SELECT `id` FROM `cloud_directory` WHERE `user_id` = ".$userid." AND `name` = '".$name."' AND `id` != ".$cloud_directory_id." AND `parent_dir_id` = ".$cloud_directory_id;
	}
	else{
		$sql = "SELECT `id` FROM `cloud_directory` WHERE `user_id` = ".$userid." AND `name` = '".$name."'";
	}
	return  $this->db->query($sql)->num_rows();
}

/*

* Get all files according to dir_id

*/
function getfilesbydirid($dirid){
		$sql = "SELECT * FROM `cloud_file` WHERE cloud_directory_id =".$dirid." AND is_deleted=0";
		$row = $this->db->query($sql)->num_rows();

		if($row > 0){
			return  $this->db->query($sql)->result_array();
		}
}


/*

* Upload a single file in AWS S3 Bucket and Add record in table.

*/
function upload($data)
{
	$data = array(
		'user_id' =>  $_SESSION['user_id'],
		'user_account_no' =>  $_SESSION['user_account_no'],
		'directory_name' =>  $this->input->post('directory_name'),
		'cloud_directory_id' =>  $this->input->post('directory_id'),
		'file_extension' => pathinfo($_FILES["fileToUpload"]['name'], PATHINFO_EXTENSION),
		'file_size' => $_FILES["fileToUpload"]["size"],
		'file_name' => basename($_FILES["fileToUpload"]['name']),
		'file_tmp_name' => $_FILES["fileToUpload"]['tmp_name'],
		'bucket_name' =>  S3_BUCKET	
	);



$dirpath_name = $data['user_account_no'].'/'.$data['directory_name'].'/';

// For this, I would generate a unqiue random string for the key name. But you can do whatever.
$keyName = $dirpath_name.$data['file_name'];
//$pathInS3 = 'https://s3.us-east-2.amazonaws.com/' . $bucketName . '/' . $keyName;
$pathInS3 = S3_BUCKET_PATH.S3_BUCKET.'/'.$keyName;
// Add it to S3
try {
	$result = $this->s3_bucket->putObject(array( 
		'Bucket'       => S3_BUCKET, // Defines name of Bucket
		'Key'          => $keyName, //Defines Folder name
		'SourceFile' => $data['file_tmp_name'],
		'StorageClass' => 'REDUCED_REDUNDANCY',
 		'ACL' => 'private' 
	 ));

	if($result)
	{ 
		$description = $data['file_name'];
		self::CloudlogMsg($data['user_id'], $data['user_account_no'], $this->input->post('directory_id'), 'AWS Cloud S3', $description, S3_BUCKET, 'upload');

		$data1 = array(
			'user_id' =>  $_SESSION['user_id'],
			'cloud_directory_id' =>  $this->input->post('directory_id'),
			'file_extension' => pathinfo($_FILES["fileToUpload"]['name'], PATHINFO_EXTENSION),
			'file_size' => $_FILES["fileToUpload"]["size"],
			'file_name' => basename($_FILES["fileToUpload"]['name']),
			'file_path' => base64_encode($pathInS3)
		);

		if($this->db->insert('cloud_file', $data1)){
			$response["status"] = "success";
			$response["message"] = "success";
			$response["response"] = 1;
		}
		else{
			$response["status"] = "failed";
			$response["message"] = "Some problem while manage file record.";
			$response["response"] = 0;
		}
	}else{
			$response["status"] = "failed";
			$response["message"] = "Some problem while manage file record at AWS.";
			$response["response"] = 0;
	}

	return $response;
} catch (S3Exception $e) {
	$response["status"] = "failed";
	$response["message"] = $e->getMessage();
	$response["response"] = 0;
	return $response;
} catch (Exception $e) {
	$response["status"] = "failed";
	$response["message"] = $e->getMessage();
	$response["response"] = 0;
	return $response;
}

		return $response;
}






/*

* Upload a multiple file in AWS S3 Bucket and Add record in table.

*/
function multiupload($data)
{


	//$dirpath_name = $data['user_account_no'].'/'.$data['directory_name'].'/';

	// For this, I would generate a unqiue random string for the key name. But you can do whatever.
	//$keyName = $dirpath_name.$data['file_name'];

	$key = base64_decode($data['dir_key']).$data['file_name'];
	//$pathInS3 = 'https://s3.us-east-2.amazonaws.com/' . $bucketName . '/' . $keyName;
	$pathInS3 = S3_BUCKET_PATH.S3_BUCKET.'/'.$key;

	//print_r($keyName);
	// Add it to S3
	try 
	{
		$result = $this->s3_bucket->putObject(array( 
			'Bucket'       => S3_BUCKET, // Defines name of Bucket
			'Key'          => $key, //Defines Folder name
			'SourceFile' => $data['file_tmp_name'],
			'StorageClass' => 'REDUCED_REDUNDANCY',
 			'ACL' => 'private' 
	 	));

		if($result)
		{ 
			$description = $data['file_name'];
			self::CloudlogMsg($data['user_id'], $data['user_account_no'], $data['cloud_directory_id'], 'AWS Cloud S3', $description, S3_BUCKET, 'upload');

			$data1 = array(
				'user_id' =>  $_SESSION['user_id'],
				'cloud_directory_id' =>  $data['cloud_directory_id'],
				'parent_dir_id' =>  $data['parent_dir_id'],
				'dir_key' => base64_encode($key),
				'file_extension' =>  $data['file_extension'],
				'file_size' => $data['file_size'],
				'file_name' => $data['file_name'],
				'random_numer' => $data['random_numer'],
				'file_path' => base64_encode($pathInS3),
				'created_on' =>  date('Y-m-d H:i:s')
			);
		
			if($this->db->insert('cloud_file', $data1))
			{
				$response["status"] = "success";
				$response["message"] = "success";
				$response["response"] = 1;
				return $response;
			}
			else
			{
				$response["status"] = "failed";
				$response["message"] = "Some problem while manage file record.";
				$response["response"] = 0;
				return $response;
			}
		}
		else{
			$response["status"] = "failed";
			$response["message"] = "Some problem while manage file record at AWS.";
			$response["response"] = 0;
			return $response;
		}
	} 
	catch (S3Exception $e) 
	{
		$response["status"] = "failed";
		$response["message"] = $e->getMessage();
		$response["response"] = 0;
		return $response;
	}
	catch (Exception $e) {
		$response["status"] = "failed";
		$response["message"] = $e->getMessage();
		$response["response"] = 0;
		return $response;
	}

	return $response;	
}














/*

* Get file url from AWS S3 Bucket

*/
function fileurl($data)
{
try{ 
	//$dirpath_name = $data['user_account_no'].'/'.$data['directory_name'].'/';
	$dirpath_name = $data['directory_name'];
	$pathInS3 = S3_BUCKET_PATH.S3_BUCKET.'/'.$dirpath_name;


	/*
	# initializing our object 
	$files = $this->s3_bucket->getIterator('ListObjects', [ # this is a Generator Object (its yields data rather than returning) 
		'Bucket' => S3_BUCKET,
		"Prefix" => $dirpath_name
	]);
*/
	foreach ($iterator = $this->s3_bucket->getIterator('ListObjects', array( 
		'Bucket' => S3_BUCKET, 
		'Prefix' =>  $dirpath_name, 
		'Delimiter' => "/",
		)) as $object) { 


			$file_acl = $this->s3_bucket->getObjectAcl([ 
				'Bucket' => S3_BUCKET, 
				'Key' => $object['Key']
			]);

			$is_private = true; 
		foreach($file_acl['Grants'] as $grant){ 
		if(isset($grant['Grantee']['effectiveUri']) && $grant['Grantee']['effectiveUri'] == S3_BUCKET_PATH.S3_BUCKET && $grant['Permission'] == 'READ'){
			$is_private = false; # this file is not private 
		}

		if($is_private == false){ 
			$file_url = $this->s3_bucket->getObjectUrl(S3_BUCKET, $file['Key']); 
		}else{
			$url_creator = $this->s3_bucket->getCommand('GetObject', [ 
				'Bucket' => S3_BUCKET, 
				'Key' => $object['Key']
			]);
	
	
			$signed_url = $this->s3_bucket->createPresignedRequest($url_creator, '+59 minutes');
			$file_url = $signed_url->getUri();
		}

		$nameName = explode('/', $object['Key']);

		$dados[] = array( 
			'key' => $object['Key'],
			'name' => $nameName, 
			'link' => $file_url 
			);
		}

	}
return $dados;
   } catch (S3Exception $e) {
	$response["status"] = "failed";
	$response["message"] = $e->getMessage();
	$response["response"] = 0;
	return $response;
} catch (Exception $e) {
	$response["status"] = "failed";
	$response["message"] = $e->getMessage();
	$response["response"] = 0;
	return $response;
} 
}

/*

* Download a file from AWS S3 Bucket

*/
function download($data)
{
// Add it to S3
try {
$dirpath_name = $data['user_account_no'].'/'.$data['directory_name'].'/';
# lets download our file 
$file_to_download = $dirpath_name.$data['file_name'];
$pathInS3 = S3_BUCKET_PATH.S3_BUCKET.'/'.$file_to_download;
$download_as_path = __dir__.'/downloads/'; 
 
$result = $this->s3_bucket->getObject([ 
	'Bucket' => S3_BUCKET, 
	'Key' => $file_to_download,
	'SaveAs' => $data['file_name']
   ]); 

   // Display the object in the browser.
   //header("Content-Type: {$result['ContentType']}");

 // Display the object in the browser.
 header("Content-Type: {$result['ContentType']}");

	return $result;
	} catch (S3Exception $e) {
		$response["status"] = "failed";
		$response["message"] = $e->getMessage();
		$response["response"] = 0;
		return $response;
	} catch (Exception $e) {
		$response["status"] = "failed";
		$response["message"] = $e->getMessage();
		$response["response"] = 0;
		return $response;
	}

		return $response;
}


/*

* Delete a file from AWS S3 Bucket

*/
function delete($data)
{

try {
$result = $this->s3_bucket->deleteObject(
	array(
		'Bucket' => S3_BUCKET, 
		'Key'    => $data['key']
	));

	if($result)
	{ 
		$description = $data['key'];
		self::CloudlogMsg($data['user_id'], $data['user_account_no'], $data['cloud_directory_id'], 'AWS Cloud S3', $description, S3_BUCKET, 'delete');

		$Wheredata1 = array(
			'user_id' =>  $data['user_id'],
			'cloud_directory_id' => $data['cloud_directory_id'],
			'file_name' =>  $data['file_name']
			);

			$data1 = array(
				'is_deleted' => 1,
				'modified_on' => date('Y-m-d H:i:s'),
			);

		$this->db->where($Wheredata1); 

		if($this->db->update('cloud_file', $data1)){
			$response["status"] = "success";
			$response["message"] = "success";
			$response["response"] = 1;
			return $response;
		}
		else{
			$response["status"] = "failed";
			$response["message"] = "Some problem while delete file record.";
			$response["response"] = 0;
			return $response;
		}
	}else{
			$response["status"] = "failed";
			$response["message"] = "Some problem while delete file record at AWS.";
			$response["response"] = 0;
			return $response;
	}
	} catch (S3Exception $e) {
		$response["status"] = "failed";
		$response["message"] = $e->getMessage();
		$response["response"] = 0;
		return $response;
	} catch (Exception $e) {
		$response["status"] = "failed";
		$response["message"] = $e->getMessage();
		$response["response"] = 0;
		return $response;
	}
}

/*

* Delete all files from AWS S3 Bucket

*/
function deleteAll($data)
{

try {

	foreach ($iterator = $this->s3_bucket->getIterator('ListObjects', array( 
		'Bucket' => S3_BUCKET, 
		'Prefix' =>  $data['key'], 
		)) as $object) { 
		$objKey[]['Key'] = $object['Key'];			
	}

	$result = $this->s3_bucket->deleteObjects([
			'Bucket' => S3_BUCKET,
			'Delete' => [
				'Objects' => $objKey,
				'Quiet' => false,
			],
	]);

	if($result)
	{ 
		$description = $data['key'];
		self::CloudlogMsg($data['user_id'], $data['user_account_no'], $data['cloud_directory_id'], 'AWS Cloud S3', $description, S3_BUCKET, 'delete');

		$Wheredata1 = array(
			'user_id' =>  $data['user_id'],
			'cloud_directory_id' => $data['cloud_directory_id']
			);

			$data1 = array(
				'is_deleted' => 1,
				'modified_on' => date('Y-m-d H:i:s'),
			);

		$this->db->where($Wheredata1); 

		if($this->db->update('cloud_file', $data1)){
			$response["status"] = "success";
			$response["message"] = "success";
			$response["response"] = 1;
			return $response;
		}
		else{
			$response["status"] = "failed";
			$response["message"] = "Some problem while delete file record.";
			$response["response"] = 0;
			return $response;
		}
	}else{
			$response["status"] = "failed";
			$response["message"] = "Some problem while delete file record at AWS.";
			$response["response"] = 0;
			return $response;
	}
	} catch (S3Exception $e) {
		$response["status"] = "failed";
		$response["message"] = $e->getMessage();
		$response["response"] = 0;
		return $response;
	} catch (Exception $e) {
		$response["status"] = "failed";
		$response["message"] = $e->getMessage();
		$response["response"] = 0;
		return $response;
	}
}

public function cloudlogs($user_id, $user_account_no, $cloud_directory_id = '') {
		
	if($cloud_directory_id && $cloud_directory_id != '') {
		$sql = "SELECT * FROM `cloud_logs` WHERE `user_id` = ".$user_id." AND `user_account_no` = '".$user_account_no."' AND `cloud_directory_id`=".$cloud_directory_id." order by id DESC";
	}
	else{
		$sql = "SELECT * FROM `cloud_logs` WHERE `user_id` = ".$user_id." AND `user_account_no` = '".$user_account_no."' order by id DESC";
	}
	return $this->db->query($sql)->result_array();
}




/*

* Create a Logs for Cloud in Table.

*/

function seletedDelete($data){

try {
$ids = explode( ',',$data['ids']);
$objID = '';		
foreach ($ids as $id){
	$objKey[]['Key'] = base64_decode($data['key']).$id;
	$objID .= "'".$id."',";
	$Wheredata1[] = array(	
		'user_id' =>  $data['user_id'],
		'cloud_directory_id' => $data['cloud_directory_id'],			  
		'file_name' =>  $id
	);

}

//$objID = (substr($objID,-1) == ',') ? substr($objID, 0, -1) : $objID;
$objID = rtrim($objID,',');

$result = $this->s3_bucket->deleteObjects([
			'Bucket' => S3_BUCKET,
			'Delete' => [
				'Objects' => $objKey,
				'Quiet' => false,
			],
]);


if($result)
{ 
	$description = base64_decode($data['key']).$data['ids'];
	self::CloudlogMsg($data['user_id'], $data['user_account_no'], $data['cloud_directory_id'], 'AWS Cloud S3', $description, S3_BUCKET, 'Delete');

$sql = "update  `cloud_file` set is_deleted = '1', modified_on = '".date('Y-m-d H:i:s')."' where file_name in (".$objID.") and user_id ='".$data['user_id']."' and cloud_directory_id='".$data['cloud_directory_id']."'";
$resultout = $this->db->query($sql);

		if($resultout){
			$response["status"] = "success";
			$response["message"] = "success";
			$response["response"] = 1;
			return $response;
		}
		else{
			$response["status"] = "failed";
			$response["message"] = "Some problem while delete file record.";
			$response["response"] = 0;
			return $response;
		}
	}else{
			$response["status"] = "failed";
			$response["message"] = "Some problem while delete file record at AWS.";
			$response["response"] = 0;
			return $response;
	}
	} catch (S3Exception $e) {
		$response["status"] = "failed";
		$response["message"] = $e->getMessage();
		$response["response"] = 0;
		return $response;
	} catch (Exception $e) {
		$response["status"] = "failed";
		$response["message"] = $e->getMessage();
		$response["response"] = 0;
		return $response;
	}
}


/*

* Create a Logs for Cloud in Table.

*/
public function CloudlogMsg($user_id, $user_account_no, $cloud_directory_id, $module, $description, $bucket_name, $status) {

	
	
	if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
			$ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
		}
		else{
			$ip = self::getUserIpAddr();
		}
	
		$dataArr['user_id'] = $user_id;
		$dataArr['user_account_no'] = $user_account_no;
		$dataArr['cloud_directory_id'] = $cloud_directory_id;
		$dataArr['module_name'] = $module;
		$dataArr['description'] = base64_encode(serialize($description));
		$dataArr['bucket_name'] = $bucket_name;
		$dataArr['ip_address'] = $ip;
		$dataArr['ip_isp'] = gethostbyaddr($ip);
		$dataArr['status'] = $status;
		$dataArr['created_on'] = date('Y-m-d H:i:s');
		$dataArr['modified_on'] = date('Y-m-d H:i:s');
		$this->db->insert('cloud_logs', $dataArr);
}

public function getUserIpAddr(){
    $ipaddress = '';
    if ($_SERVER['HTTP_CLIENT_IP'])
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if($_SERVER['HTTP_X_FORWARDED_FOR'])
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if($_SERVER['HTTP_X_FORWARDED'])
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if($_SERVER['HTTP_FORWARDED_FOR'])
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if($_SERVER['HTTP_FORWARDED'])
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if($_SERVER['HTTP_X_REAL_IP'])
        $ipaddress = $_SERVER['HTTP_X_REAL_IP'];
    else if($_SERVER['REMOTE_ADDR'])
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    $ips = explode(",",$ipaddress);
 
    return $ips[0];
}






}
