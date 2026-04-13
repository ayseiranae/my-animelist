<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyAnimeList Explorer</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .controls {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .search-box, .filter-box {
            display: flex;
            flex: 1;
            min-width: 250px;
            gap: 10px;
        }
        input, select {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            padding: 10px 20px;
            background-color: #2e51a2;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background-color: #1c3664;
        }
        .status {
            text-align: center;
            margin: 10px 0;
            display: none;
            font-weight: bold;
        }
        .error {
            color: red;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.2s;
            background: #fff;
        }
        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        .card h3 {
            font-size: 14px;
            padding: 10px;
            margin: 0;
            text-align: center;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.7);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        .close-btn {
            float: right;
            cursor: pointer;
            font-weight: bold;
            font-size: 20px;
            color: #555;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Anime Explorer</h2>
    
    <div class="controls">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Ketik judul anime...">
            <button onclick="cariAnime()">Cari</button>
        </div>
        
        <div class="filter-box">
            <select id="seasonFilter" onchange="filterMusim()">
                <option value="">-- Pilih Musim & Tahun --</option>
                <option value="upcoming">🌟 Upcoming (Mendatang)</option>
                <optgroup label="Tahun 2026">
                    <option value="2026/fall">Fall 2026</option>
                    <option value="2026/summer">Summer 2026</option>
                    <option value="2026/spring">Spring 2026</option>
                    <option value="2026/winter">Winter 2026</option>
                </optgroup>
                <optgroup label="Tahun 2025">
                    <option value="2025/fall">Fall 2025</option>
                    <option value="2025/summer">Summer 2025</option>
                    <option value="2025/spring">Spring 2025</option>
                    <option value="2025/winter">Winter 2025</option>
                </optgroup>
                <optgroup label="Tahun 2024">
                    <option value="2024/fall">Fall 2024</option>
                    <option value="2024/summer">Summer 2024</option>
                    <option value="2024/spring">Spring 2024</option>
                    <option value="2024/winter">Winter 2024</option>
                </optgroup>
            </select>
        </div>
    </div>
    
    <div id="loading" class="status">Mengambil data dari server...</div>
    <div id="error" class="status error">Waduh, terjadi kesalahan saat mengambil data. Coba lagi nanti.</div>
    
    <div id="resultGrid" class="grid"></div>
</div>

<div id="animeModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="tutupModal()">X</span>
        <h2 id="modalTitle">Judul</h2>
        <p><strong>Skor:</strong> <span id="modalScore"></span></p>
        <p><strong>Episode:</strong> <span id="modalEpisodes"></span></p>
        <p><strong>Status:</strong> <span id="modalStatus"></span></p>
        <p><strong>Sinopsis:</strong></p>
        <p id="modalSynopsis"></p>
    </div>
</div>

<script>
    let dataAnime = [];

    async function fetchAPI(url) {
        const loading = document.getElementById('loading');
        const errorMsg = document.getElementById('error');
        const grid = document.getElementById('resultGrid');

        grid.innerHTML = '';
        errorMsg.style.display = 'none';
        loading.style.display = 'block';

        try {
            const response = await axios.get(url);
            dataAnime = response.data.data;
            tampilkanData(dataAnime);
        } catch (error) {
            errorMsg.style.display = 'block';
            console.error(error);
        } finally {
            loading.style.display = 'none';
        }
    }

    function cariAnime() {
        const query = document.getElementById('searchInput').value;
        if (!query) return;
        
        document.getElementById('seasonFilter').value = '';
        
        const url = `https://api.jikan.moe/v4/anime?q=${query}&limit=12`;
        fetchAPI(url);
    }

    function filterMusim() {
        const filterValue = document.getElementById('seasonFilter').value;
        if (!filterValue) return;

        document.getElementById('searchInput').value = '';

        const url = `https://api.jikan.moe/v4/seasons/${filterValue}?limit=12`;
        fetchAPI(url);
    }

    function tampilkanData(animeList) {
        const grid = document.getElementById('resultGrid');
        
        if (animeList.length === 0) {
            grid.innerHTML = '<p style="grid-column: 1 / -1; text-align: center;">Data tidak ditemukan.</p>';
            return;
        }

        animeList.forEach((anime, index) => {
            const card = document.createElement('div');
            card.className = 'card';
            card.onclick = () => bukaModal(index);
            
            card.innerHTML = `
                <img src="${anime.images.jpg.image_url}" alt="${anime.title}">
                <h3>${anime.title}</h3>
            `;
            grid.appendChild(card);
        });
    }

    function bukaModal(index) {
        const anime = dataAnime[index];
        document.getElementById('modalTitle').innerText = anime.title;
        document.getElementById('modalScore').innerText = anime.score || 'Belum ada rating';
        document.getElementById('modalEpisodes').innerText = anime.episodes || 'Tidak diketahui';
        document.getElementById('modalStatus').innerText = anime.status || '-';
        document.getElementById('modalSynopsis').innerText = anime.synopsis || 'Sinopsis tidak tersedia.';
        
        document.getElementById('animeModal').style.display = 'flex';
    }

    function tutupModal() {
        document.getElementById('animeModal').style.display = 'none';
    }

    window.onload = () => {
        fetchAPI('https://api.jikan.moe/v4/seasons/now?limit=12');
    };
</script>

</body>
</html>