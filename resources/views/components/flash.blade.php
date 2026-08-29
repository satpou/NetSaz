@props(['message' => null, 'type' => 'success'])

@if($message || session('message') || session('success') || session('error'))
    @php
        $flashMessage = $message ?? session('message') ?? session('success') ?? session('error');
        $flashType = session('error') ? 'error' : $type;
        $isError = $flashType === 'error';
    @endphp
    <div style="background:{{ $isError ? 'var(--red-tint)' : 'var(--green-tint)' }};border:1px solid {{ $isError ? 'var(--red)' : 'var(--green)' }};border-radius:10px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;gap:10px;font-size:14px;color:{{ $isError ? 'var(--red)' : 'var(--green)' }}">
        @if($isError)
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
        @else
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>
        @endif
        {{ $flashMessage }}
    </div>
@endif
