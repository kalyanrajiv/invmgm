<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CreditPaymentDetails Model
 *
 * @property \Cake\ORM\Association\BelongsTo $CreditReceipts
 *
 * @method \App\Model\Entity\CreditPaymentDetail get($primaryKey, $options = [])
 * @method \App\Model\Entity\CreditPaymentDetail newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\CreditPaymentDetail[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CreditPaymentDetail|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CreditPaymentDetail patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CreditPaymentDetail[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\CreditPaymentDetail findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CreditPaymentDetailsTable extends Table
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

        $this->table('credit_payment_details');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('CreditReceipts', [
            'foreignKey' => 'credit_receipt_id',
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
            ->requirePresence('payment_method', 'create')
            ->notEmpty('payment_method');

        //$validator
        //    ->requirePresence('description', 'create')
        //    ->notEmpty('description');

        $validator
            ->numeric('amount')
            ->requirePresence('amount', 'create')
            ->notEmpty('amount');

        $validator
            ->integer('payment_status')
            ->requirePresence('payment_status', 'create')
            ->notEmpty('payment_status');

        $validator
            ->integer('status')
            ->allowEmpty('status');

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
        $rules->add($rules->existsIn(['credit_receipt_id'], 'CreditReceipts'));

        return $rules;
    }
}
