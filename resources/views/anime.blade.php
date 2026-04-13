<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyAnimeList Explorer</title>
    <meta name="description" content="Explore and discover anime by season, search by title, and view detailed information.">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ===== RESET & BASE ===== */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #0f0d1b;
        }
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #6c5ce7, #a855f7);
            border-radius: 4px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f0d1b 0%, #1a1333 40%, #15112b 70%, #0f0d1b 100%);
            min-height: 100vh;
            color: #e2e0f0;
            overflow-x: hidden;
        }

        /* Animated background orbs */
        body::before,
        body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.15;
            z-index: 0;
            pointer-events: none;
        }
        body::before {
            width: 600px;
            height: 600px;
            background: #6c5ce7;
            top: -200px;
            right: -100px;
            animation: floatOrb 20s ease-in-out infinite;
        }
        body::after {
            width: 500px;
            height: 500px;
            background: #a855f7;
            bottom: -150px;
            left: -100px;
            animation: floatOrb 25s ease-in-out infinite reverse;
        }

        @keyframes floatOrb {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(40px, -30px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.95); }
        }

        /* ===== CONTAINER ===== */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 24px 60px;
            position: relative;
            z-index: 1;
        }

        /* ===== HEADER ===== */
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 40px 0 20px;
        }

        .header h1 {
            font-family: 'Outfit', sans-serif;
            font-size: clamp(2rem, 4vw, 2.8rem);
            font-weight: 700;
            color: #a89bdb;
            letter-spacing: -0.5px;
        }



        .header p {
            font-size: 1rem;
            color: rgba(226, 224, 240, 0.5);
            margin-top: 8px;
            font-weight: 300;
            animation: fadeInUp 1s ease 0.3s both;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ===== CONTROLS ===== */
        .controls {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 32px;
            padding: 20px;
            background: rgba(30, 25, 55, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 16px;
            border: 1px solid rgba(108, 92, 231, 0.15);
            animation: fadeInUp 0.8s ease 0.5s both;
        }

        .search-box {
            display: flex;
            flex: 1.5;
            min-width: 280px;
            gap: 10px;
        }

        .filter-box {
            display: flex;
            flex: 1;
            min-width: 220px;
        }

        .controls input,
        .controls select {
            flex: 1;
            padding: 12px 18px;
            background: rgba(15, 13, 27, 0.6);
            border: 1px solid rgba(108, 92, 231, 0.25);
            border-radius: 12px;
            color: #e2e0f0;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            outline: none;
        }

        .controls input::placeholder {
            color: rgba(226, 224, 240, 0.35);
        }

        .controls select.placeholder {
            color: rgba(226, 224, 240, 0.35);
        }

        .controls input:focus,
        .controls select:focus {
            border-color: #6c5ce7;
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.15), 0 0 20px rgba(108, 92, 231, 0.1);
        }

        .controls select option {
            background: #1a1333;
            color: #e2e0f0;
        }

        .btn-search {
            padding: 12px 28px;
            background: linear-gradient(135deg, #6c5ce7, #a855f7);
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            white-space: nowrap;
            position: relative;
            overflow: hidden;
        }

        .btn-search::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transition: left 0.5s ease;
        }

        .btn-search:hover::before {
            left: 100%;
        }

        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(108, 92, 231, 0.4);
        }

        .btn-search:active {
            transform: translateY(0) scale(0.97);
        }

        /* ===== STATUS MESSAGES ===== */
        .status {
            text-align: center;
            margin: 20px 0;
            display: none;
            font-weight: 500;
            color: rgba(226, 224, 240, 0.6);
        }

        .error {
            color: #ff6b6b;
        }

        /* ===== SKELETON LOADING ===== */
        .skeleton-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 24px;
        }

        .skeleton-card {
            border-radius: 16px;
            overflow: hidden;
            background: rgba(30, 25, 55, 0.5);
            border: 1px solid rgba(108, 92, 231, 0.1);
        }

        .skeleton-img {
            width: 100%;
            height: 280px;
            background: linear-gradient(90deg, rgba(30, 25, 55, 0.5) 25%, rgba(50, 40, 80, 0.5) 50%, rgba(30, 25, 55, 0.5) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        .skeleton-text {
            height: 16px;
            margin: 14px;
            border-radius: 8px;
            background: linear-gradient(90deg, rgba(30, 25, 55, 0.5) 25%, rgba(50, 40, 80, 0.5) 50%, rgba(30, 25, 55, 0.5) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* ===== ANIME GRID ===== */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 24px;
        }

        /* ===== ANIME CARD ===== */
        .card {
            border-radius: 16px;
            overflow: hidden;
            cursor: pointer;
            background: rgba(30, 25, 55, 0.5);
            border: 1px solid rgba(108, 92, 231, 0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            opacity: 0;
            transform: translateY(30px);
            animation: cardEnter 0.6s ease forwards;
            position: relative;
        }

        .card:hover {
            transform: translateY(-8px) scale(1.03);
            border-color: rgba(108, 92, 231, 0.4);
            box-shadow:
                0 20px 40px rgba(0, 0, 0, 0.4),
                0 0 30px rgba(108, 92, 231, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }

        @keyframes cardEnter {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-image-wrapper {
            position: relative;
            overflow: hidden;
        }

        .card-image-wrapper img {
            width: 100%;
            height: 280px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .card:hover .card-image-wrapper img {
            transform: scale(1.08);
        }

        .card-image-wrapper::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50%;
            background: linear-gradient(to top, rgba(15, 13, 27, 0.95), transparent);
            pointer-events: none;
        }

        .card-score {
            position: absolute;
            top: 12px;
            right: 12px;
            background: linear-gradient(135deg, #6c5ce7, #a855f7);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 4px;
            box-shadow: 0 4px 12px rgba(108, 92, 231, 0.4);
        }

        .card-score svg {
            width: 12px;
            height: 12px;
        }

        .card-type {
            position: absolute;
            top: 12px;
            left: 12px;
            background: rgba(15, 13, 27, 0.7);
            backdrop-filter: blur(8px);
            color: rgba(226, 224, 240, 0.9);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            z-index: 2;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-info {
            padding: 14px 16px 18px;
            position: relative;
        }

        .card-info h3 {
            font-family: 'Outfit', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            color: #f0eef8;
            line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 6px;
        }

        .card-meta {
            font-size: 0.75rem;
            color: rgba(226, 224, 240, 0.4);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-meta span {
            display: flex;
            align-items: center;
            gap: 3px;
        }

        /* ===== MODAL ===== */
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            justify-content: center;
            align-items: center;
            z-index: 1000;
            padding: 20px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal.active {
            opacity: 1;
        }

        .modal-content {
            background: rgba(26, 19, 51, 0.95);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid rgba(108, 92, 231, 0.2);
            padding: 0;
            border-radius: 24px;
            max-width: 680px;
            width: 100%;
            max-height: 85vh;
            overflow-y: auto;
            transform: translateY(30px) scale(0.95);
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5), 0 0 40px rgba(108, 92, 231, 0.1);
        }

        .modal.active .modal-content {
            transform: translateY(0) scale(1);
        }

        .modal-content::-webkit-scrollbar {
            width: 6px;
        }
        .modal-content::-webkit-scrollbar-thumb {
            background: rgba(108, 92, 231, 0.3);
            border-radius: 3px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 28px 28px 0;
        }

        .modal-header h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.6rem;
            font-weight: 700;
            color: #f0eef8;
            line-height: 1.3;
            flex: 1;
            padding-right: 16px;
        }

        .close-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(108, 92, 231, 0.15);
            border: 1px solid rgba(108, 92, 231, 0.2);
            color: #a89bdb;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 18px;
            flex-shrink: 0;
        }

        .close-btn:hover {
            background: rgba(108, 92, 231, 0.3);
            color: white;
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 20px 28px 28px;
        }

        .modal-score-bar {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
            padding: 16px;
            background: rgba(108, 92, 231, 0.08);
            border-radius: 14px;
            border: 1px solid rgba(108, 92, 231, 0.1);
        }

        .modal-score-value {
            font-family: 'Outfit', sans-serif;
            font-size: 2.2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #6c5ce7, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .modal-score-label {
            font-size: 0.85rem;
            color: rgba(226, 224, 240, 0.5);
        }

        .modal-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }

        .info-item {
            padding: 14px;
            background: rgba(15, 13, 27, 0.4);
            border-radius: 12px;
            border: 1px solid rgba(108, 92, 231, 0.08);
        }

        .info-item-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(226, 224, 240, 0.4);
            margin-bottom: 6px;
            font-weight: 600;
        }

        .info-item-value {
            font-family: 'Outfit', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            color: #f0eef8;
        }

        .modal-genres {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 20px;
        }

        .genre-tag {
            padding: 5px 14px;
            background: rgba(108, 92, 231, 0.12);
            border: 1px solid rgba(108, 92, 231, 0.2);
            border-radius: 20px;
            font-size: 0.78rem;
            color: #a89bdb;
            font-weight: 500;
        }

        .modal-synopsis-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.1rem;
            font-weight: 600;
            color: #f0eef8;
            margin-bottom: 10px;
        }

        .modal-synopsis {
            font-size: 0.92rem;
            line-height: 1.75;
            color: rgba(226, 224, 240, 0.65);
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            color: rgba(226, 224, 240, 0.4);
        }

        .empty-state svg {
            width: 80px;
            height: 80px;
            margin-bottom: 16px;
            opacity: 0.3;
        }

        .empty-state p {
            font-size: 1.1rem;
            font-weight: 500;
        }

        /* ===== FOOTER ===== */
        .footer {
            text-align: center;
            padding: 40px 0 20px;
            color: rgba(226, 224, 240, 0.25);
            font-size: 0.8rem;
        }

        .footer a {
            color: #6c5ce7;
            text-decoration: none;
            font-weight: 500;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .container {
                padding: 16px 16px 40px;
            }

            .header {
                margin-bottom: 24px;
                padding: 24px 0 12px;
            }

            .controls {
                padding: 16px;
                gap: 10px;
            }

            .search-box,
            .filter-box {
                min-width: 100%;
            }

            .grid,
            .skeleton-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 14px;
            }

            .card-image-wrapper img {
                height: 220px;
            }

            .modal-content {
                border-radius: 18px;
            }

            .modal-header {
                padding: 20px 20px 0;
            }

            .modal-body {
                padding: 16px 20px 20px;
            }

            .modal-header h2 {
                font-size: 1.3rem;
            }
        }

        @media (max-width: 480px) {
            .grid,
            .skeleton-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }

            .card-image-wrapper img {
                height: 200px;
            }

            .card-info {
                padding: 10px 12px 14px;
            }

            .card-info h3 {
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <header class="header">
        <h1>Anime Explorer</h1>
        <p>Temukan anime favorit kamu dari berbagai musim & tahun</p>
    </header>

    <!-- Controls -->
    <div class="controls">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="🔍 Cari judul anime..." onkeydown="if(event.key==='Enter') cariAnime()">
            <button class="btn-search" onclick="cariAnime()">Cari</button>
        </div>

        <div class="filter-box">
            <select id="seasonFilter" class="placeholder" onchange="filterMusim(); this.classList.toggle('placeholder', !this.value)">
                <option value="">— Pilih Musim & Tahun —</option>
                <option value="upcoming">🌟 Upcoming (Mendatang)</option>
                <optgroup label="Tahun 2026">
                    <option value="2026/fall">🍂 Fall 2026</option>
                    <option value="2026/summer">☀️ Summer 2026</option>
                    <option value="2026/spring">🌸 Spring 2026</option>
                    <option value="2026/winter">❄️ Winter 2026</option>
                </optgroup>
                <optgroup label="Tahun 2025">
                    <option value="2025/fall">🍂 Fall 2025</option>
                    <option value="2025/summer">☀️ Summer 2025</option>
                    <option value="2025/spring">🌸 Spring 2025</option>
                    <option value="2025/winter">❄️ Winter 2025</option>
                </optgroup>
                <optgroup label="Tahun 2024">
                    <option value="2024/fall">🍂 Fall 2024</option>
                    <option value="2024/summer">☀️ Summer 2024</option>
                    <option value="2024/spring">🌸 Spring 2024</option>
                    <option value="2024/winter">❄️ Winter 2024</option>
                </optgroup>
            </select>
        </div>
    </div>

    <!-- Status Messages -->
    <div id="loading" class="status"></div>
    <div id="error" class="status error">Waduh, terjadi kesalahan saat mengambil data. Coba lagi nanti.</div>

    <!-- Skeleton Loading -->
    <div id="skeletonGrid" class="skeleton-grid" style="display: none;">
        <div class="skeleton-card"><div class="skeleton-img"></div><div class="skeleton-text"></div></div>
        <div class="skeleton-card"><div class="skeleton-img"></div><div class="skeleton-text"></div></div>
        <div class="skeleton-card"><div class="skeleton-img"></div><div class="skeleton-text"></div></div>
        <div class="skeleton-card"><div class="skeleton-img"></div><div class="skeleton-text"></div></div>
        <div class="skeleton-card"><div class="skeleton-img"></div><div class="skeleton-text"></div></div>
        <div class="skeleton-card"><div class="skeleton-img"></div><div class="skeleton-text"></div></div>
    </div>

    <!-- Results Grid -->
    <div id="resultGrid" class="grid"></div>

    <!-- Footer -->
    <footer class="footer">
        <p>Powered by <a href="https://jikan.moe/" target="_blank" rel="noopener">Jikan API</a> — MyAnimeList Explorer</p>
    </footer>
</div>

<!-- Modal -->
<div id="animeModal" class="modal" onclick="if(event.target===this) tutupModal()">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Judul Anime</h2>
            <span class="close-btn" onclick="tutupModal()">✕</span>
        </div>
        <div class="modal-body">
            <div class="modal-score-bar">
                <div>
                    <div class="modal-score-value" id="modalScore">—</div>
                    <div class="modal-score-label">Skor MAL</div>
                </div>
                <div class="modal-info-grid" style="flex:1; margin-bottom:0;">
                    <div class="info-item">
                        <div class="info-item-label">Episode</div>
                        <div class="info-item-value" id="modalEpisodes">—</div>
                    </div>
                    <div class="info-item">
                        <div class="info-item-label">Status</div>
                        <div class="info-item-value" id="modalStatus">—</div>
                    </div>
                </div>
            </div>

            <div id="modalGenres" class="modal-genres"></div>

            <div class="modal-synopsis-title">Sinopsis</div>
            <p id="modalSynopsis" class="modal-synopsis">Sinopsis tidak tersedia.</p>
        </div>
    </div>
</div>

<script>
    let dataAnime = [];

    async function fetchAPI(url) {
        const errorMsg = document.getElementById('error');
        const grid = document.getElementById('resultGrid');
        const skeleton = document.getElementById('skeletonGrid');

        grid.innerHTML = '';
        errorMsg.style.display = 'none';
        skeleton.style.display = 'grid';

        try {
            const response = await axios.get(url);
            dataAnime = response.data.data;
            skeleton.style.display = 'none';
            tampilkanData(dataAnime);
        } catch (error) {
            skeleton.style.display = 'none';
            errorMsg.style.display = 'block';
            console.error(error);
        }
    }

    function cariAnime() {
        const query = document.getElementById('searchInput').value.trim();
        if (!query) return;

        document.getElementById('seasonFilter').value = '';
        fetchAPI(`https://api.jikan.moe/v4/anime?q=${encodeURIComponent(query)}&limit=12`);
    }

    function filterMusim() {
        const filterValue = document.getElementById('seasonFilter').value;
        if (!filterValue) return;

        document.getElementById('searchInput').value = '';

        const url = filterValue === 'upcoming'
            ? 'https://api.jikan.moe/v4/seasons/upcoming?limit=12'
            : `https://api.jikan.moe/v4/seasons/${filterValue}?limit=12`;
        fetchAPI(url);
    }

    function tampilkanData(animeList) {
        const grid = document.getElementById('resultGrid');

        if (animeList.length === 0) {
            grid.innerHTML = `
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M21 21l-5.197-5.197M16.804 16.804A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0011.608 11.608z"/>
                    </svg>
                    <p>Anime tidak ditemukan. Coba kata kunci lain!</p>
                </div>`;
            return;
        }

        animeList.forEach((anime, index) => {
            const card = document.createElement('div');
            card.className = 'card';
            card.style.animationDelay = `${index * 0.07}s`;
            card.onclick = () => bukaModal(index);

            const score = anime.score ? anime.score.toFixed(1) : null;
            const type = anime.type || '';
            const episodes = anime.episodes ? `${anime.episodes} ep` : '';

            card.innerHTML = `
                <div class="card-image-wrapper">
                    <img src="${anime.images.jpg.large_image_url || anime.images.jpg.image_url}" alt="${anime.title}" loading="lazy">
                    ${score ? `<div class="card-score">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        ${score}
                    </div>` : ''}
                    ${type ? `<div class="card-type">${type}</div>` : ''}
                </div>
                <div class="card-info">
                    <h3>${anime.title}</h3>
                    <div class="card-meta">
                        ${episodes ? `<span>📺 ${episodes}</span>` : ''}
                        ${anime.season ? `<span>📅 ${anime.season} ${anime.year || ''}</span>` : ''}
                    </div>
                </div>
            `;
            grid.appendChild(card);
        });
    }

    function bukaModal(index) {
        const anime = dataAnime[index];

        document.getElementById('modalTitle').innerText = anime.title;
        document.getElementById('modalScore').innerText = anime.score ? anime.score.toFixed(1) : '—';
        document.getElementById('modalEpisodes').innerText = anime.episodes || '?';
        document.getElementById('modalStatus').innerText = anime.status || '—';
        document.getElementById('modalSynopsis').innerText = anime.synopsis || 'Sinopsis tidak tersedia.';

        // Genres
        const genresEl = document.getElementById('modalGenres');
        genresEl.innerHTML = '';
        if (anime.genres && anime.genres.length > 0) {
            anime.genres.forEach(g => {
                genresEl.innerHTML += `<span class="genre-tag">${g.name}</span>`;
            });
        }

        const modal = document.getElementById('animeModal');
        modal.style.display = 'flex';
        // Trigger animation
        requestAnimationFrame(() => {
            modal.classList.add('active');
        });
    }

    function tutupModal() {
        const modal = document.getElementById('animeModal');
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }

    // Close modal with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') tutupModal();
    });

    // Load current season on page load
    window.onload = () => {
        fetchAPI('https://api.jikan.moe/v4/seasons/now?limit=12');
    };
</script>

</body>
</html>