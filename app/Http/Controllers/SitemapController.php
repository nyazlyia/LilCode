<?php

namespace App\Http\Controllers;

use App\Services\LevelCatalog;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(LevelCatalog $catalog): Response
    {
        $urls = [
            [route('home'), '1.0'],
            [route('map'), '0.9'],
            [route('guide'), '0.6'],
            [route('badges'), '0.5'],
        ];
        foreach ($catalog->summary() as $level) {
            $urls[] = [$level['url'], '0.8'];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as [$loc, $priority]) {
            $xml .= '  <url><loc>'.htmlspecialchars($loc, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</loc>'
                .'<priority>'.$priority.'</priority></url>'."\n";
        }
        $xml .= '</urlset>'."\n";

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
