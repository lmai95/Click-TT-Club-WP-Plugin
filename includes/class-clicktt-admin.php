<?php
if (!defined('ABSPATH')) exit;

final class CTTCM_Admin {
    public static function init(): void { add_action('admin_menu',[__CLASS__,'menu']); add_action('admin_init',[__CLASS__,'register']); add_action('admin_post_cttcm_clear_cache',[__CLASS__,'clear']); }
    public static function menu(): void { add_options_page('Click-TT','Click-TT','manage_options','cttcm',[__CLASS__,'page']); }
    public static function register(): void { register_setting('cttcm','cttcm_settings',['sanitize_callback'=>[__CLASS__,'sanitize']]); }
    public static function sanitize($in): array {
        return [
            'club_name'=>sanitize_text_field($in['club_name']??''),
            'club_url'=>esc_url_raw($in['club_url']??''),
            'cache_minutes'=>max(5,min(1440,(int)($in['cache_minutes']??30))),
            'accent_mode'=>in_array(($in['accent_mode']??''),['elementor','custom'],true)?$in['accent_mode']:'elementor',
            'accent_color'=>sanitize_hex_color($in['accent_color']??'#1b4f91') ?: '#1b4f91',
            'manual_sources'=>sanitize_textarea_field($in['manual_sources']??''),
        ];
    }
    public static function clear(): void { if(!current_user_can('manage_options'))wp_die('Keine Berechtigung.'); check_admin_referer('cttcm_clear'); (new CTTCM_Service())->clear_cache(); wp_safe_redirect(add_query_arg(['page'=>'cttcm','cache'=>'cleared'],admin_url('options-general.php'))); exit; }
    public static function page(): void { if(!current_user_can('manage_options'))return; $s=CTTCM_Service::settings(); ?>
    <div class="wrap"><h1>Click-TT Club Manager</h1><?php if(isset($_GET['cache']))echo '<div class="notice notice-success"><p>Cache wurde geleert.</p></div>'; ?>
    <form method="post" action="options.php"><?php settings_fields('cttcm'); ?><table class="form-table">
    <tr><th><label for="club_name">Vereinsname</label></th><td><input class="regular-text" id="club_name" name="cttcm_settings[club_name]" value="<?php echo esc_attr($s['club_name']); ?>"></td></tr>
    <tr><th><label for="club_url">Vereinsseite</label></th><td><input class="large-text" type="url" id="club_url" name="cttcm_settings[club_url]" value="<?php echo esc_attr($s['club_url']); ?>"><p class="description">Öffentliche myTischtennis-Mannschaftsübersicht.</p></td></tr>
    <tr><th><label for="cache">Cache in Minuten</label></th><td><input type="number" min="5" max="1440" id="cache" name="cttcm_settings[cache_minutes]" value="<?php echo esc_attr($s['cache_minutes']); ?>"></td></tr>
    <tr><th>Akzentfarbe</th><td><select name="cttcm_settings[accent_mode]"><option value="elementor" <?php selected($s['accent_mode'],'elementor'); ?>>Elementor Global Accent</option><option value="custom" <?php selected($s['accent_mode'],'custom'); ?>>Eigene Farbe</option></select> <input type="color" name="cttcm_settings[accent_color]" value="<?php echo esc_attr($s['accent_color']); ?>"></td></tr>
    <tr><th><label for="manual">Manuelle Quellen</label></th><td><textarea class="large-text code" rows="8" id="manual" name="cttcm_settings[manual_sources]"><?php echo esc_textarea($s['manual_sources']); ?></textarea><p class="description">Eine Zeile je Quelle: Anzeigename | vollständige URL. Dient als Fallback, wenn die automatische Erkennung nicht reicht.</p></td></tr>
    </table><?php submit_button(); ?></form>
    <hr><h2>Shortcodes</h2><code>[clicktt_mannschaften]</code><br><code>[clicktt_tabelle team="Erwachsene II"]</code><br><code>[clicktt_spiele team="Erwachsene III" anzahl="10"]</code><br><code>[clicktt_naechste_spiele team="Erwachsene" anzahl="5"]</code><br><code>[clicktt_ergebnisse team="Erwachsene" anzahl="5"]</code>
    <p><a class="button" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=cttcm_clear_cache'),'cttcm_clear')); ?>">Cache leeren</a></p></div><?php }
}
