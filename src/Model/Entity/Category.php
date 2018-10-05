<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Category Entity
 *
 * @property int $id
 * @property string $category
 * @property string $id_name_path
 * @property string $description
 * @property string $image
 * @property string $image_dir
 * @property int $parent_id
 * @property bool $top
 * @property int $column
 * @property int $sort_order
 * @property bool $status
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\ParentCategory $parent_category
 * @property \App\Model\Entity\ChildCategory[] $child_categories
 * @property \App\Model\Entity\CsvProduct[] $csv_products
 * @property \App\Model\Entity\Kiosk10000Product[] $kiosk10000_products
 * @property \App\Model\Entity\Kiosk10Product[] $kiosk10_products
 * @property \App\Model\Entity\Kiosk11Product[] $kiosk11_products
 * @property \App\Model\Entity\Kiosk12Product[] $kiosk12_products
 * @property \App\Model\Entity\Kiosk13Product[] $kiosk13_products
 * @property \App\Model\Entity\Kiosk14Product[] $kiosk14_products
 * @property \App\Model\Entity\Kiosk15Product[] $kiosk15_products
 * @property \App\Model\Entity\Kiosk16Product[] $kiosk16_products
 * @property \App\Model\Entity\Kiosk17Product[] $kiosk17_products
 * @property \App\Model\Entity\Kiosk18Product[] $kiosk18_products
 * @property \App\Model\Entity\Kiosk19Product[] $kiosk19_products
 * @property \App\Model\Entity\Kiosk1Product[] $kiosk1_products
 * @property \App\Model\Entity\Kiosk20Product[] $kiosk20_products
 * @property \App\Model\Entity\Kiosk21Product[] $kiosk21_products
 * @property \App\Model\Entity\Kiosk22Product[] $kiosk22_products
 * @property \App\Model\Entity\Kiosk2Product[] $kiosk2_products
 * @property \App\Model\Entity\Kiosk3Product[] $kiosk3_products
 * @property \App\Model\Entity\Kiosk4Product[] $kiosk4_products
 * @property \App\Model\Entity\Kiosk5Product[] $kiosk5_products
 * @property \App\Model\Entity\Kiosk7Product[] $kiosk7_products
 * @property \App\Model\Entity\Kiosk8Product[] $kiosk8_products
 * @property \App\Model\Entity\KioskProduct[] $kiosk_products
 * @property \App\Model\Entity\KioskCancelledOrderProduct[] $kiosk_cancelled_order_products
 * @property \App\Model\Entity\OnDemandProduct[] $on_demand_products
 * @property \App\Model\Entity\Product[] $products
 */
class Category extends Entity
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
