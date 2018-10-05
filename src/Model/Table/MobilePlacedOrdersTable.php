<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MobilePlacedOrders Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 * @property \Cake\ORM\Association\BelongsTo $KioskPlacedOrders
 *
 * @method \App\Model\Entity\MobilePlacedOrder get($primaryKey, $options = [])
 * @method \App\Model\Entity\MobilePlacedOrder newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\MobilePlacedOrder[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MobilePlacedOrder|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MobilePlacedOrder patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MobilePlacedOrder[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\MobilePlacedOrder findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MobilePlacedOrdersTable extends Table
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

        $this->table('mobile_placed_orders');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Kiosks', [
            'foreignKey' => 'kiosk_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('KioskPlacedOrders', [
            'foreignKey' => 'kiosk_placed_order_id',
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
            ->integer('imei')
            ->requirePresence('imei', 'create')
            ->notEmpty('imei');

        $validator
            ->integer('brand')
            ->requirePresence('brand', 'create')
            ->notEmpty('brand');

        $validator
            ->integer('model')
            ->requirePresence('model', 'create')
            ->notEmpty('model');

        $validator
            ->integer('ntework')
            ->requirePresence('ntework', 'create')
            ->notEmpty('ntework');

        $validator
            ->integer('sold_by')
            ->requirePresence('sold_by', 'create')
            ->notEmpty('sold_by');

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
        $rules->add($rules->existsIn(['kiosk_placed_order_id'], 'KioskPlacedOrders'));

        return $rules;
    }
}
