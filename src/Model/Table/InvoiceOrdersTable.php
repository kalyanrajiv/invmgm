<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * InvoiceOrders Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $Customers
 * @property \Cake\ORM\Association\HasMany $InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk10000PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk10InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk10PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk11InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk11PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk12PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk13PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk14PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk15PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk16PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk17PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk18PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk19InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk19PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk1PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk20InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk20PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk21InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk21PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk22PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk2PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk3PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk4PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk5InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk5PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk7InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk7PaymentDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk8InvoiceOrderDetails
 * @property \Cake\ORM\Association\HasMany $Kiosk8PaymentDetails
 * @property \Cake\ORM\Association\HasMany $PaymentDetails
 * @property \Cake\ORM\Association\HasMany $TPaymentDetails
 *
 * @method \App\Model\Entity\InvoiceOrder get($primaryKey, $options = [])
 * @method \App\Model\Entity\InvoiceOrder newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\InvoiceOrder[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\InvoiceOrder|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\InvoiceOrder patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\InvoiceOrder[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\InvoiceOrder findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class InvoiceOrdersTable extends Table
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

        $this->table('invoice_orders');
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
        $this->belongsTo('Customers', [
            'foreignKey' => 'customer_id'
        ]);
        $this->hasMany('InvoiceOrderDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk10000PaymentDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk10InvoiceOrderDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk10PaymentDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk11InvoiceOrderDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk11PaymentDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk12PaymentDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk13PaymentDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk14PaymentDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk15PaymentDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk16PaymentDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk17PaymentDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk18PaymentDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk19InvoiceOrderDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk19PaymentDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk1PaymentDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk20InvoiceOrderDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk20PaymentDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk21InvoiceOrderDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk21PaymentDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk22PaymentDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk2PaymentDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk3PaymentDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk4PaymentDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk5InvoiceOrderDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk5PaymentDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk7InvoiceOrderDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk7PaymentDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk8InvoiceOrderDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('Kiosk8PaymentDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('PaymentDetails', [
            'foreignKey' => 'invoice_order_id'
        ]);
        $this->hasMany('TPaymentDetails', [
            'foreignKey' => 'invoice_order_id'
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
    //        ->requirePresence('fname', 'create')
    //        ->notEmpty('fname');
    //
    //    $validator
    //        ->requirePresence('lname', 'create')
    //        ->notEmpty('lname');
    //
    //    $validator
    //        ->email('email')
    //        ->requirePresence('email', 'create')
    //        ->notEmpty('email');
    //
    //    $validator
    //        ->requirePresence('mobile', 'create')
    //        ->notEmpty('mobile');
    //
    //    $validator
    //        ->numeric('bulk_discount')
    //        ->allowEmpty('bulk_discount');
    //
    //    $validator
    //        ->requirePresence('del_city', 'create')
    //        ->notEmpty('del_city');
    //
    //    $validator
    //        ->requirePresence('del_state', 'create')
    //        ->notEmpty('del_state');
    //
    //    $validator
    //        ->requirePresence('del_zip', 'create')
    //        ->notEmpty('del_zip');
    //
    //    $validator
    //        ->requirePresence('del_address_1', 'create')
    //        ->notEmpty('del_address_1');
    //
    //    $validator
    //        ->requirePresence('del_address_2', 'create')
    //        ->notEmpty('del_address_2');
    //
    //    $validator
    //        ->integer('invoice_status')
    //        ->requirePresence('invoice_status', 'create')
    //        ->notEmpty('invoice_status');
    //
    //    $validator
    //        ->integer('status')
    //        ->requirePresence('status', 'create')
    //        ->notEmpty('status');
    //
    //    $validator
    //        ->numeric('amount')
    //        ->requirePresence('amount', 'create')
    //        ->notEmpty('amount');
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
    //    $rules->add($rules->existsIn(['kiosk_id'], 'Kiosks'));
    //    $rules->add($rules->existsIn(['user_id'], 'Users'));
    //    $rules->add($rules->existsIn(['customer_id'], 'Customers'));
    //
    //    return $rules;
    //}
}
