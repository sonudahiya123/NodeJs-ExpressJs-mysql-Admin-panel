<?php if (!defined("BASEPATH")) {
	exit("No direct script access allowed");
}
require_once APPPATH . "libraries/vendor/autoload.php";
class Cloud extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model("cloudmodel");
		$this->load->model("loans_model");
		$this->load->model("user_model");
		$this->load->model("creditUnions_model");
		$this->load->helper("url");
		// Load zip library
		$this->load->library("zip");
	}

	/*
	 * TODO : Sonu Dahiya
	 * Add single, Direcotry in Table and S3 Bucket, Start
	 */
	public function adddirectory()
	{
		$data["session"] = $this->session->all_userdata();

		$directory_name = $this->input->post("directory_name");
		$directory_desc = $this->input->post("directory_desc");
		$loan_id = $this->input->post("directory_id");
		$id = unserialize(base64_decode($this->input->post("directory_id")));
		$ip = $this->cloudmodel->getUserIpAddr();
		if (!empty($id)) {
			$res = $this->cloudmodel->getdirectorykey($id);
			$parent_key = base64_decode($res[0]["dir_key"]);
			$key = $parent_key . $directory_name . "/";
			$data = [
				"user_id" => $_SESSION["userId"],
				"loan_id" => $loan_id,
				"name" => $directory_name,
				"description" => $directory_desc,
				"parent_dir_name" => $parent_key,
				"parent_dir_id" => $id,
				"dir_key" => base64_encode($key),
				"ip_address" => $ip,
				"ip_isp" => gethostbyaddr($ip),
				"bucket_name" => S3_BUCKET,
				"created_on" => date("Y-m-d H:i:s"),
			];
		} else {
			$key = $loan_id . "/" . $directory_name . "/";
			$data = [
				"user_id" => $_SESSION["userId"],
				"loan_id" => $loan_id,
				"name" => $directory_name,
				"description" => $directory_desc,
				"dir_key" => base64_encode($key),
				"ip_address" => $ip,
				"ip_isp" => gethostbyaddr($ip),
				"bucket_name" => S3_BUCKET,
				"created_on" => date("Y-m-d H:i:s"),
			];
		}

		$res = $this->cloudmodel->adddirectory($data);
		if ($res["status"] == "failed") {
			$status = "error";
			setFlashData(
				$status,
				"A folder named $directory_name already exists"
			);
		}
		redirect("/e22loandetails/$loan_id");
	}

	/*
	 * TODO : Sonu Dahiya
	 * Add single, Direcotry in Table and S3 Bucket for CU, Start
	 * Start
	 */
	public function cuadddirectory()
	{
		$data["session"] = $this->session->all_userdata();
		$cid = $this->input->post("cid");
		$directory_name = $this->input->post("directory_name");
		$directory_desc = $this->input->post("directory_desc");
		$cu_id = $this->input->post("directory_id");
		$id = unserialize(base64_decode($this->input->post("directory_id")));
		$ip = $this->cloudmodel->getUserIpAddr();
		if (!empty($id)) {
			$res = $this->cloudmodel->cugetdirectorykey($id);
			$parent_key = base64_decode($res[0]["dir_key"]);
			$key = $parent_key . $directory_name . "/";
			$data = [
				"user_id" => $_SESSION["userId"],
				"cu_id" => $cu_id,
				"name" => $directory_name,
				"description" => $directory_desc,
				"parent_dir_name" => $parent_key,
				"parent_dir_id" => $id,
				"dir_key" => base64_encode($key),
				"ip_address" => $ip,
				"ip_isp" => gethostbyaddr($ip),
				"bucket_name" => S3_BUCKET,
				"created_on" => date("Y-m-d H:i:s"),
			];
		} else {
			$key = $cu_id . "/" . $directory_name . "/";
			$data = [
				"user_id" => $_SESSION["userId"],
				"cu_id" => $cu_id,
				"name" => $directory_name,
				"description" => $directory_desc,
				"dir_key" => base64_encode($key),
				"ip_address" => $ip,
				"ip_isp" => gethostbyaddr($ip),
				"bucket_name" => S3_BUCKET,
				"created_on" => date("Y-m-d H:i:s"),
			];
		}

		$res = $this->cloudmodel->cuadddirectory($data);
		if ($res["status"] == "failed") {
			$status = "error";
			setFlashData(
				$status,
				"A folder named $directory_name already exists"
			);
		}

		if ($_SESSION["role"] == 2) {
			redirect("/documents");
		} else {
			redirect("/edit-credit-union/$cu_id");
		}
	}

	/*
	 * TODO : Sonu Dahiya
	 * Edit single, Direcotry in Table and S3 Bucket, Start
	 * Start
	 */
	public function editdirectory()
	{
		$data["session"] = $this->session->all_userdata();
		$directory_name = $this->input->post("d_name");
		$directory_desc = $this->input->post("d_desc");
		$loan_id = $this->input->post("loan_id");
		$id = $this->input->post("d_id");
		if (!empty($id)) {
			$res = $this->cloudmodel->getdirectorykey($id);
			$parent_key = base64_decode($res[0]["dir_key"]);
			$key = $parent_key;
			$nkey = $loan_id . "/" . $directory_name . "/";
			$data = [
				"id" => $id,
				"user_id" => $_SESSION["userId"],
				"loan_id" => $loan_id,
				"name" => $directory_name,
				"description" => $directory_desc,
				"parent_dir_name" => $parent_key,
				"dir_key_old" => base64_encode($key),
				"dir_key_new" => base64_encode($nkey),
				"modified_on" => date("Y-m-d H:i:s"),
			];
		}
		$res = $this->cloudmodel->editdirectory($data);
		if ($res["status"] == "failed") {
			$status = "error";
			setFlashData(
				$status,
				"A folder named $directory_name already exists"
			);
		}
		redirect("/e22loandetails/$loan_id");
	}

	/*
	 * TODO : Sonu Dahiya
	 * CU Edit single, Direcotry in Table and S3 Bucket, Start
	 * Start
	 */
	public function cueditdirectory()
	{
		$data["session"] = $this->session->all_userdata();
		$directory_name = $this->input->post("d_name");
		$directory_desc = $this->input->post("d_desc");
		$cu_id = $this->input->post("cu_id");
		$id = $this->input->post("d_id");
		if (!empty($id)) {
			$res = $this->cloudmodel->cugetdirectorykey($id);
			$parent_key = base64_decode($res[0]["dir_key"]);
			$key = $parent_key;
			$nkey = $cu_id . "/" . $directory_name . "/";
			$data = [
				"id" => $id,
				"user_id" => $_SESSION["userId"],
				"cu_id" => $cu_id,
				"name" => $directory_name,
				"description" => $directory_desc,
				"parent_dir_name" => $parent_key,
				"dir_key_old" => base64_encode($key),
				"dir_key_new" => base64_encode($nkey),
				"modified_on" => date("Y-m-d H:i:s"),
			];
		}
		$res = $this->cloudmodel->cueditdirectory($data);
		if ($res["status"] == "failed") {
			$status = "error";
			setFlashData(
				$status,
				"A folder named $directory_name already exists"
			);
		}

		if ($_SESSION["role"] == 2) {
			redirect("/documents");
		} else {
			redirect("/edit-credit-union/$cu_id");
		}
	}

	/* Get Direcotry Name from Table, Start  */
	public function getdirectory()
	{
		$res = $this->cloudmodel->getdirectory($_SESSION["userId"]);
	}

	/* Get Direcotry key from Table, Start  */
	public function getdirectorykey()
	{
		$id = unserialize(base64_decode($_GET["id"]));
		$res = $this->cloudmodel->getdirectorykey($id);
	}

	/* Get Direcotry Name Size from Table, Start  */
	public function getfilesize($cloud_directory_id)
	{
		return $this->cloudmodel->getfilesize($cloud_directory_id);
	}

	/* Upload Multiple File in Table and cloud, Start */
	public function multiupload()
	{
		$data["session"] = $this->session->all_userdata();
		$loan_id = $this->input->post("loan_id");
		$id = $this->input->post("directory_id");
		$res = $this->cloudmodel->getdirectorykey($id);
		$parent_dir_id = $res[0]["parent_dir_id"];
		$key = base64_decode($res[0]["dir_key"]);
		$resultData = $this->loans_model->FileUploadCU($loan_id);
		$data["loan_id"] = $loan_id;
		$data["id"] = $id;

		if (count($_FILES["fileToUpload1"]["name"]) > 0) {
			//Loop through each file
			for ($i = 0; $i < count($_FILES["fileToUpload1"]["name"]); $i++) {
				$RandomNumber = rand(1000, 100000000);

				$data = [
					"user_id" => $_SESSION["userId"],
					"loan_id" => $loan_id,
					"directory_name" => $res[0]["name"],
					"cloud_directory_id" => $id,
					"parent_dir_id" => $parent_dir_id,
					"added_by" => $_SESSION["name"],
					"dir_key" => base64_encode($key),
					"file_extension" => pathinfo(
						$_FILES["fileToUpload1"]["name"][$i],
						PATHINFO_EXTENSION
					),
					"file_size" => $_FILES["fileToUpload1"]["size"][$i],
					"file_name" => basename(
						$_FILES["fileToUpload1"]["name"][$i]
					),
					"random_numer" => $RandomNumber,
					"file_tmp_name" => $_FILES["fileToUpload1"]["tmp_name"][$i],
					"bucket_name" => S3_BUCKET,
				];
				$res = $this->cloudmodel->multiupload($data);
			}

			foreach ($resultData as $userInfo) {
				$creditUnionUser = $this->user_model->creditUnionUser(
					$userInfo->credit_union_id
				);

				foreach ($creditUnionUser as $user) {
					$email = $user->usr_email;

					$data["email"] = $email;

					$sendStatus = fileUploadEmail($data);
				}
			}
		}

		redirect("/e22loandetails/$loan_id");
	}

	/* CU Upload Multiple File in Table and cloud, Start */
	public function cumultiupload()
	{
		$data["session"] = $this->session->all_userdata();
		$cu_id = $this->input->post("cu_id");
		$id = $this->input->post("directory_id");
		$res = $this->cloudmodel->cugetdirectorykey($id);
		$parent_dir_id = $res[0]["parent_dir_id"];
		$key = base64_decode($res[0]["dir_key"]);
		if (count($_FILES["fileToUpload1"]["name"]) > 0) {
			//Loop through each file
			for ($i = 0; $i < count($_FILES["fileToUpload1"]["name"]); $i++) {
				$RandomNumber = rand(1000, 100000000);
				$data = [
					"user_id" => $_SESSION["userId"],
					"cu_id" => $cu_id,
					"directory_name" => $res[0]["name"],
					"cloud_directory_id" => $id,
					"parent_dir_id" => $parent_dir_id,
					"added_by" => $_SESSION["name"],
					"dir_key" => base64_encode($key),
					"file_extension" => pathinfo(
						$_FILES["fileToUpload1"]["name"][$i],
						PATHINFO_EXTENSION
					),
					"file_size" => $_FILES["fileToUpload1"]["size"][$i],
					"file_name" => basename(
						$_FILES["fileToUpload1"]["name"][$i]
					),
					"random_numer" => $RandomNumber,
					"file_tmp_name" => $_FILES["fileToUpload1"]["tmp_name"][$i],
					"bucket_name" => S3_BUCKET,
				];

				$res = $this->cloudmodel->cumultiupload($data);
			}
		}

		if ($_SESSION["role"] == 2) {
			redirect("/documents");
		} else {
			redirect("/edit-credit-union/$cu_id");
		}
	}

	/* Download Single File in Table and cloud, Start */
	public function download()
	{
		$data = [
			"user_id" => $_SESSION["userId"],
			//"loan_id" => $loan_id,
			"directory_name" => unserialize(base64_decode($_GET["id"])),
			"file_name" => unserialize(base64_decode($_GET["key"])),
		];
		$res = $this->cloudmodel->download($data);
		echo json_encode($res);
	}

	/* Delete File from Table and cloud, Start */
	public function delete()
	{
		$output = explode("/", $this->input->post("key"));
		$data = [
			"user_id" => $_SESSION["userId"],
			"loan_id" => $output[0],
			"key" => $this->input->post("key"),
			"id" => $this->input->post("id"),
			"file_name" => $this->input->post("name"),
		];
		$res = $this->cloudmodel->delete($data);
		echo json_encode($res);
	}

	/* CU Delete File from Table and cloud, Start */
	public function cudelete()
	{
		$output = explode("/", $this->input->post("key"));
		$data = [
			"user_id" => $_SESSION["userId"],
			"cu_id" => $output[0],
			"key" => $this->input->post("key"),
			"id" => $this->input->post("id"),
			"file_name" => $this->input->post("name"),
		];
		$res = $this->cloudmodel->cudelete($data);
		echo json_encode($res);
	}

	/* Delete Seleted Folder & Files from Table and cloud, Start */
	public function seleteddelete()
	{
		$id = $this->input->post("id");
		$res = $this->cloudmodel->getdirectorykey($id);
		$loan_id = $res[0]["loan_id"];
		$key = base64_decode($res[0]["dir_key"]);
		$data = [
			"user_id" => $_SESSION["userId"],
			"loan_id" => $loan_id,
			"key" => base64_encode($key),
			"cloud_directory_id" => $id,
		];
		$res = $this->cloudmodel->seletedDelete($data);
		echo json_encode($res);
	}

	/* CU Delete Folders & Files from Table and cloud, Start */
	public function cuseleteddelete()
	{
		$id = $this->input->post("id");
		$res = $this->cloudmodel->cugetdirectorykey($id);
		$cu_id = $res[0]["cu_id"];
		$key = base64_decode($res[0]["dir_key"]);
		$data = [
			"user_id" => $_SESSION["userId"],
			"cu_id" => $cu_id,
			"key" => base64_encode($key),
			"cloud_directory_id" => $id,
		];
		$res = $this->cloudmodel->cuseletedDelete($data);
		echo json_encode($res);
	}

	/* download folder from Table and cloud, Start */
	public function downloadFolder()
	{
		$data = [
			"user_id" => $_SESSION["userId"],
			"loan_id" => $this->input->post("loan_id"),
			"key" => $this->input->post("key"),
			"id" => $this->input->post("id"),
		];
		$res = $this->cloudmodel->downloadFolder($data);
		echo json_encode($res);
	}
}
