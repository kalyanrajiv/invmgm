<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * OrderDisputes Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 * @property \Cake\ORM\Association\BelongsTo $KioskOrders
 * @property \Cake\ORM\Association\BelongsTo $Products
 *
 * @method \App\Model\Entity\OrderDispute get($primaryKey, $options = [])
 * @method \App\Model\Entity\OrderDispute newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\OrderDispute[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\OrderDispute|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OrderDispute patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\OrderDispute[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\OrderDispute findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class OrderDisputesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('order_disputes');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Kiosks', [
            'foreignKey' => 'kiosk_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('KioskOrders', [
            'foreignKey' => 'kiosk_order_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Products', [
            'foreignKey' => 'product_id',
            'joinType' => 'INNER'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->integer('receiving_status')
            ->requirePresence('receiving_status', 'create')
            ->notEmpty('receiving_status');

        $validator
            ->integer('disputed_by')
            ->requirePresence('disputed_by', 'create')
            ->notEmpty('disputed_by');

        $validator
            ->integer('quantity')
            ->requirePresence('quantity', 'create')
            ->notEmpty('quantity');

        $validator
            ->requirePresence('kiosk_user_remarks', 'create')
            ->notEmpty('kiosk_user_remarks');

        $validator
            ->requirePresence('admin_remarks', 'create')
            ->notEmpty('admin_remarks');

        $validator
            ->integer('approval_status')
            ->requirePresence('approval_status', 'create')
            ->notEmpty('approval_status');

        $validator
            ->integer('approval_by')
            ->requirePresence('approval_by', 'create')
            ->notEmpty('approval_by');

        $validator
            ->dateTime('admin_acted')
            ->requirePresence('admin_acted', 'create')
            ->notEmpty('admin_acted');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['kiosk_id'], 'Kiosks'));
        $rules->add($rules->existsIn(['kiosk_order_id'], 'KioskOrders'));
        $rules->add($rules->existsIn(['product_id'], 'Products'));

        return $rules;
    }
}
