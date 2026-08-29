@extends('layouts.guest')

@section('title', 'Legal - NetSaz')

@section('content')
<style>
    .legal-hero {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-radius: 24px;
        padding: 64px 32px;
        text-align: center;
        position: relative;
        overflow: hidden;
        margin-bottom: 48px;
    }

    .legal-hero::before,
    .legal-hero::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        background: rgba(255,255,255,.06);
    }

    .legal-hero::before {
        width: 256px;
        height: 256px;
        top: -128px;
        right: -64px;
    }

    .legal-hero::after {
        width: 192px;
        height: 192px;
        bottom: -96px;
        left: -64px;
    }

    .legal-hero h1 {
        color: #fff;
        font-size: clamp(28px, 5vw, 42px);
        font-weight: 700;
        margin-bottom: 12px;
        position: relative;
        z-index: 1;
    }

    .legal-hero p {
        color: rgba(255,255,255,.8);
        font-size: 17px;
        max-width: 560px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .legal-section {
        max-width: 720px;
        margin: 0 auto;
        padding: 0 24px;
        margin-bottom: 48px;
    }

    .legal-card {
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 16px;
        padding: 32px;
        margin-bottom: 32px;
        box-shadow: var(--shadow);
    }

    .legal-card h2 {
        font-size: 22px;
        font-weight: 700;
        color: var(--ink);
        margin-bottom: 24px;
    }

    .legal-card h3 {
        font-size: 16px;
        font-weight: 600;
        color: var(--ink);
        margin-top: 24px;
        margin-bottom: 12px;
    }

    .legal-card p {
        color: var(--ink-soft);
        font-size: 15px;
        line-height: 1.7;
        margin-bottom: 16px;
    }

    .legal-card ul {
        list-style: none;
        padding: 0;
        margin-bottom: 16px;
    }

    .legal-card ul li {
        color: var(--ink-soft);
        font-size: 15px;
        line-height: 1.7;
        padding: 6px 0;
        padding-left: 20px;
        position: relative;
    }

    .legal-card ul li::before {
        content: '';
        position: absolute;
        left: 0;
        top: 14px;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--primary);
    }

    .legal-card ul li strong {
        color: var(--ink);
        font-weight: 600;
    }

    @media (max-width: 640px) {
        .legal-hero {
            padding: 48px 24px;
        }

        .legal-card {
            padding: 24px;
        }
    }
</style>

<section style="padding:48px 24px">
    <div style="max-width:960px;margin:0 auto">
        <div class="legal-hero">
            <h1>Kebijakan & Legal</h1>
            <p>Kebijakan privasi, syarat & ketentuan, dan dokumen legal lain dari NetSaz.</p>
        </div>
    </div>
</section>

<section style="padding:0 24px 64px">
    <div class="legal-section">
        <div class="legal-card">
            <h2>Kebijakan Privasi</h2>
            <h3>1. Data yang Kami Kumpulkan</h3>
            <p>Kami mengumpulkan data yang Anda berikan saat mendaftar, menggunakan layanan, atau menghubungi kami. Data ini meliputi nama, email, nomor telepon, dan data penggunaan layanan.</p>
            <h3>2. Penggunaan Data</h3>
            <p>Data digunakan untuk menyediakan, memelihara, dan meningkatkan layanan; mengirim notifikasi billing dan notifikasi penting; serta memenuhi kewajiban hukum.</p>
            <h3>3. Berbagi Data</h3>
            <p>Kami tidak menjual data pribadi Anda. Data hanya dibagi ke pihak ketiga yang diperlukan untuk penyediaan layanan (misal: payment gateway) dan memenuhi kewajiban hukum.</p>
            <h3>4. Keamanan Data</h3>
            <p>Kami menerapkan enkripsi, kontrol akses, dan prosedur keamanan standar industri untuk melindungi data Anda.</p>
            <h3>5. Hak Anda</h3>
            <p>Anda berhak mengakses, memperbaiki, menghapus, atau membatasi pemrosesan data Anda. Hubungi kami untuk melaksanakan hak tersebut.</p>
            <h3>6. Perubahan Kebijakan</h3>
            <p>Kebijakan ini dapat diperbarui. Perubahan akan diberitahukan melalui email atau notifikasi di platform.</p>
        </div>

        <div class="legal-card">
            <h2>Syarat & Ketentuan</h2>
            <h3>1. Penerimaan Syarat</h3>
            <p>Dengan menggunakan NetSaz, Anda setuju terikat dengan syarat ini. Jika tidak setuju, jangan gunakan layanan ini.</p>
            <h3>2. Akun & Keamanan</h3>
            <p>Anda bertanggung jawab menjaga kerahasiaan kredensial akun dan semua aktivitas di bawah akun Anda.</p>
            <h3>3. Layanan Billing</h3>
            <p>NetSaz menyediakan platform billing untuk ISP. Kami tidak bertanggung jawab atas konten data pelanggan Anda atau penggunaan layanan oleh pelanggan Anda.</p>
            <h3>4. Pembayaran & Tagihan</h3>
            <p>Anda setuju membayar biaya langganan tepat waktu. Kegagalan pembayaran dapat mengakibatkan penangguhan layanan.</p>
            <h3>5. Properti Intelektual</h3>
            <p>Semua hak atas platform, teknologi, dan merek NetSaz milik kami. Anda tidak diperkenankan menyalin, memodifikasi, atau mendistribusikan tanpa izin tertulis.</p>
            <h3>6. Batasan Tanggung Jawab</h3>
            <p>NetSaz tidak bertanggung jawab atas kerugian tidak langsung, kehilangan data, atau kerugian bisnis akibat penggunaan layanan.</p>
            <h3>7. Pemutusan</h3>
            <p>Kami berhak menutup akun yang melanggar syarat ini dengan pemberitahuan.</p>
            <h3>8. Hukum Berlaku</h3>
            <p>Syarat ini diatur oleh hukum Republik Indonesia. Sengketa diselesaikan melalui pengadilan negeri Jakarta.</p>
        </div>

        <div class="legal-card">
            <h2>Cookie Policy</h2>
            <p>Kami menggunakan cookie untuk meningkatkan pengalaman pengguna, menganalisis lalu lintas, dan memastikan keamanan.</p>
            <h3>Jenis Cookie</h3>
            <ul>
                <li><strong>Essential:</strong> Diperlukan untuk fungsi dasar situs (login, session, keamanan).</li>
                <li><strong>Analytics:</strong> Mengukur penggunaan situs untuk perbaikan (Google Analytics).</li>
                <li><strong>Functional:</strong> Mengingat preferensi Anda (bahasa, tema).</li>
            </ul>
            <h3>Pengelolaan Cookie</h3>
            <p>Anda bisa mengatur atau menonaktifkan cookie melalui pengaturan browser. Menonaktifkan cookie essential dapat mengganggu fungsi situs.</p>
        </div>
    </div>
</section>
@endsection
