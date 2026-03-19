<div>
    {{-- Toast --}}
    <div x-data="{ show: false, message: '', type: 'success' }"
        x-on:notify.window="message = $event.detail.message; type = $event.detail.type; show = true; setTimeout(() => show = false, 3000)"
        x-show="show" x-transition.opacity
        style="position:fixed;top:1rem;right:1rem;z-index:9999;padding:.75rem 1.25rem;border-radius:.75rem;font-size:.875rem;font-weight:600;box-shadow:0 4px 24px rgba(0,0,0,.4);display:none;"
        :style="type === 'success' ? 'background:#16a34a;color:#fff;' : 'background:#dc2626;color:#fff;'"
        x-text="message"></div>

    {{-- Upload --}}
    <div style="margin-bottom:1.5rem;">
        <label for="img-upload"
            style="display:block;border:2px dashed #4b5563;border-radius:1rem;padding:2rem;text-align:center;cursor:pointer;background:rgba(31,41,55,.5);"
            onmouseover="this.style.borderColor='#7c3aed'" onmouseout="this.style.borderColor='#4b5563'">
            <div style="display:flex;flex-direction:column;align-items:center;gap:.75rem;pointer-events:none;">
                <div style="width:3rem;height:3rem;background:#374151;border-radius:.75rem;display:flex;align-items:center;justify-content:center;">
                    <svg style="width:1.5rem;height:1.5rem;color:#9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p style="color:#d1d5db;font-weight:600;">Clique para selecionar imagens</p>
                    <p style="color:#6b7280;font-size:.8rem;margin-top:.25rem;">PNG, JPG, WEBP · máx. 10MB</p>
                </div>
            </div>
        </label>
        <input id="img-upload" type="file" multiple accept="image/*" wire:model="newImages" style="display:none">

        @if(count($newImages) > 0)
        <div style="margin-top:1rem;padding:1rem;background:#1f2937;border-radius:.75rem;border:1px solid #374151;">
            <p style="color:#d1d5db;font-size:.875rem;font-weight:600;margin-bottom:.5rem;">{{ count($newImages) }} arquivo(s) selecionado(s)</p>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:.75rem;">
                @foreach($newImages as $img)
                <span style="background:#374151;color:#9ca3af;font-size:.75rem;padding:.25rem .6rem;border-radius:.5rem;">{{ $img->getClientOriginalName() }}</span>
                @endforeach
            </div>
            <button wire:click="saveImages" wire:loading.attr="disabled"
                style="background:#7c3aed;color:#fff;border:none;border-radius:.6rem;padding:.5rem 1.25rem;font-size:.875rem;font-weight:600;cursor:pointer;"
                onmouseover="this.style.background='#6d28d9'" onmouseout="this.style.background='#7c3aed'">
                <span wire:loading.remove wire:target="saveImages">⬆ Enviar imagens</span>
                <span wire:loading wire:target="saveImages">Enviando...</span>
            </button>
            <button wire:click="$set('newImages', [])" style="background:transparent;color:#6b7280;border:none;font-size:.875rem;cursor:pointer;margin-left:.75rem;">Cancelar</button>
        </div>
        @endif
    </div>

    {{-- Grid --}}
    @if($this->images->count() > 0)
    <div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem;">
            <p style="font-size:.875rem;font-weight:600;color:#d1d5db;">
                {{ $this->images->count() }} imagem(ns)
                @if($primaryUuid) &nbsp;·&nbsp;<span style="color:#a78bfa;">1 padrão definida</span>@endif
            </p>
            @if($this->images->count() > 1)
            <p style="font-size:.75rem;color:#6b7280;">Clique para definir como padrão</p>
            @endif
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:.875rem;">
            @foreach($this->images as $media)
            @php
                $isPrimary = $primaryUuid === $media->uuid || (!$primaryUuid && $loop->first);
                $imgUrl    = $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl();
            @endphp
            <div x-data="{ hovered: false }" @mouseenter="hovered = true" @mouseleave="hovered = false"
                style="position:relative;aspect-ratio:1;border-radius:.875rem;overflow:hidden;cursor:pointer;transition:border .2s,box-shadow .2s;
                {{ $isPrimary ? 'border:2px solid #7c3aed;box-shadow:0 0 0 3px rgba(124,58,237,.3);' : 'border:2px solid #374151;' }}"
                @click="{{ $this->images->count() > 1 ? "\$wire.setPrimary('" . $media->uuid . "')" : '' }}">
                <img src="{{ $imgUrl }}" style="width:100%;height:100%;object-fit:cover;transition:transform .3s;" :style="hovered ? 'transform:scale(1.07)' : 'transform:scale(1)'" loading="lazy">
                @if(!$isPrimary && $this->images->count() > 1)
                <div style="position:absolute;inset:0;background:rgba(0,0,0,.55);display:flex;align-items:center;justify-content:center;transition:opacity .2s;" :style="hovered ? 'opacity:1' : 'opacity:0'">
                    <span style="background:#7c3aed;color:#fff;font-size:.7rem;font-weight:700;padding:.3rem .75rem;border-radius:999px;">★ Definir padrão</span>
                </div>
                @endif
                @if($isPrimary)
                <div style="position:absolute;top:.4rem;left:.4rem;background:#7c3aed;color:#fff;font-size:.65rem;font-weight:700;padding:.15rem .5rem;border-radius:999px;">★ Padrão</div>
                @endif
                <div style="position:absolute;bottom:.4rem;right:.4rem;background:rgba(0,0,0,.7);color:#d1d5db;font-size:.65rem;padding:.1rem .4rem;border-radius:999px;">#{{ $media->order_column }}</div>
                <button type="button" @click.stop="if(confirm('Remover esta imagem?')) $wire.deleteImage({{ $media->id }})"
                    style="position:absolute;top:.4rem;right:.4rem;width:1.6rem;height:1.6rem;background:#dc2626;color:#fff;border:none;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;padding:0;box-shadow:0 2px 6px rgba(0,0,0,.5);">✕</button>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
