<?php
/**
 * 固定ページ
 * @package Torekamafia_Yamaguchi
 */
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<div class="tmf-page">
  <div class="tmf-container">
    <?php while (have_posts()) : the_post(); ?>
      <header class="tmf-page__head reveal">
        <span class="tmf-eyebrow" style="display:inline-flex">Page</span>
        <h1 class="tmf-page__title"><?php the_title(); ?></h1>
      </header>
      <article class="tmf-article reveal">
        <?php if (has_post_thumbnail()) : ?>
          <div class="tmf-article__thumb"><?php the_post_thumbnail('large'); ?></div>
        <?php endif; ?>
        <div class="tmf-content">
          <?php the_content(); wp_link_pages(); ?>
        </div>
      </article>
    <?php endwhile; ?>
    <div class="tmf-backlink">
      <a class="tmf-btn tmf-btn--ghost" href="<?php echo esc_url(home_url('/')); ?>"><span>トップへ戻る</span></a>
    </div>
  </div>
</div>
<?php get_footer(); ?>
