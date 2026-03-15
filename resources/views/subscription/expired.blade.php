<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abonnement Expiré</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f5f5f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            max-width: 500px;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>

<body>
    <div class="card text-center p-5">
        <div class="display-1 text-danger mb-4"><i class="bi bi-clock-history"></i></div>
        <h2 class="fw-bold">Abonnement Expiré</h2>
        <p class="text-muted mb-4">Votre accès à PharmaSaaS a été suspendu car votre abonnement est arrivé à terme.
            Veuillez contacter l'administrateur pour renouveler votre licence.</p>
        <div class="d-grid">
            <a href="mailto:support@pharmasaas.cd" class="btn btn-primary btn-lg">Contact Support</a>
            <a href="{{ route('home') }}" class="btn btn-link mt-3 text-muted small">Retourner à l'accueil</a>
        </div>
    </div>
</body>

</html>