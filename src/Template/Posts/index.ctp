<?php
/**
  * @var \App\View\AppView $this
  */
?>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?= $this->Html->link(__('New Post'), ['action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Comments'), ['controller' => 'Comments', 'action' => 'index']) ?></li>
       
	</ul>
</div>
 
<div class="posts index large-9 medium-8 columns content">
    <h3><?= __('Posts') ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('user_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('title') ?></th>
                <th scope="col"><?= $this->Paginator->sort('created') ?></th>
                <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($posts as $post): ?>
            <tr>
                <td><?= $this->Number->format($post->id) ?></td>
                <td><?= $post->has('user') ? $this->Html->link($post->user['username'], ['controller' => 'Users', 'action' => 'view', $post->user->id]) : '' ?></td>
                <td><?= h($post->title) ?></td>
                <td><?= date('d-m-y g:i A',strtotime($post->created)); ?></td>
                <td><?= date('d-m-y g:i A',strtotime($post->modified));  ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $post->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $post->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $post->id], ['confirm' => __('Are you sure you want to delete # {0}?', $post->id)]) ?>
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
