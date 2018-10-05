<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CreditReceipts Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Customers
 * @property \Cake\ORM\Association\HasMany $CreditPaymentDetails
 * @property \Cake\ORM\Association\HasMany $CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $FaultyProducts
 * @property \Cake\ORM\Association\HasMany $Kiosk10CreditPaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk10CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk11CreditPaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk11CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk19CreditPaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk19CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk1CreditPaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk1CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk20CreditPaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk20CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk21CreditPaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk21CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk3CreditPaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk5CreditPaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk5CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk7CreditPaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk7CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk8CreditPaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk8CreditProductDetails
 * @property \Cake\ORM\Association\HasMany $KioskFaultyProductDetails
 *
 * @method \App\Model\Entity\CreditReceipt get($primaryKey, $options = [])
 * @method \App\Model\Entity\CreditReceipt newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\CreditReceipt[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CreditReceipt|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CreditReceipt patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CreditReceipt[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\CreditReceipt findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CreditReceiptsTable extends Table
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

        $this->table('credit_receipts');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Customers', [
            'foreignKey' => 'customer_id',
            'joinType' => 'INNER'
        ]);
        $this->hasMany('CreditPaymentDetails', [
            'foreignKey' => 'credit_receipt_id'
        ]);
        $this->hasMany('CreditProductDetails', [
            'foreignKey' => 'credit_receipt_id'
        ]);
        $this->hasMany('FaultyProducts', [
            'foreignKey' => 'credit_receipt_id'
        ]);
        $this->hasMany('Kiosk10CreditPaymentDetails', [
            'foreignKey' => 'credit_receipt_id'
        ]);
        $this->hasMany('Kiosk10CreditProductDetails', [
            'foreignKey' => 'credit_receipt_id'
        ]);
        $this->hasMany('Kiosk11CreditPaymentDetails', [
            'foreignKey' => 'credit_receipt_id'
        ]);
        $this->hasMany('Kiosk11CreditProductDetails', [
            'foreignKey' => 'credit_receipt_id'
        ]);
        $this->hasMany('Kiosk19CreditPaymentDetails', [
            'foreignKey' => 'credit_receipt_id'
        ]);
        $this->hasMany('Kiosk19CreditProductDetails', [
            'foreignKey' => 'credit_receipt_id'
        ]);
        $this->hasMany('Kiosk1CreditPaymentDetails', [
            'foreignKey' => 'credit_receipt_id'
        ]);
        $this->hasMany('Kiosk1CreditProductDetails', [
            'foreignKey' => 'credit_receipt_id'
        ]);
        $this->hasMany('Kiosk20CreditPaymentDetails', [
            'foreignKey' => 'credit_receipt_id'
        ]);
        $this->hasMany('Kiosk20CreditProductDetails', [
            'foreignKey' => 'credit_receipt_id'
        ]);
        $this->hasMany('Kiosk21CreditPaymentDetails', [
            'foreignKey' => 'credit_receipt_id'
        ]);
        $this->hasMany('Kiosk21CreditProductDetails', [
            'foreignKey' => 'credit_receipt_id'
        ]);
        $this->hasMany('Kiosk3CreditPaymentDetails', [
            'foreignKey' => 'credit_receipt_id'
        ]);
        $this->hasMany('Kiosk5CreditPaymentDetails', [
            'foreignKey' => 'credit_receipt_id'
        ]);
        $this->hasMany('Kiosk5CreditProductDetails', [
            'foreignKey' => 'credit_receipt_id'
        ]);
        $this->hasMany('Kiosk7CreditPaymentDetails', [
            'foreignKey' => 'credit_receipt_id'
        ]);
        $this->hasMany('Kiosk7CreditProductDetails', [
            'foreignKey' => 'credit_receipt_id'
        ]);
        $this->hasMany('Kiosk8CreditPaymentDetails', [
            'foreignKey' => 'credit_receipt_id'
        ]);
        $this->hasMany('Kiosk8CreditProductDetails', [
            'foreignKey' => 'credit_receipt_id'
        ]);
        $this->hasMany('KioskFaultyProductDetails', [
            'foreignKey' => 'credit_receipt_id'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    //public function validationDefault(Validator $validator)
    //{
    //    $validator
    //        ->integer('id')
    //        ->allowEmpty('id', 'create');
    //
    //    $validator
    //        ->allowEmpty('fname');
    //
    //    $validator
    //        ->allowEmpty('lname');
    //
    //    $validator
    //        ->email('email')
    //        ->requirePresence('email', 'create')
    //        ->notEmpty('email');
    //
    //    $validator
    //        ->allowEmpty('mobile');
    //
    //    $validator
    //        ->allowEmpty('address_1');
    //
    //    $validator
    //        ->allowEmpty('address_2');
    //
    //    $validator
    //        ->allowEmpty('city');
    //
    //    $validator
    //        ->allowEmpty('state');
    //
    //    $validator
    //        ->allowEmpty('zip');
    //
    //    $validator
    //        ->numeric('credit_amount')
    //        ->requirePresence('credit_amount', 'create')
    //        ->notEmpty('credit_amount');
    //
    //    $validator
    //        ->integer('bulk_discount')
    //        ->requirePresence('bulk_discount', 'create')
    //        ->notEmpty('bulk_discount');
    //
    //    $validator
    //        ->integer('processed_by')
    //        ->requirePresence('processed_by', 'create')
    //        ->notEmpty('processed_by');
    //
    //    $validator
    //        ->integer('status')
    //        ->requirePresence('status', 'create')
    //        ->notEmpty('status');
    //
    //    return $validator;
    //}

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    //public function buildRules(RulesChecker $rules)
    //{
    //    $rules->add($rules->isUnique(['email']));
    //    $rules->add($rules->existsIn(['customer_id'], 'Customers'));
    //
    //    return $rules;
    //}
}
