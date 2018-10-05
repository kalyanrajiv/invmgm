<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MobileUnlockLogs Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 * @property \Cake\ORM\Association\BelongsTo $UnlockCenters
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $MobileUnlocks
 *
 * @method \App\Model\Entity\MobileUnlockLog get($primaryKey, $options = [])
 * @method \App\Model\Entity\MobileUnlockLog newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\MobileUnlockLog[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MobileUnlockLog|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MobileUnlockLog patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MobileUnlockLog[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\MobileUnlockLog findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MobileUnlockLogsTable extends Table
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

        $this->table('mobile_unlock_logs');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Kiosks', [
            'foreignKey' => 'kiosk_id',
            'joinType' => 'INNER'
        ]);
        //$this->belongsTo('UnlockCenters', [
        //    'foreignKey' => 'unlock_center_id'
        //]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('MobileUnlocks', [
            'foreignKey' => 'mobile_unlock_id',
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
            ->allowEmpty('id', 'create');

        $validator
            ->integer('comments')
            ->allowEmpty('comments');

        //$validator
        //    ->integer('unlock_status')
        //    ->requirePresence('unlock_status', 'create')
        //    ->notEmpty('unlock_status');

        //$validator
        //    ->integer('status')
        //    ->requirePresence('status', 'create')
        //    ->notEmpty('status');

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
       // $rules->add($rules->existsIn(['unlock_center_id'], 'UnlockCenters'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->existsIn(['mobile_unlock_id'], 'MobileUnlocks'));

        return $rules;
    }
}
