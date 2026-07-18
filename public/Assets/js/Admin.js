document.addEventListener('DOMContentLoaded', function () {
    // Variables pour stocker les données de la BDD
    let events = [];
    let clubs = [];
    let transactions = [];

    // --- INITIALISATION DU DASHBOARD ---
    // Fonction qui charge toutes les données depuis le serveur
    // fonction spéciale qui peut faire des pauses (prcq on doit attendre la réponse du serveur)
    async function initDashboard() {
        try {
            const response = await fetch('../api/get_data.php');
            const data = await response.json();

            // Stocker les données récupérées
            events = data.evenements || [];
            clubs = data.clubs || [];
            transactions = data.transactions || [];
            
            // on lance toutes les fonctions de rendu
            renderAll();

        } catch (error) {
            console.error("Erreur lors du chargement des données:", error);
            document.querySelector('.main-content').innerHTML = '<div class="alert alert-danger">Impossible de charger les données du serveur.</div>';
        }
    }

    // Fonction pour tout mettre à jour
    function renderAll() {
        renderEvents();
        renderClubs();
        renderTransactions();
        updateStats();
        renderDashboardChart();
        renderPie();
        renderBar();
    }
    
    // --- FONCTIONS DE RENDU (AFFICHAGE) ---

    function renderEvents() {
        const dashboardEvents = document.getElementById('dashboardEvents');
        if (dashboardEvents) {
            dashboardEvents.innerHTML = events.slice(0, 2).map(ev => `
                <div class="event-card d-flex mb-3">
                    <div class="event-date d-flex flex-column justify-content-center">
                        <div class="event-day">${new Date(ev.date_event).getDate().toString().padStart(2, '0')}</div>
                        <div class="event-month">${["JAN","FÉV","MAR","AVR","MAI","JUI","JUI","AOÛ","SEP","OCT","NOV","DÉC"][new Date(ev.date_event).getMonth()]}</div>
                    </div>
                    <div class="event-details flex-grow-1 px-2">
                        <h5 class="mb-1">${ev.nom}</h5>
                        <div class="d-flex text-muted small mb-2">
                            <span class="me-3"><i class="fas fa-clock me-1"></i>${ev.heure_event.substring(0, 5)}</span>
                            <span><i class="fas fa-map-marker-alt me-1"></i>${ev.lieu}</span>
                        </div>
                    </div>
                </div>`).join('') || '<p class="text-muted small">Aucun événement à venir.</p>';
        }

        const eventsContainer = document.getElementById('eventsContainer');
        if (eventsContainer) {
            eventsContainer.innerHTML = events.map(ev => `...`).join(''); // Adaptez l'affichage complet si besoin
        }
        document.getElementById('eventCount').textContent = events.length;
    }

    function renderClubs() {
        const container = document.getElementById('clubsContainer');
        if (container) {
            container.innerHTML = clubs.length ? clubs.map(club => `
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">${club.nom}</h5>
                            <h6 class="card-subtitle mb-2 text-muted">Resp. ${club.responsable}</h6>
                            <p class="card-text">${club.description}</p>
                            <span class="badge bg-success">Budget: ${parseInt(club.budget).toLocaleString()} FCFA</span>
                        </div>
                    </div>
                </div>`).join('') : '<div class="alert alert-light text-center">Aucun club enregistré.</div>';
        }
        document.getElementById('clubCount').textContent = clubs.length;
    }

    function renderTransactions() {
        const table = document.getElementById('transactionsTable');
        if (!table) return;
        table.innerHTML = transactions.map(tr => {
            const amountClass = tr.type === 'depense' ? 'text-danger' : 'text-success';
            const amountPrefix = tr.type === 'depense' ? '-' : '+';
            return `
                <tr>
                    <td>${tr.date_fr}</td>
                    <td>${tr.description}</td>
                    <td>${tr.categorie}</td>
                    <td class="${amountClass} fw-bold">${amountPrefix} ${parseInt(tr.montant).toLocaleString()} FCFA</td>
                    <td><span class="badge bg-success">Validée</span></td>
                    <td><a href="#" class="text-primary"><i class="fas fa-file-alt"></i></a></td>
                </tr>`;
        }).join('') || '<tr><td colspan="6" class="text-center">Aucune transaction.</td></tr>';
    }

    function updateStats() {
        // Calculer le solde actuel à partir des transactions
        const totalIncome = transactions.filter(t => t.type === 'recette').reduce((sum, t) => sum + parseFloat(t.montant), 0);
        const totalExpense = transactions.filter(t => t.type === 'depense').reduce((sum, t) => sum + parseFloat(t.montant), 0);
        const currentBalance = totalIncome - totalExpense;
        
        document.getElementById('studentCount').textContent = 89; // Valeur statique pour l'exemple
        document.getElementById('budgetAmount').textContent = currentBalance.toLocaleString();
        document.getElementById('currentBalance').textContent = currentBalance.toLocaleString() + " FCFA";
    }

    // Les fonctions de graphiques (renderDashboardChart, renderPie, renderBar)
    // utiliseront maintenant les variables 'events' et 'transactions' qui sont remplies par lAPI.
    // --- GRAPHIQUES CHART.JS ---
    // Ces variables permettent de ne pas recréer les graphiques inutilement
    let dashChart, pieChart, barChart;

    function renderDashboardChart() {
        // Détruit l'ancien graphique s'il existe pour éviter les bugs d'affichage
        if (dashChart) {
            dashChart.destroy();
        }
        const ctx = document.getElementById('dashboardChart').getContext('2d');
        dashChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre'],
                datasets: [{
                    label: 'Transactions',
                    // On compte le nombre de transactions par mois (exemple simple)
                    data: [1, 3, 5, 4, 6, 8, transactions.length], 
                    borderColor: '#2564cf',
                    tension: 0.3,
                    fill: false
                }, {
                    label: 'Événements créés',
                    // On compte le nombre d'événements par mois (exemple simple)
                    data: [0, 1, 1, 2, 3, 3, events.length], 
                    borderColor: '#29b6f6',
                    borderDash: [5, 5]
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: true } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    function renderPie() {
        if (pieChart) {
            pieChart.destroy();
        }
        // Préparer les données pour le graphique
        const expenseByCategory = transactions
            .filter(t => t.type === 'depense') // On ne prend que les dépenses
            .reduce((acc, t) => {
                // On additionne les montants pour chaque catégorie
                acc[t.categorie] = (acc[t.categorie] || 0) + parseFloat(t.montant);
                return acc;
            }, {});

        const ctx = document.getElementById('budgetPie').getContext('2d');
        pieChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: Object.keys(expenseByCategory),
                datasets: [{
                    data: Object.values(expenseByCategory),
                    backgroundColor: ['#2564cf', '#ed6c02', '#26a69a', '#ffca28', '#c62828', '#5c6bc0']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    function renderBar() {
        if (barChart) {
            barChart.destroy();
        }
        const ctx = document.getElementById('eventBar').getContext('2d');
        barChart = new Chart(ctx, {
            type: 'bar',
            data: {
                // Utilise le nom de chaque événement pour les étiquettes
                labels: events.map(e => e.nom), 
                datasets: [{
                    label: 'Participants',
                    // Utilise le nombre de participants de chaque événement pour les données
                    data: events.map(e => e.participants), 
                    backgroundColor: '#2564cf'
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }


    // --- GESTION DES FORMULAIRES (ENVOI DES DONNÉES) ---
    async function handleFormSubmit(url, formData, modalId) {
        try {
            const response = await fetch(url, { method: 'POST', body: formData });
            const result = await response.json();
            alert(result.message);

            if (result.success) {
                // Fermer le modal et recharger les données pour tout mettre à jour
                const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
                modal.hide();
                initDashboard(); // Recharger toutes les données et ré-afficher
            }
        } catch (error) {
            console.error("Erreur lors de la soumission:", error);
            alert("Une erreur technique est survenue.");
        }
    }

    document.getElementById('saveEventBtn').onclick = () => {
        const form = document.getElementById('eventForm');
        const formData = new FormData();
        formData.append('nom', document.getElementById('eventName').value);
        formData.append('date', document.getElementById('eventDate').value);
        formData.append('heure', document.getElementById('eventTime').value);
        formData.append('lieu', document.getElementById('eventLocation').value);
        formData.append('description', document.getElementById('eventDescription').value);
        formData.append('budget', document.getElementById('eventBudget').value);
        handleFormSubmit('api/ajout_event.php', formData, 'addEventModal');
    };

    document.getElementById('saveClubBtn').onclick = () => {
        const formData = new FormData(document.getElementById('clubForm'));
        // Correction des noms de champs pour correspondre au formulaire
        formData.set('nom', document.getElementById('clubName').value);
        formData.set('responsable', document.getElementById('clubManager').value);
        formData.set('description', document.getElementById('clubDescription').value);
        formData.set('budget', document.getElementById('clubBudget').value);
        handleFormSubmit('api/ajout_club.php', formData, 'addClubModal');
    };

    document.getElementById('saveTransactionBtn').onclick = () => {
        const formData = new FormData();
        formData.append('type', document.getElementById('transactionType').value);
        formData.append('montant', document.getElementById('transactionAmount').value);
        formData.append('description', document.getElementById('transactionDescription').value);
        formData.append('categorie', document.getElementById('transactionCategory').value);
        formData.append('date', document.getElementById('transactionDate').value);
        handleFormSubmit('api/ajout_transaction.php', formData, 'addTransactionModal');
    };


    // --- NAVIGATION ET ÉLÉMENTS UI (Pas de changement ici) ---
    document.querySelectorAll('[data-page]').forEach(link => {
        link.onclick = function (e) {
            e.preventDefault();
            let page = this.getAttribute('data-page');
            if (!page) return;
            document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            document.getElementById(page).classList.add('active');
            this.classList.add('active');
        }
    });
    document.querySelector('.sidebar-toggle').onclick = () => {
        document.querySelector('.sidebar').classList.toggle('active');
    };

    // Lancement initial
    initDashboard();
});