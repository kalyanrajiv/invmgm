<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<style>
#remote .tt-dropdown-menu {max-height: 250px;overflow-y: auto;}
#remote .twitter-typehead {max-height: 250px;overflow-y: auto;}
.tt-dataset, .tt-dataset-product {max-height: 250px;overflow-y: auto;}
.row_hover:hover{color:blue;background-color:yellow;}
</style>
<div class="mobileRepairs index">
<?php
	$repairID = 0;
	$siteBaseURL = Configure::read('SITE_BASE_URL');
	$session_kiosk_id = $this->request->Session()->read('kiosk_id');
	if(isset($repair_id) && !empty($repair_id)){
		$repairID = $repair_id;
	}else{
		$repairID = $this->request['data']['MobileRepair']['id'];
	}
	echo "<h2>Manage Repair Parts &raquo;</h2>";
	echo "<h4>Last repaired at ".$kiosks[$kioskID]."</h4>";
	if(!count($viewRepairParts)){
		echo $this->Form->create(null,array('type' => 'Get', 'id' => 'main_form', 'url' => array('controller' => 'mobile_repairs','action' => 'view_repair_parts',$repairID)));
	}
	if(count($viewRepairParts)){ 
		$repairPartId = $kioskId = false;
		$repairdPartVal = '';
		if(count($this->request->query) && array_key_exists('data',$this->request->query)){
			$repairPartId = $this->request->query['data']['PartsRepaired']['part'];
			$kioskId = $this->request->query['data']['PartsRepaired']['kiosk_id'];
		}elseif(count($this->request->query) && array_key_exists('search',$this->request->query)){
			$kioskId = $kskId;
			$repairPartId = $prtId;
			if($kioskId == 0 && $repairPartId == 0){$repairPartId = $kioskId = false;}
		}
		if($repairPartId){$repairdPartVal = "value = '$repairPartId'";}
		extract($this->request->query);
		if(!isset($product)){$product = "";}
		if(!isset($product_code)){$product_code = "";}
		$webRoot = $this->request->webroot.'mobile_repairs/search';
?>
	<div><?php //echo $this->Flash->success(''); ?></div>
	<?php
	
		if(count($this->request->query)){
			//search form
			echo $this->element('/MobileRepairs/repair_part_search_form', array(
																		'product_code' => $product_code,
																		'kioskId' => $kioskId,
																		'repairdPartVal' => $repairdPartVal,
																		'categories' => $categories,
																		'repairID' => $repairID,
																			  ));
		}
		
		echo $this->Form->create(null,array('type' => 'Get', 'id' => 'main_form', 'url' => array('controller' => 'mobile_repairs','action' => 'view_repair_parts',$repairID)));
		
		echo $this->element('/MobileRepairs/manage_repair_parts', array(
																   'products' => $products,
																   'viewRepairParts' => $viewRepairParts,
																   'repairPartId' => $repairPartId,
																   'repairdPartVal' => $repairdPartVal,
																		 ));
		echo $this->element('/MobileRepairs/otherepair_parts', array(
																		 'viewOtherRepairParts' => $viewOtherRepairParts,
																		 'users' => $users,
																		 'kiosks' => $kiosks,
																		 'productName' => $productName,
																		 'productsCode' => $productsCode,
																		 ));
		if(count($products)){
			$sessionBaket = $this->request->Session()->read("parts_basket");
			echo $this->element('/MobileRepairs/products_4_repair', array(
																		  'products' => $products,
																		  'siteBaseURL' => $siteBaseURL,
																		  'sessionBaket' => $sessionBaket,
																		  'ksk_Id' => $ksk_Id,
																		  ));
		}else{
			
			echo $buttons = <<<BUTTONS
				<div class="submit">
					<input type="submit" name='delete' value="Delete" id='delete_button'/>
					<input type="submit" name='add_repair_parts' value="Add parts to Repair"/>
				</div>
BUTTONS;
		}
	}else{
			echo $this->element('/MobileRepairs/otherepair_parts', array(
																		 'viewOtherRepairParts' => $viewOtherRepairParts,
																		 'users' => $users,
																		 'kiosks' => $kiosks,
																		 'productName' => $productName,
																		 'productsCode' => $productsCode,
																		 ));
			echo $buttons = <<<BUTTONS
			<input type="submit" name='add_repair_parts' value="Add parts to Repair"/>
BUTTONS;
	}
	echo $this->Form->end();
?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Mobile Repair'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('View Repair Parts'), array('action' => 'view_repair_parts',$repairID)); ?></li>
		<li><?php echo $this->element('repair_navigation'); ?></li>
	</ul>	
</div>
<input type='hidden' name='url_category' id='url_category' value=''/>

<script type="text/javascript">
	function update_hidden(){
		var multipleValues = $( "#category_dropdown" ).val() || [];
		$('#url_category').val(multipleValues.join( "," ));
	}
	var product_dataset = new Bloodhound({
	datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
	queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote: {
			url: "/products/admin_data?category=%CID&search=%QUERY",
			replace: function (url,query) {
				var multipleValues = $( "#category_dropdown" ).val() || [];
				$('#url_category').val(multipleValues.join( "," ));
				return url.replace('%QUERY', query).replace('%CID', $('#url_category').val());
			},
			wildcard: "%QUERY"
		}
	});
		
	$('#remote .typeahead').typeahead(null, {
		name: 'product',
		display: 'product',
		source: product_dataset,
		limit:120,
		minlength:3,
		classNames: {
			input: 'Typeahead-input',
			hint: 'Typeahead-hint',
			selectable: 'Typeahead-selectable'
		},
		highlight: true,
		hint:true,
		templates: {
		    suggestion: Handlebars.compile('<div style="background-color:lightgrey;width:400px;z-index:-5000" class="row_hover"><strong style="width:400px;color:black"><a class="row_hover" href="#-1">{{product_code}}</a></strong></div>'),
			header: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>Search result for ({{query}}) :'</b></div>"),
			footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------<?php echo ADMIN_DOMAIN;?>---------</b></div>"),
		}
	});
	function updateHidden(partid, kioskId){
		document.getElementById('part_id').value = partid;
		document.getElementById('kiosk_id').value = kioskId;
		document.getElementById('main_form').submit();
	}
	
	function updateKiosk(kioskId){
		$.blockUI({ message: 'Just a moment...' });
		document.getElementById('kiosk_id').value = kioskId;
		//document.getElementById('main_form').submit();
	}
	
	$('#delete_button').click(function(ev){
		var count = $("[type='checkbox']:checked").length;
		if(!confirm("Do you really want to delete "+ count +" parts from repair?")){
			event.preventDefault(ev);
		}
	});
</script>