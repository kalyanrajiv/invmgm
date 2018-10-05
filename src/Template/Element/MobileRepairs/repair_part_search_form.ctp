<?php
	echo $this->Form->create(null, array('type' => 'Get', 'url' => array('controller' => 'mobile_repairs','action' => 'search_repair_product',$repairID)));?>
	<fieldset>	    
		<legend>Search</legend>
		<table>
		<tr>
			<td></td>
			<td colspan='2'><strong>Find by category &raquo;</strong></td>
		</tr>
			<td><div id='remote'><input class="typeahead" type = "text" value = '<?= $product_code ?>' name = "search_kw" placeholder = "Product Code or Product Title" style = "width:500px;height:25px;"/></div></td>
			<td rowspan="3"><select id='category_dropdown' name='category[]' multiple="multiple" size='6' onchange='update_hidden();'><option value="0">All</option><?php echo $categories;?></select>
			<?php if($kioskId > 0){?>
			<input type="hidden" name="selectedKiosk" value="<?=$kioskId;?>">
			<input type="hidden" name='part' <?= $repairdPartVal; ?>>
			<?php } ?>
			</td>
		</tr>
		<tr>
			<td><h4>&#42;&#42;Hold the Ctrl key &#40;&#94;&#41; to select multiple options &raquo;</h4></td>
		<tr>
			<td colspan='2'><input type='submit' name='search' value='Search'</td>
		</tr>		
		</table>
	</fieldset>
<?php
	$options = array(
		'label' => '',//Search Product
		'div' => false,
		'name' => 'submit1',
		'style' => 'display:none;'
	);
	echo $this->Form->submit("submit1",$options);
	echo $this->Form->end();
?>