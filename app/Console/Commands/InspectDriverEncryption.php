<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DriverNormalized;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Services\EncryptionService;

class InspectDriverEncryption extends Command
{
    protected $signature = 'inspect:driver-encryption';
    protected $description = 'Create a driver and inspect model vs raw DB values for encrypted fields';

    public function handle()
    {
        $this->info('Creating a test driver...');

        // Ensure we create a fresh unique email/phone
        $email = 'inspect+' . time() . '@example.test';
        $phone = '080' . rand(10000000, 99999999);

        $driver = DriverNormalized::create([
            'first_name' => 'Inspect',
            'surname' => 'Driver',
            'email' => $email,
            'phone' => $phone,
            'password' => Hash::make('Password123!'),
            'date_of_birth' => now()->subYears(25)->format('Y-m-d'),
            'license_number' => 'L' . rand(100000,999999),
            'driver_id' => 'DR' . rand(100000,999999),
        ]);

    $this->info('Model attributes (accessors applied):');
        $this->line('email: ' . $driver->email);
        $this->line('phone: ' . $driver->phone);
        $this->line('nin_number (if set): ' . ($driver->nin_number ?? 'null'));

        $this->info('Raw DB row:');
        $row = DB::table('drivers')->where('id', $driver->id)->first();

        if (!$row) {
            $this->error('Driver row not found in DB.');
            return 1;
        }

        $this->line('db.email: ' . ($row->email ?? 'null'));
        $this->line('db.phone: ' . ($row->phone ?? 'null'));
        $this->line('db.nin_number: ' . ($row->nin_number ?? 'null'));

        $this->info('Comparison:');
        $this->line('Model phone === DB phone? ' . (($driver->phone === $row->phone) ? 'yes' : 'no'));

        // Directly test EncryptionService
        if (app()->bound(EncryptionService::class)) {
            $this->info('EncryptionService self-check:');
            $service = app(EncryptionService::class);
            $this->line('isSensitiveField(phone): ' . ($service->isSensitiveField('phone') ? 'yes' : 'no'));
            $enc = $service->encryptField($phone, 'phone');
            $this->line('encryptField(phone) => ' . ($enc ?? 'null'));
            $this->line('encrypt looks like encrypted? ' . (is_string($enc) && strpos($enc, 'eyJ') === 0 ? 'starts with eyJ' : 'no eyJ prefix'));
            $dec = $service->decryptField($enc, 'phone');
            $this->line('decryptField(encrypted) => ' . ($dec ?? 'null'));
        } else {
            $this->warn('EncryptionService is not bound in container.');
        }

        $this->info('Done.');
        return 0;
    }
}
