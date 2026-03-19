@if(!$record?->id)
    <div style="padding:2rem;text-align:center;color:#6b7280;border:2px dashed #374151;border-radius:12px;">
        <p style="font-size:.875rem;">💾 Salve o produto primeiro para gerenciar as imagens.</p>
    </div>
@else
    @livewire('product-image-manager', ['productId' => $record->id], key('img-manager-'.$record->id))
@endif
