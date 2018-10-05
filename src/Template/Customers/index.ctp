<?php
/**
  * @var \App\View\AppView $this
  */
?>
 
<div class="customers index large-9 medium-8 columns content">
    <?php echo $this->Html->link(__('New Customer'), array('action' => 'add'),array('style'=>"text-decoration: none; font-size: 17px;")); ?>
	
	<?php
	$loggedInUser = $this->request->session()->read('Auth.User.username');
	if(preg_match('/'.QUOT_USER_PREFIX.'/',$loggedInUser)){
		echo $this->Html->link(__('special sale'), array('controller' => 'home','action' => 'bulk_sale',66),array('style'=>"padding-left: 87px;"));
	}
	?>
	
	<?php
		$queryStr = "";
		$rootURL = $this->request->webroot;
		if( isset($this->request->query['search_kw']) ){
			$queryStr.="search_kw=".$this->request->query['search_kw'];
		}
	?>
    <h2><?= __('Customers') ?>&nbsp;<a href="<?php echo $rootURL;?>customers/export/?<?php echo $queryStr;?>" target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png', array('fullBase' => true));?></a></h2>
	<form id="search_form" action="<?php echo $this->request->webroot;?>customers/searchAgent" method="get">
		<select name="agent" onchange='agentSubmit();' style="margin-left: 165px;margin-top: -18px;">
		<?php
		//echo $agents;die;
		foreach($allAgents as $id => $allAgent){
			if($id == $agents){
				echo"<option value=$id selected='selected'>$allAgent</option>";
			}else{
				echo"<option value=$id>$allAgent</option>";	
			}
			
		}
		?>
		</select>
	</form>
	<span style="float: right;" >**on mouse over of state if <b style="background-color: yellow;">yellow highlighted;</b> we can see customer last modified by (No VAT Applied for these customers)</span>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?php echo $this->Paginator->sort('fname','First'); ?>Name</th>
                <th scope="col"><?= $this->Paginator->sort('business') ?></th>                
                <th scope="col"><?= $this->Paginator->sort('email') ?></th>
                <th scope="col"><?= $this->Paginator->sort('mobile') ?></th>
                <th scope="col"><?= $this->Paginator->sort('Quick Sale') ?></th>
                <th>Quick Credit</th>
                 <th>Quick Performa</th> 
                <th scope="col"><?= $this->Paginator->sort('address_1') ?></th>
                <th scope="col"><?= $this->Paginator->sort('city') ?></th>
				<th scope="col"><?= $this->Paginator->sort('country') ?></th>
                <th scope="col"><?= $this->Paginator->sort('state') ?></th>
                <th scope="col"><?= $this->Paginator->sort('Account Manager') ?></th>
               
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $customer): ?>
            <tr>
                <td><?= $this->Html->link($customer->id,['controller' => 'customers', 'action' => 'edit', $customer->id]) ?></td>
                <td><?php
                    if(!empty($customer->fname)){
                            echo $this->Html->link($customer->fname,['controller' => 'customers', 'action' => 'view', $customer->id] ,['title'=>'View Customer','alt'=>'View Customer']); 
                    }else{ 
                            echo $this->Html->link("--",['controller' => 'customers', 'action' => 'view', $customer->id] ,['title'=>'View Customer','alt'=>'View Customer']);
                    }?>
                </td>&nbsp;
                <td>
                    <?php
                    if(!empty($customer->business)){
                        echo $this->Html->link($customer->business, ['controller'=>'kiosk_product_sales','action' => 'new_sale', $customer->id],['title'=>'Create Invoice','alt'=>'Create Invoice']);  
                    }else{  
                        echo $this->Html->link("--", array('controller'=>'kiosk_product_sales','action' => 'new_sale', $customer->id),array('title'=>'Sell','alt'=>'Sell'));  
                    } ?>
                </td>&nbsp;
                <td><?php echo $this->Html->link($customer->email, ['controller'=>'invoice_order_details','action' => 'create_invoice', $customer->id],['title'=>'Create Performa','alt'=>'Create Performa']); ?>&nbsp;</td>
                <td><?php
                        if(!empty($customer->mobile)){
                        echo $this->Html->link($customer->mobile, array('controller'=>'credit_product_details','action' => 'credit_note',$customer->id),array('title'=>'Create Credit Note','alt'=>'Create Credit Note'));
                        }else{
                        echo $this->Html->link("--", array('controller'=>'credit_product_details','action' => 'credit_note', $customer->id),array('title'=>'Create Credit Note','alt'=>'Create Credit Note'));
                        }
                        ?>&nbsp;
                </td>
                <td><?php echo $this->Html->link("####", ['controller'=>'home','action' => 'bulk_sale', $customer->id],['title'=>'Quick Sale','alt'=>'Quick Sale']); ?></td>
             <td> <?php   echo $this->Html->link("####", array('controller'=>'home','action' => 'bulk_credit', $customer->id),array('title'=>'Quick Credit','alt'=>'Quick Credit')); ?> </td>
		<td> <?php   echo $this->Html->link("####", array('controller'=>'home','action' => 'bulk_performa', $customer->id),array('title'=>'Quick Performa','alt'=>'Quick Performa')); ?> </td>
               <td><?= h($customer->address_1) ?></td>
                <td><?= h($customer->city) ?></td>
				
				
                <td><?= h($customer->state) ?></td>
				<?php if($customer->country == "OTH"){
					if($customer->edited_by != 0){
						$createdBy = "Last Updated BY : ".$users[$customer->edited_by];
					}else{
						$createdBy = "Last Updated BY :  --";
					}
					?>
				<td style="background-color: yellow;" title="<?php echo $createdBy; ?>"><?= h($customer->country) ?></td>
				<?php }else{?>
				<td><?= h($customer->country) ?></td>
				<?php } ?>
                <td><?php if($customer->agent_id == '0'){
					echo "--";
					}else{
						if(array_key_exists($customer->agent_id,$agentname)){
							echo $agentname[$customer->agent_id];	
						}else{
							echo "--";
						}
      
					} //pr($customer);?></td>
                 
              
               
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="paging">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
 <div style="width: 100px;float: left;">
	<h3>Search</h3>
     <form action='<?php echo $this->request->webroot;?>customers/search' method = 'get'>
	 <?php 
		echo "<div id='remote' '>";
		echo "<input name='search_kw' class='typeahead' id='cust_email' placeholder='Customer email, mobile or business' style = 'width:148px;height: 50px;margin-bottom: 10px;'   autofocus/>";;
	    echo "</div>";
	?>
	 <input type = "text" id = "custId" name = "id" placeholder = "Customer id" style = "width:145px;height: 20px;"   autofocus/><br/><br/>
	 
	<input type = "submit" name = "submit1" value = "Search"/></p>
    </form>
		 
</div>
 <script>
	function agentSubmit(){
		//alert('hi');
		document.getElementById('search_form').submit();
	}
 </script>
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
    suggestion: Handlebars.compile('<div id="cust_id" style="background-color:lightgrey;width:550px;z-index:-5000" class="row_hover"><strong style="width:400px;color:black"><a class="row_hover" href="#-1">{{fname}}</a>  <a class="row_hover" href="#-1">{{lname}}</a>  <a class="row_hover" href="#-1">{{business}}</a>  <a id="cust" rel={{id}} class="row_hover" href="#-1">{{email}}</a></strong></div>'),
	header: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>Search result for ({{query}}) :'</b></div>"),
	footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------hpwaheguru.co.uk---------</b></div>"),
  }
}).bind("typeahead:selected", function(obj, datum, name) {
$("#custId").val(datum.id);
});

</script>
 <script>
	 //$.blockUI({message: 'Just a moment...'});
 </script>