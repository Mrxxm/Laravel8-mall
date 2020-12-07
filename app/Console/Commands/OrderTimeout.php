<?php


namespace App\Console\Commands;


use App\Services\Impl\OrderServiceImpl;
use Illuminate\Console\Command;

class OrderTimeout extends Command
{
    /**
     * The name and signature of the console command.
     *用来描述命令的名字与参数
     * @var string
     */
    protected $signature = 'order:timeout';

    /**
     * The console command description.
     *存储命令描述
     * @var string
     */
    protected $description = 'Handle order timeout';

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
     *执行命令
     * @return mixed
     */
    public function handle()
    {
        $obj = new OrderServiceImpl();
        $result = true;
        while ($result) {
            $result = $obj->checkOrderStatus();
            sleep(2);
        }
    }
}
