<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * MobileRepairPart Entity
 *
 * @property int $id
 * @property int $mobile_repair_id
 * @property int $product_id
 * @property int $kiosk_id
 * @property int $user_id
 * @property int $opp_status
 * @property \Cake\I18n\Time $opp_date
 * @property float $selling_price
 * @property string $remarks
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\MobileRepair $mobile_repair
 * @property \App\Model\Entity\Product $product
 * @property \App\Model\Entity\Kiosk $kiosk
 * @property \App\Model\Entity\User $user
 */
class MobileRepairPart extends Entity
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
