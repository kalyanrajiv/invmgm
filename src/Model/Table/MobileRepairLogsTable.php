<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MobileRepairLogs Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsTo $MobileRepairs
 * @property \Cake\ORM\Association\BelongsTo $ServiceCenters
 *
 * @method \App\Model\Entity\MobileRepairLog get($primaryKey, $options = [])
 * @method \App\Model\Entity\MobileRepairLog newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\MobileRepairLog[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MobileRepairLog|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MobileRepairLog patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MobileRepairLog[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\MobileRepairLog findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MobileRepairLogsTable extends Table
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

        $this->table('mobile_repair_logs');
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
        //$this->belongsTo('ServiceCenters', [
        //    'foreignKey' => 'service_center_id',
        //    'joinType' => 'INNER'
        //]);
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

        $validator
            ->integer('repair_status')
            ->allowEmpty('repair_status');

        $validator
            ->integer('status')
            ->requirePresence('status', 'create')
            ->notEmpty('status');

        $validator
            ->integer('service_center')
            ->requirePresence('service_center', 'create')
            ->notEmpty('service_center');

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
        //$rules->add($rules->existsIn(['service_center_id'], 'ServiceCenters'));

        return $rules;
    }
}
