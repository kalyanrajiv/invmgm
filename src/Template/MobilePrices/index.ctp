<?php
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<style>
 #remote .tt-dropdown-menu {
  max-height: 250px;
  overflow-y: auto;
}
 #remote .twitter-typehead {
  max-height: 250px;
  overflow-y: auto;
}
.tt-dataset, .tt-dataset-product {
  max-height: 250px;
  overflow-y: auto;
}
.row_hover:hover{
 color:blue;
 background-color:yellow;
}
</style>
<?php
	$statusArray = array(0 => 'Inactive', 1 => 'Active');
	$currency = Configure::read('CURRENCY_TYPE');
	//$this->Number->addFormat('BRL', array('before' => "$currency ", 'negative'=>'-','zero'=>"$currency 0.00", 'escape' => false));
?>
<div class="mobilePrices index">
	<?php if(!empty($this->request->query['search_kw'])){
        $value = $this->request->query['search_kw'];
      }else{
        $value = '';
       }?>
  <?php $webRoot = $this->request->webroot."mobile-prices/search"; //FULL_BASE_URL.?>
  <?php echo $this->Form->create('Mobile_re_sales',array('url' => $webRoot,'type' => 'get'));?>
 
  <div class="search_div">
   <fieldset>
   <legend>Search</legend>
   <table>
    <tr>
     <td><div id='remote'><input type = "text" class='typeahead' name = "search_kw" id = "search_kw" value= '<?= $value;?>' placeholder = "model, brand " style = "width:450px"  autofocus/></div></td>
     <td><input type = "submit" value = "Search Mobile Prices" name = "submit",style = "width:155px"/></td>
    </tr>
   </table>
    
   
  </fieldset> 
   
  </div>
 <?php echo $this->Form->end(); ?>
 <?php
  
  $queryStr = "";
  $rootURL = $this->request->webroot;
  if( isset($this->request->query['search_kw']) ){
   $queryStr.="search_kw=".$this->request->query['search_kw'];
  }
 //echo $queryStr;
   
 ?>
 
 <h2><?php echo __('Mobile Prices'); ?>&nbsp;<a href='<?php echo $this->request->webroot;?>mobile-prices/export/?<?php echo $queryStr;?>' target='_blank' title='export csv'>
 <?php echo $this->Html->image('/img/export.png',array('fullBase' => true));?></a></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php #echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('user_id'); ?></th>
			<th><?php echo $this->Paginator->sort('brand_id'); ?></th>
			<th><?php echo $this->Paginator->sort('mobile_model_id'); ?></th>
			<th><?php echo $this->Paginator->sort('locked'); ?></th>
			<th><?php echo $this->Paginator->sort('grade'); ?></th>
			<th><?php echo $this->Paginator->sort('cost_price'); ?></th>
			<th><?php echo $this->Paginator->sort('sale_price'); ?></th>
			<th><?php echo $this->Paginator->sort('status'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($mobilePrices as $mobilePrice): //pr($mobilePrice);?>
	<tr>
		<td><?php //echo $this->Html->link($mobilePrice['MobilePrice']['id'],array('action'=>'edit',$mobilePrice['MobilePrice']['id'])); ?>&nbsp;</td>
		<td><?php if(array_key_exists($mobilePrice->user_id,$userName)){
		     echo $userName[$mobilePrice->user_id];
		 }else{
		     echo "--";
		  } ?>&nbsp;</td>
		<td>
			<?php echo $brands[$mobilePrice->brand_id]; ?>
		</td>
		<td>
			<?php echo $mobileModels[$mobilePrice->mobile_model_id]; ?>
		</td>
		<td>
			<?php echo $lockedStatus[$mobilePrice->locked]; ?>
		</td>
		<td>
			<?php echo $mobilePrice->grade; ?>
		</td>
		<td>
			<?php $cost_price =  $mobilePrice->cost_price;
				echo $CURRENCY_TYPE.$cost_price;
			?>
		</td>
		<td>
			<?php $selling_price = $mobilePrice->sale_price;
				echo $CURRENCY_TYPE.$selling_price;
			?>
		</td>
		<td>
			<?php echo $statusArray[$mobilePrice->status];?>
		</td>
		<td>
			
				<?php echo $this->Html->link('Edit',array('controller'=>'mobile_prices','action'=>'edit',$mobilePrice->mobile_model_id)); ?>
				<?php echo $this->Html->link('Edit Grid',array('controller'=>'mobile_prices','action'=>'edit_grid',$mobilePrice->mobile_model_id)); ?>
			
		</td>
	</tr>
	<?php //die;?>
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
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Mobile Price'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Brands'), array('controller' => 'brands', 'action' => 'index')); ?> </li>
		
		<li><?php echo $this->Html->link(__('List Mobile Models'), array('controller' => 'mobile_models', 'action' => 'index')); ?> </li>
		
	</ul>
</div>
<script>
	var user_dataset = new Bloodhound({
	datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
	queryTokenizer: Bloodhound.tokenizers.whitespace,
	//prefetch: "/products/data",
	remote: {
	  url: "/mobile-repair-prices/brand_suggestions?search=%QUERY",
	  wildcard: "%QUERY"
	}
      });
      
      $('#remote .typeahead').typeahead(null, {
	name: 'model',
	display: 'model',
	source: user_dataset,
	limit:100,
	minlength:3,
	classNames: {
	  input: 'Typeahead-input',
	  hint: 'Typeahead-hint',
	  selectable: 'Typeahead-selectable'
	},
	highlight: true,
	hint:true,
	templates: {
	  suggestion: Handlebars.compile('<div style="background-color:lightgrey;width:400px;z-index:-5000" class="row_hover"><strong style="width:400px;color:black"><a class="row_hover" href="#-1">{{model}}</a></strong></div>'),
	      header: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>Search result for ({{query}}) :'</b></div>"),
	      footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------<?php echo ADMIN_DOMAIN;?>---------</b></div>"),
	}
      });
</script>