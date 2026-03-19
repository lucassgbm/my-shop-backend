<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProductImageManager extends Component
{
    use WithFileUploads;

    public ?int   $productId  = null;
    public string $primaryUuid = '';
    public $newImages = [];

    public function mount(int $productId): void
    {
        $this->productId  = $productId;
        $this->primaryUuid = Product::find($productId)?->primary_image_uuid ?? '';
    }

    public function getProductProperty(): ?Product
    {
        return Product::find($this->productId);
    }

    public function getImagesProperty()
    {
        return $this->product?->getMedia('images') ?? collect();
    }

    public function saveImages(): void
    {
        $this->validate(['newImages.*' => 'image|max:10240']);
        foreach ($this->newImages as $image) {
            $this->product
                ->addMedia($image->getRealPath())
                ->usingFileName(uniqid() . '.' . $image->getClientOriginalExtension())
                ->toMediaCollection('images');
        }
        $this->newImages = [];
        if (!$this->primaryUuid) {
            $first = $this->product->fresh()->getFirstMedia('images');
            if ($first) {
                $this->primaryUuid = $first->uuid;
                $this->product->update(['primary_image_uuid' => $first->uuid]);
            }
        }
        $this->dispatch('notify', type: 'success', message: 'Imagens enviadas!');
    }

    public function setPrimary(string $uuid): void
    {
        $this->product->update(['primary_image_uuid' => $uuid]);
        $this->primaryUuid = $uuid;
        $this->dispatch('notify', type: 'success', message: 'Imagem padrão atualizada!');
    }

    public function deleteImage(int $mediaId): void
    {
        $media = $this->product->getMedia('images')->firstWhere('id', $mediaId);
        if (!$media) return;
        if ($this->primaryUuid === $media->uuid) {
            $this->primaryUuid = '';
            $this->product->update(['primary_image_uuid' => null]);
        }
        $media->delete();
        $this->dispatch('notify', type: 'success', message: 'Imagem removida.');
    }

    public function render()
    {
        return view('livewire.product-image-manager');
    }
}
