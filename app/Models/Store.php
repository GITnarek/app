<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int     $id
 * @property string  $shop
 * @property string  $store_id
 * @property string  $access_token
 * @property string  $version
 * @property boolean $status
 * @property boolean $uninstalled
 * @property boolean $is_tax_prepaid
 * @property boolean $use_client_sku
 * @property boolean $submit_with_tax
 * @property boolean $submit_all_items
 * @property string  $channel
 * @property string  $carrier_service_id
 * @property string  $fulfillment_service_id
 * @property string  $location_id
 */
class Store extends Model
{
    use HasFactory;

    protected $fillable = ['shop'];

    public const SHOPIFY_DOMAIN = '.myshopify.com';
}
