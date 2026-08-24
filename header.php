<?php if (!defined('ABSPATH')) { exit; } ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#06060c">
  <link rel="profile" href="https://gmpg.org/xfn/11">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- 背景アニメーション -->
<div class="tmf-bg" aria-hidden="true">
  <canvas id="tmf-particles"></canvas>
  <div class="tmf-bg-grid"></div>
  <div class="tmf-bg-glow tmf-bg-glow--1"></div>
  <div class="tmf-bg-glow tmf-bg-glow--2"></div>
  <div class="tmf-scanline"></div>
</div>

<a class="tmf-skip" href="#tmf-main">本文へスキップ</a>

<header class="tmf-header" id="tmf-header">
  <div class="tmf-header__inner">
    <div class="tmf-brand">
      <?php if (has_custom_logo()) : ?>
        <?php the_custom_logo(); ?>
      <?php else : ?>
        <a class="tmf-brand__text" href="<?php echo esc_url(home_url('/')); ?>">
          <span class="tmf-brand__mark">◆</span>
          <span class="tmf-brand__name"><?php echo esc_html(tmf_opt('tmf_shop_name', 'TOREKAMAFIA')); ?></span>
        </a>
      <?php endif; ?>
    </div>

    <nav class="tmf-nav" id="tmf-nav" aria-label="メインナビゲーション">
      <?php
      if (has_nav_menu('primary')) {
          wp_nav_menu(array(
              'theme_location' => 'primary',
              'container'      => false,
              'menu_class'     => 'tmf-nav__list',
              'fallback_cb'    => false,
              'depth'          => 1,
          ));
      } else {
          echo '<ul class="tmf-nav__list">';
          echo '<li><a href="' . esc_url(home_url('/#strength')) . '">選ばれる理由</a></li>';
          echo '<li><a href="' . esc_url(home_url('/#category')) . '">買取カテゴリー</a></li>';
          echo '<li><a href="' . esc_url(home_url('/#kaitori')) . '">強化買取</a></li>';
          echo '<li><a href="' . esc_url(home_url('/#flow')) . '">買取の流れ</a></li>';
          if (function_exists('tmf_souba_url') && ($su = tmf_souba_url())) {
              echo '<li><a href="' . esc_url($su) . '">相場検索</a></li>';
          }
          echo '<li><a href="' . esc_url(home_url('/#news')) . '">お知らせ</a></li>';
          echo '<li><a href="' . esc_url(home_url('/#access')) . '">店舗情報</a></li>';
          echo '</ul>';
      }
      ?>
      <?php if (tmf_opt('tmf_line')) : ?>
        <a class="tmf-nav__cta" href="<?php echo esc_url(tmf_opt('tmf_line')); ?>" target="_blank" rel="noopener">LINE査定</a>
      <?php endif; ?>
    </nav>

    <button class="tmf-burger" id="tmf-burger" aria-label="メニューを開く" aria-expanded="false" aria-controls="tmf-nav">
      <span></span><span></span><span></span>
    </button>
  </div>
</header>

<main id="tmf-main" class="tmf-main">
