import DataTable from 'datatables.net-bs5';

document.addEventListener('DOMContentLoaded', () => {
    const tableEl = document.getElementById('usersTable');
    if (!tableEl) return;

    const ajaxUrl = tableEl.dataset.ajaxUrl;
    const addUrl = tableEl.dataset.addUrl;

    const makeAddButton = () => {
        const wrap = document.createElement('div');
        wrap.className = 'd-flex align-items-center';

        const a = document.createElement('a');
        a.href = addUrl;
        a.className = 'btn btn-success';
        a.innerHTML = `<i class="fa-solid fa-plus"></i> Ajouter`;

        wrap.appendChild(a);
        return wrap;
    };

    new DataTable(tableEl, {
        processing: true,
        serverSide: true,

        ajax: {
            url: ajaxUrl,
            type: 'POST',
        },

        // 10 lignes par défaut
        pageLength: 10,

        // Choix du nombre de lignes (le sélecteur apparaît automatiquement si "pageLength" est dans le layout)
        lengthMenu: [10, 25, 50, 100],

        // Libellés FR (DataTables 2.1.8)
        language: {
            url: 'https://cdn.datatables.net/plug-ins/2.1.8/i18n/fr-FR.json',
            search: 'Recherche :',
            lengthMenu: 'Afficher _MENU_ lignes',
        },

        // Barre du haut : bouton Ajouter à gauche, (Nb lignes + Recherche) à droite
        // DataTables 2 : layout remplace dom
        layout: {
            topStart: makeAddButton,
            topEnd: {
                pageLength: {},
                search: {},
            },
            bottomStart: 'info',
            bottomEnd: 'paging',
        },

        // Colonnes attendues depuis JSON (clés nommées)
        columns: [
            { data: 'id' },
            { data: 'email' },
            { data: 'prenom' },
            { data: 'nom' },
            { data: 'statut'},
            { data: 'actions', orderable: false, searchable: false },
        ],

        // Si ton serveur renvoie du HTML (badges/actions), on garde tel quel
        columnDefs: [
            { targets: [4, 5], render: (data) => data },
        ],
    });
});
