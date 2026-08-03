<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                @foreach ($items as $index => $item)
                    @php
                        // Default to the label if specific language keys aren't provided
                        $en = $item['data-en'] ?? $item['label'];
                        $bm = $item['data-bm'] ?? $item['label'];
                    @endphp

                    @if (!empty($item['url']) && $index + 1 < count($items))
                        <li class="breadcrumb-item">
                            <a href="{{ $item['url'] }}" data-en="{{ $en }}" data-bm="{{ $bm }}">
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @else
                        <li class="breadcrumb-item active" aria-current="page" data-en="{{ $en }}" data-bm="{{ $bm }}">
                            {{ $item['label'] }}
                        </li>
                    @endif
                @endforeach
            </ol>
        </nav>
        {{-- Assuming $title_en and $title_bm are passed, or just using attributes on the title --}}
        <h1 class="page-title fw-medium fs-18 mb-0" 
            data-en="{{ $titleEn ?? $title }}" 
            data-bm="{{ $titleBm ?? $title }}">
            {{ $title }}
        </h1>
    </div>

    <div class="btn-list">
        {{ $slot }}
    </div>
</div>