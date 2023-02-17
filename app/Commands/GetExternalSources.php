<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Http;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\DomCrawler\Crawler;

class GetExternalSources extends Command
{
    protected $signature = "get:externals";
    protected $description = "list of external domains";

    public function handle()
    {
        // Parse the URL and get the domain name
        $domain = parse_url("https://twitter.com", PHP_URL_HOST);

        // Make a request to the website
        $response = Http::get("https://twitter.com");

        // Check if request was successful
        if ($response->ok()) {
            // Parse the HTML with DomCrawler
            $crawler = new Crawler($response->body());

            // Find all links in the HTML, including links in CSS and JavaScript
            $links = $crawler->filter(
                "a, link, script, img, video, audio, source"
            );

            // Initialize a list to store external domains
            $external_domains = [];

            // Loop through links to check for external domains
            foreach ($links as $link) {
                // Get the src or href attribute of the link
                if ($link->nodeName == "a") {
                    $href = $link->getAttribute("href");
                } else {
                    $href = $link->getAttribute("src");
                }

                // Check if the attribute is not empty and starts with http or https
                if (
                    !empty($href) &&
                    (strpos($href, "http://") === 0 ||
                        strpos($href, "https://") === 0)
                ) {
                    // Parse the URL of the link and get the domain name
                    $link_domain = $this->normalizeDomain($href);
                    // Check if the domain name of the link is different from the original domain
                    if ($link_domain !== $domain) {
                        // Add the external domain to the list
                        $external_domains[] = $link_domain;
                    }
                }
            }

            // Remove duplicate domains from the list
            $external_domains = array_unique($external_domains);

            dd($external_domains);
        } else {
            // Return null if request was not successful
            $this->error("Error in open the site.");
            return -1;
        }
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

    protected function normalizeDomain($href): string|array
    {
        return str_ireplace(
            "www.",
            "",
            parse_url(strtolower($href), PHP_URL_HOST)
        );
    }
}
