<?php
if (!defined('ABSPATH')) exit;

final class CTTCM_Parser {
    private function dom(string $html): DOMDocument {
        $dom = new DOMDocument();
        $old = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors(); libxml_use_internal_errors($old);
        return $dom;
    }
    private function text(?DOMNode $node): string {
        return $node ? trim(preg_replace('/\s+/u', ' ', html_entity_decode($node->textContent, ENT_QUOTES | ENT_HTML5, 'UTF-8'))) : '';
    }
    private function absolute(string $href, string $base): string {
        if (preg_match('~^https?://~i', $href)) return $href;
        $p = wp_parse_url($base); $origin = ($p['scheme'] ?? 'https').'://'.($p['host'] ?? 'www.mytischtennis.de');
        if (str_starts_with($href, '/')) return $origin.$href;
        $dir = preg_replace('~/[^/]*$~', '/', (string)($p['path'] ?? '/'));
        return $origin.$dir.$href;
    }
    public function discover_teams(string $html, string $base_url): array {
        $dom=$this->dom($html); $xp=new DOMXPath($dom); $teams=[];
        foreach ($xp->query('//a[@href]') as $a) {
            $href=$a->getAttribute('href'); $label=$this->text($a);
            if (!$label || !preg_match('~/gruppe/\d+/(?:tabelle|spielplan)/~', $href)) continue;
            $url=$this->absolute($href,$base_url);
            $teams[$url]=['name'=>$label,'url'=>$url];
        }
        return array_values($teams);
    }
    public function tables(string $html): array {
        $dom=$this->dom($html); $xp=new DOMXPath($dom); $out=[];
        foreach ($xp->query('//table') as $table) {
            $headers=[]; $rows=[];
            $ths=$xp->query('.//thead//th | .//tr[1]/th', $table);
            foreach ($ths as $th) $headers[]=$this->text($th);
            foreach ($xp->query('.//tbody/tr', $table) as $tr) {
                $cells=[];
                foreach ($xp->query('./th|./td', $tr) as $cell) $cells[]=$this->text($cell);
                if ($cells) $rows[]=$cells;
            }
            if (!$rows) {
                $trs=$xp->query('.//tr[position()>1]', $table);
                foreach ($trs as $tr) { $cells=[]; foreach ($xp->query('./th|./td',$tr) as $c) $cells[]=$this->text($c); if($cells)$rows[]=$cells; }
            }
            if ($headers && $rows) $out[]=['headers'=>$headers,'rows'=>$rows];
        }
        return $out;
    }
    public function classify(array $table): string {
        $h=mb_strtolower(implode('|',$table['headers']));
        if (str_contains($h,'mannschaft') && (str_contains($h,'punkte') || str_contains($h,'rang'))) return 'standings';
        if ((str_contains($h,'heimmannschaft') || str_contains($h,'heim')) && (str_contains($h,'gastmannschaft') || str_contains($h,'gast'))) return 'matches';
        if (str_contains($h,'liga') && str_contains($h,'termine')) return 'teams';
        return 'other';
    }
}
