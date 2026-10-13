<?php
$jsonFile = 'data/glossary.json';
$terms = [];
$total = 0;

if (file_exists($jsonFile)) {
    $content = file_get_contents($jsonFile);
    if ($content) {
        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            $terms = $decoded;
            $total = count($terms);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="noindex,nofollow,noarchive,nosnippet,noimageindex">
    <title>Wikito</title>
    <link rel="stylesheet" href="style.css?v=<?= filemtime('style.css') ?>">
</head>
<body>
    <div class="container">
        <header>
            <h1>Wikito</h1>
            <?php if ($total > 0): ?>
                <p class="term-count"><?= $total ?> définition<?= $total > 1 ? 's' : '' ?> disponible<?= $total > 1 ? 's' : '' ?></p>
            <?php endif; ?>
        </header>

        <main>
            <?php if ($total === 0): ?>
                <div class="empty-state">
                    <p>Aucune définition disponible pour le moment.</p>
                </div>
            <?php else: ?>
                <div class="search-container">
                    <input type="search" id="searchInput" placeholder="Rechercher un terme...">
                </div>
                <div class="glossary-list">
                    <?php foreach ($terms as $term): ?>
                        <div class="term-card">
                            <h2 class="term-title"><?= htmlspecialchars($term['title']) ?></h2>
                            <p class="term-desc"><?= nl2br(htmlspecialchars($term['description'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
    <script src="script.js"></script>
</body>
</html>
