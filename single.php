<?php
/**
 * 単一記事（お知らせ・投稿など）
 * @package Torekamafia_Yamaguchi
 */
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<div class="tmf-page">
  <nav class="tmf-breadcrumb" aria-label="パンくず">
    <a href="<?php echo esc_url(home_url('/')); ?>">ホーム</a>
    <?php
    $pt = get_post_type();
    if ($pt !== 'post' && ($archive = get_post_type_archive_link($pt))) {
      $obj = get_post_type_object($pt);
      echo ' / <a href="' . esc_url($archive) . '">' . esc_html($obj->labels->name) . '</a>';
    }
    ?>
    / <span><?php the_title(); ?></span>
  </nav>

  <?php while (have_posts()) : the_post(); ?>
    <article class="tmf-article reveal">
      <header class="tmf-page__head" style="text-align:left;margin-bottom:30px">
        <p class="tmf-hero__kicker" style="margin-bottom:16px"><?php echo esc_html(get_the_date('Y.m.d')); ?></p>
        <h1 class="tmf-page__title" style="font-size:clamp(24px,4vw,38px)"><?php the_title(); ?></h1>
      </header>

      <?php if (has_post_thumbnail()) : ?>
        <div class="tmf-article__thumb"><?php the_post_thumbnail('large'); ?></div>
      <?php endif; ?>

      <div class="tmf-content">
        <?php the_content(); wp_link_pages(); ?>
      </div>
    </article>
  <?php endwhile; ?>

  <div class="tmf-backlink">
    <a class="tmf-btn tmf-btn--ghost" href="<?php echo esc_url(get_post_type_archive_link(get_post_type()) ?: home_url('/')); ?>"><span>一覧へ戻る</span></a>
  </div>
</div>
<?php get_footer(); ?>
