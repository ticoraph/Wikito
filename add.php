<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="noindex,nofollow,noarchive,nosnippet,noimageindex">
    <title>Ajouter un terme</title>
    <link rel="stylesheet" href="style.css?v=<?= filemtime('style.css') ?>">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Ajouter à Wikito</h1>
            
            <form id="addForm">
                <div class="form-group">
                    <label for="title">Titre</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required></textarea>
                </div>
                
                <button type="submit">Insérer</button>
            </form>
            
            <div id="messageBox" class="message"></div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
