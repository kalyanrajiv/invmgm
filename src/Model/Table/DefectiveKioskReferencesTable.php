<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DefectiveKioskReferences Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Kiosks
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\HasMany $DefectiveKioskTransients
 *
 * @method \App\Model\Entity\DefectiveKioskReference get($primaryKey, $options = [])
 * @method \App\Model\Entity\DefectiveKioskReference newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\DefectiveKioskReference[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\DefectiveKioskReference|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\DefectiveKioskReference patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\DefectiveKioskReference[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\DefectiveKioskReference findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class DefectiveKioskReferencesTable extends Table
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

        $this->table('defective_kiosk_references');
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
        $this->hasMany('DefectiveKioskTransients', [
            'foreignKey' => 'defective_kiosk_reference_id'
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
            ->integer('status')
            ->requirePresence('status', 'create')
            ->notEmpty('status');

        $validator
            ->requirePresence('reference', 'create')
            ->notEmpty('reference');

        $validator
            ->dateTime('date_of_receiving')
            ->requirePresence('date_of_receiving', 'create')
            ->notEmpty('date_of_receiving');

        $validator
            ->integer('received_by')
            ->requirePresence('received_by', 'create')
            ->notEmpty('received_by');

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
