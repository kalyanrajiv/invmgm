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
 
use Cake\I18n\Time;
use Cake\Utility\Text;
use Cake\Routing\Router;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
?>
<div class="mobileRepairPrices index">
 
	<?php if(!empty($this->request->query['search_kw'])){
		$value = $this->request->query['search_kw'];
	}else{
		$value = '';
	};?>
	<form action='<?php echo $this->request->webroot; ?>mobile-repair-prices/search' method = 'get'>
	 
		<fieldset>
			<legend>Search</legend>
			<div>
				<table>
					<tr>
						<td><div id='remote'>
						<input type = "text" name = "search_kw" class='typeahead' value = '<?=$value;?>' placeholder = "brand or mobile model"  style = "width:420px" autofocus/></div></td>
						<td><input type = "submit" value = "Search Mobile Repair Prices" name = "submit"/></td>
					</tr>
				</table>
				
			</div>
		</fieldset>	
	</form>
	<?php
    //pr($this->request->query);
     $queryStr = "";
     $rootURL = "";//Router::url('/', true);
        if( isset($this->request->query['search_kw']) ){
             $queryStr.="?search_kw=".$this->request->query['search_kw'];
        }
       // echo $queryStr;
	?>
	<?php if(array_key_exists('submit',$this->request->query)){ ?>
	<h2><?php echo __('Mobile Repair Prices'); ?>&nbsp;<a href="<?php echo $rootURL;?>export/<?php echo $queryStr;?>" target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png', array('fullBase' => true));?></a></h2>
    <?php }else{ ?>
    <h2><?php echo __('Mobile Repair Prices'); ?>&nbsp;<a href="<?php echo $rootURL;?>mobile-repair-prices/export/<?php echo $queryStr;?>" target='_blank' title='export csv'><?php echo $this->Html->image('/img/export.png', array('fullBase' => true));?></a></h2>
    <?php } ?>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('brand_id'); ?></th>
			<th><?php echo $this->Paginator->sort('mobile_model_id'); ?></th>
			<th><?php echo $this->Paginator->sort('problem_type'); ?></th>
			<th><?php echo $this->Paginator->sort('cost_price','Cost'); ?>Price</th>
			<th><?php echo $this->Paginator->sort('repair_price','Repair'); ?>Price</th>
			<th><?php echo $this->Paginator->sort('repair_days','Repair'); ?>Days</th>			
			<th><?php echo $this->Paginator->sort('status'); ?></th>
		 
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php    foreach ($mobileRepairPrices as $mobileRepairPrice):
    //pr($problemtype);die;
    ?>
	<tr>
         <td><?php
       
         echo  $this->Html->link($mobileRepairPrice->id, ['controller' => 'Brands', 'action' => 'edit', $mobileRepairPrice->id]) ; ?></td>
		  <td><?= $mobileRepairPrice->has('brand') ? $this->Html->link($brands[$mobileRepairPrice->brand->id], ['controller' => 'Brands', 'action' => 'view', $mobileRepairPrice->brand->id]) : '' ?></td>
		 <td><?= $mobileRepairPrice->has('mobile_model') ? $this->Html->link($modelname[$mobileRepairPrice->mobile_model->id], ['controller' => 'MobileModels', 'action' => 'view', $mobileRepairPrice->mobile_model['id']]) : '' ?></td>
        <td><?php if(array_key_exists($mobileRepairPrice->problem_type,$problemtype)){
            echo $problemtype[$this->Number->format($mobileRepairPrice->problem_type)]; }?></td>
		 
                <td><?= $CURRENCY_TYPE.$mobileRepairPrice->repair_cost  ?></td>
                <td><?= $CURRENCY_TYPE.$mobileRepairPrice->repair_price  ?></td>
                <td><?= $this->Number->format($mobileRepairPrice->repair_days) ?></td>
                <td><?= $active[$this->Number->format($mobileRepairPrice->status)] ?></td>
                
                <td><?php  echo $modified = h(date('jS M, Y g:i A',strtotime($mobileRepairPrice->modified))) ;
               
                 ?></td>
		 
		 
		<td>
			<?php 
					$editUrl = "/img/16_edit_page.png";
					$viewUrl = "/img/text_preview.png";
					 $deleteUrl = "/img/list1_delete.png";
					$editgridUrl = "/img/fileview_close_right.png";
			
			?>
			<?php echo $this->Html->link($this->Html->image($viewUrl,array('fullBase' => true)),  array('action' => 'view', $mobileRepairPrice->id),
						     array('escapeTitle' => false, 'title' => 'View', 'alt' => 'View')); ?>
			
			<?php echo $this->Html->link($this->Html->image($editUrl,array('fullBase' => true)),
						    array('action' => 'edit', $mobileRepairPrice->id),
						   array('escapeTitle' => false, 'title' => 'Edit', 'alt' => 'Edit')); ?>
                          
                          
            
			<?php	 echo $this->Form->postLink(
						$this->Html->image($deleteUrl,
						   array("alt" => __('Delete'), "title" => __('Delete'))), 
						array('action' => 'delete', $mobileRepairPrice->id), 
						array('escape' => false, 'confirm' => __('Are you sure you want to delete # %s?', $mobileRepairPrice->id)) 
					    ); 
			?>
			<?php echo $this->Html->link($this->Html->image($editgridUrl,array('fullBase' => true)),
						    array('action' => 'editGrid',$mobileRepairPrice->mobile_model->id),
						   array('escapeTitle' => false, 'title' => 'Edit Grid', 'alt' => 'Edit Grid')); ?>
		 
		</td>
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
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Mobile <br/>Repair Price'), array('action' => 'add'),array('escape' => false) ); ?></li>
		 
		<?php if($this->request->session()->read('Auth.User.group_id') == MANAGERS || $this->request->session()->read('Auth.User.group_id') == ADMINISTRATORS || $this->request->session()->read('Auth.User.group_id') == FRANCHISE_OWNER){?>
                <li><?php echo $this->Html->link(__('Send repair <br/>price notification'), array('controller' => 'mobile_repair_prices', 'action' => 'repair_price_push_notification'),array('escape' => false)); ?> </li>
		<?php } ?>
	</ul>
</div>
<script>
	var user_dataset = new Bloodhound({
	datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
	queryTokenizer: Bloodhound.tokenizers.whitespace,
	//prefetch: "/products/data",
	remote: {
	  url: "/mobile-repair-prices/brand_suggestions?search=%QUERY",
	//  url: "/mobile-repair-prices/brandsuggestions?search=%QUERY",
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
	      footer: Handlebars.compile("<div style='background-color:lightgrey;width:400px;z-index:-5000'><b>---------hpwaheguru.co.uk---------</b></div>"),
	}
      });
</script>