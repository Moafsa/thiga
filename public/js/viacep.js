/**
 * ViaCEP Automatic Address Lookup Helper for TMS
 */
document.addEventListener('DOMContentLoaded', function () {
    function setupViaCepLookup() {
        const cepInputs = document.querySelectorAll('#postal_code, #zip_code, #cep, input[name="postal_code"], input[name="zip_code"], input[name="cep"]');
        
        cepInputs.forEach(function (cepInput) {
            if (cepInput.dataset.viacepAttached) return;
            cepInput.dataset.viacepAttached = "true";

            // Format mask
            cepInput.addEventListener('input', function (e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 8) value = value.substring(0, 8);
                if (value.length > 5) {
                    e.target.value = value.substring(0, 5) + '-' + value.substring(5);
                } else {
                    e.target.value = value;
                }
            });

            // Lookup on blur or full 8 digits
            function performLookup() {
                const cleanCep = cepInput.value.replace(/\D/g, '');
                if (cleanCep.length !== 8) return;

                const form = cepInput.closest('form') || document;
                const addressInput = form.querySelector('#address, #street, input[name="address"], input[name="street"]');
                const neighborhoodInput = form.querySelector('#neighborhood, #bairro, input[name="neighborhood"], input[name="bairro"]');
                const cityInput = form.querySelector('#city, #cidade, input[name="city"], input[name="cidade"]');
                const stateSelect = form.querySelector('#state, #uf, select[name="state"], select[name="uf"]');
                const numberInput = form.querySelector('#address_number, #number, input[name="address_number"], input[name="number"]');

                // Visual indicator
                cepInput.style.borderColor = '#3b82f6';

                fetch('https://viacep.com.br/ws/' + cleanCep + '/json/')
                    .then(response => response.json())
                    .then(data => {
                        if (data.erro) {
                            cepInput.style.borderColor = '#ef4444';
                            return;
                        }
                        cepInput.style.borderColor = '#10b981';

                        if (addressInput && data.logradouro) addressInput.value = data.logradouro;
                        if (neighborhoodInput && data.bairro) neighborhoodInput.value = data.bairro;
                        if (cityInput && data.localidade) cityInput.value = data.localidade;
                        if (stateSelect && data.uf) {
                            stateSelect.value = data.uf;
                            // Trigger change event for select if needed
                            stateSelect.dispatchEvent(new Event('change'));
                        }
                        if (numberInput && !numberInput.value) {
                            numberInput.focus();
                        }
                    })
                    .catch(() => {
                        cepInput.style.borderColor = '#ef4444';
                    });
            }

            cepInput.addEventListener('blur', performLookup);
            cepInput.addEventListener('keyup', function (e) {
                if (e.target.value.replace(/\D/g, '').length === 8) {
                    performLookup();
                }
            });
        });
    }

    setupViaCepLookup();
});
