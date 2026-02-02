<?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount($name, $params)->html();
} elseif ($_instance->childHasBeenRendered('7S9a2GC')) {
    $componentId = $_instance->getRenderedChildComponentId('7S9a2GC');
    $componentTag = $_instance->getRenderedChildComponentTagName('7S9a2GC');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('7S9a2GC');
} else {
    $response = \Livewire\Livewire::mount($name, $params);
    $html = $response->html();
    $_instance->logRenderedChild('7S9a2GC', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?>
<?php /**PATH /var/www/vendor/livewire/livewire/src/Testing/../views/mount-component.blade.php ENDPATH**/ ?>