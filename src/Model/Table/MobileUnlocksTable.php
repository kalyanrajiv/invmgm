<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
 
class MobileUnlocksTable extends Table
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

        $this->table('mobile_unlocks');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Kiosks', [
            'foreignKey' => 'kiosk_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('RetailCustomers', [
            'foreignKey' => 'retail_customer_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Brands', [
            'foreignKey' => 'brand_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Networks', [
            'foreignKey' => 'network_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('MobileModels', [
            'foreignKey' => 'mobile_model_id'
        ]);
        $this->hasMany('CommentMobileUnlocks', [
            'foreignKey' => 'mobile_unlock_id'
        ]);
        $this->hasMany('MobileUnlockLogs', [
            'foreignKey' => 'mobile_unlock_id'
        ]);
        $this->hasMany('MobileUnlockSales', [
            'foreignKey' => 'mobile_unlock_id'
        ]);
        $this->hasMany('UnlockPayments', [
            'foreignKey' => 'mobile_unlock_id'
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
            ->integer('unlock_number')
            ->requirePresence('unlock_number', 'create')
            ->notEmpty('unlock_number');

        $validator
            ->allowEmpty('token');

        $validator
            ->allowEmpty('unlock_code_instructions');

        $validator
            ->integer('booked_by')
            ->allowEmpty('booked_by');

        $validator
            ->integer('delivered_by')
            ->allowEmpty('delivered_by');

        $validator
            ->requirePresence('imei', 'create')
            ->notEmpty('imei');

        $validator
            ->dateTime('received_at')
            ->requirePresence('received_at', 'create')
            ->notEmpty('received_at');

        //$validator
        //    ->dateTime('delivered_at')
        //    ->requirePresence('delivered_at', 'create')
        //    ->notEmpty('delivered_at');

        //$validator
        //    ->requirePresence('brief_history', 'create')
        //    ->notEmpty('brief_history');

        $validator
            ->requirePresence('customer_fname', 'create')
            ->notEmpty('customer_fname');

        $validator
            ->requirePresence('customer_lname', 'create')
            ->notEmpty('customer_lname');

        //$validator
        //    ->requirePresence('customer_email', 'create')
        //    ->notEmpty('customer_email');

        $validator
            ->requirePresence('customer_contact', 'create')
            ->notEmpty('customer_contact');

        //$validator
        //    ->requirePresence('customer_address_1', 'create')
        //    ->notEmpty('customer_address_1');
        //
        //$validator
        //    ->requirePresence('customer_address_2', 'create')
        //    ->notEmpty('customer_address_2');
        //
        //$validator
        //    ->requirePresence('city', 'create')
        //    ->notEmpty('city');
        //
        //$validator
        //    ->requirePresence('state', 'create')
        //    ->notEmpty('state');
        //
        //$validator
        //    ->requirePresence('country', 'create')
        //    ->notEmpty('country');

        $validator
            ->requirePresence('description', 'create')
            ->notEmpty('description');

        $validator
            ->numeric('estimated_cost')
            ->allowEmpty('estimated_cost');

        $validator
            ->numeric('actual_cost')
            ->allowEmpty('actual_cost');

        $validator
            ->numeric('net_cost')
            ->allowEmpty('net_cost');

        $validator
            ->requirePresence('zip', 'create')
            ->allowEmpty('zip');

        $validator
            ->integer('status')
            ->requirePresence('status', 'create')
            ->notEmpty('status');

        $validator
            ->integer('internal_unlock')
            ->allowEmpty('internal_unlock');

        $validator
            ->integer('status_refund')
            ->allowEmpty('status_refund');

        $validator
            ->integer('status_rebooked')
            ->allowEmpty('status_rebooked');

        $validator
            ->integer('status_freezed')
            ->allowEmpty('status_freezed');

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
        //$rules->add($rules->existsIn(['retail_customer_id'], 'RetailCustomers'));
        $rules->add($rules->existsIn(['brand_id'], 'Brands'));
        $rules->add($rules->existsIn(['network_id'], 'Networks'));
        $rules->add($rules->existsIn(['mobile_model_id'], 'MobileModels'));

        return $rules;
    }
}
