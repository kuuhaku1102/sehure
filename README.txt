Kami Import Top Theme

【目的】
独自テーブル wp_kami_import（prefix対応: {$wpdb->prefix}kami_import）をTOPページ(index.php)で一覧表示するテーマです。

【使い方】
1) /wp-content/themes/ にフォルダごとアップロード
2) WP管理画面 > 外観 > テーマ で「Kami Import Top Theme」を有効化
3) 既存テーマのTOP表示に戻したい場合は、別テーマへ切り替え

【表示件数】
functions.php の filter `kami_import_limit` で変更できます（デフォルト60件）
例: functions.php やプラグインで
add_filter('kami_import_limit', fn() => 120);

【サムネURLについて】
samune が /images/... のような相対パスの場合は site_url() を自動で付与します。
