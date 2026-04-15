<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MqttService;
use App\Models\ParkirTransaksi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * MQTT Listener Command
 * 
 * Jalankan dengan: php artisan mqtt:listen
 * 
 * Ini adalah daemon yang terus berjalan dan mendengarkan MQTT messages
 * dari RFID readers (entry & exit)
 */
class MqttListenerCommand extends Command   
{
    protected $signature = 'mqtt:listen';
    protected $description = 'Listen to MQTT messages from ESP32 RFID readers (entry & exit)';

    protected $mqtt;

    public function handle()
    {
        $this->info('🚀 Starting MQTT Listener...');
        $this->info('Broker: ' . config('mqtt.broker'));
        $this->info('Port: ' . config('mqtt.port'));
        $this->newLine();

        // Initialize MQTT Service
        $this->mqtt = new MqttService();

        // Connect to broker
        if (!$this->mqtt->connect()) {
            $this->error('❌ Failed to connect to MQTT broker');
            return 1;
        }

        $this->info('✅ Connected to MQTT broker');
        $this->newLine();

        // Define topics to subscribe
        $entryTopic = MqttService::getEntryRfidTopic();
        $exitTopic = MqttService::getExitRfidTopic();

        $this->info("📡 Subscribing to topics:");
        $this->line("  • Entry RFID: $entryTopic");
        $this->line("  • Exit RFID: $exitTopic");
        $this->newLine();

        try {
            // Subscribe dengan callbacks untuk setiap topic
            $this->mqtt->subscribe($entryTopic, [$this, 'handleEntryRfid']);
            $this->mqtt->subscribe($exitTopic, [$this, 'handleExitRfid']);

            $this->info('✅ Subscribed. Listening for messages...');
            $this->warn('Press Ctrl+C to stop listening');
            $this->newLine();

            // Start listening loop
            $this->mqtt->loop();

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            Log::error('MQTT Listener error: ' . $e->getMessage());
            return 1;
        } finally {
            $this->mqtt->disconnect();
            $this->info('Listener stopped');
        }
    }

    /**
     * Callback untuk RFID Entry
     * 
     * Payload format dari ESP32:
     * {
     *   "rfid": "RFID001",
     *   "timestamp": "2024-02-16 10:30:45"
     * }
     */
    public function handleEntryRfid(string $topic, string $message)
    {
        try {
            $this->line("📥 [ENTRY] Received: $message");

            // Parse JSON payload
            $data = json_decode($message, true);
            
            if (!$data || !isset($data['rfid'])) {
                $this->error('  ⚠️  Invalid payload format');
                $this->publishLcdMessage("Kartu Tidak\nValid");
                return;
            }

            $rfidValue = $data['rfid'];

            // Check if vehicle already checked in
            $existing = ParkirTransaksi::where('card_id', $rfidValue)
                ->whereIn('status', ['IN', 'OUT'])
                ->first();

            if ($existing) {
                $this->warn("  ⚠️  RFID already checked in (ID: $rfidValue)");
                $this->publishLcdMessage("Sudah Check-in\nSilakan Keluar");
                return;
            }

            // Create checkin transaction
            $transaksi = ParkirTransaksi::create([
                'card_id' => $rfidValue,
                'checkin_time' => now(),
                'status' => 'IN',
            ]);

            $this->info("  ✅ Check-in success (ID: {$transaksi->id}, RFID: $rfidValue)");

            // Publish to servo (OPEN) and LCD (welcome message)
            $this->publishServo('entry', 'OPEN');
            $this->publishLcdMessage("Selamat Datang\nSilakan Masuk");

            Log::info("MQTT Entry: RFID $rfidValue checked in - Transaction ID: {$transaksi->id}");

        } catch (\Exception $e) {
            $this->error("  ❌ Error: " . $e->getMessage());
            Log::error('MQTT Entry handler error: ' . $e->getMessage());
        }
    }

    /**
     * Callback untuk RFID Exit
     * 
     * Payload format dari ESP32:
     * {
     *   "rfid": "RFID001",
     *   "timestamp": "2024-02-16 10:45:30"
     * }
     */
    public function handleExitRfid(string $topic, string $message)
    {
        try {
            $this->line("📤 [EXIT] Received: $message");

            // Parse JSON payload
            $data = json_decode($message, true);
            
            if (!$data || !isset($data['rfid'])) {
                $this->error('  ⚠️  Invalid payload format');
                $this->publishLcdMessage("Kartu Tidak\nValid Bukan RFID Bos");
                return;
            }

            $rfidValue = $data['rfid'];

            // Find active transaction
            $transaksi = ParkirTransaksi::where('card_id', $rfidValue)
                ->where('status', 'IN')
                ->first();

            if (!$transaksi) {
                $this->warn("  ⚠️  RFID not found or not checked in (ID: $rfidValue)");
                $this->publishLcdMessage("Kartu Tidak\nValid atau Belum Check-in");
                return;
            }

            // Calculate duration and fee
            $checkoutTime = now();
            $durationMinutes = $transaksi->checkin_time->diffInMinutes($checkoutTime);
            $durationHours = ceil($durationMinutes / 60);
            $durationHours = max($durationHours, 1); // Minimum 1 hour

            $tarifPerJam = config('parking.tarif_per_jam', 2000);
            $totalFee = $durationHours * $tarifPerJam;

            // Update transaction to OUT (waiting payment)
            $transaksi->update([
                'checkout_time' => $checkoutTime,
                'duration' => $durationHours,
                'fee' => $totalFee,
                'status' => 'OUT',
            ]);

            $this->info("  ✅ Check-out success (ID: {$transaksi->id}, RFID: $rfidValue)");
            $this->line("     Duration: {$durationHours} hours, Fee: Rp " . number_format($totalFee));

            // Publish LCD dengan informasi biaya
            $feeFormatted = number_format($totalFee);
            $this->publishLcdMessage("Total: Rp$feeFormatted\nSilakan Bayar");

            Log::info("MQTT Exit: RFID $rfidValue checked out - Duration: {$durationHours}h, Fee: Rp$totalFee");

        } catch (\Exception $e) {
            $this->error("  ❌ Error: " . $e->getMessage());
            Log::error('MQTT Exit handler error: ' . $e->getMessage());
        }
    }

    /**
     * Publish servo command
     * Untuk buka/tutup palang
     */
    private function publishServo(string $gate, string $command)
    {
        $topic = $gate === 'entry' 
            ? MqttService::getEntryServoTopic()
            : MqttService::getExitServoTopic();

        // ESP32 cek: message.indexOf("OPEN") >= 0
        // Kirim plain string "OPEN" / "CLOSE"
        $this->mqtt->publish($topic, strtoupper($command));
        $this->line("  📤 Servo $gate: $command");
    }

    /**
     * Publish LCD message
     */
    private function publishLcdMessage(string $message)
    {
        $topic = MqttService::getLcdTopic();

        // ESP32 expects format: "line1|line2" (split by "|")
        // Ganti \n dengan | kalau ada
        $payload = str_replace(["\r\n", "\r", "\n"], '|', $message);

        $this->mqtt->publish($topic, $payload);
        $this->line("  📺 LCD: $message");
    }
}