<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

 
class MobileReSalesTable extends Table
{

    
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('mobile_re_sales');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        //$this->belongsTo('Sales', [
        //    'foreignKey' => 'sale_id'
        //]);
        $this->belongsTo('MobilePurchases', [
            'foreignKey' => 'mobile_purchase_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Kiosks', [
            'foreignKey' => 'kiosk_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Brands', [
            'foreignKey' => 'brand_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('MobileModels', [
            'foreignKey' => 'mobile_model_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Networks', [
            'foreignKey' => 'network_id'
        ]);
        $this->belongsTo('RetailCustomers', [
            'foreignKey' => 'retail_customer_id',
            'joinType' => 'INNER'
        ]);
        $this->hasMany('CommentMobileReSales', [
            'foreignKey' => 'mobile_re_sale_id'
        ]);
        $this->hasMany('MobileReSalePayments', [
            'foreignKey' => 'mobile_re_sale_id'
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

        //$validator
        //    ->requirePresence('color', 'create')
        //    ->notEmpty('color');
        //
        //$validator
        //    ->requirePresence('imei', 'create')
        //    ->notEmpty('imei');

        //$validator
        //    ->requirePresence('brief_history', 'create')
        //    ->notEmpty('brief_history');

        $validator
            ->requirePresence('customer_fname', 'create')
            ->notEmpty('customer_fname');

        //$validator
        //    ->requirePresence('customer_lname', 'create')
        //    ->notEmpty('customer_lname');

        //$validator
        //    ->requirePresence('customer_email', 'create')
        //    ->notEmpty('customer_email');

        $validator
            ->requirePresence('customer_contact', 'create')
            ->notEmpty('customer_contact','Only numbers are allowed');

        $validator
            ->requirePresence('customer_address_1', 'create')
            ->notEmpty('customer_address_1');

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
            ->integer('type')
            ->requirePresence('type', 'create')
            ->notEmpty('type');

        $validator
            ->integer('grade')
            ->requirePresence('grade', 'create')
            ->notEmpty('grade');

        $validator
            ->numeric('cost_price')
            ->requirePresence('cost_price', 'create')
            ->notEmpty('cost_price');

        $validator
            ->numeric('selling_price')
            ->requirePresence('selling_price', 'create')
            ->notEmpty('selling_price');

        $validator
            ->numeric('discounted_price')
            ->allowEmpty('discounted_price');

        $validator
            ->integer('discount')
            ->allowEmpty('discount');

        $validator
            ->dateTime('selling_date')
            ->requirePresence('selling_date', 'create')
            ->notEmpty('selling_date');

        $validator
            ->requirePresence('zip', 'create')
            ->notEmpty('zip');

        $validator
            ->numeric('refund_price')
            ->requirePresence('refund_price', 'create')
            ->notEmpty('refund_price');

        $validator
            ->numeric('refund_gain')
            ->requirePresence('refund_gain', 'create')
            ->notEmpty('refund_gain');

        $validator
            ->integer('refund_by')
            ->requirePresence('refund_by', 'create')
            ->notEmpty('refund_by');

        $validator
            ->integer('refund_status')
            ->allowEmpty('refund_status');

        $validator
            ->requirePresence('refund_remarks', 'create')
            ->notEmpty('refund_remarks');

        $validator
            ->dateTime('refund_date')
            ->allowEmpty('refund_date');

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
        //$rules->add($rules->existsIn(['sale_id'], 'Sales'));
        $rules->add($rules->existsIn(['mobile_purchase_id'], 'MobilePurchases'));
        //$rules->add($rules->existsIn(['kiosk_id'], 'Kiosks'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['brand_id'], 'Brands'));
        $rules->add($rules->existsIn(['mobile_model_id'], 'MobileModels'));
        $rules->add($rules->existsIn(['network_id'], 'Networks'));
       // $rules->add($rules->existsIn(['retail_customer_id'], 'RetailCustomers'));

        return $rules;
    }
}
