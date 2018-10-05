<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DefectiveCentralProducts Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $Products
 *
 * @method \App\Model\Entity\DefectiveCentralProduct get($primaryKey, $options = [])
 * @method \App\Model\Entity\DefectiveCentralProduct newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\DefectiveCentralProduct[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\DefectiveCentralProduct|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DefectiveCentralProduct patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\DefectiveCentralProduct[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\DefectiveCentralProduct findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class DefectiveCentralProductsTable extends Table
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

        $this->table('defective_central_products');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

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
            ->requirePresence('original_quantity', 'create')
            ->notEmpty('original_quantity');

        $validator
            ->integer('sent_quantity')
            ->requirePresence('sent_quantity', 'create')
            ->notEmpty('sent_quantity');

        $validator
            ->integer('remaining_quantity')
            ->requirePresence('remaining_quantity', 'create')
            ->notEmpty('remaining_quantity');

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
        $rules->add($rules->existsIn(['product_id'], 'Products'));

        return $rules;
    }
}
