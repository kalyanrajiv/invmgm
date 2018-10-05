<div class="mobileUnlocks index">
	<strong><?php echo __('<span style="color: red; font-size: 20px">Change Customer</span> for Recipt ID '.$recipt_id); ?></strong></br>
	<b>First Name : <?php echo $customer_first_name;?></br>
	Last Name : <?php echo $customer_last_name;?></br>
	Bussiness : <?php echo $customer_bussiness;?></br></b>
	<b>amt : <?php echo $orig_bill_amount; ?></b>
	<h2><?php echo __('Customers'); ?>&nbsp;</h2>
	<?php
	$webRoot = $this->request->webroot."product-receipts/search_customer";
	echo $this->Form->create('Search', array('url' => $webRoot,'type' => 'get'));
	?>
	<h3>Search</h3>
		<?php /*echo $this->Form->input('null',array(
							'type'=>'text',
							'name'=>'search_kw',
							'label'=>false,
							'placeholder'=>'Customer email, mobile or business',
							'style'=>'height: 35px;width: 198px;',
							'autofocus' => true
							)
					      );*/
		
		 echo "<div id='remote' '>";
	echo "<input name='search_kw' class='typeahead' id='cust_email' placeholder='Customer email, mobile or business' style = 'height: 35px;width: 198px;'   autofocus/>";;
    echo "</div>";
		
		echo $this->Form->input('null',array(
							'type'=>'textarea',
							'name'=>'id',
							'id'=>'custId',
							'label'=>false,
							'placeholder'=>'Customer id',
							'style'=>'height: 21px;width: 198px;',
							'autofocus' => true,
							//'value' =>  $id_value 
							)
					      );
		
		echo $this->Form->input('kiosk_id',array('type' => 'hidden','value' => $kiosk_id));
		echo $this->Form->input('old_customer_id',array('type' => 'hidden','value' => $old_customer_id));
		echo $this->Form->input('recipt_id',array('type' => 'hidden','value' => $recipt_id));
		
		?>
		<?php
		//echo $this->Form->submit('Search');
        echo "<input type = 'submit' name = 'submit' value = 'Search'/>";
		echo $this->Form->end();
		?>
	<?php $webRoot = $this->request->webroot."product-receipts/change_customer/$recipt_id/$kiosk_id"; ?>
	<?php echo $this->Form->create('change_customer',array('url' => $webRoot));?>
	<table>
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('fname','First'); ?>Name</th>
			<th><?php echo $this->Paginator->sort('business'); ?>Name</th>
			<th><?php echo $this->Paginator->sort('email'); ?></th>
			<th><?php echo $this->Paginator->sort('mobile'); ?></th>
			<th><?php echo $this->Paginator->sort('addrss'); ?></th>
			<th><?php echo $this->Paginator->sort('state','County'); ?></th>
			<th scope="col"><?= $this->Paginator->sort('country') ?></th>
			<th><?php echo $this->Paginator->sort('zip','Postal'); ?>Code</th>
	</tr>
	</thead>
	<tbody>
		<?php //echo $old_customer_id;?>
	<?php foreach ($customers as $customer):
	//pr($customer);?>
	<tr>
		<td><?php
		$cust_id = $customer->id;
		if($cust_id == $old_customer_id){
			continue;
		}
		echo $customer->id; ?>&nbsp;</td>
		<td>
		<?php if(!empty($customer->fname)){?>
		<?php echo $customer->fname;?>&nbsp;
		<?php } ?>
		</td>
		<td>
		<?php if(!empty($customer->business)){?>
		<?php echo $customer->business;?>&nbsp;
		<?php } ?>
		</td>
		<td><?php echo $customer->email;?>&nbsp;</td>
		<td><?php
		if(!empty($customer->mobile)){
		echo $customer->mobile;
		}
		?>&nbsp;</td>
		<td><?php echo h($customer->address_1); ?>&nbsp;</td>
		<td><?php echo $customer->state; ?>&nbsp;</td>
		<?php if($customer->country == "OTH"){ ?>
				<td style="background-color: yellow;"><?= h($customer->country) ?></td>
				<?php }else{?>
				<td><?= h($customer->country) ?></td>
				<?php } ?>
		
		<td><?php echo h($customer->zip); ?>&nbsp;</td>
		<td>
					 <input type="radio" name='<?php echo "customer";?>' value='<?php echo $cust_id;?>'>
		</td>
	</tr>
<?php endforeach;
echo $this->Form->submit('Submit',array('name'=>'submit'));
echo $this->Form->end();
?>
	</tbody>
	</table>
	<div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}},
 showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
	
	
	
	
	
	
	
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Customers'), array('controller' => 'customers', 'action' => 'index')); ?> </li>
        <?php
        $loggedInUser = $this->request->session()->read('Auth.User.username');
        if (preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){//checking if the user has right for tempering
									?>
        <li><?=$this->element('tempered_side_menu')?></li>
		<li><?php echo $this->Html->link(__('ManXX Quotation'), array('controller' => 'product_receipts', 'action' => 'dr_index',1)); ?> </li>
        <?php }?>
		<li><?php echo $this->Html->link(__('View Invoices'), array('controller' => 'product_receipts', 'action' => 'all_invoices')); ?> </li>
		<li><?php echo $this->Html->link(__('View Sales'), array('controller' => 'kiosk_product_sales', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('Manxx Invoice'), array('controller' => 'product_receipts', 'action' => 'index',1)); ?> </li>
	</ul>
</div>


 <script>
 var user_dataset = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  //prefetch: "/products/data",
  remote: {
    url: "/retail-customers/custemail_w?search=%QUERY",
    wildcard: "%QUERY"
  }
});

$('#remote .typeahead').typeahead(null, {
  name: 'email',
  display: 'email',
  source: user_dataset,
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
    suggestion: Handlebars.compile('<div style="background-color:lightgrey;width:550px;z-index:-5000" class="row_hover"><strong style="width:400px;color:black"><a class="row_hover" href="#-1">{{fname}}</a>  <a class="row_hover" href="#-1">{{lname}}</a>  <a class="row_hover" href="#-1">{{business}}</a>  <a class="row_hover" href="#-1">{{email}}</a></strong></div>'),
	header: Handlebars.compile("<div style='background-color:lightgrey;width:450px;z-index:-5000'><b>Search result for ({{query}}) :'</b></div>"),
	footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------hpwaheguru.co.uk---------</b></div>"),
  }
}).bind("typeahead:selected", function(obj, datum, name) {
$("#custId").val(datum.id);
});
</script>