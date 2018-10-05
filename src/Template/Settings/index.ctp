<?php
use Cake\Utility\Text;
use Cake\Routing\Router;
  
?>
<div class="settings index">
	
	<h2><?php echo __('Settings'); ?></h2>
	 <h4>**Memo's / Comment are displayed on mouse over of attribute name</h3>
	 
	 
	<span class='paging' style='text-align:right;float:right;margin-top: -15px;'>
		 
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
      
	</span>
	 
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('attribute_name'); ?></th>
			<th><?php echo $this->Paginator->sort('attribute_value'); ?></th>
			
			<th><?php echo $this->Paginator->sort('comment','Memo'); ?></th>			
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php  foreach ($settings as $setting):
    if($setting->attribute_name == 'product_request_users' && !empty($setting->attribute_value)){
       
		$userArray = explode('|',$setting->attribute_value);
		$userNames = array();
        foreach($userArray as $key => $val){
			if(array_key_exists($val,$users)){
				$userNames[] = $users[$val];
			} 
            
         }
        $userValue = implode(", ",$userNames);
	}else{
		$userValue = strip_tags($setting->attribute_value);
	}
	?>
	<tr>
		 <td><?= $this->Number->format($setting->id) ?></td>
		 <?php
		 if(!empty($setting->comment)){
			$comment = $setting->comment;	
		 }else{
			$comment = "--";
		 }
		 
		 ?>
		 <td title = "<?php echo $comment; ?>"><?= h($setting->attribute_name) ?></td>
		<td><?php  $truncated =  Text::truncate(
                             $userValue,
                             30,
                             [
                                 'ellipsis' => '...',
                                 'exact' => false
                             ]
                         );
        if(strlen($userValue)>30){
         echo "<a href = \"\" title = \"$userValue\" alt = \"$userValue\">$truncated</a>";
        }else{
            echo $userValue;
        }
         ?>&nbsp;</td>
         <?php
				$truncated_cmt =  Text::truncate(
                             $comment,
                             30,
                             [
                                 'ellipsis' => '...',
                                 'exact' => false
                             ]
                         );
				if(strlen($comment)>30){
					$truncated_cmt =  "<a href = \"\" title = \"$comment\" alt = \"$comment\">$truncated_cmt</a>";
				}else{
					 $truncated_cmt;
				}
				
		 ?>
		
		<td><?php echo $truncated_cmt; ?>&nbsp;</td>		
		<td><?php echo date('jS M, Y g:i A',strtotime($setting->modified)); ?></td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), ['action' => 'view', $setting->id]); ?>
			<?php echo $this->Html->link(__('Edit'), ['action' => 'edit', $setting->id]); ?>
			<?php //echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $setting['id']], ['confirm' => __('Are you sure you want to delete # %s?', $setting['id'])]);
			?>
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
		<li><?php echo $this->Html->link(__('New Setting'), ['action' => 'add']); ?></li>
	</ul>
</div>
