<div class="customers index">
	<?php echo $this->Html->link(__('New Retail Customer'), array('action' => 'add'),array('style'=>"text-decoration: none;font-size: 18px;")); ?>
<?php //pr($retailcustomers); die;?>
	
	 
	<h2><?php echo __('Retail Customers'); ?> </h2>
	<table cellpadding="0" cellspacing="0">
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('fname','First'); ?>Name</th>
			 <th><?php echo $this->Paginator->sort('lname','Last'); ?>Name</th>
			<th><?php echo $this->Paginator->sort('email'); ?></th>
			<th><?php echo $this->Paginator->sort('mobile'); ?></th>
			<th><?php echo $this->Paginator->sort('addrss'); ?></th>
			<th><?php echo $this->Paginator->sort('city'); ?></th>
			<th><?php echo $this->Paginator->sort('state','County'); ?></th>
			<th><?php echo $this->Paginator->sort('zip','Postal'); ?>Code</th>
            <th>Action</th>
	</tr>
	</thead>
	<tbody>
	<?php	//pr($retailcustomers); die;?>
	<?php foreach ($retailcustomers as $customer):
	 ?>
	<tr>
		<td><?php echo $this->Html->link($customer->id,array('action' => 'edit', $customer->id),array('title'=>'Edit','alt'=>'Edit')); ?>&nbsp;</td>
		<td>
		<?php if(!empty($customer->fname)){?>
		<?php echo $this->Html->link($customer->fname,array('action' => 'view', $customer->id),array('title'=>'View','alt'=>'View')); ?>&nbsp;
		<?php }else{ ?>
		<?php echo $this->Html->link("--",array('action' => 'view', $customer->id),array('title'=>'View','alt'=>'View')); ?>&nbsp;
		<?php } ?>
		</td>
		 <td>
			<?php if(!empty($customer->lname)){
				echo $customer->lname;
			}
			
			?>
		 </td>
		<td><?php echo $this->Html->link($customer->email, array('controller'=>'invoice_order_details','action' => 'create_invoice', $customer->id),array('title'=>'Create Performa','alt'=>'Create Performa')); ?>&nbsp;</td>
		<td><?php
		if(!empty($customer->mobile)){
		echo  h($customer->mobile);
		}else{
		echo "--";
		}
		?>&nbsp;</td>
		<td><?php echo h($customer->address_1); ?>&nbsp;</td>
		<td><?php echo h($customer->city); ?>&nbsp;</td>
		<td><?php echo $customer->state; ?>&nbsp;</td>
		<td><?php echo h($customer->zip); ?>&nbsp;</td>
       <td> Add(
	    <?php echo $this->Html->link(__('Repair'), array(
                                                'controller' => 'mobile_repairs',
                                                'action' => 'add',
                                                 '?' => array('customerId' => $customer->id))); ?>,
            <?php echo $this->Html->link(__('Unlock'), array(
                                                'controller' => 'mobile_unlocks',
                                                'action' => 'add',
                                                 '?' => array('customerId' => $customer->id))
            ); ?>&nbsp;<b>|</b>&nbsp;
			 <?php echo $this->Html->link(__('Mobile Purchase'), array(
                                                'controller' => 'mobile_purchases',
                                                'action' => 'add',
                                                 '?' => array('customerId' => $customer->id))
            ); ?>
			 &nbsp;<b>|</b>&nbsp;<?php echo $this->Html->link(__('New Sale'), array(
                                                'controller' => 'kiosk_product_sales',
                                                'action' => 'new_order',
                                                 '?' => array('customerId' => $customer->id))
            ); ?>
           
        )
        </td>
	</tr>
<?php endforeach; ?>
	</tbody>
	</table>
	<p>
	<div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}},
 showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
<div style="width: 100px;float: left;">
	<?php
	 

	 if(!empty($this->request->query['search_kw'])){
		$value = $this->request->query['search_kw'];
	}else{
		$value = '';
	}
	
	if(!empty($this->request->query['id'])){
		$id_value = $this->request->query['id'];
	}else{
		$id_value = "";
	}
	$webRoot = $this->request->webroot."retail-customers/search";
	echo $this->Form->create(null, array('url' => $webRoot,'type' => 'get'));
	?>
	<h3>Search</h3>
		<?php
		echo "<div id='remote' '>";
		echo $this->Form->input('null',array(
							'type'=>'textarea',
							'class'=>'typeahead',
							'name'=>'search_kw',
							'label'=>false,
							'placeholder'=>'Customer email, mobile, postal code,fname or lname',
							'style'=>'height: 50px; width: 130px;',
							'autofocus' => true,
							'value' =>  $value 
							)
					      );
		echo "</div>";
		echo $this->Form->input('null',array(
					'type'=>'textarea',
					'name'=>'id',
					'id' => 'custId',
					'label'=>false,
					'placeholder'=>'Customer id',
					'style'=>'height: 30px; width: 130px;',
					'autofocus' => true,
					'value' =>  $id_value 
					)
				  );
		?>
		<?php
		echo $this->Form->submit("Search",['name'=>'submit']);
		echo $this->Form->end();
		?>
</div>
<script>
	var user_dataset = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  //prefetch: "/products/data",
  remote: {
    url: "/retail-customers/custemail?search=%QUERY",
    wildcard: "%QUERY"
  }
});
	
$('#remote .input .typeahead').typeahead(null, {
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
    suggestion: Handlebars.compile('<div id="cust_id" style="background-color:lightgrey;width:550px;z-index:-5000" class="row_hover"><strong style="width:400px;color:black"><a class="row_hover" href="#-1">{{fname}}</a>  <a class="row_hover" href="#-1">{{lname}}</a>  <a class="row_hover" href="#-1">{{business}}</a>  <a id="cust" rel={{id}} class="row_hover" href="#-1">{{email}}</a></strong></div>'),
	header: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>Search result for ({{query}}) :'</b></div>"),
	footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------hpwaheguru.co.uk---------</b></div>"),
  }
}).bind("typeahead:selected", function(obj, datum, name) {
$("#custId").val(datum.id);
});
</script>