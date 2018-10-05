<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Attachments Model
 *
 * @method \App\Model\Entity\Attachment get($primaryKey, $options = [])
 * @method \App\Model\Entity\Attachment newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Attachment[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Attachment|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Attachment patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Attachment[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Attachment findOrCreate($search, callable $callback = null, $options = [])
 */
class AttachmentsTable extends Table
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

        $this->table('attachments');
        $this->displayField('name');
        $this->primaryKey('id');
        //$this->addBehavior('Josegonzalez/Upload.Upload', [
        //    'attachment' => [
        //                'path' => 'webroot{DS}files{DS}{image}{DS}{attachment}{DS}{primaryKey}{DS}',
        //    ],
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
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('model', 'create')
            ->notEmpty('model');

        $validator
            ->integer('foreign_key')
            ->requirePresence('foreign_key', 'create')
            ->notEmpty('foreign_key');
            

        //$validator
        //    ->requirePresence('name', 'create')
        //    ->notEmpty('name');

        $validator
            ->requirePresence('attachment', 'create')
            ->notEmpty('attachment');

        $validator
            ->allowEmpty('dir');

        $validator
            ->allowEmpty('type');

        $validator
            ->integer('size')
            ->allowEmpty('size');

        $validator
            ->boolean('active')
            ->allowEmpty('active');

        return $validator;
    }
    
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique(['foreign_key', 'attachment']));
    
        return $rules;
    }
}
