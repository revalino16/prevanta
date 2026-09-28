<article class="health-history-card" aria-labelledby="health-history-title">
    <header class="health-history-head">
        <div class="health-history-heading">
            <span class="health-history-icon" aria-hidden="true">
                <i class="fa-solid fa-syringe"></i>
                <i class="fa-solid fa-prescription-bottle-medical"></i>
            </span>

            <div>
                <div class="health-history-title-line">
                    <h2 id="health-history-title">Riwayat Imunisasi &amp; Suplementasi Vitamin</h2>
                    <span>{{ $healthHistory->count() }} Riwayat Tercatat</span>
                </div>
                <p>Catatan berkala imunisasi dan pemberian vitamin balita.</p>
            </div>
        </div>

        <span class="health-kia-badge">
            <i class="fa-solid fa-shield-heart"></i>
            Buku KIA Digital Posyandu
        </span>
    </header>

    @if ($healthHistory->isNotEmpty())
        <div class="health-table-scroll">
            <table class="health-history-table">
                <thead>
                    <tr>
                        <th scope="col">Jenis Imunisasi / Vitamin</th>
                        <th scope="col">Tanggal Pemberian</th>
                        <th scope="col">Status</th>
                        <th scope="col">Kader / Petugas PJ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($healthHistory as $record)
                        <tr>
                            <td>
                                <div class="health-record-kind">
                                    <span class="health-record-icon health-record-icon-{{ $record['tone'] }}" aria-hidden="true">
                                        <i class="fa-solid {{ $record['icon'] }}"></i>
                                    </span>
                                    <div class="health-record-main">
                                        <strong>{{ $record['name'] }}</strong>
                                        <small>{{ $record['description'] ?: $record['type'] }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="health-record-date">
                                <strong>{{ $record['date'] }}</strong>
                                <small>{{ $record['age'] }}</small>
                            </td>
                            <td>
                                <span class="health-record-status">
                                    <i class="fa-solid fa-check"></i>
                                    {{ $record['status'] }}
                                </span>
                            </td>
                            <td class="health-record-officer">
                                <strong>{{ $record['recorder'] }}</strong>
                                <small>{{ $record['recorderRole'] }}</small>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <footer class="health-history-footer">
            <span>
                <i class="fa-regular fa-circle-info"></i>
                Seluruh data disinkronisasikan dengan Kartu Menuju Sehat (KMS) Posyandu.
            </span>
            <strong>Diperbarui: {{ $healthLastUpdated }}</strong>
        </footer>
    @else
        <div class="measurement-empty health-empty">
            <span><i class="fa-solid fa-shield-heart"></i></span>
            <h2>Belum ada riwayat imunisasi atau vitamin</h2>
            <p>Catatan pemberian imunisasi dan vitamin anak akan tampil di bagian ini.</p>
        </div>
    @endif
</article>
