<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MobileRepairSales Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 * @property \Cake\ORM\Association\BelongsTo $MobileRepairs
 * @property \Cake\ORM\Association\HasMany $RepairPayments
 *
 * @method \App\Model\Entity\MobileRepairSale get($primaryKey, $options = [])
 * @method \App\Model\Entity\MobileRepairSale newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\MobileRepairSale[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MobileRepairSale|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MobileRepairSale patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MobileRepairSale[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\MobileRepairSale findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MobileRepairSalesTable extends Table
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

        $this->table('mobile_repair_sales');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Kiosks', [
            'foreignKey' => 'kiosk_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('MobileRepairs', [
            'foreignKey' => 'mobile_repair_id',
            'joinType' => 'INNER'
        ]);
        $this->hasMany('RepairPayments', [
            'foreignKey' => 'mobile_repair_sale_id'
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
            ->requirePresence('amount', 'create')
            ->notEmpty('amount');

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
        $rules->add($rules->existsIn(['mobile_repair_id'], 'MobileRepairs'));

        return $rules;
    }
}
