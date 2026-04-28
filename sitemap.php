<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/xml; charset=UTF-8');

$urls = array(
    array('loc' => siteUrl('/'), 'changefreq' => 'weekly', 'priority' => '1.0'),
    array('loc' => siteUrl('/certifications.php'), 'changefreq' => 'monthly', 'priority' => '0.7'),
);

$conn = @new mysqli($db_host, $db_user, $db_pass, $db_name);
if (!$conn->connect_error) {
    $conn->set_charset('utf8mb4');
    $tableCheck = $conn->query("SHOW TABLES LIKE 'articles'");
    $articlesTableExists = $tableCheck && $tableCheck->num_rows > 0;
    if ($tableCheck) { $tableCheck->close(); }

    if ($articlesTableExists) {
        $stmt = $conn->prepare('SELECT slug, COALESCE(published_at, created_at) AS updated_at FROM articles WHERE is_published = 1 ORDER BY sort_order ASC, COALESCE(published_at, created_at) DESC');
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $urls[] = array(
                    'loc' => siteUrl('/article.php?slug=' . rawurlencode($row['slug'])),
                    'changefreq' => 'monthly',
                    'priority' => '0.8',
                    'lastmod' => !empty($row['updated_at']) ? date('c', strtotime($row['updated_at'])) : null,
                );
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $url): ?>
  <url>
    <loc><?php echo e($url['loc']); ?></loc>
    <?php if (!empty($url['lastmod'])): ?><lastmod><?php echo e($url['lastmod']); ?></lastmod><?php endif; ?>
    <?php if (!empty($url['changefreq'])): ?><changefreq><?php echo e($url['changefreq']); ?></changefreq><?php endif; ?>
    <?php if (!empty($url['priority'])): ?><priority><?php echo e($url['priority']); ?></priority><?php endif; ?>
  </url>
<?php endforeach; ?>
</urlset>