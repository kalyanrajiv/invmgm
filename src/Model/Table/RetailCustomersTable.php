<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * RetailCustomers Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 * @property \Cake\ORM\Association\HasMany $Kiosk10ProductReceipts
 * @property \Cake\ORM\Association\HasMany $Kiosk11ProductReceipts
 * @property \Cake\ORM\Association\HasMany $Kiosk12ProductReceipts
 * @property \Cake\ORM\Association\HasMany $Kiosk13ProductReceipts
 * @property \Cake\ORM\Association\HasMany $Kiosk14ProductReceipts
 * @property \Cake\ORM\Association\HasMany $Kiosk15ProductReceipts
 * @property \Cake\ORM\Association\HasMany $Kiosk16ProductReceipts
 * @property \Cake\ORM\Association\HasMany $Kiosk17ProductReceipts
 * @property \Cake\ORM\Association\HasMany $Kiosk18ProductReceipts
 * @property \Cake\ORM\Association\HasMany $Kiosk19ProductReceipts
 * @property \Cake\ORM\Association\HasMany $Kiosk1ProductReceipts
 * @property \Cake\ORM\Association\HasMany $Kiosk20ProductReceipts
 * @property \Cake\ORM\Association\HasMany $Kiosk21ProductReceipts
 * @property \Cake\ORM\Association\HasMany $Kiosk22ProductReceipts
 * @property \Cake\ORM\Association\HasMany $Kiosk2ProductReceipts
 * @property \Cake\ORM\Association\HasMany $Kiosk3ProductReceipts
 * @property \Cake\ORM\Association\HasMany $Kiosk4ProductReceipts
 * @property \Cake\ORM\Association\HasMany $Kiosk5ProductReceipts
 * @property \Cake\ORM\Association\HasMany $Kiosk7ProductReceipts
 * @property \Cake\ORM\Association\HasMany $Kiosk8ProductReceipts
 * @property \Cake\ORM\Association\HasMany $MobileBlkReSales
 * @property \Cake\ORM\Association\HasMany $MobileReSales
 * @property \Cake\ORM\Association\HasMany $MobileRepairs
 * @property \Cake\ORM\Association\HasMany $MobileUnlockSales
 * @property \Cake\ORM\Association\HasMany $MobileUnlocks
 *
 * @method \App\Model\Entity\RetailCustomer get($primaryKey, $options = [])
 * @method \App\Model\Entity\RetailCustomer newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\RetailCustomer[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RetailCustomer|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RetailCustomer patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\RetailCustomer[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\RetailCustomer findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RetailCustomersTable extends Table
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

        $this->table('retail_customers');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Kiosks', [
            'foreignKey' => 'kiosk_id'
        ]);
        $this->hasMany('Kiosk10ProductReceipts', [
            'foreignKey' => 'retail_customer_id'
        ]);
        $this->hasMany('Kiosk11ProductReceipts', [
            'foreignKey' => 'retail_customer_id'
        ]);
        $this->hasMany('Kiosk12ProductReceipts', [
            'foreignKey' => 'retail_customer_id'
        ]);
        $this->hasMany('Kiosk13ProductReceipts', [
            'foreignKey' => 'retail_customer_id'
        ]);
        $this->hasMany('Kiosk14ProductReceipts', [
            'foreignKey' => 'retail_customer_id'
        ]);
        $this->hasMany('Kiosk15ProductReceipts', [
            'foreignKey' => 'retail_customer_id'
        ]);
        $this->hasMany('Kiosk16ProductReceipts', [
            'foreignKey' => 'retail_customer_id'
        ]);
        $this->hasMany('Kiosk17ProductReceipts', [
            'foreignKey' => 'retail_customer_id'
        ]);
        $this->hasMany('Kiosk18ProductReceipts', [
            'foreignKey' => 'retail_customer_id'
        ]);
        $this->hasMany('Kiosk19ProductReceipts', [
            'foreignKey' => 'retail_customer_id'
        ]);
        $this->hasMany('Kiosk1ProductReceipts', [
            'foreignKey' => 'retail_customer_id'
        ]);
        $this->hasMany('Kiosk20ProductReceipts', [
            'foreignKey' => 'retail_customer_id'
        ]);
        $this->hasMany('Kiosk21ProductReceipts', [
            'foreignKey' => 'retail_customer_id'
        ]);
        $this->hasMany('Kiosk22ProductReceipts', [
            'foreignKey' => 'retail_customer_id'
        ]);
        $this->hasMany('Kiosk2ProductReceipts', [
            'foreignKey' => 'retail_customer_id'
        ]);
        $this->hasMany('Kiosk3ProductReceipts', [
            'foreignKey' => 'retail_customer_id'
        ]);
        $this->hasMany('Kiosk4ProductReceipts', [
            'foreignKey' => 'retail_customer_id'
        ]);
        $this->hasMany('Kiosk5ProductReceipts', [
            'foreignKey' => 'retail_customer_id'
        ]);
        $this->hasMany('Kiosk7ProductReceipts', [
            'foreignKey' => 'retail_customer_id'
        ]);
        $this->hasMany('Kiosk8ProductReceipts', [
            'foreignKey' => 'retail_customer_id'
        ]);
        $this->hasMany('MobileBlkReSales', [
            'foreignKey' => 'retail_customer_id'
        ]);
        $this->hasMany('MobileReSales', [
            'foreignKey' => 'retail_customer_id'
        ]);
        $this->hasMany('MobileRepairs', [
            'foreignKey' => 'retail_customer_id'
        ]);
        $this->hasMany('MobileUnlockSales', [
            'foreignKey' => 'retail_customer_id'
        ]);
        $this->hasMany('MobileUnlocks', [
            'foreignKey' => 'retail_customer_id'
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
            ->requirePresence('fname', 'create')
            ->notEmpty('fname');

        $validator
            ->requirePresence('lname', 'create')
            ->notEmpty('lname');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmpty('email');

        $validator
            ->requirePresence('mobile', 'create')
            ->notEmpty('mobile');

        //$validator
        //    ->requirePresence('city', 'create')
        //    ->notEmpty('city');

        //$validator
        //    ->requirePresence('state', 'create')
        //    ->notEmpty('state');

        //$validator
        //    ->requirePresence('country', 'create')
        //    ->notEmpty('country');

        $validator
            ->requirePresence('zip', 'create')
            ->notEmpty('zip');

        //$validator
        //    ->requirePresence('address_1', 'create')
        //    ->notEmpty('address_1');

        //$validator
        //    ->requirePresence('address_2', 'create')
        //    ->notEmpty('address_2');

        //$validator
        //    ->integer('status')
        //    ->requirePresence('status', 'create')
        //    ->notEmpty('status');

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
        $rules->add($rules->existsIn(['kiosk_id'], 'Kiosks'));

        return $rules;
    }
}
