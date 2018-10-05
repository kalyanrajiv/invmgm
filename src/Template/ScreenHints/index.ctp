
<div class="settings index">
	
	<h2><?php echo __('Screen Hints'); ?></h2>
	 
	 
	 
	<span class='paging' style='text-align:right;float:right;margin-top: -15px;'>
			<?php
				echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
				echo $this->Paginator->numbers(array('separator' => ''));
				echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
			?>
	</span>
	 
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('controller'); ?></th>
			<th><?php echo $this->Paginator->sort('action'); ?></th>
			<th><?php echo $this->Paginator->sort('hint'); ?></th>
			<th><?php echo $this->Paginator->sort('description','URL'); ?></th>			 
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($hints as $hint):
    //pr($hint);die;
		$truncatedHint = \Cake\Utility\Text::truncate(
                                                        strip_tags($hint['hint']),
                                                        30,
                                                        [
                                                            'ellipsis' => '...',
                                                            'exact' => false
                                                        ]
                                                    );
		$screenURL = $hint['description'];
		$description = str_replace("http://hpwaheguru.co.uk","",$hint['description']);
		$description = str_replace("mbwaheguru.co.uk","",$description);
	?>
	<tr> 
		<td><?php echo h($hint['controller']); ?>&nbsp;</td>
		<td><?php echo h($hint['action']); ?>&nbsp;</td>
		<td><?php echo $truncatedHint; ?>&nbsp;</td>
        <?php
        //echo 'hi'.$description;echo'<br>';die;
        //pr($screenURL);die;?>
        <td><?php echo "<a href='{$screenURL}'>$description</a>"; ?>&nbsp;</td><?php //die;?>
		 <?php echo $this->Time->format('jS M, Y g:i A',$hint['modified'],null,null); ?> 
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('controller' => 'screen_hints','action' => 'view', $hint['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('controller' => 'screen_hints','action' => 'edit', $hint['id'])); ?>
			<?php #echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $setting['Setting']['id']), array(), __('Are you sure you want to delete # %s?', $setting['Setting']['id']));
			?>
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
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Screen Hint'), array('action' => 'add')); ?></li>
	</ul>
</div>
