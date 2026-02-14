<?php
if (!defined('ABSPATH')) { exit; }

get_header();

$items = function_exists('kami_import_get_items') ? kami_import_get_items() : [];
$count = is_array($items) ? count($items) : 0;
?>
<main class="kami-wrap">
  <div class="kami-header">
    <div>
      <h1 class="kami-title"><?php echo esc_html(get_bloginfo('name')); ?></h1>
      <p class="kami-sub">データベース（wp_kami_import）から表示中：<?php echo esc_html($count); ?>件</p>
    </div>
  </div>

  <?php if (!$count): ?>
    <div class="kami-card" style="padding:16px;">
      <p style="margin:0;color:#374151;">
        表示できるデータがありません。<br>
        ・テーブル名が <code>wp_kami_import</code>（prefixが変わっている場合は <code>{$wpdb->prefix}kami_import</code>）になっているか<br>
        ・<code>post_status</code> が <code>publish</code> の行があるか<br>
        を確認してください。
      </p>
    </div>
  <?php else: ?>
    <section class="kami-grid" aria-label="kami list">
      <?php foreach($items as $item): 
        $thumb = kami_import_normalize_url($item->samune ?? '');
        $ext   = esc_url($item->url ?? '');
        $name  = esc_html($item->name ?? '');
        $age   = esc_html($item->age ?? '');
        $figure= esc_html($item->figure ?? '');
        $char  = esc_html($item->character ?? '');
        $comment = esc_html($item->comment ?? '');
      ?>
        <article class="kami-card">
          <?php if ($thumb): ?>
            <img class="kami-thumb" src="<?php echo $thumb; ?>" alt="<?php echo $name; ?>">
          <?php else: ?>
            <div class="kami-thumb" aria-hidden="true"></div>
          <?php endif; ?>

          <div class="kami-body">
            <h2 class="kami-name"><?php echo $name; ?><?php if($age !== ''): ?>（<?php echo $age; ?>）<?php endif; ?></h2>
            <div class="kami-meta">
              <?php if ($figure !== ''): ?><span class="kami-pill">体型: <?php echo $figure; ?></span><?php endif; ?>
              <?php if ($char !== ''): ?><span class="kami-pill">タイプ: <?php echo $char; ?></span><?php endif; ?>
            </div>
            <?php if ($comment !== ''): ?><p class="kami-comment"><?php echo $comment; ?></p><?php endif; ?>
          </div>

          <div class="kami-actions">
            <?php if ($ext): ?>
              <a class="kami-btn" href="<?php echo esc_url($ext); ?>" target="_blank" rel="noopener noreferrer">外部リンク</a>
            <?php else: ?>
              <span class="kami-sub">リンクなし</span>
            <?php endif; ?>
            <span class="kami-sub">ID: <?php echo esc_html($item->id ?? ''); ?></span>
          </div>
        </article>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>

  <div class="kami-footer">
    <?php echo esc_html(date_i18n('Y年n月j日')); ?> / Kami Import Top Theme
    <!-- 🚀 デプロイテスト: <?php echo date('Y-m-d H:i:s'); ?> -->
  </div>
</main>
<?php
get_footer();
