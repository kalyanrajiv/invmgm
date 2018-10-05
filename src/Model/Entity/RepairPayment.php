<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * RepairPayment Entity
 *
 * @property int $id
 * @property int $kiosk_id
 * @property int $user_id
 * @property int $mobile_repair_id
 * @property int $mobile_repair_sale_id
 * @property string $payment_method
 * @property string $description
 * @property float $amount
 * @property int $payment_status
 * @property int $status
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Kiosk $kiosk
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\MobileRepair $mobile_repair
 * @property \App\Model\Entity\MobileRepairSale $mobile_repair_sale
 */
class RepairPayment extends Entity
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
