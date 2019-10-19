<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HealthCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'health:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'You can check the application state. If it is operating normally, return exit code 0. otherwise 1.';

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
     * @return mixed
     */
    public function handle()
    {
        $code = 0;
        $v = [];
        $vv = [];
        $vvv = [];

        try {
            DB::table('migrations')->select('*')->limit(0)->get();
        } catch (\Throwable $e) {
            $code = 1;
            $v[] = "Failed to establishing a database connection.";
            $vv[] = "Failed to establishing a database connection.";
            $vvv[] = "Failed to establishing a database connection.";
            $vv[] = $e->getMessage();
            $vvv[] = $e;
        }

        if($this->output->isDebug()){
            $this->error(implode("\n", $vvv));
        }if($this->output->isVeryVerbose()){
            $this->error(implode("\n", $vv));
        }else if($this->output->isVerbose()){
            $this->error(implode("\n", $v));
        }

        if($this->output->isVerbose()){
            $this->warn("Exit code: " . $code);
        }

        return $code;
    }
}
