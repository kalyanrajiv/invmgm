<?php
$jQueryURL = "http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
if(defined('URL_SCHEME')){
	$jQueryURL = URL_SCHEME."ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js";
}
?>
<script type="text/javascript" src="<?php echo $jQueryURL;?>"></script>
<?php echo $this->Html->script('jquery.printElement');?>
	<input type='button' id='printSelected' name='print' value='Print Receipt' style='width:200px;margin-left: 533px;' align='center' />
     <div id='printDiv' style="/*! margin-left: 492px; */margin-bottom: 0px !important;margin-top: 0px !important;/*! align-content: center; */position: fixed;top: 50%;left: 50%;transform: translate(-50%, -50%);font-family: georgia times serif;"> 
	<table style="width: 300px;border: 1px solid black;">
        <tr>
			<td><?php
			$imgUrl = "/img/".$setting_arr['logo_image'];
			echo $this->Html->image($imgUrl, array('fullBase' => true,'style'=>"height: 53px;width: 210px;"));
			//echo $setting_arr['logo_image'];?></td>
			<td style="vertical-align: middle;"><b style="font-size: 22px;"><?php echo $CURRENCY_TYPE.$print_label_price;?></b></td>
		</tr>
		<tr>
			<td colspan=2 style="text-align: center;"><b><?php echo $mobileModels[$MobilePurchases_data[0]->mobile_model_id];?></b>
			&nbsp;&nbsp;
			<b><?php
			if($MobilePurchases_data[0]->type == 1){
				echo $networks[$MobilePurchases_data[0]->network_id];
			}else{
				echo $lockedUnlocked[$MobilePurchases_data[0]->type];
			}
			?></b></td>
		</tr>
		<tr>
			<td style="text-align: center;" colspan=2>
                <?php echo $barcode;?></br>
                <span style="font-size: 14px;"><?php echo $imei;?></span>
            </td>
		</tr>
    </table>
	</div>
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
							{ href:'http://<?php echo ADMIN_DOMAIN;?>/css/print.css',media:'print'}
						]*/
				overrideElementCSS:['http://<?php echo ADMIN_DOMAIN;?>/css/print.css']
				});
		});
	 });
	function printElem(options){
		$('#printDiv').printElement(options);
	}	
</script>
