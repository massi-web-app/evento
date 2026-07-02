# 🎯 Project Snapshot — Laravel Modular Monolith

> این فایل یه snapshot کامل از پروژه ست برای انتقال به چت جدید Claude.
> با یک نگاه، Claude همه چیز رو می‌فهمه.

---

## 📋 سبک کار من (Robert)

- **زبان**: فارسی برای توضیحات، انگلیسی برای اصطلاحات فنی
- **روش**: Socratic — قبل از کد، سؤال بپرس و prediction بخواه
- **سرعت**: یک تغییر در یک زمان، تست بعد از هر تغییر
- **کد**: inline نوشته می‌شود (نه heredoc — مشکل markdown link)
- **هدف**: senior-level enterprise patterns برای microservice/K8s آینده
- **شعار**: «از هر ترفندی برای افزایش مهارت من دریغ نکن»

## 🏗️ معماری: Strict Modular Monolith

- هر ماژول bounded context خودش رو داره
- ارتباط cross-module فقط از طریق **Contracts** و **Events**
- Events: **ID** برای destructive، **Object** برای state changes
- DB::transaction() در همه write operations
- `final class` برای DTO/Service/Repository/Resource — **نه** Eloquent Model
- Form Request = structural validation، Service = business validation
- Intent-based methods در Repository
- Defense in depth: factory + observer هر دو slug می‌سازند
- Factory باید خنثی باشد — هیچ field نباید از field دیگر ساخته شود

## 🛠️ محیط فنی

- Path: `/opt/backend` (داخل Docker)
- Stack: Laravel 11+, PHP 8.3, MySQL (digikala), SQLite :memory: tests
- Test framework: Pest
- Custom Main module که خودکار Providers و routes را discover می‌کند

---


## 📁 ساختار ماژول‌ها

```
modules
modules/Brands
modules/Brands/Contracts
modules/Brands/DataTransferObjects
modules/Brands/Events
modules/Brands/Exceptions
modules/Brands/Http
modules/Brands/Models
modules/Brands/Providers
modules/Brands/Repositories
modules/Brands/Services
modules/Brands/database
modules/Brands/routes
modules/Brands/tests
modules/Categories
modules/Categories/Contracts
modules/Categories/DataTransferObjects
modules/Categories/Enums
modules/Categories/Exceptions
modules/Categories/Http
modules/Categories/Models
modules/Categories/Providers
modules/Categories/Repositories
modules/Categories/Rules
modules/Categories/Services
modules/Categories/Specifications
modules/Categories/Validators
modules/Categories/configs
modules/Categories/database
modules/Categories/lang
modules/Categories/routes
modules/Categories/tests
modules/Colors
modules/Colors/Contracts
modules/Colors/DataTransferObjects
modules/Colors/Events
modules/Colors/Exceptions
modules/Colors/Http
modules/Colors/Models
modules/Colors/Providers
modules/Colors/Repositories
modules/Colors/Services
modules/Colors/database
modules/Colors/routes
modules/Colors/tests
modules/Main
modules/Main/Providers
modules/Products
modules/Products/Contracts
modules/Products/DataTransferObjects
modules/Products/Events
modules/Products/Exceptions
modules/Products/Http
modules/Products/Models
modules/Products/Providers
modules/Products/Repositories
modules/Products/Services
modules/Products/database
modules/Products/routes
modules/Products/tests
modules/Shared
modules/Shared/Contracts
modules/Shared/Helpers
modules/Shared/Providers
modules/Shared/Repositories
modules/Shared/Servies
modules/Shared/config
modules/Shared/tests
modules/Warranties
modules/Warranties/Contracts
modules/Warranties/DataTransferObjects
modules/Warranties/Events
modules/Warranties/Exceptions
modules/Warranties/Http
modules/Warranties/Models
modules/Warranties/Providers
modules/Warranties/Repositories
modules/Warranties/Services
modules/Warranties/database
modules/Warranties/routes
modules/Warranties/tests
```

## 🗄️ Database Migrations


### 📄 `modules/Brands/database/migrations/2026_05_04_153634_create_brands_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('english_name')->unique();
            $table->string('slug')->unique();

            $table->string('logo')->nullable();
            $table->string('logo_dark')->nullable();
            $table->text('description')->nullable();
            $table->string('website_url')->nullable();


            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('is_active');
            $table->index(['is_active', 'is_featured']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
```

### 📄 `modules/Brands/database/migrations/2026_05_10_140914_create_brand_category_table.php`

```php
<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('brand_category', function (Blueprint $table) {
            $table->id();

            $table->foreignId('brand_id')
                ->constrained('brands')
                ->cascadeOnDelete();

            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnDelete();

            $table->boolean('is_primary')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['brand_id', 'category_id']);

            // Indexes
            $table->index(['category_id', 'is_primary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brand_category');
    }
};
```

### 📄 `modules/Categories/database/migrations/2026_04_24_100928_create_categories_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('english_name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('image')->nullable();
            $table->string('url')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
```

### 📄 `modules/Categories/database/migrations/2026_04_27_110330_create_specifications_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('specifications', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('unit')->nullable();
            $table->string('data_type')->nullable();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('specifications')
                ->cascadeOnDelete();

            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['parent_id']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('specifications');
    }
};
```

### 📄 `modules/Categories/database/migrations/2026_04_27_110337_create_category_specification_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('category_specification', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnDelete();

            $table->foreignId('specification_id')
                ->constrained('specifications')
                ->cascadeOnDelete();


            $table->enum('type', ['group', 'free', 'select', 'multi_select']);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_filterable')->default(false);
            $table->boolean('is_important')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();


            $table->unique(['category_id', 'specification_id']);

            $table->index(['category_id', 'is_filterable']);
            $table->index(['category_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_specification');
    }
};
```

### 📄 `modules/Categories/database/migrations/2026_04_27_110343_create_specification_options_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('specification_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_specification_id')
                ->constrained('category_specification')
                ->cascadeOnDelete();


            $table->string('value');
            $table->string('display_value')->nullable();

            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();


            $table->index(['category_specification_id', 'is_active']);
            $table->index(['category_specification_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('specification_options');
    }
};
```

### 📄 `modules/Categories/database/migrations/2026_05_13_070127_is_active_to_categories_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('slug');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('is_active');
            $table->dropColumn('is_active');
        });
    }
};
```

### 📄 `modules/Colors/database/migrations/2026_05_13_094145_create_colors_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('colors', function (Blueprint $table) {
            $table->id();
            $table->string('name',100);
            $table->string('english_name',100);
            $table->string('slug',100)->unique();
            $table->string('code',20)->unique();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colors');
    }
};
```

### 📄 `modules/Products/database/migrations/2026_05_16_121050_create_products_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Products\Models\Product;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('title', 200);
            $table->string('en_title', 200);
            $table->string('slug', 220)->unique();

            $table->string('description', 500)->nullable();
            $table->longText('content')->nullable();
            $table->string('image', 500)->nullable();

            $table->foreignId('brand_id')
                ->nullable()
                ->constrained('brands')
                ->nullOnDelete();


            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->enum('status', Product::PRODUCT_STATUES)
                ->default(Product::STATUS_DRAFT);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);


            $table->unsignedBigInteger('view_count')->default(0);
            $table->unsignedBigInteger('fake_view_count')->default(0);
            $table->unsignedBigInteger('sold_count')->default(0);

            $table->unsignedBigInteger('lowest_price')->default(0);
            $table->unsignedBigInteger('highest_price')->default(0);

            $table->unsignedInteger('total_stock')->default(0);
            $table->unsignedInteger('variants_count')->default(0);

            $table->string('meta_title', 200)->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('brand_id');
            $table->index('user_id');
            $table->index(['is_active', 'status']);
            $table->index('lowest_price');
            $table->index('view_count');
            $table->index('sold_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

### 📄 `modules/Warranties/database/migrations/2026_05_16_081555_create_warranties_table.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('warranties', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('english_name', 150);
            $table->string('slug', 150)->nullable();

            $table->unsignedSmallInteger('duration_months');
            $table->string('provider', 18);

            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unSignedBigInteger('sort_order')->default(0);
            $table->softDeletes();

            $table->timestamps();

            $table->index('is_active');
            $table->index('sort_order');
            $table->index('provider');
            $table->index('duration_months');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warranties');
    }
};
```

## 🛣️ Routes (لیست همه‌ی endpoints)

```
```

## 💻 کد همه ماژول‌ها


---

## 🧩 Module: Brands


### 📂 Models

#### `modules/Brands/Models/Brand.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Brands\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Brands\database\factories\BrandFactory;
use Modules\Brands\Models\Concerns\InteractsWithCategories;

/**
 * @property int $id
 * @property string $name
 * @property string $english_name
 * @property string $slug
 * @property string|null $logo
 * @property string|null $logo_dark
 * @property string|null $description
 * @property string|null $website_url
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property bool $is_active
 * @property bool $is_featured
 * @property int $sort_order
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
final class Brand extends Model
{
    use HasFactory;
    use softDeletes;
    use InteractsWithCategories;

    protected $table = 'brands';
    protected $fillable = [
        'name',
        'english_name',
        'slug',
        'logo',
        'logo_dark',
        'description',
        'website_url',
        'meta_title',
        'meta_description',
        'is_active',
        'is_featured',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    private const string PIVOT_TABLE = 'brand_category';

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
        ];
    }

    public static function newFactory(): BrandFactory
    {
        return BrandFactory::new();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('english_name', 'like', "%{$term}%")
                ->orWhere('slug', 'like', "%{$term}%");
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
```

#### `modules/Brands/Models/Concerns/InteractsWithCategories.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Brands\Models\Concerns;

use Illuminate\Support\Facades\DB;
use Throwable;

trait InteractsWithCategories
{
    private const string PIVOT_TABLE = 'brand_category';


    /**
     * @return array<int>
     */
    public function getCategoryIds(): array
    {
        return DB::table(self::PIVOT_TABLE)
            ->where('brand_id', $this->id)
            ->orderBy('sort_order')
            ->pluck('category_id')
            ->map(fn($id) => (int)$id)
            ->all();
    }

    public function getCategoryIdWithPivot(): array
    {
        return DB::table(self::PIVOT_TABLE)
            ->where('brand_id', $this->id)
            ->orderBy('sort_order')
            ->get(['category_id', 'is_primary', 'sort_order'])
            ->map(fn($row) => [
                'category_id' => (int)$row->category_id,
                'is_primary' => (bool)$row->is_primary,
                'sort_order' => (int)$row->sort_order,
            ])
            ->all();
    }

    public function getPrimaryCategoryId(): ?int
    {
        $row = DB::table(self::PIVOT_TABLE)
            ->where('brand_id', $this->id)
            ->where('is_primary', true)
            ->value('category_id');

        return $row !== null ? (int)$row : null;
    }


    public function attachCategory(int $categoryId, bool $isPrimary = false, int $sortOrder = 0): void
    {
        if ($isPrimary) {
            $this->unsetCurrentPrimaryCategory();
        }


        DB::table(self::PIVOT_TABLE)->insert([
            'brand_id'    => $this->id,
            'category_id' => $categoryId,
            'is_primary'  => $isPrimary,
            'sort_order'  => $sortOrder,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

    }


    public function detachCategory(int $categoryId): bool
    {
        return DB::table(self::PIVOT_TABLE)
                ->where('brand_id', $this->id)
                ->where('category_id', $categoryId)
                ->delete() > 0;
    }

    /**
     * @param array<int> $categoryIds
     * @throws Throwable
     */
    public function syncCategories(array $categoryIds, ?int $primaryId = null): void
    {
        DB::transaction(function () use ($categoryIds, $primaryId) {
            DB::table(self::PIVOT_TABLE)
                ->where('brand_id', $this->id)
                ->delete();

            $rows = [];
            foreach ($categoryIds as $index => $catId) {
                $rows[] = [
                    'brand_id'    => $this->id,
                    'category_id' => $catId,
                    'is_primary'  => $catId === $primaryId,
                    'sort_order'  => $index,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }

            if (!empty($rows)) {
                DB::table(self::PIVOT_TABLE)->insert($rows);
            }
        });
    }


    public function hasCategory(int $categoryId): bool
    {
        return DB::table(self::PIVOT_TABLE)
            ->where('brand_id', $this->id)
            ->where('category_id', $categoryId)
            ->exists();
    }

    private function unsetCurrentPrimaryCategory(): void
    {
        DB::table(self::PIVOT_TABLE)
            ->where('brand_id', $this->id)
            ->update(['is_primary' => false]);
    }



}
```

#### `modules/Brands/Models/Concerns/IntractiveWithCategory.php`

```php
<?php

namespace Modules\Brands\Models\Concerns;

class IntractiveWithCategory
{

}
```

### 📂 Contracts

#### `modules/Brands/Contracts/BrandRepositoryContract.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Brands\Contracts;


use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Brands\Models\Brand;

interface BrandRepositoryContract
{
    // ──────────── Generic CRUD ────────────

    public function find(int $id): ?Brand;

    public function findOrFail(int $id): Brand;

    public function exists(int $id): bool;

    /**
     * @param array<int> $ids
     */
    public function existsAll(array $ids): bool;

    /**
     * @return Collection<int, Brand>
     */
    public function all(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Brand;

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): Brand;

    public function delete(int $id): bool;

    // ──────────── Domain-specific ────────────

    /**
     * یافتن brand با slug
     */
    public function findBySlug(string $slug): ?Brand;

    /**
     * فقط برندهای فعال
     *
     * @return Collection<int, Brand>
     */
    public function getActive(): Collection;

    /**
     * فقط برندهای featured
     *
     * @return Collection<int, Brand>
     */
    public function getFeatured(): Collection;

    /**
     * جستجو در برندها
     */
    public function search(string $term, int $perPage = 15): LengthAwarePaginator;
}
```

#### `modules/Brands/Contracts/BrandServiceContract.php`

```php
<?php

declare(strict_types=1);
namespace Modules\Brands\Contracts;

 use Modules\Brands\DataTransferObjects\BrandPublicData;

 interface BrandServiceContract
{

     /**
      * بررسی می‌کنه آیا یه brand با این ID وجود داره
      */
     public function exists(int $brandId): bool;

     /**
      * بررسی می‌کنه آیا همه‌ی این ID ها وجود دارن
      *
      * @param array<int> $brandIds
      */
     public function existsAll(array $brandIds): bool;

     /**
      * یه BrandPublicData برمی‌گردونه. اگه نباشه، null
      */
     public function getById(int $brandId): ?BrandPublicData;

     /**
      * چندین brand رو با ID بگیر
      *
      * @param array<int> $brandIds
      * @return array<int, BrandPublicData> [id => BrandPublicData]
      */
     public function getByIds(array $brandIds): array;

     /**
      * بررسی می‌کنه آیا brand فعاله
      */
     public function isActive(int $brandId): bool;
}
```

#### `modules/Brands/Contracts/CategoryServiceContract.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Brands\Contracts;

use Modules\Categories\DataTransferObjects\CategoryPublicData;

interface CategoryServiceContract
{

    public function exists(int $categoryId): bool;

    public function getById(int $categoryId): ?CategoryPublicData;

    public function existsAll(array $categoryIds): bool;

    public function isActive(int $categoryId): bool;

    public function areAllActive(array $categoryIds): bool;

}
```

### 📂 DataTransferObjects

#### `modules/Brands/DataTransferObjects/BrandPublicData.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Brands\DataTransferObjects;

final readonly class BrandPublicData
{
    public function __construct(
        public int     $id,
        public string  $name,
        public string  $englishName,
        public string  $slug,
        public ?string $logo,
        public bool    $isActive,
        public bool    $isFeatured,
    )
    {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id:          (int) $data['id'],
            name:        (string) $data['name'],
            englishName: (string) $data['english_name'],
            slug:        (string) $data['slug'],
            logo:        $data['logo'] ?? null,
            isActive:    (bool) $data['is_active'],
            isFeatured:  (bool) $data['is_featured'],
        );
    }




}
```

#### `modules/Brands/DataTransferObjects/CreateBrandData.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Brands\DataTransferObjects;

final readonly class CreateBrandData
{

    public function __construct(
        public string $name,
        public string $englishName,
        public string $slug,
        public ?string $logo = null,
        public ?string $logoDark = null,
        public ?string $description = null,
        public ?string $websiteUrl = null,
        public ?string $metaTitle = null,
        public ?string $metaDescription = null,
        public bool $isActive = true,
        public bool $isFeatured = false,
        public int $sortOrder = 0,
        public ?int $createdBy = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name:            (string) $data['name'],
            englishName:     (string) $data['english_name'],
            slug:            (string) $data['slug'],
            logo:            $data['logo'] ?? null,
            logoDark:        $data['logo_dark'] ?? null,
            description:     $data['description'] ?? null,
            websiteUrl:      $data['website_url'] ?? null,
            metaTitle:       $data['meta_title'] ?? null,
            metaDescription: $data['meta_description'] ?? null,
            isActive:        (bool) ($data['is_active'] ?? true),
            isFeatured:      (bool) ($data['is_featured'] ?? false),
            sortOrder:       (int) ($data['sort_order'] ?? 0),
            createdBy:       isset($data['created_by']) ? (int) $data['created_by'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'name'             => $this->name,
            'english_name'     => $this->englishName,
            'slug'             => $this->slug,
            'logo'             => $this->logo,
            'logo_dark'        => $this->logoDark,
            'description'      => $this->description,
            'website_url'      => $this->websiteUrl,
            'meta_title'       => $this->metaTitle,
            'meta_description' => $this->metaDescription,
            'is_active'        => $this->isActive,
            'is_featured'      => $this->isFeatured,
            'sort_order'       => $this->sortOrder,
            'created_by'       => $this->createdBy,
        ];
    }



}
```

#### `modules/Brands/DataTransferObjects/UpdateBrandData.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Brands\DataTransferObjects;


final readonly class UpdateBrandData
{
    public function __construct(
        public ?string $name = null,
        public ?string $englishName = null,
        public ?string $slug = null,
        public ?string $logo = null,
        public ?string $logoDark = null,
        public ?string $description = null,
        public ?string $websiteUrl = null,
        public ?string $metaTitle = null,
        public ?string $metaDescription = null,
        public ?bool $isActive = null,
        public ?bool $isFeatured = null,
        public ?int $sortOrder = null,
        public ?int $updatedBy = null,
    ) {}

    /**
     * ساخت از array — معمولاً برای FormRequest
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name:            isset($data['name']) ? (string) $data['name'] : null,
            englishName:     isset($data['english_name']) ? (string) $data['english_name'] : null,
            slug:            isset($data['slug']) ? (string) $data['slug'] : null,
            logo:            isset($data['logo']) ? (string) $data['logo'] : null,
            logoDark:        isset($data['logo_dark']) ? (string) $data['logo_dark'] : null,
            description:     isset($data['description']) ? (string) $data['description'] : null,
            websiteUrl:      isset($data['website_url']) ? (string) $data['website_url'] : null,
            metaTitle:       isset($data['meta_title']) ? (string) $data['meta_title'] : null,
            metaDescription: isset($data['meta_description']) ? (string) $data['meta_description'] : null,
            isActive:        isset($data['is_active']) ? (bool) $data['is_active'] : null,
            isFeatured:      isset($data['is_featured']) ? (bool) $data['is_featured'] : null,
            sortOrder:       isset($data['sort_order']) ? (int) $data['sort_order'] : null,
            updatedBy:       isset($data['updated_by']) ? (int) $data['updated_by'] : null,
        );
    }

    /**
     * تبدیل به array — فقط فیلدهای **set شده** (non-null) برمی‌گردن
     * این برای ارسال به Repository::update() ه که فقط فیلدهای داده شده رو آپدیت می‌کنه
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->name !== null) {
            $data['name'] = $this->name;
        }
        if ($this->englishName !== null) {
            $data['english_name'] = $this->englishName;
        }
        if ($this->slug !== null) {
            $data['slug'] = $this->slug;
        }
        if ($this->logo !== null) {
            $data['logo'] = $this->logo;
        }
        if ($this->logoDark !== null) {
            $data['logo_dark'] = $this->logoDark;
        }
        if ($this->description !== null) {
            $data['description'] = $this->description;
        }
        if ($this->websiteUrl !== null) {
            $data['website_url'] = $this->websiteUrl;
        }
        if ($this->metaTitle !== null) {
            $data['meta_title'] = $this->metaTitle;
        }
        if ($this->metaDescription !== null) {
            $data['meta_description'] = $this->metaDescription;
        }
        if ($this->isActive !== null) {
            $data['is_active'] = $this->isActive;
        }
        if ($this->isFeatured !== null) {
            $data['is_featured'] = $this->isFeatured;
        }
        if ($this->sortOrder !== null) {
            $data['sort_order'] = $this->sortOrder;
        }
        if ($this->updatedBy !== null) {
            $data['updated_by'] = $this->updatedBy;
        }

        return $data;
    }

    /**
     * چک می‌کنه آیا حتی یه فیلد set شده برای update
     */
    public function hasChanges(): bool
    {
        return ! empty($this->toArray());
    }
}
```

### 📂 Events

#### `modules/Brands/Events/BrandActivated.php`

```php
<?php
declare(strict_types=1);
namespace Modules\Brands\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Brands\Models\Brand;

final class BrandActivated
{
    use Dispatchable;

    public function __construct(public readonly Brand $brand)
    {

    }

}
```

#### `modules/Brands/Events/BrandCategoriesSynced.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Brands\Events;

use Illuminate\Foundation\Events\Dispatchable;

final class BrandCategoriesSynced
{
    use Dispatchable;

    public function __construct(
        public readonly int   $brandId,
        public readonly array $categoryIds,
        public readonly ?int  $primaryCategoryId,
    )
    {

    }
}
```

#### `modules/Brands/Events/BrandCreated.php`

```php
<?php
declare(strict_types=1);
namespace Modules\Brands\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Brands\Models\Brand;

final class BrandCreated
{
    use Dispatchable;

    public function __construct(public readonly Brand $brand)
    {

    }

}
```

#### `modules/Brands/Events/BrandDeactivated.php`

```php
<?php
declare(strict_types=1);
namespace Modules\Brands\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Brands\Models\Brand;

final class BrandDeactivated
{
    use Dispatchable;

    public function __construct(public readonly Brand $brand)
    {

    }

}
```

#### `modules/Brands/Events/BrandDeleted.php`

```php
<?php
declare(strict_types=1);
namespace Modules\Brands\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Brands\Models\Brand;

final class BrandDeleted
{
    use Dispatchable;

    public function __construct(public readonly int $brandId)
    {

    }

}
```

#### `modules/Brands/Events/BrandUpdated.php`

```php
<?php
declare(strict_types=1);
namespace Modules\Brands\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Brands\Models\Brand;

final class BrandUpdated
{
    use Dispatchable;

    public function __construct(public readonly Brand $brand)
    {

    }

}
```

#### `modules/Brands/Events/CategoryAttachedToBrand.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Brands\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Brands\Models\Brand;

final class CategoryAttachedToBrand
{
    use Dispatchable;

    public function __construct(
        public readonly int $brandId,
        public readonly int $categoryId,
        public readonly int $isPrimary,
    )
    {
    }

}
```

#### `modules/Brands/Events/CategoryDetachedFromBrand.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Brands\Events;


use Illuminate\Foundation\Events\Dispatchable;

final class CategoryDetachedFromBrand
{
    use Dispatchable;

    public function __construct(
        public readonly int $brandId,
        public readonly int $categoryId
    )
    {

    }

}
```

### 📂 Exceptions

#### `modules/Brands/Exceptions/BrandNotFoundException.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Brands\Exceptions;

use RuntimeException;

class BrandNotFoundException extends RuntimeException
{
}
```

#### `modules/Brands/Exceptions/CategoryAlreadyAttachedException.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Brands\Exceptions;

use RuntimeException;

class CategoryAlreadyAttachedException extends RuntimeException
{

    public static function for(int $brandId, int $categoryId): self
    {
        return new self("Category {$categoryId} is already attached to brand {$brandId}");
    }
}
```

#### `modules/Brands/Exceptions/CategoryNotAttachedException.php`

```php
<?php
declare(strict_types=1);
namespace Modules\Brands\Exceptions;

use RuntimeException;

class CategoryNotAttachedException extends RuntimeException
{


    public static function for(int $brandId, int $categoryId): self
    {
        return new self("Category {$categoryId} is not attached to brand {$brandId}");
    }

}
```

#### `modules/Brands/Exceptions/CategoryNotFoundException.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Brands\Exceptions;

use RuntimeException;

final class CategoryNotFoundException extends RuntimeException
{

    public static function withId(int $categoryId): self
    {
        return new self("Category with id {$categoryId} not found}");

    }

}
```

#### `modules/Brands/Exceptions/InactiveCategoryException.php`

```php
<?php
declare(strict_types=1);
namespace Modules\Brands\Exceptions;

use RuntimeException;

final class InactiveCategoryException extends RuntimeException
{


    public static function withId(int $categoryId): self
    {
        return new self("Category with id {$categoryId} is inactive");
    }

}
```

### 📂 Repositories

#### `modules/Brands/Repositories/EloquentBrandRepository.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Brands\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Brands\Contracts\BrandRepositoryContract;
use Modules\Brands\Models\Brand;
use Modules\Shared\Repositories\BaseRepository;

final class EloquentBrandRepository extends BaseRepository implements BrandRepositoryContract
{
    /**
     * Model برای BaseRepository
     */
    protected function model(): Model
    {
        return new Brand();
    }

    // ──────────── Type-safe overrides ────────────

    public function find(int $id): ?Brand
    {
        /** @var Brand|null */
        return parent::find($id);
    }

    public function findOrFail(int $id): Brand
    {
        /** @var Brand */
        return parent::findOrFail($id);
    }

    public function create(array $data): Brand
    {
        /** @var Brand */
        return parent::create($data);
    }

    public function update(int $id, array $data): Brand
    {
        /** @var Brand */
        return parent::update($id, $data);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->ordered()
            ->paginate($perPage);
    }

    // ──────────── Domain-specific ────────────

    public function findBySlug(string $slug): ?Brand
    {
        return $this->query()
            ->where('slug', $slug)
            ->first();
    }

    public function getActive(): Collection
    {
        return $this->query()
            ->active()
            ->ordered()
            ->get();
    }

    public function getFeatured(): Collection
    {
        return $this->query()
            ->featured()
            ->active()
            ->ordered()
            ->get();
    }

    public function search(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->search($term)
            ->ordered()
            ->paginate($perPage);
    }
}
```

### 📂 Services

#### `modules/Brands/Services/BrandService.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Brands\Services;


use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\Brands\Contracts\BrandRepositoryContract;
use Modules\Brands\Contracts\BrandServiceContract;
use Modules\Brands\DataTransferObjects\BrandPublicData;
use Modules\Brands\DataTransferObjects\CreateBrandData;
use Modules\Brands\DataTransferObjects\UpdateBrandData;
use Modules\Brands\Events\BrandActivated;
use Modules\Brands\Events\BrandCategoriesSynced;
use Modules\Brands\Events\BrandCreated;
use Modules\Brands\Events\BrandDeactivated;
use Modules\Brands\Events\BrandDeleted;
use Modules\Brands\Events\BrandUpdated;
use Modules\Brands\Events\CategoryAttachedToBrand;
use Modules\Brands\Events\CategoryDetachedFromBrand;
use Modules\Brands\Exceptions\BrandNotFoundException;
use Modules\Brands\Exceptions\CategoryAlreadyAttachedException;
use Modules\Brands\Exceptions\CategoryNotAttachedException;
use Modules\Brands\Exceptions\CategoryNotFoundException;
use Modules\Brands\Exceptions\InactiveCategoryException;
use Modules\Brands\Models\Brand;
use Modules\Categories\Contracts\CategoryServiceContract;
use Throwable;

readonly class BrandService implements BrandServiceContract
{

    public function __construct(private BrandRepositoryContract $repository,
                                private CategoryServiceContract $categoryService)
    {

    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function findById(int $brandId): Brand
    {
        $brand = $this->repository->find($brandId);

        if ($brand === null) {
            throw new BrandNotFoundException("Brand with id {$brandId} not found");
        }

        return $brand;
    }

    public function findBySlug(string $slug): Brand
    {
        $brand = $this->repository->findBySlug($slug);

        if ($brand === null) {
            throw new BrandNotFoundException("Brand with slug '{$slug}' not found");
        }

        return $brand;
    }

    /**
     * @throws Throwable
     */
    public function create(CreateBrandData $data): Brand
    {
        return DB::transaction(function () use ($data) {
            $brand = $this->repository->create($data->toArray());

            BrandCreated::dispatch($brand);

            return $brand;
        });

    }

    /**
     * @throws Throwable
     */
    public function update(int $brandId, UpdateBrandData $data): Brand
    {
        if (!$data->hasChanges()) {
            return $this->findById($brandId);
        }

        return DB::transaction(function () use ($brandId, $data) {
            $brand = $this->repository->update($brandId, $data->toArray());

            BrandUpdated::dispatch($brand);

            return $brand;
        });

    }


    /**
     * @throws Throwable
     */
    public function delete(int $brandId): bool
    {
        return DB::transaction(function () use ($brandId) {

            $this->findById($brandId);

            $deleted = $this->repository->delete($brandId);

            if ($deleted) {
                BrandDeleted::dispatch($brandId);
            }
            return $deleted;
        });

    }

    /**
     * @throws Throwable
     */
    public function activate(int $brandId): Brand
    {
        return DB::transaction(function () use ($brandId) {

            $brand = $this->repository->update($brandId, ['is_active' => true]);

            BrandActivated::dispatch($brand);

            return $brand;
        });

    }

    /**
     * @throws Throwable
     */
    public function deactivate(int $brandId): Brand
    {
        return DB::transaction(function () use ($brandId) {

            $brand = $this->repository->update($brandId, ['is_active' => false]);

            BrandDeactivated::dispatch($brand);

            return $brand;
        });

    }

    public function search(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->search($term, $perPage);
    }


    // ──────────── Public Contract Methods ────────────
    public function exists(int $brandId): bool
    {
        return $this->repository->exists($brandId);
    }

    public function existsAll(array $brandIds): bool
    {
        return $this->repository->existsAll($brandIds);
    }

    public function getById(int $brandId): ?BrandPublicData
    {
        $brand = $this->repository->find($brandId);

        if ($brand === null) {
            return null;
        }

        return $this->toPublicData($brand);
    }

    public function getByIds(array $brandIds): array
    {
        if (empty($brandIds)) {
            return [];
        }

        $uniqueIds = array_values(array_unique($brandIds));

        $result = [];
        foreach ($uniqueIds as $brandId) {
            $brand = $this->repository->find($brandId);

            if ($brand !== null) {
                $result[$brand->id] = $this->toPublicData($brand);
            }
        }
        return $result;
    }

    public function isActive(int $brandId): bool
    {
        $brand = $this->repository->find($brandId);

        return $brand !== null && $brand->is_active;
    }

    private function toPublicData(Brand $brand): BrandPublicData
    {
        return new BrandPublicData(
            id: $brand->id,
            name: $brand->name,
            englishName: $brand->english_name,
            slug: $brand->slug,
            logo: $brand->logo,
            isActive: $brand->is_active,
            isFeatured: $brand->is_featured,
        );
    }


    // ──────────── Cross-Module: Categories ────────────


    /**
     * @throws Throwable
     */
    public function attachCategory(int $brandId, int $categoryId, bool $isPrimary = false)
    {
        return DB::transaction(function () use ($brandId, $categoryId, $isPrimary) {

            $brand = $this->findById($brandId);
            if (!$this->categoryService->exists($categoryId)) {
                throw CategoryNotFoundException::withId($categoryId);
            }

            if (!$this->categoryService->isActive($categoryId)) {
                throw InactiveCategoryException::withId($categoryId);
            }

            if ($brand->hasCategory($categoryId)) {
                throw CategoryAlreadyAttachedException::for($brandId, $categoryId);
            }

            $brand->attachCategory($categoryId, $isPrimary);

            CategoryAttachedToBrand::dispatch($brandId, $categoryId, $isPrimary);

            return $brand;

        });
    }

    /**
     * @throws Throwable
     */
    public function detachCategory(int $brandId, int $categoryId): Brand
    {

        return DB::transaction(function () use ($brandId, $categoryId) {
            $brand = $this->findById($brandId);

            if (!$brand->hasCategory($categoryId)) {
                throw CategoryNotAttachedException::for($brandId, $categoryId);
            }

            $brand->detachCategory($categoryId);

            CategoryDetachedFromBrand::dispatch($brandId, $categoryId);

            return $brand;
        });

    }

    /**
     * @throws Throwable
     */
    public function syncCategories(int $brandId, array $categoryIds, ?int $primaryId = null): Brand
    {
        return DB::transaction(function () use ($brandId, $categoryIds, $primaryId) {
            $brand = $this->findById($brandId);

            if (!$this->categoryService->existsAll($categoryIds)) {
                throw new CategoryNotFoundException('One or more categories not found');
            }

            if (!$this->categoryService->areAllActive($categoryIds)) {
                throw new InactiveCategoryException('One or more categories are inactive');

            }

            if ($primaryId !== null && !in_array($primaryId, $categoryIds, true)) {
                throw new InvalidArgumentException('One or more categories not found');
            }

            $brand->syncCategories($categoryIds, $primaryId);

            BrandCategoriesSynced::dispatch($brandId, $categoryIds,$primaryId);

            return $brand;
        });
    }


}


```

### 📂 Http

#### `modules/Brands/Http/Controllers/Admin/AdminBrandController.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Brands\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Modules\Brands\Contracts\BrandServiceContract;
use Modules\Brands\Http\Requests\StoreBrandRequest;
use Modules\Brands\Http\Requests\UpdateBrandRequest;
use Modules\Brands\Http\Resources\BrandResource;
use Modules\Brands\Models\Brand;
use Throwable;

final class AdminBrandController extends Controller
{

    public function __construct(
        private readonly BrandServiceContract $service
    )
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min((int)$request->query('per_page', 15), 100);

        $brands = $this->service->paginate($perPage);

        return BrandResource::collection($brands);
    }

    public function show(Brand $brand): BrandResource
    {
        return BrandResource::make($brand);
    }

    /**
     * @throws Throwable
     */
    public function store(StoreBrandRequest $request): JsonResponse
    {
        $brand = $this->service->create($request->toDTO());
        return BrandResource::make($brand)
            ->response()->setStatusCode(201);
    }

    /**
     * @throws Throwable
     */
    public function update(UpdateBrandRequest $request, Brand $brand): BrandResource
    {
        $updated = $this->service->update($brand->id, $request->toDTO());
        return BrandResource::make($updated);
    }

    /**
     * @throws Throwable
     */
    public function destroy(Brand $brand): Response
    {
        $this->service->delete($brand->id);
        return response()->noContent();
    }

    /**
     * @throws Throwable
     */
    public function activate(Brand $brand): BrandResource
    {
        $updated = $this->service->activate($brand->id);
        return BrandResource::make($updated);
    }

    /**
     * @throws Throwable
     */
    public function deactivate(Brand $brand): BrandResource
    {
        $updated = $this->service->deactivate($brand->id);

        return BrandResource::make($updated);
    }

    public function search(Request $request): AnonymousResourceCollection
    {
        $term = (string)$request->query('q', '');
        $perPage = min((int)$request->query('per_page', 15), 100);

        $brands = $this->service->search($term, $perPage);

        return BrandResource::collection($brands);
    }


}
```

#### `modules/Brands/Http/Controllers/Admin/BrandCategoryController.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Brands\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Modules\Brands\Contracts\BrandServiceContract;
use Modules\Brands\Http\Requests\AttachCategoryRequest;
use Modules\Brands\Http\Requests\SyncCategoriesRequest;
use Modules\Brands\Models\Brand;
use Modules\Categories\Contracts\CategoryServiceContract;

final class BrandCategoryController extends Controller
{
    public function __construct(
        private readonly BrandServiceContract    $brandService,
        private readonly CategoryServiceContract $categoryService,

    )
    {
    }

    public function index(Brand $brand): JsonResponse
    {
        $pivotData = $brand->getCategoryIdWithPivot();

        if (empty($pivotData)) {
            return response()->json(['data' => []]);
        }

        $categoryIds = array_column($pivotData, 'category_id');
        $categories = $this->categoryService->getByIds($categoryIds);

        $data = array_map(function (array $pivot) use ($categories) {
            $category = $categories[$pivot['category_id']] ?? null;

            return [
                'category_id' => $pivot['category_id'],
                'name' => $category?->name,
                'slug' => $category?->slug,
                'is_primary' => $pivot['is_primary'],
                'sort_order' => $pivot['sort_order'],
            ];
        }, $pivotData);


        return response()->json(['data' => $data]);

    }


    public function attach(AttachCategoryRequest $request, Brand $brand): JsonResponse
    {
        $this->brandService->attachCategory($brand->id, $request->getCategoryId(), $request->isPrimary());

        return response()->json([
            'message' => 'دسته‌بندی با موفقیت اضافه شد',
        ], 201);
    }

    public function sync(SyncCategoriesRequest $request, Brand $brand): JsonResponse
    {
        $this->brandService->syncCategories($brand->id, $request->getCategoryIds(), $request->getPrimaryId());
        return response()->json([
            'message' => 'دسته‌بندی‌ها با موفقیت به‌روزرسانی شد',
        ]);
    }

    public function detach(Brand $brand, int $categoryId): Response
    {
        $this->brandService->detachCategory($brand->id, $categoryId);

        return response()->noContent();
    }

}
```

#### `modules/Brands/Http/Requests/AttachCategoryRequest.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Brands\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AttachCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'min:1'],
            'is_primary' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];

    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'شناسه دسته‌بندی الزامی است',
            'category_id.integer'  => 'شناسه دسته‌بندی باید عدد باشد',
        ];
    }

    public function getCategoryId(): int
    {
        return (int) $this->validated('category_id');
    }

    public function isPrimary(): bool
    {
        return (bool) $this->validated('is_primary', false);
    }

    public function getSortOrder(): int
    {
        return (int) $this->validated('sort_order', 0);
    }

}
```

#### `modules/Brands/Http/Requests/StoreBrandRequest.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Brands\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Brands\DataTransferObjects\CreateBrandData;

final class StoreBrandRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    /**
     * validation rules
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('brands', 'name'),
            ],
            'english_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('brands', 'english_name'),
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',  // slug format
                Rule::unique('brands', 'slug'),
            ],
            'logo' => ['nullable', 'string', 'max:500'],
            'logo_dark' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:5000'],
            'website_url' => ['nullable', 'string', 'url', 'max:500'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    /**
     * پیام‌های خطای فارسی
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'نام برند الزامی است',
            'name.unique' => 'این نام برند قبلاً ثبت شده',
            'english_name.required' => 'نام انگلیسی برند الزامی است',
            'english_name.unique' => 'این نام انگلیسی قبلاً ثبت شده',
            'slug.required' => 'slug برند الزامی است',
            'slug.regex' => 'slug فقط می‌تواند شامل حروف کوچک، اعداد و خط تیره باشد',
            'slug.unique' => 'این slug قبلاً استفاده شده',
            'website_url.url' => 'آدرس وب‌سایت معتبر نیست',
        ];
    }

    public function toDTO(): CreateBrandData
    {
        return CreateBrandData::fromArray($this->validated());
    }
}
```

#### `modules/Brands/Http/Requests/SyncCategoriesRequest.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Brands\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SyncCategoriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_ids' => [
                'required',
                'array',
                'min:1',
                'max:50',  // حد بالا برای DoS protection
            ],
            'category_ids.*' => [
                'integer',
                'min:1',
                'distinct',  // ← هیچ duplicate
            ],
            'primary_id' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_ids.required' => 'لیست دسته‌بندی‌ها الزامی است',
            'category_ids.array'    => 'لیست دسته‌بندی‌ها باید آرایه باشد',
            'category_ids.min'      => 'حداقل یک دسته‌بندی الزامی است',
            'category_ids.max'      => 'حداکثر ۵۰ دسته‌بندی مجاز است',
            'category_ids.*.distinct' => 'دسته‌بندی‌های تکراری مجاز نیست',
        ];
    }

    /**
     * Validation extra: primary_id باید تو category_ids باشه
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $primaryId = $this->input('primary_id');
            $categoryIds = $this->input('category_ids', []);

            if ($primaryId !== null && ! in_array((int) $primaryId, array_map('intval', $categoryIds), true)) {
                $validator->errors()->add(
                    'primary_id',
                    'شناسه primary باید در لیست category_ids باشد'
                );
            }
        });
    }

    /**
     * @return array<int>
     */
    public function getCategoryIds(): array
    {
        return array_map('intval', $this->validated('category_ids'));
    }

    public function getPrimaryId(): ?int
    {
        $primaryId = $this->validated('primary_id');
        return $primaryId !== null ? (int) $primaryId : null;
    }
}
```

#### `modules/Brands/Http/Requests/UpdateBrandRequest.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Brands\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Brands\DataTransferObjects\UpdateBrandData;
use Modules\Brands\Models\Brand;

final class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $brandId = $this->getBrandId();

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('brands', 'name')->ignore($brandId),
            ],
            'english_name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('brands', 'english_name')->ignore($brandId),
            ],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('brands', 'slug')->ignore($brandId),
            ],
            'logo' => ['sometimes', 'nullable', 'string', 'max:500'],
            'logo_dark' => ['sometimes', 'nullable', 'string', 'max:500'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'website_url' => ['sometimes', 'nullable', 'string', 'url', 'max:500'],
            'meta_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meta_description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:9999'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'نام برند الزامی است',
            'name.unique' => 'این نام برند قبلاً ثبت شده',
            'english_name.required' => 'نام انگلیسی برند الزامی است',
            'english_name.unique' => 'این نام انگلیسی قبلاً ثبت شده',
            'slug.required' => 'slug برند الزامی است',
            'slug.regex' => 'slug فقط می‌تواند شامل حروف کوچک، اعداد و خط تیره باشد',
            'slug.unique' => 'این slug قبلاً استفاده شده',
            'website_url.url' => 'آدرس وب‌سایت معتبر نیست',
        ];
    }

    /**
     * تبدیل به DTO
     */
    public function toDTO(): UpdateBrandData
    {
        return UpdateBrandData::fromArray($this->validated());
    }

    /**
     * گرفتن ID از route binding
     *
     * Route می‌تونه brand رو به عنوان Model بده (Route Model Binding)
     * یا به عنوان slug. هر کدوم رو drive می‌کنیم.
     */
    private function getBrandId(): ?int
    {
        $brand = $this->route('brand');

        if ($brand instanceof Brand) {
            return $brand->id;
        }

        if (is_numeric($brand)) {
            return (int) $brand;
        }

        // Slug — از database query کنیم
        if (is_string($brand)) {
            return Brand::where('slug', $brand)->value('id');
        }

        return null;
    }
}
```

#### `modules/Brands/Http/Resources/BrandResource.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Brands\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Brands\Models\Brand;

/**
 * @mixin Brand
 */
final class BrandResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Identity
            'id'           => $this->id,
            'name'         => $this->name,
            'english_name' => $this->english_name,
            'slug'         => $this->slug,

            // Display
            'logo'         => $this->logo,
            'logo_dark'    => $this->logo_dark,
            'description'  => $this->description,
            'website_url'  => $this->website_url,

            // SEO (group)
            'seo' => [
                'meta_title'       => $this->meta_title,
                'meta_description' => $this->meta_description,
            ],

            // State
            'is_active'   => (bool)$this->is_active,
            'is_featured' => (bool)$this->is_featured,
            'sort_order'  => $this->sort_order,

            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
```

### 📂 Providers

#### `modules/Brands/Providers/BrandsServiceProvider.php`

```php
<?php

namespace Modules\Brands\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Brands\Contracts\BrandRepositoryContract;
use Modules\Brands\Contracts\BrandServiceContract;
use Modules\Brands\Repositories\EloquentBrandRepository;
use Modules\Brands\Services\BrandService;

class BrandsServiceProvider extends ServiceProvider
{

    public function register(): void
    {

        $this->app->bind(
            BrandRepositoryContract::class,
            EloquentBrandRepository::class
        );

        $this->app->bind(
            BrandServiceContract::class,
            BrandService::class
        );

    }

    public function boot(): void
    {

    }


}
```

### 📂 database/factories

#### `modules/Brands/database/factories/BrandFactory.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Brands\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Brands\Models\Brand;
use Modules\Categories\Models\Category;

/**
 * @extends Factory<Brand>
 */
class BrandFactory extends Factory
{
    protected $model = Brand::class;

    public function definition(): array
    {
        $englishName = $this->faker->unique()->company();
        $slug = Str::slug($englishName);
        return [
            'name' => $this->faker->unique()->name(),
            'english_name' => $englishName,
            'slug' => $slug,
            'logo' => null,
            'logo_dark' => null,
            'description' => $this->faker->paragraph(),
            'website_url' => $this->faker->url(),
            'meta_title' => $this->faker->sentence(3),
            'meta_description' => $this->faker->sentence(10),
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => $this->faker->numberBetween(1, 100),
            'created_by' => null,
            'updated_by' => null,
        ];
    }


    public function active(): self
    {
        return $this->state(fn() => ['is_active' => true]);
    }
    public function inActive(): self
    {
        return $this->state(fn() => ['is_active' => false]);
    }

    public function featured(): self
    {
        return $this->state(fn() => [
            'is_active' => true,
            'is_featured' => true,
        ]);
    }

    public function withoutLogo(): self
    {
        return $this->state(fn() => [
            'logo' => null,
            'logo_dark' => null,
        ]);
    }

    public function withLogo(): self
    {
        return $this->state(fn() => [
            'logo' => 'brands/logos/sample.svg',
            'logo_dark' => 'brands/logos/sample-dark.svg',
        ]);
    }


}
```

---

## 🧩 Module: Categories


### 📂 Models

#### `modules/Categories/Models/Category.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Categories\database\factories\CategoryFactory;

class Category extends Model
{

    //region model config
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'english_name', 'url', 'parent_id', 'description', 'image', 'icon', 'slug', 'is_active'];


    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];

    }
    //endregion


    //region model relations

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    //endregion


    public static function booted(): void
    {
        static::creating(function (Category $category) {
            if (empty($category->slug)) {
                $category->slug = static::generateUniqueSlug($category->english_name);
            }
        });
    }

    protected static function generateUniqueSlug(string $source): string
    {
        $baseSlug = Str::slug($source);
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    protected static function newFactory(): Factory
    {
        return CategoryFactory::new();
    }


    public function specifications(): BelongsToMany
    {
        return $this->belongsToMany(Specification::class)
            ->withPivot([
                'id', 'type', 'is_required', 'is_filterable', 'is_important', 'position'])
            ->withTimestamps()
            ->orderByPivot('position');
    }

}
```

#### `modules/Categories/Models/Specification.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Categories\database\factories\SpecificationFactory;
use Modules\Categories\Enums\SpecificationType;

class Specification extends Model
{

    use SoftDeletes,HasFactory;

    protected $fillable = ['name', 'slug', 'unit', 'data_type', 'parent_id', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Specification::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Specification::class, 'parent_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_specification')
            ->withPivot([
                'id',
                'type',
                'is_required',
                'is_filterable',
                'is_important',
                'position',
            ])->withTimestamps();
    }


    protected static function newFactory(): Factory
    {
        return SpecificationFactory::new();
    }


}
```

### 📂 Contracts

#### `modules/Categories/Contracts/CategoryRepositoryContract.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Modules\Categories\Models\Category;

interface CategoryRepositoryContract
{
    public function find(int $id): ?Category;

    public function findOrFail(int $id): Category;

    public function exists(int $id): bool;

    /**
     * @param array<int> $ids
     */
    public function existsAll(array $ids): bool;

    /**
     * @return Collection<int, Category>
     */
    public function all(): Collection;

    /**
     * @return LengthAwarePaginator<Category>
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Category;

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): Category;

    public function delete(int $id): bool;

    // ──────────── Domain-specific methods ────────────

    /**
     * فقط category های فعال
     *
     * @return Collection<int, Category>
     */
    public function getActive(): Collection;

    /**
     * Category با parent خاص
     *
     * @return Collection<int, Category>
     */
    public function getByParent(?int $parentId): Collection;

    public function countActiveByIds(array $ids): int;

    public function findByIds(array $ids): Collection;
}
```

#### `modules/Categories/Contracts/CategoryServiceContract.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Contracts;

use Modules\Categories\DataTransferObjects\CategoryPublicData;

interface CategoryServiceContract
{
    /**
     * بررسی می‌کنه آیا یه category با این ID وجود داره
     */
    public function exists(int $categoryId): bool;

    /**
     * یه CategoryPublicData برمی‌گردونه. اگه نباشه، null
     */
    public function getById(int $categoryId): ?CategoryPublicData;

    /**
     * بررسی می‌کنه آیا همه‌ی این ID ها وجود دارن
     *
     * @param array<int> $categoryIds
     */
    public function existsAll(array $categoryIds): bool;

    /**
     * بررسی می‌کنه آیا category فعال ه
     */
    public function isActive(int $categoryId): bool;


    public function areAllActive(array $categoryIds): bool;

    public function getByIds(array $categoryIds): array;
}
```

#### `modules/Categories/Contracts/SpecificationRepositoryContract.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Categories\Models\Specification;

interface SpecificationRepositoryContract
{
    // ──────────── Generic CRUD ────────────

    public function find(int $id): ?Specification;

    public function findOrFail(int $id): Specification;

    public function exists(int $id): bool;

    /**
     * @return Collection<int, Specification>
     */
    public function all(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Specification;

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): Specification;

    public function delete(int $id): bool;

    // ──────────── Domain-specific ────────────

    /**
     * @return Collection<int, Specification>
     */
    public function findChildren(int $parentId): Collection;

    public function paginateRoots(int $perPage = 15): LengthAwarePaginator;

    public function save(Specification $specification): Specification;
}
```

### 📂 DataTransferObjects

#### `modules/Categories/DataTransferObjects/AttachSpecificationData.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\DataTransferObjects;

use Modules\Categories\Enums\SpecificationType;

final readonly class AttachSpecificationData
{


    public function __construct(
        public int               $specificationId,
        public SpecificationType $type,
        public bool              $isRequired = false,
        public bool              $isFilterable = false,
        public bool              $isImportant = false,
        public int               $position = 0,
    )
    {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            specificationId: $data['specification_id'],
            type: SpecificationType::from($data['type']),
            isRequired: (bool)($data['is_required'] ?? false),
            isFilterable: (bool)($data['is_filterable'] ?? false),
            isImportant: (bool)($data['is_important'] ?? false),
            position: (int)($data['position'] ?? 0)
        );
    }


    /**
     * @param array<int,array<string,mixed>> $items
     * @return array<int,self>
     */
    public static function collectionFromArray(array $items): array
    {
        return array_map(static fn(array $item) => self::fromArray($item), $items);
    }


    /**
     * @return array<string,mixed>
     */
    public function toPivotArray(): array
    {
        return [
            'type'          => $this->type->value,
            'is_required'   => $this->isRequired,
            'is_filterable' => $this->isFilterable,
            'is_important'  => $this->isImportant,
            'position'      => $this->position,
        ];

    }


}
```

#### `modules/Categories/DataTransferObjects/CategoryPublicData.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Categories\DataTransferObjects;

final readonly class CategoryPublicData
{
    public function __construct(
        public int    $id,
        public string $name,
        public string $slug,
        public bool   $isActive,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int)$data['id'],
            (string)$data['name'],
            (string)$data['slug'],
            (bool)$data['isActive'],
        );
    }

}
```

#### `modules/Categories/DataTransferObjects/DataTransferObjects.php`

```php
<?php
declare(strict_types=1);
namespace Modules\Categories\DataTransferObjects;

final readonly class DataTransferObjects
{

}
```

#### `modules/Categories/DataTransferObjects/UpdatePivotData.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\DataTransferObjects;

use Modules\Categories\Enums\SpecificationType;

final readonly class UpdatePivotData
{
    public function __construct(
        public int                $specificationId,
        public ?SpecificationType $type = null,
        public ?bool $isRequired = null,
        public ?bool $isFilterable = null,
        public ?bool $isImportant = null,
        public ?int $position = null,
    )
    {

    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            specificationId: (int) $data['specification_id'],
            type: isset($data['type']) ? SpecificationType::from($data['type']) : null,
            isRequired: isset($data['is_required']) ? (bool) $data['is_required'] : null,
            isFilterable: isset($data['is_filterable']) ? (bool) $data['is_filterable'] : null,
            isImportant: isset($data['is_important']) ? (bool) $data['is_important'] : null,
            position: isset($data['position']) ? (int) $data['position'] : null,
        );
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, self>
     */
    public static function collectionFromArray(array $items): array
    {
        return array_map(static fn (array $item) => self::fromArray($item), $items);
    }


    /**
     * @return array<string, mixed>
     */
    public function toPivotArray(): array
    {
        return array_filter([
            'type'          => $this->type?->value,
            'is_required'   => $this->isRequired,
            'is_filterable' => $this->isFilterable,
            'is_important'  => $this->isImportant,
            'position'      => $this->position,
        ], static fn ($value) => $value !== null);
    }

    public function hasChanges(): bool
    {
        return !empty($this->toPivotArray());
    }



}
```

### 📂 Exceptions

#### `modules/Categories/Exceptions/BulkAttachValidationException.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class BulkAttachValidationException extends RuntimeException
{

    /**
     * @param array<int, string> $errors
     */
    public function __construct(
        private readonly array $errors,
        string                 $message = 'Bulk validation failed.',
        ?\Throwable            $previous = null
    )
    {
        parent::__construct($message, 0, $previous);

    }

    /**
     * @return array<int, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'the given data was invalid.',
            'errors' => $this->formatErrors(),
        ], Response::HTTP_UNPROCESSABLE_ENTITY);

    }

    /**
     * @return array<string, list<string>>
     */
    private function formatErrors(): array
    {
        $formatted = [];

        foreach ($this->errors as $index => $message) {
            $formatted["specifications.{$index}"] = [$message];
        }

        return $formatted;
    }

}
```

#### `modules/Categories/Exceptions/BulkValidationException.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class BulkValidationException extends RuntimeException
{
    /**
     * @param array<int, string> $errors
     */
    public function __construct(
        private readonly array $errors,
        string $message = 'Bulk validation failed.',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * @return array<int, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'The given data was invalid.',
            'errors'  => $this->formatErrors(),
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @return array<string, list<string>>
     */
    private function formatErrors(): array
    {
        $formatted = [];

        foreach ($this->errors as $index => $message) {
            $formatted["specifications.{$index}"] = [$message];
        }

        return $formatted;
    }
}
```

#### `modules/Categories/Exceptions/CategoryNotFoundException.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Categories\Exceptions;

use RuntimeException;

class CategoryNotFoundException extends RuntimeException
{
}
```

#### `modules/Categories/Exceptions/CategorySpecificationException.php`

```php
<?php

namespace Modules\Categories\Exceptions;

use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;

class CategorySpecificationException extends RuntimeException
{
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'The given data was invalid.',
            'errors' => [
                'specification_id' => [$this->getMessage()],
            ],
        ], 422);
    }
}
```

#### `modules/Categories/Exceptions/CategorySpecificationValidationException.php`

```php
<?php

namespace Modules\Categories\Exceptions;

use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;

class CategorySpecificationValidationException extends RuntimeException
{
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], 422);
    }
}
```

#### `modules/Categories/Exceptions/InvalidPivotConfigurationException.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class InvalidPivotConfigurationException extends RuntimeException
{

    public function __construct(string $message, private readonly string $field = 'is_filterable')
    {
        parent::__construct($message);

    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'Invalid Pivot configuration.',
            'errors' => [
                $this->field => $this->getMessage(),
            ]
        ],Response::HTTP_UNPROCESSABLE_ENTITY);

    }

}
```

#### `modules/Categories/Exceptions/InvalidSpecificationHierarchyException.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class InvalidSpecificationHierarchyException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly string $field = 'is_filterable',
    ) {
        parent::__construct($message);
    }


    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'the given data was invalid.',
            'errors' => [
                'parent_id' => [$this->getMessage()]
            ]
        ], Response::HTTP_UNPROCESSABLE_ENTITY);

    }

}
```

#### `modules/Categories/Exceptions/SpecificationAlreadyAttachedException.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class SpecificationAlreadyAttachedException extends RuntimeException
{
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'The given data was invalid.',
            'errors'  => [
                'specification_id' => [$this->getMessage()],
            ],
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
```

#### `modules/Categories/Exceptions/SpecificationNotAttachedException.php`

```php
<?php

declare(strict_types=1);
namespace Modules\Categories\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class SpecificationNotAttachedException extends RuntimeException
{
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'The given data was invalid.',
            'errors'  => [
                'specification_id' => [$this->getMessage()],
            ],
        ], Response::HTTP_NOT_FOUND);
    }

}
```

#### `modules/Categories/Exceptions/SpecificationNotFoundException.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class SpecificationNotFoundException extends RuntimeException
{

    public function render():JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], Response::HTTP_NOT_FOUND);
    }
}
```

### 📂 Repositories

#### `modules/Categories/Repositories/EloquentCategoryRepository.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Categories\Contracts\CategoryRepositoryContract;
use Modules\Categories\Models\Category;
use Modules\Shared\Contracts\Specification;
use Modules\Shared\Repositories\BaseRepository;

final class EloquentCategoryRepository extends BaseRepository implements CategoryRepositoryContract
{

    protected function model(): Model
    {
        return new Category();
    }

    // ──────────── Type-safe overrides ────────────

    public function create(array $data): Category
    {
        return $this->query()->create($data);
    }

    public function find(int $id): ?Category
    {
        /** @var Category|null */
        return $this->query()->with(['parent', 'children'])->find($id);
    }

    public function findOrFail(int $id): Category
    {
        /** @var Category */
        return $this->query()->with(['parent', 'children'])->findOrFail($id);
    }

    public function update(int $id, array $data): Category
    {
        /** @var Category */
        return parent::update($id, $data);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->with(['parent', 'children'])
            ->latest()
            ->paginate($perPage);
    }

    // ──────────── Domain-specific ────────────

    public function findBySlug(string $slug): ?Category
    {
        return $this->query()
            ->with(['parent', 'children'])
            ->where('slug', $slug)
            ->first();
    }

    public function findRoots(): Collection
    {
        return $this->query()
            ->whereNull('parent_id')
            ->with('children')
            ->get();
    }

    public function findBy(Specification ...$specs): Collection
    {
        $query = $this->query();

        foreach ($specs as $spec) {
            $query = $spec->apply($query);
        }

        return $query->get();
    }

    public function getActive(): Collection
    {
        return $this->query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getByParent(?int $parentId): Collection
    {
        return $this->query()
            ->where('parent_id', $parentId)
            ->orderBy('name')
            ->get();
    }

    public function save(Category $category): Category
    {
        $category->save();

        return $category;
    }


    public function whereIn(array $categoryIds = [], $field = 'id'): Builder
    {
        return $this->query()->whereIn($field, $categoryIds);
    }

    public function countActiveByIds(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        return $this->query()->whereIn('id', $ids)
            ->where('is_active', true)->count();
    }

    public function findByIds(array $ids): Collection
    {
        if (empty($ids)) {
            return new Collection();
        }

        return $this->query()->whereIn('id', $ids)->get();
    }
}
```

#### `modules/Categories/Repositories/EloquentSpecificationRepository.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Categories\Contracts\SpecificationRepositoryContract;
use Modules\Categories\Models\Specification;
use Modules\Shared\Repositories\BaseRepository;

final class EloquentSpecificationRepository extends BaseRepository implements SpecificationRepositoryContract
{
    protected function model(): Model
    {
        return new Specification();
    }

    // ──────────── Type-safe overrides ────────────

    public function find(int $id): ?Specification
    {
        /** @var Specification|null */
        return $this->query()->with(['parent', 'children'])->find($id);
    }

    public function findOrFail(int $id): Specification
    {
        /** @var Specification */
        return $this->query()->with(['parent', 'children'])->findOrFail($id);
    }

    public function create(array $data): Specification
    {
        /** @var Specification */
        return parent::create($data);
    }

    public function update(int $id, array $data): Specification
    {
        /** @var Specification */
        return parent::update($id, $data);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->with(['parent', 'children'])
            ->latest()
            ->paginate($perPage);
    }

    // ──────────── Domain-specific ────────────

    public function findChildren(int $parentId): Collection
    {
        return $this->query()
            ->where('parent_id', $parentId)
            ->orderBy('name')
            ->get();
    }

    public function save(Specification $specification): Specification
    {
        $specification->save();

        return $specification;
    }

    public function paginateRoots(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->whereNull('parent_id')
            ->with(['parent', 'children'])
            ->latest()
            ->paginate($perPage);
    }
}
```

### 📂 Services

#### `modules/Categories/Services/CategoryService.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Categories\Contracts\CategoryRepositoryContract;
use Modules\Categories\Contracts\CategoryServiceContract;
use Modules\Categories\DataTransferObjects\CategoryPublicData;
use Modules\Categories\Exceptions\CategoryNotFoundException;
use Modules\Categories\Models\Category;
use Modules\Categories\Specifications\IsRootSpec;
use Modules\Categories\Specifications\RecentSpec;
use Modules\Shared\Servies\File\FileService;
use Throwable;

final class CategoryService implements CategoryServiceContract
{
    private const string IMAGE_DIRECTORY = 'uploads';

    public function __construct(
        private readonly CategoryRepositoryContract $repository,  // ← Contract!
        private readonly FileService                $fileService,
    )
    {
    }

    // ──────────── Internal Methods (داخل ماژول) ────────────
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function rootCategories(): Collection
    {
        return $this->repository->findRoots();
    }

    public function findById(int $id): Category
    {
        $category = $this->repository->find($id);

        if ($category === null) {
            throw new CategoryNotFoundException("Category with id {$id} not found");
        }

        return $category;
    }

    public function findBySlug(string $slug): Category
    {
        $category = $this->repository->findBySlug($slug);

        if ($category === null) {
            throw new CategoryNotFoundException("Category with slug '{$slug}' not found");
        }

        return $category;
    }

    /**
     * @throws Throwable
     */
    public function create(array $data, ?UploadedFile $image = null): Category
    {
        return DB::transaction(function () use ($data, $image) {
            if ($image !== null) {
                $data['image'] = $this->fileService->upload($image, self::IMAGE_DIRECTORY);
            }

            return $this->repository->create($data);
        });
    }

    /**
     * @throws Throwable
     */
    public function update(Category $category, array $data, ?UploadedFile $image = null): Category
    {
        return DB::transaction(function () use ($category, $data, $image) {
            if ($image !== null) {
                if ($category->image !== null) {
                    $this->fileService->delete($category->image);
                }
                $data['image'] = $this->fileService->upload($image, self::IMAGE_DIRECTORY);
            }

            $category->fill($data);

            return $this->repository->save($category);
        });
    }

    /**
     * @throws Throwable
     */
    public function delete(Category $category): bool
    {
        return DB::transaction(function () use ($category) {
            return $this->repository->delete($category->id);
        });
    }

    public function findRecentRoots(int $days = 30): Collection
    {
        return $this->repository->findBy(
            new IsRootSpec(),
            new RecentSpec($days)
        );
    }

    // ──────────── Public Contract Methods (برای ماژول‌های دیگه) ────────────

    public function exists(int $categoryId): bool
    {
        return $this->repository->exists($categoryId);
    }

    public function getById(int $categoryId): ?CategoryPublicData
    {
        $category = $this->repository->find($categoryId);

        if ($category === null) {
            return null;
        }

        return new CategoryPublicData(
            id: $category->id,
            name: $category->name,
            slug: $category->slug,
            isActive: (bool)$category->is_active,
        );
    }

    public function existsAll(array $categoryIds): bool
    {
        return $this->repository->existsAll($categoryIds);
    }

    public function isActive(int $categoryId): bool
    {
        $category = $this->repository->find($categoryId);

        return $category !== null && (bool)$category->is_active;
    }

    public function areAllActive(array $categoryIds): bool
    {
        if (empty($categoryIds)) {
            return true;
        }


        $uniqueIds = array_unique($categoryIds);
        $count = $this->repository->countActiveByIds($uniqueIds);

        return $count === count($categoryIds);
    }

    /**
     * @param array<int> $categoryIds
     * @return array<int, CategoryPublicData>
     */
    public function getByIds(array $categoryIds): array
    {
        if (empty($categoryIds)) {
            return [];
        }

        $categories = $this->repository->findByIds(array_unique($categoryIds));

        $result = [];
        foreach ($categories as $category) {
            $result[$category->id] = new CategoryPublicData(
                id: $category->id,
                name: $category->name,
                slug: $category->slug,
                isActive: (bool)$category->is_active,
            );
        }

        return $result;
    }
}
```

#### `modules/Categories/Services/CategorySpecificationService.php`

```php
<?php


declare(strict_types=1);

namespace Modules\Categories\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Categories\DataTransferObjects\AttachSpecificationData;
use Modules\Categories\DataTransferObjects\UpdatePivotData;
use Modules\Categories\Exceptions\BulkValidationException;
use Modules\Categories\Exceptions\SpecificationAlreadyAttachedException;
use Modules\Categories\Exceptions\SpecificationNotAttachedException;
use Modules\Categories\Models\Category;
use Modules\Categories\Models\Specification;
use Modules\Categories\Validators\BulkUpdateValidator;
use Modules\Categories\Validators\SpecificationValidatorFactory;
use Modules\Categories\Validators\ValidatorChain;
use Throwable;

final readonly class CategorySpecificationService
{

    public function __construct(
        private SpecificationValidatorFactory $validatorFactory,
        private BulkUpdateValidator           $bulkUpdateValidator,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function attach(Category $category, AttachSpecificationData $data): void
    {
        DB::transaction(function () use ($category, $data) {

            $validator = $this->validatorFactory->forAttach($category);

            $error = $validator->validate($data);


            if ($error !== null) {
                throw new SpecificationAlreadyAttachedException($error);
            }
            $category->specifications()->attach(
                $data->specificationId,
                $data->toPivotArray()
            );

        });

    }

    /**
     * @throws Throwable
     */
    public function update(Category $category, Specification $specification, UpdatePivotData $data): void
    {
        DB::transaction(function () use ($category, $specification, $data) {

            $current = $this->getCurrentPivot($category, $specification);

            if ($current === null) {
                throw new SpecificationNotAttachedException(
                    "Specification with ID {$specification->id} is not attached to this category."
                );
            }

            $validator = $this->validatorFactory->forUpdate($current);

            $error = $validator->validate($data);

            if ($error !== null) {
                throw new BulkValidationException([0 => $error], $error);
            }

            if ($data->hasChanges()) {
                $category->specifications()->updateExistingPivot(
                    $specification->id,
                    $data->toPivotArray()
                );
            }
        });
    }

    /**
     * @throws Throwable
     */
    public function detach(Category $category, Specification $specification): void
    {
        DB::transaction(function () use ($category, $specification) {
            $detached = $category->specifications()->detach($specification->id);

            if ($detached === 0) {
                throw new SpecificationNotAttachedException(
                    "Specification with ID {$specification->id} is not attached to this category."
                );
            }
        });
    }

    /**
     * @param array<int,AttachSpecificationData> $items
     * @throws Throwable
     */
    public function bulkAttach(Category $category, array $items): void
    {
        DB::transaction(function () use ($category, $items) {

            $validator = $this->validatorFactory->forBulkAttach($category);

            $this->runValidatorOnItems($validator, $items);
            $pivotData = [];

            foreach ($items as $item) {
                $pivotData[$item->specificationId] = $item->toPivotArray();
            }

            $category->specifications()->attach($pivotData);
        });

    }

    /**
     * @param array<int, UpdatePivotData> $items
     * @throws Throwable
     */
    public function bulkUpdate(Category $category, array $items): void
    {
        DB::transaction(function () use ($category, $items) {
            $errors = $this->bulkUpdateValidator->validate($category, $items);
            if (!empty($errors)) {
                throw new BulkValidationException($errors);
            }

            foreach ($items as $item) {
                if (!$item->hasChanges()) {
                    continue;
                }

                $category->specifications()->updateExistingPivot(
                    $item->specificationId,
                    $item->toPivotArray()
                );
            }
        });
    }


    /**
     * @param array<int, AttachSpecificationData> $items
     * @throws Throwable
     */
    public function sync(Category $category, array $items): void
    {
        DB::transaction(function () use ($category, $items) {
            $validator = $this->validatorFactory->forSync();

            $this->runValidatorOnItems($validator, $items);
            $syncData = [];
            foreach ($items as $item) {
                $syncData[$item->specificationId] = $item->toPivotArray();
            }

            $category->specifications()->sync($syncData);
        });
    }

    public function listForCategory(Category $category): Collection
    {
        return $category->specifications()
            ->with(['parent', 'children'])
            ->get();
    }


    /**
     * @return array<int>
     */
    private function getAttachedSpecificationIds(Category $category): array
    {
        return $category->specifications()
            ->pluck('specifications.id')
            ->all();
    }

    private function runValidatorOnItems(ValidatorChain $validator, array $items): void
    {
        $errors = [];

        foreach ($items as $index => $item) {
            $error = $validator->validate($item);
            if ($error !== null) {
                $errors[$index] = $error;
            }
        }

        if (!empty($errors)) {
            throw new BulkValidationException($errors);
        }
    }

    private function getCurrentPivot(Category $category, Specification $specification): ?Specification

    {
        return $category->specifications()
            ->where('specifications.id', $specification->id)
            ->first();
    }

}
```

#### `modules/Categories/Services/SpecificationService.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Categories\Contracts\SpecificationRepositoryContract;
use Modules\Categories\Exceptions\InvalidSpecificationHierarchyException;
use Modules\Categories\Models\Specification;

final readonly class SpecificationService
{

    public function __construct(
        private SpecificationRepositoryContract $repository,
    )
    {
    }

    public function paginateRoots(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginateRoots($perPage);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function findById(int $id): Specification
    {
        $specification = $this->repository->find($id);

        if ($specification === null) {
            throw new InvalidSpecificationHierarchyException(
                "Specification with ID {$id} not found"
            );
        }

        return $specification;
    }


    public function findChildren(Specification $parent): Collection
    {
        return $this->repository->findChildren($parent);
    }


    public function create(array $data): Specification
    {
        return DB::transaction(function () use ($data) {
            $this->validateHierarchy($data);

            $specification = new Specification($data);
            return $this->repository->save($specification);
        });
    }


    public function update(Specification $specification, array $data): Specification
    {

        return DB::transaction(function () use ($specification, $data) {

            $this->validateHierarchy($data, $specification);

            $specification->fill($data);

            return $this->repository->save($specification);
        });
    }

    public function delete(int $specification): bool
    {
        return DB::transaction(function () use ($specification) {
            return $this->repository->delete($specification);
        });
    }

    public function validateHierarchy(array $data, ?Specification $current = null): void
    {

        $parentId = $data['parent_id'] ?? null;

        if ($parentId === null) {
            return;
        }

        if ($current !== null && (int)$parentId === $current->id) {
            throw new InvalidSpecificationHierarchyException(
                'A specification cannot be its own parent.'
            );
        }

        $parent = $this->repository->find((int)$parentId);
        if ($parent === null) {
            throw new InvalidSpecificationHierarchyException(
                "Parent specification with ID {$parentId} not found."
            );
        }

        if ($parent->parent_id !== null) {
            throw new InvalidSpecificationHierarchyException(
                'Specifications can only be nested one level deep.'
            );
        }

        if ($current !== null) {
            $children = $this->repository->findChildren($current);
            $childIds = $children->pluck('id')->all();

            if (in_array((int)$parentId, $childIds, strict: true)) {
                throw new InvalidSpecificationHierarchyException(
                    'A specification cannot be a child of its own descendant.'
                );
            }
        }
    }


}
```

#### `modules/Categories/tests/Unit/Services/CategoryServiceTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Modules\Categories\Contracts\CategoryRepositoryContract;
use Modules\Categories\Models\Category;
use Modules\Categories\Services\CategoryService;
use Modules\Shared\Servies\File\FileService;

uses(\Tests\TestCase::class);

test('it uploads image when creating category with image', function () {
    $fileService = Mockery::mock(FileService::class);
    $repository  = Mockery::mock(CategoryRepositoryContract::class);

    // FileService انتظار داره upload صدا زده بشه
    $fileService->expects('upload')
        ->once()
        ->andReturn('uploads/test-image.jpg');

    // Repository انتظار داره create صدا زده بشه
    $repository->expects('create')
        ->once()
        ->andReturnUsing(function (array $data) {
            $category = new Category($data);
            $category->id = 1;
            return $category;
        });

    $service = new CategoryService($repository, $fileService);

    $image = UploadedFile::fake()->image('test.jpg');

    $category = $service->create(
        ['name' => 'Test', 'english_name' => 'Electronics'],
        $image
    );

    expect($category)->toBeInstanceOf(Category::class);
});

test('it does not call upload when no image is provided', function () {
    $fileService = Mockery::mock(FileService::class);
    $repository  = Mockery::mock(CategoryRepositoryContract::class);

    // FileService نباید upload صدا زده بشه
    $fileService->shouldNotReceive('upload');

    // Repository انتظار داره create صدا زده بشه
    $repository->expects('create')
        ->once()
        ->andReturnUsing(function (array $data) {
            $category = new Category($data);
            $category->id = 1;
            return $category;
        });

    $service = new CategoryService($repository, $fileService);

    $category = $service->create([
        'name'         => 'Test',
        'english_name' => 'Electronics',
    ]);

    expect($category)->toBeInstanceOf(Category::class);
});
```

### 📂 Http

#### `modules/Categories/Http/Controllers/Admin/AdminCategoryController.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Categories\Http\Requests\StoreCategoryRequest;
use Modules\Categories\Http\Requests\UpdateCategoryRequest;
use Modules\Categories\Http\Resources\CategoryResource;
use Modules\Categories\Models\Category;
use Modules\Categories\Services\CategoryService;
use Symfony\Component\HttpFoundation\Response;

class AdminCategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService,
    ) {}

    /**
     * نمایش لیست دسته‌بندی‌ها (شامل soft deleted در آینده)
     */
    public function index(): AnonymousResourceCollection
    {
        $categories = $this->categoryService->paginate(20);

        return CategoryResource::collection($categories);
    }

    /**
     * نمایش جزئیات یک دسته
     */
    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category->load(['parent','children']));
    }

    /**
     * ساخت دسته جدید
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->create(
            data: $request->safe()->except('image'),
            image: $request->file('image'),
        );

        return CategoryResource::make($category)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }


    public function update(UpdateCategoryRequest $request, Category $category): CategoryResource
    {
        $updated = $this->categoryService->update($category, $request->safe()->except('image'), $request->file('image'));

        return new CategoryResource($updated);
    }

    public function destroy(Category $category): \Illuminate\Http\Response
    {
        $this->categoryService->delete($category);

        return response()->noContent();
    }

//    public function attachSpecification(Attach)
//    {
//
//    }

}
```

#### `modules/Categories/Http/Controllers/Admin/CategoryController.php`

```php
<?php

namespace Modules\Categories\Http\Controllers\Admin;

class CategoryController
{

}
```

#### `modules/Categories/Http/Controllers/Admin/CategorySpecificationController.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Categories\DataTransferObjects\AttachSpecificationData;
use Modules\Categories\DataTransferObjects\UpdatePivotData;
use Modules\Categories\Http\Requests\CategorySpecification\AttachSpecificationRequest;
use Modules\Categories\Http\Requests\CategorySpecification\BulkAttachRequest;
use Modules\Categories\Http\Requests\CategorySpecification\BulkUpdateRequest;
use Modules\Categories\Http\Requests\CategorySpecification\SyncSpecificationsRequest;
use Modules\Categories\Http\Requests\CategorySpecification\UpdatePivotRequest;
use Modules\Categories\Http\Resources\CategorySpecificationResource;
use Modules\Categories\Models\Category;
use Modules\Categories\Models\Specification;
use Modules\Categories\Services\CategorySpecificationService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CategorySpecificationController extends Controller
{
    public function __construct(
        private readonly CategorySpecificationService $service,
    )
    {
    }

    public function index(Category $category): AnonymousResourceCollection
    {
        $specifications = $this->service->listForCategory($category);

        return CategorySpecificationResource::collection($specifications);
    }

    /**
     * @throws Throwable
     */
    public function store(AttachSpecificationRequest $request, Category   $category): JsonResponse
    {
        $data = AttachSpecificationData::fromArray($request->validated());

        $this->service->attach($category, $data);

        return response()->json([
            'message' => 'Specification attached successfully.',
        ], Response::HTTP_CREATED);
    }

    /**
     * @throws Throwable
     */
    public function update(
        UpdatePivotRequest $request,
        Category           $category,
        Specification      $specification
    ): JsonResponse
    {

        $payload = $request->validated();

        $payload['specification_id'] = $specification->id;

        $data = UpdatePivotData::fromArray(
            $request->validatedWithSpecificationId($specification->id)
        );

        $this->service->update($category, $specification, $data);

        return response()->json([
            'message' => 'Specification updated successfully.',
        ]);
    }

    /**
     * @throws Throwable
     */
    public function destroy(
        Category      $category,
        Specification $specification
    ): Response
    {
        $this->service->detach($category, $specification);

        return response()->noContent();
    }

    /**
     * @throws Throwable
     */
    public function bulkStore(
        BulkAttachRequest $request,
        Category          $category
    ): JsonResponse
    {
        $items = AttachSpecificationData::collectionFromArray(
            $request->validated('specifications')
        );

        $this->service->bulkAttach($category, $items);

        return response()->json([
            'message' => 'Specifications attached successfully.',
            'count' => count($items),
        ], Response::HTTP_CREATED);
    }

    /**
     * @throws Throwable
     */
    public function bulkUpdate(
        BulkUpdateRequest $request,
        Category          $category
    ): JsonResponse
    {
        $items = UpdatePivotData::collectionFromArray(
            $request->validated('specifications')
        );

        $this->service->bulkUpdate($category, $items);

        return response()->json([
            'message' => 'Specifications updated successfully.',
            'count' => count($items),
        ]);
    }


    /**
     * @throws Throwable
     */
    public function sync(
        SyncSpecificationsRequest $request,
        Category                  $category
    ): JsonResponse
    {
        $items = AttachSpecificationData::collectionFromArray(
            $request->validated('specifications')
        );

        $this->service->sync($category, $items);

        return response()->json([
            'message' => 'Specifications synced successfully.',
            'count' => count($items),
        ]);
    }


}
```

#### `modules/Categories/Http/Controllers/Admin/SpecificationController.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Categories\Http\Requests\StoreSpecificationRequest;
use Modules\Categories\Http\Requests\UpdateSpecificationRequest;
use Modules\Categories\Http\Resources\SpecificationResource;
use Modules\Categories\Models\Specification;
use Modules\Categories\Services\SpecificationService;
use Symfony\Component\HttpFoundation\Response;

final  class SpecificationController extends Controller
{

    public function __construct(private readonly SpecificationService $service)
    {

    }


    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int)$request->query('perPage', 20);
        $perPage = min($perPage, 100);
        $specifications = $this->service->paginateRoots($perPage);

        return SpecificationResource::collection($specifications);
    }


    public function store(StoreSpecificationRequest $request): JsonResponse
    {
        $specification = $this->service->create($request->validated());

        return SpecificationResource::make($specification)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);

    }


    public function show(Specification $specification): SpecificationResource
    {
        $specification->load(['parent', 'children']);

        return new SpecificationResource($specification);
    }


    public function update(UpdateSpecificationRequest $request, Specification $specification): SpecificationResource
    {
        $updated = $this->service->update($specification, $request->validated());

        return new SpecificationResource($updated);
    }

    public function destroy(int $specification): Response
    {
        $this->service->delete($specification);

        return response()->noContent();
    }

}
```

#### `modules/Categories/Http/Controllers/Api/AdminCategoryController.php`

```php
<?php

namespace Modules\Categories\Http\Controllers\Api;

class AdminCategoryController
{

}
```

#### `modules/Categories/Http/Controllers/Api/CategoryController.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Categories\Http\Resources\CategoryResource;
use Modules\Categories\Models\Category;
use Modules\Categories\Services\CategoryService;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService,
    ) {}

    /**
     * نمایش لیست دسته‌بندی‌ها
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = min($perPage, 100); // محدود کن

        $categories = $this->categoryService->paginate($perPage);

        return CategoryResource::collection($categories);
    }

    /**
     * نمایش یک دسته با slug
     */
    public function show(string $slug): CategoryResource
    {
        $category = $this->categoryService->findBySlug($slug);

        return new CategoryResource($category);
    }
}
```

#### `modules/Categories/Http/Controllers/CategoryController.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Modules\Categories\Http\Requests\CategoryRequest;
use Modules\Categories\Http\Requests\UpdateCategoryRequest;
use Modules\Categories\Http\Resources\CategoryResource;
use Modules\Categories\Models\Category;
use Modules\Categories\Services\CategoryService;

final readonly class CategoryController
{

    public function __construct(
        private CategoryService $categoryService,
    )
    {

    }

    /**
     * نمایش لیست دسته‌بندی‌ها
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int)$request->query('per_page', 15);
        $perPage = min($perPage, 100); // محدود کن

        $categories = $this->categoryService->paginate($perPage);

        return CategoryResource::collection($categories);
    }

    /**
     * نمایش یک دسته با slug
     */
    public function show(string $slug): CategoryResource
    {
        $category = $this->categoryService->findBySlug($slug);

        return new CategoryResource($category);
    }




}
```

#### `modules/Categories/Http/Requests/AttachSpecificationRequest.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Categories\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Categories\Enums\SpecificationType;

class AttachSpecificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'specification_id' => [
                'required',
                'integer',
                Rule::exists('specifications', 'id'),
            ],
            'type' => [
                'required',
                'string',
                Rule::in(SpecificationType::values()),
            ],
            'is_required'   => ['boolean'],
            'is_filterable' => ['boolean'],
            'is_important'  => ['boolean'],
            'position'      => ['integer', 'min:0'],
        ];
    }

}
```

#### `modules/Categories/Http/Requests/BulkAttachRequest.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Categories\Enums\SpecificationType;

class BulkAttachRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'specifications'                       => ['required', 'array', 'min:1', 'max:100'],
            'specifications.*.specification_id'    => [
                'required',
                'integer',
                Rule::exists('specifications', 'id'),
            ],
            'specifications.*.type' => [
                'required',
                'string',
                Rule::in(SpecificationType::values()),
            ],
            'specifications.*.is_required'   => ['boolean'],
            'specifications.*.is_filterable' => ['boolean'],
            'specifications.*.is_important'  => ['boolean'],
            'specifications.*.position'      => ['integer', 'min:0'],
        ];
    }

}
```

#### `modules/Categories/Http/Requests/BulkUpdateRequest.php`

```php
<?php

declare(strict_types=1);
namespace Modules\Categories\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Categories\Enums\SpecificationType;

class BulkUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'specifications' => ['required', 'array', 'min:1', 'max:100'],
            'specifications.*.specification_id' => ['required', 'integer'],
            'specifications.*.type' => [
                'sometimes',
                'string',
                Rule::in(SpecificationType::values()),
            ],
            'specifications.*.is_required' => ['sometimes', 'boolean'],
            'specifications.*.is_filterable' => ['sometimes', 'boolean'],
            'specifications.*.is_important' => ['sometimes', 'boolean'],
            'specifications.*.position' => ['sometimes', 'integer', 'min:0'],
        ];
    }

}
```

#### `modules/Categories/Http/Requests/CategoryRequest.php`

```php
<?php

namespace Modules\Categories\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Categories\Rules\CheckCategoryEnglishNameRule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {

        $categoryId = $this->route('category')?->id;
        return [
            "name" => ["required", "string", "max:255"],
            "english_name" => [
                "nullable",
                "string",
                "max:255",
                Rule::unique("categories", "english_name")->ignore($categoryId),
            ],
            "url" => [
                Rule::requiredIf(fn() => $this->missing('english_name')),
                "nullable",
                "string",
                "url",
                "max:255",
            ],
            'description' => ['nullable', 'string'],
            'image'       => ['nullable', 'image', 'max:2048'],
            'icon'        => ['nullable', 'string', 'max:255'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id'),
            ],
        ];
    }

}
```

#### `modules/Categories/Http/Requests/CategorySepecifcation/AttachSpecificationRequest.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Categories\Http\Requests\CategorySepecifcation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Categories\Enums\SpecificationType;

class AttachSpecificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'specification_id' => [
                'required',
                'integer',
                Rule::exists('specifications', 'id'),
            ],
            'type' => [
                'required',
                'string',
                Rule::in(SpecificationType::values()),
            ],
            'is_required'   => ['boolean'],
            'is_filterable' => ['boolean'],
            'is_important'  => ['boolean'],
            'position'      => ['integer', 'min:0'],
        ];
    }

}
```

#### `modules/Categories/Http/Requests/CategorySepecification/AttachSpecificationRequest.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Categories\Http\Requests\CategorySepecification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Categories\Enums\SpecificationType;

class AttachSpecificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'specification_id' => [
                'required',
                'integer',
                Rule::exists('specifications', 'id'),
            ],
            'type' => [
                'required',
                'string',
                Rule::in(SpecificationType::values()),
            ],
            'is_required'   => ['boolean'],
            'is_filterable' => ['boolean'],
            'is_important'  => ['boolean'],
            'position'      => ['integer', 'min:0'],
        ];
    }

}
```

#### `modules/Categories/Http/Requests/CategorySepecification/BulkAttachRequest.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Http\Requests\CategorySepecification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Categories\Enums\SpecificationType;

class BulkAttachRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'specifications'                       => ['required', 'array', 'min:1', 'max:100'],
            'specifications.*.specification_id'    => [
                'required',
                'integer',
                Rule::exists('specifications', 'id'),
            ],
            'specifications.*.type' => [
                'required',
                'string',
                Rule::in(SpecificationType::values()),
            ],
            'specifications.*.is_required'   => ['boolean'],
            'specifications.*.is_filterable' => ['boolean'],
            'specifications.*.is_important'  => ['boolean'],
            'specifications.*.position'      => ['integer', 'min:0'],
        ];
    }

}
```

#### `modules/Categories/Http/Requests/CategorySepecification/BulkUpdateRequest.php`

```php
<?php

declare(strict_types=1);
namespace Modules\Categories\Http\Requests\CategorySepecification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Categories\Enums\SpecificationType;

class BulkUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'specifications' => ['required', 'array', 'min:1', 'max:100'],
            'specifications.*.specification_id' => ['required', 'integer'],
            'specifications.*.type' => [
                'sometimes',
                'string',
                Rule::in(SpecificationType::values()),
            ],
            'specifications.*.is_required' => ['sometimes', 'boolean'],
            'specifications.*.is_filterable' => ['sometimes', 'boolean'],
            'specifications.*.is_important' => ['sometimes', 'boolean'],
            'specifications.*.position' => ['sometimes', 'integer', 'min:0'],
        ];
    }

}
```

#### `modules/Categories/Http/Requests/CategorySepecification/UpdatePivotRequest.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Categories\Http\Requests\CategorySepecification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Categories\Enums\SpecificationType;

class UpdatePivotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => [
                'sometimes',
                'string',
                Rule::in(
                    SpecificationType::values()),
            ],
            'is_required'   => ['sometimes', 'boolean'],
            'is_filterable' => ['sometimes', 'boolean'],
            'is_important'  => ['sometimes', 'boolean'],
            'position'      => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * specification_id رو از route می‌گیریم چون تو URL ه
     */
    public function validatedWithSpecificationId(int $specificationId): array
    {
        return [...$this->validated(), 'specification_id' => $specificationId];
    }

}
```

#### `modules/Categories/Http/Requests/CategorySpecification/AttachSpecificationRequest.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Categories\Http\Requests\CategorySpecification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Categories\Enums\SpecificationType;

class AttachSpecificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'specification_id' => [
                'required',
                'integer',
                Rule::exists('specifications', 'id'),
            ],
            'type' => [
                'required',
                'string',
                Rule::in(SpecificationType::values()),
            ],
            'is_required'   => ['boolean'],
            'is_filterable' => ['boolean'],
            'is_important'  => ['boolean'],
            'position'      => ['integer', 'min:0'],
        ];
    }

}
```

#### `modules/Categories/Http/Requests/CategorySpecification/BulkAttachRequest.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Http\Requests\CategorySpecification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Categories\Enums\SpecificationType;

class BulkAttachRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'specifications'                       => ['required', 'array', 'min:1', 'max:100'],
            'specifications.*.specification_id'    => [
                'required',
                'integer',
                Rule::exists('specifications', 'id'),
            ],
            'specifications.*.type' => [
                'required',
                'string',
                Rule::in(SpecificationType::values()),
            ],
            'specifications.*.is_required'   => ['boolean'],
            'specifications.*.is_filterable' => ['boolean'],
            'specifications.*.is_important'  => ['boolean'],
            'specifications.*.position'      => ['integer', 'min:0'],
        ];
    }

}
```

#### `modules/Categories/Http/Requests/CategorySpecification/BulkUpdateRequest.php`

```php
<?php

declare(strict_types=1);
namespace Modules\Categories\Http\Requests\CategorySpecification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Categories\Enums\SpecificationType;

class BulkUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'specifications' => ['required', 'array', 'min:1', 'max:100'],
            'specifications.*.specification_id' => ['required', 'integer'],
            'specifications.*.type' => [
                'sometimes',
                'string',
                Rule::in(SpecificationType::values()),
            ],
            'specifications.*.is_required' => ['sometimes', 'boolean'],
            'specifications.*.is_filterable' => ['sometimes', 'boolean'],
            'specifications.*.is_important' => ['sometimes', 'boolean'],
            'specifications.*.position' => ['sometimes', 'integer', 'min:0'],
        ];
    }

}
```

#### `modules/Categories/Http/Requests/CategorySpecification/SyncSpecificationsRequest.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Http\Requests\CategorySpecification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Categories\Enums\SpecificationType;

class SyncSpecificationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'specifications' => ['present', 'array', "min:1", 'max:100'],
            'specifications.*.specification_id' => [
                'required',
                'integer',
                Rule::exists('specifications', 'id'),
            ],
            'specifications.*.type' => [
                'required',
                'string',
                Rule::in(SpecificationType::values()),
            ],
            'specifications.*.is_required' => ['boolean'],
            'specifications.*.is_filterable' => ['boolean'],
            'specifications.*.is_important' => ['boolean'],
            'specifications.*.position' => ['integer', 'min:0'],
        ];
    }

}
```

#### `modules/Categories/Http/Requests/CategorySpecification/UpdatePivotRequest.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Categories\Http\Requests\CategorySpecification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Categories\Enums\SpecificationType;

class UpdatePivotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => [
                'sometimes',
                'string',
                Rule::in(
                    SpecificationType::values()),
            ],
            'is_required'   => ['sometimes', 'boolean'],
            'is_filterable' => ['sometimes', 'boolean'],
            'is_important'  => ['sometimes', 'boolean'],
            'position'      => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * specification_id رو از route می‌گیریم چون تو URL ه
     */
    public function validatedWithSpecificationId(int $specificationId): array
    {
        return [...$this->validated(), 'specification_id' => $specificationId];
    }

}
```

#### `modules/Categories/Http/Requests/StoreCategoryRequest.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            'english_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'english_name'),
            ],

            'url' => [
                Rule::requiredIf(fn () => $this->missing('english_name')),
                'nullable',
                'string',
                'url',
                'max:255',
            ],

            'description' => ['nullable', 'string'],
            'image'       => ['nullable', 'image', 'max:2048'],
            'icon'        => ['nullable', 'string', 'max:255'],

            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id'),
            ],
        ];
    }
}
```

#### `modules/Categories/Http/Requests/StoreSpecificationRequest.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Categories\Enums\DataType;

class StoreSpecificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('specifications', 'slug')
            ],
            'unit' => ['nullable', 'string', 'max:50'],
            'data_type' => ['nullable', 'string', Rule::in(DataType::values())],
            'parent_id' => ['nullable', 'integer', Rule::exists('specifications', 'id')],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }
}
```

#### `modules/Categories/Http/Requests/SyncSpecificationsRequest.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Categories\Enums\SpecificationType;

class SyncSpecificationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'specifications'                       => ['present', 'array', 'max:100'],
            'specifications.*.specification_id'    => [
                'required',
                'integer',
                Rule::exists('specifications', 'id'),
            ],
            'specifications.*.type' => [
                'required',
                'string',
                Rule::in(SpecificationType::values()),
            ],
            'specifications.*.is_required'   => ['boolean'],
            'specifications.*.is_filterable' => ['boolean'],
            'specifications.*.is_important'  => ['boolean'],
            'specifications.*.position'      => ['integer', 'min:0'],
        ];
    }

}
```

#### `modules/Categories/Http/Requests/UpdateCategortRequest.php`

```php
<?php

namespace Modules\Categories\Http\Requests;

class UpdateCategortRequest
{

}
```

#### `modules/Categories/Http/Requests/UpdateCategoryRequest.php`

```php
<?php

namespace Modules\Categories\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        $categoryId = $this->route()?->id ?? $this->route('category');
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],

            'english_name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'english_name')->ignore($categoryId),
            ],

            'url' => ['sometimes', 'nullable', 'string', 'url', 'max:255'],

            'description' => ['sometimes', 'nullable', 'string'],
            'image'       => ['sometimes', 'nullable', 'image', 'max:2048'],
            'icon'        => ['sometimes', 'nullable', 'string', 'max:255'],

            'parent_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('categories', 'id'),
                function ($attribute, $value, $fail) use ($categoryId) {
                    if ($value !== null && (int) $value === (int) $categoryId) {
                        $fail('یک دسته نمی‌تواند والد خودش باشد.');
                    }
                },
            ],
        ];

    }

}
```

#### `modules/Categories/Http/Requests/UpdatePivotRequest.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Categories\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Categories\Enums\SpecificationType;

class UpdatePivotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => [
                'sometimes',
                'string',
                Rule::in(
                    SpecificationType::values()),
            ],
            'is_required'   => ['sometimes', 'boolean'],
            'is_filterable' => ['sometimes', 'boolean'],
            'is_important'  => ['sometimes', 'boolean'],
            'position'      => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * specification_id رو از route می‌گیریم چون تو URL ه
     */
    public function validatedWithSpecificationId(int $specificationId): array
    {
        return [...$this->validated(), 'specification_id' => $specificationId];
    }

}
```

#### `modules/Categories/Http/Requests/UpdateSpecificationRequest.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Categories\Enums\DataType;

class UpdateSpecificationRequest extends FormRequest
{


    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        $specificationId = $this->route()->parameter('specification')?->id ?? $this->route('specification');


        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],

            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('specifications', 'slug')->ignore($specificationId),
            ],
            'unit' => ['sometimes', 'nullable', 'string', 'max:50'],

            'data_type' => [
                'sometimes',
                'nullable',
                'string',
                Rule::in(DataType::values()),
            ],
            'parent_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('specifications', 'id'),
                function ($attribute, $value, $fail) use ($specificationId) {
                    if ($value !== null && (int) $value === (int) $specificationId) {
                        $fail('A specification cannot be its own parent.');
                    }
                },
            ],
        ];

    }
}
```

#### `modules/Categories/Http/Resources/CategoryResource.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Categories\Models\Category;

/**
 * @mixin Category
 */
class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'english_name' => $this->english_name,
            'slug'         => $this->slug,
            'description'  => $this->description,
            'url'          => $this->url,
            'image_url'    => $this->image ? asset("storage/{$this->image}") : null,
            'icon'         => $this->icon,
            'parent_id'    => $this->parent_id,
            'created_at'   => $this->created_at?->toIso8601String(),

            'parent'   => new CategoryResource($this->whenLoaded('parent')),
            'children' => CategoryResource::collection($this->whenLoaded('children')),
        ];
    }
}
```

#### `modules/Categories/Http/Resources/CategorySpecificationResource.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Categories\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Categories\Models\Specification;

/**
 * @mixin Specification
 */
class CategorySpecificationResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'unit' => $this->unit,
            'data_type' => $this->data_type,
            'description' => $this->description,
            'is_active' => (bool)$this->is_active,
            'pivot' => [
                'type' => $this->pivot->type,
                'is_required' => (bool)$this->pivot->is_required,
                'is_filterable' => (bool)$this->pivot->is_filterable,
                'is_important' => (bool)$this->pivot->is_important,
                'position' => (int)$this->pivot->position,
            ],
        ];
    }

}
```

#### `modules/Categories/Http/Resources/SpecificationResource.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SpecificationResource extends JsonResource
{

    /**
     * mixin @Specification
     * @param Request $request
     * @return array
     */

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'unit' => $this->unit,
            'data_type' => $this->data_type,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'is_root' => $this->parent === null,
            'created_at' => $this->created_at?->toIso8601String(),

            'parent' => new SpecificationResource($this->whenLoaded('parent')),
            'children' => SpecificationResource::collection($this->whenLoaded('children')),
        ];

    }

}
```

### 📂 Providers

#### `modules/Categories/Providers/CategoriesProvider.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Categories\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Categories\Contracts\CategoryRepositoryContract;
use Modules\Categories\Contracts\CategoryServiceContract;
use Modules\Categories\Contracts\SpecificationRepositoryContract;
use Modules\Categories\Repositories\EloquentCategoryRepository;
use Modules\Categories\Repositories\EloquentSpecificationRepository;
use Modules\Categories\Services\CategoryService;

class CategoriesProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bind(
            CategoryRepositoryContract::class,
            EloquentCategoryRepository::class
        );

        $this->app->bind(
            CategoryServiceContract::class,
            CategoryService::class
        );

        $this->app->bind(
            SpecificationRepositoryContract::class,
            EloquentSpecificationRepository::class
        );
    }

    public function boot(): void
    {
    }


}
```

### 📂 database/factories

#### `modules/Categories/database/factories/CategoryFactory.php`

```php
<?php

declare(strict_types=1);


namespace Modules\Categories\database\factories;


use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Categories\Models\Category;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $englishName = $this->faker->unique()->words(2, true);
        return [
            'name' => $this->faker->word(),
            'english_name' => $englishName,
            'description' => $this->faker->sentence(),
            'url' => $this->faker->url(),
            'icon' => $this->faker->word(),
            'is_active' => true,
            'image' => null,
            'parent_id' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(['is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function withSlug(): static
    {
        return $this->state(function () {
            $englishName = $this->faker->unique()->words(2, true);
            return [
                'english_name' => $englishName,
                'slug' => Str::slug($englishName) . '-' . Str::random(4),
            ];
        });
    }
}


```

#### `modules/Categories/database/factories/SpecificationFactory.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Categories\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Categories\Models\Specification;

class SpecificationFactory extends Factory
{

    protected $model = Specification::class;


    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);
        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->randomNumber(4),
            'unit' => $this->faker->randomElement(['گرم', 'اینچ', 'گیگابایت', 'سانتی‌متر', null]),
            'data_type' => $this->faker->randomElement(['string', 'integer', 'decimal', 'boolean']),
            'parent_id' => null,
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }


    public function asChildOf(Specification $parent): static
    {
        return $this->state(fn(array $attributes) => [
            'parent_id' => $parent->id
        ]);
    }


    public function asGroup(): static
    {
        return $this->state(fn(array $attributes) => [
            'unit' => null,
            'data_type' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

}
```

---

## 🧩 Module: Colors


### 📂 Models

#### `modules/Colors/Models/Color.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Colors\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Colors\database\factories\ColorFactory;


/**
 * @property int $id
 * @property string $name
 * @property string $english_name
 * @property string $slug
 * @property string $code
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon| null $deleted_at
 */
final class Color extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'colors';

    protected $fillable = [
        'name',
        'english_name',
        'slug',
        'code',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string,string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function newFactory(): ColorFactory
    {
        return new ColorFactory();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }


    // ──────────── Query Scopes ────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where('name', 'like', '%' . $term . '%')
            ->orWhere('english_name', 'like', '%' . $term . '%')
            ->orWhere('code', 'like', '%' . $term . '%');
    }


}
```

### 📂 Contracts

#### `modules/Colors/Contracts/ColorRepositoryContract.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Colors\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Colors\Models\Color;

interface ColorRepositoryContract
{
    // ──────────── از BaseRepository ────────────

    public function find(int $id): ?Color;

    public function findBySlug(string $slug): ?Color;

    public function findOrFail(int $id): Color;

    public function exists(int $id): bool;

    /**
     * @param array<int> $ids
     */
    public function existsAll(array $ids): bool;

    /**
     * @return Collection<int, Color>
     */
    public function all(): Collection;

    /**
     * @return LengthAwarePaginator<Color>
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Color;

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): Color;

    public function delete(int $id): bool;


}
```

#### `modules/Colors/Contracts/ColorServiceContract.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Colors\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Colors\DataTransferObjects\ColorPublicData;
use Modules\Colors\DataTransferObjects\CreateColorData;
use Modules\Colors\DataTransferObjects\UpdateColorData;
use Modules\Colors\Models\Color;

interface ColorServiceContract
{
    // ──────────── Public API (cross-module) ────────────

    public function exists(int $colorId): bool;

    /**
     * @param array<int> $colorIds
     */
    public function existsAll(array $colorIds): bool;

    public function getById(int $colorId): ?ColorPublicData;

    /**
     * @param array<int> $colorIds
     * @return array<int, ColorPublicData>
     */
    public function getByIds(array $colorIds): array;

    public function isActive(int $colorId): bool;

    /**
     * @param array<int> $colorIds
     */
    public function areAllActive(array $colorIds): bool;

    // ──────────── Internal Methods (admin) ────────────

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findById(int $colorId): Color;

    public function findBySlug(string $slug): Color;

    public function create(CreateColorData $data): Color;

    public function update(int $colorId, UpdateColorData $data): Color;

    public function delete(int $colorId): bool;

    public function activate(int $colorId): Color;

    public function deactivate(int $colorId): Color;

    public function search(string $term, int $perPage = 15): LengthAwarePaginator;
}
```

#### `modules/Colors/Contracts/LengthAwarePaginator.php`

```php
<?php

namespace Modules\Colors\Contracts;

class LengthAwarePaginator
{

}
```

### 📂 DataTransferObjects

#### `modules/Colors/DataTransferObjects/ColorPublicData.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Colors\DataTransferObjects;

/**
 * Public DTO برای Color
 *
 * این DTO نمایش‌گر یه Color برای ماژول‌های دیگه ست.
 * فقط فیلدهای ضروری رو دارا ست — هر چی public کنیم، یه قرارداد ه.
 */
final readonly class ColorPublicData
{
    public function __construct(
        public int $id,
        public string $name,
        public string $englishName,
        public string $slug,
        public string $code,
        public bool $isActive,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id:          (int) $data['id'],
            name:        (string) $data['name'],
            englishName: (string) $data['english_name'],
            slug:        (string) $data['slug'],
            code:        (string) $data['code'],
            isActive:    (bool) $data['is_active'],
        );
    }
}
```

#### `modules/Colors/DataTransferObjects/CreateColorData.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Colors\DataTransferObjects;

final readonly class CreateColorData
{
    public function __construct(
        public string $name,
        public string $englishName,
        public string $slug,
        public string $code,
        public bool   $isActive = true,
        public int    $sortOrder = 0,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: (string)$data['name'],
            englishName: (string)$data['english_name'],
            slug: (string)$data['slug'],
            code: (string)$data['code'],
            isActive: (bool)($data['is_active'] ?? true),
            sortOrder: (int)($data['sort_order'] ?? 0),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'english_name' => $this->englishName,
            'slug' => $this->slug,
            'code' => $this->code,
            'is_active' => $this->isActive,
            'sort_order' => $this->sortOrder,
        ];
    }


}
```

#### `modules/Colors/DataTransferObjects/UpdateColorData.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Colors\DataTransferObjects;

final readonly class UpdateColorData
{
    public function __construct(
        public ?string $name = null,
        public ?string $englishName = null,
        public ?string $slug = null,
        public ?string $code = null,
        public ?bool $isActive = null,
        public ?int $sortOrder = null,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name:        isset($data['name']) ? (string) $data['name'] : null,
            englishName: isset($data['english_name']) ? (string) $data['english_name'] : null,
            slug:        isset($data['slug']) ? (string) $data['slug'] : null,
            code:        isset($data['code']) ? (string) $data['code'] : null,
            isActive:    isset($data['is_active']) ? (bool) $data['is_active'] : null,
            sortOrder:   isset($data['sort_order']) ? (int) $data['sort_order'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->name !== null) {
            $data['name'] = $this->name;
        }
        if ($this->englishName !== null) {
            $data['english_name'] = $this->englishName;
        }
        if ($this->slug !== null) {
            $data['slug'] = $this->slug;
        }
        if ($this->code !== null) {
            $data['code'] = $this->code;
        }
        if ($this->isActive !== null) {
            $data['is_active'] = $this->isActive;
        }
        if ($this->sortOrder !== null) {
            $data['sort_order'] = $this->sortOrder;
        }

        return $data;
    }

    public function hasChanges(): bool
    {
        return ! empty($this->toArray());
    }
}
```

### 📂 Events

#### `modules/Colors/Events/ColorActivated.php`

```php
<?php
declare(strict_types=1);
namespace Modules\Colors\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Colors\Models\Color;

class ColorActivated
{
    use Dispatchable;

    public function __construct(
        public readonly Color $color,
    ) {}
}
```

#### `modules/Colors/Events/ColorCreated.php`

```php
<?php
declare(strict_types=1);
namespace Modules\Colors\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Colors\Models\Color;

final class ColorCreated
{
    use Dispatchable;


    public function __construct(
        public readonly Color $color,
    )
    {

    }

}
```

#### `modules/Colors/Events/ColorDeactivated.php`

```php
<?php
declare(strict_types=1);
namespace Modules\Colors\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Colors\Models\Color;

final class ColorDeactivated
{

    use Dispatchable;

    public function __construct(
        private readonly Color $color,
    )
    {

    }
}
```

#### `modules/Colors/Events/ColorDeleted.php`

```php
<?php

declare(strict_types=1);
namespace Modules\Colors\Events;

use Illuminate\Foundation\Events\Dispatchable;

class ColorDeleted
{
    use Dispatchable;
    public function __construct(
        public readonly int $colorId,
    ) {}

}
```

#### `modules/Colors/Events/ColorUpdated.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Colors\Events;
use Illuminate\Foundation\Events\Dispatchable;
use Modules\Colors\Models\Color;

final class ColorUpdated
{
    use Dispatchable;

    public function __construct(
        public readonly Color $color,
    ) {}

}
```

### 📂 Exceptions

#### `modules/Colors/Exceptions/ColorNotFoundException.php`

```php
<?php
declare(strict_types=1);
namespace Modules\Colors\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ColorNotFoundException extends NotFoundHttpException
{
}
```

### 📂 Repositories

#### `modules/Colors/Repositories/EloquentColorRepository.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Colors\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\Colors\Contracts\ColorRepositoryContract;
use Modules\Colors\Models\Color;
use Modules\Shared\Repositories\BaseRepository;
use Override;

class EloquentColorRepository extends BaseRepository implements ColorRepositoryContract
{

    protected function model(): Model
    {
        return new Color();
    }

    public function find(int $id): ?Color
    {
        /**
         * @var Color| null
         */
        return parent::findOrFail($id);
    }
    #[Override]
    public function findOrFail(int $id): Color
    {
        /** @var Color */
        return parent::findOrFail($id);
    }

    #[Override]
    public function create(array $data): Color
    {
        /** @var Color */
        return parent::create($data);
    }

    #[Override]
    public function update(int $id, array $data): Color
    {
        /** @var Color */
        return parent::update($id, $data);
    }

    public function findBySlug(string $slug): ?Color
    {
        return $this->query()
            ->where('slug', $slug)
            ->first();
    }

    public function getActive(): Collection
    {
        return $this->query()
            ->active()
            ->ordered()
            ->get();
    }

    public function search(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->search($term)
            ->ordered()
            ->paginate($perPage);
    }

    public function countActiveByIds(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }
        return $this->query()
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->count();
    }

    public function findByIds(array $ids): Collection
    {
        if (empty($ids)) {
            return new Collection();
        }

        return $this->query()
            ->whereIn('id', $ids)
            ->ordered()
            ->get();
    }

    public function paginate(int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->query()
            ->ordered()
            ->paginate($perPage);
    }
}
```

### 📂 Services

#### `modules/Colors/Services/ColorService.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Colors\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Colors\Contracts\ColorRepositoryContract;
use Modules\Colors\Contracts\ColorServiceContract;
use Modules\Colors\DataTransferObjects\ColorPublicData;
use Modules\Colors\DataTransferObjects\CreateColorData;
use Modules\Colors\DataTransferObjects\UpdateColorData;
use Modules\Colors\Events\ColorActivated;
use Modules\Colors\Events\ColorCreated;
use Modules\Colors\Events\ColorDeactivated;
use Modules\Colors\Events\ColorDeleted;
use Modules\Colors\Events\ColorUpdated;
use Modules\Colors\Exceptions\ColorNotFoundException;
use Modules\Colors\Models\Color;
use Throwable;

final class ColorService implements ColorServiceContract
{

    public function __construct(
        private readonly ColorRepositoryContract $repository,
    )
    {
    }


    public function exists(int $colorId): bool
    {
        return $this->repository->exists($colorId);
    }

    public function existsAll(array $colorIds): bool
    {
        return $this->repository->existsAll($colorIds);
    }

    public function getById(int $colorId): ?ColorPublicData
    {
        $color = $this->repository->find($colorId);

        if ($color === null) {
            return null;
        }

        return $this->toPublicData($color);
    }

    public function getByIds(array $colorIds): array
    {
        if (empty($colorIds)) {
            return [];
        }

        $colors = $this->repository->findByIds(array_unique($colorIds));

        $result = [];
        foreach ($colors as $color) {
            $result[$color->id] = $this->toPublicData($color);
        }

        return $result;
    }

    public function isActive(int $colorId): bool
    {
        $color = $this->repository->find($colorId);

        return $color !== null && $color->is_active;
    }

    public function areAllActive(array $colorIds): bool
    {
        if (empty($colorIds)) {
            return true;
        }

        $uniqueIds = array_unique($colorIds);
        $count = $this->repository->countActiveByIds($uniqueIds);

        return $count === count($uniqueIds);
    }
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function findById(int $colorId): Color
    {
        $color = $this->repository->find($colorId);

        if ($color === null) {
            throw new ColorNotFoundException("Color with id {$colorId} not found");
        }
        return $color;
    }

    public function findBySlug(string $slug): Color
    {
        $color = $this->repository->findBySlug($slug);

        if ($color === null) {
            throw new ColorNotFoundException("Color with id {$slug} not found");
        }

        return $color;
    }

    /**
     * @throws Throwable
     */
    public function create(CreateColorData $data): Color
    {
        return DB::transaction(function () use ($data) {
            $color = $this->repository->create($data->toArray());

            ColorCreated::dispatch($color);

            return $color;
        });
    }

    /**
     * @throws Throwable
     */
    public function update(int $colorId, UpdateColorData $data): Color
    {

        if (!$data->hasChanges()){
            return $this->findById($colorId);
        }

        return DB::transaction(function () use ($colorId,$data) {
            $color = $this->repository->update($colorId,$data->toArray());

            ColorUpdated::dispatch($color);

            return $color;
        });
    }

    /**
     * @throws Throwable
     */
    public function delete(int $colorId): bool
    {
        return DB::transaction(function () use ($colorId) {
             $this->findById($colorId);

            $deleted=$this->repository->delete($colorId);

            if ($deleted){
                ColorDeleted::dispatch($colorId);
            }
            return $deleted;
        });
    }

    /**
     * @throws Throwable
     */
    public function activate(int $colorId): Color
    {
        return DB::transaction(function () use ($colorId) {
            $color = $this->repository->update($colorId, ['is_active' => true]);

            ColorActivated::dispatch($color);

            return $color;
        });
    }

    /**
     * @throws Throwable
     */
    public function deactivate(int $colorId): Color
    {
        return DB::transaction(function () use ($colorId) {
            $color = $this->repository->update($colorId, ['is_active' => false]);

            ColorDeactivated::dispatch($color);

            return $color;
        });
    }

    public function search(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->search($term, $perPage);
    }


    private function toPublicData(Color $color): ColorPublicData
    {
        return new ColorPublicData(
            id:          $color->id,
            name:        $color->name,
            englishName: $color->english_name,
            slug:        $color->slug,
            code:        $color->code,
            isActive:    $color->is_active,
        );
    }
}
```

### 📂 Http

#### `modules/Colors/Http/Controllers/Admin/AdminColorController.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Colors\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Modules\Colors\Contracts\ColorServiceContract;
use Modules\Colors\DataTransferObjects\CreateColorData;
use Modules\Colors\DataTransferObjects\UpdateColorData;
use Modules\Colors\Http\Requests\StoreColorRequest;
use Modules\Colors\Http\Requests\UpdateColorRequest;
use Modules\Colors\Http\Resources\ColorResource;
use Modules\Colors\Models\Color;

final class AdminColorController extends Controller
{
    public function __construct(
        private readonly ColorServiceContract $colorService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->query('per_page', 15);

        $colors = $this->colorService->paginate($perPage);

        return ColorResource::collection($colors);
    }

    public function store(StoreColorRequest $request): JsonResponse
    {
        $data = CreateColorData::fromArray($request->validated());

        $color = $this->colorService->create($data);

        return ColorResource::make($color)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Color $color): ColorResource
    {
        return ColorResource::make($color);
    }

    public function update(UpdateColorRequest $request, Color $color): ColorResource
    {
        $data = UpdateColorData::fromArray($request->validated());

        $updated = $this->colorService->update($color->id, $data);

        return ColorResource::make($updated);
    }

    public function destroy(Color $color): JsonResponse
    {
        $this->colorService->delete($color->id);

        return response()->json(null, 204);
    }

    public function activate(Color $color): ColorResource
    {
        $updated = $this->colorService->activate($color->id);

        return ColorResource::make($updated);
    }

    public function deactivate(Color $color): ColorResource
    {
        $updated = $this->colorService->deactivate($color->id);

        return ColorResource::make($updated);
    }

    public function search(Request $request): AnonymousResourceCollection
    {
        $term = (string) $request->query('q', '');
        $perPage = (int) $request->query('per_page', 15);

        $colors = $this->colorService->search($term, $perPage);

        return ColorResource::collection($colors);
    }
}
```

#### `modules/Colors/Http/Requests/StoreColorRequest.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Colors\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreColorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;  // TODO: اضافه کردن authorization در آینده
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'english_name' => [
                'required',
                'string',
                'max:100',
            ],
            'slug' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('colors', 'slug'),
            ],
            'code' => [
                'required',
                'string',
                'max:20',
                'regex:/^#[0-9A-Fa-f]{6}$/',
                Rule::unique('colors', 'code'),
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
            'sort_order' => [
                'sometimes',
                'integer',
                'min:0',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex'  => 'Slug فقط می‌تواند شامل حروف کوچک، اعداد و خط تیره باشد.',
            'code.regex'  => 'کد رنگ باید فرمت hex 6 رقمی باشد (مثل #FF0000).',
            'slug.unique' => 'این slug قبلاً استفاده شده است.',
            'code.unique' => 'این کد رنگ قبلاً استفاده شده است.',
        ];
    }
}
```

#### `modules/Colors/Http/Requests/UpdateColorRequest.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Colors\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Colors\Models\Color;

final class UpdateColorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Color $color */
        $color = $this->route('color');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],
            'english_name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('colors', 'slug')->ignore($color->id),
            ],
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                'regex:/^#[0-9A-Fa-f]{6}$/',
                Rule::unique('colors', 'code')->ignore($color->id),
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
            'sort_order' => [
                'sometimes',
                'integer',
                'min:0',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex'  => 'Slug فقط می‌تواند شامل حروف کوچک، اعداد و خط تیره باشد.',
            'code.regex'  => 'کد رنگ باید فرمت hex 6 رقمی باشد (مثل #FF0000).',
            'slug.unique' => 'این slug قبلاً استفاده شده است.',
            'code.unique' => 'این کد رنگ قبلاً استفاده شده است.',
        ];
    }
}
```

#### `modules/Colors/Http/Resources/ColorResource.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Colors\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Colors\Models\Color;

/**
 * @mixin Color
 */
final class ColorResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'english_name' => $this->english_name,
            'slug'         => $this->slug,
            'code'         => $this->code,
            'is_active'    => (bool) $this->is_active,
            'sort_order'   => (int) $this->sort_order,
            'created_at'   => $this->created_at?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),
        ];
    }
}
```

### 📂 Providers

#### `modules/Colors/Providers/ColorsServiceProvider.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Colors\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Colors\Contracts\ColorRepositoryContract;
use Modules\Colors\Contracts\ColorServiceContract;
use Modules\Colors\Repositories\EloquentColorRepository;
use Modules\Colors\Services\ColorService;

class ColorsServiceProvider extends ServiceProvider
{
    public function register(): void
    {

        $this->app->bind(
            ColorRepositoryContract::class,
            EloquentColorRepository::class
        );

        $this->app->bind(
            ColorServiceContract::class,
            ColorService::class
        );

    }

    public function boot(): void
    {

    }

}
```

### 📂 database/factories

#### `modules/Colors/database/factories/ColorFactory.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Colors\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Colors\Models\Color;

class ColorFactory extends Factory
{

    protected $model = Color::class;

    /**
     * @return array<string,mixed>
     */
    public function definition(): array
    {
        $englishName = $this->faker->unique()->colorName();

        return [
            'name' => $this->faker->word(),
            'english_name' => $englishName,
            'slug' => Str::slug($englishName) . '-' . Str::random(4),
            'code' => $this->faker->unique()->hexColor(),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function active(): static
    {
        return $this->state(['is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function red(): static
    {
        return $this->state([
            'name' => 'قرمز',
            'english_name' => 'red',
            'slug' => 'red-' . Str::random(4),
            'code' => '#FF0000',
        ]);

    }
}
```

---

## 🧩 Module: Main


### 📂 Providers

#### `modules/Main/Providers/ModuleProvider.php`

```php
<?php

namespace Modules\Main\Providers;

use App\ModuleSystem\ModuleRegistrar;
use App\Services\ModuleSystem\Contracts\ExecutionPhase;
use App\Services\ModuleSystem\Contracts\ModuleDiscoverer;
use App\Services\ModuleSystem\Discovery\FileSystemDiscoverer;
use App\Services\ModuleSystem\Handlers\ConfigHandler;
use App\Services\ModuleSystem\Handlers\MigrationHandler;
use App\Services\ModuleSystem\Handlers\ProviderHandler;
use App\Services\ModuleSystem\Handlers\RouteHandler;
use App\Services\ModuleSystem\Handlers\TranslationHandler;
use App\Services\ModuleSystem\ModuleLoader;
use App\Services\ModuleSystem\ModuleManifest;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

final class ModuleProvider extends ServiceProvider
{

    /**
     * @throws BindingResolutionException
     */
    public function register(): void
    {
        $this->bindDiscoverer();
        $this->bindManifest();
        $this->bindHandlers();
        $this->bindLoader();

        $this->app->make(ModuleLoader::class)->loadForPhase(ExecutionPhase::Register);


    }

    /**
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $this->app->make(ModuleLoader::class)->loadForPhase(ExecutionPhase::Boot);

        $this->app->booted(function () {
            $this->app->make(ModuleLoader::class)->loadForPhase(ExecutionPhase::Booted);
        });
    }

    private function bindDiscoverer(): void
    {
        $this->app->singleton(ModuleDiscoverer::class, fn($app) => new FileSystemDiscoverer(
            modulesPath: base_path('modules'),
            excludedModules: config('module.excluded', ['Main']),
        ));
    }

    private function bindManifest(): void
    {
        $this->app->singleton(ModuleManifest::class, function ($app) {
            return new ModuleManifest(
                builder: fn() => $app->make(ModuleLoader::class)->buildManifest(),
                files: $app->make(Filesystem::class),
                manifestPath: $app->bootstrapPath('cache/modules.php'),
            );
        });
    }

    private function bindHandlers(): void
    {
        $this->app->tag([
            ProviderHandler::class,
            ConfigHandler::class,
            MigrationHandler::class,
            TranslationHandler::class,
            RouteHandler::class,
        ], 'module.handler');

        $this->app->when(ProviderHandler::class)
            ->needs('$namespaceRoot')
            ->give(config('module.namespace', 'Modules'));

    }

    private function bindLoader(): void
    {
        $this->app->singleton(ModuleLoader::class, fn($app) => new ModuleLoader(
            discoverer: $app->make(ModuleDiscoverer::class),
            manifest: $app->make(ModuleManifest::class),
            handlers: iterator_to_array($app->tagged('module.handler')),
        ));
    }

}
```

---

## 🧩 Module: Products


### 📂 Models

#### `modules/Products/Models/Product.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Products\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Products\database\factories\ProductFactory;

/**
 * @property int $id
 * @property string $title
 * @property string $en_title
 * @property string $slug
 * @property string|null $description
 * @property string|null $content
 * @property string|null $image
 * @property int|null $brand_id
 * @property int $user_id
 * @property string $status
 * @property bool $is_active
 * @property bool $is_featured
 * @property int $view_count
 * @property int $fake_view_count
 * @property int $sold_count
 * @property int $lowest_price
 * @property int $highest_price
 * @property int $total_stock
 * @property int $variants_count
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property Carbon|null $published_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
class Product extends Model
{

    use HasFactory;
    use SoftDeletes;

    public const string STATUS_DRAFT = 'draft';
    public const string STATUS_PUBLISHED = 'published';
    public const string STATUS_ARCHIVED = 'archived';

    public const array PRODUCT_STATUES = [self::STATUS_DRAFT, self::STATUS_PUBLISHED, self::STATUS_ARCHIVED];

    protected $table = 'products';


    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'en_title',
        'slug',
        'description',
        'content',
        'image',
        'brand_id',
        'user_id',
        'status',
        'is_active',
        'is_featured',
        'view_count',
        'fake_view_count',
        'sold_count',
        'lowest_price',
        'highest_price',
        'total_stock',
        'variants_count',
        'meta_title',
        'meta_description',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'view_count' => 'integer',
            'fake_view_count' => 'integer',
            'sold_count' => 'integer',
            'lowest_price' => 'integer',
            'highest_price' => 'integer',
            'total_stock' => 'integer',
            'variants_count' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }


    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ──────────── Query Scopes ────────────
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeByBrand(Builder $query, int $brandId): Builder
    {
        return $query->where('brand_id', $brandId);
    }

    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('is_featured')->orderByDesc('created_at');
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('en_title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }


    public function getTotalViewsAttribute(): int
    {
        return $this->view_count + $this->fake_view_count;
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->is_active
            && $this->status === self::STATUS_PUBLISHED
            && $this->total_stock > 0;
    }

}
```

### 📂 Contracts

#### `modules/Products/Contracts/ProductRepositoryContract.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Products\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Products\Models\Product;

interface ProductRepositoryContract
{
    // ──────────── Base CRUD ────────────

    public function find(int $id): ?Product;
    public function findOrFail(int $id): Product;
    public function exists(int $id): bool;

    /**
     * @param array<int> $ids
     */
    public function existsAll(array $ids): bool;

    /**
     * @return Collection<int, Product>
     */
    public function all(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Product;

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): Product;

    public function delete(int $id): bool;

    // ──────────── Domain-specific ────────────

    public function findBySlug(string $slug): ?Product;

    /**
     * @return Collection<int, Product>
     */
    public function getActive(): Collection;

    /**
     * @return Collection<int, Product>
     */
    public function getFeatured(int $limit = 10): Collection;

    public function search(string $term, int $perPage = 15): LengthAwarePaginator;

    public function getByBrand(int $brandId, int $perPage = 15): LengthAwarePaginator;

    public function getByUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Pattern: Intent-based Method
     *
     * بهترین از "incrementView" نه از Builder leak (مثل getQuery())
     */
    public function incrementViewCount(int $productId): bool;

    public function incrementSoldCount(int $productId, int $by = 1): bool;

    public function countActiveByIds(array $ids): int;

    /**
     * @param array<int> $ids
     * @return Collection<int, Product>
     */
    public function findByIds(array $ids): Collection;
}
```

#### `modules/Products/Contracts/ProductServiceContract.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Products\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Products\DataTransferObjects\CreateProductData;
use Modules\Products\DataTransferObjects\ProductPublicData;
use Modules\Products\DataTransferObjects\UpdateProductData;
use Modules\Products\Models\Product;


interface ProductServiceContract
{
    // ──────────── Public API (cross-module) ────────────

    public function exists(int $productId): bool;

    /**
     * @param array<int> $productIds
     */
    public function existsAll(array $productIds): bool;

    public function getById(int $productId): ?ProductPublicData;

    /**
     * @param array<int> $productIds
     * @return array<int, ProductPublicData>
     */
    public function getByIds(array $productIds): array;

    public function isActive(int $productId): bool;

    /**
     * @param array<int> $productIds
     */
    public function areAllActive(array $productIds): bool;

    /**
     * افزایش تعداد بازدید (با event)
     */
    public function recordView(int $productId, ?int $userId = null): void;

    // ──────────── Internal Methods (admin) ────────────

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findById(int $productId): Product;

    public function findBySlug(string $slug): Product;

    public function create(CreateProductData $data): Product;

    public function update(int $productId, UpdateProductData $data): Product;

    public function delete(int $productId): bool;

    public function publish(int $productId): Product;

    public function archive(int $productId): Product;

    public function activate(int $productId): Product;

    public function deactivate(int $productId): Product;

    public function feature(int $productId): Product;

    public function unfeature(int $productId): Product;

    public function search(string $term, int $perPage = 15): LengthAwarePaginator;

    public function getByBrand(int $brandId, int $perPage = 15): LengthAwarePaginator;

    public function getByUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    public function getFeatured(int $limit = 10): array;
}
```

### 📂 DataTransferObjects

#### `modules/Products/DataTransferObjects/CreateProductData.php`

```php
<?php
declare(strict_types=1);
namespace Modules\Products\DataTransferObjects;

use Modules\Products\Models\Product;

final readonly class CreateProductData
{

    public function __construct(
        public string $title,
        public string $enTitle,
        public string $slug,
        public int $userId,
        public ?string $description = null,
        public ?string $content = null,
        public ?string $image = null,
        public ?int $brandId = null,
        public string $status = Product::STATUS_DRAFT,
        public bool $isActive = true,
        public bool $isFeatured = false,
        public int $fakeViewCount = 0,
        public ?string $metaTitle = null,
        public ?string $metaDescription = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title:           (string) $data['title'],
            enTitle:         (string) $data['en_title'],
            slug:            (string) $data['slug'],
            userId:          (int) $data['user_id'],
            description:     isset($data['description']) ? (string) $data['description'] : null,
            content:         isset($data['content']) ? (string) $data['content'] : null,
            image:           isset($data['image']) ? (string) $data['image'] : null,
            brandId:         isset($data['brand_id']) ? (int) $data['brand_id'] : null,
            status:          (string) ($data['status'] ?? Product::STATUS_DRAFT),
            isActive:        (bool) ($data['is_active'] ?? true),
            isFeatured:      (bool) ($data['is_featured'] ?? false),
            fakeViewCount:   (int) ($data['fake_view_count'] ?? 0),
            metaTitle:       isset($data['meta_title']) ? (string) $data['meta_title'] : null,
            metaDescription: isset($data['meta_description']) ? (string) $data['meta_description'] : null,
        );
    }

    public function toArray(): array
    {
        return [
            'title'             => $this->title,
            'en_title'          => $this->enTitle,
            'slug'              => $this->slug,
            'user_id'           => $this->userId,
            'description'       => $this->description,
            'content'           => $this->content,
            'image'             => $this->image,
            'brand_id'          => $this->brandId,
            'status'            => $this->status,
            'is_active'         => $this->isActive,
            'is_featured'       => $this->isFeatured,
            'fake_view_count'   => $this->fakeViewCount,
            'meta_title'        => $this->metaTitle,
            'meta_description' => $this->metaDescription,
        ];
    }


}
```

#### `modules/Products/DataTransferObjects/ProductPublicData.php`

```php
<?php
declare(strict_types=1);
namespace Modules\Products\DataTransferObjects;

class ProductPublicData
{

    public function __construct(
        public int $id,
        public string $title,
        public string $enTitle,
        public string $slug,
        public ?string $description,
        public ?string $image,
        public ?int $brandId,
        public string $status,
        public bool $isActive,
        public bool $isFeatured,
        public int $totalViews,         // computed: view + fake_view
        public int $soldCount,
        public int $lowestPrice,
        public int $highestPrice,
        public int $totalStock,
        public bool $isAvailable,       // computed
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id:           (int) $data['id'],
            title:        (string) $data['title'],
            enTitle:      (string) $data['en_title'],
            slug:         (string) $data['slug'],
            description:  isset($data['description']) ? (string) $data['description'] : null,
            image:        isset($data['image']) ? (string) $data['image'] : null,
            brandId:      isset($data['brand_id']) ? (int) $data['brand_id'] : null,
            status:       (string) $data['status'],
            isActive:     (bool) $data['is_active'],
            isFeatured:   (bool) $data['is_featured'],
            totalViews:   (int) ($data['total_views'] ?? 0),
            soldCount:    (int) $data['sold_count'],
            lowestPrice:  (int) $data['lowest_price'],
            highestPrice: (int) $data['highest_price'],
            totalStock:   (int) $data['total_stock'],
            isAvailable:  (bool) ($data['is_available'] ?? false),
        );
    }
}
```

#### `modules/Products/DataTransferObjects/UpdateProductData.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Products\DataTransferObjects;

final readonly class UpdateProductData
{

    public function __construct(
        public ?string $title = null,
        public ?string $enTitle = null,
        public ?string $slug = null,
        public ?string $description = null,
        public ?string $content = null,
        public ?string $image = null,
        public ?int $brandId = null,
        public ?string $status = null,
        public ?bool $isActive = null,
        public ?bool $isFeatured = null,
        public ?int $fakeViewCount = null,
        public ?string $metaTitle = null,
        public ?string $metaDescription = null,
    ) {}



    public static function fromArray(array $data): self
    {
        return new self(
            title:           isset($data['title']) ? (string) $data['title'] : null,
            enTitle:         isset($data['en_title']) ? (string) $data['en_title'] : null,
            slug:            isset($data['slug']) ? (string) $data['slug'] : null,
            description:     array_key_exists('description', $data) ? $data['description'] : null,
            content:         array_key_exists('content', $data) ? $data['content'] : null,
            image:           array_key_exists('image', $data) ? $data['image'] : null,
            brandId:         array_key_exists('brand_id', $data) ? $data['brand_id'] : null,
            status:          isset($data['status']) ? (string) $data['status'] : null,
            isActive:        isset($data['is_active']) ? (bool) $data['is_active'] : null,
            isFeatured:      isset($data['is_featured']) ? (bool) $data['is_featured'] : null,
            fakeViewCount:   isset($data['fake_view_count']) ? (int) $data['fake_view_count'] : null,
            metaTitle:       array_key_exists('meta_title', $data) ? $data['meta_title'] : null,
            metaDescription: array_key_exists('meta_description', $data) ? $data['meta_description'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'title'             => $this->title,
            'en_title'          => $this->enTitle,
            'slug'              => $this->slug,
            'description'       => $this->description,
            'content'           => $this->content,
            'image'             => $this->image,
            'brand_id'          => $this->brandId,
            'status'            => $this->status,
            'is_active'         => $this->isActive,
            'is_featured'       => $this->isFeatured,
            'fake_view_count'   => $this->fakeViewCount,
            'meta_title'        => $this->metaTitle,
            'meta_description'  => $this->metaDescription,
        ], fn($value) => $value !== null);
    }
    public function hasChanges(): bool
    {
        return ! empty(array_filter($this->toArray()));
    }



}
```

### 📂 Events

#### `modules/Products/Events/ProductArchived.php`

```php
<?php

declare(strict_types=1);
namespace Modules\Products\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Products\Models\Product;

final class ProductArchived
{

    use Dispatchable;

    public function __construct(
        public readonly Product    $product,
    ) {}
}
```

#### `modules/Products/Events/ProductCreated.php`

```php
<?php

namespace Modules\Products\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Products\Models\Product;

final class ProductCreated
{

    use Dispatchable;

    public function __construct(
        public readonly Product    $product,
    ) {}
}
```

#### `modules/Products/Events/ProductDeleted.php`

```php
<?php
declare(strict_types=1);
namespace Modules\Products\Events;

use Illuminate\Foundation\Events\Dispatchable;

final class ProductDeleted
{
    use Dispatchable;

    public function __construct(
        public readonly int $productId,
    ) {}

}
```

#### `modules/Products/Events/ProductPublished.php`

```php
<?php
declare(strict_types=1);
namespace Modules\Products\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Products\Models\Product;

final class ProductPublished
{

    use Dispatchable;

    public function __construct(
        public readonly Product $product,
    ) {}
}
```

#### `modules/Products/Events/ProductUpdated.php`

```php
<?php
declare(strict_types=1);
namespace Modules\Products\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Products\Models\Product;

final class ProductUpdated
{
    use Dispatchable;

    public function __construct(
        public readonly Product    $product,
    ) {}

}
```

#### `modules/Products/Events/ProductViewed.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Products\Events;

use Illuminate\Foundation\Events\Dispatchable;

final class ProductViewed
{
    use Dispatchable;

    public function __construct(
        public readonly int  $productId,
        public readonly ?int $userId = null,  // اگه login کرده
    )
    {
    }

}
```

### 📂 Exceptions

#### `modules/Products/Exceptions/ProductNotFoundException.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Products\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductNotFoundException extends NotFoundHttpException
{

}
```

### 📂 Repositories

#### `modules/Products/Repositories/EloquentProductRepository.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Products\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\Products\Contracts\ProductRepositoryContract;
use Modules\Products\DataTransferObjects\ProductPublicData;
use Modules\Products\Models\Product;
use Modules\Shared\Repositories\BaseRepository;

/**
 * Eloquent implementation of ProductRepositoryContract.
 *
 * Pattern: Repository Pattern + Template Method
 *
 * چرا extends BaseRepository؟
 * - DRY: متدهای CRUD مشترک رو inherit می‌کنیم
 * - Type-safety: ولی متدهای CRUD رو override می‌کنیم برای return type دقیق
 *
 * چرا final؟
 * - این implementation نباید extend شه. اگه نیاز به behavior متفاوت داری،
 *   یه implementation جدید بساز (مثل CachedProductRepository).
 */

final class EloquentProductRepository extends BaseRepository implements ProductRepositoryContract
{
    protected function model(): Model
    {
        return new Product();
    }

    // ──────────── Type-safe overrides ────────────

    public function find(int $id): ?Product
    {
        /** @var Product|null */
        return parent::find($id);
    }

    public function findOrFail(int $id): Product
    {
        /** @var Product */
        return parent::findOrFail($id);
    }

    public function create(array $data): Product
    {
        /** @var Product */
        return parent::create($data);
    }

    public function update(int $id, array $data): Product
    {
        /** @var Product */
        return parent::update($id, $data);
    }

    public function paginate(int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->query()
            ->ordered()
            ->paginate($perPage);
    }

    // ──────────── Domain-specific ────────────

    public function findBySlug(string $slug): Product
    {
        return $this->query()
            ->where('slug', $slug)
            ->first();
    }

    public function getActive(): Collection
    {
        return $this->query()
            ->active()
            ->published()
            ->ordered()
            ->get();
    }

    public function getFeatured(int $limit = 10): Collection
    {
        return $this->query()
            ->active()
            ->published()
            ->featured()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function search(string $term, int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->query()
            ->search($term)
            ->ordered()
            ->paginate($perPage);
    }

    public function getByBrand(int $brandId, int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->query()
            ->byBrand($brandId)
            ->ordered()
            ->paginate($perPage);
    }

    public function getByUser(int $userId, int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->query()
            ->byUser($userId)
            ->ordered()
            ->paginate($perPage);
    }

    public function incrementViewCount(int $productId): bool
    {
        return $this->query()
                ->where('id', $productId)
                ->increment('view_count') > 0;
    }

    public function incrementSoldCount(int $productId, int $by = 1): bool
    {
        return $this->query()
                ->where('id', $productId)
                ->increment('sold_count', $by) > 0;
    }

    public function countActiveByIds(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        return $this->query()
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->count();
    }

    public function findByIds(array $ids): Collection
    {
        if (empty($ids)) {
            return new Collection();
        }

        return $this->query()
            ->whereIn('id', $ids)
            ->ordered()
            ->get();
    }

    public function getById(int $productId): ?ProductPublicData
    {
        // TODO: Implement getById() method.
    }

    public function getByIds(array $productIds): array
    {
        // TODO: Implement getByIds() method.
    }

    public function isActive(int $productId): bool
    {
        // TODO: Implement isActive() method.
    }

    public function areAllActive(array $productIds): bool
    {
        // TODO: Implement areAllActive() method.
    }

    public function recordView(int $productId, ?int $userId = null): void
    {
        // TODO: Implement recordView() method.
    }

    public function findById(int $productId): Product
    {
        // TODO: Implement findById() method.
    }

    public function publish(int $productId): Product
    {
        // TODO: Implement publish() method.
    }

    public function archive(int $productId): Product
    {
        // TODO: Implement archive() method.
    }

    public function activate(int $productId): Product
    {
        // TODO: Implement activate() method.
    }

    public function deactivate(int $productId): Product
    {
        // TODO: Implement deactivate() method.
    }

    public function feature(int $productId): Product
    {
        // TODO: Implement feature() method.
    }

    public function unfeature(int $productId): Product
    {
        // TODO: Implement unfeature() method.
    }
}
```

### 📂 Services

#### `modules/Products/Services/ProductService.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Products\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Products\Contracts\ProductRepositoryContract;
use Modules\Products\Contracts\ProductServiceContract;
use Modules\Products\DataTransferObjects\CreateProductData;
use Modules\Products\DataTransferObjects\ProductPublicData;
use Modules\Products\DataTransferObjects\UpdateProductData;
use Modules\Products\Events\ProductArchived;
use Modules\Products\Events\ProductCreated;
use Modules\Products\Events\ProductDeleted;
use Modules\Products\Events\ProductPublished;
use Modules\Products\Events\ProductUpdated;
use Modules\Products\Events\ProductViewed;
use Modules\Products\Exceptions\ProductNotFoundException;
use Modules\Products\Models\Product;
use Throwable;

/**
 * Service for Product business logic.
 *
 * Patterns:
 * - Service Layer (encapsulate business logic)
 * - Transaction Script (DB::transaction())
 * - Domain Events (dispatch on state changes)
 * - Repository Pattern (decouple from DB)
 *
 * چرا transactions در همه write methods؟
 * - اگه event listener fail بشه، rollback می‌شه
 * - data consistency
 * - atomic operations
 */
final readonly class ProductService implements ProductServiceContract
{
    public function __construct(
        private readonly ProductRepositoryContract $repository,
    ) {}

    // ──────────── Internal Methods (admin) ────────────

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function findById(int $productId): Product
    {
        $product = $this->repository->find($productId);

        if ($product === null) {
            throw new ProductNotFoundException("Product with id {$productId} not found");
        }

        return $product;
    }

    public function findBySlug(string $slug): Product
    {
        $product = $this->repository->findBySlug($slug);

        if ($product === null) {
            throw new ProductNotFoundException("Product with slug '{$slug}' not found");
        }

        return $product;
    }

    public function create(CreateProductData $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = $this->repository->create($data->toArray());

            ProductCreated::dispatch($product);

            return $product;
        });
    }

    /**
     * @throws Throwable
     */
    public function update(int $productId, UpdateProductData $data): Product
    {
        // Pattern: Early Return Optimization
        if (! $data->hasChanges()) {
            return $this->findById($productId);
        }

        return DB::transaction(function () use ($productId, $data) {
            $product = $this->repository->update($productId, $data->toArray());

            ProductUpdated::dispatch($product);

            return $product;
        });
    }

    /**
     * @throws Throwable
     */
    public function delete(int $productId): bool
    {
        return DB::transaction(function () use ($productId) {
            $this->findById($productId);  // validation

            $deleted = $this->repository->delete($productId);

            if ($deleted) {
                ProductDeleted::dispatch($productId);
            }

            return $deleted;
        });
    }

    /**
     * Pattern: State Transition Method
     *
     * این متد یه state transition انجام می‌ده:
     * draft/archived → published
     *
     * چرا یه متد جداگانه؟
     * - explicit business intent
     * - می‌تونیم validation اضافه کنیم (مثلاً "نمی‌تونی publish کنی اگه image نداره")
     * - event مخصوص خودش (ProductPublished)
     */
    public function publish(int $productId): Product
    {
        return DB::transaction(function () use ($productId) {
            $product = $this->repository->update($productId, [
                'status'       => Product::STATUS_PUBLISHED,
                'published_at' => now(),
            ]);

            ProductPublished::dispatch($product);

            return $product;
        });
    }

    public function archive(int $productId): Product
    {
        return DB::transaction(function () use ($productId) {
            $product = $this->repository->update($productId, [
                'status' => Product::STATUS_ARCHIVED,
            ]);

            ProductArchived::dispatch($product);

            return $product;
        });
    }

    public function activate(int $productId): Product
    {
        return DB::transaction(function () use ($productId) {
            $product = $this->repository->update($productId, ['is_active' => true]);
            ProductUpdated::dispatch($product);
            return $product;
        });
    }

    public function deactivate(int $productId): Product
    {
        return DB::transaction(function () use ($productId) {
            $product = $this->repository->update($productId, ['is_active' => false]);
            ProductUpdated::dispatch($product);
            return $product;
        });
    }

    public function feature(int $productId): Product
    {
        return DB::transaction(function () use ($productId) {
            $product = $this->repository->update($productId, ['is_featured' => true]);
            ProductUpdated::dispatch($product);
            return $product;
        });
    }

    public function unfeature(int $productId): Product
    {
        return DB::transaction(function () use ($productId) {
            $product = $this->repository->update($productId, ['is_featured' => false]);
            ProductUpdated::dispatch($product);
            return $product;
        });
    }

    public function search(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->search($term, $perPage);
    }

    public function getByBrand(int $brandId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getByBrand($brandId, $perPage);
    }

    public function getByUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getByUser($userId, $perPage);
    }

    public function getFeatured(int $limit = 10): array
    {
        $products = $this->repository->getFeatured($limit);

        return $products->map(fn($p) => $this->toPublicData($p))->all();
    }

    // ──────────── Public API ────────────

    public function exists(int $productId): bool
    {
        return $this->repository->exists($productId);
    }

    public function existsAll(array $productIds): bool
    {
        return $this->repository->existsAll($productIds);
    }

    public function getById(int $productId): ?ProductPublicData
    {
        $product = $this->repository->find($productId);

        return $product === null ? null : $this->toPublicData($product);
    }

    public function getByIds(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $products = $this->repository->findByIds(array_unique($productIds));

        $result = [];
        foreach ($products as $product) {
            $result[$product->id] = $this->toPublicData($product);
        }

        return $result;
    }

    public function isActive(int $productId): bool
    {
        $product = $this->repository->find($productId);

        return $product !== null && $product->is_active;
    }

    public function areAllActive(array $productIds): bool
    {
        if (empty($productIds)) {
            return true;
        }

        $uniqueIds = array_unique($productIds);
        $count = $this->repository->countActiveByIds($uniqueIds);

        return $count === count($uniqueIds);
    }

    /**
     * Pattern: Fire-and-Forget for Analytics
     *
     * چرا void return؟
     * - این یه side effect ه، نه business logic
     * - caller نباید منتظر این بمونه
     * - می‌تونیم در future با queue async کنیم
     */
    public function recordView(int $productId, ?int $userId = null): void
    {
        // Fast atomic increment (بدون فراخوانی find)
        $incremented = $this->repository->incrementViewCount($productId);

        if ($incremented) {
            ProductViewed::dispatch($productId, $userId);
        }
    }

    // ──────────── Private Helpers ────────────

    /**
     * Pattern: Anti-Corruption Layer
     *
     * تبدیل Eloquent model به Public DTO.
     * این جلوگیری می‌کنه که ماژول‌های دیگه به Eloquent گره بخورن.
     */
    private function toPublicData(Product $product): ProductPublicData
    {
        return new ProductPublicData(
            id:           $product->id,
            title:        $product->title,
            enTitle:      $product->en_title,
            slug:         $product->slug,
            description:  $product->description,
            image:        $product->image,
            brandId:      $product->brand_id,
            status:       $product->status,
            isActive:     $product->is_active,
            isFeatured:   $product->is_featured,
            totalViews:   $product->total_views,
            soldCount:    $product->sold_count,
            lowestPrice:  $product->lowest_price,
            highestPrice: $product->highest_price,
            totalStock:   $product->total_stock,
            isAvailable:  $product->is_available,
        );
    }
}
```

#### `modules/Products/tests/Unit/Services/ProductServiceTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Modules\Products\Contracts\ProductRepositoryContract;
use Modules\Products\DataTransferObjects\CreateProductData;
use Modules\Products\DataTransferObjects\UpdateProductData;
use Modules\Products\Events\ProductArchived;
use Modules\Products\Events\ProductCreated;
use Modules\Products\Events\ProductDeleted;
use Modules\Products\Events\ProductPublished;
use Modules\Products\Events\ProductUpdated;
use Modules\Products\Events\ProductViewed;
use Modules\Products\Exceptions\ProductNotFoundException;
use Modules\Products\Models\Product;
use Modules\Products\Services\ProductService;
use Tests\TestCase;

uses(TestCase::class);

/**
 * Helper: Service factory برای tests
 *
 * Pattern: Test Helper / Builder Pattern
 * این کاهش boilerplate در tests ه.
 *
 * @return array{0: ProductService, 1: \Mockery\MockInterface}
 */
function makeProductService(): array
{
    $repository = Mockery::mock(ProductRepositoryContract::class);
    $service = new ProductService($repository);

    return [$service, $repository];
}

// ============================================================
// findById
// ============================================================

test('findById returns product when exists', function () {
    [$service, $repo] = makeProductService();

    $p = Product::factory()->forUnitTest()->make(['id' => 1]);
    $repo->expects('find')->with(1)->andReturn($p);

    expect($service->findById(1))->toBeInstanceOf(Product::class);
});

test('findById throws exception when not found', function () {
    [$service, $repo] = makeProductService();

    $repo->expects('find')->with(999)->andReturn(null);

    expect(fn () => $service->findById(999))
        ->toThrow(ProductNotFoundException::class);
});

// ============================================================
// findBySlug
// ============================================================

test('findBySlug returns product when exists', function () {
    [$service, $repo] = makeProductService();

    $p = Product::factory()->forUnitTest()->make(['slug' => 'test']);
    $repo->expects('findBySlug')->with('test')->andReturn($p);

    expect($service->findBySlug('test')->slug)->toBe('test');
});

test('findBySlug throws exception when not found', function () {
    [$service, $repo] = makeProductService();

    $repo->expects('findBySlug')->with('missing')->andReturn(null);

    expect(fn () => $service->findBySlug('missing'))
        ->toThrow(ProductNotFoundException::class);
});

// ============================================================
// create
// ============================================================

test('create persists product and dispatches event', function () {
    Event::fake();
    [$service, $repo] = makeProductService();

    $p = Product::factory()->forUnitTest()->make(['id' => 1]);
    $repo->expects('create')->andReturn($p);

    $data = new CreateProductData(
        title: 'تست',
        enTitle: 'Test',
        slug: 'test',
        userId: 1,
    );

    $service->create($data);

    Event::assertDispatched(ProductCreated::class);
});

// ============================================================
// update
// ============================================================

test('update returns product without query when no changes', function () {
    [$service, $repo] = makeProductService();

    $p = Product::factory()->forUnitTest()->make(['id' => 1]);
    $repo->shouldNotReceive('update');
    $repo->expects('find')->with(1)->andReturn($p);

    $service->update(1, new UpdateProductData());

    expect(true)->toBeTrue();  // no exception = pass
});

test('update modifies product and dispatches event', function () {
    Event::fake();
    [$service, $repo] = makeProductService();

    $updated = Product::factory()->forUnitTest()->make(['id' => 1, 'title' => 'جدید']);
    $repo->expects('update')->andReturn($updated);

    $service->update(1, new UpdateProductData(title: 'جدید'));

    Event::assertDispatched(ProductUpdated::class);
});

// ============================================================
// delete
// ============================================================

test('delete removes product and dispatches event', function () {
    Event::fake();
    [$service, $repo] = makeProductService();

    $p = Product::factory()->forUnitTest()->make(['id' => 1]);
    $repo->expects('find')->with(1)->andReturn($p);
    $repo->expects('delete')->with(1)->andReturn(true);

    $service->delete(1);

    Event::assertDispatched(
        ProductDeleted::class,
        fn ($e) => $e->productId === 1,
    );
});

test('delete throws when product not found', function () {
    [$service, $repo] = makeProductService();

    $repo->expects('find')->with(999)->andReturn(null);
    $repo->shouldNotReceive('delete');

    expect(fn () => $service->delete(999))
        ->toThrow(ProductNotFoundException::class);
});

// ============================================================
// State Transitions
// ============================================================

test('publish sets status and dispatches event', function () {
    Event::fake();
    [$service, $repo] = makeProductService();

    $p = Product::factory()->forUnitTest()->make([
        'id' => 1,
        'status' => Product::STATUS_PUBLISHED,
    ]);

    $repo->expects('update')
        ->with(1, Mockery::on(fn($data) =>
            $data['status'] === Product::STATUS_PUBLISHED
            && isset($data['published_at'])
        ))
        ->andReturn($p);

    $service->publish(1);

    Event::assertDispatched(ProductPublished::class);
});

test('archive sets status and dispatches event', function () {
    Event::fake();
    [$service, $repo] = makeProductService();

    $p = Product::factory()->forUnitTest()->make([
        'id' => 1,
        'status' => Product::STATUS_ARCHIVED,
    ]);

    $repo->expects('update')
        ->with(1, ['status' => Product::STATUS_ARCHIVED])
        ->andReturn($p);

    $service->archive(1);

    Event::assertDispatched(ProductArchived::class);
});

test('activate updates is_active and dispatches event', function () {
    Event::fake();
    [$service, $repo] = makeProductService();

    $p = Product::factory()->forUnitTest()->make(['id' => 1, 'is_active' => true]);
    $repo->expects('update')->with(1, ['is_active' => true])->andReturn($p);

    $service->activate(1);

    Event::assertDispatched(ProductUpdated::class);
});

test('feature updates is_featured and dispatches event', function () {
    Event::fake();
    [$service, $repo] = makeProductService();

    $p = Product::factory()->forUnitTest()->make(['id' => 1, 'is_featured' => true]);
    $repo->expects('update')->with(1, ['is_featured' => true])->andReturn($p);

    $service->feature(1);

    Event::assertDispatched(ProductUpdated::class);
});

// ============================================================
// recordView
// ============================================================

test('recordView increments view_count and dispatches event', function () {
    Event::fake();
    [$service, $repo] = makeProductService();

    $repo->expects('incrementViewCount')->with(1)->andReturn(true);

    $service->recordView(1);

    Event::assertDispatched(
        ProductViewed::class,
        fn ($e) => $e->productId === 1,
    );
});

test('recordView with userId passes through to event', function () {
    Event::fake();
    [$service, $repo] = makeProductService();

    $repo->expects('incrementViewCount')->with(1)->andReturn(true);

    $service->recordView(1, userId: 42);

    Event::assertDispatched(
        ProductViewed::class,
        fn ($e) => $e->productId === 1 && $e->userId === 42,
    );
});

test('recordView does not dispatch event when increment fails', function () {
    Event::fake();
    [$service, $repo] = makeProductService();

    $repo->expects('incrementViewCount')->with(999)->andReturn(false);

    $service->recordView(999);

    Event::assertNotDispatched(ProductViewed::class);
});

// ============================================================
// Public Contract
// ============================================================

test('exists returns true when product exists', function () {
    [$service, $repo] = makeProductService();

    $repo->expects('exists')->with(1)->andReturn(true);

    expect($service->exists(1))->toBeTrue();
});

test('isActive returns true for active product', function () {
    [$service, $repo] = makeProductService();

    $p = Product::factory()->forUnitTest()->make(['is_active' => true]);
    $repo->expects('find')->with(1)->andReturn($p);

    expect($service->isActive(1))->toBeTrue();
});

test('isActive returns false when not found', function () {
    [$service, $repo] = makeProductService();

    $repo->expects('find')->with(999)->andReturn(null);

    expect($service->isActive(999))->toBeFalse();
});

test('getById returns ProductPublicData when found', function () {
    [$service, $repo] = makeProductService();

    $p = Product::factory()->forUnitTest()->make([
        'id' => 1,
        'title' => 'تست',
        'status' => Product::STATUS_PUBLISHED,
        'is_active' => true,
        'total_stock' => 5,
    ]);
    $repo->expects('find')->with(1)->andReturn($p);

    $result = $service->getById(1);

    expect($result)->not->toBeNull()
        ->and($result->title)->toBe('تست')
        ->and($result->isActive)->toBeTrue();
});

test('getById returns null when not found', function () {
    [$service, $repo] = makeProductService();

    $repo->expects('find')->with(999)->andReturn(null);

    expect($service->getById(999))->toBeNull();
});

test('areAllActive returns true when empty', function () {
    [$service, $repo] = makeProductService();

    $repo->shouldNotReceive('countActiveByIds');

    expect($service->areAllActive([]))->toBeTrue();
});

test('areAllActive returns true when all active', function () {
    [$service, $repo] = makeProductService();

    $repo->expects('countActiveByIds')->with([1, 2, 3])->andReturn(3);

    expect($service->areAllActive([1, 2, 3]))->toBeTrue();
});

test('areAllActive returns false when some inactive', function () {
    [$service, $repo] = makeProductService();

    $repo->expects('countActiveByIds')->with([1, 2, 3])->andReturn(2);

    expect($service->areAllActive([1, 2, 3]))->toBeFalse();
});
```

### 📂 Http

#### `modules/Products/Http/Controllers/Admin/AdminProductController.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Products\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Modules\Products\Contracts\ProductServiceContract;
use Modules\Products\DataTransferObjects\CreateProductData;
use Modules\Products\DataTransferObjects\UpdateProductData;
use Modules\Products\Http\Requests\StoreProductRequest;
use Modules\Products\Http\Requests\UpdateProductRequest;
use Modules\Products\Http\Resources\ProductResource;
use Modules\Products\Models\Product;

final class AdminProductController extends Controller
{
    public function __construct(
        private readonly ProductServiceContract $productService,
    ) {}

    // ──────────── CRUD ────────────

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->query('per_page', 15);
        $products = $this->productService->paginate($perPage);

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = CreateProductData::fromArray($request->validated());
        $product = $this->productService->create($data);

        return ProductResource::make($product)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Product $product): ProductResource
    {
        return ProductResource::make($product);
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $data = UpdateProductData::fromArray($request->validated());
        $updated = $this->productService->update($product->id, $data);

        return ProductResource::make($updated);
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->productService->delete($product->id);

        return response()->json(null, 204);
    }

    // ──────────── State Transitions ────────────

    /**
     * Pattern: Explicit Action Endpoint
     *
     * چرا یه endpoint جداگانه به جای update؟
     * - explicit intent: واضح ه که publish اتفاق می‌افته
     * - می‌تونه permissions جداگانه داشته باشه
     * - logs/audit بهتر
     */
    public function publish(Product $product): ProductResource
    {
        $updated = $this->productService->publish($product->id);
        return ProductResource::make($updated);
    }

    public function archive(Product $product): ProductResource
    {
        $updated = $this->productService->archive($product->id);
        return ProductResource::make($updated);
    }

    public function activate(Product $product): ProductResource
    {
        $updated = $this->productService->activate($product->id);
        return ProductResource::make($updated);
    }

    public function deactivate(Product $product): ProductResource
    {
        $updated = $this->productService->deactivate($product->id);
        return ProductResource::make($updated);
    }

    public function feature(Product $product): ProductResource
    {
        $updated = $this->productService->feature($product->id);
        return ProductResource::make($updated);
    }

    public function unfeature(Product $product): ProductResource
    {
        $updated = $this->productService->unfeature($product->id);
        return ProductResource::make($updated);
    }

    // ──────────── Search & Filtering ────────────

    public function search(Request $request): AnonymousResourceCollection
    {
        $term = (string) $request->query('q', '');
        $perPage = (int) $request->query('per_page', 15);

        $products = $this->productService->search($term, $perPage);

        return ProductResource::collection($products);
    }

    public function byBrand(Request $request, int $brandId): AnonymousResourceCollection
    {
        $perPage = (int) $request->query('per_page', 15);
        $products = $this->productService->getByBrand($brandId, $perPage);

        return ProductResource::collection($products);
    }
}
```

#### `modules/Products/Http/Requests/StoreProductRequest.php`

```php
<?php
declare(strict_types=1);
namespace Modules\Products\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Products\Models\Product;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            // ──────────── Identity ────────────
            'title' => ['required', 'string', 'max:200'],
            'en_title' => ['required', 'string', 'max:200'],
            'slug' => [
                'required',
                'string',
                'max:220',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('products', 'slug'),
            ],

            // ──────────── Content ────────────
            'description' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:500'],

            // ──────────── Relationships ────────────
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],

            // ──────────── Status ────────────
            'status' => [
                'sometimes',
                'string',
                Rule::in([
                    Product::STATUS_DRAFT,
                    Product::STATUS_PUBLISHED,
                    Product::STATUS_ARCHIVED,
                ]),
            ],
            'is_active' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],

            // ──────────── Analytics ────────────
            'fake_view_count' => ['sometimes', 'integer', 'min:0'],

            // ──────────── SEO ────────────
            'meta_title' => ['nullable', 'string', 'max:200'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex'  => 'Slug فقط می‌تواند شامل حروف کوچک، اعداد و خط تیره باشد.',
            'slug.unique' => 'این slug قبلاً استفاده شده است.',
            'brand_id.exists' => 'برند انتخاب شده وجود ندارد.',
            'user_id.exists'  => 'کاربر انتخاب شده وجود ندارد.',
            'status.in' => 'وضعیت انتخاب شده معتبر نیست.',
        ];
    }

}
```

#### `modules/Products/Http/Requests/UpdateProductRequest.php`

```php
<?php
declare(strict_types=1);
namespace Modules\Products\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Products\Models\Product;

final class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Product $product */
        $product = $this->route('product');

        return [
            // ──────────── Identity ────────────
            'title'    => ['sometimes', 'required', 'string', 'max:200'],
            'en_title' => ['sometimes', 'required', 'string', 'max:200'],
            'slug'     => [
                'sometimes',
                'required',
                'string',
                'max:220',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('products', 'slug')->ignore($product->id),
            ],

            // ──────────── Content ────────────
            'description' => ['nullable', 'string', 'max:500'],
            'content'     => ['nullable', 'string'],
            'image'       => ['nullable', 'string', 'max:500'],

            // ──────────── Relationships ────────────
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],

            // ──────────── Status ────────────
            'status' => [
                'sometimes',
                'string',
                Rule::in([
                    Product::STATUS_DRAFT,
                    Product::STATUS_PUBLISHED,
                    Product::STATUS_ARCHIVED,
                ]),
            ],
            'is_active'   => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],

            // ──────────── Analytics ────────────
            'fake_view_count' => ['sometimes', 'integer', 'min:0'],

            // ──────────── SEO ────────────
            'meta_title'       => ['nullable', 'string', 'max:200'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ];
    }


    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex'      => 'Slug فقط می‌تواند شامل حروف کوچک، اعداد و خط تیره باشد.',
            'slug.unique'     => 'این slug قبلاً استفاده شده است.',
            'brand_id.exists' => 'برند انتخاب شده وجود ندارد.',
            'status.in'       => 'وضعیت انتخاب شده معتبر نیست.',
        ];
    }
}
```

#### `modules/Products/Http/Resources/ProductResource.php`

```php
<?php
declare(strict_types=1);
namespace Modules\Products\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Products\Models\Product;

/**
 * @mixin Product
 */
final class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,

            // ──────────── Identity ────────────
            'title'    => $this->title,
            'en_title' => $this->en_title,
            'slug'     => $this->slug,

            // ──────────── Content ────────────
            'description' => $this->description,
            'content'     => $this->content,
            'image'       => $this->image,

            // ──────────── Relationships ────────────
            'brand_id' => $this->brand_id,
            'user_id'  => (int) $this->user_id,

            // ──────────── Status ────────────
            'status'      => $this->status,
            'is_active'   => (bool) $this->is_active,
            'is_featured' => (bool) $this->is_featured,

            // ──────────── Analytics ────────────
            'view_count'      => (int) $this->view_count,
            'fake_view_count' => (int) $this->fake_view_count,
            'total_views'     => (int) $this->total_views,    // computed
            'sold_count'      => (int) $this->sold_count,

            // ──────────── Pricing ────────────
            'lowest_price'  => (int) $this->lowest_price,
            'highest_price' => (int) $this->highest_price,

            // ──────────── Inventory ────────────
            'total_stock'     => (int) $this->total_stock,
            'variants_count'  => (int) $this->variants_count,
            'is_available'    => (bool) $this->is_available, // computed

            // ──────────── SEO (grouped) ────────────
            'seo' => [
                'title'       => $this->meta_title,
                'description' => $this->meta_description,
            ],

            // ──────────── Timestamps ────────────
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at'   => $this->created_at?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),
        ];
    }

}
```

### 📂 Providers

#### `modules/Products/Providers/ProductsServiceProvider.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Products\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Products\Contracts\ProductRepositoryContract;
use Modules\Products\Contracts\ProductServiceContract;
use Modules\Products\Repositories\EloquentProductRepository;
use Modules\Products\Services\ProductService;

final class ProductsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ProductRepositoryContract::class,
            EloquentProductRepository::class,
        );

        $this->app->bind(
            ProductServiceContract::class,
            ProductService::class,
        );
    }


}
```

### 📂 database/factories

#### `modules/Products/database/factories/ProductFactory.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Products\database\factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Products\Models\Product;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unique = Str::random(8);

        return [
            'title' => "product-title-{$unique}",
            'en_title' => "product-en-{$unique}",
            'slug' => "product-slug-{$unique}",
            'description' => null,
            'content' => null,
            'image' => null,
            'brand_id' => null,
            'user_id' => User::factory(),
            'status' => Product::STATUS_PUBLISHED,
            'is_active' => true,
            'is_featured' => false,
            'view_count' => 0,
            'fake_view_count' => 0,
            'sold_count' => 0,
            'lowest_price' => 0,
            'highest_price' => 0,
            'total_stock' => 0,
            'variants_count' => 0,
            'meta_title' => null,
            'meta_description' => null,
            'published_at' => null,
        ];
    }

    // ──────────── Status States ────────────

    public function draft(): static
    {
        return $this->state(['status' => Product::STATUS_DRAFT]);
    }

    public function published(): static
    {
        return $this->state([
            'status' => Product::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
    }

    public function archived(): static
    {
        return $this->state(['status' => Product::STATUS_ARCHIVED]);
    }

    // ──────────── Activity States ────────────

    public function active(): static
    {
        return $this->state(['is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }

    public function forUnitTest(): static
    {
        return $this->state([
            'user_id' => 1,
            'brand_id' => null,
        ]);
    }
}
```

---

## 🧩 Module: Shared


### 📂 Contracts

#### `modules/Shared/Contracts/Specification.php`

```php
<?php
declare(strict_types=1);

namespace Modules\Shared\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface Specification
{
    public function apply(Builder $query): Builder;
}
```

#### `modules/Shared/Contracts/Validator.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Shared\Contracts;
interface Validator
{
    public function isSatisfiedBy(mixed $candidate): bool;

    public function getErrorMessage(): string;

}
```

### 📂 Repositories

#### `modules/Shared/Repositories/BaseRepository.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Shared\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository
{
    abstract protected function model(): Model;

    public function query(): Builder
    {
        return $this->model()->newQuery();
    }

    public function find(int $id): ?Model
    {
        return $this->query()->newQuery()->find($id);
    }

    public function findOrFail(int $id): Model
    {
        return $this->query()->newQuery()->findOrFail($id);
    }

    public function exists(int $id): bool
    {
        return $this->query()->newQuery()->where('id', $id)->exists();
    }

    public function existsAll(array $ids): bool
    {
        if (empty($ids)) {
            return true;
        }

        $uniqueIds = array_unique($ids);
        $count = $this->query()->whereIn('id', $uniqueIds)->count();

        return $count === count($uniqueIds);
    }

    public function all(): Collection
    {
        return $this->query()->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage);
    }

    /**
     * @param array<string,mixed> $data
     */

    public function create(array $data): Model
    {
        return $this->query()->create($data);
    }

    /**
     * @param array<string,mixed> $data
     */
    public function update(int $id, array $data): Model
    {
        $record = $this->findOrFail($id);
        $record->update($data);

        return $record->fresh();
    }

    public function delete(int $id): bool
    {
        return (bool)$this->findOrFail($id)->delete();
    }

    public function forceDelete(int $id): bool
    {
        return (bool)$this->findOrFail($id)->forceDelete();
    }


    public function count(): int
    {
        return $this->query()->count();
    }


}
```

### 📂 Providers

#### `modules/Shared/Providers/SharedProvider.php`

```php
<?php

namespace Modules\Shared\Providers;

use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Carbon\Laravel\ServiceProvider;
use Modules\Shared\Servies\File\FileService;
use Modules\Shared\Servies\File\LocalFileService;

class SharedProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->singleton(FileService::class, function ($app) {
            return new LocalFileService(
                $app->make(FilesystemFactory::class),
                config('modules.shared.disk', 'public')
            );
        });

    }

}
```

---

## 🧩 Module: Warranties


### 📂 Models

#### `modules/Warranties/Models/Warranty.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Warranties\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\Warranties\database\factories\WarrantyFactory;

/**
 * @property int $id
 * @property string $name
 * @property string $english_name
 * @property string $slug
 * @property int $duration_months
 * @property string $provider
 * @property string|null $description
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
final class Warranty extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'warranties';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'english_name',
        'slug',
        'duration_months',
        'provider',
        'description',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active'       => 'boolean',
            'duration_months' => 'integer',
            'sort_order'      => 'integer',
        ];
    }

    protected static function newFactory(): WarrantyFactory
    {
        return WarrantyFactory::new();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ──────────── Query Scopes ────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('duration_months');
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('english_name', 'like', "%{$term}%")
                ->orWhere('provider', 'like', "%{$term}%");
        });
    }

    public function scopeByProvider(Builder $query, string $provider): Builder
    {
        return $query->where('provider', $provider);
    }
}
```

### 📂 Contracts

#### `modules/Warranties/Contracts/WarrantyRepositoryContract.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Warranties\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Warranties\Models\Warranty;

interface WarrantyRepositoryContract
{
    public function find(int $id): ?Warranty;
    public function findOrFail(int $id): Warranty;
    public function exists(int $id): bool;

    /**
     * @param array<int> $ids
     */
    public function existsAll(array $ids): bool;

    /**
     * @return Collection<int, Warranty>
     */
    public function all(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Warranty;

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): Warranty;

    public function delete(int $id): bool;

    // ──────────── Domain-specific ────────────

    public function findBySlug(string $slug): ?Warranty;

    /**
     * @return Collection<int, Warranty>
     */
    public function getActive(): Collection;

    public function search(string $term, int $perPage = 15): LengthAwarePaginator;

    public function countActiveByIds(array $ids): int;

    /**
     * @param array<int> $ids
     * @return Collection<int, Warranty>
     */
    public function findByIds(array $ids): Collection;
}
```

#### `modules/Warranties/Contracts/WarrantyServiceContract.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Warranties\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Warranties\DataTransferObjects\CreateWarrantyData;
use Modules\Warranties\DataTransferObjects\UpdateWarrantyData;
use Modules\Warranties\DataTransferObjects\WarrantyPublicData;
use Modules\Warranties\Models\Warranty;

interface WarrantyServiceContract
{
    // ──────────── Public API ────────────

    public function exists(int $warrantyId): bool;

    /**
     * @param array<int> $warrantyIds
     */
    public function existsAll(array $warrantyIds): bool;

    public function getById(int $warrantyId): ?WarrantyPublicData;

    /**
     * @param array<int> $warrantyIds
     * @return array<int, WarrantyPublicData>
     */
    public function getByIds(array $warrantyIds): array;

    public function isActive(int $warrantyId): bool;

    /**
     * @param array<int> $warrantyIds
     */
    public function areAllActive(array $warrantyIds): bool;

    // ──────────── Internal Methods ────────────

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findById(int $warrantyId): Warranty;

    public function findBySlug(string $slug): Warranty;

    public function create(CreateWarrantyData $data): Warranty;

    public function update(int $warrantyId, UpdateWarrantyData $data): Warranty;

    public function delete(int $warrantyId): bool;

    public function activate(int $warrantyId): Warranty;

    public function deactivate(int $warrantyId): Warranty;

    public function search(string $term, int $perPage = 15): LengthAwarePaginator;
}
```

### 📂 DataTransferObjects

#### `modules/Warranties/DataTransferObjects/CreateWarrantyData.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Warranties\DataTransferObjects;

final readonly class CreateWarrantyData
{
    public function __construct(
        public string $name,
        public string $englishName,
        public string $slug,
        public int $durationMonths,
        public string $provider,
        public ?string $description = null,
        public bool $isActive = true,
        public int $sortOrder = 0,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name:           (string) $data['name'],
            englishName:    (string) $data['english_name'],
            slug:           (string) $data['slug'],
            durationMonths: (int) $data['duration_months'],
            provider:       (string) $data['provider'],
            description:    isset($data['description']) ? (string) $data['description'] : null,
            isActive:       (bool) ($data['is_active'] ?? true),
            sortOrder:      (int) ($data['sort_order'] ?? 0),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name'            => $this->name,
            'english_name'    => $this->englishName,
            'slug'            => $this->slug,
            'duration_months' => $this->durationMonths,
            'provider'        => $this->provider,
            'description'     => $this->description,
            'is_active'       => $this->isActive,
            'sort_order'      => $this->sortOrder,
        ];
    }
}
```

#### `modules/Warranties/DataTransferObjects/UpdateWarrantyData.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Warranties\DataTransferObjects;

final readonly class UpdateWarrantyData
{
    public function __construct(
        public ?string $name = null,
        public ?string $englishName = null,
        public ?string $slug = null,
        public ?int    $durationMonths = null,
        public ?string $provider = null,
        public ?string $description = null,
        public ?bool   $isActive = null,
        public ?int    $sortOrder = null,
    )
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: isset($data['name']) ? (string)$data['name'] : null,
            englishName: isset($data['english_name']) ? (string)$data['english_name'] : null,
            slug: isset($data['slug']) ? (string)$data['slug'] : null,
            durationMonths: isset($data['duration_months']) ? (int)$data['duration_months'] : null,
            provider: isset($data['provider']) ? (string)$data['provider'] : null,
            description: array_key_exists('description', $data) ? $data['description'] : null,
            isActive: isset($data['is_active']) ? (bool)$data['is_active'] : null,
            sortOrder: isset($data['sort_order']) ? (int)$data['sort_order'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->name !== null) $data['name'] = $this->name;
        if ($this->englishName !== null) $data['english_name'] = $this->englishName;
        if ($this->slug !== null) $data['slug'] = $this->slug;
        if ($this->durationMonths !== null) $data['duration_months'] = $this->durationMonths;
        if ($this->provider !== null) $data['provider'] = $this->provider;
        if ($this->description !== null) $data['description'] = $this->description;
        if ($this->isActive !== null) $data['is_active'] = $this->isActive;
        if ($this->sortOrder !== null) $data['sort_order'] = $this->sortOrder;

        return $data;
    }

    public function hasChanges(): bool
    {
        return !empty($this->toArray());
    }
}
```

#### `modules/Warranties/DataTransferObjects/WarrantyPublicData.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Warranties\DataTransferObjects;

final readonly class WarrantyPublicData
{
    public function __construct(
        public int $id,
        public string $name,
        public string $englishName,
        public string $slug,
        public int $durationMonths,
        public string $provider,
        public bool $isActive,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id:             (int) $data['id'],
            name:           (string) $data['name'],
            englishName:    (string) $data['english_name'],
            slug:           (string) $data['slug'],
            durationMonths: (int) $data['duration_months'],
            provider:       (string) $data['provider'],
            isActive:       (bool) $data['is_active'],
        );
    }
}
```

### 📂 Events

#### `modules/Warranties/Events/WarrantyActivated.php`

```php
<?php

namespace Modules\Warranties\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Warranties\Models\Warranty;

class WarrantyActivated
{
    use Dispatchable;

    public function __construct(
        public readonly Warranty $warranty,
    ) {}

}
```

#### `modules/Warranties/Events/WarrantyCreated.php`

```php
<?php

namespace Modules\Warranties\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Warranties\Models\Warranty;

class WarrantyCreated
{

    use Dispatchable;

    public function __construct(
        public readonly Warranty $warranty,
    ) {}
}
```

#### `modules/Warranties/Events/WarrantyDeactivated.php`

```php
<?php

namespace Modules\Warranties\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Warranties\Models\Warranty;

class WarrantyDeactivated
{
    use Dispatchable;

    public function __construct(
        public readonly Warranty $warranty,
    ) {}

}
```

#### `modules/Warranties/Events/WarrantyDeleted.php`

```php
<?php

namespace Modules\Warranties\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Warranties\Models\Warranty;

class WarrantyDeleted
{
    use Dispatchable;

    public function __construct(
        public readonly int $warrantyId,
    ) {}

}
```

#### `modules/Warranties/Events/WarrantyUpdated.php`

```php
<?php

namespace Modules\Warranties\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Warranties\Models\Warranty;

class WarrantyUpdated
{
    use Dispatchable;

    public function __construct(
        public readonly Warranty $warranty,
    ) {}

}
```

### 📂 Exceptions

#### `modules/Warranties/Exceptions/WarrantyNotFoundException.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Warranties\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class WarrantyNotFoundException extends NotFoundHttpException
{
}
```

### 📂 Repositories

#### `modules/Warranties/Repositories/EloquentWarrantyRepository.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Warranties\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Repositories\BaseRepository;
use Modules\Warranties\Contracts\WarrantyRepositoryContract;
use Modules\Warranties\Models\Warranty;

final class EloquentWarrantyRepository extends BaseRepository implements WarrantyRepositoryContract
{
    protected function model(): Model
    {
        return new Warranty();
    }

    // ──────────── Type-safe overrides ────────────

    public function find(int $id): ?Warranty
    {
        /** @var Warranty|null */
        return parent::find($id);
    }

    public function findOrFail(int $id): Warranty
    {
        /** @var Warranty */
        return parent::findOrFail($id);
    }

    public function create(array $data): Warranty
    {
        /** @var Warranty */
        return parent::create($data);
    }

    public function update(int $id, array $data): Warranty
    {
        /** @var Warranty */
        return parent::update($id, $data);
    }

    public function paginate(int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->query()
            ->ordered()
            ->paginate($perPage);
    }

    // ──────────── Domain-specific ────────────

    public function findBySlug(string $slug): ?Warranty
    {
        return $this->query()
            ->where('slug', $slug)
            ->first();
    }

    public function getActive(): Collection
    {
        return $this->query()
            ->active()
            ->ordered()
            ->get();
    }

    public function search(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->search($term)
            ->ordered()
            ->paginate($perPage);
    }

    public function countActiveByIds(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        return $this->query()
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->count();
    }

    public function findByIds(array $ids): Collection
    {
        if (empty($ids)) {
            return new Collection();
        }

        return $this->query()
            ->whereIn('id', $ids)
            ->ordered()
            ->get();
    }
}
```

### 📂 Services

#### `modules/Warranties/Services/WarrantyService.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Warranties\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Warranties\Contracts\WarrantyRepositoryContract;
use Modules\Warranties\Contracts\WarrantyServiceContract;
use Modules\Warranties\DataTransferObjects\CreateWarrantyData;
use Modules\Warranties\DataTransferObjects\UpdateWarrantyData;
use Modules\Warranties\DataTransferObjects\WarrantyPublicData;
use Modules\Warranties\Events\WarrantyActivated;
use Modules\Warranties\Events\WarrantyCreated;
use Modules\Warranties\Events\WarrantyDeactivated;
use Modules\Warranties\Events\WarrantyDeleted;
use Modules\Warranties\Events\WarrantyUpdated;
use Modules\Warranties\Exceptions\WarrantyNotFoundException;
use Modules\Warranties\Models\Warranty;

final readonly class WarrantyService implements WarrantyServiceContract
{
    public function __construct(
        private readonly WarrantyRepositoryContract $repository,
    ) {}

    // ──────────── Internal Methods ────────────

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function findById(int $warrantyId): Warranty
    {
        $warranty = $this->repository->find($warrantyId);

        if ($warranty === null) {
            throw new WarrantyNotFoundException("Warranty with id {$warrantyId} not found");
        }

        return $warranty;
    }

    public function findBySlug(string $slug): Warranty
    {
        $warranty = $this->repository->findBySlug($slug);

        if ($warranty === null) {
            throw new WarrantyNotFoundException("Warranty with slug '{$slug}' not found");
        }

        return $warranty;
    }

    public function create(CreateWarrantyData $data): Warranty
    {
        return DB::transaction(function () use ($data) {
            $warranty = $this->repository->create($data->toArray());

            WarrantyCreated::dispatch($warranty);

            return $warranty;
        });
    }

    public function update(int $warrantyId, UpdateWarrantyData $data): Warranty
    {
        if (! $data->hasChanges()) {
            return $this->findById($warrantyId);
        }

        return DB::transaction(function () use ($warrantyId, $data) {
            $warranty = $this->repository->update($warrantyId, $data->toArray());

            WarrantyUpdated::dispatch($warranty);

            return $warranty;
        });
    }

    public function delete(int $warrantyId): bool
    {
        return DB::transaction(function () use ($warrantyId) {
            $this->findById($warrantyId);

            $deleted = $this->repository->delete($warrantyId);

            if ($deleted) {
                WarrantyDeleted::dispatch($warrantyId);
            }

            return $deleted;
        });
    }

    public function activate(int $warrantyId): Warranty
    {
        return DB::transaction(function () use ($warrantyId) {
            $warranty = $this->repository->update($warrantyId, ['is_active' => true]);

            WarrantyActivated::dispatch($warranty);

            return $warranty;
        });
    }

    public function deactivate(int $warrantyId): Warranty
    {
        return DB::transaction(function () use ($warrantyId) {
            $warranty = $this->repository->update($warrantyId, ['is_active' => false]);

            WarrantyDeactivated::dispatch($warranty);

            return $warranty;
        });
    }

    public function search(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->search($term, $perPage);
    }

    // ──────────── Public Contract Methods ────────────

    public function exists(int $warrantyId): bool
    {
        return $this->repository->exists($warrantyId);
    }

    public function existsAll(array $warrantyIds): bool
    {
        return $this->repository->existsAll($warrantyIds);
    }

    public function getById(int $warrantyId): ?WarrantyPublicData
    {
        $warranty = $this->repository->find($warrantyId);

        if ($warranty === null) {
            return null;
        }

        return $this->toPublicData($warranty);
    }

    public function getByIds(array $warrantyIds): array
    {
        if (empty($warrantyIds)) {
            return [];
        }

        $warranties = $this->repository->findByIds(array_unique($warrantyIds));

        $result = [];
        foreach ($warranties as $warranty) {
            $result[$warranty->id] = $this->toPublicData($warranty);
        }

        return $result;
    }

    public function isActive(int $warrantyId): bool
    {
        $warranty = $this->repository->find($warrantyId);

        return $warranty !== null && $warranty->is_active;
    }

    public function areAllActive(array $warrantyIds): bool
    {
        if (empty($warrantyIds)) {
            return true;
        }

        $uniqueIds = array_unique($warrantyIds);
        $count = $this->repository->countActiveByIds($uniqueIds);

        return $count === count($uniqueIds);
    }

    // ──────────── Private Helpers ────────────

    private function toPublicData(Warranty $warranty): WarrantyPublicData
    {
        return new WarrantyPublicData(
            id:             $warranty->id,
            name:           $warranty->name,
            englishName:    $warranty->english_name,
            slug:           $warranty->slug,
            durationMonths: $warranty->duration_months,
            provider:       $warranty->provider,
            isActive:       $warranty->is_active,
        );
    }
}
```

#### `modules/Warranties/tests/Unit/Services/WarrantyServiceTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Modules\Warranties\Contracts\WarrantyRepositoryContract;
use Modules\Warranties\DataTransferObjects\CreateWarrantyData;
use Modules\Warranties\DataTransferObjects\UpdateWarrantyData;
use Modules\Warranties\Events\WarrantyActivated;
use Modules\Warranties\Events\WarrantyCreated;
use Modules\Warranties\Events\WarrantyDeactivated;
use Modules\Warranties\Events\WarrantyDeleted;
use Modules\Warranties\Events\WarrantyUpdated;
use Modules\Warranties\Exceptions\WarrantyNotFoundException;
use Modules\Warranties\Models\Warranty;
use Modules\Warranties\Services\WarrantyService;
use Tests\TestCase;

uses(TestCase::class);

function makeWarrantyService(): array
{
    $repository = Mockery::mock(WarrantyRepositoryContract::class);
    $service = new WarrantyService($repository);

    return [$service, $repository];
}

// ──────────── findById ────────────

test('findById returns warranty when exists', function () {
    [$service, $repo] = makeWarrantyService();

    $w = Warranty::factory()->make(['id' => 1]);
    $repo->expects('find')->with(1)->andReturn($w);

    expect($service->findById(1))->toBeInstanceOf(Warranty::class);
});

test('findById throws exception when not found', function () {
    [$service, $repo] = makeWarrantyService();

    $repo->expects('find')->with(999)->andReturn(null);

    expect(fn () => $service->findById(999))
        ->toThrow(WarrantyNotFoundException::class);
});

// ──────────── create ────────────

test('create persists warranty and dispatches event', function () {
    Event::fake();
    [$service, $repo] = makeWarrantyService();

    $w = Warranty::factory()->make(['id' => 1]);
    $repo->expects('create')->andReturn($w);

    $data = new CreateWarrantyData(
        name: 'گارانتی ۲۴ ماهه',
        englishName: 'Test 24',
        slug: 'test-24',
        durationMonths: 24,
        provider: 'تست',
    );

    $service->create($data);

    Event::assertDispatched(WarrantyCreated::class);
});

// ──────────── update ────────────

test('update returns warranty without query when no changes', function () {
    [$service, $repo] = makeWarrantyService();

    $w = Warranty::factory()->make(['id' => 1]);
    $repo->shouldNotReceive('update');
    $repo->expects('find')->with(1)->andReturn($w);

    $result = $service->update(1, new UpdateWarrantyData());

    expect($result->id)->toBe(1);
});

test('update modifies warranty and dispatches event', function () {
    Event::fake();
    [$service, $repo] = makeWarrantyService();

    $updated = Warranty::factory()->make(['id' => 1, 'name' => 'جدید']);
    $repo->expects('update')->andReturn($updated);

    $service->update(1, new UpdateWarrantyData(name: 'جدید'));

    Event::assertDispatched(WarrantyUpdated::class);
});

// ──────────── delete ────────────

test('delete removes warranty and dispatches event', function () {
    Event::fake();
    [$service, $repo] = makeWarrantyService();

    $w = Warranty::factory()->make(['id' => 1]);
    $repo->expects('find')->with(1)->andReturn($w);
    $repo->expects('delete')->with(1)->andReturn(true);

    $service->delete(1);

    Event::assertDispatched(
        WarrantyDeleted::class,
        fn ($e) => $e->warrantyId === 1,
    );
});

test('delete throws when not found', function () {
    [$service, $repo] = makeWarrantyService();

    $repo->expects('find')->with(999)->andReturn(null);
    $repo->shouldNotReceive('delete');

    expect(fn () => $service->delete(999))
        ->toThrow(WarrantyNotFoundException::class);
});

// ──────────── activate / deactivate ────────────

test('activate updates is_active and dispatches event', function () {
    Event::fake();
    [$service, $repo] = makeWarrantyService();

    $w = Warranty::factory()->make(['id' => 1, 'is_active' => true]);
    $repo->expects('update')->with(1, ['is_active' => true])->andReturn($w);

    $service->activate(1);

    Event::assertDispatched(WarrantyActivated::class);
});

test('deactivate updates is_active and dispatches event', function () {
    Event::fake();
    [$service, $repo] = makeWarrantyService();

    $w = Warranty::factory()->make(['id' => 1, 'is_active' => false]);
    $repo->expects('update')->with(1, ['is_active' => false])->andReturn($w);

    $service->deactivate(1);

    Event::assertDispatched(WarrantyDeactivated::class);
});

// ──────────── Public Contract ────────────

test('exists returns true when warranty exists', function () {
    [$service, $repo] = makeWarrantyService();

    $repo->expects('exists')->with(1)->andReturn(true);

    expect($service->exists(1))->toBeTrue();
});

test('isActive returns true for active warranty', function () {
    [$service, $repo] = makeWarrantyService();

    $w = Warranty::factory()->make(['is_active' => true]);
    $repo->expects('find')->with(1)->andReturn($w);

    expect($service->isActive(1))->toBeTrue();
});

test('isActive returns false when not found', function () {
    [$service, $repo] = makeWarrantyService();

    $repo->expects('find')->with(999)->andReturn(null);

    expect($service->isActive(999))->toBeFalse();
});

test('getById returns WarrantyPublicData when found', function () {
    [$service, $repo] = makeWarrantyService();

    $w = Warranty::factory()->make([
        'id' => 1,
        'name' => 'تست',
        'duration_months' => 24,
        'provider' => 'تست',
        'is_active' => true,
    ]);
    $repo->expects('find')->with(1)->andReturn($w);

    $result = $service->getById(1);

    expect($result)->not->toBeNull()
        ->and($result->durationMonths)->toBe(24)
        ->and($result->isActive)->toBeTrue();
});

test('getById returns null when not found', function () {
    [$service, $repo] = makeWarrantyService();

    $repo->expects('find')->with(999)->andReturn(null);

    expect($service->getById(999))->toBeNull();
});

test('areAllActive returns true when empty', function () {
    [$service, $repo] = makeWarrantyService();

    $repo->shouldNotReceive('countActiveByIds');

    expect($service->areAllActive([]))->toBeTrue();
});

test('areAllActive returns true when all active', function () {
    [$service, $repo] = makeWarrantyService();

    $repo->expects('countActiveByIds')->with([1, 2, 3])->andReturn(3);

    expect($service->areAllActive([1, 2, 3]))->toBeTrue();
});

test('areAllActive returns false when some inactive', function () {
    [$service, $repo] = makeWarrantyService();

    $repo->expects('countActiveByIds')->with([1, 2, 3])->andReturn(2);

    expect($service->areAllActive([1, 2, 3]))->toBeFalse();
});
```

### 📂 Http

#### `modules/Warranties/Http/Controllers/Admin/AdminWarrantyController.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Warranties\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Modules\Warranties\Contracts\WarrantyServiceContract;
use Modules\Warranties\DataTransferObjects\CreateWarrantyData;
use Modules\Warranties\DataTransferObjects\UpdateWarrantyData;
use Modules\Warranties\Http\Requests\StoreWarrantyRequest;
use Modules\Warranties\Http\Requests\UpdateWarrantyRequest;
use Modules\Warranties\Http\Resources\WarrantyResource;
use Modules\Warranties\Models\Warranty;

final class AdminWarrantyController extends Controller
{
    public function __construct(
        private readonly WarrantyServiceContract $warrantyService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->query('per_page', 15);
        $warranties = $this->warrantyService->paginate($perPage);

        return WarrantyResource::collection($warranties);
    }

    public function store(StoreWarrantyRequest $request): JsonResponse
    {
        $data = CreateWarrantyData::fromArray($request->validated());

        $warranty = $this->warrantyService->create($data);

        return WarrantyResource::make($warranty)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Warranty $warranty): WarrantyResource
    {
        return WarrantyResource::make($warranty);
    }

    public function update(UpdateWarrantyRequest $request, Warranty $warranty): WarrantyResource
    {
        $data = UpdateWarrantyData::fromArray($request->validated());

        $updated = $this->warrantyService->update($warranty->id, $data);

        return WarrantyResource::make($updated);
    }

    public function destroy(Warranty $warranty): JsonResponse
    {
        $this->warrantyService->delete($warranty->id);

        return response()->json(null, 204);
    }

    public function activate(Warranty $warranty): WarrantyResource
    {
        $updated = $this->warrantyService->activate($warranty->id);

        return WarrantyResource::make($updated);
    }

    public function deactivate(Warranty $warranty): WarrantyResource
    {
        $updated = $this->warrantyService->deactivate($warranty->id);

        return WarrantyResource::make($updated);
    }

    public function search(Request $request): AnonymousResourceCollection
    {
        $term = (string) $request->query('q', '');
        $perPage = (int) $request->query('per_page', 15);

        $warranties = $this->warrantyService->search($term, $perPage);

        return WarrantyResource::collection($warranties);
    }
}
```

#### `modules/Warranties/Http/Requests/StoreWarrantyRequest.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Warranties\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreWarrantyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:150'],
            'english_name'    => ['required', 'string', 'max:150'],
            'slug'            => [
                'required',
                'string',
                'max:170',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('warranties', 'slug'),
            ],
            'duration_months' => ['required', 'integer', 'min:1', 'max:120'],
            'provider'        => ['required', 'string', 'max:100'],
            'description'     => ['nullable', 'string', 'max:1000'],
            'is_active'       => ['sometimes', 'boolean'],
            'sort_order'      => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex'              => 'Slug فقط می‌تواند شامل حروف کوچک، اعداد و خط تیره باشد.',
            'slug.unique'             => 'این slug قبلاً استفاده شده است.',
            'duration_months.min'     => 'مدت گارانتی باید حداقل ۱ ماه باشد.',
            'duration_months.max'     => 'مدت گارانتی نمی‌تواند بیش از ۱۲۰ ماه باشد.',
        ];
    }
}
```

#### `modules/Warranties/Http/Requests/UpdateWarrantyRequest.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Warranties\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Warranties\Models\Warranty;

final class UpdateWarrantyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Warranty $warranty */
        $warranty = $this->route('warranty');

        return [
            'name'            => ['sometimes', 'required', 'string', 'max:150'],
            'english_name'    => ['sometimes', 'required', 'string', 'max:150'],
            'slug'            => [
                'sometimes',
                'required',
                'string',
                'max:170',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('warranties', 'slug')->ignore($warranty->id),
            ],
            'duration_months' => ['sometimes', 'required', 'integer', 'min:1', 'max:120'],
            'provider'        => ['sometimes', 'required', 'string', 'max:100'],
            'description'     => ['nullable', 'string', 'max:1000'],
            'is_active'       => ['sometimes', 'boolean'],
            'sort_order'      => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex'              => 'Slug فقط می‌تواند شامل حروف کوچک، اعداد و خط تیره باشد.',
            'slug.unique'             => 'این slug قبلاً استفاده شده است.',
            'duration_months.min'     => 'مدت گارانتی باید حداقل ۱ ماه باشد.',
            'duration_months.max'     => 'مدت گارانتی نمی‌تواند بیش از ۱۲۰ ماه باشد.',
        ];
    }
}
```

#### `modules/Warranties/Http/Resources/WarrantyResource.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Warranties\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Warranties\Models\Warranty;

/**
 * @mixin Warranty
 */
final class WarrantyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'english_name'    => $this->english_name,
            'slug'            => $this->slug,
            'duration_months' => (int) $this->duration_months,
            'provider'        => $this->provider,
            'description'     => $this->description,
            'is_active'       => (bool) $this->is_active,
            'sort_order'      => (int) $this->sort_order,
            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),
        ];
    }
}
```

### 📂 Providers

#### `modules/Warranties/Providers/WarrantiesServiceProvider.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Warranties\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Warranties\Contracts\WarrantyRepositoryContract;
use Modules\Warranties\Contracts\WarrantyServiceContract;
use Modules\Warranties\Repositories\EloquentWarrantyRepository;
use Modules\Warranties\Services\WarrantyService;

final class WarrantiesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            WarrantyRepositoryContract::class,
            EloquentWarrantyRepository::class,
        );

        $this->app->bind(
            WarrantyServiceContract::class,
            WarrantyService::class,
        );
    }

    public function boot(): void
    {
    }
}
```

### 📂 database/factories

#### `modules/Warranties/database/factories/WarrantyFactory.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Warranties\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Warranties\Models\Warranty;

class WarrantyFactory extends Factory
{
    protected $model = Warranty::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unique = Str::random(8);

        return [
            'name'            => "warranty-name-{$unique}",
            'english_name'    => "warranty-en-{$unique}",
            'slug'            => "warranty-slug-{$unique}",
            'duration_months' => 12,
            'provider'        => "provider-{$unique}",
            'description'     => null,
            'is_active'       => true,
            'sort_order'      => 0,
        ];
    }

    public function active(): static
    {
        return $this->state(['is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function withDuration(int $months): static
    {
        return $this->state(['duration_months' => $months]);
    }
}
```

---

## 🧪 تست‌ها


### `modules/Brands/tests/Feature/Admin/BrandActivateTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Modules\Brands\Events\BrandActivated;
use Modules\Brands\Events\BrandDeactivated;
use Modules\Brands\Models\Brand;

uses(\Tests\TestCase::class);
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// ──────────── Activate ────────────

test('admin can activate brand', function () {
    Event::fake();

    $brand = Brand::factory()->inactive()->create();

    $response = $this->postJson("/api/admin/brands/{$brand->slug}/activate");

    $response->assertOk();
    $response->assertJsonPath('data.is_active', true);

    $brand->refresh();
    expect($brand->is_active)->toBeTrue();

    Event::assertDispatched(BrandActivated::class);
});

test('activating already active brand still works', function () {
    Event::fake();

    $brand = Brand::factory()->active()->create();

    $response = $this->postJson("/api/admin/brands/{$brand->slug}/activate");

    $response->assertOk();
    $response->assertJsonPath('data.is_active', true);

    Event::assertDispatched(BrandActivated::class);
});

test('activate returns 404 for non-existent brand', function () {
    $response = $this->postJson('/api/admin/brands/non-existent/activate');

    $response->assertStatus(404);
});

// ──────────── Deactivate ────────────

test('admin can deactivate brand', function () {
    Event::fake();

    $brand = Brand::factory()->active()->create();

    $response = $this->postJson("/api/admin/brands/{$brand->slug}/deactivate");

    $response->assertOk();
    $response->assertJsonPath('data.is_active', false);

    $brand->refresh();
    expect($brand->is_active)->toBeFalse();

    Event::assertDispatched(BrandDeactivated::class);
});

test('deactivating already inactive brand still works', function () {
    Event::fake();

    $brand = Brand::factory()->inactive()->create();

    $response = $this->postJson("/api/admin/brands/{$brand->slug}/deactivate");

    $response->assertOk();
    $response->assertJsonPath('data.is_active', false);

    Event::assertDispatched(BrandDeactivated::class);
});

test('deactivate returns 404 for non-existent brand', function () {
    $response = $this->postJson('/api/admin/brands/non-existent/deactivate');

    $response->assertStatus(404);
});
```

### `modules/Brands/tests/Feature/Admin/BrandCategoryAttachTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Brands\Events\CategoryAttachedToBrand;
use Modules\Brands\Models\Brand;
use Modules\Categories\Models\Category;
use Tests\TestCase;

uses(RefreshDatabase::class, TestCase::class);

// ──────────── Happy Path ────────────

test('admin can attach category to brand', function () {
    Event::fake();

    $brand = Brand::factory()->create();
    $category = Category::factory()->active()->create(['slug'=>'hello']);

    $response = $this->postJson("/api/admin/brands/{$brand->slug}/categories", [
        'category_id' => $category->id,
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('brand_category', [
        'brand_id'    => $brand->id,
        'category_id' => $category->id,
        'is_primary'  => false,
    ]);

    Event::assertDispatched(
        CategoryAttachedToBrand::class,
        fn ($event) => $event->brandId === $brand->id
            && $event->categoryId === $category->id,
    );
});

test('admin can attach category as primary', function () {
    $brand = Brand::factory()->create();
    $category = Category::factory()->active()->create();

    $response = $this->postJson("/api/admin/brands/{$brand->slug}/categories", [
        'category_id' => $category->id,
        'is_primary'  => true,
    ]);

    $response->assertStatus(201);  // ← فیکس: 201 نه 200

    $this->assertDatabaseHas('brand_category', [
        'brand_id'    => $brand->id,
        'category_id' => $category->id,
        'is_primary'  => true,
    ]);
});

test('attaching primary unsets previous primary', function () {
    $brand = Brand::factory()->create();
    $oldPrimary = Category::factory()->active()->create();
    $newPrimary = Category::factory()->active()->create();

    $brand->attachCategory($oldPrimary->id, true);

    $response = $this->postJson("/api/admin/brands/{$brand->slug}/categories", [
        //          ↑ فیکس: postJson نه posTJson
        'category_id' => $newPrimary->id,
        'is_primary'  => true,
    ]);

    $response->assertStatus(201);  // ← فیکس: 201

    $this->assertDatabaseHas('brand_category', [
        'brand_id'    => $brand->id,
        'category_id' => $oldPrimary->id,
        'is_primary'  => false,
    ]);

    $this->assertDatabaseHas('brand_category', [
        'brand_id'    => $brand->id,
        'category_id' => $newPrimary->id,
        'is_primary'  => true,
    ]);
});

// ──────────── Validation Error ────────────

test('it requires category_id', function () {
    $brand = Brand::factory()->create();

    $response = $this->postJson("/api/admin/brands/{$brand->slug}/categories", []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['category_id']);
});

test('it rejects non-integer category_id', function () {
    $brand = Brand::factory()->create();

    $response = $this->postJson("/api/admin/brands/{$brand->slug}/categories", [
        'category_id' => 'not-a-number',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['category_id']);
});

test('it errors when category does not exist', function () {
    $brand = Brand::factory()->create();

    $response = $this->postJson("/api/admin/brands/{$brand->slug}/categories", [
        'category_id' => 99999,
    ]);

    expect($response->status())->toBeIn([404, 422, 500]);
});

test('it errors when category is inactive', function () {
    $brand = Brand::factory()->create();
    $inactive = Category::factory()->inactive()->create();  // ← فیکس: inactive state

    $response = $this->postJson("/api/admin/brands/{$brand->slug}/categories", [
        'category_id' => $inactive->id,
    ]);

    expect($response->status())->toBeIn([422, 500]);

    $this->assertDatabaseMissing('brand_category', [
        'brand_id'    => $brand->id,
        'category_id' => $inactive->id,
    ]);
});

test('it errors when category already attached', function () {
    $brand = Brand::factory()->create();
    $category = Category::factory()->active()->create();

    $brand->attachCategory($category->id);

    $response = $this->postJson("/api/admin/brands/{$brand->slug}/categories", [
        'category_id' => $category->id,
    ]);

    expect($response->status())->toBeIn([409, 422, 500]);
});

test('it returns 404 for non-existent brand', function () {
    $category = Category::factory()->active()->create();

    $response = $this->postJson('/api/admin/brands/non-existent/categories', [
        'category_id' => $category->id,
    ]);

    $response->assertStatus(404);
});
```

### `modules/Brands/tests/Feature/Admin/BrandCategoryDetachTest.php`

```php
<?php
declare(strict_types=1);


use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Brands\Events\CategoryDetachedFromBrand;
use Modules\Brands\Models\Brand;
use Modules\Categories\Models\Category;
use Tests\TestCase;

uses(RefreshDatabase::class, TestCase::class);


test('admin can detach category from brand', function () {

    Event::fake();

    $brand = Brand::factory()->create();

    $category = Category::factory()->active()->create(['slug' => 'hello']);

    $brand->attachCategory($category->id);


    $response = $this->deleteJson("/api/admin/brands/{$brand->slug}/categories/{$category->id}");

    $response->assertNoContent();

    $this->assertDatabaseMissing('brand_category', [
        'brand_id' => $brand->id,
        'category_id' => $category->id,
    ]);

    Event::assertDispatched(CategoryDetachedFromBrand::class, fn($event) => $event->brandId === $brand->id
        && $event->categoryId === $category->id);

});


test('detaching one category keeps others attached', function () {

    $brand = Brand::factory()->create();

    $cat1 = Category::factory()->active()->create(['slug' => 'cate-1']);
    $cat2 = Category::factory()->active()->create(['slug' => 'cate-2']);


    $brand->attachCategory($cat1->id);
    $brand->attachCategory($cat2->id);

    $response = $this->deleteJson("/api/admin/brands/{$brand->slug}/categories/{$cat1->id}");

    $response->assertNoContent();


    $this->assertDatabaseMissing('brand_category', [
        'brand_id' => $brand->id,
        'category_id' => $cat1->id,
    ]);

    $this->assertDatabaseHas('brand_category', [
        'brand_id' => $brand->id,
        'category_id' => $cat2->id,
    ]);


});

// ──────────── Business Errors ────────────

test('it returns 404 for non-existent category', function () {

    $category = Category::factory()->active()->create();


    $response=$this->deleteJson("/api/admin/brands/category/none-existent/categories/{$category->id}");

    $response->assertStatus(404);
});
```

### `modules/Brands/tests/Feature/Admin/BrandCategoryListTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Brands\Models\Brand;
use Modules\Categories\Models\Category;
use Tests\TestCase;

uses(RefreshDatabase::class, TestCase::class);

test('returns empty list when brand has no categories', function () {
    $brand = Brand::factory()->create();

    $response = $this->getJson("/api/admin/brands/{$brand->slug}/categories");

    $response->assertOk();
    $response->assertJsonCount(0, 'data');
});

test('returns categories attached to brand', function () {
    $brand = Brand::factory()->create();
    $cat1 = Category::factory()->active()->withSlug()->create([
        'name' => 'موبایل',
    ]);
    $cat2 = Category::factory()->active()->withSlug()->create([
        'name' => 'تبلت',
    ]);

    $brand->attachCategory($cat1->id);
    $brand->attachCategory($cat2->id);

    $response = $this->getJson("/api/admin/brands/{$brand->slug}/categories");

    $response->assertOk();
    $response->assertJsonCount(2, 'data');
});

test('includes category details from CategoryServiceContract', function () {
    $brand = Brand::factory()->create();
    $category = Category::factory()->active()->withSlug()->create([
        'name' => 'موبایل',
    ]);

    $brand->attachCategory($category->id);

    $response = $this->getJson("/api/admin/brands/{$brand->slug}/categories");

    $response->assertOk();
    $response->assertJsonPath('data.0.category_id', $category->id);
    $response->assertJsonPath('data.0.name', 'موبایل');
});

test('includes is_primary flag', function () {
    $brand = Brand::factory()->create();
    $primary = Category::factory()->active()->withSlug()->create();
    $secondary = Category::factory()->active()->withSlug()->create();

    $brand->attachCategory($primary->id, isPrimary: true);
    $brand->attachCategory($secondary->id);

    $response = $this->getJson("/api/admin/brands/{$brand->slug}/categories");

    $response->assertOk();

    // پیدا کن primary رو
    $data = $response->json('data');
    $primaryItem = collect($data)->firstWhere('category_id', $primary->id);
    $secondaryItem = collect($data)->firstWhere('category_id', $secondary->id);

    expect($primaryItem['is_primary'])->toBeTrue()
        ->and($secondaryItem['is_primary'])->toBeFalse();
});

test('returns categories ordered by sort_order', function () {
    $brand = Brand::factory()->create();
    $cat1 = Category::factory()->active()->withSlug()->create();
    $cat2 = Category::factory()->active()->withSlug()->create();
    $cat3 = Category::factory()->active()->withSlug()->create();

    // attach با sort_order متفاوت
    $brand->attachCategory($cat1->id, sortOrder: 2);
    $brand->attachCategory($cat2->id, sortOrder: 0);
    $brand->attachCategory($cat3->id, sortOrder: 1);

    $response = $this->getJson("/api/admin/brands/{$brand->slug}/categories");

    $response->assertOk();
    $data = $response->json('data');

    // ترتیب: cat2 (0), cat3 (1), cat1 (2)
    expect($data[0]['category_id'])->toBe($cat2->id)
        ->and($data[1]['category_id'])->toBe($cat3->id)
        ->and($data[2]['category_id'])->toBe($cat1->id);
});

test('returns 404 for non-existent brand', function () {
    $response = $this->getJson('/api/admin/brands/non-existent/categories');

    $response->assertStatus(404);
});
```

### `modules/Brands/tests/Feature/Admin/BrandCategorySyncTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Brands\Events\BrandCategoriesSynced;
use Modules\Brands\Models\Brand;
use Modules\Categories\Models\Category;
use Tests\TestCase;

uses(RefreshDatabase::class, TestCase::class);

// ──────────── Happy Path ────────────

test('admin can sync categories to empty brand', function () {
    Event::fake();

    $brand = Brand::factory()->create();
    $cats = Category::factory()->active()->withSlug()->count(3)->create();
    $ids = $cats->pluck('id')->all();

    $response = $this->putJson("/api/admin/brands/{$brand->slug}/categories", [
        'category_ids' => $ids,
    ]);

    $response->assertOk();

    // همه 3 تا attached شدن
    foreach ($ids as $catId) {
        $this->assertDatabaseHas('brand_category', [
            'brand_id'    => $brand->id,
            'category_id' => $catId,
        ]);
    }

    Event::assertDispatched(BrandCategoriesSynced::class);
});

test('sync replaces previous categories', function () {
    $brand = Brand::factory()->create();
    $oldCats = Category::factory()->active()->count(2)->create();
    $newCats = Category::factory()->active()->count(3)->create();

    // اول old ها رو attach کن
    foreach ($oldCats as $cat) {
        $brand->attachCategory($cat->id);
    }

    // sync با new ها
    $response = $this->putJson("/api/admin/brands/{$brand->slug}/categories", [
        'category_ids' => $newCats->pluck('id')->all(),
    ]);

    $response->assertOk();

    // old ها نباشن
    foreach ($oldCats as $cat) {
        $this->assertDatabaseMissing('brand_category', [
            'brand_id'    => $brand->id,
            'category_id' => $cat->id,
        ]);
    }

    // new ها باشن
    foreach ($newCats as $cat) {
        $this->assertDatabaseHas('brand_category', [
            'brand_id'    => $brand->id,
            'category_id' => $cat->id,
        ]);
    }
});

test('sync sets primary correctly', function () {
    $brand = Brand::factory()->create();
    $cats = Category::factory()->active()->count(3)->create();
    $ids = $cats->pluck('id')->all();
    $primaryId = $ids[1];  // دومی

    $response = $this->putJson("/api/admin/brands/{$brand->slug}/categories", [
        'category_ids' => $ids,
        'primary_id'   => $primaryId,
    ]);

    $response->assertOk();

    // دومی primary ه
    $this->assertDatabaseHas('brand_category', [
        'brand_id'    => $brand->id,
        'category_id' => $primaryId,
        'is_primary'  => true,
    ]);

    // اولی و سومی primary نیستن
    $this->assertDatabaseHas('brand_category', [
        'brand_id'    => $brand->id,
        'category_id' => $ids[0],
        'is_primary'  => false,
    ]);
    $this->assertDatabaseHas('brand_category', [
        'brand_id'    => $brand->id,
        'category_id' => $ids[2],
        'is_primary'  => false,
    ]);
});

test('sync without primary_id sets all non-primary', function () {
    $brand = Brand::factory()->create();
    $cats = Category::factory()->active()->count(3)->create();

    $response = $this->putJson("/api/admin/brands/{$brand->slug}/categories", [
        'category_ids' => $cats->pluck('id')->all(),
        // primary_id نمی‌فرستیم
    ]);

    $response->assertOk();

    // همه non-primary
    foreach ($cats as $cat) {
        $this->assertDatabaseHas('brand_category', [
            'brand_id'    => $brand->id,
            'category_id' => $cat->id,
            'is_primary'  => false,
        ]);
    }
});

// ──────────── Validation Errors ────────────

test('it requires category_ids', function () {
    $brand = Brand::factory()->create();

    $response = $this->putJson("/api/admin/brands/{$brand->slug}/categories", []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['category_ids']);
});

test('it rejects empty category_ids array', function () {
    $brand = Brand::factory()->create();

    $response = $this->putJson("/api/admin/brands/{$brand->slug}/categories", [
        'category_ids' => [],
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['category_ids']);
});

test('it rejects duplicate category_ids', function () {
    $brand = Brand::factory()->create();
    $cat = Category::factory()->active()->create();

    $response = $this->putJson("/api/admin/brands/{$brand->slug}/categories", [
        'category_ids' => [$cat->id, $cat->id],  // duplicate
    ]);

    $response->assertStatus(422);
});

test('it rejects primary_id not in category_ids list', function () {
    $brand = Brand::factory()->create();
    $cats = Category::factory()->active()->count(2)->create();
    $other = Category::factory()->active()->create();

    $response = $this->putJson("/api/admin/brands/{$brand->slug}/categories", [
        'category_ids' => $cats->pluck('id')->all(),
        'primary_id'   => $other->id,  // ← تو list نیست!
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['primary_id']);
});

// ──────────── Business Errors ────────────

test('it errors when some categories do not exist', function () {
    $brand = Brand::factory()->create();
    $existingCat = Category::factory()->active()->create();

    $response = $this->putJson("/api/admin/brands/{$brand->slug}/categories", [
        'category_ids' => [$existingCat->id, 99999],  // یکی وجود نداره
    ]);

    expect($response->status())->toBeIn([404, 422, 500]);

    // هیچ‌چیز attach نشده
    $this->assertDatabaseMissing('brand_category', [
        'brand_id' => $brand->id,
    ]);
});

test('it errors when some categories are inactive', function () {
    $brand = Brand::factory()->create();
    $active = Category::factory()->active()->create();
    $inactive = Category::factory()->inactive()->create();

    $response = $this->putJson("/api/admin/brands/{$brand->slug}/categories", [
        'category_ids' => [$active->id, $inactive->id],
    ]);

    expect($response->status())->toBeIn([422, 500]);

    // هیچ attach نشده (transaction rollback)
    $this->assertDatabaseMissing('brand_category', [
        'brand_id' => $brand->id,
    ]);
});

// ──────────── 404 ────────────

test('it returns 404 for non-existent brand', function () {
    $cat = Category::factory()->active()->create();

    $response = $this->putJson('/api/admin/brands/non-existent/categories', [
        'category_ids' => [$cat->id],
    ]);

    $response->assertStatus(404);
});
```

### `modules/Brands/tests/Feature/Admin/BrandDestoryTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Brands\Events\BrandDeleted;
use Modules\Brands\Models\Brand;

uses(\Tests\TestCase::class);
uses(RefreshDatabase::class);

test('admin can soft delete brand', function () {
    Event::fake();

    $brand = Brand::factory()->create();

    $response = $this->deleteJson("/api/admin/brands/{$brand->slug}");

    $response->assertNoContent();  // 204

    // Soft deleted ه
    $this->assertSoftDeleted('brands', ['id' => $brand->id]);

    Event::assertDispatched(BrandDeleted::class);
});

test('it returns 404 when deleting non-existent brand', function () {
    $response = $this->deleteJson('/api/admin/brands/non-existent-slug');

    $response->assertStatus(404);
});

test('it returns 404 when deleting already trashed brand', function () {
    $brand = Brand::factory()->create();
    $brand->delete();  // soft delete اول

    $response = $this->deleteJson("/api/admin/brands/{$brand->slug}");

    $response->assertStatus(404);  // Route binding پیدا نمی‌کنه
});

test('it dispatches event with brand id', function () {
    Event::fake();

    $brand = Brand::factory()->create();
    $brandId = $brand->id;

    $this->deleteJson("/api/admin/brands/{$brand->slug}");

    Event::assertDispatched(
        BrandDeleted::class,
        fn (BrandDeleted $event) => $event->brandId === $brandId,
    );
});
```

### `modules/Brands/tests/Feature/Admin/BrandIndexTest.php`

```php
<?php

declare(strict_types=1);

use Modules\Brands\Models\Brand;

uses(\Tests\TestCase::class);
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('admin can list brands', function () {
    Brand::factory()->count(3)->create();

    $response = $this->getJson('/api/admin/brands');

    $response->assertOk();
    $response->assertJsonCount(3, 'data');
});

test('it returns empty list when no brands', function () {
    $response = $this->getJson('/api/admin/brands');

    $response->assertOk();
    $response->assertJsonCount(0, 'data');
});

test('it paginates results', function () {
    Brand::factory()->count(20)->create();

    $response = $this->getJson('/api/admin/brands?per_page=5');

    $response->assertOk();
    $response->assertJsonCount(5, 'data');
});

test('it caps per_page at 100', function () {
    Brand::factory()->count(150)->create();

    $response = $this->getJson('/api/admin/brands?per_page=999');

    $response->assertOk();
    // فقط 100 تا، نه 150
    $response->assertJsonCount(100, 'data');
});

test('it does not include soft deleted brands', function () {
    Brand::factory()->count(2)->create();
    $deleted = Brand::factory()->create();
    $deleted->delete();  // soft delete

    $response = $this->getJson('/api/admin/brands');

    $response->assertOk();
    $response->assertJsonCount(2, 'data');
});

test('it returns proper structure', function () {
    Brand::factory()->create();

    $response = $this->getJson('/api/admin/brands');

    $response->assertOk();
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id',
                'name',
                'english_name',
                'slug',
                'is_active',
                'is_featured',
                'seo' => [
                    'meta_title',
                    'meta_description',
                ],
            ],
        ],
        'links',
        'meta',
    ]);
});
```

### `modules/Brands/tests/Feature/Admin/BrandSearchTest.php`

```php
<?php

declare(strict_types=1);

use Modules\Brands\Models\Brand;

uses(\Tests\TestCase::class);
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('it finds brand by name', function () {
    Brand::factory()->create(['name' => 'سامسونگ']);
    Brand::factory()->create(['name' => 'اپل']);

    $response = $this->getJson('/api/admin/brands/search?q=سامسونگ');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.name', 'سامسونگ');
});

test('it finds brand by english name', function () {
    Brand::factory()->create(['english_name' => 'Samsung']);
    Brand::factory()->create(['english_name' => 'Apple']);

    $response = $this->getJson('/api/admin/brands/search?q=Sam');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

test('it finds brand by slug', function () {
    Brand::factory()->create(['slug' => 'samsung-mobile']);
    Brand::factory()->create(['slug' => 'apple-tv']);

    $response = $this->getJson('/api/admin/brands/search?q=mobile');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

test('it returns empty results for no match', function () {
    Brand::factory()->count(3)->create();

    $response = $this->getJson('/api/admin/brands/search?q=non-existent-term');

    $response->assertOk();
    $response->assertJsonCount(0, 'data');
});

test('it handles empty search term', function () {
    Brand::factory()->count(3)->create();

    $response = $this->getJson('/api/admin/brands/search?q=');

    $response->assertOk();
    // empty term معمولاً همه رو برمی‌گردونه
});

test('it returns paginated results', function () {
    for ($i = 1; $i <= 20; $i++) {
        Brand::factory()->create([
            'name' => "سامسونگ مدل {$i}",  // ← هر کدوم متفاوت
        ]);
    }

    $response = $this->getJson('/api/admin/brands/search?q=سامسونگ&per_page=5');

    $response->assertOk();
    $response->assertJsonCount(5, 'data');
});
```

### `modules/Brands/tests/Feature/Admin/BrandShowTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Brands\Models\Brand;

uses(\Tests\TestCase::class);
uses(RefreshDatabase::class);

test('admin can view brand by slug', function () {
    $brand = Brand::factory()->create([
        'name' => 'سامسونگ',
        'slug' => 'samsung',
    ]);

    $response = $this->getJson("/api/admin/brands/{$brand->slug}");

    $response->assertOk();
    $response->assertJsonPath('data.id', $brand->id);
    $response->assertJsonPath('data.name', 'سامسونگ');
    $response->assertJsonPath('data.slug', 'samsung');
});

test('it includes seo group in response', function () {
    $brand = Brand::factory()->create([
        'meta_title'       => 'عنوان سئو',
        'meta_description' => 'توضیحات سئو',
    ]);

    $response = $this->getJson("/api/admin/brands/{$brand->slug}");

    $response->assertOk();
    $response->assertJsonPath('data.seo.meta_title', 'عنوان سئو');
    $response->assertJsonPath('data.seo.meta_description', 'توضیحات سئو');
});

test('it returns is_active as boolean not integer', function () {
    $brand = Brand::factory()->create(['is_active' => true]);

    $response = $this->getJson("/api/admin/brands/{$brand->slug}");

    $response->assertOk();

    // باید boolean باشه نه 1
    $data = $response->json('data');
    expect($data['is_active'])->toBeBool()
        ->and($data['is_active'])->toBeTrue();
});

test('it does not expose audit fields', function () {
    $brand = Brand::factory()->create([
        'created_by' => 5,
        'updated_by' => 7,
    ]);

    $response = $this->getJson("/api/admin/brands/{$brand->slug}");

    $response->assertOk();

    $data = $response->json('data');
    expect($data)->not->toHaveKey('created_by')
        ->and($data)->not->toHaveKey('updated_by');
});

test('it returns 404 when brand does not exist', function () {
    $response = $this->getJson('/api/admin/brands/non-existent-slug');

    $response->assertStatus(404);
});

test('it returns 404 for soft deleted brand', function () {
    $brand = Brand::factory()->create();
    $brand->delete();  // soft delete

    $response = $this->getJson("/api/admin/brands/{$brand->slug}");

    $response->assertStatus(404);
});
```

### `modules/Brands/tests/Feature/Admin/BrandStoreTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Brands\Events\BrandCreated;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);


test('admin can create brand with valid data', function () {

    Event::fake();
    $payload = [
        'name' => 'سامسونگ',
        'english_name' => 'Samsung',
        'slug' => 'samsung',
        'description' => 'برند کره‌ای',
        'is_active' => true,
        'is_featured' => false,
    ];


    $response = $this->postJson('/api/admin/brands', $payload);

    $response->assertStatus(201);
    $response->assertJsonPath('data.name', 'سامسونگ');
    $response->assertJsonPath('data.slug', 'samsung');
    $response->assertJsonPath('data.is_active', true);

    $this->assertDatabaseHas('brands', [
        'name' => 'سامسونگ',
        'slug' => 'samsung',
    ]);

    Event::assertDispatched(BrandCreated::class);
});


test('admin can create brand with minimum fields', function () {
    $payload = [
        'name' => 'تست',
        'english_name' => 'Test Brand',
        'slug' => 'test-brand',
    ];


    $response = $this->postJson('/api/admin/brands', $payload);

    $response->assertStatus(201);
    $response->assertJsonPath('data.is_active', true);  // default
    $response->assertJsonPath('data.is_featured', false);  // default
});
```

### `modules/Brands/tests/Feature/Admin/BrandUpdateTest.php`

```php
<?php
declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Brands\Events\BrandUpdated;
use Modules\Brands\Models\Brand;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('admin can update brand name', function () {

    Event::fake();

    $brand = Brand::factory()->create([
        'name' => 'سامسونگ',
        'slug' => 'samsung',
    ]);

    $response = $this->patchJson("/api/admin/brands/{$brand->slug}", [
        'name' => 'سامسونگ الکترونیک',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.name', 'سامسونگ الکترونیک');
    $this->assertDatabaseHas('brands', [
        'id' => $brand->id,
        'name' => 'سامسونگ الکترونیک',
    ]);

    Event::assertDispatched(BrandUpdated::class);
});


test('admin can update multiple fields at once', function () {

    $brand = Brand::factory()->create([
        'name' => 'برند قدیم',
        'description' => 'توضیحات قدیم',
    ]);

    $response = $this->patchJson("/api/admin/brands/{$brand->slug}", [
        'name' => 'برند جدید',
        'description' => 'توضیحات جدید',
        'is_featured' => true,
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.name', 'برند جدید');
    $response->assertJsonPath('data.name', 'برند جدید');
    $response->assertJsonPath('data.description', 'توضیحات جدید');
    $response->assertJsonPath('data.is_featured', true);


    $this->assertDatabaseHas('brands', [
        'name' => 'برند جدید',
        'description' => 'توضیحات جدید',
        'is_featured' => true,
    ]);
});


test('partial update only changes specified fields', function () {
    $brand = Brand::factory()->create([
        'name' => 'سامسونگ',
        'english_name' => 'Samsung',
        'description' => 'توضیحات اصلی',
    ]);


    $response = $this->patchJson("/api/admin/brands/{$brand->slug}", [
        'name' => 'جدید'
    ]);

    $brand->refresh();

    expect($brand->name)->toBe('جدید')
        ->and($brand->english_name)->toBe('Samsung')
        ->and($brand->description)->toBe('توضیحات اصلی');
});


test('it can keep same slug when updating', function () {

    $brand = Brand::factory()->create(['slug' => 'samsung']);

    $response = $this->patchJson("/api/admin/brands/{$brand->slug}", [
        'name' => 'جدید',
        'slug' => 'samsung',
    ]);

    $response->assertOk();
});



test('it rejects slug already used by another brand',function (){

     Brand::factory()->create(['slug' => 'apple']);

    $samsung = Brand::factory()->create(['slug' => 'samsung']);

    $response = $this->patchJson("/api/admin/brands/{$samsung->slug}", [
        'slug' => 'apple',  // ← slug brand دیگه
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['slug']);
});


// ──────────── Validation ────────────

test('it rejects invalid slug format', function () {
    $brand = Brand::factory()->create();

    $response = $this->patchJson("/api/admin/brands/{$brand->slug}", [
        'slug' => 'INVALID SLUG!',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['slug']);
});

test('it rejects empty name when provided', function () {
    $brand = Brand::factory()->create();

    $response = $this->patchJson("/api/admin/brands/{$brand->slug}", [
        'name' => '',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name']);
});

// ──────────── 404 ────────────

test('it returns 404 for non-existent brand', function () {
    $response = $this->patchJson('/api/admin/brands/non-existent-slug', [
        'name' => 'تست',
    ]);

    $response->assertStatus(404);
});

// ──────────── No Changes ────────────

test('update with empty body does not crash', function () {
    $brand = Brand::factory()->create();

    $response = $this->patchJson("/api/admin/brands/{$brand->slug}", []);

    $response->assertOk();
    // Brand همون می‌مونه
});


```

### `modules/Brands/tests/Unit/BrandModelTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Brands\Models\Brand;
use Tests\TestCase;

uses(TestCase::class,RefreshDatabase::class);

// ──────────── Creation ────────────

test('it can create a brand with factory', function () {
    $brand = Brand::factory()->create();

    expect($brand)->toBeInstanceOf(Brand::class)
        ->and($brand->id)->toBeInt()
        ->and($brand->name)->toBeString()
        ->and($brand->slug)->toBeString();
});

test('it can create a brand with specific attributes', function () {
    $brand = Brand::factory()->create([
        'name'         => 'سامسونگ',
        'english_name' => 'Samsung',
        'slug'         => 'samsung',
    ]);

    expect($brand->name)->toBe('سامسونگ')
        ->and($brand->english_name)->toBe('Samsung')
        ->and($brand->slug)->toBe('samsung');
});

// ──────────── Casts ────────────

test('it casts is_active to boolean', function () {
    $brand = Brand::factory()->create(['is_active' => 1]);

    expect($brand->is_active)->toBeBool()
        ->and($brand->is_active)->toBeTrue();
});

test('it casts is_featured to boolean', function () {
    $brand = Brand::factory()->create(['is_featured' => 0]);

    expect($brand->is_featured)->toBeBool()
        ->and($brand->is_featured)->toBeFalse();
});

test('it casts sort_order to integer', function () {
    $brand = Brand::factory()->create(['sort_order' => '42']);

    expect($brand->sort_order)->toBeInt()
        ->and($brand->sort_order)->toBe(42);
});

// ──────────── Scopes ────────────

test('scope active returns only active brands', function () {
    Brand::factory()->active()->count(3)->create();
    Brand::factory()->inactive()->count(2)->create();

    $active = Brand::active()->get();

    expect($active)->toHaveCount(3)
        ->and($active->every(fn($b) => $b->is_active === true))->toBeTrue();
});

test('scope featured returns only featured brands', function () {
    Brand::factory()->featured()->count(2)->create();
    Brand::factory()->count(3)->create();  // not featured

    $featured = Brand::featured()->get();

    expect($featured)->toHaveCount(2)
        ->and($featured->every(fn($b) => $b->is_featured === true))->toBeTrue();
});

test('scope ordered sorts by sort_order then name', function () {
    Brand::factory()->create(['name' => 'B Brand', 'sort_order' => 1]);
    Brand::factory()->create(['name' => 'A Brand', 'sort_order' => 2]);
    Brand::factory()->create(['name' => 'C Brand', 'sort_order' => 0]);

    $ordered = Brand::ordered()->get();

    expect($ordered->first()->name)->toBe('C Brand')
        ->and($ordered->last()->name)->toBe('A Brand');  // sort_order 0
    // sort_order 2
});

test('scope search finds by name', function () {
    Brand::factory()->create(['name' => 'سامسونگ']);
    Brand::factory()->create(['name' => 'اپل']);
    Brand::factory()->create(['name' => 'هواوی']);

    $results = Brand::search('سامسونگ')->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->name)->toBe('سامسونگ');
});

test('scope search finds by english name', function () {
    Brand::factory()->create(['english_name' => 'Samsung']);
    Brand::factory()->create(['english_name' => 'Apple']);

    $results = Brand::search('Sam')->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->english_name)->toBe('Samsung');
});

test('scope search finds by slug', function () {
    Brand::factory()->create(['slug' => 'samsung-mobile']);
    Brand::factory()->create(['slug' => 'apple-tv']);

    $results = Brand::search('mobile')->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->slug)->toBe('samsung-mobile');
});

// ──────────── Soft Delete ────────────

test('it soft deletes brand', function () {
    $brand = Brand::factory()->create();
    $id = $brand->id;

    $brand->delete();

    // در database هست (soft deleted)
    expect(Brand::find($id))->toBeNull()
        ->and(Brand::withTrashed()->find($id))->not->toBeNull()
        ->and(Brand::withTrashed()->find($id)->trashed())->toBeTrue();
});

test('it can restore soft deleted brand', function () {
    $brand = Brand::factory()->create();
    $brand->delete();

    Brand::withTrashed()->find($brand->id)->restore();

    expect(Brand::find($brand->id))->not->toBeNull();
});

// ──────────── Route Model Binding ────────────

test('it uses slug for route model binding', function () {
    $brand = Brand::factory()->create(['slug' => 'samsung']);

    expect($brand->getRouteKeyName())->toBe('slug');
});
```

### `modules/Brands/tests/Unit/BrandServiceTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Modules\Brands\Contracts\BrandRepositoryContract;
use Modules\Brands\DataTransferObjects\CreateBrandData;
use Modules\Brands\DataTransferObjects\UpdateBrandData;
use Modules\Brands\Events\BrandActivated;
use Modules\Brands\Events\BrandCreated;
use Modules\Brands\Events\BrandDeactivated;
use Modules\Brands\Events\BrandDeleted;
use Modules\Brands\Events\BrandUpdated;
use Modules\Brands\Exceptions\BrandNotFoundException;
use Modules\Brands\Models\Brand;
use Modules\Brands\Services\BrandService;
use Modules\Categories\Contracts\CategoryServiceContract;
use Tests\TestCase;

uses(TestCase::class);

// ──────────── Helper ────────────

function makeBrandService(): array
{
    $brandRepo = Mockery::mock(BrandRepositoryContract::class);
    $categoryService = Mockery::mock(CategoryServiceContract::class);
    $service = new BrandService($brandRepo, $categoryService);

    return [$service, $brandRepo, $categoryService];  // ← هر دو mock
}

// ──────────── findById ────────────

test('findById returns brand when exists', function () {
    [$service, $repo] = makeBrandService();

    $brand = Brand::factory()->make(['id' => 1]);
    $repo->expects('find')->with(1)->andReturn($brand);

    $result = $service->findById(1);

    expect($result)->toBeInstanceOf(Brand::class)
        ->and($result->id)->toBe(1);
});

test('findById throws exception when not found', function () {
    [$service, $repo] = makeBrandService();

    $repo->expects('find')->with(999)->andReturn(null);

    expect(fn() => $service->findById(999))
        ->toThrow(BrandNotFoundException::class);
});

// ──────────── create ────────────

test('create persists brand and dispatches event', function () {
    Event::fake();

    [$service, $repo] = makeBrandService();

    $brand = Brand::factory()->make(['id' => 1, 'name' => 'سامسونگ']);

    $repo->expects('create')
        ->with(Mockery::on(fn($data) => $data['name'] === 'سامسونگ'))
        ->andReturn($brand);

    $data = new CreateBrandData(
        name: 'سامسونگ',
        englishName: 'Samsung',
        slug: 'samsung',
    );

    $result = $service->create($data);

    expect($result)->toBeInstanceOf(Brand::class)
        ->and($result->name)->toBe('سامسونگ');

    Event::assertDispatched(BrandCreated::class);
});

// ──────────── update ────────────

test('update returns brand without query when no changes', function () {
    [$service, $repo] = makeBrandService();

    $brand = Brand::factory()->make(['id' => 1]);

    // hasChanges() will be false, so update should NOT be called
    $repo->shouldNotReceive('update');
    $repo->expects('find')->with(1)->andReturn($brand);

    $emptyData = new UpdateBrandData();
    $result = $service->update(1, $emptyData);

    expect($result->id)->toBe(1);
});

test('update modifies brand and dispatches event', function () {
    Event::fake();

    [$service, $repo] = makeBrandService();

    $updatedBrand = Brand::factory()->make(['id' => 1, 'name' => 'جدید']);

    $repo->expects('update')
        ->with(1, Mockery::on(fn($data) => $data['name'] === 'جدید'))
        ->andReturn($updatedBrand);

    $data = new UpdateBrandData(name: 'جدید');
    $result = $service->update(1, $data);

    expect($result->name)->toBe('جدید');

    Event::assertDispatched(BrandUpdated::class);
});

// ──────────── delete ────────────

test('delete removes brand and dispatches event', function () {
    Event::fake();

    [$service, $repo] = makeBrandService();

    $brand = Brand::factory()->make(['id' => 1]);

    $repo->expects('find')->with(1)->andReturn($brand);
    $repo->expects('delete')->with(1)->andReturn(true);

    $result = $service->delete(1);

    expect($result)->toBeTrue();
    Event::assertDispatched(BrandDeleted::class);
});

test('delete throws exception when brand not found', function () {
    [$service, $repo] = makeBrandService();

    $repo->expects('find')->with(999)->andReturn(null);
    $repo->shouldNotReceive('delete');

    expect(fn() => $service->delete(999))
        ->toThrow(BrandNotFoundException::class);
});

// ──────────── activate ────────────

test('activate updates is_active and dispatches event', function () {
    Event::fake();

    [$service, $repo] = makeBrandService();

    $brand = Brand::factory()->make(['id' => 1, 'is_active' => true]);

    $repo->expects('update')
        ->with(1, ['is_active' => true])
        ->andReturn($brand);

    $result = $service->activate(1);

    expect($result->is_active)->toBeTrue();
    Event::assertDispatched(BrandActivated::class);
});

// ──────────── deactivate ────────────

test('deactivate updates is_active and dispatches event', function () {
    Event::fake();

    [$service, $repo] = makeBrandService();

    $brand = Brand::factory()->make(['id' => 1, 'is_active' => false]);

    $repo->expects('update')
        ->with(1, ['is_active' => false])
        ->andReturn($brand);

    $result = $service->deactivate(1);

    expect($result->is_active)->toBeFalse();
    Event::assertDispatched(BrandDeactivated::class);
});

// ──────────── exists / isActive ────────────

test('exists returns true when brand exists', function () {
    [$service, $repo] = makeBrandService();

    $repo->expects('exists')->with(1)->andReturn(true);

    expect($service->exists(1))->toBeTrue();
});

test('isActive returns true for active brand', function () {
    [$service, $repo] = makeBrandService();

    $brand = Brand::factory()->make(['is_active' => true]);
    $repo->expects('find')->with(1)->andReturn($brand);

    expect($service->isActive(1))->toBeTrue();
});

test('isActive returns false for inactive brand', function () {
    [$service, $repo] = makeBrandService();

    $brand = Brand::factory()->make(['is_active' => false]);
    $repo->expects('find')->with(1)->andReturn($brand);

    expect($service->isActive(1))->toBeFalse();
});

test('isActive returns false when brand not found', function () {
    [$service, $repo] = makeBrandService();

    $repo->expects('find')->with(999)->andReturn(null);

    expect($service->isActive(999))->toBeFalse();
});

// ──────────── getById (Public Contract) ────────────

test('getById returns BrandPublicData when found', function () {
    [$service, $repo] = makeBrandService();

    $brand = Brand::factory()->make([
        'id' => 1,
        'name' => 'سامسونگ',
        'english_name' => 'Samsung',
        'slug' => 'samsung',
        'is_active' => true,
        'is_featured' => false,
    ]);

    $repo->expects('find')->with(1)->andReturn($brand);

    $result = $service->getById(1);

    expect($result)->not->toBeNull()
        ->and($result->name)->toBe('سامسونگ')
        ->and($result->englishName)->toBe('Samsung')
        ->and($result->isActive)->toBeTrue();
});

test('getById returns null when not found', function () {
    [$service, $repo] = makeBrandService();

    $repo->expects('find')->with(999)->andReturn(null);

    $result = $service->getById(999);

    expect($result)->toBeNull();
});
```

### `modules/Categories/tests/Feature/Admin/CategoryDestroyTest.php`

```php
<?php

declare(strict_types=1);


use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Categories\Models\Category;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);


test('admin can soft delete category', function () {

    $category = Category::factory()->create();


    $response = $this->deleteJson('/api/admin/categories/' . $category->id);

    $response->assertNoContent();


    $this->assertSoftDeleted('categories', ['id' => $category->id]);

    expect(Category::withTrashed()->find($category->id))->not->toBeNull();

});



test('it returns 404 when deleting non-existent category', function () {

    $response = $this->deleteJson('/api/admin/categories/999999');

    $response->assertNotFound();

});


test('it returns 404 when deleting already trashed category',function (){

    $category=Category::factory()->trashed()->create();

    $response = $this->deleteJson('/api/admin/categories/' . $category->id);

    $response->assertNotFound();
});



```

### `modules/Categories/tests/Feature/Admin/CategoryShowTest.php`

```php
<?php

declare(strict_types=1);


use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Categories\Models\Category;
use Tests\TestCase;


uses(TestCase::class, RefreshDatabase::class);

test('admin can view a category by id', function () {

    $category = Category::factory()->create();

    $response = $this->getJson('/api/admin/categories/' . $category->id);

    $response->assertOk();


    $response->assertJsonPath('data.id', $category->id);
    $response->assertJsonPath('data.name', $category->name);
    $response->assertJsonPath('data.slug', $category->slug);
});


test('it returns 404 when category does not exists', function () {


    $response = $this->getJson('/api/admin/categories/123132112');

    $response->assertNotFound();
});


test('it returns 404 for soft deleted category', function () {

    $category = Category::factory()->trashed()->create();

    $response = $this->getJson('/api/admin/categories/' . $category->id);

    $response->assertNotFound();
});
```

### `modules/Categories/tests/Feature/Admin/CategorySpecification.php`

```php
<?php

declare(strict_types=1);


```

### `modules/Categories/tests/Feature/Admin/CategorySpecification/AttachSpecificationTest.php`

```php
<?php

declare(strict_types=1);


use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Categories\Models\Category;
use Modules\Categories\Models\Specification;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);


/**
 * @return array<Category,Specification>
 */

function addCategoryAndSpecification(): array
{

    $category = Category::factory()->create();

    $spec = Specification::factory()->create();

    return [$category, $spec];
}


test('it attaches a specifications with all fields', function () {

    [$category, $spec] = addCategoryAndSpecification();

    $response = $this->postJson("/api/admin/categories/{$category->id}/specifications", [
        'specification_id' => $spec->id,
        'type' => 'multi_select',
        'is_required' => true,
        'is_filterable' => true,
        'is_important' => true,
        'position' => 5,
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('category_specification', ['id' => $spec->id]);

});


test('persistent a specification with only required fields', function () {

    [$category, $spec] = addCategoryAndSpecification();

    $response = $this->postJson("/api/admin/categories/{$category->id}/specifications", [
        'specification_id' => $spec->id,
        'type' => 'free',
    ]);

    $response->assertCreated();
});


test('persists pivot data correctly in database', function () {
    [$category, $spec] = addCategoryAndSpecification();
    $this->postJson(
        "/api/admin/categories/{$category->id}/specifications",
        [
            'specification_id' => $spec->id,
            'type' => 'select',
            'is_filterable' => true,
            'position' => 3,
        ]
    );

    $this->assertDatabaseHas('category_specification', [
        'category_id' => $category->id,
        'specification_id' => $spec->id,
        'type' => 'select',
        'is_filterable' => true,
        'position' => 3,
    ]);
});


test('uses default position when not provided', function () {
    [$category, $spec] = addCategoryAndSpecification();
    $this->postJson(
        "/api/admin/categories/{$category->id}/specifications",
        [
            'specification_id' => $spec->id,
            'type' => 'select',
        ]
    );

    $this->assertDatabaseHas('category_specification', [
        'category_id' => $category->id,
        'specification_id' => $spec->id,
        'position' => 0,
    ]);
});

test('fails when specification_id is missing', function () {

    [$category] = addCategoryAndSpecification();

    $response = $this->postJson("/api/admin/categories/{$category->id}/specifications", [
        [
            'type' => 'select',
        ]
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['specification_id']);
});


test('fails when type is invalid', function () {
    [$category, $spec] = addCategoryAndSpecification();

    $response = $this->postJson("/api/admin/categories/{$category->id}/specifications", [
        'specification_id' => $spec->id,
        'type' => 'invalid_type',
    ]);


    $response->assertUnprocessable();

    $response->assertJsonValidationErrors(['type']);
});



test('fails when specification_id does not exits',function (){

    [$category] = addCategoryAndSpecification();

    $response = $this->postJson(
        "/api/admin/categories/{$category->id}/specifications",
        [
            'specification_id' => 999999,
            'type' => 'select',
        ]
    );

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['specification_id']);

});



test('fails when specification is already attached', function () {
    [$category,$spec] = addCategoryAndSpecification();

    $category->specifications()->attach($spec->id,[
        'type' => 'select',
    ]);

    $response = $this->postJson(
        "/api/admin/categories/{$category->id}/specifications",
        [
            'specification_id' => $spec->id,
            'type' => 'select',
        ]
    );

    $response->assertUnprocessable();
});



test('fails when free type is set as filterable', function () {

    [$category,$spec] = addCategoryAndSpecification();


    $response=$this->postJson("/api/admin/categories/{$category->id}/specifications",[
        'specification_id' => $spec->id,
        'type' => 'free',
        'is_filterable' => true,
    ]);


    $response->assertUnprocessable();
});

```

### `modules/Categories/tests/Feature/Admin/CategorySpecification/BulkAttachTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Categories\Models\Category;
use Modules\Categories\Models\Specification;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

// ──────────── Happy Paths ────────────

test('attaches multiple specifications successfully', function () {
    $category = Category::factory()->create();
    $spec1 = Specification::factory()->create();
    $spec2 = Specification::factory()->create();
    $spec3 = Specification::factory()->create();

    $response = $this->postJson(
        "/api/admin/categories/{$category->id}/specifications/bulk",
        [
            'specifications' => [
                ['specification_id' => $spec1->id, 'type' => 'select', 'is_filterable' => true],
                ['specification_id' => $spec2->id, 'type' => 'free'],
                ['specification_id' => $spec3->id, 'type' => 'multi_select', 'position' => 5],
            ],
        ]
    );

    $response->assertCreated();
    $response->assertJsonPath('count', 3);
});

test('persists all pivot data correctly', function () {
    $category = Category::factory()->create();
    $spec1 = Specification::factory()->create();
    $spec2 = Specification::factory()->create();

    $this->postJson(
        "/api/admin/categories/{$category->id}/specifications/bulk",
        [
            'specifications' => [
                ['specification_id' => $spec1->id, 'type' => 'select', 'position' => 1],
                ['specification_id' => $spec2->id, 'type' => 'free', 'position' => 2],
            ],
        ]
    );

    $this->assertDatabaseHas('category_specification', [
        'category_id' => $category->id,
        'specification_id' => $spec1->id,
        'type' => 'select',
        'position' => 1,
    ]);

    $this->assertDatabaseHas('category_specification', [
        'category_id' => $category->id,
        'specification_id' => $spec2->id,
        'type' => 'free',
        'position' => 2,
    ]);
});

// ──────────── Form Request Errors ────────────

test('fails when specifications array is missing', function () {
    $category = Category::factory()->create();

    $response = $this->postJson(
        "/api/admin/categories/{$category->id}/specifications/bulk",
        []
    );

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['specifications']);
});

test('fails when specifications array is empty', function () {
    $category = Category::factory()->create();

    $response = $this->postJson(
        "/api/admin/categories/{$category->id}/specifications/bulk",
        ['specifications' => []]
    );

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['specifications']);
});

test('fails when item is missing specification_id', function () {
    $category = Category::factory()->create();

    $response = $this->postJson(
        "/api/admin/categories/{$category->id}/specifications/bulk",
        [
            'specifications' => [
                ['type' => 'select'],
            ],
        ]
    );

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['specifications.0.specification_id']);
});

test('fails when item has invalid type', function () {
    $category = Category::factory()->create();
    $spec = Specification::factory()->create();

    $response = $this->postJson(
        "/api/admin/categories/{$category->id}/specifications/bulk",
        [
            'specifications' => [
                ['specification_id' => $spec->id, 'type' => 'invalid_type'],
            ],
        ]
    );

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['specifications.0.type']);
});

test('fails when array exceeds max size', function () {
    $category = Category::factory()->create();
    $spec = Specification::factory()->create();

    $items = array_fill(0, 101, [
        'specification_id' => $spec->id,
        'type' => 'select',
    ]);

    $response = $this->postJson(
        "/api/admin/categories/{$category->id}/specifications/bulk",
        ['specifications' => $items]
    );

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['specifications']);
});

// ──────────── Business Rule Violations ────────────

test('fails when input contains duplicate specification_ids', function () {
    $category = Category::factory()->create();
    $spec = Specification::factory()->create();

    $response = $this->postJson(
        "/api/admin/categories/{$category->id}/specifications/bulk",
        [
            'specifications' => [
                ['specification_id' => $spec->id, 'type' => 'select'],
                ['specification_id' => $spec->id, 'type' => 'free'],
            ],
        ]
    );

    $response->assertUnprocessable();
});

test('fails when one item is already attached', function () {
    $category = Category::factory()->create();
    $spec1 = Specification::factory()->create();
    $spec2 = Specification::factory()->create();

    // قبلاً attach کن
    $category->specifications()->attach($spec1->id, ['type' => 'select']);

    // حالا bulk attach که شامل spec1 ه
    $response = $this->postJson(
        "/api/admin/categories/{$category->id}/specifications/bulk",
        [
            'specifications' => [
                ['specification_id' => $spec1->id, 'type' => 'select'],
                ['specification_id' => $spec2->id, 'type' => 'free'],
            ],
        ]
    );

    $response->assertUnprocessable();
});

test('fails when item has free type with filterable true', function () {
    $category = Category::factory()->create();
    $spec1 = Specification::factory()->create();
    $spec2 = Specification::factory()->create();

    $response = $this->postJson(
        "/api/admin/categories/{$category->id}/specifications/bulk",
        [
            'specifications' => [
                ['specification_id' => $spec1->id, 'type' => 'select'],
                ['specification_id' => $spec2->id, 'type' => 'free', 'is_filterable' => true],
            ],
        ]
    );

    $response->assertUnprocessable();
});

// ──────────── Atomicity ────────────

test('rolls back entire bulk if one item fails', function () {
    $category = Category::factory()->create();
    $spec1 = Specification::factory()->create();
    $spec2 = Specification::factory()->create();
    $spec3 = Specification::factory()->create();

    // قبلاً spec2 رو attach کن
    $category->specifications()->attach($spec2->id, ['type' => 'select']);

    // bulk attach که شامل spec2 ه (که قبلاً attach شده)
    $this->postJson(
        "/api/admin/categories/{$category->id}/specifications/bulk",
        [
            'specifications' => [
                ['specification_id' => $spec1->id, 'type' => 'select'],
                ['specification_id' => $spec2->id, 'type' => 'free'],  // ← این fail می‌شه
                ['specification_id' => $spec3->id, 'type' => 'multi_select'],
            ],
        ]
    );

    // spec1 و spec3 نباید attach شده باشن (rollback)
    $this->assertDatabaseMissing('category_specification', [
        'category_id' => $category->id,
        'specification_id' => $spec1->id,
    ]);

    $this->assertDatabaseMissing('category_specification', [
        'category_id' => $category->id,
        'specification_id' => $spec3->id,
    ]);

    // ولی spec2 (که قبلاً attach شده بود) باید بمونه
    $this->assertDatabaseHas('category_specification', [
        'category_id' => $category->id,
        'specification_id' => $spec2->id,
    ]);
});
```

### `modules/Categories/tests/Feature/Admin/CategorySpecification/BulkUpdateTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Categories\Models\Category;
use Modules\Categories\Models\Specification;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

// ──────────── Happy Paths ────────────

test('updates multiple specifications successfully', function () {
    $category = Category::factory()->create();
    $spec1 = Specification::factory()->create();
    $spec2 = Specification::factory()->create();

    $category->specifications()->attach($spec1->id, ['type' => 'select', 'position' => 1]);
    $category->specifications()->attach($spec2->id, ['type' => 'free', 'position' => 2]);

    $response = $this->patchJson(
        "/api/admin/categories/{$category->id}/specifications/bulk",
        [
            'specifications' => [
                ['specification_id' => $spec1->id, 'position' => 10],
                ['specification_id' => $spec2->id, 'position' => 20],
            ],
        ]
    );

    $response->assertOk();
    $response->assertJsonPath('count', 2);
});

test('persists all changes to database', function () {
    $category = Category::factory()->create();
    $spec1 = Specification::factory()->create();
    $spec2 = Specification::factory()->create();

    $category->specifications()->attach($spec1->id, ['type' => 'select', 'position' => 1]);
    $category->specifications()->attach($spec2->id, ['type' => 'free', 'position' => 2]);

    $this->patchJson(
        "/api/admin/categories/{$category->id}/specifications/bulk",
        [
            'specifications' => [
                ['specification_id' => $spec1->id, 'position' => 99, 'is_important' => true],
                ['specification_id' => $spec2->id, 'is_required' => true],
            ],
        ]
    );

    $this->assertDatabaseHas('category_specification', [
        'category_id' => $category->id,
        'specification_id' => $spec1->id,
        'position' => 99,
        'is_important' => 1,
    ]);

    $this->assertDatabaseHas('category_specification', [
        'category_id' => $category->id,
        'specification_id' => $spec2->id,
        'is_required' => 1,
    ]);
});

test('updates only specified fields', function () {
    $category = Category::factory()->create();
    $spec = Specification::factory()->create();

    $category->specifications()->attach($spec->id, [
        'type' => 'select',
        'is_filterable' => true,
        'position' => 5,
    ]);

    $this->patchJson(
        "/api/admin/categories/{$category->id}/specifications/bulk",
        [
            'specifications' => [
                ['specification_id' => $spec->id, 'position' => 99],
            ],
        ]
    );

    // position عوض شده
    // ولی type و is_filterable دست نخوردن
    $this->assertDatabaseHas('category_specification', [
        'category_id' => $category->id,
        'specification_id' => $spec->id,
        'type' => 'select',
        'is_filterable' => 1,
        'position' => 99,
    ]);
});

// ──────────── Form Request Errors ────────────

test('fails when specifications array is missing', function () {
    $category = Category::factory()->create();

    $response = $this->patchJson(
        "/api/admin/categories/{$category->id}/specifications/bulk",
        []
    );

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['specifications']);
});

test('fails when item is missing specification_id', function () {
    $category = Category::factory()->create();

    $response = $this->patchJson(
        "/api/admin/categories/{$category->id}/specifications/bulk",
        [
            'specifications' => [
                ['position' => 5],
            ],
        ]
    );

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['specifications.0.specification_id']);
});

test('fails when item has invalid type', function () {
    $category = Category::factory()->create();
    $spec = Specification::factory()->create();

    $category->specifications()->attach($spec->id, ['type' => 'select']);

    $response = $this->patchJson(
        "/api/admin/categories/{$category->id}/specifications/bulk",
        [
            'specifications' => [
                ['specification_id' => $spec->id, 'type' => 'invalid_type'],
            ],
        ]
    );

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['specifications.0.type']);
});

// ──────────── Business Rule Violations ────────────

test('fails when input contains duplicates', function () {
    $category = Category::factory()->create();
    $spec = Specification::factory()->create();

    $category->specifications()->attach($spec->id, ['type' => 'select']);

    $response = $this->patchJson(
        "/api/admin/categories/{$category->id}/specifications/bulk",
        [
            'specifications' => [
                ['specification_id' => $spec->id, 'position' => 5],
                ['specification_id' => $spec->id, 'position' => 10],
            ],
        ]
    );

    $response->assertUnprocessable();
});

test('fails when item is not attached', function () {
    $category = Category::factory()->create();
    $spec = Specification::factory()->create();

    // spec attach نشده

    $response = $this->patchJson(
        "/api/admin/categories/{$category->id}/specifications/bulk",
        [
            'specifications' => [
                ['specification_id' => $spec->id, 'position' => 5],
            ],
        ]
    );

    $response->assertUnprocessable();
});

test('fails when business rule is violated', function () {
    $category = Category::factory()->create();
    $spec = Specification::factory()->create();

    $category->specifications()->attach($spec->id, [
        'type' => 'free',
        'is_filterable' => false,
    ]);

    // می‌خوام is_filterable=true کنم با type=free
    $response = $this->patchJson(
        "/api/admin/categories/{$category->id}/specifications/bulk",
        [
            'specifications' => [
                ['specification_id' => $spec->id, 'is_filterable' => true],
            ],
        ]
    );

    $response->assertUnprocessable();
});

// ──────────── Atomicity ────────────

test('rolls back all changes if one item fails', function () {
    $category = Category::factory()->create();
    $spec1 = Specification::factory()->create();
    $spec2 = Specification::factory()->create();
    $spec3 = Specification::factory()->create();

    $category->specifications()->attach($spec1->id, ['type' => 'select', 'position' => 1]);
    $category->specifications()->attach($spec2->id, ['type' => 'free', 'position' => 2]);
    // spec3 attach نشده

    $this->patchJson(
        "/api/admin/categories/{$category->id}/specifications/bulk",
        [
            'specifications' => [
                ['specification_id' => $spec1->id, 'position' => 99],
                ['specification_id' => $spec2->id, 'position' => 88],
                ['specification_id' => $spec3->id, 'position' => 77],  // ← fail چون attach نشده
            ],
        ]
    );

    // هیچ کدوم نباید عوض شده باشن
    $this->assertDatabaseHas('category_specification', [
        'category_id' => $category->id,
        'specification_id' => $spec1->id,
        'position' => 1,  // ← مقدار اولیه
    ]);

    $this->assertDatabaseHas('category_specification', [
        'category_id' => $category->id,
        'specification_id' => $spec2->id,
        'position' => 2,  // ← مقدار اولیه
    ]);
});

// ──────────── Edge Cases ────────────

test('items with no changes are no-op', function () {
    $category = Category::factory()->create();
    $spec = Specification::factory()->create();

    $category->specifications()->attach($spec->id, [
        'type' => 'select',
        'position' => 5,
    ]);

    // فقط specification_id فرستاده — هیچ تغییری نخواسته
    $response = $this->patchJson(
        "/api/admin/categories/{$category->id}/specifications/bulk",
        [
            'specifications' => [
                ['specification_id' => $spec->id],
            ],
        ]
    );

    $response->assertOk();

    // داده اصلی باید دست نخورده باشه
    $this->assertDatabaseHas('category_specification', [
        'category_id' => $category->id,
        'specification_id' => $spec->id,
        'type' => 'select',
        'position' => 5,
    ]);
});
```

### `modules/Categories/tests/Feature/Admin/CategorySpecification/DetachSpecificationTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Categories\Models\Category;
use Modules\Categories\Models\Specification;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('detaches a specification successfully', function () {
    $category = Category::factory()->create();
    $spec = Specification::factory()->create();

    $category->specifications()->attach($spec->id, [
        'type' => 'select',
    ]);

    $response = $this->deleteJson(
        "/api/admin/categories/{$category->id}/specifications/{$spec->id}"
    );

    $response->assertNoContent();
});

test('removes pivot row from database', function () {
    $category = Category::factory()->create();
    $spec = Specification::factory()->create();

    $category->specifications()->attach($spec->id, [
        'type' => 'select',
    ]);

    $this->deleteJson(
        "/api/admin/categories/{$category->id}/specifications/{$spec->id}"
    );

    $this->assertDatabaseMissing('category_specification', [
        'category_id' => $category->id,
        'specification_id' => $spec->id,
    ]);
});

test('fails when specification is not attached', function () {
    $category = Category::factory()->create();
    $spec = Specification::factory()->create();

    $response = $this->deleteJson(
        "/api/admin/categories/{$category->id}/specifications/{$spec->id}"
    );

    $response->assertNotFound();
});

test('fails when category does not exist', function () {
    $spec = Specification::factory()->create();

    $response = $this->deleteJson(
        "/api/admin/categories/999999/specifications/{$spec->id}"
    );

    $response->assertNotFound();
});

test('does not affect other categories with same specification', function () {
    $mobile = Category::factory()->create();
    $tablet = Category::factory()->create();
    $spec = Specification::factory()->create();

    $mobile->specifications()->attach($spec->id, ['type' => 'select']);
    $tablet->specifications()->attach($spec->id, ['type' => 'multi_select']);

    $this->deleteJson(
        "/api/admin/categories/{$mobile->id}/specifications/{$spec->id}"
    );

    // mobile باید خالی باشه
    $this->assertDatabaseMissing('category_specification', [
        'category_id' => $mobile->id,
        'specification_id' => $spec->id,
    ]);

    // tablet نباید تأثیر بگیره
    $this->assertDatabaseHas('category_specification', [
        'category_id' => $tablet->id,
        'specification_id' => $spec->id,
        'type' => 'multi_select',
    ]);
});
```

### `modules/Categories/tests/Feature/Admin/CategorySpecification/SyncSpecificationsTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Categories\Models\Category;
use Modules\Categories\Models\Specification;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

// ──────────── Happy Paths ────────────

test('syncs specifications to empty category', function () {
    $category = Category::factory()->create();
    $spec1 = Specification::factory()->create();
    $spec2 = Specification::factory()->create();

    $response = $this->putJson(
        "/api/admin/categories/{$category->id}/specifications/sync",
        [
            'specifications' => [
                ['specification_id' => $spec1->id, 'type' => 'select'],
                ['specification_id' => $spec2->id, 'type' => 'free'],
            ],
        ]
    );

    $response->assertOk();
    $response->assertJsonPath('count', 2);

    expect($category->refresh()->specifications)->toHaveCount(2);
});

test('syncs replaces existing specifications correctly', function () {
    $category = Category::factory()->create();
    $spec1 = Specification::factory()->create();
    $spec2 = Specification::factory()->create();
    $spec3 = Specification::factory()->create();
    $spec4 = Specification::factory()->create();

    // قبل از sync: 1, 2, 3
    $category->specifications()->attach($spec1->id, ['type' => 'select']);
    $category->specifications()->attach($spec2->id, ['type' => 'free']);
    $category->specifications()->attach($spec3->id, ['type' => 'multi_select']);

    // sync با: 2, 4
    $this->putJson(
        "/api/admin/categories/{$category->id}/specifications/sync",
        [
            'specifications' => [
                ['specification_id' => $spec2->id, 'type' => 'select'],
                ['specification_id' => $spec4->id, 'type' => 'multi_select'],
            ],
        ]
    );

    // فقط 2 و 4 باید بمونن
    $attached = $category->refresh()->specifications->pluck('id')->all();
    expect($attached)->toEqualCanonicalizing([$spec2->id, $spec4->id]);
});

test('detaches specifications not in sync list', function () {
    $category = Category::factory()->create();
    $spec1 = Specification::factory()->create();
    $spec2 = Specification::factory()->create();

    $category->specifications()->attach($spec1->id, ['type' => 'select']);
    $category->specifications()->attach($spec2->id, ['type' => 'free']);

    $this->putJson(
        "/api/admin/categories/{$category->id}/specifications/sync",
        [
            'specifications' => [
                ['specification_id' => $spec1->id, 'type' => 'select'],
            ],
        ]
    );

    // spec1 باید بمونه
    $this->assertDatabaseHas('category_specification', [
        'category_id' => $category->id,
        'specification_id' => $spec1->id,
    ]);

    // spec2 باید پاک شده باشه
    $this->assertDatabaseMissing('category_specification', [
        'category_id' => $category->id,
        'specification_id' => $spec2->id,
    ]);
});

test('updates pivot data of existing specifications', function () {
    $category = Category::factory()->create();
    $spec = Specification::factory()->create();

    // قبل: type=select, position=1
    $category->specifications()->attach($spec->id, [
        'type' => 'select',
        'position' => 1,
    ]);

    // sync با همون spec ولی type و position متفاوت
    $this->putJson(
        "/api/admin/categories/{$category->id}/specifications/sync",
        [
            'specifications' => [
                [
                    'specification_id' => $spec->id,
                    'type' => 'multi_select',
                    'position' => 99,
                ],
            ],
        ]
    );

    // pivot باید update شده باشه
    $this->assertDatabaseHas('category_specification', [
        'category_id' => $category->id,
        'specification_id' => $spec->id,
        'type' => 'multi_select',
        'position' => 99,
    ]);
});

// ──────────── Form Request Errors ────────────

test('fails when specifications is empty', function () {
    $category = Category::factory()->create();

    $response = $this->putJson(
        "/api/admin/categories/{$category->id}/specifications/sync",
        ['specifications' => []]
    );

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['specifications']);
});

test('fails when item has invalid type', function () {
    $category = Category::factory()->create();
    $spec = Specification::factory()->create();

    $response = $this->putJson(
        "/api/admin/categories/{$category->id}/specifications/sync",
        [
            'specifications' => [
                ['specification_id' => $spec->id, 'type' => 'invalid_type'],
            ],
        ]
    );

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['specifications.0.type']);
});

// ──────────── Business Rule Violations ────────────

test('fails when input contains duplicates', function () {
    $category = Category::factory()->create();
    $spec = Specification::factory()->create();

    $response = $this->putJson(
        "/api/admin/categories/{$category->id}/specifications/sync",
        [
            'specifications' => [
                ['specification_id' => $spec->id, 'type' => 'select'],
                ['specification_id' => $spec->id, 'type' => 'free'],
            ],
        ]
    );

    $response->assertUnprocessable();
});

test('fails when item has free type with filterable true', function () {
    $category = Category::factory()->create();
    $spec = Specification::factory()->create();

    $response = $this->putJson(
        "/api/admin/categories/{$category->id}/specifications/sync",
        [
            'specifications' => [
                [
                    'specification_id' => $spec->id,
                    'type' => 'free',
                    'is_filterable' => true,
                ],
            ],
        ]
    );

    $response->assertUnprocessable();
});

// ──────────── Atomicity ────────────

test('rolls back all changes if one item fails', function () {
    $category = Category::factory()->create();
    $spec1 = Specification::factory()->create();
    $spec2 = Specification::factory()->create();

    // قبل: spec1 با position=1
    $category->specifications()->attach($spec1->id, [
        'type' => 'select',
        'position' => 1,
    ]);

    // sync که شامل invalid item ه
    $this->putJson(
        "/api/admin/categories/{$category->id}/specifications/sync",
        [
            'specifications' => [
                ['specification_id' => $spec1->id, 'type' => 'multi_select', 'position' => 99],
                ['specification_id' => $spec2->id, 'type' => 'free', 'is_filterable' => true],  // ← fail
            ],
        ]
    );

    // spec1 نباید update شده باشه (rollback)
    $this->assertDatabaseHas('category_specification', [
        'category_id' => $category->id,
        'specification_id' => $spec1->id,
        'type' => 'select',
        'position' => 1,
    ]);

    // spec2 نباید attach شده باشه
    $this->assertDatabaseMissing('category_specification', [
        'category_id' => $category->id,
        'specification_id' => $spec2->id,
    ]);
});

// ──────────── Idempotency ────────────

test('running sync twice produces same result', function () {
    $category = Category::factory()->create();
    $spec1 = Specification::factory()->create();
    $spec2 = Specification::factory()->create();

    $payload = [
        'specifications' => [
            ['specification_id' => $spec1->id, 'type' => 'select', 'position' => 5],
            ['specification_id' => $spec2->id, 'type' => 'free', 'position' => 10],
        ],
    ];

    // اولین sync
    $this->putJson(
        "/api/admin/categories/{$category->id}/specifications/sync",
        $payload
    )->assertOk();

    $firstState = $category->refresh()->specifications->pluck('id')->all();

    // دومین sync با همون payload
    $this->putJson(
        "/api/admin/categories/{$category->id}/specifications/sync",
        $payload
    )->assertOk();

    $secondState = $category->refresh()->specifications->pluck('id')->all();

    // باید یکسان باشن
    expect($firstState)->toEqualCanonicalizing($secondState);

    // pivot data هم باید درست باشه
    $this->assertDatabaseHas('category_specification', [
        'category_id' => $category->id,
        'specification_id' => $spec1->id,
        'position' => 5,
    ]);

    $this->assertDatabaseHas('category_specification', [
        'category_id' => $category->id,
        'specification_id' => $spec2->id,
        'position' => 10,
    ]);
});
```

### `modules/Categories/tests/Feature/Admin/CategorySpecification/UpdatePivotTest.php`

```php
<?php

declare(strict_types=1);


use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Categories\Models\Category;
use Modules\Categories\Models\Specification;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);


test('updates a single field', function () {

    $category = Category::factory()->create();
    $spec = Specification::factory()->create();


    $category->specifications()->attach($spec->id, [
        'type' => 'select',
        'position' => 1
    ]);


    $position = 4;
    $response = $this->patchJson("/api/admin/categories/{$category->id}/specifications/{$spec->id}", [
        "position" => $position,
        'category_id' => $category->id,
        'specification_id' => $spec->id,
    ]);

    $response->assertOk();

    $this->assertDatabaseHas('category_specification', ['position' => $position]);
});


test('updates multiple fields', function () {


    $category = Category::factory()->create();

    $spec = Specification::factory()->create();

    $category->specifications()->attach($spec->id, [
        'type' => 'select',
        'position' => 1
    ]);

    $response = $this->patchJson("/api/admin/categories/{$category->id}/specifications/{$spec->id}", [
        'category_id' => $category->id,
        'specification_id' => $spec->id,
        'position' => 2,
        'is_important' => true,
        'is_required' => true,
    ]);

    $response->assertOk();

    $this->assertDatabaseHas('category_specification', ['position' => 2, 'is_important' => true, 'is_required' => true]);

});


test('persists changes to database', function () {

    $category = Category::factory()->create();
    $spec = Specification::factory()->create();

    $category->specifications()->attach($spec->id, [
        'type' => 'select',
        'position' => 1,
    ]);

    $this->patchJson(
        "/api/admin/categories/{$category->id}/specifications/{$spec->id}",
        [
            'position' => 99,
            'is_filterable' => true,
        ]
    );

    $this->assertDatabaseHas('category_specification', [
        'category_id' => $category->id,
        'specification_id' => $spec->id,
        'position' => 99,
        'is_filterable' => 1,
    ]);

});


// ──────────── Form Request Errors ────────────
test('fails when type is invalid', function () {

    $category = Category::factory()->create();

    $spec = Specification::factory()->create();

    $category->specifications()->attach($spec->id, ['type' => 'select']);

    $response = $this->patchJson(
        "/api/admin/categories/{$category->id}/specifications/{$spec->id}",
        ['type' => 'invalid_type']
    );

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['type']);
});


// ──────────── Business Rule Violations ────────────


test('fails when current type is free and trying to set filterable', function () {

    $category = Category::factory()->create();

    $spec = Specification::factory()->create();

    $category->specifications()->attach($spec->id, [
        'type' => 'free',
        'is_filterable' => false,
    ]);


    $response = $this->patchJson(
        "/api/admin/categories/{$category->id}/specifications/{$spec->id}",
        ['is_filterable' => true]
    );

    $response->assertUnprocessable();
});


test('fils when changing to free with filterable already true', function () {

    $category = Category::factory()->create();

    $spec = Specification::factory()->create();

    $category->specifications()->attach($spec->id, [
        'type' => 'select',
        'is_filterable' => true,
    ]);

    $response = $this->patchJson(
        "/api/admin/categories/{$category->id}/specifications/{$spec->id}",
        ['type' => 'free']
    );


    $response->assertUnprocessable();
});

// ──────────── Edge Cases ────────────

test('fails when specification is not attached', function () {

    $category = Category::factory()->create();

    $spec = Specification::factory()->create();

    $response = $this->patchJson(
        "/api/admin/categories/{$category->id}/specifications/{$spec->id}",
        ['position' => 5]
    );

    $response->assertNotFound();
});


test('allows empty payload as no-op', function () {

    $category = Category::factory()->create();

    $spec = Specification::factory()->create();


    $category->specifications()->attach($spec->id, [
        'type' => 'select',
        'position' => 5,
    ]);


    $response = $this->patchJson(
        "/api/admin/categories/{$category->id}/specifications/{$spec->id}",
        []
    );

    $response->assertOk();

    $this->assertDatabaseHas('category_specification', [
        'category_id' => $category->id,
        'specification_id' => $spec->id,
        'type' => 'select',
        'position' => 5,
    ]);
});


test('does not affect other categories pivot data', function () {

    $mobile = Category::factory()->create();
    $tablet = Category::factory()->create();
    $spec = Specification::factory()->create();

    $mobile->specifications()->attach($spec->id, [
        'type' => 'select',
        'position' => 1,
    ]);

    $tablet->specifications()->attach($spec->id, [
        'type' => 'multi_select',
        'position' => 2,
    ]);

    $this->patchJson(
        "/api/admin/categories/{$mobile->id}/specifications/{$spec->id}",
        ['position' => 99]
    );

    $this->assertDatabaseHas('category_specification', [
        'category_id' => $mobile->id,
        'specification_id' => $spec->id,
        'position' => 99,
    ]);

    $this->assertDatabaseHas('category_specification', [
        'category_id' => $tablet->id,
        'specification_id' => $spec->id,
        'position' => 2,
        'type' => 'multi_select',
    ]);





});
```

### `modules/Categories/tests/Feature/Admin/CategoryStoreTest.php`

```php
<?php

declare(strict_types=1);


use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Categories\Models\Category;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);


test('admin can create a category with valida data', function () {
    $payload = [
        'name' => 'الکترونیک',
        'english_name' => 'Electronics',
        'description' => 'محصولات الکترونیکی',
    ];


    $response = $this->postJson('/api/admin/categories', $payload);

    $response->assertCreated();

    // 2. response data
    $response->assertJsonPath('data.name', 'الکترونیک');
    $response->assertJsonPath('data.english_name', 'Electronics');
    $response->assertJsonPath('data.slug', 'electronics');

    $this->assertDatabaseHas('categories', [
        'name' => 'الکترونیک',
        'english_name' => 'Electronics',
        'slug' => 'electronics',
    ]);
});


test('it requires name field', function () {
    $response = $this->postJson('/api/admin/categories', [
        'english_name' => 'Electronics',
    ]);


    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name']);

    $this->assertDatabaseEmpty('categories');
});


test('it validates required fields', function (array $payload, array $errors) {
    $response = $this->postJson('/api/admin/categories', $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors($errors);
})->with([
    'missing name' => [
        ['english_name' => 'Electronics'],
        ['name']
    ],
    'missing english_name' => [
        ['name' => 'الکترونیک'],
        ['english_name']
    ],
    'empty name' => [
        ['name' => '', 'english_name' => 'Electronics'],
        ['name']
    ]
]);


test('it requires unique english_name', function () {
    Category::factory()->create([
        'english_name' => 'Electronics',
    ]);

    $response = $this->postJson('/api/admin/categories', [
        'name' => 'الکترونیک ۲',
        'english_name' => 'Electronics',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['english_name']);

    $this->assertDatabaseCount('categories', 1);
});
```

### `modules/Categories/tests/Feature/Admin/CategoryUpdateTest.php`

```php
<?php

declare(strict_types=1);


use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Categories\Models\Category;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);


test('admin can update a category', function () {


    $category = Category::factory()->create();

    $name = 'نام جدید';

    $response = $this->patch("/api/admin/categories/{$category->id}", [
        'name' => $name,
    ]);

    $response->assertOk();

    $response->assertJsonPath('data.name', 'نام جدید');


    $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => $name]);

});


test('it returns 404 when updating none-existent category', function () {


    $response = $this->patch("/api/admin/categories/99999", ['name' => 'test']);


    $response->assertNotFound();
});


test('it allows updating without changing english_name', function () {

    $category = Category::factory()->create(['english_name' => 'Electronics']);

    $response = $this->patch("/api/admin/categories/{$category->id}", [
        'english_name' => 'Electronics',
        'name' => 'نام جدید'
    ]);

    $response->assertOk();
});

test('it rejects update when english_name belongs to another category', function () {
    $category1 = Category::factory()->create(['english_name' => 'Electronics']);
    $category2 = Category::factory()->create(['english_name' => 'Books']);

    $response = $this->patchJson("/api/admin/categories/{$category2->id}", [
        'english_name' => 'Electronics',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['english_name']);
});












```

### `modules/Categories/tests/Feature/Admin/ListSpecificationsTest.php`

```php
<?php

declare(strict_types=1);


use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Categories\Models\Category;
use Modules\Categories\Models\Specification;
use Tests\TestCase;


uses(TestCase::class, RefreshDatabase::class);

test('returns empty list when category has no specifications', function () {

    $category = Category::factory()->create();

    $response = $this->getJson("/api/admin/categories/{$category->id}/specifications");

    $response->assertOk();
    $response->assertJsonCount(0, 'data');
});


test('lists all specifications attached to a category', function () {
    $category = Category::factory()->create();

    $spec1 = Specification::factory()->create();
    $spec2 = Specification::factory()->create();


    $category->specifications()->attach($spec1->id, [
        'type' => 'select',
        'is_filterable' => true,
        'position' => 1
    ]);


    $category->specifications()->attach($spec2->id, [
        'type' => 'free',
        'position' => 1
    ]);

    $response = $this->getJson("/api/admin/categories/{$category->id}/specifications");

    $response->assertOk();
    $response->assertJsonCount(2, 'data');

});


test('return specification with pivot data', function () {

    $category = Category::factory()->create();

    $spec = Specification::factory()->create();


    $category->specifications()->attach($spec->id, [
        'type' => 'multi_select',
        'is_required' => true,
        'is_filterable' => true,
        'is_important' => false,
        'position' => 5,
    ]);

    $response = $this->getJson("/api/admin/categories/{$category->id}/specifications");



    $response->assertOk();
    $response->assertJsonPath('data.0.id', $spec->id);
    $response->assertJsonPath('data.0.pivot.type', 'multi_select');
    $response->assertJsonPath('data.0.pivot.is_required', true);
    $response->assertJsonPath('data.0.pivot.is_filterable', true);
    $response->assertJsonPath('data.0.pivot.is_important', false);
    $response->assertJsonPath('data.0.pivot.position', 5);

});

it('returns 404 when category does not exist', function () {
    $response = $this->getJson('/api/admin/categories/999999/specifications');

    $response->assertNotFound();
});
```

### `modules/Categories/tests/Feature/Admin/SpecificationDestroyTest.php`

```php
<?php


declare(strict_types=1);


use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Categories\Models\Specification;
use Tests\TestCase;

uses(TestCase::class,RefreshDatabase::class);

test('admin can soft delete a specification', function () {

    $specification = Specification::factory()->create();

    $response = $this->deleteJson('/api/admin/specifications/' . $specification->id);


    $response->assertNoContent();


    $this->assertSoftDeleted('specifications', ['id' => $specification->id]);

    expect(Specification::withTrashed()->find($specification->id))->not->toBeNull();

});


test('it returns 404 when deleting none existent specification', function () {

    $response = $this->deleteJson('/api/admin/specifications/99999');

    $response->assertNotFound();
});


test('it returns 404 when deleting already trashed specification', function () {
    $specification = Specification::factory()->trashed()->create();

    $response = $this->deleteJson('/api/admin/specifications/' . $specification->id);

    $response->assertNotFound();
});
```

### `modules/Categories/tests/Feature/Admin/SpecificationIndexTest.php`

```php
<?php


declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Categories\Models\Specification;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);


test('admin can view empty list if specification', function () {

    $response = $this->getJson('/api/admin/specifications');

    $response->assertStatus(200);

    $response->assertJsonCount(0, 'data');
});


test('it returns paginated structure', function () {

    Specification::factory()->count(3)->create();

    $response = $this->getJson('/api/admin/specifications');

    $response->assertOk();
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'name', 'slug', 'unit', 'data_type'],
        ],
        'links' => ['first', 'last', 'prev', 'next'],
        'meta'  => ['current_page', 'per_page', 'total'],
    ]);
});

test('it returns only root specifications', function () {
    $parent = Specification::factory()->create();
    Specification::factory()->count(3)->create([
        'parent_id' => $parent->id,
    ]);

    $response = $this->getJson('/api/admin/specifications');

    $response->assertOk();

    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.id', $parent->id);
});




```

### `modules/Categories/tests/Feature/Admin/SpecificationShowTest.php`

```php
<?php

declare(strict_types=1);


use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Categories\Models\Specification;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);


test('admin can view a specification by id', function () {

    $specification = Specification::factory()->create();

    $response = $this->getJson('/api/admin/specifications/' . $specification->id);

    $response->assertStatus(200);
    $response->assertJsonPath('data.id',$specification->id);
    $response->assertJsonPath('data.name',$specification->name);
    $response->assertJsonPath('data.slug',$specification->slug);

});


test('it returns 404 when specification does not exits ', function () {

    $specification = Specification::factory()->trashed()->create();

    $response = $this->getJson('/api/admin/specifications/' . $specification->id);

    $response->assertNotFound();

    $this->assertSoftDeleted($specification, ['id' => $specification->id]);
});


```

### `modules/Categories/tests/Feature/Admin/SpecificationStoreTest.php`

```php
<?php

declare(strict_types=1);


use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Categories\Enums\DataType;
use Modules\Categories\Models\Specification;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);


test('admin can create a specification with valid data', function () {

    $payload = [
        'name' => 'اندازه صفحه نمایش',
        'slug' => 'screen-size',
        'unit' => 'اینچ',
        'data_type' => 'decimal',
        'description' => 'اندازه صفحه به اینچ',
    ];

    $response = $this->postJson('/api/admin/specifications', $payload);


    $response->assertCreated();

    $response->assertJsonPath('data.slug', 'screen-size');

    $this->assertDatabaseHas('specifications', [
        'name' => 'اندازه صفحه نمایش',
        'slug' => 'screen-size',
    ]);

});


test('it requires name field', function () {

    $response = $this->postJson('/api/admin/specifications', [
        'slug' => 'screen-size',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name']);

    $this->assertDatabaseEmpty('specifications');
});


test('it requires unique slug', function () {

    $specification = Specification::factory()->create(['slug' => 'screen-size']);

    $response = $this->postJson('/api/admin/specifications', [
        'name' => 'صحفه نمایش',
        'slug' => $specification->slug,
    ]);
    $response->assertStatus(422);

    $response->assertJsonValidationErrors(['slug']);
});


test('it rejects invalid data_type', function () {
    $response = $this->postJson('/api/admin/specifications', [
        'name' => 'اسم',
        'slug' => 'screen-size',
        'data_type' => 'json'
    ]);


    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['data_type']);
    $this->assertDatabaseEmpty('specifications');
});


test('it rejects nesting beyond one level', function () {

    $specification_root = Specification::factory()->create([
        'name' => 'صفحه نمایش',
        'slug' => 'screen-size',
    ]);

    $specification_child = Specification::factory()->create([
        'name' => 'رزولوشین',
        'slug' => 'resolution',
        'parent_id' => $specification_root->id,
    ]);


    $response = $this->postJson('/api/admin/specifications', [
        'name' => 'پیکسل',
        'slug' => 'pixel',
        'parent_id' => $specification_child->id,
    ]);


    $response->assertStatus(422);

    $response->assertJsonValidationErrors(['parent_id']);


});
```

### `modules/Categories/tests/Feature/Admin/SpecificationUpdateTest.php`

```php
<?php

declare(strict_types=1);


use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Categories\Models\Specification;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);


test('admin can update a specification', function () {

    $specification = Specification::factory()->create();

    $response = $this->patchJson('/api/admin/specifications/' . $specification->id, [
        'slug' => 'screen-size-2'
    ]);

    $response->assertOk();

    $response->assertJsonPath('data.slug', 'screen-size-2');

    $this->assertDatabaseHas('specifications',
        [
            'id' => $specification->id,
            'slug' => 'screen-size-2'
        ]);
});


test('it returns 404 when updating none-existent specification ', function () {

    $response = $this->patchJson('/api/admin/specifications/9999', [
        'name' => 'test'
    ]);

    $response->assertNotFound();
});


test('it allows updating without changing slug', function () {

    $specification = Specification::factory()->create(['slug' => 'screen-size']);


    $response = $this->patchJson('/api/admin/specifications/' . $specification->id, [
        'slug' => 'screen-size',
        'name' => 'نام جدید'
    ]);

    $response->assertOk();
});


test('it rejects update when slug belongs to another specification', function () {


    $specification1 = Specification::factory()->create(['slug' => 'screen-size']);

    $specification2 = Specification::factory()->create(['slug' => 'resolution']);


    $response = $this->patchJson('/api/admin/specifications/' . $specification2->id, [
        'slug' => 'screen-size',
    ]);


    $response->assertStatus(422);
});


test('it rejects setting parent_id to one of its own children', function () {

    $parent = Specification::factory()->create();


    $child = Specification::factory()->create(['parent_id' => $parent->id]);


    $response= $this->patchJson('/api/admin/specifications/' . $parent->id, [
            'parent_id' => $child->id,
    ]);

    $response->assertStatus(422);


});

test('it rejects setting specification as its own parent', function () {
    $specification = Specification::factory()->create();

    $response = $this->patchJson('/api/admin/specifications/' . $specification->id, [
        'parent_id' => $specification->id,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['parent_id']);
});
```

### `modules/Categories/tests/Feature/CategoryStoreTest.php`

```php
<?php

declare(strict_types=1);


use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Categories\Models\Category;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);


test('admin can create a category with valida data', function () {
    $payload = [
        'name' => 'الکترونیک',
        'english_name' => 'Electronics',
        'description' => 'محصولات الکترونیکی',
    ];


    $response = $this->postJson('/api/admin/categories', $payload);

    $response->assertCreated();

    // 2. response data
    $response->assertJsonPath('data.name', 'الکترونیک');
    $response->assertJsonPath('data.english_name', 'Electronics');
    $response->assertJsonPath('data.slug', 'electronics');

    $this->assertDatabaseHas('categories', [
        'name' => 'الکترونیک',
        'english_name' => 'Electronics',
        'slug' => 'electronics',
    ]);
});


test('it requires name field', function () {
    $response = $this->postJson('/api/admin/categories', [
        'english_name' => 'Electronics',
    ]);


    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name']);

    $this->assertDatabaseEmpty('categories');
});


test('it validates required fields', function (array $payload, array $errors) {
    $response = $this->postJson('/api/admin/categories', $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors($errors);
})->with([
    'missing name' => [
        ['english_name' => 'Electronics'],
        ['name']
    ],
    'missing english_name' => [
        ['name' => 'الکترونیک'],
        ['english_name']
    ],
    'empty name' => [
        ['name' => '', 'english_name' => 'Electronics'],
        ['name']
    ]
]);


test('it requires unique english_name', function () {
    Category::factory()->create([
        'english_name' => 'Electronics',
    ]);

    $response = $this->postJson('/api/admin/categories', [
        'name' => 'الکترونیک ۲',
        'english_name' => 'Electronics',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['english_name']);

    $this->assertDatabaseCount('categories', 1);
});


test('admin can view a category by id', function () {

    $category = Category::factory()->create();

    $response = $this->getJson('/api/admin/categories/' . $category->id);

    $response->assertOk();


    $response->assertJsonPath('data.id', $category->id);
    $response->assertJsonPath('data.name', $category->name);
    $response->assertJsonPath('data.slug', $category->slug);
});
```

### `modules/Categories/tests/Unit/CategorySlugTest.php`

```php
<?php

declare (strict_types=1);


use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Categories\Models\Category;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('it generates simple slug from english name', function () {

    $category = Category::factory()->create(['english_name' => 'Electronics']);

    expect($category->slug)->toBe('electronics');
});


test('it appends counter when slug already exists', function () {
    $category1 = Category::factory()->create(['english_name' => 'Electronics']);
    $category2 = Category::factory()->create(['english_name' => 'Electronics']);

    expect($category1->slug)->toBe('electronics')
        ->and($category2->slug)->toBe('electronics-1');
});

test('it appends counter when slug already exists three category', function () {


    $category1 = Category::factory()->create(['english_name' => 'Electronics']);
    $category2 = Category::factory()->create(['english_name' => 'Electronics']);
    $category3 = Category::factory()->create(['english_name' => 'Electronics']);
    expect($category3->slug)->toBe('electronics-2');


});

test('it respects manually provided slug', function () {

    Category::factory()->create([
        'english_name' => 'Electronics',
        'slug' => 'my-custom-slug',
    ]);

    $this->assertDatabaseHas('categories', [
        'english_name' => 'Electronics',
        'slug' => 'my-custom-slug',
    ]);

});


test('it converts english_name with various formats', function (string $input, string $expected) {

    $category = Category::factory()->create(['english_name' => $input]);

    expect($category->slug)->toBe($expected);
})->with([
    'multiple words' => ['Cool Cat Stuff', 'cool-cat-stuff'],
    'extra spaces'   => ['Hello   World',   'hello-world'],
    'mixed case'     => ['HelloWorld',      'helloworld'],
]);

```

### `modules/Categories/tests/Unit/Services/CategoryServiceTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Modules\Categories\Contracts\CategoryRepositoryContract;
use Modules\Categories\Models\Category;
use Modules\Categories\Services\CategoryService;
use Modules\Shared\Servies\File\FileService;

uses(\Tests\TestCase::class);

test('it uploads image when creating category with image', function () {
    $fileService = Mockery::mock(FileService::class);
    $repository  = Mockery::mock(CategoryRepositoryContract::class);

    // FileService انتظار داره upload صدا زده بشه
    $fileService->expects('upload')
        ->once()
        ->andReturn('uploads/test-image.jpg');

    // Repository انتظار داره create صدا زده بشه
    $repository->expects('create')
        ->once()
        ->andReturnUsing(function (array $data) {
            $category = new Category($data);
            $category->id = 1;
            return $category;
        });

    $service = new CategoryService($repository, $fileService);

    $image = UploadedFile::fake()->image('test.jpg');

    $category = $service->create(
        ['name' => 'Test', 'english_name' => 'Electronics'],
        $image
    );

    expect($category)->toBeInstanceOf(Category::class);
});

test('it does not call upload when no image is provided', function () {
    $fileService = Mockery::mock(FileService::class);
    $repository  = Mockery::mock(CategoryRepositoryContract::class);

    // FileService نباید upload صدا زده بشه
    $fileService->shouldNotReceive('upload');

    // Repository انتظار داره create صدا زده بشه
    $repository->expects('create')
        ->once()
        ->andReturnUsing(function (array $data) {
            $category = new Category($data);
            $category->id = 1;
            return $category;
        });

    $service = new CategoryService($repository, $fileService);

    $category = $service->create([
        'name'         => 'Test',
        'english_name' => 'Electronics',
    ]);

    expect($category)->toBeInstanceOf(Category::class);
});
```

### `modules/Colors/tests/Feature/Admin/ColorActivateTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Colors\Events\ColorActivated;
use Modules\Colors\Events\ColorDeactivated;
use Modules\Colors\Models\Color;
use Tests\TestCase;

uses(RefreshDatabase::class, TestCase::class);

// ──────────── Activate ────────────

test('admin can activate an inactive color', function () {
    Event::fake();

    $color = Color::factory()->inactive()->create();

    $response = $this->patchJson("/api/admin/colors/{$color->slug}/activate");

    $response->assertOk();
    $response->assertJsonPath('data.is_active', true);

    $this->assertDatabaseHas('colors', [
        'id'        => $color->id,
        'is_active' => true,
    ]);

    Event::assertDispatched(ColorActivated::class);
});

test('activating an already active color is idempotent', function () {
    $color = Color::factory()->active()->create();

    $response = $this->patchJson("/api/admin/colors/{$color->slug}/activate");

    $response->assertOk();
    $response->assertJsonPath('data.is_active', true);

    $this->assertDatabaseHas('colors', [
        'id'        => $color->id,
        'is_active' => true,
    ]);
});

test('activate returns 404 for non-existent slug', function () {
    $response = $this->patchJson('/api/admin/colors/non-existent/activate');

    $response->assertStatus(404);
});

// ──────────── Deactivate ────────────

test('admin can deactivate an active color', function () {
    Event::fake();

    $color = Color::factory()->active()->create();

    $response = $this->patchJson("/api/admin/colors/{$color->slug}/deactivate");

    $response->assertOk();
    $response->assertJsonPath('data.is_active', false);

    $this->assertDatabaseHas('colors', [
        'id'        => $color->id,
        'is_active' => false,
    ]);

    Event::assertDispatched(ColorDeactivated::class);
});

test('deactivating an already inactive color is idempotent', function () {
    $color = Color::factory()->inactive()->create();

    $response = $this->patchJson("/api/admin/colors/{$color->slug}/deactivate");

    $response->assertOk();
    $response->assertJsonPath('data.is_active', false);
});

test('deactivate returns 404 for non-existent slug', function () {
    $response = $this->patchJson('/api/admin/colors/non-existent/deactivate');

    $response->assertStatus(404);
});

// ──────────── Toggle Workflow ────────────

test('activate then deactivate toggles correctly', function () {
    $color = Color::factory()->inactive()->create();

    // اول activate
    $this->patchJson("/api/admin/colors/{$color->slug}/activate");
    $color->refresh();
    expect($color->is_active)->toBeTrue();

    // سپس deactivate
    $this->patchJson("/api/admin/colors/{$color->slug}/deactivate");
    $color->refresh();
    expect($color->is_active)->toBeFalse();
});
```

### `modules/Colors/tests/Feature/Admin/ColorDestroyTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Colors\Events\ColorDeleted;
use Modules\Colors\Models\Color;
use Tests\TestCase;

uses(RefreshDatabase::class, TestCase::class);

// ──────────── Happy Path ────────────

test('admin can soft delete a color', function () {
    Event::fake();

    $color = Color::factory()->create();
    $colorId = $color->id;

    $response = $this->deleteJson("/api/admin/colors/{$color->slug}");

    $response->assertNoContent();  // 204

    // soft deleted - معمولی query پیدا نکنه
    expect(Color::find($colorId))->toBeNull();

    // ولی با withTrashed پیدا کنه
    expect(Color::withTrashed()->find($colorId))->not->toBeNull();

    Event::assertDispatched(
        ColorDeleted::class,
        fn ($event) => $event->colorId === $colorId,
    );
});

test('deleted color no longer appears in index', function () {
    Color::factory()->count(2)->create();
    $deleting = Color::factory()->create();

    $this->deleteJson("/api/admin/colors/{$deleting->slug}");

    $response = $this->getJson('/api/admin/colors');

    $response->assertOk();
    $response->assertJsonCount(2, 'data');  // فقط ۲ تا
});

test('deleted color cannot be retrieved by show', function () {
    $color = Color::factory()->create();

    $this->deleteJson("/api/admin/colors/{$color->slug}");

    $response = $this->getJson("/api/admin/colors/{$color->slug}");
    $response->assertStatus(404);
});

// ──────────── 404 ────────────

test('returns 404 for non-existent slug', function () {
    $response = $this->deleteJson('/api/admin/colors/non-existent');

    $response->assertStatus(404);
});

test('returns 404 when deleting already deleted color', function () {
    $color = Color::factory()->create();

    // اول delete کن
    $this->deleteJson("/api/admin/colors/{$color->slug}");

    // دوباره delete کن
    $response = $this->deleteJson("/api/admin/colors/{$color->slug}");

    $response->assertStatus(404);
});
```

### `modules/Colors/tests/Feature/Admin/ColorIndexTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Colors\Models\Color;
use Tests\TestCase;

uses(RefreshDatabase::class, TestCase::class);

// ──────────── Happy Path ────────────

test('returns empty list when no colors exist', function () {
    $response = $this->getJson('/api/admin/colors');

    $response->assertOk();
    $response->assertJsonCount(0, 'data');
});

test('returns all colors with pagination structure', function () {
    Color::factory()->count(3)->create();

    $response = $this->getJson('/api/admin/colors');

    $response->assertOk();
    $response->assertJsonCount(3, 'data');
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id',
                'name',
                'english_name',
                'slug',
                'code',
                'is_active',
                'sort_order',
                'created_at',
                'updated_at',
            ],
        ],
        'links',
        'meta',
    ]);
});

test('respects per_page query parameter', function () {
    Color::factory()->count(20)->create();

    $response = $this->getJson('/api/admin/colors?per_page=5');

    $response->assertOk();
    $response->assertJsonCount(5, 'data');
    $response->assertJsonPath('meta.per_page', 5);
});

test('returns paginated results with default per_page=15', function () {
    Color::factory()->count(20)->create();

    $response = $this->getJson('/api/admin/colors');

    $response->assertOk();
    $response->assertJsonCount(15, 'data');
    $response->assertJsonPath('meta.per_page', 15);
    $response->assertJsonPath('meta.total', 20);
});

// ──────────── Ordering ────────────

test('returns colors ordered by sort_order then name', function () {
    Color::factory()->create(['name' => 'C', 'sort_order' => 2]);
    Color::factory()->create(['name' => 'A', 'sort_order' => 1]);
    Color::factory()->create(['name' => 'B', 'sort_order' => 1]);

    $response = $this->getJson('/api/admin/colors');

    $response->assertOk();

    $data = $response->json('data');

    expect($data[0]['name'])->toBe('A')
        ->and($data[1]['name'])->toBe('B')
        ->and($data[2]['name'])->toBe('C');  // sort_order=1, name=A
    // sort_order=1, name=B
    // sort_order=2
});

// ──────────── Includes Both Active and Inactive ────────────

test('includes both active and inactive colors', function () {
    Color::factory()->count(2)->active()->create();
    Color::factory()->count(3)->inactive()->create();

    $response = $this->getJson('/api/admin/colors');

    $response->assertOk();
    $response->assertJsonCount(5, 'data');  // both
});

// ──────────── Soft Deleted Excluded ────────────

test('excludes soft deleted colors', function () {
    Color::factory()->count(3)->create();
    $deleted = Color::factory()->create();
    $deleted->delete();

    $response = $this->getJson('/api/admin/colors');

    $response->assertOk();
    $response->assertJsonCount(3, 'data');
});
```

### `modules/Colors/tests/Feature/Admin/ColorSearchTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Colors\Models\Color;
use Tests\TestCase;

uses(RefreshDatabase::class, TestCase::class);

// ──────────── Match Different Fields ────────────

test('search matches by name', function () {
    Color::factory()->create(['name' => 'قرمز']);
    Color::factory()->create(['name' => 'آبی']);

    $response = $this->getJson('/api/admin/colors/search?q=قرمز');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.name', 'قرمز');
});

test('search matches by english_name', function () {
    Color::factory()->create(['english_name' => 'Red']);
    Color::factory()->create(['english_name' => 'Blue']);

    $response = $this->getJson('/api/admin/colors/search?q=Red');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

test('search matches by code', function () {
    Color::factory()->create(['code' => '#FF0000']);
    Color::factory()->create(['code' => '#00FF00']);

    $response = $this->getJson('/api/admin/colors/search?q=FF0000');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

// ──────────── Partial Match ────────────

test('search uses partial matching', function () {
    Color::factory()->create(['english_name' => 'Dark Red']);
    Color::factory()->create(['english_name' => 'Light Red']);
    Color::factory()->create(['english_name' => 'Blue']);

    $response = $this->getJson('/api/admin/colors/search?q=Red');

    $response->assertOk();
    $response->assertJsonCount(2, 'data');  // هر دو Red
});

// ──────────── Empty/No Match ────────────

test('search with no match returns empty', function () {
    Color::factory()->count(3)->create([
        'english_name' => 'Red',
    ]);

    $response = $this->getJson('/api/admin/colors/search?q=NonExistent');

    $response->assertOk();
    $response->assertJsonCount(0, 'data');
});

test('empty search query returns all colors', function () {
    Color::factory()->count(3)->create();

    $response = $this->getJson('/api/admin/colors/search?q=');

    $response->assertOk();
    $response->assertJsonCount(3, 'data');
});

// ──────────── Pagination ────────────

test('search results are paginated', function () {
    // ۲۰ تا color با english_name 'Red' بسازیم
    for ($i = 1; $i <= 20; $i++) {
        Color::factory()->create([
            'english_name' => "Red Variant {$i}",
            'slug'         => "red-{$i}",
        ]);
    }

    $response = $this->getJson('/api/admin/colors/search?q=Red&per_page=5');

    $response->assertOk();
    $response->assertJsonCount(5, 'data');
    $response->assertJsonPath('meta.per_page', 5);
    $response->assertJsonPath('meta.total', 20);
});

// ──────────── Includes Both Active and Inactive ────────────

test('search includes both active and inactive', function () {
    Color::factory()->active()->create(['name' => 'قرمز فعال']);
    Color::factory()->inactive()->create(['name' => 'قرمز غیرفعال']);

    $response = $this->getJson('/api/admin/colors/search?q=قرمز');

    $response->assertOk();
    $response->assertJsonCount(2, 'data');
});
```

### `modules/Colors/tests/Feature/Admin/ColorShowTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Colors\Models\Color;
use Tests\TestCase;

uses(RefreshDatabase::class, TestCase::class);

// ──────────── Happy Path ────────────

test('returns color by slug', function () {
    $color = Color::factory()->create([
        'name'         => 'قرمز',
        'english_name' => 'Red',
        'slug'         => 'red',
        'code'         => '#FF0000',
    ]);

    $response = $this->getJson("/api/admin/colors/{$color->slug}");

    $response->assertOk();
    $response->assertJsonPath('data.id', $color->id);
    $response->assertJsonPath('data.name', 'قرمز');
    $response->assertJsonPath('data.english_name', 'Red');
    $response->assertJsonPath('data.slug', 'red');
    $response->assertJsonPath('data.code', '#FF0000');
});

test('returns complete resource structure', function () {
    $color = Color::factory()->create();

    $response = $this->getJson("/api/admin/colors/{$color->slug}");

    $response->assertOk();
    $response->assertJsonStructure([
        'data' => [
            'id',
            'name',
            'english_name',
            'slug',
            'code',
            'is_active',
            'sort_order',
            'created_at',
            'updated_at',
        ],
    ]);
});

// ──────────── 404 ────────────

test('returns 404 for non-existent slug', function () {
    $response = $this->getJson('/api/admin/colors/non-existent-slug');

    $response->assertStatus(404);
});

test('returns 404 for soft deleted color', function () {
    $color = Color::factory()->create();
    $color->delete();

    $response = $this->getJson("/api/admin/colors/{$color->slug}");

    $response->assertStatus(404);
});

// ──────────── Type Casts ────────────

test('boolean fields are cast correctly in response', function () {
    $color = Color::factory()->create([
        'is_active' => true,
    ]);

    $response = $this->getJson("/api/admin/colors/{$color->slug}");

    $response->assertOk();

    $isActive = $response->json('data.is_active');

    expect($isActive)->toBeBool()
        ->and($isActive)->toBeTrue();
});

test('integer fields are cast correctly in response', function () {
    $color = Color::factory()->create([
        'sort_order' => 5,
    ]);

    $response = $this->getJson("/api/admin/colors/{$color->slug}");

    $response->assertOk();

    $sortOrder = $response->json('data.sort_order');

    expect($sortOrder)->toBeInt()
        ->and($sortOrder)->toBe(5);
});
```

### `modules/Colors/tests/Feature/Admin/ColorUpdateTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Colors\Events\ColorUpdated;
use Modules\Colors\Models\Color;
use Tests\TestCase;

uses(RefreshDatabase::class, TestCase::class);

// ──────────── Happy Path ────────────

test('admin can update color name', function () {
    Event::fake();

    $color = Color::factory()->create(['name' => 'قدیمی']);

    $response = $this->patchJson("/api/admin/colors/{$color->slug}", [
        'name' => 'جدید',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.name', 'جدید');

    $this->assertDatabaseHas('colors', [
        'id'   => $color->id,
        'name' => 'جدید',
    ]);

    Event::assertDispatched(ColorUpdated::class);
});

test('admin can update multiple fields at once', function () {
    $color = Color::factory()->create();

    $response = $this->patchJson("/api/admin/colors/{$color->slug}", [
        'name'       => 'قرمز جدید',
        'code'       => '#FF1234',
        'is_active'  => false,
        'sort_order' => 10,
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.name', 'قرمز جدید');
    $response->assertJsonPath('data.code', '#FF1234');
    $response->assertJsonPath('data.is_active', false);
    $response->assertJsonPath('data.sort_order', 10);
});

test('partial update only modifies sent fields', function () {
    $color = Color::factory()->create([
        'name'       => 'اصلی',
        'code'       => '#FF0000',
        'sort_order' => 5,
    ]);

    $response = $this->patchJson("/api/admin/colors/{$color->slug}", [
        'name' => 'جدید',
        // code و sort_order نمی‌فرستیم
    ]);

    $response->assertOk();

    $this->assertDatabaseHas('colors', [
        'id'         => $color->id,
        'name'       => 'جدید',
        'code'       => '#FF0000',  // بدون تغییر
        'sort_order' => 5,           // بدون تغییر
    ]);
});

test('empty body returns color without changes', function () {
    $color = Color::factory()->create(['name' => 'اصلی']);

    $response = $this->patchJson("/api/admin/colors/{$color->slug}", []);

    $response->assertOk();
    $response->assertJsonPath('data.name', 'اصلی');
});

// ──────────── Validation ────────────

test('it rejects invalid slug format', function () {
    $color = Color::factory()->create();

    $response = $this->patchJson("/api/admin/colors/{$color->slug}", [
        'slug' => 'Invalid Slug!',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['slug']);
});

test('it rejects invalid code format', function () {
    $color = Color::factory()->create();

    $response = $this->patchJson("/api/admin/colors/{$color->slug}", [
        'code' => 'invalid',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['code']);
});

test('it rejects empty name when sent', function () {
    $color = Color::factory()->create();

    $response = $this->patchJson("/api/admin/colors/{$color->slug}", [
        'name' => '',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name']);
});

// ──────────── Unique with Ignore ────────────

test('allows updating with same slug as own', function () {
    $color = Color::factory()->create(['slug' => 'red']);

    // ست کردن slug به همون مقدار قبلی نباید خطا بده
    $response = $this->patchJson("/api/admin/colors/{$color->slug}", [
        'slug' => 'red',
        'name' => 'تغییر نام',
    ]);

    $response->assertOk();
});

test('allows updating with same code as own', function () {
    $color = Color::factory()->create(['code' => '#FF0000']);

    $response = $this->patchJson("/api/admin/colors/{$color->slug}", [
        'code' => '#FF0000',  // همون قبلی
        'name' => 'تغییر نام',
    ]);

    $response->assertOk();
});

test('rejects duplicate slug from another color', function () {
    Color::factory()->create(['slug' => 'red']);
    $other = Color::factory()->create(['slug' => 'blue']);

    $response = $this->patchJson("/api/admin/colors/{$other->slug}", [
        'slug' => 'red',  // متعلق به یه color دیگه
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['slug']);
});

test('rejects duplicate code from another color', function () {
    Color::factory()->create(['code' => '#FF0000']);
    $other = Color::factory()->create(['code' => '#00FF00']);

    $response = $this->patchJson("/api/admin/colors/{$other->slug}", [
        'code' => '#FF0000',  // متعلق به یه color دیگه
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['code']);
});

// ──────────── 404 ────────────

test('returns 404 for non-existent slug', function () {
    $response = $this->patchJson('/api/admin/colors/non-existent', [
        'name' => 'تست',
    ]);

    $response->assertStatus(404);
});
```

### `modules/Colors/tests/Feature/Admin/StoreColorTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Colors\Events\ColorCreated;
use Modules\Colors\Models\Color;
use Tests\TestCase;

uses(RefreshDatabase::class, TestCase::class);

// ──────────── Happy Path ────────────

test('admin can create a color', function () {
    Event::fake();

    $payload = [
        'name'         => 'قرمز',
        'english_name' => 'Red',
        'slug'         => 'red',
        'code'         => '#FF0000',
        'is_active'    => true,
        'sort_order'   => 5,
    ];

    $response = $this->postJson('/api/admin/colors', $payload);

    $response->assertStatus(201);
    $response->assertJsonPath('data.name', 'قرمز');
    $response->assertJsonPath('data.code', '#FF0000');
    $response->assertJsonPath('data.is_active', true);

    $this->assertDatabaseHas('colors', [
        'name'  => 'قرمز',
        'slug'  => 'red',
        'code'  => '#FF0000',
    ]);

    Event::assertDispatched(ColorCreated::class);
});

test('default values are applied when optional fields are missing', function () {
    $payload = [
        'name'         => 'آبی',
        'english_name' => 'Blue',
        'slug'         => 'blue',
        'code'         => '#0000FF',
        // is_active و sort_order نیست
    ];

    $response = $this->postJson('/api/admin/colors', $payload);

    $response->assertStatus(201);
    $response->assertJsonPath('data.is_active', true);   // default
    $response->assertJsonPath('data.sort_order', 0);     // default
});

// ──────────── Validation Errors ────────────

test('it requires name', function () {
    $response = $this->postJson('/api/admin/colors', [
        'english_name' => 'Red',
        'slug'         => 'red',
        'code'         => '#FF0000',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name']);
});

test('it requires english_name', function () {
    $response = $this->postJson('/api/admin/colors', [
        'name' => 'قرمز',
        'slug' => 'red',
        'code' => '#FF0000',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['english_name']);
});

test('it requires slug', function () {
    $response = $this->postJson('/api/admin/colors', [
        'name'         => 'قرمز',
        'english_name' => 'Red',
        'code'         => '#FF0000',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['slug']);
});

test('it requires code', function () {
    $response = $this->postJson('/api/admin/colors', [
        'name'         => 'قرمز',
        'english_name' => 'Red',
        'slug'         => 'red',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['code']);
});

test('it rejects invalid slug format', function () {
    $response = $this->postJson('/api/admin/colors', [
        'name'         => 'قرمز',
        'english_name' => 'Red',
        'slug'         => 'Red Color!',  // ← spaces and special chars
        'code'         => '#FF0000',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['slug']);
});

test('it rejects invalid code format', function () {
    $response = $this->postJson('/api/admin/colors', [
        'name'         => 'قرمز',
        'english_name' => 'Red',
        'slug'         => 'red',
        'code'         => 'red',  // ← not hex
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['code']);
});

test('it rejects code without hash prefix', function () {
    $response = $this->postJson('/api/admin/colors', [
        'name'         => 'قرمز',
        'english_name' => 'Red',
        'slug'         => 'red',
        'code'         => 'FF0000',  // ← بدون #
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['code']);
});

test('it rejects duplicate slug', function () {
    Color::factory()->create(['slug' => 'red']);

    $response = $this->postJson('/api/admin/colors', [
        'name'         => 'قرمز جدید',
        'english_name' => 'New Red',
        'slug'         => 'red',  // ← duplicate
        'code'         => '#FF1234',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['slug']);
});

test('it rejects duplicate code', function () {
    Color::factory()->create(['code' => '#FF0000']);

    $response = $this->postJson('/api/admin/colors', [
        'name'         => 'قرمز جدید',
        'english_name' => 'New Red',
        'slug'         => 'new-red',
        'code'         => '#FF0000',  // ← duplicate
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['code']);
});
```

### `modules/Colors/tests/Unit/ColorModelTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Colors\Models\Color;
use Tests\TestCase;

uses(RefreshDatabase::class, TestCase::class);


test('factory creates color with valid data', function () {

    $color = Color::factory()->create();

    expect($color)->toBeInstanceOf(Color::class)
        ->and($color->id)->toBeInt()
        ->and($color->name)->not->toBeEmpty()
        ->and($color->code)->not->toBeEmpty()
        ->and($color->slug)->not->toBeEmpty();

});


test('it active cast to boolean', function () {

    $color = Color::factory()->create(['is_active' => true]);

    expect($color->is_active)->toBeBool()
        ->and($color->is_active)->toBeTrue();
});


test('sort_order is cast to integer', function () {
    $color = Color::factory()->create(['sort_order' => 5]);
    expect($color->sort_order)->toBeInt()
        ->and($color->sort_order)->toBe(5);
});


// ──────────── Scopes ────────────


test('scope active returns only active colors', function () {
    Color::factory()->count(3)->active()->create();
    Color::factory()->count(5)->inactive()->create();
    $active = Color::active()->get();

    expect($active)->toHaveCount(3);
});


test('scope order returns colors by sort_order then name', function () {

    Color::factory()->create(['name' => 'C', 'sort_order' => 2]);
    Color::factory()->create(['name' => 'A', 'sort_order' => 1]);
    Color::factory()->create(['name' => 'B', 'sort_order' => 1]);

    $ordered = Color::ordered()->get();
    expect($ordered[0]->name)->toBe('A')
        ->and($ordered[1]->name)->toBe('B')
        ->and($ordered[2]->name)->toBe('C');
});

test('scope search matches name', function () {

    Color::factory()->create(['name' => 'قرمز']);
    Color::factory()->create(['name' => 'آبی']);

    $results = Color::search('قرمز')->get();


    expect($results)->toHaveCount(1)
        ->and($results->first()->name)->toBe('قرمز');
});


test('scope search matches english_name', function () {

    Color::factory()->create(['english_name' => 'Red']);
    Color::factory()->create(['english_name' => 'Blue']);

    $results = Color::search('Red')->get();

    expect($results)->toHaveCount(1);
});

test('scope search matches code', function () {
    Color::factory()->create(['code' => '#FF0000']);
    Color::factory()->create(['code' => '#00FF00']);

    $results = Color::search('FF0000')->get();

    expect($results)->toHaveCount(1);
});


// ──────────── Soft Delete ────────────

test('color uses soft delete', function () {
    $color = Color::factory()->create();
    $colorId = $color->id;

    $color->delete();

    // معمولی query پیدا نکنه
    expect(Color::find($colorId))->toBeNull()
        ->and(Color::withTrashed()->find($colorId))->not->toBeNull();

    // ولی با withTrashed پیدا می‌کنه
});

test('soft deleted color has deleted_at timestamp', function () {
    $color = Color::factory()->create();

    $color->delete();
    $color->refresh();

    expect($color->deleted_at)->not->toBeNull();
});



// ──────────── Route Key ────────────

test('route key is slug not id', function () {
    $color = Color::factory()->create();

    expect($color->getRouteKeyName())->toBe('slug');
});









```

### `modules/Colors/tests/Unit/ColorServiceTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Modules\Colors\Contracts\ColorRepositoryContract;
use Modules\Colors\DataTransferObjects\CreateColorData;
use Modules\Colors\DataTransferObjects\UpdateColorData;
use Modules\Colors\Events\ColorActivated;
use Modules\Colors\Events\ColorCreated;
use Modules\Colors\Events\ColorDeactivated;
use Modules\Colors\Events\ColorDeleted;
use Modules\Colors\Events\ColorUpdated;
use Modules\Colors\Exceptions\ColorNotFoundException;
use Modules\Colors\Models\Color;
use Modules\Colors\Services\ColorService;
use Tests\TestCase;

uses(TestCase::class);

// ──────────── Helper ────────────

function makeColorService(): array
{
    $repository = Mockery::mock(ColorRepositoryContract::class);
    $service = new ColorService($repository);

    return [$service, $repository];
}

// ──────────── findById ────────────

test('findById returns color when exists', function () {
    [$service, $repo] = makeColorService();

    $color = Color::factory()->make(['id' => 1]);
    $repo->expects('find')->with(1)->andReturn($color);

    $result = $service->findById(1);

    expect($result)->toBeInstanceOf(Color::class)
        ->and($result->id)->toBe(1);
});

test('findById throws exception when not found', function () {
    [$service, $repo] = makeColorService();

    $repo->expects('find')->with(999)->andReturn(null);

    expect(fn () => $service->findById(999))
        ->toThrow(ColorNotFoundException::class);
});

// ──────────── create ────────────

test('create persists color and dispatches event', function () {
    Event::fake();

    [$service, $repo] = makeColorService();

    $color = Color::factory()->make(['id' => 1, 'name' => 'قرمز']);

    $repo->expects('create')
        ->with(Mockery::on(fn ($data) => $data['name'] === 'قرمز'))
        ->andReturn($color);

    $data = new CreateColorData(
        name: 'قرمز',
        englishName: 'Red',
        slug: 'red',
        code: '#FF0000',
    );

    $result = $service->create($data);

    expect($result)->toBeInstanceOf(Color::class)
        ->and($result->name)->toBe('قرمز');

    Event::assertDispatched(ColorCreated::class);
});

// ──────────── update ────────────

test('update returns color without query when no changes', function () {
    [$service, $repo] = makeColorService();

    $color = Color::factory()->make(['id' => 1]);

    $repo->shouldNotReceive('update');
    $repo->expects('find')->with(1)->andReturn($color);

    $emptyData = new UpdateColorData();
    $result = $service->update(1, $emptyData);

    expect($result->id)->toBe(1);
});

test('update modifies color and dispatches event', function () {
    Event::fake();

    [$service, $repo] = makeColorService();

    $updated = Color::factory()->make(['id' => 1, 'name' => 'جدید']);

    $repo->expects('update')
        ->with(1, Mockery::on(fn ($data) => $data['name'] === 'جدید'))
        ->andReturn($updated);

    $data = new UpdateColorData(name: 'جدید');
    $result = $service->update(1, $data);

    expect($result->name)->toBe('جدید');

    Event::assertDispatched(ColorUpdated::class);
});

// ──────────── delete ────────────

test('delete removes color and dispatches event', function () {
    Event::fake();

    [$service, $repo] = makeColorService();

    $color = Color::factory()->make(['id' => 1]);

    $repo->expects('find')->with(1)->andReturn($color);
    $repo->expects('delete')->with(1)->andReturn(true);

    $result = $service->delete(1);

    expect($result)->toBeTrue();
    Event::assertDispatched(
        ColorDeleted::class,
        fn ($event) => $event->colorId === 1,
    );
});

test('delete throws exception when color not found', function () {
    [$service, $repo] = makeColorService();

    $repo->expects('find')->with(999)->andReturn(null);
    $repo->shouldNotReceive('delete');

    expect(fn () => $service->delete(999))
        ->toThrow(ColorNotFoundException::class);
});

// ──────────── activate / deactivate ────────────

test('activate updates is_active and dispatches event', function () {
    Event::fake();

    [$service, $repo] = makeColorService();

    $color = Color::factory()->make(['id' => 1, 'is_active' => true]);

    $repo->expects('update')
        ->with(1, ['is_active' => true])
        ->andReturn($color);

    $result = $service->activate(1);

    expect($result->is_active)->toBeTrue();
    Event::assertDispatched(ColorActivated::class);
});

test('deactivate updates is_active and dispatches event', function () {
    Event::fake();

    [$service, $repo] = makeColorService();

    $color = Color::factory()->make(['id' => 1, 'is_active' => false]);

    $repo->expects('update')
        ->with(1, ['is_active' => false])
        ->andReturn($color);

    $result = $service->deactivate(1);

    expect($result->is_active)->toBeFalse();
    Event::assertDispatched(ColorDeactivated::class);
});

// ──────────── exists / isActive ────────────

test('exists returns true when color exists', function () {
    [$service, $repo] = makeColorService();

    $repo->expects('exists')->with(1)->andReturn(true);

    expect($service->exists(1))->toBeTrue();
});

test('isActive returns true for active color', function () {
    [$service, $repo] = makeColorService();

    $color = Color::factory()->make(['is_active' => true]);
    $repo->expects('find')->with(1)->andReturn($color);

    expect($service->isActive(1))->toBeTrue();
});

test('isActive returns false for inactive color', function () {
    [$service, $repo] = makeColorService();

    $color = Color::factory()->make(['is_active' => false]);
    $repo->expects('find')->with(1)->andReturn($color);

    expect($service->isActive(1))->toBeFalse();
});

test('isActive returns false when color not found', function () {
    [$service, $repo] = makeColorService();

    $repo->expects('find')->with(999)->andReturn(null);

    expect($service->isActive(999))->toBeFalse();
});

// ──────────── getById (Public Contract) ────────────

test('getById returns ColorPublicData when found', function () {
    [$service, $repo] = makeColorService();

    $color = Color::factory()->make([
        'id' => 1,
        'name' => 'قرمز',
        'english_name' => 'Red',
        'slug' => 'red',
        'code' => '#FF0000',
        'is_active' => true,
    ]);

    $repo->expects('find')->with(1)->andReturn($color);

    $result = $service->getById(1);

    expect($result)->not->toBeNull()
        ->and($result->name)->toBe('قرمز')
        ->and($result->englishName)->toBe('Red')
        ->and($result->code)->toBe('#FF0000')
        ->and($result->isActive)->toBeTrue();
});

test('getById returns null when not found', function () {
    [$service, $repo] = makeColorService();

    $repo->expects('find')->with(999)->andReturn(null);

    $result = $service->getById(999);

    expect($result)->toBeNull();
});

// ──────────── areAllActive ────────────

test('areAllActive returns true when empty array', function () {
    [$service, $repo] = makeColorService();

    // empty → return true بدون query
    $repo->shouldNotReceive('countActiveByIds');

    expect($service->areAllActive([]))->toBeTrue();
});

test('areAllActive returns true when all active', function () {
    [$service, $repo] = makeColorService();

    $repo->expects('countActiveByIds')
        ->with([1, 2, 3])
        ->andReturn(3);

    expect($service->areAllActive([1, 2, 3]))->toBeTrue();
});

test('areAllActive returns false when some inactive', function () {
    [$service, $repo] = makeColorService();

    $repo->expects('countActiveByIds')
        ->with([1, 2, 3])
        ->andReturn(2);  // فقط 2 از 3

    expect($service->areAllActive([1, 2, 3]))->toBeFalse();
});
```

### `modules/Products/tests/Feature/Admin/ProductAdminTest.php`

```php
<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Brands\Models\Brand;
use Modules\Products\Events\ProductArchived;
use Modules\Products\Events\ProductCreated;
use Modules\Products\Events\ProductDeleted;
use Modules\Products\Events\ProductPublished;
use Modules\Products\Models\Product;
use Tests\TestCase;

uses(RefreshDatabase::class, TestCase::class);

// ============================================================
// STORE
// ============================================================

test('admin can create a product', function () {
    Event::fake();
    $user = User::factory()->create();

    $payload = [
        'title'    => 'گوشی Samsung S23',
        'en_title' => 'Samsung S23',
        'slug'     => 'samsung-s23',
        'user_id'  => $user->id,
    ];

    $response = $this->postJson('/api/admin/products', $payload);

    $response->assertStatus(201);
    $response->assertJsonPath('data.title', 'گوشی Samsung S23');
    $response->assertJsonPath('data.slug', 'samsung-s23');

    $this->assertDatabaseHas('products', [
        'slug' => 'samsung-s23',
    ]);

    Event::assertDispatched(ProductCreated::class);
});

test('store: it requires title', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/admin/products', [
        'en_title' => 'Test',
        'slug'     => 'test',
        'user_id'  => $user->id,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['title']);
});

test('store: rejects invalid slug format', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/admin/products', [
        'title'    => 'تست',
        'en_title' => 'Test',
        'slug'     => 'Invalid Slug!',
        'user_id'  => $user->id,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['slug']);
});

test('store: rejects duplicate slug', function () {
    $user = User::factory()->create();
    Product::factory()->create(['slug' => 'existing']);

    $response = $this->postJson('/api/admin/products', [
        'title'    => 'تست',
        'en_title' => 'Test',
        'slug'     => 'existing',
        'user_id'  => $user->id,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['slug']);
});

test('store: rejects invalid user_id', function () {
    $response = $this->postJson('/api/admin/products', [
        'title'    => 'تست',
        'en_title' => 'Test',
        'slug'     => 'test',
        'user_id'  => 99999,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['user_id']);
});

test('store: defaults to draft status', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/admin/products', [
        'title'    => 'تست',
        'en_title' => 'Test',
        'slug'     => 'test-draft',
        'user_id'  => $user->id,
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('data.status', 'draft');
});

// ============================================================
// INDEX
// ============================================================

test('returns empty list when no products', function () {
    $response = $this->getJson('/api/admin/products');

    $response->assertOk();
    $response->assertJsonCount(0, 'data');
});

test('returns paginated list', function () {
    Product::factory()->count(20)->create();

    $response = $this->getJson('/api/admin/products?per_page=5');

    $response->assertOk();
    $response->assertJsonCount(5, 'data');
    $response->assertJsonPath('meta.total', 20);
});

test('index excludes soft deleted', function () {
    Product::factory()->count(3)->create();
    $deleted = Product::factory()->create();
    $deleted->delete();

    $response = $this->getJson('/api/admin/products');

    $response->assertJsonCount(3, 'data');
});

// ============================================================
// SHOW
// ============================================================

test('returns product by slug', function () {
    $p = Product::factory()->create([
        'slug'  => 'test-product',
        'title' => 'محصول تست',
    ]);

    $response = $this->getJson("/api/admin/products/{$p->slug}");

    $response->assertOk();
    $response->assertJsonPath('data.id', $p->id);
    $response->assertJsonPath('data.title', 'محصول تست');
});

test('show returns 404 for non-existent slug', function () {
    $response = $this->getJson('/api/admin/products/non-existent');

    $response->assertStatus(404);
});

test('show includes computed fields', function () {
    $p = Product::factory()->create([
        'view_count'      => 100,
        'fake_view_count' => 50,
    ]);

    $response = $this->getJson("/api/admin/products/{$p->slug}");

    $response->assertOk();
    $response->assertJsonPath('data.total_views', 150);
});

test('show includes seo grouped object', function () {
    $p = Product::factory()->create([
        'meta_title'       => 'SEO Title',
        'meta_description' => 'SEO Desc',
    ]);

    $response = $this->getJson("/api/admin/products/{$p->slug}");

    $response->assertOk();
    $response->assertJsonPath('data.seo.title', 'SEO Title');
    $response->assertJsonPath('data.seo.description', 'SEO Desc');
});

// ============================================================
// UPDATE
// ============================================================

test('admin can update product', function () {
    $p = Product::factory()->create(['title' => 'قدیمی']);

    $response = $this->patchJson("/api/admin/products/{$p->slug}", [
        'title' => 'جدید',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.title', 'جدید');
});

test('update: partial update only modifies sent fields', function () {
    $p = Product::factory()->create([
        'title'     => 'اصلی',
        'en_title'  => 'Original',
    ]);

    $this->patchJson("/api/admin/products/{$p->slug}", [
        'title' => 'جدید',
    ]);

    $this->assertDatabaseHas('products', [
        'id'       => $p->id,
        'title'    => 'جدید',
        'en_title' => 'Original',
    ]);
});

test('update: allows same slug as own', function () {
    $p = Product::factory()->create(['slug' => 'test-1']);

    $response = $this->patchJson("/api/admin/products/{$p->slug}", [
        'slug'  => 'test-1',
        'title' => 'تست',
    ]);

    $response->assertOk();
});

test('update: rejects duplicate slug from other product', function () {
    Product::factory()->create(['slug' => 'existing']);
    $other = Product::factory()->create(['slug' => 'other']);

    $response = $this->patchJson("/api/admin/products/{$other->slug}", [
        'slug' => 'existing',
    ]);

    $response->assertStatus(422);
});

// ============================================================
// DESTROY
// ============================================================

test('admin can soft delete product', function () {
    Event::fake();

    $p = Product::factory()->create();
    $id = $p->id;

    $response = $this->deleteJson("/api/admin/products/{$p->slug}");

    $response->assertNoContent();
    expect(Product::find($id))->toBeNull();
    expect(Product::withTrashed()->find($id))->not->toBeNull();

    Event::assertDispatched(
        ProductDeleted::class,
        fn ($e) => $e->productId === $id,
    );
});

// ============================================================
// STATE TRANSITIONS
// ============================================================

test('admin can publish a draft product', function () {
    Event::fake();
    $p = Product::factory()->draft()->create();

    $response = $this->patchJson("/api/admin/products/{$p->slug}/publish");

    $response->assertOk();
    $response->assertJsonPath('data.status', 'published');

    $this->assertDatabaseHas('products', [
        'id'     => $p->id,
        'status' => 'published',
    ]);

    Event::assertDispatched(ProductPublished::class);
});

test('admin can archive a published product', function () {
    Event::fake();
    $p = Product::factory()->published()->create();

    $response = $this->patchJson("/api/admin/products/{$p->slug}/archive");

    $response->assertOk();
    $response->assertJsonPath('data.status', 'archived');

    Event::assertDispatched(ProductArchived::class);
});

test('admin can activate inactive product', function () {
    $p = Product::factory()->inactive()->create();

    $response = $this->patchJson("/api/admin/products/{$p->slug}/activate");

    $response->assertOk();
    $response->assertJsonPath('data.is_active', true);
});

test('admin can feature a product', function () {
    $p = Product::factory()->create(['is_featured' => false]);

    $response = $this->patchJson("/api/admin/products/{$p->slug}/feature");

    $response->assertOk();
    $response->assertJsonPath('data.is_featured', true);
});

test('admin can unfeature a product', function () {
    $p = Product::factory()->featured()->create();

    $response = $this->patchJson("/api/admin/products/{$p->slug}/unfeature");

    $response->assertOk();
    $response->assertJsonPath('data.is_featured', false);
});

// ============================================================
// SEARCH
// ============================================================

test('search matches by title', function () {
    Product::factory()->create(['title' => 'گوشی Samsung', 'en_title' => 'unique-1']);
    Product::factory()->create(['title' => 'لپتاپ Apple', 'en_title' => 'unique-2']);

    $response = $this->getJson('/api/admin/products/search?q=Samsung');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

test('empty search returns all', function () {
    Product::factory()->count(3)->create();

    $response = $this->getJson('/api/admin/products/search?q=');

    $response->assertOk();
    $response->assertJsonCount(3, 'data');
});

// ============================================================
// BY BRAND
// ============================================================

test('by-brand returns products of specific brand', function () {
    $brand1 = \Modules\Brands\Models\Brand::factory()->create();
    $brand2 = \Modules\Brands\Models\Brand::factory()->create();

    Product::factory()->count(3)->create(['brand_id' => $brand1->id]);
    Product::factory()->count(2)->create(['brand_id' => $brand2->id]);

    $response = $this->getJson("/api/admin/products/by-brand/{$brand1->id}");

    $response->assertOk();
    $response->assertJsonCount(3, 'data');
});
test('by-brand returns empty when no products for brand', function () {
    $brand=Brand::factory()->create();
    Product::factory()->count(3)->create(['brand_id' => $brand->id]);

    $response = $this->getJson('/api/admin/products/by-brand/999');

    $response->assertOk();
    $response->assertJsonCount(0, 'data');
});
```

### `modules/Products/tests/Unit/ProductModelTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Brands\Models\Brand;
use Modules\Products\Models\Product;
use Tests\TestCase;

uses(RefreshDatabase::class, TestCase::class);

// ============================================================
// Creation & Casts
// ============================================================

test('factory creates product with valid data', function () {
    $p = Product::factory()->create();

    expect($p)->toBeInstanceOf(Product::class)
        ->and($p->id)->toBeInt()
        ->and($p->title)->not->toBeEmpty()
        ->and($p->slug)->not->toBeEmpty()
        ->and($p->user_id)->toBeInt();
});

test('is_active is cast to boolean', function () {
    $p = Product::factory()->create(['is_active' => 1]);

    expect($p->is_active)->toBeBool()->toBeTrue();
});

test('is_featured is cast to boolean', function () {
    $p = Product::factory()->create(['is_featured' => 1]);

    expect($p->is_featured)->toBeBool()->toBeTrue();
});

test('numeric counts are cast to integers', function () {
    $p = Product::factory()->create([
        'view_count'      => '100',
        'fake_view_count' => '50',
        'sold_count'      => '25',
        'lowest_price'    => '10000',
        'total_stock'     => '5',
    ]);

    expect($p->view_count)->toBeInt()->toBe(100)
        ->and($p->fake_view_count)->toBeInt()->toBe(50)
        ->and($p->sold_count)->toBeInt()->toBe(25)
        ->and($p->lowest_price)->toBeInt()->toBe(10000)
        ->and($p->total_stock)->toBeInt()->toBe(5);
});

test('published_at is cast to Carbon', function () {
    $p = Product::factory()->published()->create();

    expect($p->published_at)->toBeInstanceOf(\Carbon\Carbon::class);
});

// ============================================================
// Status Constants
// ============================================================

test('status constants are defined', function () {
    expect(Product::STATUS_DRAFT)->toBe('draft')
        ->and(Product::STATUS_PUBLISHED)->toBe('published')
        ->and(Product::STATUS_ARCHIVED)->toBe('archived');
});

// ============================================================
// Scopes
// ============================================================

test('scope active returns only active products', function () {
    Product::factory()->count(3)->active()->create();
    Product::factory()->count(2)->inactive()->create();

    expect(Product::active()->count())->toBe(3);
});

test('scope published returns only published products', function () {
    Product::factory()->count(3)->published()->create();
    Product::factory()->count(2)->draft()->create();
    Product::factory()->count(1)->archived()->create();

    expect(Product::published()->count())->toBe(3);
});

test('scope featured returns only featured products', function () {
    Product::factory()->count(2)->featured()->create();
    Product::factory()->count(3)->create();  // not featured

    expect(Product::featured()->count())->toBe(2);
});

test('scope byBrand filters by brand_id', function () {
    $brand1=Brand::factory()->create();
    $brand2=Brand::factory()->create();
    Product::factory()->count(3)->create(['brand_id' => $brand1->id]);
    Product::factory()->count(2)->create(['brand_id' => $brand2->id]);

    expect(Product::byBrand(1)->count())->toBe(3);
});

test('scope ordered prioritizes featured then recent', function () {
    $old = Product::factory()->create(['created_at' => now()->subDays(5)]);
    $newRegular = Product::factory()->create(['created_at' => now()->subDay()]);
    $oldFeatured = Product::factory()->featured()->create(['created_at' => now()->subDays(10)]);

    $ordered = Product::ordered()->get();

    // featured (حتی قدیمی) اول
    expect($ordered[0]->id)->toBe($oldFeatured->id)
        ->and($ordered[1]->id)->toBe($newRegular->id)
        ->and($ordered[2]->id)->toBe($old->id);
});

test('scope search matches title', function () {
    Product::factory()->create(['title' => 'گوشی سامسونگ']);
    Product::factory()->create(['title' => 'لپتاپ اپل']);

    expect(Product::search('سامسونگ')->count())->toBe(1);
});

// ============================================================
// Computed Properties
// ============================================================

test('total_views combines view_count and fake_view_count', function () {
    $p = Product::factory()->create([
        'view_count'      => 100,
        'fake_view_count' => 50,
    ]);

    expect($p->total_views)->toBe(150);
});

test('is_available is true when active and published and in stock', function () {
    $p = Product::factory()->active()->published()->create([
        'total_stock' => 5,
    ]);

    expect($p->is_available)->toBeTrue();
});

test('is_available is false when inactive', function () {
    $p = Product::factory()->inactive()->published()->create([
        'total_stock' => 5,
    ]);

    expect($p->is_available)->toBeFalse();
});

test('is_available is false when not published', function () {
    $p = Product::factory()->active()->draft()->create([
        'total_stock' => 5,
    ]);

    expect($p->is_available)->toBeFalse();
});

test('is_available is false when out of stock', function () {
    $p = Product::factory()->active()->published()->create([
        'total_stock' => 0,
    ]);

    expect($p->is_available)->toBeFalse();
});

// ============================================================
// Soft Delete
// ============================================================

test('product uses soft delete', function () {
    $p = Product::factory()->create();
    $id = $p->id;

    $p->delete();

    expect(Product::find($id))->toBeNull()
        ->and(Product::withTrashed()->find($id))->not->toBeNull();
});

// ============================================================
// Route Key
// ============================================================

test('route key is slug not id', function () {
    $p = Product::factory()->create();

    expect($p->getRouteKeyName())->toBe('slug');
});
```

### `modules/Products/tests/Unit/Services/ProductServiceTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Modules\Products\Contracts\ProductRepositoryContract;
use Modules\Products\DataTransferObjects\CreateProductData;
use Modules\Products\DataTransferObjects\UpdateProductData;
use Modules\Products\Events\ProductArchived;
use Modules\Products\Events\ProductCreated;
use Modules\Products\Events\ProductDeleted;
use Modules\Products\Events\ProductPublished;
use Modules\Products\Events\ProductUpdated;
use Modules\Products\Events\ProductViewed;
use Modules\Products\Exceptions\ProductNotFoundException;
use Modules\Products\Models\Product;
use Modules\Products\Services\ProductService;
use Tests\TestCase;

uses(TestCase::class);

/**
 * Helper: Service factory برای tests
 *
 * Pattern: Test Helper / Builder Pattern
 * این کاهش boilerplate در tests ه.
 *
 * @return array{0: ProductService, 1: \Mockery\MockInterface}
 */
function makeProductService(): array
{
    $repository = Mockery::mock(ProductRepositoryContract::class);
    $service = new ProductService($repository);

    return [$service, $repository];
}

// ============================================================
// findById
// ============================================================

test('findById returns product when exists', function () {
    [$service, $repo] = makeProductService();

    $p = Product::factory()->forUnitTest()->make(['id' => 1]);
    $repo->expects('find')->with(1)->andReturn($p);

    expect($service->findById(1))->toBeInstanceOf(Product::class);
});

test('findById throws exception when not found', function () {
    [$service, $repo] = makeProductService();

    $repo->expects('find')->with(999)->andReturn(null);

    expect(fn () => $service->findById(999))
        ->toThrow(ProductNotFoundException::class);
});

// ============================================================
// findBySlug
// ============================================================

test('findBySlug returns product when exists', function () {
    [$service, $repo] = makeProductService();

    $p = Product::factory()->forUnitTest()->make(['slug' => 'test']);
    $repo->expects('findBySlug')->with('test')->andReturn($p);

    expect($service->findBySlug('test')->slug)->toBe('test');
});

test('findBySlug throws exception when not found', function () {
    [$service, $repo] = makeProductService();

    $repo->expects('findBySlug')->with('missing')->andReturn(null);

    expect(fn () => $service->findBySlug('missing'))
        ->toThrow(ProductNotFoundException::class);
});

// ============================================================
// create
// ============================================================

test('create persists product and dispatches event', function () {
    Event::fake();
    [$service, $repo] = makeProductService();

    $p = Product::factory()->forUnitTest()->make(['id' => 1]);
    $repo->expects('create')->andReturn($p);

    $data = new CreateProductData(
        title: 'تست',
        enTitle: 'Test',
        slug: 'test',
        userId: 1,
    );

    $service->create($data);

    Event::assertDispatched(ProductCreated::class);
});

// ============================================================
// update
// ============================================================

test('update returns product without query when no changes', function () {
    [$service, $repo] = makeProductService();

    $p = Product::factory()->forUnitTest()->make(['id' => 1]);
    $repo->shouldNotReceive('update');
    $repo->expects('find')->with(1)->andReturn($p);

    $service->update(1, new UpdateProductData());

    expect(true)->toBeTrue();  // no exception = pass
});

test('update modifies product and dispatches event', function () {
    Event::fake();
    [$service, $repo] = makeProductService();

    $updated = Product::factory()->forUnitTest()->make(['id' => 1, 'title' => 'جدید']);
    $repo->expects('update')->andReturn($updated);

    $service->update(1, new UpdateProductData(title: 'جدید'));

    Event::assertDispatched(ProductUpdated::class);
});

// ============================================================
// delete
// ============================================================

test('delete removes product and dispatches event', function () {
    Event::fake();
    [$service, $repo] = makeProductService();

    $p = Product::factory()->forUnitTest()->make(['id' => 1]);
    $repo->expects('find')->with(1)->andReturn($p);
    $repo->expects('delete')->with(1)->andReturn(true);

    $service->delete(1);

    Event::assertDispatched(
        ProductDeleted::class,
        fn ($e) => $e->productId === 1,
    );
});

test('delete throws when product not found', function () {
    [$service, $repo] = makeProductService();

    $repo->expects('find')->with(999)->andReturn(null);
    $repo->shouldNotReceive('delete');

    expect(fn () => $service->delete(999))
        ->toThrow(ProductNotFoundException::class);
});

// ============================================================
// State Transitions
// ============================================================

test('publish sets status and dispatches event', function () {
    Event::fake();
    [$service, $repo] = makeProductService();

    $p = Product::factory()->forUnitTest()->make([
        'id' => 1,
        'status' => Product::STATUS_PUBLISHED,
    ]);

    $repo->expects('update')
        ->with(1, Mockery::on(fn($data) =>
            $data['status'] === Product::STATUS_PUBLISHED
            && isset($data['published_at'])
        ))
        ->andReturn($p);

    $service->publish(1);

    Event::assertDispatched(ProductPublished::class);
});

test('archive sets status and dispatches event', function () {
    Event::fake();
    [$service, $repo] = makeProductService();

    $p = Product::factory()->forUnitTest()->make([
        'id' => 1,
        'status' => Product::STATUS_ARCHIVED,
    ]);

    $repo->expects('update')
        ->with(1, ['status' => Product::STATUS_ARCHIVED])
        ->andReturn($p);

    $service->archive(1);

    Event::assertDispatched(ProductArchived::class);
});

test('activate updates is_active and dispatches event', function () {
    Event::fake();
    [$service, $repo] = makeProductService();

    $p = Product::factory()->forUnitTest()->make(['id' => 1, 'is_active' => true]);
    $repo->expects('update')->with(1, ['is_active' => true])->andReturn($p);

    $service->activate(1);

    Event::assertDispatched(ProductUpdated::class);
});

test('feature updates is_featured and dispatches event', function () {
    Event::fake();
    [$service, $repo] = makeProductService();

    $p = Product::factory()->forUnitTest()->make(['id' => 1, 'is_featured' => true]);
    $repo->expects('update')->with(1, ['is_featured' => true])->andReturn($p);

    $service->feature(1);

    Event::assertDispatched(ProductUpdated::class);
});

// ============================================================
// recordView
// ============================================================

test('recordView increments view_count and dispatches event', function () {
    Event::fake();
    [$service, $repo] = makeProductService();

    $repo->expects('incrementViewCount')->with(1)->andReturn(true);

    $service->recordView(1);

    Event::assertDispatched(
        ProductViewed::class,
        fn ($e) => $e->productId === 1,
    );
});

test('recordView with userId passes through to event', function () {
    Event::fake();
    [$service, $repo] = makeProductService();

    $repo->expects('incrementViewCount')->with(1)->andReturn(true);

    $service->recordView(1, userId: 42);

    Event::assertDispatched(
        ProductViewed::class,
        fn ($e) => $e->productId === 1 && $e->userId === 42,
    );
});

test('recordView does not dispatch event when increment fails', function () {
    Event::fake();
    [$service, $repo] = makeProductService();

    $repo->expects('incrementViewCount')->with(999)->andReturn(false);

    $service->recordView(999);

    Event::assertNotDispatched(ProductViewed::class);
});

// ============================================================
// Public Contract
// ============================================================

test('exists returns true when product exists', function () {
    [$service, $repo] = makeProductService();

    $repo->expects('exists')->with(1)->andReturn(true);

    expect($service->exists(1))->toBeTrue();
});

test('isActive returns true for active product', function () {
    [$service, $repo] = makeProductService();

    $p = Product::factory()->forUnitTest()->make(['is_active' => true]);
    $repo->expects('find')->with(1)->andReturn($p);

    expect($service->isActive(1))->toBeTrue();
});

test('isActive returns false when not found', function () {
    [$service, $repo] = makeProductService();

    $repo->expects('find')->with(999)->andReturn(null);

    expect($service->isActive(999))->toBeFalse();
});

test('getById returns ProductPublicData when found', function () {
    [$service, $repo] = makeProductService();

    $p = Product::factory()->forUnitTest()->make([
        'id' => 1,
        'title' => 'تست',
        'status' => Product::STATUS_PUBLISHED,
        'is_active' => true,
        'total_stock' => 5,
    ]);
    $repo->expects('find')->with(1)->andReturn($p);

    $result = $service->getById(1);

    expect($result)->not->toBeNull()
        ->and($result->title)->toBe('تست')
        ->and($result->isActive)->toBeTrue();
});

test('getById returns null when not found', function () {
    [$service, $repo] = makeProductService();

    $repo->expects('find')->with(999)->andReturn(null);

    expect($service->getById(999))->toBeNull();
});

test('areAllActive returns true when empty', function () {
    [$service, $repo] = makeProductService();

    $repo->shouldNotReceive('countActiveByIds');

    expect($service->areAllActive([]))->toBeTrue();
});

test('areAllActive returns true when all active', function () {
    [$service, $repo] = makeProductService();

    $repo->expects('countActiveByIds')->with([1, 2, 3])->andReturn(3);

    expect($service->areAllActive([1, 2, 3]))->toBeTrue();
});

test('areAllActive returns false when some inactive', function () {
    [$service, $repo] = makeProductService();

    $repo->expects('countActiveByIds')->with([1, 2, 3])->andReturn(2);

    expect($service->areAllActive([1, 2, 3]))->toBeFalse();
});
```

### `modules/Shared/tests/Unit/StringHelperTest.php`

```php
<?php

declare(strict_types=1);


use Modules\Shared\Helpers\StringHelper;

test('it replaces single space with default replacement', function () {

    $result = StringHelper::replaceSpace('hello world');

    expect($result)->toBe('hello-world');
});


test('it replaces multiple spaces with single replacement', function () {

    $result = StringHelper::replaceSpace('hello   world');

    expect($result)->toBe('hello-world');
});


test('it trims leading and trailing spaces', function () {
    expect(StringHelper::replaceSpace('  hello world  '))->toBe('hello-world');
});


test('it returns empty string for empty input', function () {
    expect(StringHelper::replaceSpace(''))->toBe('');
});


test('it handles various whitespace patterns', function (string $input, string $expected) {
    expect(StringHelper::replaceSpace($input))->toBe($expected);

})->with([
    'single space' => ['hello world', 'hello-world'],
    'multiple spaces' => ['hello   world', 'hello-world'],
    'leading spaces' => ['  hello world', 'hello-world'],
    'trailing spaces' => ['hello world  ', 'hello-world'],
    'tabs' => ["hello\tworld", 'hello-world'],
    'newlines' => ["hello\nworld", 'hello-world'],
    'no whitespace' => ['hello', 'hello'],
    'empty string' => ['', ''],
    'persian space' => ['سلام  دنیا', 'سلام-دنیا'],
]);
```

### `modules/Warranties/tests/Feature/Admin/WarramtyAdminTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Warranties\Events\WarrantyCreated;
use Modules\Warranties\Events\WarrantyDeleted;
use Modules\Warranties\Models\Warranty;
use Tests\TestCase;

uses(RefreshDatabase::class, TestCase::class);

// ============================================================
// STORE
// ============================================================

test('admin can create a warranty', function () {
    Event::fake();

    $payload = [
        'name'            => 'گارانتی ۲۴ ماهه',
        'english_name'    => '24-Month Warranty',
        'slug'            => '24-month',
        'duration_months' => 24,
        'provider'        => 'سامسونگ',
    ];

    $response = $this->postJson('/api/admin/warranties', $payload);

    $response->assertStatus(201);
    $response->assertJsonPath('data.duration_months', 24);
    $response->assertJsonPath('data.provider', 'سامسونگ');

    $this->assertDatabaseHas('warranties', [
        'slug'            => '24-month',
        'duration_months' => 24,
    ]);

    Event::assertDispatched(WarrantyCreated::class);
});

test('store: it requires name', function () {
    $response = $this->postJson('/api/admin/warranties', [
        'english_name'    => 'Test',
        'slug'            => 'test',
        'duration_months' => 12,
        'provider'        => 'تست',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name']);
});

test('store: it requires duration_months', function () {
    $response = $this->postJson('/api/admin/warranties', [
        'name'         => 'تست',
        'english_name' => 'Test',
        'slug'         => 'test',
        'provider'     => 'تست',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['duration_months']);
});

test('store: rejects invalid duration (zero)', function () {
    $response = $this->postJson('/api/admin/warranties', [
        'name'            => 'تست',
        'english_name'    => 'Test',
        'slug'            => 'test',
        'duration_months' => 0,
        'provider'        => 'تست',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['duration_months']);
});

test('store: rejects duration too long', function () {
    $response = $this->postJson('/api/admin/warranties', [
        'name'            => 'تست',
        'english_name'    => 'Test',
        'slug'            => 'test',
        'duration_months' => 200,  // max is 120
        'provider'        => 'تست',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['duration_months']);
});

test('store: rejects duplicate slug', function () {
    Warranty::factory()->create(['slug' => 'test-slug']);

    $response = $this->postJson('/api/admin/warranties', [
        'name'            => 'تست',
        'english_name'    => 'Test',
        'slug'            => 'test-slug',
        'duration_months' => 12,
        'provider'        => 'تست',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['slug']);
});

// ============================================================
// INDEX
// ============================================================

test('returns empty list when no warranties', function () {
    $response = $this->getJson('/api/admin/warranties');

    $response->assertOk();
    $response->assertJsonCount(0, 'data');
});

test('returns paginated list', function () {
    Warranty::factory()->count(20)->create();

    $response = $this->getJson('/api/admin/warranties?per_page=5');

    $response->assertOk();
    $response->assertJsonCount(5, 'data');
    $response->assertJsonPath('meta.total', 20);
});

test('index excludes soft deleted', function () {
    Warranty::factory()->count(3)->create();
    $deleted = Warranty::factory()->create();
    $deleted->delete();

    $response = $this->getJson('/api/admin/warranties');

    $response->assertJsonCount(3, 'data');
});

// ============================================================
// SHOW
// ============================================================

test('returns warranty by slug', function () {
    $w = Warranty::factory()->create([
        'slug' => 'test-warranty',
        'duration_months' => 18,
    ]);

    $response = $this->getJson("/api/admin/warranties/{$w->slug}");

    $response->assertOk();
    $response->assertJsonPath('data.id', $w->id);
    $response->assertJsonPath('data.duration_months', 18);
});

test('show returns 404 for non-existent slug', function () {
    $response = $this->getJson('/api/admin/warranties/non-existent');

    $response->assertStatus(404);
});

// ============================================================
// UPDATE
// ============================================================

test('admin can update warranty', function () {
    $w = Warranty::factory()->create(['name' => 'قدیمی']);

    $response = $this->patchJson("/api/admin/warranties/{$w->slug}", [
        'name' => 'جدید',
        'duration_months' => 36,
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.name', 'جدید');
    $response->assertJsonPath('data.duration_months', 36);
});

test('update: partial update only modifies sent fields', function () {
    $w = Warranty::factory()->create([
        'name'            => 'اصلی',
        'duration_months' => 12,
        'provider'        => 'سامسونگ',
    ]);

    $this->patchJson("/api/admin/warranties/{$w->slug}", [
        'name' => 'جدید',
    ]);

    $this->assertDatabaseHas('warranties', [
        'id'              => $w->id,
        'name'            => 'جدید',
        'duration_months' => 12,        // بدون تغییر
        'provider'        => 'سامسونگ', // بدون تغییر
    ]);
});

test('update: allows same slug as own', function () {
    $w = Warranty::factory()->create(['slug' => 'test-1']);

    $response = $this->patchJson("/api/admin/warranties/{$w->slug}", [
        'slug' => 'test-1',
        'name' => 'تست',
    ]);

    $response->assertOk();
});

test('update: rejects duplicate slug from other warranty', function () {
    Warranty::factory()->create(['slug' => 'existing']);
    $other = Warranty::factory()->create(['slug' => 'other']);

    $response = $this->patchJson("/api/admin/warranties/{$other->slug}", [
        'slug' => 'existing',
    ]);

    $response->assertStatus(422);
});

// ============================================================
// DESTROY
// ============================================================

test('admin can soft delete warranty', function () {
    Event::fake();

    $w = Warranty::factory()->create();
    $id = $w->id;

    $response = $this->deleteJson("/api/admin/warranties/{$w->slug}");

    $response->assertNoContent();
    expect(Warranty::find($id))->toBeNull();
    expect(Warranty::withTrashed()->find($id))->not->toBeNull();

    Event::assertDispatched(
        WarrantyDeleted::class,
        fn ($e) => $e->warrantyId === $id,
    );
});

test('destroy returns 404 for non-existent slug', function () {
    $response = $this->deleteJson('/api/admin/warranties/non-existent');

    $response->assertStatus(404);
});

// ============================================================
// ACTIVATE / DEACTIVATE
// ============================================================

test('admin can activate inactive warranty', function () {
    $w = Warranty::factory()->inactive()->create();

    $response = $this->patchJson("/api/admin/warranties/{$w->slug}/activate");

    $response->assertOk();
    $response->assertJsonPath('data.is_active', true);
});

test('admin can deactivate active warranty', function () {
    $w = Warranty::factory()->active()->create();

    $response = $this->patchJson("/api/admin/warranties/{$w->slug}/deactivate");

    $response->assertOk();
    $response->assertJsonPath('data.is_active', false);
});

test('activate returns 404 for non-existent slug', function () {
    $response = $this->patchJson('/api/admin/warranties/non-existent/activate');

    $response->assertStatus(404);
});

// ============================================================
// SEARCH
// ============================================================

test('search matches by name', function () {
    Warranty::factory()->create([
        'name' => 'گارانتی سامسونگ پلاس',
        'provider' => 'تست-A',  // ← یه provider خاص که با هیچ test query match نمی‌شه
    ]);
    Warranty::factory()->create([
        'name' => 'گارانتی اپل',
        'provider' => 'تست-B',
    ]);

    $response = $this->getJson('/api/admin/warranties/search?q=سامسونگ');

    $response->assertJsonCount(1, 'data');
});

test('search matches by provider', function () {
    Warranty::factory()->create([
        'provider' => 'سامسونگ',
        'name' => 'تست-A',           // ← name خاص که match نکنه
        'english_name' => 'test-a',  // ← همچنین english_name
    ]);
    Warranty::factory()->create([
        'provider' => 'اپل',
        'name' => 'تست-B',
        'english_name' => 'test-b',
    ]);

    $response = $this->getJson('/api/admin/warranties/search?q=اپل');

    $response->assertJsonCount(1, 'data');
});
```

### `modules/Warranties/tests/Unit/Services/WarrantyServiceTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Modules\Warranties\Contracts\WarrantyRepositoryContract;
use Modules\Warranties\DataTransferObjects\CreateWarrantyData;
use Modules\Warranties\DataTransferObjects\UpdateWarrantyData;
use Modules\Warranties\Events\WarrantyActivated;
use Modules\Warranties\Events\WarrantyCreated;
use Modules\Warranties\Events\WarrantyDeactivated;
use Modules\Warranties\Events\WarrantyDeleted;
use Modules\Warranties\Events\WarrantyUpdated;
use Modules\Warranties\Exceptions\WarrantyNotFoundException;
use Modules\Warranties\Models\Warranty;
use Modules\Warranties\Services\WarrantyService;
use Tests\TestCase;

uses(TestCase::class);

function makeWarrantyService(): array
{
    $repository = Mockery::mock(WarrantyRepositoryContract::class);
    $service = new WarrantyService($repository);

    return [$service, $repository];
}

// ──────────── findById ────────────

test('findById returns warranty when exists', function () {
    [$service, $repo] = makeWarrantyService();

    $w = Warranty::factory()->make(['id' => 1]);
    $repo->expects('find')->with(1)->andReturn($w);

    expect($service->findById(1))->toBeInstanceOf(Warranty::class);
});

test('findById throws exception when not found', function () {
    [$service, $repo] = makeWarrantyService();

    $repo->expects('find')->with(999)->andReturn(null);

    expect(fn () => $service->findById(999))
        ->toThrow(WarrantyNotFoundException::class);
});

// ──────────── create ────────────

test('create persists warranty and dispatches event', function () {
    Event::fake();
    [$service, $repo] = makeWarrantyService();

    $w = Warranty::factory()->make(['id' => 1]);
    $repo->expects('create')->andReturn($w);

    $data = new CreateWarrantyData(
        name: 'گارانتی ۲۴ ماهه',
        englishName: 'Test 24',
        slug: 'test-24',
        durationMonths: 24,
        provider: 'تست',
    );

    $service->create($data);

    Event::assertDispatched(WarrantyCreated::class);
});

// ──────────── update ────────────

test('update returns warranty without query when no changes', function () {
    [$service, $repo] = makeWarrantyService();

    $w = Warranty::factory()->make(['id' => 1]);
    $repo->shouldNotReceive('update');
    $repo->expects('find')->with(1)->andReturn($w);

    $result = $service->update(1, new UpdateWarrantyData());

    expect($result->id)->toBe(1);
});

test('update modifies warranty and dispatches event', function () {
    Event::fake();
    [$service, $repo] = makeWarrantyService();

    $updated = Warranty::factory()->make(['id' => 1, 'name' => 'جدید']);
    $repo->expects('update')->andReturn($updated);

    $service->update(1, new UpdateWarrantyData(name: 'جدید'));

    Event::assertDispatched(WarrantyUpdated::class);
});

// ──────────── delete ────────────

test('delete removes warranty and dispatches event', function () {
    Event::fake();
    [$service, $repo] = makeWarrantyService();

    $w = Warranty::factory()->make(['id' => 1]);
    $repo->expects('find')->with(1)->andReturn($w);
    $repo->expects('delete')->with(1)->andReturn(true);

    $service->delete(1);

    Event::assertDispatched(
        WarrantyDeleted::class,
        fn ($e) => $e->warrantyId === 1,
    );
});

test('delete throws when not found', function () {
    [$service, $repo] = makeWarrantyService();

    $repo->expects('find')->with(999)->andReturn(null);
    $repo->shouldNotReceive('delete');

    expect(fn () => $service->delete(999))
        ->toThrow(WarrantyNotFoundException::class);
});

// ──────────── activate / deactivate ────────────

test('activate updates is_active and dispatches event', function () {
    Event::fake();
    [$service, $repo] = makeWarrantyService();

    $w = Warranty::factory()->make(['id' => 1, 'is_active' => true]);
    $repo->expects('update')->with(1, ['is_active' => true])->andReturn($w);

    $service->activate(1);

    Event::assertDispatched(WarrantyActivated::class);
});

test('deactivate updates is_active and dispatches event', function () {
    Event::fake();
    [$service, $repo] = makeWarrantyService();

    $w = Warranty::factory()->make(['id' => 1, 'is_active' => false]);
    $repo->expects('update')->with(1, ['is_active' => false])->andReturn($w);

    $service->deactivate(1);

    Event::assertDispatched(WarrantyDeactivated::class);
});

// ──────────── Public Contract ────────────

test('exists returns true when warranty exists', function () {
    [$service, $repo] = makeWarrantyService();

    $repo->expects('exists')->with(1)->andReturn(true);

    expect($service->exists(1))->toBeTrue();
});

test('isActive returns true for active warranty', function () {
    [$service, $repo] = makeWarrantyService();

    $w = Warranty::factory()->make(['is_active' => true]);
    $repo->expects('find')->with(1)->andReturn($w);

    expect($service->isActive(1))->toBeTrue();
});

test('isActive returns false when not found', function () {
    [$service, $repo] = makeWarrantyService();

    $repo->expects('find')->with(999)->andReturn(null);

    expect($service->isActive(999))->toBeFalse();
});

test('getById returns WarrantyPublicData when found', function () {
    [$service, $repo] = makeWarrantyService();

    $w = Warranty::factory()->make([
        'id' => 1,
        'name' => 'تست',
        'duration_months' => 24,
        'provider' => 'تست',
        'is_active' => true,
    ]);
    $repo->expects('find')->with(1)->andReturn($w);

    $result = $service->getById(1);

    expect($result)->not->toBeNull()
        ->and($result->durationMonths)->toBe(24)
        ->and($result->isActive)->toBeTrue();
});

test('getById returns null when not found', function () {
    [$service, $repo] = makeWarrantyService();

    $repo->expects('find')->with(999)->andReturn(null);

    expect($service->getById(999))->toBeNull();
});

test('areAllActive returns true when empty', function () {
    [$service, $repo] = makeWarrantyService();

    $repo->shouldNotReceive('countActiveByIds');

    expect($service->areAllActive([]))->toBeTrue();
});

test('areAllActive returns true when all active', function () {
    [$service, $repo] = makeWarrantyService();

    $repo->expects('countActiveByIds')->with([1, 2, 3])->andReturn(3);

    expect($service->areAllActive([1, 2, 3]))->toBeTrue();
});

test('areAllActive returns false when some inactive', function () {
    [$service, $repo] = makeWarrantyService();

    $repo->expects('countActiveByIds')->with([1, 2, 3])->andReturn(2);

    expect($service->areAllActive([1, 2, 3]))->toBeFalse();
});
```

### `modules/Warranties/tests/Unit/WarrantyModelTest.php`

```php
<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Warranties\Models\Warranty;
use Tests\TestCase;

uses(RefreshDatabase::class, TestCase::class);

// ──────────── Creation & Casts ────────────

test('factory creates warranty with valid data', function () {
    $w = Warranty::factory()->create();

    expect($w)->toBeInstanceOf(Warranty::class)
        ->and($w->id)->toBeInt()
        ->and($w->name)->not->toBeEmpty()
        ->and($w->slug)->not->toBeEmpty()
        ->and($w->provider)->not->toBeEmpty();
});

test('is_active is cast to boolean', function () {
    $w = Warranty::factory()->create(['is_active' => 1]);

    expect($w->is_active)->toBeBool()->toBeTrue();
});

test('duration_months is cast to integer', function () {
    $w = Warranty::factory()->create(['duration_months' => '24']);

    expect($w->duration_months)->toBeInt()->toBe(24);
});

// ──────────── Scopes ────────────

test('scope active returns only active warranties', function () {
    Warranty::factory()->count(3)->active()->create();
    Warranty::factory()->count(2)->inactive()->create();

    expect(Warranty::active()->count())->toBe(3);
});

test('scope ordered orders by sort_order then duration_months', function () {
    Warranty::factory()->create(['sort_order' => 2, 'duration_months' => 6]);
    Warranty::factory()->create(['sort_order' => 1, 'duration_months' => 24]);
    Warranty::factory()->create(['sort_order' => 1, 'duration_months' => 12]);

    $ordered = Warranty::ordered()->get();

    expect($ordered[0]->duration_months)->toBe(12)  // sort=1, dur=12
    ->and($ordered[1]->duration_months)->toBe(24)  // sort=1, dur=24
    ->and($ordered[2]->duration_months)->toBe(6);   // sort=2
});

test('scope search matches name', function () {
    Warranty::factory()->create(['name' => 'گارانتی سامسونگ']);
    Warranty::factory()->create(['name' => 'گارانتی اپل']);

    expect(Warranty::search('سامسونگ')->count())->toBe(1);
});

test('scope search matches provider', function () {
    Warranty::factory()->create(['provider' => 'سامسونگ']);
    Warranty::factory()->create(['provider' => 'اپل']);

    expect(Warranty::search('اپل')->count())->toBe(1);
});
test('scope byProvider filters correctly', function () {
    Warranty::factory()->count(3)->create(['provider' => 'سامسونگ']);
    Warranty::factory()->count(2)->create(['provider' => 'اپل']);

    expect(Warranty::byProvider('سامسونگ')->count())->toBe(3);
});

// ──────────── Soft Delete ────────────

test('warranty uses soft delete', function () {
    $w = Warranty::factory()->create();
    $id = $w->id;

    $w->delete();

    expect(Warranty::find($id))->toBeNull()
        ->and(Warranty::withTrashed()->find($id))->not->toBeNull();
});

// ──────────── Route Key ────────────

test('route key is slug not id', function () {
    $w = Warranty::factory()->create();

    expect($w->getRouteKeyName())->toBe('slug');
});
```

---

## 📊 وضعیت فعلی تست‌ها

```
اجرا شده در: Tue May 19 08:21:54 UTC 2026

  [32;1m✓[39;22m[90m [39m[90mupdate returns warranty without query when no changes[39m[90m               [39m [90m0.01s[39m  
  [32;1m✓[39;22m[90m [39m[90mupdate modifies warranty and dispatches event[39m[90m                       [39m [90m0.01s[39m  
  [32;1m✓[39;22m[90m [39m[90mdelete removes warranty and dispatches event[39m[90m                        [39m [90m0.01s[39m  
  [32;1m✓[39;22m[90m [39m[90mdelete throws when not found[39m[90m                                        [39m [90m0.01s[39m  
  [32;1m✓[39;22m[90m [39m[90mactivate updates is_active and dispatches event[39m[90m                     [39m [90m0.03s[39m  
  [32;1m✓[39;22m[90m [39m[90mdeactivate updates is_active and dispatches event[39m[90m                   [39m [90m0.01s[39m  
  [32;1m✓[39;22m[90m [39m[90mexists returns true when warranty exists[39m[90m                            [39m [90m0.01s[39m  
  [32;1m✓[39;22m[90m [39m[90misActive returns true for active warranty[39m[90m                           [39m [90m0.01s[39m  
  [32;1m✓[39;22m[90m [39m[90misActive returns false when not found[39m[90m                               [39m [90m0.01s[39m  
  [32;1m✓[39;22m[90m [39m[90mgetById returns WarrantyPublicData when found[39m[90m                       [39m [90m0.01s[39m  
  [32;1m✓[39;22m[90m [39m[90mgetById returns null when not found[39m[90m                                 [39m [90m0.01s[39m  
  [32;1m✓[39;22m[90m [39m[90mareAllActive returns true when empty[39m[90m                                [39m [90m0.01s[39m  
  [32;1m✓[39;22m[90m [39m[90mareAllActive returns true when all active[39m[90m                           [39m [90m0.01s[39m  
  [32;1m✓[39;22m[90m [39m[90mareAllActive returns false when some inactive[39m[90m                       [39m [90m0.01s[39m  

  [30;42;1m PASS [39;49;22m[39m Modules\Warranties\tests\Unit\WarrantyModelTest[39m
  [32;1m✓[39;22m[90m [39m[90mfactory creates warranty with valid data[39m[90m                            [39m [90m0.03s[39m  
  [32;1m✓[39;22m[90m [39m[90mis_active is cast to boolean[39m[90m                                        [39m [90m0.01s[39m  
  [32;1m✓[39;22m[90m [39m[90mduration_months is cast to integer[39m[90m                                  [39m [90m0.01s[39m  
  [32;1m✓[39;22m[90m [39m[90mscope active returns only active warranties[39m[90m                         [39m [90m0.01s[39m  
  [32;1m✓[39;22m[90m [39m[90mscope ordered orders by sort_order then duration_months[39m[90m             [39m [90m0.01s[39m  
  [32;1m✓[39;22m[90m [39m[90mscope search matches name[39m[90m                                           [39m [90m0.01s[39m  
  [32;1m✓[39;22m[90m [39m[90mscope search matches provider[39m[90m                                       [39m [90m0.01s[39m  
  [32;1m✓[39;22m[90m [39m[90mscope byProvider filters correctly[39m[90m                                  [39m [90m0.01s[39m  
  [32;1m✓[39;22m[90m [39m[90mwarranty uses soft delete[39m[90m                                           [39m [90m0.01s[39m  
  [32;1m✓[39;22m[90m [39m[90mroute key is slug not id[39m[90m                                            [39m [90m0.01s[39m  

  [90mTests:[39m    [32;1m424 passed[39;22m[90m (1065 assertions)[39m
  [90mDuration:[39m [39m7.33s[39m

```

---

## 🎯 برای شروع چت جدید

این فایل رو attach کن به Claude و این پیام رو بفرست:

```
سلام Claude! من Robert هستم.
این snapshot کامل پروژه‌ی Laravel modular monolith منه.

لطفاً:
1. کامل بخون (سبک کار، معماری، ماژول‌ها، تست‌ها)
2. بگو "آماده‌ام"
3. منتظر دستور بعدی بمون

آخرین وضعیت در پایان فایل (Test Status) رو ببین.
ادامه می‌دیم از همون نقطه.
```

