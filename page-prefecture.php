<?php
/**
 * Template Name: Prefecture Page
 * 都道府県別ページテンプレート
 */

if (!defined('ABSPATH')) { exit; }

get_header();

global $post;
$prefectures = get_prefectures();
$slug = $post->post_name;
$pref_name = isset($prefectures[$slug]) ? $prefectures[$slug] : '';

// ランダムに8-12件表示（都道府県に関係なく全国からランダム）
$random_count = rand(8, 12);
$items = kami_import_get_items($random_count);
$count = is_array($items) ? count($items) : 0;
?>
<main class="sefure-wrap prefecture-page">
  <!-- パンくずリスト -->
  <nav class="breadcrumb">
    <a href="<?php echo esc_url(home_url('/')); ?>">ホーム</a>
    <span class="breadcrumb-separator">›</span>
    <span class="breadcrumb-current"><?php echo esc_html($pref_name); ?>のセフレ掲示板</span>
  </nav>

  <div class="prefecture-hero">
    <h1 class="prefecture-title"><?php echo esc_html($pref_name); ?>のセフレ掲示板</h1>
    <p class="prefecture-subtitle"><?php echo esc_html($pref_name); ?>で安全にセフレを探せる</p>
  </div>

  <?php if ($count > 0): ?>
    <section class="girls-section">
      <h2 class="section-title"><?php echo esc_html($pref_name); ?>の女性会員（<?php echo $count; ?>件）</h2>
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
  <?php else: ?>
    <div class="no-data-message">
      <p><?php echo esc_html($pref_name); ?>の女性会員は現在登録されていません。</p>
      <p><a href="<?php echo esc_url(home_url('/')); ?>">トップページに戻る</a></p>
    </div>
  <?php endif; ?>

  <section class="content-section">
    <h2 class="section-title"><?php echo esc_html($pref_name); ?>でセフレを作る方法</h2>
    <div class="content-box">
      <p><?php echo esc_html($pref_name); ?>でセフレを探すなら、安全性の高い出会い系サービスを利用することが重要です。無料の掲示板とは異なり、年齢確認や本人確認が徹底されたサービスを通じて、安心して出会いを楽しむことができます。</p>
      <p><?php echo esc_html($pref_name); ?>には多くの出会いを求める女性がいます。当サイトでは、<?php echo esc_html($pref_name); ?>で実際に出会える信頼できるサービスのみを紹介しています。</p>
      
      <h3>安全にセフレを作るためのポイント</h3>
      <ul>
        <li>年齢確認・本人確認が徹底されたサービスを利用する</li>
        <li>プロフィールを丁寧に作成し、誠実な印象を与える</li>
        <li>メッセージは丁寧に、相手を尊重する姿勢を忘れない</li>
        <li>初回は人目のある場所で短時間会う</li>
        <li>お互いの合意を確認してから関係を進める</li>
      </ul>
    </div>
  </section>

  <section class="related-prefectures">
    <h2 class="section-title">他の都道府県から探す</h2>
    <div class="prefecture-grid">
      <?php 
      // ランダムに6つの都道府県を表示
      $random_prefs = array_rand($prefectures, min(6, count($prefectures)));
      if (!is_array($random_prefs)) $random_prefs = array($random_prefs);
      
      foreach($random_prefs as $key): 
        if ($key === $slug) continue; // 現在のページは除外
        $other_slug = array_keys($prefectures)[$key];
        $other_name = $prefectures[$other_slug];
      ?>
        <a href="<?php echo esc_url(home_url('/' . $other_slug . '/')); ?>" class="prefecture-card">
          <span class="prefecture-name"><?php echo esc_html($other_name); ?></span>
          <span class="prefecture-arrow">→</span>
        </a>
      <?php endforeach; ?>
    </div>
    <div class="back-to-top">
      <a href="<?php echo esc_url(home_url('/')); ?>" class="btn-back">全都道府県を見る</a>
    </div>
  </section>

  <div class="sefure-footer-info">
    <?php echo esc_html(date_i18n('Y年n月j日')); ?> / <?php echo esc_html($pref_name); ?>のセフレ掲示板
  </div>
</main>
<?php
get_footer();
