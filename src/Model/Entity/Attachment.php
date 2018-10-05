<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Attachment Entity
 *
 * @property int $id
 * @property string $model
 * @property int $foreign_key
 * @property string $name
 * @property string $attachment
 * @property string $dir
 * @property string $type
 * @property int $size
 * @property bool $active
 */
class Attachment extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];
}
