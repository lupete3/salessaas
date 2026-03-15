<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['name', 'slug', 'permissions'];

    protected $casts = [
        'permissions' => 'array',
    ];

    // Slugs constants
    const OWNER = 'proprietaire';
    const MANAGER = 'gerant';
    const SELLER = 'vendeur';
    const STOCK_MANAGER = 'gestionnaire_stock';
    const SUPERADMIN = 'superadmin';

    const PERMISSIONS_ALL = ['dashboard', 'pos', 'products', 'stock', 'suppliers', 'purchases', 'finances', 'reports', 'users', 'settings'];
    const PERMISSIONS_SELLER = ['dashboard', 'pos', 'products'];
    const PERMISSIONS_MANAGER = ['dashboard', 'pos', 'products', 'stock', 'suppliers', 'purchases', 'finances', 'reports'];
    const PERMISSIONS_SUPERADMIN = ['dashboard', 'stores', 'settings'];

    public static function getPermissionsFor(string $slug): array
    {
        return match ($slug) {
            self::SUPERADMIN => self::PERMISSIONS_SUPERADMIN,
            self::OWNER => self::PERMISSIONS_ALL,
            self::MANAGER => self::PERMISSIONS_MANAGER,
            self::SELLER => self::PERMISSIONS_SELLER,
            self::STOCK_MANAGER => ['dashboard', 'products', 'stock', 'suppliers', 'purchases'],
            default => []
        };
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }

    public function displayName(): string
    {
        return __("users.roles.{$this->slug}");
    }
}
