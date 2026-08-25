<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\DropiApiService;
use Illuminate\Console\Command;

class SyncDropiStockCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dropi:sync-stock {--product= : ID específico de producto o Dropi ID a sincronizar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincronizar automáticamente el inventario y stock de productos importados desde Dropi';

    /**
     * Execute the console command.
     */
    public function handle(DropiApiService $dropiApi): int
    {
        $this->info('Iniciando sincronización de stock con Dropi...');

        $productId = $this->option('product');

        if ($productId) {
            $product = Product::where('id', $productId)
                ->orWhere('dropi_id', $productId)
                ->first();

            if (!$product) {
                $this->error("No se encontró el producto con ID o Dropi ID: {$productId}");
                return 1;
            }

            $result = $dropiApi->syncProductStock($product);
            if ($result['success']) {
                $this->info($result['message']);
            } else {
                $this->error($result['message']);
            }

            return 0;
        }

        $result = $dropiApi->syncAllProductsStock();

        $this->info("Total productos Dropi: {$result['total_products']}");
        $this->info("Productos sincronizados: {$result['synced_count']}");

        if ($result['out_of_stock_count'] > 0) {
            $this->warn("⚠️ Productos agotados en Dropi: {$result['out_of_stock_count']}");
        }

        $this->table(
            ['ID', 'Nombre', 'Dropi ID', 'Stock Anterior', 'Stock Actual', 'Estado'],
            array_map(function ($item) {
                return [
                    $item['product_id'] ?? '-',
                    $item['name'] ?? '-',
                    $item['dropi_id'] ?? '-',
                    $item['previous_stock'] ?? '-',
                    $item['current_stock'] ?? 0,
                    ($item['is_in_stock'] ?? false) ? 'EN STOCK' : 'AGOTADO',
                ];
            }, $result['details'] ?? [])
        );

        $this->info('Sincronización finalizada con éxito.');
        return 0;
    }
}
