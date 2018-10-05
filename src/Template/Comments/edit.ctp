<?php
/**
  * @var \App\View\AppView $this
  */
?>
 
 <div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		 <li class="heading"><?= __('Actions') ?></li>
         <li><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $comment->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $comment->id)]
            )
        ?></li>
        <li><?= $this->Html->link(__('List Comments'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Posts'), ['controller' => 'Posts', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Post'), ['controller' => 'Posts', 'action' => 'add']) ?></li>
        
	</ul>
</div>
 
<div class="brands form large-9 medium-8 columns content">
    <?= $this->Form->create($comment) ?>
    <fieldset>
        <legend><?= __('Edit Comment') ?></legend>
        <?php
           echo $this->Form->input('user_id', ['options' => $users]);
            echo $this->Form->input('post_id', ['options' => $posts]);
            echo $this->Form->input('comments');
            echo $this->Form->input('status', ['options' => $active]);
            
        ?>
    </fieldset>
     <?= $this->Form->button(__('Submit'),array('name'=>'submit','style'=>"width: 108px;height: 46px;")) ?>
    <?= $this->Form->end() ?>
</div>
