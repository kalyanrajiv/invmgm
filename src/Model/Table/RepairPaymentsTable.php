<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * RepairPayments Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $MobileRepairs
 * @property \Cake\ORM\Association\BelongsTo $MobileRepairSales
 *
 * @method \App\Model\Entity\RepairPayment get($primaryKey, $options = [])
 * @method \App\Model\Entity\RepairPayment newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\RepairPayment[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RepairPayment|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RepairPayment patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\RepairPayment[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\RepairPayment findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RepairPaymentsTable extends Table
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

        $this->table('repair_payments');
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
        $this->belongsTo('MobileRepairs', [
            'foreignKey' => 'mobile_repair_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('MobileRepairSales', [
            'foreignKey' => 'mobile_repair_sale_id',
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

        $validator
            ->allowEmpty('description');

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
        $rules->add($rules->existsIn(['kiosk_id'], 'Kiosks'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['mobile_repair_id'], 'MobileRepairs'));
        $rules->add($rules->existsIn(['mobile_repair_sale_id'], 'MobileRepairSales'));

        return $rules;
    }
}
