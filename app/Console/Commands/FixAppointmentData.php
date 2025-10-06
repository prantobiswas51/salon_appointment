<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixAppointmentData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:fix-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix appointment data by parsing "Client Name - Service" format in service field';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing appointment data...');
        
        $appointmentsToFix = \App\Models\Appointment::where('service', 'LIKE', '% - %')
            ->whereNull('client_id')
            ->get();
            
        if ($appointmentsToFix->isEmpty()) {
            $this->info('No appointments to fix.');
            return 0;
        }
        
        $this->info("Found {$appointmentsToFix->count()} appointments to fix.");
        
        $fixed = 0;
        
        foreach ($appointmentsToFix as $appointment) {
            $serviceParts = explode(' - ', $appointment->service, 2);
            
            if (count($serviceParts) === 2) {
                $clientName = trim($serviceParts[0]);
                $serviceName = trim($serviceParts[1]);
                
                // Find or create client
                $client = \App\Models\Client::firstOrCreate(
                    ['name' => $clientName],
                    [
                        'phone' => '', // Will need to be updated later
                        'status' => 'Green'
                    ]
                );
                
                // Update the appointment
                $appointment->update([
                    'client_id' => $client->id,
                    'service' => $serviceName
                ]);
                
                $this->line("Fixed appointment #{$appointment->id}: '{$appointment->service}' -> Client: '{$clientName}', Service: '{$serviceName}'");
                $fixed++;
            }
        }
        
        $this->info("Successfully fixed {$fixed} appointments.");
        return 0;
    }
}
