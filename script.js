document.addEventListener('DOMContentLoaded', () => {
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const cards = document.querySelectorAll('.term-card');
            
            cards.forEach(card => {
                const title = card.querySelector('.term-title').textContent.toLowerCase();
                const desc = card.querySelector('.term-desc').textContent.toLowerCase();
                
                if (title.includes(query) || desc.includes(query)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    const addForm = document.getElementById('addForm');
    if (addForm) {
        const messageBox = document.getElementById('messageBox');

        addForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Reset message
            messageBox.className = 'message';
            messageBox.textContent = '';

            const formData = new FormData(addForm);
            const title = formData.get('title').trim();
            const description = formData.get('description').trim();

            if (!title || !description) {
                showMessage('Veuillez remplir tous les champs.', 'error');
                return;
            }

            try {
                const response = await fetch('api/insert.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    showMessage('Terme ajouté avec succès !', 'success');
                    addForm.reset();
                } else {
                    showMessage(data.message || 'Une erreur est survenue.', 'error');
                }
            } catch (error) {
                showMessage('Erreur de connexion au serveur.', 'error');
            }
        });

        function showMessage(msg, type) {
            messageBox.textContent = msg;
            messageBox.className = `message ${type}`;
        }
    }
});
