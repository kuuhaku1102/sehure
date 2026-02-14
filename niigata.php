<?php
/**
 * 新潟県のセフレ掲示板
 */

// WordPress環境の読み込み
require_once(__DIR__ . '/wp-load.php');

$pref_slug = 'niigata';
$pref_name = '新潟県';

get_header();

// ランダムに8-12件表示
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
      
      <h3><?php echo esc_html($pref_name); ?>の出会いの特徴</h3>
      <p><?php echo esc_html($pref_name); ?>は、独自の文化や雰囲気を持っており、その地域ならではの出会いの特徴があります。地域の特性を理解することで、より効果的にセフレを見つけることができます。</p>
      
      <h3>安全にセフレを作るためのポイント</h3>
      <ul>
        <li>年齢確認・本人確認が徹底されたサービスを利用する</li>
        <li>プロフィールを丁寧に作成し、誠実な印象を与える</li>
        <li>メッセージは丁寧に、相手を尊重する姿勢を忘れない</li>
        <li>初回は人目のある場所で短時間会う</li>
        <li>お互いの合意を確認してから関係を進める</li>
      </ul>
      
      <h3><?php echo esc_html($pref_name); ?>でのおすすめの出会い方</h3>
      <p><?php echo esc_html($pref_name); ?>で効果的にセフレを作るには、以下のポイントを押さえましょう。</p>
      <ul>
        <li><strong>プロフィール写真</strong>：清潔感のある写真を使用し、好印象を与える</li>
        <li><strong>自己紹介文</strong>：<?php echo esc_html($pref_name); ?>在住であることを明記し、地域の話題を盛り込む</li>
        <li><strong>メッセージ</strong>：相手のプロフィールをよく読み、共通の話題から会話を始める</li>
        <li><strong>待ち合わせ場所</strong>：<?php echo esc_html($pref_name); ?>の主要駅や人気スポット周辺を選ぶ</li>
      </ul>
      
      <h3><?php echo esc_html($pref_name); ?>でセフレを作る際の注意点</h3>
      <p><?php echo esc_html($pref_name); ?>でセフレを作る際には、以下の点に注意してください。</p>
      <ul>
        <li>相手のプライバシーを尊重し、個人情報の取り扱いには十分注意する</li>
        <li>金銭のやり取りは避け、健全な関係を築く</li>
        <li>相手の都合を考慮し、無理な要求はしない</li>
        <li>定期的にコミュニケーションを取り、良好な関係を維持する</li>
      </ul>
    </div>
  </section>

  <section class="content-section">
    <h2 class="section-title"><?php echo esc_html($pref_name); ?>のセフレ掲示板を利用するメリット</h2>
    <div class="content-box">
      <p><?php echo esc_html($pref_name); ?>に特化したセフレ掲示板を利用することで、以下のようなメリットがあります。</p>
      
      <h3>地域密着型の出会い</h3>
      <p><?php echo esc_html($pref_name); ?>在住の女性と出会えるため、実際に会いやすく、継続的な関係を築きやすいです。遠距離の相手と比べて、気軽に会える距離にいることは大きなメリットです。</p>
      
      <h3>共通の話題が豊富</h3>
      <p>同じ<?php echo esc_html($pref_name); ?>に住んでいることで、地域の話題や共通の知り合いなど、会話のきっかけが豊富にあります。これにより、初対面でも打ち解けやすく、関係を深めやすいです。</p>
      
      <h3>安全性の高い出会い</h3>
      <p>当サイトで紹介している出会い系サービスは、すべて年齢確認と本人確認が徹底されています。<?php echo esc_html($pref_name); ?>で安全にセフレを探すなら、信頼できるサービスを利用することが重要です。</p>
      
      <h3>多様な女性との出会い</h3>
      <p><?php echo esc_html($pref_name); ?>には、様々な年齢層や職業の女性が登録しています。あなたの好みやライフスタイルに合った女性を見つけることができます。</p>
    </div>
  </section>

  <div class="back-to-top">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="btn-back">トップページに戻る</a>
  </div>

  <div class="sefure-footer-info">
    <?php echo esc_html(date_i18n('Y年n月j日')); ?> / <?php echo esc_html($pref_name); ?>のセフレ掲示板
  </div>
</main>
<?php
get_footer();
