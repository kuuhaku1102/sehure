<?php
if (!defined('ABSPATH')) { exit; }

get_header();

$prefectures = get_prefectures();
$items = kami_import_get_items(null, 12); // 全国からランダムに12件
$count = is_array($items) ? count($items) : 0;
?>
<main class="sefure-wrap">
  <div class="sefure-hero">
    <h1 class="sefure-title">セフレ掲示板</h1>
    <p class="sefure-subtitle">全国47都道府県から安全にセフレを探せる</p>
  </div>

  <section class="prefecture-section">
    <h2 class="section-title">都道府県から探す</h2>
    <div class="prefecture-grid">
      <?php foreach($prefectures as $slug => $name): ?>
        <a href="<?php echo esc_url(home_url('/' . $slug . '/')); ?>" class="prefecture-card">
          <span class="prefecture-name"><?php echo esc_html($name); ?></span>
          <span class="prefecture-arrow">→</span>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

  <?php if ($count > 0): ?>
    <section class="girls-section">
      <h2 class="section-title">最新の女性会員</h2>
      <div class="girls-grid">
        <?php foreach($items as $item): 
          $thumb = kami_import_normalize_url($item->samune ?? '');
          $ext   = esc_url($item->url ?? '');
          $name  = esc_html($item->name ?? '');
          $age   = esc_html($item->age ?? '');
          $figure= esc_html($item->figure ?? '');
          $char  = esc_html($item->character ?? '');
          $comment = esc_html($item->comment ?? '');
        ?>
          <article class="girl-card">
            <?php if ($thumb): ?>
              <img class="girl-thumb" src="<?php echo $thumb; ?>" alt="<?php echo $name; ?>">
            <?php else: ?>
              <div class="girl-thumb girl-thumb-placeholder"></div>
            <?php endif; ?>

            <div class="girl-body">
              <h3 class="girl-name"><?php echo $name; ?><?php if($age !== ''): ?>（<?php echo $age; ?>）<?php endif; ?></h3>
              <div class="girl-meta">
                <?php if ($figure !== ''): ?><span class="girl-tag">体型: <?php echo $figure; ?></span><?php endif; ?>
                <?php if ($char !== ''): ?><span class="girl-tag">タイプ: <?php echo $char; ?></span><?php endif; ?>
              </div>
              <?php if ($comment !== ''): ?><p class="girl-comment"><?php echo $comment; ?></p><?php endif; ?>
            </div>

            <div class="girl-actions">
              <?php if ($ext): ?>
                <a class="girl-btn" href="<?php echo esc_url($ext); ?>" target="_blank" rel="noopener noreferrer">プロフィールを見る</a>
              <?php else: ?>
                <span class="girl-no-link">リンクなし</span>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <section class="content-section">
    <h2 class="section-title">セフレ掲示板とは</h2>
    <div class="content-box">
      <p>セフレ掲示板は、全国47都道府県から安全にセフレを探せる出会いの場を提供しています。各都道府県ごとに地域に特化した出会いの情報を掲載しており、あなたの住んでいる地域で理想のセフレを見つけることができます。</p>
      <p>当サイトでは、安全性を最優先に考え、信頼できる出会い系サービスのみを紹介しています。無料の掲示板とは異なり、年齢確認や本人確認が徹底されたサービスを通じて、安心して出会いを楽しむことができます。</p>
    </div>
  </section>

  <div class="sefure-footer-info">
    <?php echo esc_html(date_i18n('Y年n月j日')); ?> / セフレ掲示板
  </div>
</main>
<?php
get_footer();
