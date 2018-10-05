<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * DefectiveKioskTransient Entity
 *
 * @property int $id
 * @property int $defective_kiosk_reference_id
 * @property int $product_id
 * @property int $kiosk_id
 * @property int $quantity
 * @property int $status
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\DefectiveKioskReference $defective_kiosk_reference
 * @property \App\Model\Entity\Product $product
 * @property \App\Model\Entity\Kiosk $kiosk
 */
class DefectiveKioskTransient extends Entity
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
