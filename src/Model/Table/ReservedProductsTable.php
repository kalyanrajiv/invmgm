<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ReservedProducts Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Products
 * @property \Cake\ORM\Association\BelongsTo $Categories
 *
 * @method \App\Model\Entity\ReservedProduct get($primaryKey, $options = [])
 * @method \App\Model\Entity\ReservedProduct newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\ReservedProduct[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ReservedProduct|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ReservedProduct patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ReservedProduct[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\ReservedProduct findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ReservedProductsTable extends Table
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

        $this->table('reserved_products');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Products', [
            'foreignKey' => 'product_id'
        ]);
        $this->belongsTo('Categories', [
            'foreignKey' => 'category_id'
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
            ->allowEmpty('quantity');

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
        $rules->add($rules->existsIn(['product_id'], 'Products'));
        $rules->add($rules->existsIn(['category_id'], 'Categories'));

        return $rules;
    }
}
