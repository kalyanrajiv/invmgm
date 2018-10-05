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
            <td colspan=2 style="text-align: center;"><?php 
			echo $truncatedProduct =
									\Cake\Utility\Text::truncate(
                                                                        $product_data[0]->product,
                                                                        30,
                                                                        [
                                                                                'ellipsis' => '...',
                                                                                'exact' => false
                                                                        ]
                                                                );
			
			//$product_data[0]->product;?></td>
        </tr>
        <tr>
            <td style="text-align: center;">
                <?php echo $barcode;?></br>
                <span style="font-size: 14px;"><?php echo $product_code;?></span>
            </td>
            <td >
                <b style="font-size: 22px;"><?php echo $CURRENCY_TYPE.$print_label_price;?></b>
            </td>
        </tr>
		<?php if(!empty(trim($setting_arr['sourced_by']))){ ?>
        <tr >
            <td colspan=2>sourced by,</br><span style="font-size: 14px;"><?php echo $setting_arr['sourced_by']; ?></span></td>
        </tr>
		<?php } ?>
    </table>
	</div>
<script>
	
</script>
