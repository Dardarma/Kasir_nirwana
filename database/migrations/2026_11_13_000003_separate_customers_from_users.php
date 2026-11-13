<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legacy_user_id')->nullable()->unique();
            $table->string('name');
            $table->enum('status', ['aktif', 'non_aktif'])->default('aktif');
            $table->timestamps();
        });

        DB::table('users')
            ->whereNull('username')
            ->orderBy('id')
            ->each(function ($user) {
                DB::table('customers')->insert([
                    'id' => $user->id,
                    'legacy_user_id' => $user->id,
                    'name' => $user->name,
                    'status' => $user->status,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]);
            });

        Schema::table('sub_barang', function (Blueprint $table) {
            $table->foreignId('customer_id')
                ->nullable()
                ->after('barang_id')
                ->constrained('customers')
                ->restrictOnDelete();
        });

        DB::table('sub_barang')->orderBy('id')->each(function ($subBarang) {
            $customerId = DB::table('customers')
                ->where('legacy_user_id', $subBarang->user_id)
                ->value('id');

            if ($customerId === null) {
                throw new \RuntimeException("Customer untuk sub barang {$subBarang->id} tidak ditemukan.");
            }

            DB::table('sub_barang')->where('id', $subBarang->id)->update([
                'customer_id' => $customerId,
            ]);
        });

        Schema::table('transaksi', function (Blueprint $table) {
            $table->foreignId('customer_id')
                ->nullable()
                ->after('tanggal')
                ->constrained('customers')
                ->nullOnDelete();
        });

        DB::table('transaksi')
            ->whereNotNull('customer')
            ->orderBy('id')
            ->each(function ($transaksi) {
                $legacyValue = trim((string) $transaksi->customer);
                $customer = null;

                if (ctype_digit($legacyValue)) {
                    $customer = DB::table('customers')
                        ->where('legacy_user_id', (int) $legacyValue)
                        ->first();
                }

                $customer ??= DB::table('customers')
                    ->where('name', $legacyValue)
                    ->first();

                if ($customer !== null) {
                    DB::table('transaksi')->where('id', $transaksi->id)->update([
                        'customer_id' => $customer->id,
                        'customer' => $customer->name,
                    ]);
                }
            });

        Schema::table('sub_barang', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        DB::table('users')->whereNull('username')->delete();
    }

    public function down(): void
    {
        $customerUserIds = [];

        DB::table('customers')->orderBy('id')->each(function ($customer) use (&$customerUserIds) {
            $preferredId = $customer->legacy_user_id ?: $customer->id;
            $existingUser = DB::table('users')->where('id', $preferredId)->first();
            $userId = $existingUser?->username === null ? $existingUser->id : null;

            if ($existingUser === null) {
                DB::table('users')->insert([
                    'id' => $preferredId,
                    'name' => $customer->name,
                    'username' => null,
                    'password' => null,
                    'status' => $customer->status,
                    'remember_token' => null,
                    'created_at' => $customer->created_at,
                    'updated_at' => $customer->updated_at,
                ]);
                $userId = $preferredId;
            } elseif ($userId === null) {
                $userId = DB::table('users')->insertGetId([
                    'name' => $customer->name,
                    'username' => null,
                    'password' => null,
                    'status' => $customer->status,
                    'remember_token' => null,
                    'created_at' => $customer->created_at,
                    'updated_at' => $customer->updated_at,
                ]);
            }

            $customerUserIds[$customer->id] = $userId;
        });

        Schema::table('sub_barang', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('barang_id')
                ->constrained('users')
                ->cascadeOnDelete();
        });

        DB::table('sub_barang')->orderBy('id')->each(function ($subBarang) use ($customerUserIds) {
            DB::table('sub_barang')->where('id', $subBarang->id)->update([
                'user_id' => $customerUserIds[$subBarang->customer_id] ?? null,
            ]);
        });

        Schema::table('sub_barang', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });

        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });

        Schema::dropIfExists('customers');
    }
};
