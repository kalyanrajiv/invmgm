<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

 
class UsersTable extends Table
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

        $this->table('users');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
		$this->addBehavior('Acl.Acl', ['type' => 'requester']);

        $this->belongsTo('Groups', [
            'foreignKey' => 'group_id',
            'joinType' => 'INNER'
        ]);
        
        // $this->belongsTo('Attachments', [
        //    'foreignKey' => 'id',
        //    'joinType' => 'INNER'
        //]);
            
        $this->hasMany('CenterOrders', [
            'foreignKey' => 'user_id'
        ]);
        
         $this->hasMany('Attachments', [
            'foreignKey' => 'foreign_key'
        ]);
         
        $this->hasMany('CommentMobilePurchases', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('CommentMobileReSales', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('CommentMobileRepairs', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('CommentMobileUnlocks', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Comments', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('CsvProducts', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('DailyTargets', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('DefectiveBinReferences', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('DefectiveBinTransients', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('DefectiveCentralProducts', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('DefectiveKioskProducts', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('DefectiveKioskReferences', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('ImportOrderReferences', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('InvoiceOrders', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Kiosk10InvoiceOrders', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Kiosk10Products', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Kiosk11InvoiceOrders', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Kiosk11Products', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Kiosk17Products', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Kiosk18Products', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Kiosk19InvoiceOrders', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Kiosk1Products', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Kiosk20InvoiceOrders', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Kiosk21InvoiceOrders', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Kiosk22Products', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Kiosk2Products', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Kiosk5InvoiceOrders', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Kiosk5Products', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Kiosk7InvoiceOrders', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Kiosk7Products', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Kiosk8InvoiceOrders', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Kiosk8Products', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('KioskOrders', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('KioskPlacedOrders', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Messages', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('MobileBlkReSalePayments', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('MobileBlkReSales', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('MobilePayments', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('MobilePrices', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('MobilePurchases', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('MobileReSalePayments', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('MobileReSales', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('MobileRepairLogs', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('MobileRepairParts', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('MobileTransferLogs', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('MobileUnlockLogs', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('OnDemandOrders', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Posts', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('ProductPayments', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Products', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Profiles', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('RepairLog', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('RepairPayments', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('RevertStocks', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('SessionBackups', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('StockTakingDetails', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('StockTakingReferences', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('TempProductDetails', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('TempProductOrders', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('UnderstockLevelOrders', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('UnlockPayments', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('UserAttendances', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('UserMessges', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('WarehouseStock', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('ProductSellStat', [
            'foreignKey' => 'user_id'
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
        //    ->requirePresence('f_name', 'create')
        //    ->notEmpty('f_name');
        //
        //$validator
        //    ->requirePresence('l_name', 'create')
        //    ->notEmpty('l_name');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmpty('email')
            ->add('email', 'unique', ['rule' => 'validateUnique', 'provider' => 'table','message' => 'Email is already in use']);
        $validator
            ->requirePresence('username', 'create')
            ->notEmpty('username',['message' => 'Username required'])
            ->add('username', 'length', ['rule' => ['minLength', 4],'message' => 'Username must be at least 4 characters long'])
            ->add('username', 'unique', ['rule' => 'validateUnique', 'provider' => 'table','message' => 'Username is not available']);

        $validator
            ->requirePresence('password', 'create')
            ->add('password', 'length', ['rule' => ['lengthBetween', 8, 100],'message' => 'Your password must be between 8 and 40 characters'])
            ->notEmpty('password');
            
        $validator
            ->requirePresence('confirm_password', 'create')
            ->add('confirm_password', 'length', ['rule' => ['lengthBetween', 8, 100],'message' => 'Your password must be between 8 and 40 characters'])
            ->notEmpty('confirm_password',['message' => 'confirm_password required']);    

        $validator
            ->requirePresence('mobile', 'create')
            ->notEmpty('mobile');

        $validator
            ->requirePresence('group_id', 'create')
            ->notEmpty('group_id');

         $validator
            ->date('date_of_birth')
            ->notEmpty('date_of_birth');
        
          
        //  $validator->add('national_insurance',[
        //        
        //        'message'=>'national_insurance'
        //         ]
        //);
      $validator
          ->notEmpty("national_insurance","national_insurance cannot be empty.");
        //    ->requirePresence('national_insurance', 'create')
        //    ->notEmpty('national_insurance');
        //$validator
        //    ->requirePresence('visa_type', 'create')
        //    ->notEmpty('visa_type');
        //
        $validator
            ->date('visa_expiry_date')
          ->notEmpty('visa_expiry_date');
        
        $validator
              ->notEmpty('memo','Pls Enter the Memo');
        //$validator
        //    ->requirePresence('address_1', 'create')
        //    ->notEmpty('address_1');
        //
        //$validator
        //    ->requirePresence('address_2', 'create')
        //    ->notEmpty('address_2');
        //
        //$validator
        //    ->requirePresence('city', 'create')
        //    ->notEmpty('city');
        //
        //$validator
        //    ->requirePresence('state', 'create')
        //    ->notEmpty('state');

        //$validator
        //    ->requirePresence('country', 'create')
        //    ->notEmpty('country');
        //
        //$validator
        //    ->requirePresence('zip', 'create')
        //    ->notEmpty('zip');
        //
        //$validator
        //    ->date('start_from')
        //    ->allowEmpty('start_from');
        //
        //$validator
        //    ->integer('active')
        //    ->allowEmpty('active');
        
        

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
        $rules->add($rules->isUnique(['email']));
        $rules->add($rules->isUnique(['username']));
        $rules->add($rules->existsIn(['group_id'], 'Groups'));
  

        return $rules;
    }
    
     protected function _setPassword($password)
    {
        return (new DefaultPasswordHasher)->hash($password);
    }
}
