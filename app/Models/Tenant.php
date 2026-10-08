<?php

namespace App\Models;

use App\Support\Permissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    /**
     * The one place a center comes into existence: the tenant row plus its
     * owner login with the owner role, in a single transaction. Used by the
     * hosted signup approval (Admin\SignupRequests) and by the local-install
     * command (app:install-center), so the two can never drift apart.
     *
     * $password may be plain text or an existing bcrypt hash — the User model's
     * 'hashed' cast hashes plain text and leaves a hash untouched.
     *
     * @return array{tenant: Tenant, owner: User}
     */
    public static function provision(string $name, string $ownerName, string $ownerEmail, string $password): array
    {
        return DB::transaction(function () use ($name, $ownerName, $ownerEmail, $password) {
            $tenant = static::create([
                'name' => $name,
                'slug' => static::uniqueSlug($name),
                'settings' => [],
            ]);

            $owner = User::create([
                'tenant_id' => $tenant->id,
                'name' => $ownerName,
                'email' => $ownerEmail,
                'password' => $password,
                'status' => 'نشط',
                'email_verified_at' => now(),
            ]);
            $owner->assignRole(Permissions::OWNER_ROLE);

            return ['tenant' => $tenant, 'owner' => $owner];
        });
    }

    /** ASCII slug from the (usually Arabic) center name, suffixed on collision. */
    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name, '-', 'ar') ?: 'center';
        $slug = $base;
        for ($i = 2; static::where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
