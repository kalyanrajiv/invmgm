<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<div class="productReceipts form">
<?php
	$siteBaseURL = Configure::read('SITE_BASE_URL'); //rasu
	$url = $this->Url->build(array('controller' => 'customers', 'action' => 'get_address'));
	echo $this->Form->create('KioskProductSale');
?>
	<fieldset>
        
		<legend><?php echo __('Price Change'); ?>(for recipt id <?php echo $kioskProductSale['product_receipt_id'];?> with total Invoice amount <?php echo $cashCardAmt;?>)</legend>
		<div>
		<?php //pr($products);
		echo "<table>";
		echo "<tr>";
		echo "<th>Product code";
		echo "<th>Product Title";
		echo "<th>Product Image";
        echo "<th> Qantity";
        echo "<th> Kiosk";
        echo "<th> User";
		echo "<th> Lowest Price";
        echo "<th> Sold Price(unit price)";
		echo "</tr>";
        //pr($products);die;
		foreach($products as $product ){
			//pr($product);die;
			echo "<tr>";
				echo "<td>".$product['product_code']."</td>";
				echo "<td>".$product ['product']."</td>";
				echo "<td>";
					$imageDir = WWW_ROOT."files".DS.'product'.DS.'image'.DS.$product['id'].DS;
					$imageName = 'thumb_'.$product['image'];
					$absoluteImagePath = $imageDir.$imageName;
					$imageURL = "/thumb_no-image.png";
					if(@readlink($absoluteImagePath) || file_exists($absoluteImagePath)){
						$imageURL = "$siteBaseURL/files/product/image/".$product['id']."/$imageName"; //rasu
					}
					echo $this->Html->link(
						$this->Html->image($imageURL, array('fullBase' => false)), //rasu
						array('controller' => 'products','action' => 'edit', $product ['id']),
						array('escapeTitle' => false, 'title' => $product['product'])
					);
            echo "</td>";
		}
		echo "<td>";
        echo $kioskProductSale['quantity'];
        echo "</td>";
        echo "<td>";
        echo $kiosks[$kioskProductSale['kiosk_id']];
        echo "</td>";
        echo "<td>";
        echo $users[$kioskProductSale['sold_by']];
        echo "</td>";
		 $discountedPrice = $kioskProductSale['sale_price']-($kioskProductSale['sale_price']*$kioskProductSale['discount']/100);
		 echo "<td>";
			echo $lowest_price;
		 echo "</td>";
        echo "<td>";
            echo "<input type = 'text' name ='changed_amount' value =".$discountedPrice.">";
            echo "<input type = 'hidden' name ='org_amount' value =".$discountedPrice.">";
        echo "</td>";
		echo "</tr>";
		echo "</table>";
		?>
		<?php //pr($kioskProductSale);
        $discountedPrice = $kioskProductSale['sale_price']-($kioskProductSale['sale_price']*$kioskProductSale['discount']/100);
		
		if($discountedPrice<$kioskProductSale['sale_price']){?>
			Item sold on <?php echo $this->Time->format('M jS, Y', $kioskProductSale['created'],null,null); ?>
			at <b><span style="color: red;"><?php echo $CURRENCY_TYPE; echo $discountedPrice ;?></span></b> with a discount of <?php echo ( number_format($kioskProductSale['discount'],2));?>% (Actual Price <?php echo $CURRENCY_TYPE; echo $kioskProductSale['sale_price'] ;?>).</br></br>
			<h4><b>Note:</b> The above price is for one item only.</h4>
		<?php }else{ ?>
	 
			Item sold on <?php  echo $this->Time->format('M jS, Y', $kioskProductSale['created'],null,null); ?> at  <?php echo $CURRENCY_TYPE; echo ($kioskProductSale['sale_price'])?> each</br></br>
			<h4><b>Note:</b> The above price is for one item only.</h4>
		<?php } ?>
		
		</div> 
		
		<?php
		echo "<table>";
				echo $this->Form->input('id', array('type' => 'hidden','value' =>$kioskProductSale['id']));
				echo $this->Form->input('kiosk_id', array('type' => 'hidden','value' =>$kioskProductSale['kiosk_id']));
				echo $this->Form->input('sold_by', array('type' => 'hidden'));
				echo $this->Form->input('refund_by', array('type' => 'hidden'));
				echo $this->Form->input('status', array('type' => 'hidden'));
				echo $this->Form->input('discount_status', array('type' => 'hidden'));
				echo $this->Form->input('sale_price', array('type' => 'hidden'));
				echo $this->Form->input('quantity', array('type' => 'hidden', 'value' => $kioskProductSale['quantity']));
				echo $this->Form->input('discount', array('type' => 'hidden'));
				echo $this->Form->input('product_receipt_id',array('type' => 'hidden','value' =>$kioskProductSale['product_receipt_id']));
			    echo $this->Form->input('product_id',array('type' => 'hidden','value' =>  $kioskProductSale['product_id']));
				 echo $this->Form->input('created',array('type' => 'hidden'));
				echo "</table>";
	?>
		
	</fieldset>
<?php
echo $this->Form->Submit(__('Submit'));
echo $this->Form->end(); ?>
</div>
<div class="actions">
    <h3><?php echo __('Actions'); ?></h3>
    <ul>
        <li><?php echo $this->Html->link(__('View Sale'), array('action' => 'index')); ?></li>        
        <li><?php echo $this->Html->link(__('New Sale'), array('action' => 'new_order')); ?> </li>
            
    </ul>
</div>
