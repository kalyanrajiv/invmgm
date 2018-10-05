<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * CsvProduct Entity
 *
 * @property string $prefix
 * @property int $id
 * @property string $product
 * @property int $quantity
 * @property string $description
 * @property string $location
 * @property int $category_id
 * @property float $cost_price
 * @property \Cake\I18n\Time $lu_cp
 * @property float $vat_excluded_wholesale_price
 * @property float $vat_exclude_retail_price
 * @property float $retail_cost_price
 * @property \Cake\I18n\Time $lu_rcp
 * @property float $selling_price
 * @property \Cake\I18n\Time $lu_sp
 * @property float $retail_selling_price
 * @property \Cake\I18n\Time $lu_rsp
 * @property int $brand_id
 * @property string $model
 * @property \Cake\I18n\Time $manufacturing_date
 * @property int $sku
 * @property string $country_make
 * @property string $product_code
 * @property float $weight
 * @property string $color
 * @property int $user_id
 * @property int $featured
 * @property float $discount
 * @property float $retail_discount
 * @property int $discount_status
 * @property int $rt_discount_status
 * @property float $max_discount
 * @property float $min_discount
 * @property int $special_offer
 * @property int $festival_offer
 * @property int $retail_special_offer
 * @property int $image_id
 * @property string $image_dir
 * @property string $manufacturer
 * @property int $stock_level
 * @property int $dead_stock_level
 * @property int $status
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Image $image
 * @property \App\Model\Entity\Category $category
 * @property \App\Model\Entity\Brand $brand
 * @property \App\Model\Entity\User $user
 */
class CsvProduct extends Entity
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
