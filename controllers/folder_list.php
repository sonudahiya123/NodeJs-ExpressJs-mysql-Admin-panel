<style>
.jFiler-theme-default .jFiler-input.focused {

    outline: none;
    -webkit-box-shadow: 0 0 7px #f00 !important;
    -moz-box-shadow: 0 0 7px #f00 !important;
    box-shadow: 0 0 7px #f00 !important;
}
</style>
<?php
//echo phpinfo();


//print_r($data['get_folder_total_size']);
function getfilesize($size)
{
    if ($size >= 1073741824) {
        return number_format($size / 1073741824, 2) . ' GB';
    } elseif ($size >= 1048576) {
        return number_format($size / 1048576, 2) . ' MB';
    } elseif ($size >= 1024) {
        return number_format($size / 1024, 2) . ' KB';
    } elseif ($size > 1) {
        return  $size . ' bytes';
    } elseif ($size == 1) {
        return  $size . ' byte';
    } else {
        return '0 bytes';
    }
}
										

?>
<style>
	.cloud-action-bar-list li {
		padding: 0 6px !important;
	}
</style>
<div class="main-body">
                                <div class="page-wrapper hostbooks-cloud">
                                    
                                    <!-- Start, Card -->
                                    <div class="card">
                                    <div class="card-header">
                                                	<div class="row">
                                                    	<div class="col-md-7">
                                                             <h5 style="text-transform: capitalize !important;"><?=unserialize(base64_decode($_GET['id']))?></h5>
                                                        <span>
                                                    
                                                        
                                                        <ul class="breadcrumb-title">
                                                            <li class="breadcrumb-item">
                                                                <a href="<?php echo base_url();?>index.php/user/dashboard">
                                                                    <i class="icofont icofont-home"></i>
                                                                </a>
                                                            </li>
                                                            <li class="breadcrumb-item"><a href="<?php echo base_url();?>index.php/cloud/mycloud">My HostBooks Cloud</a>
                                                            </li>
                                                            <li class="breadcrumb-item"><?=unserialize(base64_decode($_GET['id']))?></li>
    
                                                        </ul>
                                             
                                                        </span>
                                                        </div>
                                                        
                                                        
                                                    
                                                        <div class="col-md-5 right">
                                                        	<div class="cloud-action-bar-list">
                                                         
                                                         
                                                               <?php if(!empty($getfilelist)){ ?>
                  <!--  <a href="javascript:void(0);" class="btn btn-danger btn-outline-danger" onclick="myFilesAllDelete('<?=unserialize(base64_decode($_GET['id']))?>', '<?=unserialize(base64_decode($_GET['key']))?>')"><i class="ti ti-trash"></i>&nbsp;Delete All</a>-->
                    &nbsp;&nbsp;
                     <button class="btn btn-danger btn-outline-danger" id="del_all" name="del_all" value="<?=$_GET['key']?>">Delete</button>
                     &nbsp;&nbsp;
                    <?php } ?>                                  
                    <button class="btn btn-inverse btn-outline-inverse upload-btn">Upload</button>                          
                    &nbsp;&nbsp;
                    <a href="<?php echo base_url();?>index.php/cloud/cloudlogs/?token=<?=$_GET['key']?>" class="btn btn-info btn-outline-info">Logs</a>
                    &nbsp;&nbsp;
                    <button type="button" class="btn btn-info btn-outline-info add-new-directory f-right">Create Folder</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                   
                                                </div>

<!-- End, Card Header -->

<div class="card-block">
<div class="page-body">
<div class="row">
                                        
<?php 
$i=0;
$folderSize = 0;

foreach($total_dir_list as $dirname){

$i++; 
$classFolder = 'client-files-block';
    
if($i%4==0){ 
    echo '<div class="clearfix"></div>'; 
    $classFolder = 'images-block';
}
else if($i%2==0){
    $classFolder = 'new-folder-block';
}
else if($i%3==0){
    $classFolder = 'my-documents-block';
}
?>

<div class="col-md-3">
    <div class="card counter-card-1 <?=$classFolder?>">
        <div class="card-block">
            <a href="<?php echo base_url();?>index.php/cloud/subfolderlist/?id=<?=base64_encode(serialize($dirname['name']))?>&key=<?=base64_encode(serialize($dirname['id']))?>">
            <div>
                <h4 style="text-transform: capitalize !important;"><?=ucwords($dirname['name'])?></h4>
                <p><span class="icon"><i class="ti-image"></i></span> <span class="count f-right">
                <?=getfilesize($dirname['file_size'])?></span></p>
            </div>
            </a>                                                        
        </div>
    </div>
</div>
<? } ?>
</div>
</div>



<div class="dt-responsive table-responsive ">
     <table id="simpletable" class="table table-striped table-bordered nowrap compact dataTable" role="grid">
       <thead>
                                                                <tr>
                                                                  <th>#</th>
																  
																<th width="15%"><input id="selecctall" type="checkbox">&nbsp;Check All</th></th>
                                                                    <th width="30%">File Name</th>
                                                                    <th width="15%">File Type</th>
                                                                    <th width="10%">Size</th>
                                                                    <th width="20%">Created on</th>
                                                                    <th width="10%">Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                          
                                                            <?php

														
														$size = 0;
														$i=1;
														foreach ($getfilelist as $object) {
														if($object['file_size']!=0)		{
                                                        $size = $size+$object['file_size'];
                                                        ?>
                                                                <tr>
                                                                    <td><?=$i++?></td>
																	<td>
                                                                        <input name="checkbox[]" class="checkbox1" type="checkbox" id="checkbox[]" value="<?=$object['file_name']?>">
                                                                        <?php unserialize(base64_decode($_GET['key']))."|".unserialize(base64_decode($_GET['id']));  ?>
                                                                    </td>
                                                                    <td><?php
                                                                    if(!empty($object['random_numer']))
                                                                    {
                                                                        $fileName = explode($object['random_numer'], $object['file_name']);
                                                                        echo $fileName[1];
                                                                    }
                                                                    else{
                                                                       echo $object['file_name']; 
                                                                    }
                                                                    ?></td>
                                                                    <td><?=$object['file_extension'];?></td>
                                                                    <td><?=getfilesize($object['file_size'])?></td>
                                                                    <td><?=$object['created_on']?></td>
                                                                    <td>  

                                                                 
                
<?php

foreach($getfileurl as $key)
{
    //print_r($key);
    if(in_array($object['file_name'],$key['name'])){  ?>
    <a href="javascript:void(0);" class="btn btn-danger btn-mini FileDelete" id="FileDelete" onclick="myFileDelete('<?=$key['key']?>', '<?=unserialize(base64_decode($_GET['key']))?>', '<?=$object['file_name']?>')">
    <i class="ti ti-trash"></i>
    </a>&nbsp;&nbsp;
    <a href="<?=$key['link']?>" class="btn btn-primary btn-mini"><i class="ti ti-download"></i></a>
<?php  } } ?>
</td>         
</tr>                                                           
<?php } } ?>
                                                                
</tbody>
</table>

<strong> Total Size Used: <?php echo getfilesize($size); ?></strong> 
</div>                                         
</div>       
</div>
</div>
</div>

 <!-- Popup -->
 <!-- File upload popup -->
<div class="modal fade business-modal" id="upload-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
             
                <h5 class="modal-title text-center">Upload File</h5>
                <button type="button" class="close" data-dismiss="modal" style="cursor: pointor; margin-top:-5px; color:#666;">&times;</button>
            </div>
            <form action="<?php echo base_url();?>index.php/cloud/multiupload" method="POST" name="addfile" id="add_file" enctype="multipart/form-data">
            <input type="hidden" id="directory_id" value="<?=$_GET['key']?>" name="directory_id">
	            <div class="modal-body">
	                <div class="">



                    <div class="card-block">
                        <input type="file" name="fileToUpload1[]" multiple="multiple" id="filer_input" required/>
                    <div id="preview">
	                       	  <div id="error_image_upload" class="bg-danger"></div>
	                       	  <div id="success_image_upload" class="bg-success"></div>	                          
	                       </div>
                    </div>
            
	                </div>
	                
	            </div>
	            <div class="modal-footer text-center">
                <input type="submit" class="btn btn-primary" id="addfile" value="Upload File" name="submit">
	   
	            </div>
            </form>
        </div>
    </div>
</div>
   <!-- File upload popup -->  
   
   
   <!-- Multiple File upload popup -->
<div class="modal fade business-modal" id="multiple-upload-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
             
                <h5 class="modal-title text-center">Multiple Upload File</h5>
                <button type="button" class="close" data-dismiss="modal" style="cursor: pointor; margin-top:-5px; color:#666;">&times;</button>
            </div>
            <form action="#" method="POST" name="add_multiple_file" id="add_multiple_file" enctype="multipart/form-data">
            <input type="text" id="directory_name" value="<?=unserialize(base64_decode($_GET['id']))?>" name="directory_name">
            <input type="text" id="directory_id" value="<?=unserialize(base64_decode($_GET['key']))?>" name="directory_id">
	            <div class="modal-body">
	                <div class="row">
	                    <div class="col-sm-12">
	                        <div class="upload-btn-wrapper2">
	                            <input type="file" name="fileToUpload1[]" id="filer_input1" multiple="multiple">
							
	                        </div>
	                    </div>
	                    
	                </div>
					<div id="preview">
						  <div id="error_image_upload" class="bg-danger"></div>
						  <div id="success_image_upload" class="bg-success"></div>	                          
					   </div>
	                
	            </div>
	            <div class="modal-footer text-center">
                <input type="submit" class="btn btn-primary" id="addfile" value="Upload File" name="submit">
	   
	            </div>
            </form>
        </div>
    </div>
</div>
   <!-- File upload popup -->  
   
   
   

   <!-- Popup -->
 <div class="modal fade" id="add-new-directory" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content new-directory">
                <div class="modal-header">
                    <h6 class="modal-title">Add New Folder</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="">
                        <form action="<?php echo base_url();?>index.php/cloud/addsubdirectory" name="addsubdirectorypopup" id="addsubdirectorypopup" method="post" class="j-pro" id="j-pro" style="border:none;" autocomplete="off">
                        <input type="hidden" id="directory_id" name="directory_id" value="<?=$_GET['key']?>">
                        <div class="row">
                                                            <div class="col-sm-12">
                                                                <div class="p-20 z-depth-top-0 waves-effect">
                                                                    <div class="form-group">
                                                                        <label class="j-label">Folder Name*</label>
                                                                         <div class="j-input">
                                                                            <input type="text" class="form-control" id="sub_directory_name" name="sub_directory_name">
                                                                            <p id="error_directory_name" class="text-danger error"></p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        
                                                        </div>
                                                        <div class="j-response"></div>
                                                        <div class="p-10 f-right">
                                                            <input type="submit" id="directory_btn" class="btn btn-primary" value="Create Folder"/>
                                                        </div>
                                                    </form>  
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Popup end -->
	



