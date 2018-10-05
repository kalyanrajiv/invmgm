 
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
    <ul>
        
        <li><?= $this->Html->link(__('New Problem Type'), ['action' => 'add']) ?></li>
    </ul>
</div>
<div class="problemTypes index large-9 medium-8 columns content">
    <h2><?= __('Problem Types') ?></h2>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('problem_type') ?></th>
                <th scope="col"><?= $this->Paginator->sort('description') ?></th>
                <th scope="col"><?= $this->Paginator->sort('status') ?></th>
                <th scope="col"><?= $this->Paginator->sort('created') ?></th>
                <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($problemTypes as $problemType): ?>
            <tr>
                <td><?= $this->Number->format($problemType->id) ?></td>
                <td><?= h($problemType->problem_type) ?></td>
                <td><?=  $problemType->description  ?></td>
                <td><?= $statusOptions[$this->Number->format($problemType->status)] ?></td>
                		 <td><?php echo date('jS M, Y g:i A',strtotime($problemType->created));?></td>&nbsp; 
		<td><?php echo date('jS M, Y g:i A',strtotime($problemType->modified));//$this->Time->format('jS M, Y g:i A',$mobilecondition['modified'] ,null,null); ?>&nbsp;</td>
        
                 
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $problemType->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $problemType->id]) ?>
                   
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
