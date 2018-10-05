<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TransientStock Model
 *
 * @property \Cake\ORM\Association\BelongsTo $KioskOrders
 * @property \Cake\ORM\Association\BelongsTo $Products
 *
 * @method \App\Model\Entity\TransientStock get($primaryKey, $options = [])
 * @method \App\Model\Entity\TransientStock newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\TransientStock[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TransientStock|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\TransientStock patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\TransientStock[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\TransientStock findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TransientStockTable extends Table
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

        $this->table('transient_stock');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

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
            ->integer('quanities_sent')
            ->requirePresence('quanities_sent', 'create')
            ->notEmpty('quanities_sent');

        $validator
            ->integer('quantities_received')
            ->requirePresence('quantities_received', 'create')
            ->notEmpty('quantities_received');

        $validator
            ->numeric('cost_price')
            ->requirePresence('cost_price', 'create')
            ->notEmpty('cost_price');

        $validator
            ->numeric('selling_price')
            ->requirePresence('selling_price', 'create')
            ->notEmpty('selling_price');

        $validator
            ->requirePresence('remarks', 'create')
            ->notEmpty('remarks');

        $validator
            ->integer('status')
            ->requirePresence('status', 'create')
            ->notEmpty('status');

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
        $rules->add($rules->existsIn(['kiosk_order_id'], 'KioskOrders'));
        $rules->add($rules->existsIn(['product_id'], 'Products'));

        return $rules;
    }
}
