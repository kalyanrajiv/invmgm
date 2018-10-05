<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MobileUnlockSales Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 * @property \Cake\ORM\Association\BelongsTo $RetailCustomers
 * @property \Cake\ORM\Association\BelongsTo $MobileUnlocks
 * @property \Cake\ORM\Association\HasMany $UnlockPayments
 *
 * @method \App\Model\Entity\MobileUnlockSale get($primaryKey, $options = [])
 * @method \App\Model\Entity\MobileUnlockSale newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\MobileUnlockSale[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MobileUnlockSale|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MobileUnlockSale patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MobileUnlockSale[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\MobileUnlockSale findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MobileUnlockSalesTable extends Table
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

        $this->table('mobile_unlock_sales');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Kiosks', [
            'foreignKey' => 'kiosk_id',
            'joinType' => 'INNER'
        ]);
        //$this->belongsTo('RetailCustomers', [
        //    'foreignKey' => 'retail_customer_id',
        //    'joinType' => 'INNER'
        //]);
        $this->belongsTo('MobileUnlocks', [
            'foreignKey' => 'mobile_unlock_id',
            'joinType' => 'INNER'
        ]);
        $this->hasMany('UnlockPayments', [
            'foreignKey' => 'mobile_unlock_sale_id'
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
            ->integer('sold_by')
            ->requirePresence('sold_by', 'create')
            ->notEmpty('sold_by');

        $validator
            ->dateTime('sold_on')
            ->allowEmpty('sold_on');

        $validator
            ->integer('refund_by')
            ->allowEmpty('refund_by');

        $validator
            ->numeric('amount')
            ->allowEmpty('amount');

        $validator
            ->numeric('refund_amount')
            ->allowEmpty('refund_amount');

        $validator
            ->integer('refund_status')
            ->requirePresence('refund_status', 'create')
            ->notEmpty('refund_status');

        $validator
            ->dateTime('refund_on')
            ->allowEmpty('refund_on');

        $validator
            ->allowEmpty('refund_remarks');

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
        $rules->add($rules->existsIn(['kiosk_id'], 'Kiosks'));
       // $rules->add($rules->existsIn(['retail_customer_id'], 'RetailCustomers'));
        $rules->add($rules->existsIn(['mobile_unlock_id'], 'MobileUnlocks'));

        return $rules;
    }
}
