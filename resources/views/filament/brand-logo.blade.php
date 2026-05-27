<div class="flex items-center gap-2" style="max-height: 3rem;">
    @if(file_exists(public_path('images/logo.png')))
        <img src="{{ asset('images/logo.png') }}" alt="Logo" style="height:36px;width:36px;border-radius:50%;object-fit:cover;">
    @else
        <div style="height:36px;width:36px;min-width:36px;border-radius:50%;background:linear-gradient(135deg,#059669,#047857);display:flex;align-items:center;justify-content:center;">
            <span style="color:white;font-weight:700;font-size:14px;">AS</span>
        </div>
    @endif
    <div style="line-height:1.2;">
        <span style="font-weight:600;font-size:13px;color:#1f2937;display:block;">Asy-Syifaa Wal Mahmuudiyyah</span>
        <span style="font-size:10px;color:#9ca3af;display:block;">Sistem Pesantren Terintegrasi Terpadu</span>
    </div>
</div>
