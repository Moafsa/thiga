<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotação de Frete - {{ $tenant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: transparent;
        }

        .calculator-card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
            max-width: 400px;
            margin: 0 auto;
            font-family: 'Inter', sans-serif;
        }

        .btn-primary {
            background-color:
                {{ $tenant->primary_color ?? '#3b82f6' }}
            ;
            color: white;
        }

        .btn-primary:hover {
            opacity: 0.9;
        }
    </style>
</head>

<body>
    <div class="calculator-card" id="app">
        <h2 class="text-lg font-bold mb-4 text-gray-800">Cotar Frete</h2>

        <form id="calcForm" onsubmit="calculateFreight(event)" class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700">Origem</label>
                <input type="text" name="origin" required placeholder="CEP ou Cidade"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Destino</label>
                <input type="text" name="destination" required placeholder="CEP ou Cidade"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border">
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Peso (kg)</label>
                    <input type="number" name="weight" step="0.1" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Valor (R$)</label>
                    <input type="number" name="invoice_value" step="0.01" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border">
                </div>
            </div>

            <button type="submit" id="submitBtn"
                class="w-full btn-primary py-2 px-4 rounded-md font-medium text-sm transition-colors cursor-pointer">
                Calcular
            </button>
        </form>

        <div id="result" class="hidden mt-4 p-3 bg-gray-50 rounded-md border border-gray-200">
            <p class="text-sm text-gray-500">Valor Estimado:</p>
            <p class="text-2xl font-bold text-gray-800" id="priceDisplay">R$ 0,00</p>
        </div>

        <div id="error" class="hidden mt-4 p-3 bg-red-50 text-red-700 text-sm rounded-md border border-red-200"></div>

        <div class="mt-4 text-center">
            <a href="#" class="text-xs text-gray-400 hover:text-gray-600">Powered by Thiga</a>
        </div>
    </div>

    <script>
        async function calculateFreight(e) {
            e.preventDefault();
            const btn = document.getElementById('submitBtn');
            const resultDiv = document.getElementById('result');
            const errorDiv = document.getElementById('error');
            const form = e.target;

            btn.disabled = true;
            btn.innerText = 'Calculando...';
            resultDiv.classList.add('hidden');
            errorDiv.classList.add('hidden');

            const data = {
                origin: form.origin.value,
                destination: form.destination.value,
                weight: form.weight.value,
                invoice_value: form.invoice_value.value
            };

            try {
                const response = await fetch(`/calculator/{{ $tenant->domain }}/calculate`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                });

                const json = await response.json();

                if (json.success) {
                    document.getElementById('priceDisplay').innerText = 'R$ ' + json.total;
                    resultDiv.classList.remove('hidden');
                } else {
                    errorDiv.innerText = json.message || 'Erro ao calcular.';
                    errorDiv.classList.remove('hidden');
                }
            } catch (err) {
                errorDiv.innerText = 'Erro de conexão.';
                errorDiv.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.innerText = 'Calcular';
            }
        }
    </script>
</body>

</html>