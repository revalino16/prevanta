{{-- resources/views/orangtua/partials/riwayat-style.blade.php
     Isi CSS halaman riwayat. Di-include lewat @include di riwayat.blade.php,
     jadi tidak bergantung pada folder public / asset() / APP_URL. --}}
<style>
/* public/css/orangtua/riwayat.css
   Semua selector diawali .rw- dan di-scope di .rw-app supaya tidak bentrok dengan CSS lain (Bootstrap/Tailwind/layout). */
.rw-app {
    --rw-brand: #A23B55;
    --rw-brand-soft: #E8707F;
    --rw-brand-tint: #F1BCC5;
    --rw-ink: #1E1B1C;
    --rw-muted: #5F5A5B;
    --rw-bg: #FAF8F6;
    --rw-side: #F6F4F1;
    --rw-tile: #F4F2EE;
    --rw-line: #ECE8E4;
    --rw-ok: #1F7A45;
    --rw-danger: #C0332B;
    --rw-warn: #B7791F;
    --rw-mint: #C9EFC7;
}
.rw-app, .rw-app *, .rw-app *::before, .rw-app *::after { box-sizing: border-box; }
.rw-app * { margin: 0; padding: 0; }
.rw-app { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; color: var(--rw-ink); font-size: 14px; }
.rw-app a { color: inherit; text-decoration: none; }
.rw-app svg { width: 18px; height: 18px; flex: none; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }







.rw-content { max-width: 1040px; padding: 28px 36px 56px; }


.rw-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap; }
.rw-head h1 { font-size: 30px; font-weight: 700; letter-spacing: -.02em; display: inline; }
.rw-tag { display: inline-block; margin-left: 12px; padding: 4px 12px; border-radius: 999px; background: #F8E6EA; color: var(--rw-brand); font-size: 11.5px; font-weight: 600; vertical-align: middle; }
.rw-head p { margin-top: 6px; color: var(--rw-muted); }
.rw-pendampingan { display: flex; align-items: center; gap: 12px; background: #fff; border: 1px solid var(--rw-line); border-radius: 16px; padding: 10px 20px; font-size: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.04); }
.rw-pendampingan .rw-dot { width: 10px; height: 10px; border-radius: 50%; background: var(--rw-brand-soft); box-shadow: 0 0 0 4px #F8DDE2; }
.rw-pendampingan b { display: block; color: var(--rw-brand); font-weight: 700; font-size: 13px; }


.rw-child { background: #fff; border-radius: 18px; padding: 16px 20px; margin-top: 24px; box-shadow: 0 2px 10px rgba(60,30,30,.06); }
.rw-child-row { display: flex; align-items: center; gap: 16px; }
.rw-child-photo { position: relative; width: 64px; height: 64px; }
.rw-child-photo img { width: 64px; height: 64px; border-radius: 14px; object-fit: cover; background: #EEE; }
.rw-child-photo span { position: absolute; right: -4px; bottom: -4px; background: var(--rw-mint); color: #1F4D28; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 8px; }
.rw-child-info small { color: var(--rw-muted); font-size: 11.5px; }
.rw-child-info .rw-meta { color: var(--rw-brand); font-weight: 600; margin-left: 12px; }
.rw-child-info h2 { font-size: 19px; font-weight: 700; margin: 2px 0; }
.rw-child-info .rw-nik { font-size: 12px; color: var(--rw-muted); }
.rw-child-info .rw-nik code { font-family: ui-monospace, monospace; font-weight: 700; color: var(--rw-ink); }
.rw-switch { margin-left: auto; position: relative; }
.rw-switch > button { display: flex; align-items: center; gap: 8px; background: #EFEBE7; border: 0; padding: 10px 16px; border-radius: 12px; font: inherit; font-weight: 600; cursor: pointer; }
.rw-switch ul { display: none; position: absolute; right: 0; top: 110%; min-width: 220px; background: #fff; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,.14); list-style: none; padding: 6px; z-index: 10; }
.rw-switch.rw-open ul { display: block; }
.rw-switch li a { display: block; padding: 10px 12px; border-radius: 8px; font-weight: 500; }
.rw-switch li a:hover, .rw-switch li a.rw-on { background: #F8E6EA; color: var(--rw-brand); }
.rw-child-note { display: flex; align-items: center; gap: 8px; margin-top: 12px; background: var(--rw-bg); border-radius: 12px; padding: 8px 16px; font-size: 12px; }
.rw-child-note svg { width: 15px; height: 15px; color: var(--rw-brand); }


.rw-tabs { display: inline-flex; gap: 4px; padding: 5px; background: #fff; border-radius: 14px; margin: 24px 0 20px; box-shadow: 0 2px 8px rgba(0,0,0,.05); }
.rw-tabs button { display: flex; align-items: center; gap: 8px; padding: 10px 18px; border: 0; border-radius: 10px; background: none; font: inherit; font-weight: 600; font-size: 13px; color: #4A4445; cursor: pointer; }
.rw-tabs button.rw-on { background: var(--rw-brand); color: #fff; }
.rw-tabs svg { width: 16px; height: 16px; }
.rw-app .rw-panel[hidden] { display: none; }


.rw-exam { background: #fff; border-radius: 16px; border-left: 8px solid var(--rw-brand); padding: 22px 26px 26px; margin-bottom: 24px; box-shadow: 0 2px 10px rgba(60,30,30,.06); }
.rw-exam-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
.rw-exam-title { display: flex; align-items: center; gap: 14px; }
.rw-exam-ico { width: 38px; height: 38px; border-radius: 10px; background: var(--rw-brand); color: #fff; display: grid; place-items: center; }
.rw-exam-title h3 { font-size: 18px; font-weight: 700; }
.rw-exam-title small { color: var(--rw-muted); font-size: 12px; }
.rw-verif { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 500; color: #2B2627; }
.rw-verif svg { width: 16px; height: 16px; color: var(--rw-ok); }
.rw-verif.rw-pending svg { color: var(--rw-warn); }

.rw-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-top: 22px; }
.rw-tile { position: relative; background: var(--rw-tile); border-radius: 14px; padding: 16px 16px 18px; overflow: hidden; }
.rw-tile.rw-alert::before { content: ''; position: absolute; top: -50px; right: -50px; width: 120px; height: 120px; border-radius: 50%; background: var(--rw-brand-tint); }
.rw-tile-label { position: relative; display: flex; justify-content: space-between; align-items: center; font-size: 11.5px; font-weight: 600; letter-spacing: .03em; text-transform: uppercase; color: #4B4546; }
.rw-tile-label svg { width: 17px; height: 17px; color: var(--rw-ok); }
.rw-tile.rw-alert .rw-tile-label svg { color: var(--rw-danger); }
.rw-val { position: relative; font-size: 30px; font-weight: 700; letter-spacing: -.02em; margin: 6px 0 18px; }
.rw-val small { font-size: 13px; font-weight: 500; color: var(--rw-muted); margin-left: 3px; letter-spacing: 0; }
.rw-row { position: relative; display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 8px; color: #4B4546; }
.rw-row b { font-weight: 700; }
.rw-text-danger { color: var(--rw-danger); }
.rw-text-ok { color: var(--rw-ok); }
.rw-text-warn { color: var(--rw-warn); }
.rw-badge { position: relative; display: block; text-align: center; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
.rw-badge-danger { background: #EA8798; color: #5C0F1E; }
.rw-badge-warn { background: #F5D98B; color: #5F4300; }
.rw-badge-ok { background: transparent; color: #1E1B1C; font-weight: 600; padding: 3px 0; }

.rw-empty { background: #fff; border-radius: 16px; padding: 40px 24px; text-align: center; color: var(--rw-muted); }
.rw-empty b { display: block; color: var(--rw-ink); font-size: 16px; margin-bottom: 4px; }


.rw-imun { background: #fff; border-radius: 16px; padding: 8px 24px; }
.rw-imun-item { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 16px 0; border-bottom: 1px solid var(--rw-line); }
.rw-imun-item:last-child { border-bottom: 0; }
.rw-imun-item small { color: var(--rw-muted); display: block; margin-top: 2px; }
.rw-pill { padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; }
.rw-pill.rw-done { background: #DDF3E2; color: var(--rw-ok); }
.rw-pill.rw-todo { background: #FBEAC5; color: #8A5A00; }

.rw-app :focus-visible { outline: 3px solid #E5788A; outline-offset: 2px; }


@media (max-width: 1100px) { .rw-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 860px) {
    .rw-content { padding: 20px 16px 40px; }
    .rw-child-row { flex-wrap: wrap; }
    .rw-switch { margin-left: 0; }
}
@media (max-width: 520px) { .rw-grid { grid-template-columns: 1fr; } .rw-exam { padding: 18px; } }
</style>
