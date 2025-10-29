<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleCalendarService;

class GoogleCalendarSSLCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calendar:ssl-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and configure Google Calendar SSL settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking Google Calendar SSL Configuration...');
        
        // Check if credentials exist
        $credentialsPath = storage_path('app/google-calendar/service-account-credentials.json');
        if (!file_exists($credentialsPath)) {
            $this->error('❌ Google Calendar credentials not found at: ' . $credentialsPath);
            $this->line('Please ensure your Google Calendar credentials are properly configured.');
            return 1;
        }
        
        $this->info('✅ Google Calendar credentials found');
        
        // Test SSL connection
        $this->info('🔐 Testing SSL connection to Google Calendar API...');
        
        try {
            $calendarService = new GoogleCalendarService();
            $result = $calendarService->getEvents();
            
            if ($result['success']) {
                $this->info('✅ SSL connection successful! Found ' . count($result['events']) . ' events');
                return 0;
            } else {
                $this->error('❌ SSL connection failed: ' . $result['error']);
                
                // Check if SSL verification is disabled
                $sslVerify = env('GOOGLE_SSL_VERIFY', true);
                if ($sslVerify === false || $sslVerify === 'false') {
                    $this->warn('⚠️  SSL verification is already disabled in your .env file');
                } else {
                    $this->warn('💡 To fix SSL issues in development, add this to your .env file:');
                    $this->line('   GOOGLE_SSL_VERIFY=false');
                    $this->line('');
                    $this->warn('⚠️  WARNING: Only use this in development environments!');
                }
                
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Connection failed: ' . $e->getMessage());
            
            if (strpos($e->getMessage(), 'SSL') !== false) {
                $this->warn('💡 This appears to be an SSL certificate issue.');
                $this->warn('   Add GOOGLE_SSL_VERIFY=false to your .env file for development.');
            }
            
            return 1;
        }
    }
}
