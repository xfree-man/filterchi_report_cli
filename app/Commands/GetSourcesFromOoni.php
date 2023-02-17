<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Http;
use LaravelZero\Framework\Commands\Command;
use OpenSpout\Writer\CSV\Writer;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Symfony\Component\DomCrawler\Crawler;

class GetSourcesFromOoni extends Command
{
    protected $signature = "ooni:get";
    protected $description = "Get sources from onni";
    const OONI_START_API_ENDPOINT = "https://api.ooni.io/api/v1/measurements?probe_cc=IR&confirmed=true&offset=0&limit=100";

    public function handle()
    {
        $csvFile = SimpleExcelWriter::create("ooni.csv")->addHeader([
            "domain",
            "filter",
            "measurement_start_time",
            "source",
//            "External links"
        ]);
        $this->info("getting results from ooni");
        $result = Http::get(self::OONI_START_API_ENDPOINT)->json();
        $i = 1;
        while (!empty($result["metadata"]["next_url"])) {
            $this->info("fetched until: " . $i * 100);
            $csvFile = $csvFile->addRows($this->getRows($result["results"]));
            $result = Http::get($result["metadata"]["next_url"])->json();
            $i++;
        }
        $this->info("Done.");
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }

    protected function getRows($results)
    {
        return collect($results)
            ->map(
                function($r) {
                    $domain=str_ireplace(
                        "www.",
                        "",
                        parse_url($r["input"], PHP_URL_HOST)
                    );
                    return [
                        "domain" => $domain,
                        "filter" => $r["anomaly"],
                        "measurement_start_time" => $r["measurement_start_time"],
                        "source" => "ooni.com",
//                        "External links"=>implode('-',$this->getExternals($domain))
                    ];
                }
            )
            ->toArray();
    }
}
