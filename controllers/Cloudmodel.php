<?php if (!defined("BASEPATH")) {
	exit("No direct script access allowed");
}
include_once "callapi.php";
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
class Cloudmodel extends CI_Model
{
	public $s3_bucket;

	/*
	 * TODO : Sonu Dahiya
	 * Default constructor
	 * Start
	 */

	public function __construct()
	{
		// Call the Model constructor
		parent::__construct();

		$bucketName = S3_BUCKET;
		// Connect to AWS
		try {
			// You may need to change the region. It will say in the URL when the bucket is open
			// and on creation.
			$this->s3_bucket = S3Client::factory([
				"credentials" => [
					"key" => S3_BUCKET_KEY,
					"secret" => S3_BUCKET_SECRET,
				],
				"version" => "latest",
				"region" => "us-east-2",
			]);
		} catch (Exception $e) {
			// We use a die, so if this fails. It stops here. Typically this is a REST call so this would
			// return a json object.
			$response["status"] = "failed";
			$response["message"] =
				"There seems to be an issue connecting to your cloud directory.";
			$response["response"] = 0;

			return $response;
		}
	}

	/*
	 * TODO : Sonu Dahiya
	 * Create a Directory/Folder
	 * Start
	 */
	function adddirectory($data)
	{
		// Check if Dir already exist in record.
		$parant_id = 0;

		if (!empty($data["parent_dir_id"])) {
			$parant_id = self::getdirectorykey($data["parent_dir_id"]);
			$parant_id = $parant_id[0]["parent_dir_id"];
		}

		$checkName = self::getdirectorybyname(
			$data["loan_id"],
			$data["name"],
			$data["parent_dir_id"],
			$parant_id[0]["parent_dir_id"]
		);
		$description = $data["name"];

		if ($checkName == 0) {
			$result = $this->s3_bucket->putObject([
				"Bucket" => S3_BUCKET, // Defines name of Bucket
				"Key" => base64_decode($data["dir_key"]), //Defines Folder name
				"Body" => "",
				"ACL" => "private", // Defines Permission to that folder, default: public-read
			]);

			if ($result) {
				if ($this->db->insert("document_directory", $data)) {
					$cloud_directory_id = $this->db->insert_id();
					//$description = base64_decode($data["dir_key"]);
					$response["status"] = "success";
					$response["message"] = "success";
					$response["id"] = $cloud_directory_id;
					$response["response"] = 1;
					self::CloudlogMsg(
						$data["user_id"],
						$data["loan_id"],
						$cloud_directory_id,
						"AWS Cloud S3",
						$description,
						S3_BUCKET,
						"Create"
					);
					return $response;
				} else {
					$response["status"] = "failed";
					$response["message"] =
						"Some problem while creating directory record.";
					$response["response"] = 0;
					return $response;
				}
			} else {
				$response["status"] = "failed";
				$response["message"] =
					"Some problem while creating directory at AWS.";
				$response["response"] = 0;
				return $response;
			}
		} else {
			$response["status"] = "failed";
			$response[
				"message"
			] = "A folder named $description already exists.";
			$response["response"] = 0;
			return $response;
		}
	}

	/*
	 * TODO : Sonu Dahiya
	 * Create a Directory/Folder
	 * Start
	 */
	function cuadddirectory($data)
	{
		// Check if Dir already exist in record.
		$parant_id = 0;
		if (!empty($data["parent_dir_id"])) {
			$parant_id = self::cugetdirectorykey($data["parent_dir_id"]);
			$parant_id = $parant_id[0]["parent_dir_id"];
		}
		$folder_name = $data["name"];

		$checkName = self::cugetdirectorybyname(
			$data["cu_id"],
			$data["name"],
			$data["parent_dir_id"],
			$parant_id[0]["parent_dir_id"]
		);
		$description = $data["description"];

		if ($checkName == 0) {
			$result = $this->s3_bucket->putObject([
				"Bucket" => S3_BUCKET, // Defines name of Bucket
				"Key" => base64_decode($data["dir_key"]), //Defines Folder name
				"Body" => "",
				"ACL" => "private", // Defines Permission to that folder, default: public-read
			]);
			if ($result) {
				if ($this->db->insert("cu_document_directory", $data)) {
					$cloud_directory_id = $this->db->insert_id();
					//$description = base64_decode($data["dir_key"]);
					$response["status"] = "success";
					$response["message"] = "success";
					$response["id"] = $cloud_directory_id;
					$response["response"] = 1;
					self::CloudlogMsg(
						$data["user_id"],
						$data["cu_id"],
						$cloud_directory_id,
						"AWS Cloud S3",
						$description,
						S3_BUCKET,
						"Create"
					);
					return $response;
				} else {
					$response["status"] = "failed";
					$response["message"] =
						"Some problem while creating directory record.";
					$response["response"] = 0;
					return $response;
				}
			} else {
				$response["status"] = "failed";
				$response["message"] =
					"Some problem while creating directory at AWS.";
				$response["response"] = 0;
				return $response;
			}
		} else {
			$response["status"] = "failed";
			$response[
				"message"
			] = "A folder named $folder_name already exists.";
			$response["response"] = 0;
			return $response;
		}
	}

	/*
	 * TODO : Sonu Dahiya
	 * Edit a Directory/Folder
	 * Start
	 */
	function editdirectory($data)
	{
		// try {
		// 	$response = $this->s3_bucket->copyObject([
		// 		"Bucket" => S3_BUCKET,
		// 		"CopySource" => urlencode(
		// 			S3_BUCKET . "/" . base64_decode($data["dir_key_old"])
		// 		),
		// 		"Key" => base64_decode($data["dir_key_new"]),
		// 		"MetadataDirective" => "COPY",
		// 	]);

		// 	if (!empty($response)) {
		//	$this->s3_bucket->deleteObject(['Bucket' => S3_BUCKET, 'Key' => base64_decode($data["dir_key_old"])]);
		$Wheredata1 = [
			"id" => $data["id"],
			"loan_id" => $data["loan_id"],
		];

		$data1 = [
			"user_id" => $data["user_id"],
			"loan_id" => $data["loan_id"],
			"name" => $data["name"],
			"description" => $data["description"],
			// "parent_dir_name" => $data["parent_dir_name"],
			//"parent_dir_id" => $data["parent_dir_id"],
			//"dir_key" => $data["dir_key_new"],
			"modified_on" => date("Y-m-d H:i:s"),
		];

		$this->db->where($Wheredata1);

		if ($this->db->update("document_directory", $data1)) {
			//	$cloud_directory_id = $this->db->insert_id();
			//	$description = base64_decode($data["dir_key"]);
			$response["status"] = "success";
			$response["message"] = "success";
			$response["response"] = 1;
			// self::CloudlogMsg(
			// 	$data["user_id"],
			// 	$data["loan_id"],
			// 	$cloud_directory_id,
			// 	"AWS Cloud S3",
			// 	$data["description"],
			// 	S3_BUCKET,
			// 	"Edit"
			// );
			return $response;
		} else {
			$response["status"] = "failed";
			$response["message"] = "Some problem while creating directory.";
			$response["response"] = 0;
			return $response;
		}
		// } else {
		// 	$response["status"] = "failed";
		// 	$response["message"] =
		// 		"Some problem while update folder record at AWS.";
		// 	$response["response"] = 0;
		// 	return $response;
		// }
		// } catch (S3Exception $e) {
		// 	$response["status"] = "failed";
		// 	$response["message"] = $e->getMessage();
		// 	$response["response"] = 0;
		// 	return $response;
		// } catch (Exception $e) {
		// 	$response["status"] = "failed";
		// 	$response["message"] = $e->getMessage();
		// 	$response["response"] = 0;
		// 	return $response;
		// }
	}

	/*
	 * TODO : Sonu Dahiya
	 * CU Edit a Directory/Folder
	 * Start
	 */
	function cueditdirectory($data)
	{
		$Wheredata1 = [
			"id" => $data["id"],
			"cu_id" => $data["cu_id"],
		];

		$data1 = [
			"user_id" => $data["user_id"],
			"cu_id" => $data["cu_id"],
			"name" => $data["name"],
			"description" => $data["description"],
			"modified_on" => date("Y-m-d H:i:s"),
		];

		$this->db->where($Wheredata1);

		if ($this->db->update("cu_document_directory", $data1)) {
			$response["status"] = "success";
			$response["message"] = "success";
			$response["response"] = 1;
			return $response;
		} else {
			$response["status"] = "failed";
			$response["message"] =
				"Some problem while creating directory.";
			$response["response"] = 0;
			return $response;
		}
	}

	/*
	 * TODO : Sonu Dahiya
	 * Get Directory/Folder list with file size if exist according to dir id
	 * Start
	 */
	function getdirectory($userid)
	{
		$sql =
			"SELECT `name`, `id` FROM `document_directory` WHERE `user_id` =" .
			$userid .
			" AND `parent_dir_id` = 0 ORDER BY `name`";

		return $this->db->query($sql)->result_array();
	}

	/* Get loan directory key */

	public function getdirectorykey($id)
	{
		$sql = "SELECT cd.dir_key, cd.name, cd.parent_dir_id,cd.loan_id FROM `document_directory` cd  where cd.id=$id";
		$sql .= " ORDER BY cd.id Limit 0,1";
		return $this->db->query($sql)->result_array();
	}

	/* Get loan directory info */
	public function docDirectoryInfo($id)
	{
		$sql = "SELECT cd.id,cd.loan_id FROM `document_directory` cd  WHERE cd.loan_id=$id";
		return $this->db->query($sql)->result_array();
	}

	/* Get CU directory key */
	public function cugetdirectorykey($id)
	{
		$sql = "SELECT cd.dir_key, cd.name, cd.parent_dir_id,cd.cu_id FROM `cu_document_directory` cd where cd.id=$id";
		$sql .= " ORDER BY cd.id Limit 0,1";
		return $this->db->query($sql)->result_array();
	}

	/* Get Cu directory info */
	public function cudocDirectoryInfo($id)
	{
		$sql = "SELECT cd.id,cd.cu_id FROM `cu_document_directory` cd  WHERE cd.cu_id=$id";
		return $this->db->query($sql)->result_array();
	}

	/*
	 * TODO : Sonu Dahiya
	 * Get Directory/Folder id according to id and name
	 * Start
	 */
	function getdirectorybyname(
		$loanid,
		$name,
		$cloud_directory_id = "",
		$parent_dir_id
	) {
		if ($cloud_directory_id && $cloud_directory_id != "") {
			$sql =
				"SELECT `id` FROM `document_directory` WHERE `loan_id` = " .
				$loanid .
				" AND `name` = '" .
				$name .
				"' AND `id` != " .
				$cloud_directory_id .
				" AND `parent_dir_id` = " .
				$cloud_directory_id;
		} else {
			$sql =
				"SELECT `id` FROM `document_directory` WHERE `loan_id` = " .
				$loanid .
				" AND `name` = '" .
				$name .
				"'";
		}
		return $this->db->query($sql)->num_rows();
	}

	/* Get CU Directory/Folder id according to id and name */

	function cugetdirectorybyname(
		$cu_id,
		$name,
		$cloud_directory_id = "",
		$parent_dir_id
	) {
		if ($cloud_directory_id && $cloud_directory_id != "") {
			$sql =
				"SELECT `id` FROM `cu_document_directory` WHERE `cu_id` = '" .
				$cu_id .
				"' AND `name` = '" .
				$name .
				"' AND `id` != " .
				$cloud_directory_id .
				" AND `parent_dir_id` = " .
				$cloud_directory_id;
		} else {
			$sql =
				"SELECT `id` FROM `cu_document_directory` WHERE `cu_id` = '" .
				$cu_id .
				"' AND `name` = '" .
				$name .
				"'";
		}
		return $this->db->query($sql)->num_rows();
	}

	/*
	 * TODO : sonu dahiya
	 * Upload a multiple file in AWS S3 Bucket and Add record in table.
	 * Start
	 */
	function multiupload($data)
	{
		$key = base64_decode($data["dir_key"]) . $data["file_name"];
		$pathInS3 = S3_BUCKET_PATH . S3_BUCKET . "/" . $key;
		// Add it to S3
		try {
			$result = $this->s3_bucket->putObject([
				"Bucket" => S3_BUCKET, // Defines name of Bucket
				"Key" => $key, //Defines Folder name
				"SourceFile" => $data["file_tmp_name"],
				"StorageClass" => "REDUCED_REDUNDANCY",
				"ACL" => "private",
			]);

			if ($result) {
				$description = $data["file_name"];
				self::CloudlogMsg(
					$data["user_id"],
					$data["loan_id"],
					$data["cloud_directory_id"],
					"AWS Cloud S3",
					$description,
					S3_BUCKET,
					"upload"
				);

				$data1 = [
					"user_id" => $data["user_id"],
					"loan_id" => $data["loan_id"],
					"cloud_directory_id" => $data["cloud_directory_id"],
					"parent_dir_id" => $data["parent_dir_id"],
					"dir_key" => base64_encode($key),
					"file_extension" => $data["file_extension"],
					"file_size" => $data["file_size"],
					"file_name" => $data["file_name"],
					"random_numer" => $data["random_numer"],
					"file_path" => base64_encode($pathInS3),
					"created_on" => date("Y-m-d H:i:s"),
					"added_by" => $data["added_by"],
				];

				if ($this->db->insert("document_file", $data1)) {
					$response["status"] = "success";
					$response["message"] = "success";
					$response["response"] = 1;
					return $response;
				} else {
					$response["status"] = "failed";
					$response["message"] =
						"Some problem while manage file record.";
					$response["response"] = 0;
					return $response;
				}
			} else {
				$response["status"] = "failed";
				$response["message"] =
					"Some problem while manage file record at AWS.";
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

		return $response;
	}

	/*
	 * TODO : sonu dahiya
	 * CU Upload a multiple file in AWS S3 Bucket and Add record in table.
	 * Start
	 */
	function cumultiupload($data)
	{
		$key = base64_decode($data["dir_key"]) . $data["file_name"];
		$pathInS3 = S3_BUCKET_PATH . S3_BUCKET . "/" . $key;

		// Add it to S3
		try {
			$result = $this->s3_bucket->putObject([
				"Bucket" => S3_BUCKET, // Defines name of Bucket
				"Key" => $key, //Defines Folder name
				"SourceFile" => $data["file_tmp_name"],
				"StorageClass" => "REDUCED_REDUNDANCY",
				"ACL" => "private",
			]);

			if ($result) {
				$description = $data["file_name"];
				self::CloudlogMsg(
					$data["user_id"],
					$data["cu_id"],
					$data["cloud_directory_id"],
					"AWS Cloud S3",
					$description,
					S3_BUCKET,
					"upload"
				);

				$data1 = [
					"user_id" => $data["user_id"],
					"cu_id" => $data["cu_id"],
					"cloud_directory_id" => $data["cloud_directory_id"],
					"parent_dir_id" => $data["parent_dir_id"],
					"dir_key" => base64_encode($key),
					"file_extension" => $data["file_extension"],
					"file_size" => $data["file_size"],
					"file_name" => $data["file_name"],
					"random_numer" => $data["random_numer"],
					"file_path" => base64_encode($pathInS3),
					"created_on" => date("Y-m-d H:i:s"),
					"added_by" => $data["added_by"],
				];

				if ($this->db->insert("cu_document_file", $data1)) {
					$response["status"] = "success";
					$response["message"] = "success";
					$response["response"] = 1;
					return $response;
				} else {
					$response["status"] = "failed";
					$response["message"] =
						"Some problem while manage file record.";
					$response["response"] = 0;
					return $response;
				}
			} else {
				$response["status"] = "failed";
				$response["message"] =
					"Some problem while manage file record at AWS.";
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

		return $response;
	}

	/*
	 * TODO : Sonu Dahiya
	 * Get file url from AWS S3 Bucket
	 * Start
	 */
	function fileurl($data)
	{
		try {
			$dirpath_name = $data;
			$pathInS3 = S3_BUCKET_PATH . S3_BUCKET . "/" . $dirpath_name;

			foreach (
				$iterator = $this->s3_bucket->getIterator("ListObjects", [
					"Bucket" => S3_BUCKET,
					"Prefix" => $dirpath_name,
					"Delimiter" => "/",
				])
				as $object
			) {
				$file_acl = $this->s3_bucket->getObjectAcl([
					"Bucket" => S3_BUCKET,
					"Key" => $object["Key"],
				]);

				$is_private = true;
				foreach ($file_acl["Grants"] as $grant) {
					if (
						isset($grant["Grantee"]["effectiveUri"]) &&
						$grant["Grantee"]["effectiveUri"] ==
							S3_BUCKET_PATH . S3_BUCKET &&
						$grant["Permission"] == "READ"
					) {
						$is_private = false; # this file is not private
					}

					if ($is_private == false) {
						$file_url = $this->s3_bucket->getObjectUrl(
							S3_BUCKET,
							$object["Key"]
						);
					} else {
						$url_creator = $this->s3_bucket->getCommand(
							"GetObject",
							[
								"Bucket" => S3_BUCKET,
								"Key" => $object["Key"],
							]
						);

						$signed_url = $this->s3_bucket->createPresignedRequest(
							$url_creator,
							"+10 minutes"
						);
						$file_url = $signed_url->getUri();
					}

					$nameName = explode("/", $object["Key"]);

					$dados[] = [
						"key" => $object["Key"],
						"name" => $nameName,
						"link" => $file_url,
					];
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

	// get only this file's details
	function getFileUrl($data)
	{
		try {
			$dirpath_name = $data;
			$pathInS3 = S3_BUCKET_PATH . S3_BUCKET . "/" . $dirpath_name;
				$file_acl = $this->s3_bucket->getObjectAcl([
					"Bucket" => S3_BUCKET,
					"Key" => $dirpath_name,
				]);

				$is_private = true;
				foreach ($file_acl["Grants"] as $grant) {
					if (
						isset($grant["Grantee"]["effectiveUri"]) &&
						$grant["Grantee"]["effectiveUri"] ==
							S3_BUCKET_PATH . S3_BUCKET &&
						$grant["Permission"] == "READ"
					) {
						$is_private = false; # this file is not private
					}

					if ($is_private == false) {
						$file_url = $this->s3_bucket->getObjectUrl(
							S3_BUCKET,
							$dirpath_name
						);
					} else {
						$url_creator = $this->s3_bucket->getCommand(
							"GetObject",
							[
								"Bucket" => S3_BUCKET,
								"Key" => $dirpath_name,
							]
						);

						$signed_url = $this->s3_bucket->createPresignedRequest(
							$url_creator,
							"+10 minutes"
						);
						$file_url = $signed_url->getUri();
					}

					$nameName = explode("/", $dirpath_name);

					$dados[] = [
						"key" => $dirpath_name,
						"name" => $nameName,
						"link" => $file_url,
					];
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
	 * TODO : Sonu Dahiya
	 * Download a file from AWS S3 Bucket
	
	 * Start
	 */
	function download($data)
	{
		// Add it to S3
		try {
			$dirpath_name =
				$data["loan_id"] . "/" . $data["directory_name"] . "/";
			# lets download our file
			$file_to_download = $dirpath_name . $data["file_name"];
			$pathInS3 = S3_BUCKET_PATH . S3_BUCKET . "/" . $file_to_download;
			$download_as_path = __DIR__ . "/downloads/";
			$result = $this->s3_bucket->getObject([
				"Bucket" => S3_BUCKET,
				"Key" => $file_to_download,
				"SaveAs" => $data["file_name"],
			]);
			// Display the object in the browser.
			header("Content-Type: {$result["ContentType"]}");
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
	 * TODO : Sonu Dahiya
	 * Delete a file from AWS S3 Bucket
	 * Start
	 */
	function delete($data)
	{
		try {
			$result = $this->s3_bucket->deleteObject([
				"Bucket" => S3_BUCKET,
				"Key" => $data["key"],
			]);

			if ($result) {
				$description = $data["key"];
				self::CloudlogMsg(
					$data["user_id"],
					$data["loan_id"],
					$data["id"],
					"AWS Cloud S3",
					$description,
					S3_BUCKET,
					"delete"
				);

				$Wheredata1 = [
					"id" => $data["id"],
					"file_name" => $data["file_name"],
				];

				$data1 = [
					"is_deleted" => 1,
					"modified_on" => date("Y-m-d H:i:s"),
				];

				$this->db->where($Wheredata1);

				if ($this->db->update("document_file", $data1)) {
					$response["status"] = "success";
					$response["message"] = "success";
					$response["response"] = 1;
					return $response;
				} else {
					$response["status"] = "failed";
					$response["message"] =
						"Some problem while delete file record.";
					$response["response"] = 0;
					return $response;
				}
			} else {
				$response["status"] = "failed";
				$response["message"] =
					"Some problem while delete file record at AWS.";
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
	 * TODO : Sonu Dahiya
	 * CU Delete a file from AWS S3 Bucket
	 * Start
	 */
	function cudelete($data)
	{
		try {
			$result = $this->s3_bucket->deleteObject([
				"Bucket" => S3_BUCKET,
				"Key" => $data["key"],
			]);
			if ($result) {
				$description = $data["key"];
				self::CloudlogMsg(
					$data["user_id"],
					$data["cu_id"],
					$data["id"],
					"AWS Cloud S3",
					$description,
					S3_BUCKET,
					"delete"
				);

				$Wheredata1 = [
					"id" => $data["id"],
					"file_name" => $data["file_name"],
				];

				$data1 = [
					"is_deleted" => 1,
					"modified_on" => date("Y-m-d H:i:s"),
				];

				$this->db->where($Wheredata1);

				if ($this->db->update("cu_document_file", $data1)) {
					$response["status"] = "success";
					$response["message"] = "success";
					$response["response"] = 1;
					return $response;
				} else {
					$response["status"] = "failed";
					$response["message"] =
						"Some problem while delete file record.";
					$response["response"] = 0;
					return $response;
				}
			} else {
				$response["status"] = "failed";
				$response["message"] =
					"Some problem while delete file record at AWS.";
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
	 * TODO : Sonu Dahiya
	 * Delete all files from AWS S3 Bucket
	 * Start
	 */
	function deleteAll($data)
	{
		try {
			foreach (
				$iterator = $this->s3_bucket->getIterator("ListObjects", [
					"Bucket" => S3_BUCKET,
					"Prefix" => $data["key"],
				])
				as $object
			) {
				$objKey[]["Key"] = $object["Key"];
			}

			$result = $this->s3_bucket->deleteObjects([
				"Bucket" => S3_BUCKET,
				"Delete" => [
					"Objects" => $objKey,
					"Quiet" => false,
				],
			]);

			if ($result) {
				$description = $data["key"];
				self::CloudlogMsg(
					$data["user_id"],
					$data["loan_id"],
					$data["cloud_directory_id"],
					"AWS Cloud S3",
					$description,
					S3_BUCKET,
					"delete"
				);

				$Wheredata1 = [
					"user_id" => $data["user_id"],
					"cloud_directory_id" => $data["cloud_directory_id"],
				];

				$data1 = [
					"is_deleted" => 1,
					"modified_on" => date("Y-m-d H:i:s"),
				];

				$this->db->where($Wheredata1);

				if ($this->db->update("document_file", $data1)) {
					$response["status"] = "success";
					$response["message"] = "success";
					$response["response"] = 1;
					return $response;
				} else {
					$response["status"] = "failed";
					$response["message"] =
						"Some problem while delete file record.";
					$response["response"] = 0;
					return $response;
				}
			} else {
				$response["status"] = "failed";
				$response["message"] =
					"Some problem while delete file record at AWS.";
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

	public function cloudlogs($user_id, $loan_id, $cloud_directory_id = "")
	{
		if ($cloud_directory_id && $cloud_directory_id != "") {
			$sql =
				"SELECT * FROM `document_logs` WHERE `user_id` = " .
				$user_id .
				" AND `loan_id` = '" .
				$loan_id .
				"' AND `cloud_directory_id`=" .
				$cloud_directory_id .
				" order by id DESC";
		} else {
			$sql =
				"SELECT * FROM `document_logs` WHERE `user_id` = " .
				$user_id .
				" AND `loan_id` = '" .
				$loan_id .
				"' order by id DESC";
		}
		return $this->db->query($sql)->result_array();
	}

	/*
	 * TODO : Sonu Dahiya
	 * Delete folder for Cloud in Table.
	 * Start
	 */

	function seletedDelete($data)
	{
		try {
			$response_list = $this->s3_bucket->getIterator("ListObjects", [
				"Bucket" => S3_BUCKET,
				"Prefix" => base64_decode($data["key"]),
			]);
			//delete each
			foreach ($response_list as $object) {
				$fileName = $object["Key"];
				$this->s3_bucket->deleteObject([
					"Bucket" => S3_BUCKET,
					"Key" => $fileName,
				]);
			}
			$result = $this->s3_bucket->deleteObject([
				"Bucket" => S3_BUCKET,
				"Key" => base64_decode($data["key"]),
			]);
			if ($result) {
				// $description = base64_decode($data["key"]) . $data["id"];
				// self::CloudlogMsg(
				// 	$data["user_id"],
				// 	$data["loan_id"],
				// 	$data["cloud_directory_id"],
				// 	"AWS Cloud S3",
				// 	$description,
				// 	S3_BUCKET,
				// 	"Delete"
				// );

$sqlout ="DELETE FROM  `document_directory` where loan_id ='" .	$data["loan_id"] ."' and id='" .$data["cloud_directory_id"] ."'";

				$resultoutdata = $this->db->query($sqlout);

				$sql =
				"DELETE FROM `document_file` where loan_id ='" .
				$data["loan_id"] .
				"' and cloud_directory_id='" .
				$data["cloud_directory_id"] .
				"'";
				$resultout = $this->db->query($sql);

				if ($resultout) {
					$response["status"] = "success";
					$response["message"] = "success";
					$response["response"] = 1;
					return $response;
				} else {
					$response["status"] = "failed";
					$response["message"] =
						"Some problem while delete file record.";
					$response["response"] = 0;
					return $response;
				}
			} else {
				$response["status"] = "failed";
				$response["message"] =
					"Some problem while delete file record at AWS.";
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
	 * TODO : Sonu Dahiya
	 * CU Delete Folder for Cloud in Table.
	 * Start
	 */

	function cuseletedDelete($data)
	{
		try {
			$response_list = $this->s3_bucket->getIterator("ListObjects", [
				"Bucket" => S3_BUCKET,
				"Prefix" => base64_decode($data["key"]),
			]);
			//delete each
			foreach ($response_list as $object) {
				$fileName = $object["Key"];
				$this->s3_bucket->deleteObject([
					"Bucket" => S3_BUCKET,
					"Key" => $fileName,
				]);
			}

			$result = $this->s3_bucket->deleteObject([
				"Bucket" => S3_BUCKET,
				"Key" => base64_decode($data["key"]),
			]);

			if ($result) {
				// $description = base64_decode($data["key"]) . $data["id"];
				// self::CloudlogMsg(
				// 	$data["user_id"],
				// 	$data["loan_id"],
				// 	$data["cloud_directory_id"],
				// 	"AWS Cloud S3",
				// 	$description,
				// 	S3_BUCKET,
				// 	"Delete"
				// );

				$sqlout =
					"DELETE FROM `cu_document_directory` where cu_id ='" .
					$data["cu_id"] .
					"' and id='" .
					$data["cloud_directory_id"] .
					"'";

				$resultoutdata = $this->db->query($sqlout);

				$sql =
					"DELETE FROM `cu_document_file` where cu_id ='" .
					$data["cu_id"] .
					"' and cloud_directory_id='" .
					$data["cloud_directory_id"] .
					"'";
				$resultout = $this->db->query($sql);

				if ($resultout) {
					$response["status"] = "success";
					$response["message"] = "success";
					$response["response"] = 1;
					return $response;
				} else {
					$response["status"] = "failed";
					$response["message"] =
						"Some problem while delete file record.";
					$response["response"] = 0;
					return $response;
				}
			} else {
				$response["status"] = "failed";
				$response["message"] =
					"Some problem while delete file record at AWS.";
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
	 * TODO : Sonu Dahiya
	 * Create a Logs for Cloud in Table.
	 *
	 * Start
	 */
	public function CloudlogMsg(
		$user_id,
		$loan_id,
		$cloud_directory_id,
		$module,
		$description,
		$bucket_name,
		$status
	) {
		if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
			$ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
		} else {
			$ip = self::getUserIpAddr();
		}

		$dataArr["user_id"] = $user_id;
		$dataArr["loan_id"] = $loan_id;
		$dataArr["cloud_directory_id"] = $cloud_directory_id;
		$dataArr["module_name"] = $module;
		$dataArr["description"] = base64_encode(serialize($description));
		$dataArr["bucket_name"] = $bucket_name;
		$dataArr["ip_address"] = $ip;
		$dataArr["ip_isp"] = gethostbyaddr($ip);
		$dataArr["status"] = $status;
		$dataArr["created_on"] = date("Y-m-d H:i:s");
		$dataArr["modified_on"] = date("Y-m-d H:i:s");
		$this->db->insert("document_logs", $dataArr);
	}

	public function getUserIpAddr()
	{
		$ipaddress = "";
		if ($_SERVER["HTTP_CLIENT_IP"]) {
			$ipaddress = $_SERVER["HTTP_CLIENT_IP"];
		} elseif ($_SERVER["HTTP_X_FORWARDED_FOR"]) {
			$ipaddress = $_SERVER["HTTP_X_FORWARDED_FOR"];
		} elseif ($_SERVER["HTTP_X_FORWARDED"]) {
			$ipaddress = $_SERVER["HTTP_X_FORWARDED"];
		} elseif ($_SERVER["HTTP_FORWARDED_FOR"]) {
			$ipaddress = $_SERVER["HTTP_FORWARDED_FOR"];
		} elseif ($_SERVER["HTTP_FORWARDED"]) {
			$ipaddress = $_SERVER["HTTP_FORWARDED"];
		} elseif ($_SERVER["HTTP_X_REAL_IP"]) {
			$ipaddress = $_SERVER["HTTP_X_REAL_IP"];
		} elseif ($_SERVER["REMOTE_ADDR"]) {
			$ipaddress = $_SERVER["REMOTE_ADDR"];
		} else {
			$ipaddress = "UNKNOWN";
		}
		$ips = explode(",", $ipaddress);

		return $ips[0];
	}

	/*
	 * TODO : Sonu Dahiya
	 * Download a folder from AWS S3 Bucket
	 * Start
	 */
	function downloadFolder($data)
	{
		try {
			$zipfolder = explode("/", base64_decode($data["key"]));
			$folder_name = $zipfolder[1] . ".zip";
			$bucket = S3_BUCKET;
			$directory = base64_decode($data["key"]);
			chdir("uploads/");
			$download_as_path = getcwd() . "/" . $data["user_id"] . "/" . $data["id"] . "/";
			$result = $this->s3_bucket->downloadBucket(
				$download_as_path . $zipfolder[1],
				$bucket,
				$directory
			);
			chdir($data["user_id"] . "/" . $data["id"] . "/");
			// File name
			$filename = $folder_name;
			// Directory path (uploads directory stored in project root)
			$path = $zipfolder[1];
			// Add directory to zip
			$this->zip->read_dir($path);
			// Save the zip file to archivefiles directory
			$path = $download_as_path . $zipfolder[1] . "/";
			$this->load->helper("file"); // load the helper
			delete_files($path, true); // delete all files/folders inside images folder
			rmdir($zipfolder[1]);
			// Download
			$download = $this->zip->download($filename);
			$response["status"] = "success";
			$response["message"] = "success";
			$response["response"] = 1;
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
	}
}
