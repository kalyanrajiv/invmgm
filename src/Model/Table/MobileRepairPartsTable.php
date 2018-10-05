<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MobileRepairParts Model
 *
 * @property \Cake\ORM\Association\BelongsTo $MobileRepairs
 * @property \Cake\ORM\Association\BelongsTo $Products
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 * @property \Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\MobileRepairPart get($primaryKey, $options = [])
 * @method \App\Model\Entity\MobileRepairPart newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\MobileRepairPart[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MobileRepairPart|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MobileRepairPart patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MobileRepairPart[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\MobileRepairPart findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MobileRepairPartsTable extends Table
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

        $this->table('mobile_repair_parts');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('MobileRepairs', [
            'foreignKey' => 'mobile_repair_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Products', [
            'foreignKey' => 'product_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Kiosks', [
            'foreignKey' => 'kiosk_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
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
            ->integer('opp_status')
            ->requirePresence('opp_status', 'create')
            ->notEmpty('opp_status');

        $validator
            ->dateTime('opp_date')
            ->allowEmpty('opp_date');

        $validator
            ->numeric('selling_price')
            ->requirePresence('selling_price', 'create')
            ->notEmpty('selling_price');

        $validator
            ->requirePresence('remarks', 'create')
            ->notEmpty('remarks');

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
        $rules->add($rules->existsIn(['mobile_repair_id'], 'MobileRepairs'));
        $rules->add($rules->existsIn(['product_id'], 'Products'));
        $rules->add($rules->existsIn(['kiosk_id'], 'Kiosks'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }
}
