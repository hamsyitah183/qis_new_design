<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                @foreach ($items as $index => $item)
                    @if (!empty($item['url']) && $index + 1 < count($items))
                        <li class="breadcrumb-item">
                            <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                        </li>
                    @else
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ $item['label'] }}
                        </li>
                    @endif
                @endforeach
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">{{ $title }}</h1>
    </div>

    <div class="btn-list">
        {{ $slot }}
    </div>
</div>
