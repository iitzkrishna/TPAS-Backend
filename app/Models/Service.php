<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'partner_id',
        'title',
        'type',
        'subtype',
        'amount',
        'thumbnail',
        'description',
        'discount_percentage',
        'discount_expires_on',
        'status_visibility',
        'location',
        'district_id',
        'availability'
    ];

    protected $casts = [
        'availability' => 'array',
        'discount_expires_on' => 'datetime',
        'amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2'
    ];

    // Service type constants
    const TYPE_STAY = 'stay';
    const TYPE_RENTAL = 'rental';
    const TYPE_ATTRACTION = 'attraction';
    const TYPE_OTHER = 'other';

    // Status visibility constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_DRAFT = 'draft';

    // Subtype constants
    const SUBTYPE_HOTEL = 'hotel';
    const SUBTYPE_APARTMENT = 'apartment';
    const SUBTYPE_VILLA = 'villa';
    const SUBTYPE_GUEST_HOUSE = 'guest_house';
    const SUBTYPE_BEACH_HOUSE = 'beach_house';
    const SUBTYPE_FARMHOUSE = 'farmhouse';
    const SUBTYPE_CAR = 'cars';
    const SUBTYPE_VAN = 'vans';
    const SUBTYPE_TUKTUK = 'tuktuk';
    const SUBTYPE_MOTORCYCLE = 'motorcycles';
    const SUBTYPE_AIRPORT_TAXI = 'airport-taxi';
    const SUBTYPE_YACHT = 'yatch';
    const SUBTYPE_BOAT = 'boat';
    const SUBTYPE_THEME_PARK = 'theme_park';
    const SUBTYPE_MUSEUM = 'museum';
    const SUBTYPE_ZOO = 'zoo';
    const SUBTYPE_WATER_PARK = 'water_park';
    const SUBTYPE_HISTORICAL_SITE = 'historical_site';
    const SUBTYPE_NATURE_PARK = 'nature_park';
    const SUBTYPE_NATIONAL_PARK = 'national_park';
    const SUBTYPE_OTHER = 'other';

    /**
     * Get the partner that owns the service.
     */
    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * Get the district where the service is located.
     */
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id', 'district_id');
    }

    /**
     * Get the images for the service.
     */
    public function images()
    {
        return $this->hasMany(ServicePackageImage::class);
    }

    /**
     * Get the reviews for the service.
     */
    public function reviews()
    {
        return $this->hasMany(ServicePackageReview::class);
    }

    /**
     * Scope a query to only include active services.
     */
    public function scopeActive($query)
    {
        return $query->where('status_visibility', self::STATUS_ACTIVE);
    }

    /**
     * Scope a query to only include services of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include services of a specific subtype.
     */
    public function scopeOfSubtype($query, $subtype)
    {
        return $query->where('subtype', $subtype);
    }

    /**
     * Get all available service types.
     */
    public static function getServiceTypes()
    {
        return [
            self::TYPE_STAY => 'Accommodation',
            self::TYPE_RENTAL => 'Vehicle Rental',
            self::TYPE_ATTRACTION => 'Tourist Attraction',
            self::TYPE_OTHER => 'Other'
        ];
    }

    /**
     * Get all available subtypes for a given type.
     */
    public static function getSubtypesForType($type)
    {
        $subtypes = [
            self::TYPE_STAY => [
                self::SUBTYPE_HOTEL,
                self::SUBTYPE_APARTMENT,
                self::SUBTYPE_VILLA,
                self::SUBTYPE_GUEST_HOUSE,
                self::SUBTYPE_BEACH_HOUSE,
                self::SUBTYPE_FARMHOUSE
            ],
            self::TYPE_RENTAL => [
                self::SUBTYPE_CAR,
                self::SUBTYPE_VAN,
                self::SUBTYPE_TUKTUK,
                self::SUBTYPE_MOTORCYCLE,
                self::SUBTYPE_AIRPORT_TAXI,
                self::SUBTYPE_YACHT,
                self::SUBTYPE_BOAT
            ],
            self::TYPE_ATTRACTION => [
                self::SUBTYPE_THEME_PARK,
                self::SUBTYPE_MUSEUM,
                self::SUBTYPE_ZOO,
                self::SUBTYPE_WATER_PARK,
                self::SUBTYPE_HISTORICAL_SITE,
                self::SUBTYPE_NATURE_PARK,
                self::SUBTYPE_NATIONAL_PARK
            ],
            self::TYPE_OTHER => [self::SUBTYPE_OTHER]
        ];

        return $subtypes[$type] ?? [];
    }

    /**
     * Check if the service is currently discounted.
     */
    public function isDiscounted()
    {
        return $this->discount_percentage > 0 && 
               $this->discount_expires_on && 
               $this->discount_expires_on->isFuture();
    }

    /**
     * Calculate the discounted price.
     */
    public function getDiscountedPrice()
    {
        if (!$this->isDiscounted()) {
            return $this->amount;
        }

        return $this->amount * (1 - ($this->discount_percentage / 100));
    }
} 