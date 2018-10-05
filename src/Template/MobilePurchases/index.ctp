<?php
use Cake\I18n\Time;
?>
<div class="mobilePurchases index">
	<?php $search_kw = "";
	 $kiosk_id = $this->request->session()->read('kiosk_id');
	if(!empty($this->request->query)&&array_key_exists("search_kw",$this->request->query))$search_kw = $this->request->query['search_kw'];
	if(array_key_exists('id',$this->request->query)){
		$search_id = $this->request->query['id'];
	}else{
		$search_id = "";
	}
	?>
	<form action='<?php echo $this->request->webroot; ?>mobilePurchases/search' method = 'get'>
		<fieldset>
			<legend>Search</legend>
			<div>
				<input type = "text" name = "id" placeholder = "ID" style = "width:150px  " autofocus value="<?=$search_id?>" />
				<input type = "text" name = "search_kw" placeholder = "Model, IMEI, Brand" style = "width:430px  " autofocus value='<?php echo $search_kw; ?>'/>
				<input type = "submit" value = "Search Mobile Phone" name = "submit"/>
			</div>
		</fieldset>	
	</form>
	<h2><?php echo __('Mobile Stock'); ?></h2>
	<h3 style="color: red;">Red Color ID Is For Wholesale Bulk Mobiles</h3>
	<h3 style="color: blue;">Blue Color ID Is For Kiosk Bulk Mobiles</h3>
	<?php echo $this->Form->create('TransferMobile', array('url' => array('controller' => 'mobile_purchases', 'action' => 'index')));?>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th>&nbsp;<?php echo $this->Paginator->sort('id'); ?></th>
			<?php
			if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){
			?>
			<th><?php echo $this->Paginator->sort('kiosk_id'); ?></th>
			<?php
			}
			?>
			<th><?php echo $this->Paginator->sort('brand_id'); ?></th>
			<th><?php echo $this->Paginator->sort('mobile_model_id'); ?></th>
			<th><?php echo $this->Paginator->sort('color'); ?></th>
			<th><?php echo $this->Paginator->sort('imei', 'IMEI'); ?></th>			
			<th><?php echo $this->Paginator->sort('selling_price','Selling Price'); ?></th>
			
			<th><?php echo $this->Paginator->sort('grade'); ?></th>
			<th><?php echo $this->Paginator->sort('type'); ?></th>
			<th><?php echo $this->Paginator->sort('network_id'); ?></th>
			 		
			<th><?php echo $this->Paginator->sort('created','Purchased On'); ?></th>
			<th><?php echo $this->Paginator->sort('new_kiosk_id','Destination'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<tr style="display: none">
		<td colspan = '14'></td>
		<td><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', 0), array(), __('Are you sure you want to delete # %s?', 0));?></td>
	</tr>
	
	<?php  //pr($mobilePurchases);
	foreach ($mobilePurchases as $mobilePurchase):
		$purchaseId = $mobilePurchase->id;
		$newKioskId = $mobilePurchase['new_kiosk_id'] ;
		$currentStatus = $mobilePurchase->status ;
		$receiving_status = $mobilePurchase->receiving_status ;
		$editUrl = "/img/16_edit_page.png";
        
		if(array_key_exists($mobilePurchase->grade,$gradeType)){
			$mobGrade = $gradeType[$mobilePurchase->grade ];
		}else{
			$mobGrade = $mobilePurchase->grade ;
		}
		$cssStyle = $currentStatus == 2 ? 'background-color: yellow;' : '';
		$blkPurchased = false;
		if($mobilePurchase->purchase_status  == 1){
			$blkPurchased = true;
		}
		if($mobilePurchase->custom_grades  == 1 && $mobilePurchase->purchase_status  == 1){
			$msg = "You can not edit bulk purchased phone with custom grades from this screen!";
			
			if($mobilePurchase->purchased_by_kiosk != 10000 && $mobilePurchase->purchased_by_kiosk != "" && $mobilePurchase->purchased_by_kiosk != 0){
				$editLink = "<a href='#-1' title='$msg' alt='$msg' style='color:blue'>".$mobilePurchase->id ."</a>";
			}else{
				$editLink = "<a href='#-1' title='$msg' alt='$msg' style='color:red'>".$mobilePurchase->id ."</a>";	
			}
		}else{
			$editLink = $this->Html->link($mobilePurchase->id , array('action' => 'edit', $mobilePurchase->id),array('escapeTitle' => false, 'title' => 'Edit', 'alt' => 'Edit'));
		}
		
		$dispatched_on = $mobilePurchase['created'];
        if(!empty($dispatched_on)){
           $dispatched_on->i18nFormat(
                                                [\IntlDateFormatter::FULL, \IntlDateFormatter::FULL]
                                        );
			$dispatched_on_date =  $dispatched_on->i18nFormat('dd-MM-yyyy HH:mm:ss');
            $dispatched_on_date = date("d-m-y h:i a",strtotime($dispatched_on_date)); 
        }else{
            $dispatched_on_date = "--";
        }
		
	?>
	<tr <?= $cssStyle;?>>
		<td><?php echo $editLink ?>&nbsp;
		</td>
		<?php
			if ($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == MANAGERS){
			?>
		<td>
			<?php if($mobilePurchase->kiosk_id == 0){
                echo "Warehouse";
                }else{
                    
                       echo  $mobilePurchase->has('kiosk') ? $this->Html->link($kiosks[$mobilePurchase->kiosk->id], ['controller' => 'Kiosks', 'action' => 'view', $mobilePurchase->kiosk->id]) : ''  ;
                }   ?>
		</td>
		<?php
			}
			?>
		<td>
			<?php echo $this->Html->link($mobilePurchase->brand['brand'], array('controller' => 'brands', 'action' => 'view', $mobilePurchase->brand['id'])); ?>
		</td>
		<td><?php if(array_key_exists($mobilePurchase->mobile_model_id,$mobileModels)){
			echo h($mobileModels[$mobilePurchase->mobile_model_id]);
		}
			?>&nbsp;</td>
		<td><?php   if(array_key_exists($mobilePurchase->color,$colorOptions)){
          echo   h($colorOptions[$mobilePurchase->color]);
          }?>&nbsp;</td>
		<td><?php echo $this->Html->link($mobilePurchase->imei,array('action'=>'mobile_transfer_logs',$mobilePurchase->imei),array('alt'=>'logs','title'=>'logs')); ?>&nbsp;</td> 
		<td><?php
		$selling_price = $mobilePurchase->selling_price ;
		if(!empty($selling_price) && $selling_price != 0){
			echo h($selling_price); 
		}else{
			if(array_key_exists($purchaseId,$salePrice)){
				$selling_price = $salePrice[$purchaseId];
			}
			echo h($selling_price); 
		}
		?>&nbsp;</td>
		
		
		<td><?php echo $mobGrade; ?>&nbsp;</td>
		<td><?php echo h($lockedUnlocked[$mobilePurchase->type]); ?>&nbsp;</td>
		<td><?php
		if(!empty( $mobilePurchase->network_id )){
            if(array_key_exists($mobilePurchase->network_id,$networks)){
                echo h($networks[$mobilePurchase->network_id]);
            }
		 }?>&nbsp;</td>
   	
		<td><?php echo  $dispatched_on_date ;?>&nbsp;</td>
		<td><?php if($mobilePurchase->status  == 2 || $mobilePurchase->receiving_status  == 1){
			if($mobilePurchase->new_kiosk_id){
				if(!array_key_exists($mobilePurchase->new_kiosk_id,$kiosks)){
					$kiosk_name = "<b style='background-color: yellow;'>Unassigned Kiosk (ID: {$mobilePurchase->new_kiosk_id} )</b>"; //$kiosks[$mobilePurchase->new_kiosk_id ];
				}else{
					$kiosk_name = $kiosks[$mobilePurchase->new_kiosk_id ];
				}
			}else{
				$kiosk_name = "";
			}
				
					$kioskName = ($mobilePurchase->new_kiosk_id ==0) ? "Warehouse" : $kiosk_name;
					if($mobilePurchase->status  == 2){
						$reservedBy = (array_key_exists($mobilePurchase->reserved_by ,$users)) ? 'Reserved by '.$users[$mobilePurchase->reserved_by ] : '--';
						$reserveDate = ($mobilePurchase->reserve_date  != '0000-00-00 00:00:00' && $mobilePurchase->reserve_date != '') ? 'on '. $mobilePurchase->reserve_date   : '--';
					}else{
						$reservedBy = (array_key_exists($mobilePurchase->transient_by ,$users)) ? 'Moved by '.$users[$mobilePurchase->transient_by ] : '--';
						$reserveDate = ($mobilePurchase->transient_date  != '0000-00-00 00:00:00' && $mobilePurchase->transient_date  != '') ? 'on '. $mobilePurchase->transient_date   : '--';
					}
					
					echo "<span title=\"$reservedBy $reserveDate\">".$kioskName."</span>";
				}else{
					echo '--';
				}?></td>
		<td class="actions">
			<?php
			if($currentStatus==2){
				if($kiosk_id == $mobilePurchase->kiosk_id ){
                    echo "<input type='hidden' name='TransferMobile[transfer_reserved][$purchaseId]' value = {$newKioskId}>";
                   echo  $this->Form->button(__('Transfer'));  
     
                      ?>&nbsp;
				<?php }else{
					echo "<strong>Reserved</strong>";
				}
			}elseif($currentStatus==3){?>
				<strong>Unlocking</strong>
			<?php }elseif($currentStatus==4){?>
				<strong>Repairing</strong>
			<?php }elseif($receiving_status==1){?>
				<strong>Transient</strong>
			<?php }else{
				if(($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER || $this->request->session()->read('Auth.User.group_id') == SALESMAN | $this->request->session()->read('Auth.User.group_id') == inventory_manager&&
				   ($mobilePurchase->kiosk_id == 10000 ||
				    $mobilePurchase->kiosk_id == 0)) ||
				   $this->request->session()->read('Auth.User.group_id') == KIOSK_USERS
				   ){
					if($blkPurchased){
						echo $this->Html->link(__('Sell'), array('controller'=>'mobile_blk_re_sales','action' => 'add', $mobilePurchase->id ));
					}else{
						echo $this->Html->link(__('Sell'), array('controller'=>'mobile_re_sales','action' => 'add', $mobilePurchase->id ));
					}
				}
			}
			?>
			<?php echo $this->Html->link(__('view'), array('controller'=>'mobile_purchases','action' => 'view', $mobilePurchase->id ));?>
			<?php if ($this->request->session()->read('Auth.User.group_id') == MANAGERS){
				if($currentStatus==0){
			?>
		 
			<?php }elseif($currentStatus==2){
				 
			}
			}?>
			<?php
				if($this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER ){
					$imei = $mobilePurchase->imei;
					if(array_key_exists($mobilePurchase->mobile_model_id,$mobileModels)){
							$mobile_model =  $mobileModels[$mobilePurchase->mobile_model_id];
					}else{
						$mobile_model = ""; 
					}
					
				 	echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $mobilePurchase->id ], ['confirm' => __("Are you sure you want to delete mobile with imei $imei, model: $mobile_model ?", $mobilePurchase->id)]); }?>
			
		</td>
		<td>
			<form target="_blank" method="post" action="/mobile-purchases/print_label">
						<input type="text" name="print_label_price" value="<?php echo $selling_price;?>" style="width: 29px;" />
						<input type="submit" name="print" value="Print Label" />
						<input type="hidden" name="id" value="<?php echo $mobilePurchase->id;?>" />
						<input type="hidden" name="selling_price_for_label" value="<?php echo $selling_price;?>" />
					</form>
		</td>
	</tr>
<?php endforeach;
	echo  $this->Form->end() ;  
?>
 
	</tbody>
	</table>
	  <div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New <br/>Mobile Purchase'), array('action' => 'add'),array('escape' => false)); ?></li>
		<?php if($this->request->session()->read('Auth.User.group_id') != KIOSK_USERS){?>
		<li><?php echo $this->Html->link(__('Mobile Stock In'), array('action' => 'bulk_mobile_purchase')); ?> </li>
		<?php } ?>
		 
		<li><?php echo $this->Html->link(__('View Mobile Sale'), array('controller' => 'mobile_re_sales', 'action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('Buy Mobile Phones'), array('controller' => 'mobile_purchases', 'action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('Mobile Stock/Sell'), array('controller' => 'mobile_purchases', 'action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('Global Mobile <br/> Search'), array('controller' => 'mobile_purchases', 'action' => 'global_search'),['escape'=>false]); ?></li>
	</ul>
</div>
<script>
	$(function() {
		$( document ).tooltip();
	});
</script>