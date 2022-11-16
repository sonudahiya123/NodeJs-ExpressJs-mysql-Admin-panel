<div class="main-body">
                                <div class="page-wrapper hostbooks-cloud">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h5>My hostbooks cloud</h5>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="cloud-progressbar">
                                                        <div class="row">
                                                            <div class="col-sm-4 p-0 text-right">
                                                                <label>Cloud Disk (10 GB)</label>
                                                            </div>
                                                            <div class="col-sm-5 progress-bar-page" >
                                                                <div class="progress">
                                                                <?php
                                                                
                                                                    $GB = 10737418240; 
                                                                    $totalUsedGB = (($totalFileSizeUsed/$GB)*100);
                                                                    $totalUsedGB = number_format($totalUsedGB,2);
																	
																	
                                                                ?>
                                                                    <div class="progress-bar progress-bar-striped progress-bar-primary" role="progressbar" style="width: <?=$totalUsedGB?>%" aria-valuenow="<?=$totalUsedGB?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-3 p-0">
                                                                <label>Used: <?=$get_dir_total_size?></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-header" style="border-top: 1px solid #eee;">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <!-- 
                                                    <div class="left-icon-control">
                                                    <input type="text" class="form-control  search-text" placeholder="Search by file/folder name" style="padding-left: 35px !important;">
                                                        <div class="form-icon">
                                                            <i class="icofont icofont-search"></i>
                                                        </div>
                                                    </div>
                                                    -->
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="cloud-action-bar">
                                                        <a href="<?php echo base_url();?>index.php/cloud/cloudlogs/" class="btn btn-info">Cloud Logs</a>&nbsp;&nbsp;
                                                        <button type="button" class="btn btn-info add-new-directory f-right">Create Folder</button>
                                                    
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="page-body">
                                        <div class="row">
                                            

<?php 
$i=0;
$folderSize = 0;

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
            <a href="<?php echo base_url();?>index.php/cloud/folderlist/?id=<?=base64_encode(serialize($dirname['name']))?>&key=<?=base64_encode(serialize($dirname['id']))?>">
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
                                </div>
                                <div id="styleSelector"></div>
                            </div>





 <!-- Popup -->
 <div class="modal fade " id="add-new-directory" tabindex="-1" role="dialog">
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
                        <form action="<?php echo base_url();?>index.php/cloud/adddirectory" name="adddirectorypopup" id="adddirectorypopup" method="post" class="j-pro" id="j-pro" novalidate style="border:none;" autocomplete="off">
                                                        <div class="row">
                                                            <div class="col-sm-12">
                                                                <div class="p-20 z-depth-top-0 waves-effect">
                                                                    <div class="form-group">
                                                                        <label class="j-label">Folder Name*</label>
                                                                         <div class="j-input">
                                                                            <input type="text" class="form-control" id="directory_name" name="directory_name">
                                                                            <p id="error_directory_name" class="text-danger error"></p>
                                                                        </div>
                                                                        </div>
<!--
<div class="form-group">
                                                                <label class="j-label">Folder Size</label>
                                                                <div class="j-select">
                                                                    <select name="folder_size" id="folder_size" class="form-control">
                                                <option value="1">1 GB</option>
                                                <option value="2">2 GB</option>
                                                <option value="3">3 GB</option>
                                                <option value="4">4 GB</option>
                                                <option value="5">5 GB</option>
                                            </select>
                                                                </div>
                                                                    </div>
                                                                    -->
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



