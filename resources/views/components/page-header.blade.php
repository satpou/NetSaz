@props(['title', 'subtitle' => null])

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px">
    <div>
        <h1 style="font-size:26px;font-weight:700;color:var(--ink)">{{ $title }}</h1>
        @if($subtitle)
            <p style="color:var(--ink-soft);font-size:14px">{{ $subtitle }}</p>
        @endif
    </div>
    {{ $slot }}
</div>
