<?php
if (!defined('ABSPATH')) exit;

final class CTTCM_Service {
    private CTTCM_Fetcher $fetcher; private CTTCM_Parser $parser;
    public function __construct(){ $this->fetcher=new CTTCM_Fetcher(); $this->parser=new CTTCM_Parser(); }
    public static function settings(): array { return wp_parse_args(get_option('cttcm_settings',[]), ['club_name'=>'','club_url'=>'','cache_minutes'=>30,'accent_mode'=>'elementor','accent_color'=>'#1b4f91','manual_sources'=>'']); }
    private function key(string $url): string { return 'cttcm_' . md5($url); }
    public function clear_cache(): void { global $wpdb; $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_cttcm_%' OR option_name LIKE '_transient_timeout_cttcm_%'"); }
    public function source(string $url, bool $force=false): array {
        $key=$this->key($url); if(!$force){$cached=get_transient($key); if(is_array($cached))return $cached;}
        $old=get_option($key.'_stale'); $r=$this->fetcher->get($url);
        if(!$r['ok']) { if(is_array($old)){ $old['stale']=true; $old['warning']=$r['error']; return $old; } return $r; }
        $data=['ok'=>true,'url'=>$url,'fetched_at'=>time(),'teams'=>$this->parser->discover_teams($r['body'],$url),'tables'=>$this->parser->tables($r['body'])];
        $min=max(5,min(1440,(int)self::settings()['cache_minutes'])); set_transient($key,$data,$min*MINUTE_IN_SECONDS); update_option($key.'_stale',$data,false); return $data;
    }
    public function configured_sources(): array {
        $s=self::settings(); $sources=[];
        if($s['club_url']) $sources[]=['name'=>$s['club_name'] ?: 'Verein','url'=>$s['club_url']];
        foreach(preg_split('/\R/',(string)$s['manual_sources']) as $line){
            $line=trim($line); if(!$line)continue; $parts=array_map('trim',explode('|',$line,2));
            $url=count($parts)===2?$parts[1]:$parts[0]; $name=count($parts)===2?$parts[0]:'Quelle'; if($url)$sources[]=['name'=>$name,'url'=>$url];
        }
        return $sources;
    }
    public function all_sources(): array {
        $sources=$this->configured_sources(); $club=self::settings()['club_url'];
        if($club){$d=$this->source($club); if(!empty($d['teams'])) foreach($d['teams'] as $t)$sources[]=$t;}
        $unique=[]; foreach($sources as $s)$unique[$s['url']]=$s; return array_values($unique);
    }
    public function table_for(string $team, string $kind): ?array {
        foreach($this->all_sources() as $src){
            if($team && stripos($src['name'],$team)===false && stripos($src['url'],$team)===false) continue;
            $d=$this->source($src['url']); if(empty($d['tables']))continue;
            foreach($d['tables'] as $t) if($this->parser->classify($t)===$kind) return ['source'=>$src,'table'=>$t,'meta'=>$d];
        }
        return null;
    }
    public function team_overview(): array {
        $s=self::settings(); if(!$s['club_url'])return []; $d=$this->source($s['club_url']);
        foreach(($d['tables']??[]) as $t) if($this->parser->classify($t)==='teams') return $t;
        if(!empty($d['teams'])) return ['headers'=>['Mannschaft','Liga/Quelle'],'rows'=>array_map(fn($x)=>[$x['name'],$x['url']],$d['teams'])];
        return [];
    }
}
