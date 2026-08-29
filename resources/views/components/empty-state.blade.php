@props(['title', 'description' => null])

<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px 20px;text-align:center">
    @if(isset($icon))
        <div style="width:56px;height:56px;border-radius:14px;background:var(--bg);display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
            {{ $icon }}
        </div>
    @endif
    <div style="font-size:15px;color:var(--ink-soft);margin-bottom:{{ $description ? '6px' : '20px' }}">{{ $title }}</div>
    @if($description)
        <div style="font-size:13px;color:var(--ink-faint);margin-bottom:20px;max-width:360px">{{ $description }}</div>
    @endif
    {{ $slot }}
</div>
