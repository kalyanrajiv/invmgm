<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class MobileRepairsTable extends Table
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

        $this->table('mobile_repairs');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Kiosks', [
            'foreignKey' => 'kiosk_id'
        ]);
        //$this->belongsTo('RetailCustomers', [
        //    'foreignKey' => 'retail_customer_id',
        //    'joinType' => 'INNER'
        //]);
        $this->belongsTo('Brands', [
            'foreignKey' => 'brand_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('MobileModels', [
            'foreignKey' => 'mobile_model_id',
            'joinType' => 'INNER'
        ]);
        $this->hasMany('CommentMobileRepairs', [
            'foreignKey' => 'mobile_repair_id'
        ]);
        $this->hasMany('MobileRepairLogs', [
            'foreignKey' => 'mobile_repair_id'
        ]);
        $this->hasMany('MobileRepairParts', [
            'foreignKey' => 'mobile_repair_id'
        ]);
        $this->hasMany('MobileRepairSales', [
            'foreignKey' => 'mobile_repair_id'
        ]);
        $this->hasMany('RepairPayments', [
            'foreignKey' => 'mobile_repair_id'
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
            ->integer('repair_number')
            ->requirePresence('repair_number', 'create')
            ->notEmpty('repair_number');

        $validator
            ->integer('booked_by')
            ->allowEmpty('booked_by');

        $validator
            ->integer('delivered_by')
            ->allowEmpty('delivered_by');

        //$validator
        //    ->requirePresence('problem_type', 'create')
        //    ->notEmpty('problem_type');

        $validator
            ->allowEmpty('mobile_condition');

        $validator
            ->allowEmpty('mobile_condition_remark');

        $validator
            ->allowEmpty('function_condition');

        $validator
            ->requirePresence('imei', 'create')
            ->notEmpty('imei');

        $validator
            ->dateTime('received_at')
            ->requirePresence('received_at', 'create')
            ->notEmpty('received_at');

        $validator
            ->dateTime('delivered_at')
            ->requirePresence('delivered_at', 'create')
            ->notEmpty('delivered_at');

        $validator
            ->requirePresence('brief_history', 'create')
            ->notEmpty('brief_history');

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
            ->notEmpty('phone_password');

        $validator
            ->requirePresence('estimated_cost', 'create')
            ->notEmpty('estimated_cost');

        $validator
            ->numeric('actual_cost')
            ->requirePresence('actual_cost', 'create')
            ->notEmpty('actual_cost');

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
            ->integer('internal_repair')
            ->allowEmpty('internal_repair');

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
       // $rules->add($rules->existsIn(['retail_customer_id'], 'RetailCustomers'));
        $rules->add($rules->existsIn(['brand_id'], 'Brands'));
        $rules->add($rules->existsIn(['mobile_model_id'], 'MobileModels'));

        return $rules;
    }
}
