<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TMS SaaS - Sistema de Gestão de Transportes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-2xl font-bold text-gray-900">TMS SaaS</h1>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/login" class="text-gray-500 hover:text-gray-700">Entrar</a>
                    <a href="/register" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Começar Grátis</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-600 to-blue-800 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold mb-6">
                    Gestão de Transportes
                    <span class="text-blue-200">Inteligente</span>
                </h1>
                <p class="text-xl md:text-2xl mb-8 text-blue-100">
                    Plataforma SaaS completa para transportadoras com IA, WhatsApp e integração fiscal
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="/register" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                        Começar Teste Grátis
                    </a>
                    <a href="#features" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors">
                        Ver Funcionalidades
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Funcionalidades Principais
                </h2>
                <p class="text-xl text-gray-600">
                    Tudo que sua transportadora precisa em uma única plataforma
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-gray-50 p-8 rounded-lg">
                    <div class="text-blue-600 text-4xl mb-4">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Gestão Multi-Tenant</h3>
                    <p class="text-gray-600">
                        Isolamento completo de dados entre transportadoras com segurança máxima.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-gray-50 p-8 rounded-lg">
                    <div class="text-blue-600 text-4xl mb-4">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">WhatsApp com IA</h3>
                    <p class="text-gray-600">
                        Atendimento automatizado inteligente para rastreamento e suporte aos clientes.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-gray-50 p-8 rounded-lg">
                    <div class="text-blue-600 text-4xl mb-4">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Integração Fiscal</h3>
                    <p class="text-gray-600">
                        Emissão automática de CT-e e MDF-e com integração direta à Sefaz.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-gray-50 p-8 rounded-lg">
                    <div class="text-blue-600 text-4xl mb-4">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Gestão de Cargas</h3>
                    <p class="text-gray-600">
                        Controle completo do ciclo de vida das cargas com rastreamento em tempo real.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-gray-50 p-8 rounded-lg">
                    <div class="text-blue-600 text-4xl mb-4">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Gestão Financeira</h3>
                    <p class="text-gray-600">
                        Faturamento, contas a receber/pagar e fluxo de caixa integrados.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-gray-50 p-8 rounded-lg">
                    <div class="text-blue-600 text-4xl mb-4">
                        <i class="fas fa-route"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Otimização de Rotas</h3>
                    <p class="text-gray-600">
                        Planejamento inteligente de rotas com app para motoristas.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Planos e Preços
                </h2>
                <p class="text-xl text-gray-600">
                    Escolha o plano ideal para sua transportadora
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Básico -->
                <div class="bg-white p-8 rounded-lg shadow-sm">
                    <h3 class="text-2xl font-bold mb-4">Básico</h3>
                    <div class="text-4xl font-bold text-blue-600 mb-4">R$ 99<span class="text-lg text-gray-500">/mês</span></div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i>Até 5 usuários</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i>100 cargas/mês</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i>WhatsApp integrado</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i>Suporte por email</li>
                    </ul>
                    <a href="/register?plan=basico" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold text-center block hover:bg-blue-700 transition-colors">
                        Começar Teste
                    </a>
                </div>

                <!-- Profissional -->
                <div class="bg-white p-8 rounded-lg shadow-sm border-2 border-blue-600 relative">
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                        <span class="bg-blue-600 text-white px-4 py-1 rounded-full text-sm font-semibold">Mais Popular</span>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Profissional</h3>
                    <div class="text-4xl font-bold text-blue-600 mb-4">R$ 199<span class="text-lg text-gray-500">/mês</span></div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i>Até 15 usuários</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i>500 cargas/mês</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i>WhatsApp + IA</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i>Integração fiscal</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i>API completa</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i>Analytics avançado</li>
                    </ul>
                    <a href="/register?plan=profissional" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold text-center block hover:bg-blue-700 transition-colors">
                        Começar Teste
                    </a>
                </div>

                <!-- Empresarial -->
                <div class="bg-white p-8 rounded-lg shadow-sm">
                    <h3 class="text-2xl font-bold mb-4">Empresarial</h3>
                    <div class="text-4xl font-bold text-blue-600 mb-4">R$ 399<span class="text-lg text-gray-500">/mês</span></div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i>Até 50 usuários</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i>2000 cargas/mês</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i>Todos os recursos</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i>Suporte prioritário</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i>Integrações customizadas</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-3"></i>Treinamento dedicado</li>
                    </ul>
                    <a href="/register?plan=empresarial" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold text-center block hover:bg-blue-700 transition-colors">
                        Começar Teste
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-blue-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">
                Pronto para revolucionar sua transportadora?
            </h2>
            <p class="text-xl mb-8 text-blue-100">
                Comece seu teste gratuito de 30 dias hoje mesmo
            </p>
            <a href="/register" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                Começar Agora - Grátis
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">TMS SaaS</h3>
                    <p class="text-gray-400">
                        A plataforma mais completa para gestão de transportadoras.
                    </p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Produto</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white">Funcionalidades</a></li>
                        <li><a href="#" class="hover:text-white">Preços</a></li>
                        <li><a href="#" class="hover:text-white">API</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Suporte</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white">Documentação</a></li>
                        <li><a href="#" class="hover:text-white">Contato</a></li>
                        <li><a href="#" class="hover:text-white">Status</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Empresa</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white">Sobre</a></li>
                        <li><a href="#" class="hover:text-white">Blog</a></li>
                        <li><a href="#" class="hover:text-white">Carreiras</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2024 TMS SaaS. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>
</body>
</html>























<?php /**PATH /var/www/resources/views/welcome.blade.php ENDPATH**/ ?>