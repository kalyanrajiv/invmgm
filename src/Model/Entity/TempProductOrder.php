<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * TempProductOrder Entity
 *
 * @property int $id
 * @property int $kiosk_id
 * @property int $user_id
 * @property float $total_amount
 * @property int $product_receipt_id
 * @property string $fname
 * @property string $lname
 * @property string $mobile
 * @property string $email
 * @property string $zip
 * @property string $address_1
 * @property int $address_2
 * @property string $city
 * @property string $state
 * @property string $remarks
 * @property int $status
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Kiosk $kiosk
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\ProductReceipt $product_receipt
 * @property \App\Model\Entity\TempProductDetail[] $temp_product_details
 */
class TempProductOrder extends Entity
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
