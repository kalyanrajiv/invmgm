<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DailyTargets Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 * @property \Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\DailyTarget get($primaryKey, $options = [])
 * @method \App\Model\Entity\DailyTarget newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\DailyTarget[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\DailyTarget|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DailyTarget patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\DailyTarget[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\DailyTarget findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class DailyTargetsTable extends Table
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

        $this->table('daily_targets');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Kiosks', [
            'foreignKey' => 'kiosk_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Users', [
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
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->numeric('target')
            ->allowEmpty('target');

        $validator
            ->numeric('product_sale')
            ->allowEmpty('product_sale');

        $validator
            ->numeric('mobile_sale')
            ->allowEmpty('mobile_sale');

        $validator
            ->numeric('mobile_repair_sale')
            ->allowEmpty('mobile_repair_sale');

        $validator
            ->numeric('mobile_unlock_sale')
            ->allowEmpty('mobile_unlock_sale');

        $validator
            ->numeric('product_refund')
            ->allowEmpty('product_refund');

        $validator
            ->numeric('mobile_refund')
            ->allowEmpty('mobile_refund');

        $validator
            ->numeric('mobile_repair_refund')
            ->allowEmpty('mobile_repair_refund');

        $validator
            ->numeric('mobile_unlock_refund')
            ->allowEmpty('mobile_unlock_refund');

        $validator
            ->numeric('total_sale')
            ->allowEmpty('total_sale');

        $validator
            ->numeric('total_refund')
            ->allowEmpty('total_refund');

        $validator
            ->date('target_date')
            ->requirePresence('target_date', 'create')
            ->notEmpty('target_date');

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
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }
}
