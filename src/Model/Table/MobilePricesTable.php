<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MobilePrices Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $Brands
 * @property \Cake\ORM\Association\BelongsTo $MobileModels
 * @property \Cake\ORM\Association\BelongsTo $Networks
 *
 * @method \App\Model\Entity\MobilePrice get($primaryKey, $options = [])
 * @method \App\Model\Entity\MobilePrice newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\MobilePrice[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MobilePrice|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MobilePrice patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MobilePrice[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\MobilePrice findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MobilePricesTable extends Table
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

        $this->table('mobile_prices');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
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
            ->integer('locked')
            ->allowEmpty('locked');

        $validator
            ->integer('discount_status')
            ->requirePresence('discount_status', 'create')
            ->notEmpty('discount_status');

        $validator
            ->requirePresence('maximum_discount', 'create')
            ->notEmpty('maximum_discount');

        $validator
            ->integer('topup_status')
            ->requirePresence('topup_status', 'create')
            ->notEmpty('topup_status');

        $validator
            ->requirePresence('maximum_topup', 'create')
            ->notEmpty('maximum_topup');

        $validator
            ->integer('grade')
            ->requirePresence('grade', 'create')
            ->notEmpty('grade');

        $validator
            ->numeric('cost_price')
            ->requirePresence('cost_price', 'create')
            ->notEmpty('cost_price');

        $validator
            ->numeric('sale_price')
            ->requirePresence('sale_price', 'create')
            ->notEmpty('sale_price');

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
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['brand_id'], 'Brands'));
        $rules->add($rules->existsIn(['mobile_model_id'], 'MobileModels'));
        $rules->add($rules->existsIn(['network_id'], 'Networks'));

        return $rules;
    }
}
