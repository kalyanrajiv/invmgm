<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CommentMobileReSales Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $MobileReSales
 *
 * @method \App\Model\Entity\CommentMobileReSale get($primaryKey, $options = [])
 * @method \App\Model\Entity\CommentMobileReSale newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\CommentMobileReSale[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CommentMobileReSale|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\CommentMobileReSale patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\CommentMobileReSale[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\CommentMobileReSale findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CommentMobileReSalesTable extends Table
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

        $this->table('comment_mobile_re_sales');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('MobileReSales', [
            'foreignKey' => 'mobile_re_sale_id',
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
            ->requirePresence('comments', 'create')
            ->notEmpty('comments');

        $validator
            ->requirePresence('admin_remarks', 'create')
            ->notEmpty('admin_remarks');

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
        $rules->add($rules->existsIn(['mobile_re_sale_id'], 'MobileReSales'));

        return $rules;
    }
}
