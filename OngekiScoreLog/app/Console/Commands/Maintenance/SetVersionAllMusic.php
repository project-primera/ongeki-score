<?php

namespace App\Console\Commands\Maintenance;

use Illuminate\Console\Command;
use App\MusicData;

class SetVersionAllMusic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Maintenance:SetVersionAllMusic {version}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'If exist level, set "normal_added_version" and "lunatic_added_version" of "music_datas" to argument';

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
        $music = MusicData::All();
        foreach ($music as $key => $value) {
            if(!is_null($value->basic_level) && is_null($value->normal_added_version)){
                $music[$key]->normal_added_version = $this->argument("version");
                $music[$key]->save();
            }
            if(!is_null($value->lunatic_level) && is_null($value->lunatic_added_version)){
                $music[$key]->lunatic_added_version = $this->argument("version");
                $music[$key]->save();
            }
        }
    }
}
