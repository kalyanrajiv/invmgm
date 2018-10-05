<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
 
class MobilePurchasesTable extends Table
{

     
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('mobile_purchases');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
         $this->addBehavior('Josegonzalez/Upload.Upload', [
            'image' => [
                'fields' => [
                    // if these fields or their defaults exist
                    // the values will be set.
                    // 'path' =>'webroot{DS}files{DS}{model}{DS}{field}{DS}{primaryKey}{DS}',
                
                    'dir' => 'image_dir', // defaults to `dir`
                    'size' => 'photo_size', // defaults to `size`
                   'type' => 'photo_type', // defaults to `type`
                ],
                 'thumbnailMethod'  => 'php', //or php
                                                'thumbnailSizes' => [ 
                                                            'xvga' => '1024x768',
                                                            'vga' => '640x480',
                                                            'thumb' => '80x80',
                                                            'mini' => '30x30',
                       ],
												'keepFilesOnDelete' => false,
            ],
        ]);
        $this->belongsTo('Kiosks', [
            'foreignKey' => 'kiosk_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
        //$this->belongsTo('NewKiosks', [
        //    'foreignKey' => 'new_kiosk_id'
        //]);
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
        $this->hasMany('CommentMobilePurchases', [
            'foreignKey' => 'mobile_purchase_id'
        ]);
        $this->hasMany('MobileBlkReSalePayments', [
            'foreignKey' => 'mobile_purchase_id'
        ]);
        $this->hasMany('MobileBlkReSales', [
            'foreignKey' => 'mobile_purchase_id'
        ]);
        $this->hasMany('MobileBlkTransferLogs', [
            'foreignKey' => 'mobile_purchase_id'
        ]);
        $this->hasMany('MobilePayments', [
            'foreignKey' => 'mobile_purchase_id'
        ]);
        $this->hasMany('MobileReSalePayments', [
            'foreignKey' => 'mobile_purchase_id'
        ]);
        $this->hasMany('MobileReSales', [
            'foreignKey' => 'mobile_purchase_id'
        ]);
        $this->hasMany('MobileTransferLogs', [
            'foreignKey' => 'mobile_purchase_id'
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
        //$validator
        //    ->integer('id')
        //    ->allowEmpty('id', 'create');
        //
        //$validator
        //    ->integer('purchase_number')
        //    ->requirePresence('purchase_number', 'create')
        //    ->notEmpty('purchase_number');

        $validator
            ->allowEmpty('mobile_purchase_reference');

        //$validator
        //    ->integer('rand_num')
        //    ->requirePresence('rand_num', 'create')
        //    ->notEmpty('rand_num');

        $validator
            ->integer('purchased_by_kiosk')
            ->requirePresence('purchased_by_kiosk', 'create')
            ->notEmpty('purchased_by_kiosk');

        //$validator
        //    ->allowEmpty('mobile_condition');
        //
        //$validator
        //    ->allowEmpty('mobile_condition_remark');
        //
        //$validator
        //    ->allowEmpty('function_condition');

        $validator
            ->requirePresence('color', 'create')
            ->notEmpty('color');

        $validator
            ->requirePresence('imei', 'create')
            ->notEmpty('imei');
        
        $validator
            ->requirePresence('brand_id', 'create')
            ->notEmpty('brand_id');
            
        $validator
            ->requirePresence('mobile_model_id', 'create')
            ->notEmpty('mobile_model_id');
            
        $validator
            ->requirePresence('serial_number', 'create')
            ->notEmpty('serial_number');  
        
        
        //$validator
        //    ->requirePresence('brief_history', 'create')
        //    ->notEmpty('brief_history');

        $validator
            ->requirePresence('customer_fname', 'create')
            ->notEmpty('customer_fname');

        //$validator
        //    ->requirePresence('customer_lname', 'create')
        //    ->notEmpty('customer_lname');

        $validator
            ->date('date_of_birth')
            ->requirePresence('date_of_birth', 'create')
            ->notEmpty('date_of_birth');

        $validator
            ->requirePresence('customer_email', 'create')
            ->notEmpty('customer_email');

        $validator
            ->requirePresence('customer_contact', 'create')
            ->notEmpty('customer_contact');

        $validator
            ->requirePresence('customer_address_1', 'create')
            ->notEmpty('customer_address_1');

        //$validator
        //    ->requirePresence('customer_address_2', 'create')
        //    ->notEmpty('customer_address_2');

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
            ->requirePresence('customer_identification', 'create')
            ->notEmpty('customer_identification');

        //$validator
        //    ->requirePresence('serial_number', 'create')
        //    ->notEmpty('serial_number');

        $validator
         ->allowEmpty('image');
        //$validator
        //    ->requirePresence('image_dir', 'create')
        //    ->notEmpty('image_dir');

        //$validator
        //    ->requirePresence('photo_type', 'create')
        //    ->notEmpty('photo_type');

        //$validator
        //    ->requirePresence('photo_size', 'create')
        //    ->notEmpty('photo_size');

        //$validator
        //    ->requirePresence('path', 'create')
        //    ->notEmpty('path');

        $validator
            ->requirePresence('description', 'create')
            ->notEmpty('description');

        $validator
            ->numeric('cost_price')
            ->requirePresence('cost_price', 'create')
            ->notEmpty('cost_price');

        //$validator
        //    ->numeric('topedup_price')
        //    ->allowEmpty('topedup_price');
        //
        //$validator
        //    ->requirePresence('grade', 'create')
        //    ->notEmpty('grade');
            
                $validator
            ->requirePresence('brief_history', 'create')
            ->notEmpty('brief_history');    

        //$validator
        //    ->integer('type')
        //    ->requirePresence('type', 'create')
        //    ->notEmpty('type');

        //$validator
        //    ->dateTime('purchasing_date')
        //    ->requirePresence('purchasing_date', 'create')
        //    ->notEmpty('purchasing_date');

        //$validator
        //    ->dateTime('reserve_date')
        //    ->requirePresence('reserve_date', 'create')
        //    ->notEmpty('reserve_date');

        //$validator
        //    ->integer('reserved_by')
        //    ->requirePresence('reserved_by', 'create')
        //    ->notEmpty('reserved_by');

        //$validator
        //    ->dateTime('transient_date')
        //    ->requirePresence('transient_date', 'create')
        //    ->notEmpty('transient_date');

        //$validator
        //    ->integer('transient_by')
        //    ->requirePresence('transient_by', 'create')
        //    ->notEmpty('transient_by');

        $validator
            ->requirePresence('zip', 'create')
            ->notEmpty('zip');
        //
        //$validator
        //    ->integer('receiving_status')
        //    ->requirePresence('receiving_status', 'create')
        //    ->notEmpty('receiving_status');

        //$validator
        //    ->integer('status')
        //    ->requirePresence('status', 'create')
        //    ->notEmpty('status');
        //
        //$validator
        //    ->integer('mobile_status')
        //    ->requirePresence('mobile_status', 'create')
        //    ->notEmpty('mobile_status');
        //
        //$validator
        //    ->integer('purchase_status')
        //    ->allowEmpty('purchase_status');
        //
        //$validator
        //    ->integer('custom_grades')
        //    ->allowEmpty('custom_grades');

        //$validator
        //    ->numeric('selling_price')
        //    ->requirePresence('selling_price', 'create')
        //    ->notEmpty('selling_price');
        //
        //$validator
        //    ->numeric('static_selling_price')
        //    ->allowEmpty('static_selling_price');
        //
        //$validator
        //    ->numeric('lowest_selling_price')
        //    ->requirePresence('lowest_selling_price', 'create')
        //    ->notEmpty('lowest_selling_price');

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
        //$rules->add($rules->existsIn(['kiosk_id'], 'Kiosks'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        //$rules->add($rules->existsIn(['new_kiosk_id'], 'NewKiosks'));
        $rules->add($rules->existsIn(['brand_id'], 'Brands'));
        $rules->add($rules->existsIn(['mobile_model_id'], 'MobileModels'));
        $rules->add($rules->existsIn(['network_id'], 'Networks'));

        return $rules;
    }
}
