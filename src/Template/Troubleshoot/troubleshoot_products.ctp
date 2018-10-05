<div class="brands index">
    <?php echo $this->Form->create(null,array('url' => array('controller' => 'troubleshoot','action' => 'troubleshoot_products'),'id'=>"ProductTroubleshootForm")); ?>
		<form id="ProductViewStockForm" method="post">
	<fieldset>
		<?php //pr($kiosks);?>
		<div>**If you see any product with issue here, just edit and save it on <?php echo ADMIN_DOMAIN;?>. Thats it.</div>
	<legend><span><strong>Kiosk</strong><span style='color:red'><sup>*</sup></span></legend> <?php echo $this->Form->input(null, array(
									       'options' => $kiosks,
									       'label' => false,
									       'div' => false,
									       'name' => 'Troubleshoot[kiosk_id]',
									       'value' => $kiosk_id,
									       'onChange'=>'select_change();',
									       'empty' => 'Choose Kiosk',
									       'id'=>'Product'
									      
									       )
													      );?></span>
		
		
	</fieldset>
	</form>
	<h2>Troubleshoot Kiosk</h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<th>kiosk</th>
	<th>Product Id</th>
    <th>Product Code(Kiosk)</th>
    <th>Product Code(Main)</th>
	</thead>
	<tbody>
	<?php
	//pr($productRS);die;
        foreach($productRS as $key => $sngProduct){
            //pr($sngProduct);
			foreach($sngProduct as $raw => $value){
                //pr($raw);
				$productID = $sngProduct[$raw]['id'];
				$kioskProductCode = $sngProduct[$raw]['product_code'];
				$productCode = $sngProduct[$raw]['product_code'];
				if($kioskProductCode == $productCode){
					continue;
				}
				echo "<tr><td>$kiosks[$key]</td><td>$productID</td><td>$kioskProductCode</td><td>$productCode</td>";
			}
        }
    ?>
	</tbody>
	</table>
	<p>
	
	
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index')); ?> </li>
	</ul>
</div>

<script>
	
function select_change(){
	$.blockUI({ message: 'Loading ...' });
    var z = document.getElementById("Product").value;
    document.getElementById("ProductTroubleshootForm").submit();
}

//window.onload = select_change();//break;

function submitForm(){
	document.getElementById("display_form").submit();
}
</script>