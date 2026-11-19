<?php
if (!defined('ABSPATH')) exit;

final class CTTCM_Shortcodes {
    public static function init(): void {
        add_shortcode('clicktt_mannschaften',[__CLASS__,'teams']);
        add_shortcode('clicktt_tabelle',[__CLASS__,'standings']);
        add_shortcode('clicktt_spiele',[__CLASS__,'matches']);
        add_shortcode('clicktt_naechste_spiele',[__CLASS__,'upcoming']);
        add_shortcode('clicktt_ergebnisse',[__CLASS__,'results']);
    }
    private static function begin(): void { wp_enqueue_style('cttcm-frontend'); }
    private static function msg(string $m): string { return '<div class="cttcm-message">'.esc_html($m).'</div>'; }
    private static function table(array $t, int $limit=0, string $club=''): string {
        if(empty($t['headers'])||empty($t['rows'])) return self::msg('Keine Daten verfügbar.');
        $rows=$limit>0?array_slice($t['rows'],0,$limit):$t['rows']; ob_start(); ?>
        <div class="cttcm-scroll"><table class="cttcm-table"><thead><tr><?php foreach($t['headers'] as $h): ?><th><?php echo esc_html($h); ?></th><?php endforeach; ?></tr></thead><tbody>
        <?php foreach($rows as $r): $own=$club && stripos(implode(' ',$r),$club)!==false; ?><tr<?php echo $own?' class="cttcm-own-team"':''; ?>><?php foreach($r as $c): ?><td><?php echo esc_html($c); ?></td><?php endforeach; ?></tr><?php endforeach; ?>
        </tbody></table></div><?php return ob_get_clean();
    }
    public static function teams($atts): string { self::begin(); $a=shortcode_atts(['anzahl'=>0],$atts); $s=CTTCM_Service::settings(); return self::table((new CTTCM_Service())->team_overview(),(int)$a['anzahl'],$s['club_name']); }
    public static function standings($atts): string { self::begin(); $a=shortcode_atts(['team'=>'','anzahl'=>0],$atts); $x=(new CTTCM_Service())->table_for(sanitize_text_field($a['team']),'standings'); return $x?self::table($x['table'],(int)$a['anzahl'],CTTCM_Service::settings()['club_name']):self::msg('Keine passende Tabelle gefunden.'); }
    public static function matches($atts): string { self::begin(); $a=shortcode_atts(['team'=>'','anzahl'=>0],$atts); $x=(new CTTCM_Service())->table_for(sanitize_text_field($a['team']),'matches'); return $x?self::table($x['table'],(int)$a['anzahl'],CTTCM_Service::settings()['club_name']):self::msg('Kein passender Spielplan gefunden.'); }
    public static function upcoming($atts): string { return self::filtered_matches($atts,true); }
    public static function results($atts): string { return self::filtered_matches($atts,false); }
    private static function filtered_matches($atts,bool $future): string {
        self::begin(); $a=shortcode_atts(['team'=>'','anzahl'=>5],$atts); $x=(new CTTCM_Service())->table_for(sanitize_text_field($a['team']),'matches'); if(!$x)return self::msg('Kein passender Spielplan gefunden.');
        $today=current_time('timestamp'); $rows=[];
        foreach($x['table']['rows'] as $r){$joined=implode(' ',$r); $ts=self::date_from($joined); if(!$ts)continue; if(($future&&$ts>=$today)||(!$future&&$ts<$today))$rows[]=['ts'=>$ts,'row'=>$r];}
        usort($rows,fn($a,$b)=>$future?($a['ts']<=>$b['ts']):($b['ts']<=>$a['ts']));
        $t=$x['table']; $t['rows']=array_column(array_slice($rows,0,max(1,(int)$a['anzahl'])),'row'); return self::table($t,0,CTTCM_Service::settings()['club_name']);
    }
    private static function date_from(string $s): int {
        if(preg_match('/\b(\d{1,2})\.(\d{1,2})\.(\d{2,4})\b/',$s,$m)){ $y=(int)$m[3]; if($y<100)$y+=2000; return mktime(12,0,0,(int)$m[2],(int)$m[1],$y); }
        return 0;
    }
}
