<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * OnDemandPlacedOrders Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $KioskPlacedOrders
 *
 * @method \App\Model\Entity\OnDemandPlacedOrder get($primaryKey, $options = [])
 * @method \App\Model\Entity\OnDemandPlacedOrder newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\OnDemandPlacedOrder[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\OnDemandPlacedOrder|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OnDemandPlacedOrder patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\OnDemandPlacedOrder[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\OnDemandPlacedOrder findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class OnDemandPlacedOrdersTable extends Table
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

        $this->table('on_demand_placed_orders');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Kiosks', [
            'foreignKey' => 'kiosk_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('KioskPlacedOrders', [
            'foreignKey' => 'kiosk_placed_order_id'
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
            ->integer('status')
            ->requirePresence('status', 'create')
            ->notEmpty('status');

        $validator
            ->dateTime('dispatched_on')
            ->requirePresence('dispatched_on', 'create')
            ->notEmpty('dispatched_on');

        $validator
            ->dateTime('received_on')
            ->requirePresence('received_on', 'create')
            ->notEmpty('received_on');

        $validator
            ->integer('received_by')
            ->requirePresence('received_by', 'create')
            ->notEmpty('received_by');

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
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['kiosk_placed_order_id'], 'KioskPlacedOrders'));

        return $rules;
    }
}
