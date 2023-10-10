<?php

namespace App\Console\Commands;

use App\Models\Usermodel;
use Carbon\Carbon;
use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class StatusUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'status:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Status update successfully';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {


        $user_last_status_update = Usermodel::where('online_status', 1)->get();
        // dd($user_last_status_update);
        // ->update(['online_status'=> 0, 'last_status_update'=>Carbon::now()->toDateTimeString()])
        foreach ($user_last_status_update as $key => $value) {
            $time_new = Carbon::parse(date('Y-m-d H:i:s'));
            $time = Carbon::parse($value['last_status_update']);
            $time_after_five = $time->addMinutes(5);
            if ($time_new > $time_after_five) {
                Usermodel::where('id', $value['id'])->update(['online_status'=> 0, 'last_status_update'=>Carbon::now()->toDateTimeString()]);
                Log::info("one user offline");
            }
            Log::info("successfull");
        }
        Log::info("successfull");
        $this->info('Status updated successfully.');
    }
}
