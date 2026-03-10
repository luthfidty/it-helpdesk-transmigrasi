<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\KawasanTransmigrasi;
use App\Models\User;
use Illuminate\Support\Str;

class GenerateUserKawasan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-user-kawasan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $kawasan = KawasanTransmigrasi::all();

        foreach ($kawasan as $k) {

            User::create([
                'name' => 'User '.$k->nama_kawasan,
                'email' => Str::slug($k->nama_kawasan).'@example.com',
                'password' => bcrypt('password123'),
                'kawasan_id' => $k->id
            ]);
        }

        $this->info('154 user berhasil dibuat!');
    }

}
