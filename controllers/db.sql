CREATE TABLE `cloud_directory` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_account_no` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(256) NOT NULL,
  `dir_key` varchar(300) DEFAULT NULL,
  `dir_size` varchar(30) DEFAULT NULL,
  `type` varchar(20) NOT NULL,
  `bucket_name` varchar(256) NOT NULL,
  `parent_dir_name` varchar(100) NOT NULL DEFAULT 'Default',
  `parent_dir_id` int(11) NOT NULL DEFAULT 0,
  `ip_address` varchar(30) NOT NULL,
  `ip_isp` varchar(256) NOT NULL,
  `status` int(1) NOT NULL DEFAULT 1,
  `created_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



CREATE TABLE `cloud_file` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `cloud_directory_id` int(11) DEFAULT NULL,
  `dir_key` varchar(300) DEFAULT NULL,
  `file_name` varchar(256) DEFAULT NULL,
  `file_extension` varchar(256) DEFAULT NULL,
  `file_size` varchar(20) DEFAULT '0',
  `file_path` varchar(20) DEFAULT NULL,
  `status` int(1) DEFAULT 1,
  `is_deleted` int(1) DEFAULT 0,
  `created_on` timestamp NULL DEFAULT current_timestamp(),
  `modified_on` timestamp NULL DEFAULT NULL,
  `parent_dir_id` int(11) DEFAULT 0,
  `random_numer` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `cloud_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_account_no` varchar(100) NOT NULL,
  `cloud_directory_id` int(11) NOT NULL,
  `module_name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `bucket_name` varchar(256) NOT NULL,
  `ip_address` varchar(30) NOT NULL,
  `ip_isp` varchar(256) NOT NULL,
  `status` varchar(10) NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_on` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
