<?php $__env->startSection('title', 'Configurações de Aparência - TMS SaaS'); ?>
<?php $__env->startSection('page-title', 'Configurações de Aparência'); ?>

<?php $__env->startSection('content'); ?>
<style>
    .settings-card {
        background-color: var(--cor-secundaria);
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        margin-bottom: 30px;
    }

    .settings-card h2 {
        color: var(--cor-acento);
        font-size: 1.5em;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid rgba(255, 107, 53, 0.3);
    }

    .color-picker-group {
        margin-bottom: 25px;
    }

    .color-picker-group label {
        display: block;
        color: var(--cor-texto-claro);
        font-weight: 600;
        margin-bottom: 10px;
        font-size: 1em;
    }

    .color-picker-wrapper {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .color-picker-input {
        width: 80px;
        height: 50px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        background: none;
        padding: 0;
    }

    .color-picker-input::-webkit-color-swatch-wrapper {
        padding: 0;
    }

    .color-picker-input::-webkit-color-swatch {
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 6px;
    }

    .color-text-input {
        flex: 1;
        padding: 12px 15px;
        background-color: var(--cor-principal);
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        color: var(--cor-texto-claro);
        font-size: 1em;
        font-family: 'Courier New', monospace;
    }

    .color-text-input:focus {
        outline: none;
        border-color: var(--cor-acento);
    }

    .preview-section {
        margin-top: 30px;
        padding-top: 30px;
        border-top: 2px solid rgba(255, 107, 53, 0.3);
    }

    .preview-section h3 {
        color: var(--cor-acento);
        font-size: 1.2em;
        margin-bottom: 20px;
    }

    .preview-box {
        background-color: var(--cor-principal);
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 15px;
    }

    .preview-box-secondary {
        background-color: var(--cor-secundaria);
        padding: 15px;
        border-radius: 8px;
        margin-top: 10px;
    }

    .preview-text {
        color: var(--cor-texto-claro);
        margin-bottom: 10px;
    }

    .preview-button {
        background-color: var(--cor-acento);
        color: var(--cor-principal);
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        font-weight: 600;
        cursor: pointer;
        display: inline-block;
    }

    .btn-save {
        background-color: var(--cor-acento);
        color: var(--cor-principal);
        padding: 12px 30px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 1em;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .btn-save:hover {
        background-color: #FF885A;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .alert-success {
        background-color: rgba(34, 197, 94, 0.2);
        border: 2px solid rgba(34, 197, 94, 0.5);
        color: #86efac;
    }

    .help-text {
        color: rgba(245, 245, 245, 0.7);
        font-size: 0.9em;
        margin-top: 5px;
    }
</style>

<?php if(session('success')): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>

<div class="settings-card">
    <h2>Personalização de Cores</h2>
    <p class="help-text" style="margin-bottom: 25px;">
        Personalize as cores do seu dashboard. As alterações serão aplicadas imediatamente após salvar.
    </p>

    <form method="POST" action="<?php echo e(route('settings.appearance.update')); ?>" id="appearanceForm">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="color-picker-group">
            <label for="primary_color">
                <i class="fas fa-palette"></i> Cor Primária
            </label>
            <div class="color-picker-wrapper">
                <input 
                    type="color" 
                    id="primary_color" 
                    name="primary_color" 
                    value="<?php echo e($tenant->primary_color ?? '#245a49'); ?>" 
                    class="color-picker-input"
                    onchange="updateTextInput('primary_color', this.value)"
                >
                <input 
                    type="text" 
                    id="primary_color_text" 
                    value="<?php echo e($tenant->primary_color ?? '#245a49'); ?>" 
                    class="color-text-input"
                    pattern="^#[0-9A-Fa-f]{6}$"
                    onchange="updateColorInput('primary_color', this.value)"
                    placeholder="#245a49"
                >
            </div>
            <p class="help-text">Cor de fundo principal do sistema (ex: #245a49)</p>
        </div>

        <div class="color-picker-group">
            <label for="secondary_color">
                <i class="fas fa-paint-brush"></i> Cor Secundária
            </label>
            <div class="color-picker-wrapper">
                <input 
                    type="color" 
                    id="secondary_color" 
                    name="secondary_color" 
                    value="<?php echo e($tenant->secondary_color ?? '#1a3d33'); ?>" 
                    class="color-picker-input"
                    onchange="updateTextInput('secondary_color', this.value)"
                >
                <input 
                    type="text" 
                    id="secondary_color_text" 
                    value="<?php echo e($tenant->secondary_color ?? '#1a3d33'); ?>" 
                    class="color-text-input"
                    pattern="^#[0-9A-Fa-f]{6}$"
                    onchange="updateColorInput('secondary_color', this.value)"
                    placeholder="#1a3d33"
                >
            </div>
            <p class="help-text">Cor de fundo secundária (ex: #1a3d33)</p>
        </div>

        <div class="color-picker-group">
            <label for="accent_color">
                <i class="fas fa-star"></i> Cor de Destaque
            </label>
            <div class="color-picker-wrapper">
                <input 
                    type="color" 
                    id="accent_color" 
                    name="accent_color" 
                    value="<?php echo e($tenant->accent_color ?? '#FF6B35'); ?>" 
                    class="color-picker-input"
                    onchange="updateTextInput('accent_color', this.value)"
                >
                <input 
                    type="text" 
                    id="accent_color_text" 
                    value="<?php echo e($tenant->accent_color ?? '#FF6B35'); ?>" 
                    class="color-text-input"
                    pattern="^#[0-9A-Fa-f]{6}$"
                    onchange="updateColorInput('accent_color', this.value)"
                    placeholder="#FF6B35"
                >
            </div>
            <p class="help-text">Cor de destaque para botões e elementos importantes (ex: #FF6B35)</p>
        </div>

        <div class="preview-section">
            <h3>Preview das Cores</h3>
            <div class="preview-box" id="previewBoxPrimary" style="background-color: var(--cor-principal);">
                <p class="preview-text">Exemplo de área com cor primária</p>
                <div class="preview-box-secondary" id="previewBoxSecondary" style="background-color: var(--cor-secundaria);">
                    <p class="preview-text">Exemplo de área com cor secundária</p>
                    <button type="button" class="preview-button" id="previewButton">Botão de Ação</button>
                </div>
            </div>
        </div>

        <div style="margin-top: 30px; display: flex; justify-content: flex-end;">
            <button type="submit" class="btn-save">
                <i class="fas fa-save"></i> Salvar Alterações
            </button>
        </div>
    </form>
</div>

<script>
    function updateTextInput(colorId, value) {
        document.getElementById(colorId + '_text').value = value;
        updatePreview();
    }

    function updateColorInput(colorId, value) {
        if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
            document.getElementById(colorId).value = value;
            updatePreview();
        }
    }

    function updatePreview() {
        const primaryColor = document.getElementById('primary_color').value;
        const secondaryColor = document.getElementById('secondary_color').value;
        const accentColor = document.getElementById('accent_color').value;
        
        document.documentElement.style.setProperty('--cor-principal', primaryColor);
        document.documentElement.style.setProperty('--cor-secundaria', secondaryColor);
        document.documentElement.style.setProperty('--cor-acento', accentColor);
        
        // Update preview boxes
        const previewBoxPrimary = document.getElementById('previewBoxPrimary');
        const previewBoxSecondary = document.getElementById('previewBoxSecondary');
        const previewButton = document.getElementById('previewButton');
        if (previewBoxPrimary) previewBoxPrimary.style.backgroundColor = primaryColor;
        if (previewBoxSecondary) previewBoxSecondary.style.backgroundColor = secondaryColor;
        if (previewButton) {
            previewButton.style.backgroundColor = accentColor;
            // Calculate text color based on accent color brightness
            const rgb = accentColor.match(/\w\w/g).map(x => parseInt(x, 16));
            const brightness = (rgb[0] * 299 + rgb[1] * 587 + rgb[2] * 114) / 1000;
            previewButton.style.color = brightness > 128 ? '#000000' : '#FFFFFF';
        }
    }

    // Initialize preview on load
    document.addEventListener('DOMContentLoaded', function() {
        updatePreview();
        
        // Watch for color input changes
        document.getElementById('primary_color').addEventListener('input', updatePreview);
        document.getElementById('secondary_color').addEventListener('input', updatePreview);
        document.getElementById('accent_color').addEventListener('input', updatePreview);
    });
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/settings/appearance.blade.php ENDPATH**/ ?>