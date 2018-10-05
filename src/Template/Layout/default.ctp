<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License 
 */
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig; 
$curentController = $this->request->params['controller'];
$cakeDescription = 'Inventory Management Admin';
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $cakeDescription ?>:
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <?php //echo  $this->Html->css('base.css') ?>
    <?php //echo  $this->Html->css('cake.css') ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>

	<?php
  //  echo $siteBaseURL;
		$controller = array(
								'mobile_repairs' => 'mobile_repairs',
								'mobile_unlocks' => 'mobile_unlocks',
								'kiosk_product_sales' => 'kiosk_product_sales',
								'mobile_re_sales' => 'mobile_re_sales',
							);
		$action = array(
								'edit' => 'edit',//for mobile repair and unlock
								'search' => 'search',//for kiosk product sales
								'search_product' => 'search_product',//for mobile repair
								'new_order' => 'new_order',//for kiosk product sales
								'new_sale' => 'new_sale',//for kiosk product sales admin sale
								'search_new_sale' => 'search_new_sale',//for kiosk product sales admin sale
								'add' => 'add',//for mobile resales
								'add_unlock_payment' => 'add_unlock_payment',
								'add#' => 'add#',
						);
		//echo $this->Html->meta('icon');
		echo $this->Html->css('cake.generic');
		echo $this->Html->css('jquery-ui.css');
         if($curentController == 'Acl'){
			$this->Html->css('cake.css');
			echo  $this->Html->css('base.css');
			
		 }
         $path = dirname(__FILE__); 
        $isboloRam = strpos($path,"mbwaheguru");
        if($isboloRam != false){
             echo $this->Html->css('menu_mb.css');
        }else{
               echo $this->Html->css('menu.css');
        }
		//echo $this->Html->css('menu.css');
		echo $this->Html->css('blitzer-jquery-ui.css'); //added on 1st Oct, 2016
		echo '<link rel="stylesheet" type="text/css" href="https://'.ADMIN_DOMAIN.'/css/colorbox.css" />';
		//echo $this->fetch('meta');
		//echo $this->fetch('css');
		//echo $this->fetch('script');
		echo $this->Html->script('jquery');
		echo $this->Html->script('handlebars-v3.0.3.js');
		echo $this->Html->script('typeahead.bundle.js');
		echo $this->Html->script('jquery-ui.js');
		echo '<script type="text/javascript" src="https://'.ADMIN_DOMAIN.'/js/jquery.colorbox.js"></script>';
		echo $this->Html->script('jquery.blockUI');
		echo $this->Html->script('ckeditor/ckeditor.js');
		echo $this->Html->script('menu-script.js');
		echo $this->Html->script('jquery.confirm.min.js');
		echo $this->Html->script('jquery.easy-confirm-dialog.js'); //added on 1st Oct, 2016
		//echo $this->Html->script('jquery.printElement');
		$redUrl = $siteBaseURL."users/login";
	?>
	<style>
		#cssmenu ul li, #cssmenu ul li{
			z-index: 1000;
		}
	</style>
	 <script src="https://js.pusher.com/4.0/pusher.min.js"></script>
  
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
	<script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
</head>
<body>
    <?php 
        //$this->request->session()->read('Auth.User.id');
        
    ?>
    <div id="container">
		<div id="header">
			<h1><?php   if($curentController == 'Acl'){
			  
		}else{
			  echo $this->Html->link($cakeDescription, $redUrl);}?></h1>
		</div>
		<div id="content" style="min-height: 800px;">
		<?php
       
           $domain_name = $_SERVER['HTTP_HOST']; 
			echo $this->element('admin_navigation');
			if ($this->request->session()->read('Auth.User.id')):
			$userType = '';
			if($this->request->session()->read('Auth.User.group_id') == KIOSK_USERS){ //KIOSK_USERS
				$userType = $this->request->session()->read('Auth.User.user_type'); //AuthComponent::user('user_type');
			}
		?>
			<div style='text-align: right'>			
				Logged in as <?= $this->request->session()->read('Auth.User.username'); ?>
				<?php
					$userType = $this->request->session()->read('Auth.User.user_type'); //AuthComponent::user('user_type');
					if(!empty($userType)){
						echo " (<strong>".ucfirst($userType)."</strong>) ";
					}
					echo "|";
					echo $this->Html->link(__('Logout'), array('controller' => 'users', 'action' => 'logout','plugin' => null));
					echo "<br/>";
					echo $this->Html->link(__('Change Password'), array('controller' => 'users', 'action' => 'change_pwd','plugin' => null));
					echo "|";
					echo $this->Html->link(__('Dashboard'), array('controller' => 'home', 'action' => 'dashboard','plugin' => null));
					echo "|";
					$confirmArr = array('confirm' => 'You can not login today after day off. Are you sure you want to day off? ');
					echo $this->Html->link(__('Dayoff' ), array( 'controller' => 'user_attendances',  'action' => 'Dayoff','plugin' => null) , $confirmArr);
					echo "</br>";
					if(!empty($target)){
						echo "<b> Today Target = ".$target."</b>";
					}
				?>
			</div>
		<?php
			endif;
			echo $this->Flash->render('auth');
			 echo $this->Flash->render();
			 //echo $this->Flash->render('email');
			echo $this->fetch('content');
		?>
		</div>
		<div id="footer">
			Website : <?php echo $this->Html->link(
					'www.'.ADMIN_DOMAIN,
					ADMIN_DOMAIN,
					array('target' => '_blank', 'escape' => false, 'id' => 'Proavid Powered')
				);
			?>
		</div>
	</div>
    <!--<nav class="top-bar expanded" data-topbar role="navigation">
        <ul class="title-area large-3 medium-4 columns">
            <li class="name">
                <h1><a href=""><?= $this->fetch('title') ?></a></h1>
            </li>
        </ul>
        <div class="top-bar-section">
            <ul class="right">
                <li><a target="_blank" href="https://book.cakephp.org/3.0/">Documentation</a></li>
                <li><a target="_blank" href="https://api.cakephp.org/3.0/">API</a></li>
            </ul>
        </div>
    </nav>
    <?= $this->Flash->render() ?>
    <div class="container clearfix">
        <?= $this->fetch('content') ?>
    </div>-->
    <footer>
    </footer>
  <?php
  $pusher_credentials = Configure::read('pusher_credentials');
  $pusher_key = $pusher_credentials['key'];
            ?>
			<input type="hidden" id="pusher_key" value="<?php echo $pusher_key; ?>" />
    
    <style>#busy-indicator { display:none; } </style>
	<?php
		echo $this->Html->image('indicator.gif',array('id' => 'busy-indicator'));
		if (class_exists('JsHelper') && method_exists($this->Js, 'writeBuffer')) {
			echo $this->Js->writeBuffer($options = array('inline' => true,'safe' => true));
		}
	?>
<script>
	 $(document).ready(function() {
		$("#printSelected").click(function() {
			$('#ProductPlacedOrderForm').hide();
			$('#heighlighted_block').hide();
			$('#cancel_item_1').hide();
			$('#Dispatch').hide();
		    printElem({
				printMode:'popup',
				leaveOpen:true,
				/*overrideElementCSS:[
							'print.css',
							{ href:'https://<?php echo ADMIN_DOMAIN;?>/css/print.css',media:'print'}
						]*/
				overrideElementCSS:['https://<?php echo ADMIN_DOMAIN;?>/css/print.css']
				});
		});
	 });
	function printElem(options){
		$('#printDiv').printElement(options);
	}
	
	//Start: code added by yamini
	<?php
	$currentAction = $this->request->action;
	$curentController = $this->request->params['controller'];
	$currentUrl = $curentController.'/'.$currentAction;
	
	$exceptScreens = array(
					   'Products/importKioskProducts',
					   'Products/exportWarehouseProducts',
					   'Products/exportKioskProducts',
					   'Products/add',
                       'Products/edit',
                       'Products/cloneProduct',
                       'Categories/edit',
                       'Categories/add',
					   'Groups/add',
					   'KioskProductSales/newSale',
                       'Posts/add',
					   'Customers/add',
					   'MobilePurchases/add',
					   'Brands/add',
					   'Categories/add',
					   'MobileReSales/add',
					   'MobileReSales/edit',
					   'MobileBlkReSales/add',
					   'MobileBlkReSales/edit',
					   'Kiosks/add',
					   'WarehouseStocks/index'
					   );
	
	if(in_array($currentUrl,$exceptScreens)){
		//do nothing
	}else{
	?>
	$('input[name = "submit"]').click(function(){
		$.blockUI({ message: 'Just a moment...' });
	});
	
	$('input[name = "update_quantity"]').click(function(){
		$.blockUI({ message: 'Just a moment...' });
	});
	
	$('button[name = "submit"]').click(function(){
		$.blockUI({ message: 'Just a moment...' });
	});
	
	$('input[name = "Dispatch"]').click(function(){
		$.blockUI({ message: 'Just a moment...' });
	});
	
	$('input[name = "save_listing"]').click(function(){
		$.blockUI({ message: 'Just a moment...' });
	});
	
	<?php } ?>
	$('li a').click(function(){
		$.blockUI({ message: 'Just a moment...' });
	});
   
	//End: code added by yamini
	
</script>
	 
     <script src="https://js.pusher.com/4.1/pusher.min.js"></script>
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
	<script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
  
  <script>

    Pusher.logToConsole = true;
               var pusher_key = $("#pusher_key").val();
                 var pusher = new Pusher(pusher_key, {
                    cluster: 'eu',
                    encrypted: true
                  });
            
            
   
     var channel = pusher.subscribe('mychannel');
      channel.bind('myevent', function(data) {
        window.console.log(data.message);
        toastr.options = {
            "closeButton": true,
            "debug": true,
            "newestOnTop": false,
            "progressBar": false,
            "positionClass": "toast-bottom-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "1000000",
            "hideDuration": "1",
            "timeOut": "50000000",
            "extendedTimeOut": "1000000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }
        toastr.info(data.message)
        
    });
    
    
    var channel1 = pusher.subscribe('mychannel');
    <?php
       $kiosk_id = $this->request->Session()->read('kiosk_id');
     ?>
    channel1.bind('<?=$kiosk_id."_kiosk_email";?>', function(data) {
     
     window.console.log(data.message);
     toastr.options = {
         "closeButton": true,
         "debug": true,
         "newestOnTop": false,
         "progressBar": false,
         "positionClass": "toast-bottom-right",
         "preventDuplicates": false,
         "onclick": null,
         "showDuration": "3000000",
         "hideDuration": "1",
         "timeOut": "50000000",
         "extendedTimeOut": "1000000",
         "showEasing": "swing",
         "hideEasing": "linear",
         "showMethod": "fadeIn",
         "hideMethod": "fadeOut"
     }
     toastr.info(data.message)
    });
 
  var channel2 = pusher.subscribe('mychannel');
    <?php
      $user_id =  $this->request->Session()->read('Auth.User.id');
     ?>
     channel2.bind('<?=$user_id."_user_email";?>', function(data) {
     window.console.log(data.message);
     toastr.options = {
         "closeButton": true,
         "debug": true,
         "newestOnTop": false,
         "progressBar": false,
         "positionClass": "toast-bottom-right",
         "preventDuplicates": false,
         "onclick": null,
         "showDuration": "3000000",
         "hideDuration": "1",
         "timeOut": "50000000",
         "extendedTimeOut": "1000000",
         "showEasing": "swing",
         "hideEasing": "linear",
         "showMethod": "fadeIn",
         "hideMethod": "fadeOut"
     }
     toastr.info(data.message)
    });
 var channel3 = pusher.subscribe('mychannel');
  <?php $auth = $this->request->session()->read('Auth.User.group_id'); ?>
   channel3.bind('<?=$auth."_auth_group";?>', function(data) {
     window.console.log(data.message);
     toastr.options = {
         "closeButton": true,
         "debug": true,
         "newestOnTop": false,
         "progressBar": false,
         "positionClass": "toast-bottom-right",
         "preventDuplicates": false,
         "onclick": null,
         "showDuration": "3000000",
         "hideDuration": "1",
         "timeOut": "50000000",
         "extendedTimeOut": "1000000",
         "showEasing": "swing",
         "hideEasing": "linear",
         "showMethod": "fadeIn",
         "hideMethod": "fadeOut"
     }
     toastr.info(data.message)
    });
     
   
  </script>
 
 
</body>
</html>
