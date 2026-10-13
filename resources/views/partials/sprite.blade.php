{{-- Sprite SVG: karakter, tile peta, dan ikon. Dipakai lewat <use href="#id">. --}}
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <defs>
        {{-- ========== KIKO si Kancil (menghadap kanan) ========== --}}
        <symbol id="kiko" viewBox="0 0 64 64">
            <g stroke="#2B1020" stroke-width="2.4" stroke-linejoin="round" stroke-linecap="round">
                {{-- ekor --}}
                <path d="M13 32c-5-1-8 2-8 6 4 1 8 0 10-3z" fill="#EFECD7"/>
                {{-- kaki belakang --}}
                <rect x="15" y="40" width="6.500" height="20" rx="3.250" fill="#D8B21F"/>
                <rect x="24" y="41" width="6.500" height="19" rx="3.250" fill="#EDC92C"/>
                {{-- leher --}}
                <ellipse cx="43" cy="30" rx="7.500" ry="11" transform="rotate(-18 43 30)" fill="#EDC92C"/>
                {{-- badan --}}
                <ellipse cx="29" cy="37" rx="19" ry="11" fill="#EDC92C"/>
                {{-- kaki depan --}}
                <rect x="35" y="41" width="6.500" height="19" rx="3.250" fill="#EDC92C"/>
                <rect x="43.500" y="40" width="6.500" height="20" rx="3.250" fill="#D8B21F"/>
                {{-- telinga --}}
                <path d="M41 17C36 9 37 5 41 4c5 3 7 8 6 13z" fill="#EDC92C"/>
                <path d="M50 15c2-7 5-11 9-11 2 3 0 8-4 15z" fill="#EDC92C"/>
                {{-- kepala --}}
                <circle cx="48" cy="24" r="11.500" fill="#EDC92C"/>
            </g>
            <path d="M42.500 15.500C39 10.500 39.500 7.500 41 6.500c3 2 4.500 5 4.500 9z" fill="#E3AFC9"/>
            <path d="M52 14.500c1.500-4.500 3.500-7 5.500-7.500 1 2 0 5.500-2.500 10z" fill="#E3AFC9"/>
            <ellipse cx="29" cy="42.500" rx="12" ry="3.800" fill="#EFECD7"/>
            <g fill="#EFECD7"><circle cx="21" cy="33" r="2"/><circle cx="29" cy="31" r="2"/><circle cx="37" cy="34" r="1.800"/><circle cx="26" cy="36.500" r="1.400"/></g>
            <ellipse cx="55" cy="29" rx="6.500" ry="4.800" fill="#EFECD7" stroke="#2B1020" stroke-width="2.400"/>
            <ellipse cx="60" cy="27.500" rx="2.200" ry="1.800" fill="#2B1020"/>
            <circle cx="52" cy="22" r="3.200" fill="#2B1020"/>
            <circle cx="53.100" cy="20.900" r="1.200" fill="#fff"/>
            <circle cx="46.500" cy="28.500" r="2.800" fill="#E3AFC9"/>
            {{-- syal --}}
            <path d="M37 32c5 4 11 3 15-1l1.500 5c-5 5-13 6-19 1z" fill="#E11A45" stroke="#2B1020" stroke-width="2.400" stroke-linejoin="round"/>
            {{-- kuku kaki --}}
            <g fill="#2B1020"><rect x="15" y="56.500" width="6.500" height="3.500" rx="1.500"/><rect x="24" y="56.500" width="6.500" height="3.500" rx="1.500"/><rect x="35" y="56.500" width="6.500" height="3.500" rx="1.500"/><rect x="43.500" y="56.500" width="6.500" height="3.500" rx="1.500"/></g>
        </symbol>

        {{-- ========== PIPO si Burung Hantu ========== --}}
        <symbol id="pipo" viewBox="0 0 64 64">
            <g stroke="#2B1020" stroke-width="2.400" stroke-linejoin="round" stroke-linecap="round">
                <path d="M16 24L13 10l13 7z" fill="#E3AFC9"/>
                <path d="M48 24l3-14-13 7z" fill="#E3AFC9"/>
                <ellipse cx="32" cy="38" rx="21" ry="22" fill="#E3AFC9"/>
                <ellipse cx="11.500" cy="42" rx="6" ry="13" transform="rotate(8 11.500 42)" fill="#E11A45"/>
                <ellipse cx="52.500" cy="42" rx="6" ry="13" transform="rotate(-8 52.500 42)" fill="#E11A45"/>
                <ellipse cx="32" cy="47" rx="12" ry="12.500" fill="#EFECD7"/>
                <circle cx="22.500" cy="27" r="8.500" fill="#EFECD7"/>
                <circle cx="41.500" cy="27" r="8.500" fill="#EFECD7"/>
                <path d="M28.500 33h7L32 40.500z" fill="#EDC92C"/>
                <path d="M23 59h7M34 59h7" stroke-width="3.400" stroke="#EDC92C"/>
            </g>
            <g fill="#EDC92C"><circle cx="22.500" cy="27" r="5.200"/><circle cx="41.500" cy="27" r="5.200"/></g>
            <g fill="#2B1020"><circle cx="23" cy="27" r="3"/><circle cx="41" cy="27" r="3"/></g>
            <g fill="#fff"><circle cx="24.100" cy="25.800" r="1.100"/><circle cx="42.100" cy="25.800" r="1.100"/></g>
            <g fill="none" stroke="#E11A45" stroke-width="1.800" stroke-linecap="round"><path d="M27 46q2.500 2.500 5 0q2.500 2.500 5 0"/><path d="M27 52q2.500 2.500 5 0q2.500 2.500 5 0"/></g>
        </symbol>

        {{-- ========== Tile peta (viewBox 48) ========== --}}
        <symbol id="t0" viewBox="0 0 48 48">
            <g fill="none" stroke="#C9C39A" stroke-width="2.400" stroke-linecap="round"><path d="M14 36l1.500-6M18 36l3-5M10 36l-2-4"/><path d="M34 16l1-4M37 16l2.500-3.500"/></g>
        </symbol>
        <symbol id="t1" viewBox="0 0 48 48">
            <ellipse cx="25" cy="41" rx="15" ry="3" fill="#2B1020" opacity=".15"/>
            <path d="M8 38C5 27 12 19 17 17c3-8 12-9 18-3 6 3 8 12 5 18 1 4-1 6-4 6z" fill="#8C5A74" stroke="#2B1020" stroke-width="2.600" stroke-linejoin="round"/>
            <path d="M16 24c2-5 6-7 11-6" fill="none" stroke="#C48DAA" stroke-width="2.800" stroke-linecap="round"/>
            <path d="M31 35l3-5" fill="none" stroke="#6B4058" stroke-width="2.400" stroke-linecap="round"/>
        </symbol>
        <symbol id="t2" viewBox="0 0 48 48">
            <g fill="none" stroke="#F8F6E8" stroke-width="2.800" stroke-linecap="round" opacity=".85">
                <path d="M5 16q4-5 8 0t8 0 8 0 8 0"/><path d="M5 34q4-5 8 0t8 0 8 0 8 0"/>
            </g>
        </symbol>
        <symbol id="t3" viewBox="0 0 48 48">
            <rect x="0" y="7" width="48" height="34" fill="#EDC92C" stroke="#2B1020" stroke-width="2.600"/>
            <g stroke="#B8960F" stroke-width="2.400"><path d="M12 8v32M24 8v32M36 8v32"/></g>
            <g fill="#2B1020"><rect x="0" y="4" width="48" height="5" rx="1"/><rect x="0" y="39" width="48" height="5" rx="1"/></g>
        </symbol>
        <symbol id="t4" viewBox="0 0 48 48">
            <ellipse cx="24" cy="40" rx="15" ry="3.500" fill="#2B1020" opacity=".18"/>
            <path d="M8 30c0-10 8-17 17-17s15 7 15 16c0 6-6 10-16 10S8 36 8 30z" fill="#C48DAA" stroke="#2B1020" stroke-width="2.600" stroke-linejoin="round"/>
            <path d="M15 25c2-5 6-7 11-7" fill="none" stroke="#F3D6E5" stroke-width="2.800" stroke-linecap="round"/>
        </symbol>
        <symbol id="flag" viewBox="0 0 48 48">
            <ellipse cx="15" cy="42" rx="8" ry="3.200" fill="#EDC92C" stroke="#2B1020" stroke-width="2.400"/>
            <path d="M15 40V7" stroke="#2B1020" stroke-width="3.400" stroke-linecap="round"/>
            <path d="M17 8l23 7.500L17 24z" fill="#E11A45" stroke="#2B1020" stroke-width="2.600" stroke-linejoin="round"/>
        </symbol>

        {{-- ========== Ikon (viewBox 24, garis mengikuti warna teks) ========== --}}
        <symbol id="i-forward" viewBox="0 0 24 24"><path d="M12 20V5M6 11l6-6 6 6"/></symbol>
        <symbol id="i-turn_left" viewBox="0 0 24 24"><path d="M18 20v-7a3 3 0 0 0-3-3H6M10 5l-5 5 5 5"/></symbol>
        <symbol id="i-turn_right" viewBox="0 0 24 24"><path d="M6 20v-7a3 3 0 0 1 3-3h9M14 5l5 5-5 5"/></symbol>
        <symbol id="i-play" viewBox="0 0 24 24"><path d="M7 4.500v15l12-7.500z" fill="currentColor"/></symbol>
        <symbol id="i-reset" viewBox="0 0 24 24"><path d="M4.500 12a7.500 7.500 0 1 0 2.400-5.500M4 4v5h5"/></symbol>
        <symbol id="i-trash" viewBox="0 0 24 24"><path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13M10 11v6M14 11v6"/></symbol>
        <symbol id="i-next" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></symbol>
        <symbol id="i-close" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></symbol>
        <symbol id="i-repeat" viewBox="0 0 24 24"><path d="M17 3l4 4-4 4M3 11V9a2 2 0 0 1 2-2h16M7 21l-4-4 4-4M21 13v2a2 2 0 0 1-2 2H3"/></symbol>
        <symbol id="i-plus" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></symbol>
        <symbol id="i-minus" viewBox="0 0 24 24"><path d="M5 12h14"/></symbol>
        <symbol id="i-map" viewBox="0 0 24 24"><path d="M9 4L3 6v14l6-2 6 2 6-2V4l-6 2zM9 4v14M15 6v14"/></symbol>
        <symbol id="i-lock" viewBox="0 0 24 24"><path d="M6 11h12v9H6zM8 11V8a4 4 0 0 1 8 0v3"/></symbol>
        <symbol id="i-fork" viewBox="0 0 24 24"><path d="M12 21v-8M12 13L6 5M12 13l6-8M3 8l3-3 3 3M15 8l3-3 3 3"/></symbol>
        <symbol id="i-star" viewBox="0 0 24 24"><path d="M12 3l2.700 5.800 6.300.800-4.600 4.300 1.200 6.300L12 17.200 6.400 20.200l1.200-6.300L3 9.600l6.300-.800z" fill="currentColor"/></symbol>
    </defs>
</svg>
