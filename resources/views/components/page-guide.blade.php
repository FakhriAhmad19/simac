@props(['title' => 'Panduan Halaman'])

@php
    // Stable key per route so the open/closed state persists across visits.
    $guideKey = 'pg-'.\Illuminate\Support\Str::slug(request()->route()?->getName() ?? request()->path());
    $guideId = 'guide-'.$guideKey;
@endphp

@once
    @push('head')
        <style>
            .page-guide { border: 1px solid #bfdbfe; background: #eff6ff; border-radius: .75rem; overflow: hidden; }
            .page-guide-toggle { color: #1d4ed8; background: transparent; border: 0; }
            .page-guide-toggle:hover { background: #dbeafe; }
            .page-guide-toggle .chevron { transition: transform .2s ease; }
            .page-guide-toggle[aria-expanded="false"] .chevron { transform: rotate(-90deg); }
            .page-guide ul { margin: 0; padding-left: 1.1rem; }
            .page-guide ul li { margin-bottom: .25rem; color: #334155; }
            .page-guide ul li:last-child { margin-bottom: 0; }
        </style>
    @endpush
    @push('scripts')
        <script>
            document.querySelectorAll('.page-guide .collapse').forEach(function (el) {
                var key = 'pageGuide:' + el.dataset.key;
                var toggle = el.previousElementSibling;
                if (localStorage.getItem(key) === 'closed') {
                    el.classList.remove('show');
                    if (toggle) toggle.setAttribute('aria-expanded', 'false');
                }
                el.addEventListener('shown.bs.collapse', function () { localStorage.setItem(key, 'open'); });
                el.addEventListener('hidden.bs.collapse', function () { localStorage.setItem(key, 'closed'); });
            });
        </script>
    @endpush
@endonce

<div class="page-guide mb-3">
    <button class="page-guide-toggle w-100 text-start d-flex align-items-center gap-2 px-3 py-2"
            type="button" data-bs-toggle="collapse" data-bs-target="#{{ $guideId }}" aria-expanded="true">
        <i class="bi bi-info-circle-fill"></i>
        <span class="fw-semibold">{{ $title }}</span>
        <i class="bi bi-chevron-down ms-auto chevron"></i>
    </button>
    <div class="collapse show" id="{{ $guideId }}" data-key="{{ $guideKey }}">
        <div class="px-3 pb-3 pt-1 small">
            {{ $slot }}
        </div>
    </div>
</div>
