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
            background-color: {{ $tenant->primary_color ?? '#3b82f6' }};
            color: white;
        }

        .btn-primary:hover {
            opacity: 0.9;
        }

        /* Fix for dropdown visibility */
        select, option {
            color: #1f2937 !important;
            background-color: #ffffff !important;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        .step-dot {
            height: 0.5rem;
            width: 0.5rem;
            border-radius: 50%;
            background-color: #e5e7eb;
            margin: 0 0.25rem;
        }
        .step-dot.active {
            background-color: {{ $tenant->primary_color ?? '#3b82f6' }};
        }
    </style>
</head>

<body>
    <div class="calculator-card" id="app">
        <h2 class="text-lg font-bold mb-4 text-gray-800">Cotar Frete</h2>
        
        <!-- Steps Indicator -->
        <div class="step-indicator" id="stepIndicator">
            <div class="step-dot active" id="dot1"></div>
            <div class="step-dot" id="dot2"></div>
            <div class="step-dot" id="dot3"></div>
        </div>

        <div id="error" class="hidden mb-4 p-3 bg-red-50 text-red-700 text-sm rounded-md border border-red-200"></div>

        <!-- STEP 1: Contact Info -->
        <div id="step-contact">
            <p class="text-sm text-gray-600 mb-4">Informe seus dados para iniciar a cotação.</p>
            <form onsubmit="requestOtp(event)" class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nome do Cliente</label>
                    <input type="text" id="client_name" required placeholder="Nome da empresa ou cliente"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">WhatsApp *</label>
                        <input type="text" id="whatsapp" required placeholder="(99) 99999-9999"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email (opcional)</label>
                        <input type="email" id="email" placeholder="seu@email.com"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border">
                    </div>
                </div>

                <button type="submit" id="btn-request-otp"
                    class="w-full btn-primary py-2 px-4 rounded-md font-medium text-sm transition-colors cursor-pointer mt-2">
                    Continuar
                </button>
            </form>
        </div>

        <!-- STEP 2: OTP Verification -->
        <div id="step-otp" class="hidden">
            <p class="text-sm text-gray-600 mb-4">Enviamos um código para seu WhatsApp.</p>
            <form onsubmit="verifyOtp(event)" class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-center text-gray-700">Código de Verificação</label>
                    <input type="text" id="otp_code" required placeholder="000000" maxlength="6"
                        class="mt-1 block w-2/3 mx-auto text-center text-xl tracking-widest rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border">
                </div>
                
                <button type="submit" id="btn-verify-otp"
                    class="w-full btn-primary py-2 px-4 rounded-md font-medium text-sm transition-colors cursor-pointer mt-2">
                    Validar Código
                </button>
                
                <div class="text-center mt-2">
                    <button type="button" onclick="showStep(1)" class="text-xs text-gray-500 hover:underline">Voltar / Corrigir telefone</button>
                </div>
            </form>
        </div>

        <!-- STEP 3: Calculator -->
        <div id="step-calc" class="hidden">
            <form onsubmit="calculateFreight(event)" class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Origem</label>
                    <input type="text" id="origin" required placeholder="CEP ou Cidade"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Destino</label>
                    <input type="text" id="destination" required placeholder="CEP ou Cidade"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Peso (kg)</label>
                        <input type="number" id="weight" step="0.1" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Valor (R$)</label>
                        <input type="number" id="invoice_value" step="0.01" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 border">
                    </div>
                </div>

                <button type="submit" id="btn-calculate"
                    class="w-full btn-primary py-2 px-4 rounded-md font-medium text-sm transition-colors cursor-pointer">
                    Calcular Frete
                </button>
            </form>

            <div id="result" class="hidden mt-4 p-3 bg-gray-50 rounded-md border border-gray-200">
                <p class="text-sm text-gray-500">Valor Estimado:</p>
                <p class="text-2xl font-bold text-gray-800" id="priceDisplay">R$ 0,00</p>
                <div class="mt-2 pt-2 border-t border-gray-200">
                    <p class="text-xs text-green-600 flex items-center justify-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Resumo enviado para seu WhatsApp
                    </p>
                </div>
            </div>
            
            <div class="text-center mt-2">
                 <button type="button" onclick="resetCalc()" class="text-xs text-gray-500 hover:underline">Nova Cotação</button>
            </div>
        </div>

        <div class="mt-4 text-center">
            <a href="#" class="text-xs text-gray-400 hover:text-gray-600">Powered by Thiga</a>
        </div>
    </div>

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
            btn.innerText = 'Enviando...';

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
                showError('Erro de conexão.');
            } finally {
                btn.disabled = false;
                btn.innerText = 'Continuar';
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
                    showError(json.message || 'Código inválido.');
                }
            } catch (err) {
                showError('Erro de conexão.');
            } finally {
                btn.disabled = false;
                btn.innerText = 'Validar Código';
            }
        }

        async function calculateFreight(e) {
            e.preventDefault();
            if(!otpVerified) {
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
                    showError(json.message || 'Erro ao calcular.');
                }
            } catch (err) {
                showError('Erro de conexão.');
            } finally {
                btn.disabled = false;
                btn.innerText = 'Calcular Frete';
            }
        }

        function showStep(step) {
            document.getElementById('step-contact').classList.add('hidden');
            document.getElementById('step-otp').classList.add('hidden');
            document.getElementById('step-calc').classList.add('hidden');
            
            document.getElementById('dot1').classList.remove('active');
            document.getElementById('dot2').classList.remove('active');
            document.getElementById('dot3').classList.remove('active');

            if(step === 1) {
                document.getElementById('step-contact').classList.remove('hidden');
                document.getElementById('dot1').classList.add('active');
            } else if(step === 2) {
                document.getElementById('step-otp').classList.remove('hidden');
                document.getElementById('dot1').classList.add('active');
                document.getElementById('dot2').classList.add('active');
            } else if(step === 3) {
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