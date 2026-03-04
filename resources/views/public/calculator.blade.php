<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotação de Frete - {{ $tenant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: #f8fafc;
            font-family: 'Poppins', sans-serif;
        }

        .calculator-card {
            background-color: white;
            border-radius: 1.25rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            max-width: 450px;
            margin: 2rem auto;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .btn-primary {
            background-color:
                {{ $tenant->primary_color ?? '#245a49' }}
            ;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .btn-accent {
            background-color:
                {{ $tenant->accent_color ?? '#FF6B35' }}
            ;
            color: white;
        }

        /* Input overrides */
        input,
        select {
            border: 1.5px solid #e2e8f0 !important;
            transition: border-color 0.2s ease !important;
        }

        input:focus,
        select:focus {
            border-color:
                {{ $tenant->primary_color ?? '#245a49' }}
                !important;
            ring: 0 !important;
        }

        .step-dot {
            height: 0.6rem;
            width: 0.6rem;
            border-radius: 50%;
            background-color: #e2e8f0;
            margin: 0 0.35rem;
            transition: all 0.3s ease;
        }

        .step-dot.active {
            background-color:
                {{ $tenant->accent_color ?? '#FF6B35' }}
            ;
            width: 1.5rem;
            border-radius: 1rem;
        }

        .result-box {
            background: linear-gradient(135deg,
                    {{ $tenant->primary_color ?? '#245a49' }}
                    0%,
                    {{ $tenant->secondary_color ?? '#1a3d33' }}
                    100%);
            color: white;
            border-radius: 1rem;
            padding: 1.5rem;
        }
    </style>
</head>

<body>
    <div class="calculator-card" id="app">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-800">Cote seu Frete</h2>
            <div class="text-xs font-semibold px-2 py-1 rounded bg-orange-100 text-orange-600 uppercase tracking-wider">
                Digital
            </div>
        </div>

        <!-- Steps Indicator -->
        <div class="flex justify-center mb-8" id="stepIndicator">
            <div class="step-dot active" id="dot1"></div>
            <div class="step-dot" id="dot2"></div>
            <div class="step-dot" id="dot3"></div>
        </div>

        <div id="error" class="hidden mb-4 p-3 bg-red-50 text-red-700 text-sm rounded-lg border border-red-100"></div>

        <!-- STEP 1: Contact Info -->
        <div id="step-contact">
            <p class="text-sm text-gray-500 mb-6 font-medium">Inicie informando os dados fundamentais para sua cotação.
            </p>
            <form onsubmit="requestOtp(event)" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-tight mb-1">Nome Completo /
                        Empresa</label>
                    <input type="text" id="client_name" required placeholder="Ex: João Silva ou Thiga Transp."
                        class="mt-1 block w-full rounded-lg sm:text-sm p-3 outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-xs font-bold text-gray-400 uppercase tracking-tight mb-1">WhatsApp</label>
                        <input type="text" id="whatsapp" required placeholder="(00) 00000-0000"
                            class="mt-1 block w-full rounded-lg sm:text-sm p-3 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-tight mb-1">Email
                            (Opcional)</label>
                        <input type="email" id="email" placeholder="contato@empresa.com"
                            class="mt-1 block w-full rounded-lg sm:text-sm p-3 outline-none">
                    </div>
                </div>

                <button type="submit" id="btn-request-otp"
                    class="w-full btn-primary py-3.5 px-4 rounded-xl font-bold text-sm shadow-lg shadow-green-900/10 mt-4">
                    Continuar para Cotação
                </button>
            </form>
        </div>

        <!-- STEP 2: OTP Verification -->
        <div id="step-otp" class="hidden">
            <p class="text-sm text-gray-500 mb-6 font-medium text-center">Enviamos um código de segurança via WhatsApp.
            </p>
            <form onsubmit="verifyOtp(event)" class="space-y-5">
                <div>
                    <label
                        class="block text-xs font-bold text-gray-400 uppercase tracking-tight mb-1 text-center">Código
                        de 6 dígitos</label>
                    <input type="text" id="otp_code" required placeholder="000 000" maxlength="6"
                        class="mt-2 block w-full text-center text-2xl font-bold tracking-[0.5em] rounded-lg p-3 outline-none border-dashed">
                </div>

                <button type="submit" id="btn-verify-otp"
                    class="w-full btn-primary py-3.5 px-4 rounded-xl font-bold text-sm shadow-lg shadow-green-900/10 transition-all">
                    Validar e Prosseguir
                </button>

                <div class="text-center pt-2">
                    <button type="button" onclick="showStep(1)"
                        class="text-xs font-semibold text-gray-400 hover:text-orange-500 transition-colors">
                        <i class="fas fa-arrow-left mr-1"></i> Corrigir dados de contato
                    </button>
                </div>
            </form>
        </div>

        <!-- STEP 3: Calculator -->
        <div id="step-calc" class="hidden">
            <form onsubmit="calculateFreight(event)" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-xs font-bold text-gray-400 uppercase tracking-tight mb-1">Origem</label>
                        <input type="text" id="origin" required placeholder="CEP ou Cidade"
                            class="mt-1 block w-full rounded-lg sm:text-sm p-3 outline-none">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-bold text-gray-400 uppercase tracking-tight mb-1">Destino</label>
                        <input type="text" id="destination" required placeholder="CEP ou Cidade"
                            class="mt-1 block w-full rounded-lg sm:text-sm p-3 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-tight mb-1">Peso
                            (kg)</label>
                        <input type="number" id="weight" step="0.1" required placeholder="0.0"
                            class="mt-1 block w-full rounded-lg sm:text-sm p-3 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-tight mb-1">Valor NF
                            (R$)</label>
                        <input type="number" id="invoice_value" step="0.01" required placeholder="0,00"
                            class="mt-1 block w-full rounded-lg sm:text-sm p-3 outline-none">
                    </div>
                </div>

                <button type="submit" id="btn-calculate"
                    class="w-full btn-primary py-3.5 px-4 rounded-xl font-bold text-sm shadow-lg shadow-green-900/10">
                    <i class="fas fa-calculator mr-2"></i> Calcular Valor do Frete
                </button>
            </form>

            <div id="result" class="hidden mt-6 result-box">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-xs font-bold uppercase tracking-widest opacity-70">Honorário Estimado</span>
                    <i class="fas fa-check-circle text-orange-400"></i>
                </div>
                <p class="text-3xl font-bold" id="priceDisplay">R$ 0,00</p>
                <div class="mt-4 pt-4 border-t border-white/10">
                    <p class="text-[10px] opacity-70 leading-relaxed">
                        *Valor estimado sujeito a variações fiscais. Detalhes enviados automaticamente para seu
                        WhatsApp.
                    </p>
                </div>
            </div>

            <div class="text-center mt-6">
                <button type="button" onclick="resetCalc()"
                    class="text-xs font-bold text-gray-400 hover:text-orange-500 transition-colors">
                    <i class="fas fa-redo mr-1"></i> Nova Cotação
                </button>
            </div>
        </div>

        <div class="mt-8 text-center border-t border-gray-50 pt-4">
            <a href="#"
                class="text-[10px] font-bold text-gray-300 uppercase tracking-[0.2em] hover:text-orange-500 transition-all">Powered
                by Thiga Systems</a>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        // Global State
        let otpVerified = false;

        // Mask
        document.getElementById('whatsapp').addEventListener('input', function (e) {
            var x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
            e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
        });

        async function requestOtp(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-request-otp');
            const errorDiv = document.getElementById('error');

            errorDiv.classList.add('hidden');
            btn.disabled = true;
            btn.innerText = 'Processando...';

            const data = {
                client_name: document.getElementById('client_name').value,
                whatsapp: document.getElementById('whatsapp').value,
                email: document.getElementById('email').value
            };

            try {
                const response = await fetch(`/calculator/{{ $tenant->domain }}/send-otp`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(data)
                });
                const json = await response.json();

                if (json.success) {
                    showStep(2);
                } else {
                    showError(json.message || 'Erro ao enviar código.');
                }
            } catch (err) {
                showError('Erro de conexão ao servidor.');
            } finally {
                btn.disabled = false;
                btn.innerText = 'Continuar para Cotação';
            }
        }

        async function verifyOtp(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-verify-otp');
            const errorDiv = document.getElementById('error');

            errorDiv.classList.add('hidden');
            btn.disabled = true;
            btn.innerText = 'Validando...';

            const data = {
                client_name: document.getElementById('client_name').value,
                whatsapp: document.getElementById('whatsapp').value,
                email: document.getElementById('email').value,
                code: document.getElementById('otp_code').value
            };

            try {
                const response = await fetch(`/calculator/{{ $tenant->domain }}/verify-otp`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(data)
                });
                const json = await response.json();

                if (json.success) {
                    otpVerified = true;
                    showStep(3);
                } else {
                    showError(json.message || 'Código inválido ou expirado.');
                }
            } catch (err) {
                showError('Erro de conexão ao validar.');
            } finally {
                btn.disabled = false;
                btn.innerText = 'Validar e Prosseguir';
            }
        }

        async function calculateFreight(e) {
            e.preventDefault();
            if (!otpVerified) {
                showError("Sessão expirada. Por favor autentique-se novamente.");
                showStep(1);
                return;
            }

            const btn = document.getElementById('btn-calculate');
            const resultDiv = document.getElementById('result');
            const errorDiv = document.getElementById('error');

            btn.disabled = true;
            btn.innerText = 'Calculando...';
            resultDiv.classList.add('hidden');
            errorDiv.classList.add('hidden');

            const data = {
                otp_verified: true,
                client_name: document.getElementById('client_name').value,
                whatsapp: document.getElementById('whatsapp').value,
                email: document.getElementById('email').value,
                origin: document.getElementById('origin').value,
                destination: document.getElementById('destination').value,
                weight: document.getElementById('weight').value,
                invoice_value: document.getElementById('invoice_value').value
            };

            try {
                const response = await fetch(`/calculator/{{ $tenant->domain }}/calculate`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(data)
                });

                const json = await response.json();

                if (json.success) {
                    document.getElementById('priceDisplay').innerText = 'R$ ' + json.total;
                    resultDiv.classList.remove('hidden');
                } else {
                    showError(json.message || 'Não temos rota para este destino no momento.');
                }
            } catch (err) {
                showError('Erro técnico durante o cálculo.');
            } finally {
                btn.disabled = false;
                btn.innerText = 'Calcular Valor do Frete';
            }
        }

        function showStep(step) {
            document.getElementById('step-contact').classList.add('hidden');
            document.getElementById('step-otp').classList.add('hidden');
            document.getElementById('step-calc').classList.add('hidden');

            document.getElementById('dot1').classList.remove('active');
            document.getElementById('dot2').classList.remove('active');
            document.getElementById('dot3').classList.remove('active');

            if (step === 1) {
                document.getElementById('step-contact').classList.remove('hidden');
                document.getElementById('dot1').classList.add('active');
            } else if (step === 2) {
                document.getElementById('step-otp').classList.remove('hidden');
                document.getElementById('dot1').classList.add('active');
                document.getElementById('dot2').classList.add('active');
            } else if (step === 3) {
                document.getElementById('step-calc').classList.remove('hidden');
                document.getElementById('dot1').classList.add('active');
                document.getElementById('dot2').classList.add('active');
                document.getElementById('dot3').classList.add('active');
            }

            document.getElementById('error').classList.add('hidden');
        }

        function showError(msg) {
            const errorDiv = document.getElementById('error');
            errorDiv.innerText = msg;
            errorDiv.classList.remove('hidden');
        }

        function resetCalc() {
            document.getElementById('result').classList.add('hidden');
            document.getElementById('origin').value = '';
            document.getElementById('destination').value = '';
            document.getElementById('weight').value = '';
            document.getElementById('invoice_value').value = '';
        }
    </script>
</body>

</html>