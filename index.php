<?php
/**
 * 汎用フォールバックテンプレート（ブログ一覧・検索・その他）
 * @package Torekamafia_Yamaguchi
 */
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<div class="tmf-page">
  <div class="tmf-container">
    <header class="tmf-page__head reveal">
      <h1 class="tmf-page__title">
        <?php
        if (is_search()) {
          printf('「%s」の検索結果', esc_html(get_search_query()));
        } elseif (is_archive()) {
          the_archive_title();
        } else {
          echo esc_html(get_the_title(get_option('page_for_posts')) ?: 'ブログ');
        }
        ?>
      </h1>
    </header>

    <?php if (have_posts()) : ?>
      <div class="tmf-archive-grid tmf-grid tmf-grid--3 reveal-stagger">
        <?php while (have_posts()) : the_post(); ?>
          <article class="tmf-glass tmf-kaitori-card">
            <a href="<?php the_permalink(); ?>">
              <div class="tmf-kaitori-card__img" style="aspect-ratio:16/10">
                <?php if (has_post_thumbnail()) : the_post_thumbnail('medium', array('loading' => 'lazy')); else : ?>
                  <span class="tmf-kaitori-card__ph">◆</span>
                <?php endif; ?>
              </div>
              <div class="tmf-kaitori-card__body">
                <span class="tmf-kaitori-card__cat"><?php echo esc_html(get_the_date('Y.m.d')); ?></span>
                <h2 class="tmf-kaitori-card__name"><?php the_title(); ?></h2>
                <p class="tmf-kaitori-card__note"><?php echo esc_html(wp_trim_words(get_the_excerpt(), 40)); ?></p>
              </div>
            </a>
          </article>
        <?php endwhile; ?>
      </div>
      <div class="tmf-center" style="margin-top:50px">
        <?php the_posts_pagination(array('mid_size' => 1, 'prev_text' => '←', 'next_text' => '→')); ?>
      </div>
    <?php else : ?>
      <p class="tmf-center tmf-lead">該当する記事が見つかりませんでした。</p>
    <?php endif; ?>

    <div class="tmf-backlink">
      <a class="tmf-btn tmf-btn--ghost" href="<?php echo esc_url(home_url('/')); ?>"><span>トップへ戻る</span></a>
    </div>
  </div>
</div>
<?php get_footer(); ?>
