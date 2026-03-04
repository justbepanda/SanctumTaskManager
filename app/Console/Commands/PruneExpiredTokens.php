<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Такая команды уже есть в Sanctum - можно было её не писать.
 */
class PruneExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:prune-expired-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Удаляет просроченные токены';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        PersonalAccessToken::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();

        return self::SUCCESS;
    }
}
