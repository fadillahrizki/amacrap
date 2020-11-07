<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use KubAT\PhpSimple\HtmlDomParser;
use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;

class Amazon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amazon:scrap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $link = "https://www.amazon.com/s?i=specialty-aps&bbn=16225007011&rh=n%3A16225007011%2Cn%3A13896617011&ref=nav_em__nav_desktop_sa_intl_computers_tablets_0_2_6_4";

        $html = HtmlDomParser::file_get_html($link);

        $this->scrap($html);
    }
    
    public function scrap($html){

        $data = json_decode(file_get_contents(public_path('data.json')));

        foreach($html->find(".s-result-item.s-asin") as $item){
    
            // $detail_link = "https://www.amazon.com".$item->find('a',0)->href;

            // $puppeteer = new Puppeteer();

            // $browser = $puppeteer->launch([
            //     'args' => [
            //         '--proxy-server='.config('app.proxy'),
            //     ],
            //     'headless'=>true
            // ]);

            // $page = $browser->newPage();

            // $page->setDefaultNavigationTimeout(0);

            // $page->goto($detail_link, ["waitUntil" => "load"]);

            // $product = $page->evaluate(JsFunction::createWithBody("
            //     let title = document.querySelector('#title')
            //     let price = document.querySelector('#price')

            //     return {
            //         title:title.innerText.trim(),
            //         price:price.innerText.trim(),
            //     }
            // "));

            $product['title'] = trim($item->find('h2 > a',0)->text());
            $product['asin'] = $item->attr['data-asin'];
            $product['uuid'] = $item->attr['data-uuid'];
            
            $price = $item->find('.a-offscreen',0);

            if($price){
                $product['price'] = $price->text();

                // $price= explode('+',$product['price']);
    
                // $price = $price[0];
    
                // $price = explode(':',$price);
    
                $price = str_replace('$','',$product['price']);
    
                $product['price'] = trim($price);

            }else{
                $product['price'] = 0;
            }


            $data[] = $product;

            // print_r($product);

            $this->info("Success create: ".$product['title']);

            // return;

        }

        file_put_contents(public_path('data.json'),json_encode($data));

        $next = $html->find('.a-pagination .a-last a',0);

        if($next){
            $link = "https://www.amazon.com$next->href";
            $next_html = HtmlDomParser::file_get_html($link);
            $this->scrap($next_html);
        }

    }
}
