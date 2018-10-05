 
<?php
//pr($activeOptions);die;
/**
  * @var \App\View\AppView $this
  */
?>

<div class="brands form large-9 medium-8 columns content">
    <?= $this->Form->create($agent) ?>
    <fieldset>
        <legend><?= __('Edit Account Manager') ?></legend>
        <?php
           echo $this->Form->input('name');
            echo $this->Form->input('memo');
            echo $this->Form->input('status',['options' => $activeOptions,'type' => 'select']);
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit'),array('name'=>'submit','style'=>"width: 108px;height: 46px;")) ?>
    <?= $this->Form->end() ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $agent->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $agent->id)]
            )
        ?></li>
        <li><?= $this->Html->link(__('List Account Manager'), ['action' => 'index']) ?></li>
        
	</ul>
</div>
