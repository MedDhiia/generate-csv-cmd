<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateCsvCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:generatecsv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loading Data From the API and Generate a CSV File';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //get all the data
        $customers = $this->getCustomers();
        $orders = $this->getOrders();
        $items = $this->getItems();

        // Create a Collections so that i can use the collecion methods that laravel provides
        $customers = collect($customers)->groupBy('addresses.*.type')->map(function ($customers) {
            return $customers->groupBy('id');
        })->toArray();
        $orders = collect($orders)->groupBy('id')->toArray();

        
        // Here i'm trying to filter the data and creating an array as result who contains the needs ()
        $data = array_map(function ($item) use ($customers, $orders) {

            // just to make function return looks pretty
            $itemId = $item['id'];
            $orderId = $item['orderId'];
            $order = $orders[$orderId][0];
            $customerId = $orders[$orderId][0]['customerId'];
            $customer = $customers['shipping'][$customerId][0];

            // get the shipping address not the billing one
            $addresses = collect($customer['addresses'])->groupBy('type')->toArray();
            
            $address = $addresses['shipping'][0] ;

            return [
                'orderID' => $order['id'],
                'orderDate' => $order['createdAt'],
                'orderItemID' => $itemId,
                'orderItemName' => $item['name'],
                'orderItemQuantity' => $item['quantity'],
                'customerFirstName' => $customer['firstName'],
                'customerLastName' => $customer['lastName'],
                'customerAddress' => $address['address'],
                'customerCity' => $address['city'],
                'customerZipCode' => $address['zip'],
                'customerEmail' => $customer['email'],
            ];
        }, $items);

        // generating the csv file
        $this->dataToCsv($data);
        
        return   $this->info('The command was successful!');
    }

    private function getCustomers() : array
    {
        return json_decode(file_get_contents("https://storage.googleapis.com/neta-interviews/MJZkEW3a8wmunaLv/customers.json"), true);
    }

    private function getOrders() : array
    {
        return json_decode(file_get_contents("https://storage.googleapis.com/neta-interviews/MJZkEW3a8wmunaLv/orders.json"), true);
    }

    private function getItems() : array
    {
        return json_decode(file_get_contents("https://storage.googleapis.com/neta-interviews/MJZkEW3a8wmunaLv/items.json"), true);
    }

    private function dataToCsv(array $datas) : void
    {
        if (!File::exists(public_path()."/files")) {
            File::makeDirectory(public_path() . "/files");
        }

        $filename =  public_path("files/shipping".date('YmdHms').".csv");

        $handle = fopen($filename, 'w');

        fputcsv($handle, array_keys($datas[0]));

        foreach ($datas as $data) {
            fputcsv($handle, $data);
        }

        fclose($handle);
    }
}
