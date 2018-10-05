<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DefectiveBinTransients Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 * @property \Cake\ORM\Association\BelongsTo $DefectiveBinReferences
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $Products
 *
 * @method \App\Model\Entity\DefectiveBinTransient get($primaryKey, $options = [])
 * @method \App\Model\Entity\DefectiveBinTransient newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\DefectiveBinTransient[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\DefectiveBinTransient|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DefectiveBinTransient patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\DefectiveBinTransient[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\DefectiveBinTransient findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class DefectiveBinTransientsTable extends Table
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

        $this->table('defective_bin_transients');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Kiosks', [
            'foreignKey' => 'kiosk_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('DefectiveBinReferences', [
            'foreignKey' => 'defective_bin_reference_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Products', [
            'foreignKey' => 'product_id',
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
            ->integer('quantity')
            ->requirePresence('quantity', 'create')
            ->notEmpty('quantity');

        $validator
            ->numeric('single_product_cost')
            ->requirePresence('single_product_cost', 'create')
            ->notEmpty('single_product_cost');

        $validator
            ->numeric('total_product_cost')
            ->requirePresence('total_product_cost', 'create')
            ->notEmpty('total_product_cost');

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
        $rules->add($rules->existsIn(['defective_bin_reference_id'], 'DefectiveBinReferences'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['product_id'], 'Products'));

        return $rules;
    }
}
