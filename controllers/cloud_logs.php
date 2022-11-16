<div class="main-body">
                                <div class="page-wrapper hostbooks-cloud">
                                    
                                    <!-- Start, Card -->
                                    <div class="card">
                                    <div class="card-header">
                                                	<div class="row">
                                                    	<div class="col-md-3">
                                                             <h5 style="text-transform: capitalize !important;">Cloud Logs</h5>
                                                        <span>
                                                        <ul class="breadcrumb-title">
                                                            <li class="breadcrumb-item">
                                                                <a href="<?php echo base_url();?>index.php/user/dashboard">
                                                                    <i class="icofont icofont-home"></i>
                                                                </a>
                                                            </li>
                                                            <li class="breadcrumb-item"><a href="<?php echo base_url();?>index.php/cloud/mycloud">My HostBooks Cloud</a>
                                                            </li>
    
                                                        </ul>
                                                        </span>
                                                        </div>
                                                        
                                                        
                                                 
                                                    </div>
                                                   
                                                </div>

<!-- End, Card Header -->


<div class="card-block">
<div class="dt-responsive table-responsive">
     <table id="simpletable" class="table table-striped table-bordered nowrap compact dataTable" role="grid">
       <thead>
                                                                <tr>
                                                                  <th>#</th>
                                                                   <!-- <th>User ID</th> -->
                                                                    <th width="35%">Description</th>
                                                                    <th width="15%">IP Address</th>
                                                                    <th width="20%">IP ISP</th>
                                                                    <th width="20%">Created on</th>
                                                                    <th width="10%">Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                          
     
                                                                <?php $i=1;
                                                                foreach($cloud_logs as $key) { ?>
                                                                <tr>
                                                                    <td><?=$i++?></td>
                                                                    <!-- <td><?=$key['user_id']?></td> -->
                                                                    <td><?= unserialize(base64_decode($key['description']))?></td>
                                                                    <td><?=$key['ip_address']?></td>
                                                                    <td><?=$key['ip_isp']?></td>                                                                  
                                                                    <td><?=$key['created_on']?></td>
                                                                    <td><?=ucwords($key['status'])?></td>
                                                                </tr>
                                                                <?php } ?>
                                                                
                                                           
                                                                
                                                                
                                                            </tbody>
                                                        </table>
	
	
                                                    </div>











                                              
</div>







                                
          
</div>
</div>
</div>

	



