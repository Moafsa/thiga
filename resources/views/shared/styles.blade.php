<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .page-header-text h2 {
        color: var(--cor-texto-claro);
        font-size: 0.9em;
        opacity: 0.8;
        margin-top: 5px;
    }

    .btn-primary {
        background-color: var(--cor-acento);
        color: var(--cor-principal);
        padding: 12px 24px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: background-color 0.3s ease;
        border: none;
        cursor: pointer;
        font-size: 1em;
        font-family: inherit;
    }

    .btn-primary:hover {
        background-color: #FF885A;
    }

    .btn-primary:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    form .btn-primary {
        width: auto;
    }

    .btn-secondary {
        padding: 10px 20px;
        background-color: transparent;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 8px;
        color: var(--cor-texto-claro);
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-secondary:hover {
        border-color: var(--cor-acento);
        background-color: rgba(255, 107, 53, 0.1);
    }

    .card {
        background-color: var(--cor-secundaria);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        margin-bottom: 20px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background-color: var(--cor-secundaria);
        padding: 20px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: var(--cor-acento);
        border-radius: 10px;
        font-size: 20px;
        color: var(--cor-principal);
    }

    .stat-content h3 {
        font-size: 1.8em;
        color: var(--cor-acento);
        margin-bottom: 5px;
    }

    .stat-content p {
        color: var(--cor-texto-claro);
        opacity: 0.8;
        font-size: 0.9em;
    }

    .table-card {
        background-color: var(--cor-secundaria);
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        overflow: hidden;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead {
        background-color: var(--cor-principal);
    }

    thead th {
        padding: 15px;
        text-align: left;
        color: var(--cor-texto-claro);
        font-size: 0.85em;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    tbody tr {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        transition: background-color 0.2s ease;
    }

    tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }

    tbody td {
        padding: 15px;
        color: var(--cor-texto-claro);
        font-size: 0.95em;
    }

    .filter-group {
        margin-bottom: 15px;
    }

    .filter-group label {
        display: block;
        color: var(--cor-texto-claro);
        font-size: 0.9em;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .filter-group select,
    .filter-group input {
        width: 100%;
        padding: 10px 15px;
        background-color: var(--cor-principal);
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        color: var(--cor-texto-claro);
        font-size: 0.95em;
    }

    .filter-group select:focus,
    .filter-group input:focus {
        outline: none;
        border-color: var(--cor-acento);
    }

    /* Form inputs styling */
    .form-input,
    input[type="datetime-local"],
    input[type="date"],
    input[type="time"],
    input[type="number"],
    input[type="text"],
    input[type="email"],
    textarea,
    select {
        width: 100% !important;
        padding: 10px 15px !important;
        background-color: var(--cor-secundaria) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        border-radius: 5px !important;
        color: var(--cor-texto-claro) !important;
        font-size: 0.95em !important;
        font-family: inherit !important;
        transition: border-color 0.3s ease !important;
        box-sizing: border-box !important;
    }

    .form-input:focus,
    input[type="datetime-local"]:focus,
    input[type="date"]:focus,
    input[type="time"]:focus,
    input[type="number"]:focus,
    input[type="text"]:focus,
    input[type="email"]:focus,
    textarea:focus,
    select:focus {
        outline: none !important;
        border-color: var(--cor-acento) !important;
    }

    input[type="datetime-local"]::-webkit-calendar-picker-indicator,
    input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
        cursor: pointer;
    }

    label {
        display: block;
        color: rgba(245, 245, 245, 0.7);
        font-size: 0.9em;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .alert {
        position: fixed;
        top: 80px;
        right: 30px;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 1000;
        animation: slideIn 0.3s ease;
    }

    .alert-success {
        background-color: rgba(76, 175, 80, 0.9);
        color: white;
    }

    .alert-error {
        background-color: rgba(244, 67, 54, 0.9);
        color: white;
    }

    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
</style>



















