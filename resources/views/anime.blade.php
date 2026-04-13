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
            background-color: #080612;
            min-height: 100vh;
            color: #e2e0f0;
            overflow-x: hidden;
            position: relative;
        }

        /* iOS 26 Glassmorphism Background */
        .bg-mirror {
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .bg-orb {
            position: absolute;
            border-radius: 50%;
            opacity: 0.7;
            filter: blur(80px);
            animation: floatOrb 20s ease-in-out infinite;
        }

        .bg-orb-1 {
            width: 50vw; height: 50vw;
            background: rgba(108, 92, 231, 0.7);
            top: -10vw; right: -10vw;
            animation-duration: 25s;
        }

        .bg-orb-2 {
            width: 45vw; height: 45vw;
            background: rgba(168, 85, 247, 0.6);
            bottom: -10vw; left: -10vw;
            animation-duration: 28s;
            animation-direction: reverse;
        }

        .bg-orb-3 {
            width: 40vw; height: 40vw;
            background: rgba(43, 27, 84, 0.8);
            top: 30vh; left: 30vw;
            animation-duration: 22s;
            animation-delay: -5s;
        }

        .bg-glass-layer {
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            backdrop-filter: blur(70px) saturate(180%);
            -webkit-backdrop-filter: blur(70px) saturate(180%);
            background: rgba(15, 13, 27, 0.35); /* Base tint matching original theme */
            z-index: 0;
            pointer-events: none;
        }

        @keyframes floatOrb {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(6vw, -4vh) scale(1.15); }
            66% { transform: translate(-4vw, 4vh) scale(0.85); }
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
            position: relative;
            z-index: 100;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 32px;
            padding: 20px;
            background: rgba(25, 20, 45, 0.25);
            backdrop-filter: blur(24px) saturate(150%);
            -webkit-backdrop-filter: blur(24px) saturate(150%);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-top: 1px solid rgba(255, 255, 255, 0.25);
            border-left: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.05);
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

        .controls input {
            flex: 1;
            padding: 12px 18px;
            background: rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            color: #f0eef8;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            outline: none;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .controls input::placeholder {
            color: rgba(226, 224, 240, 0.35);
        }

        .controls input:focus {
            border-color: rgba(168, 85, 247, 0.6);
            box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.15), inset 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* ===== CUSTOM GLASS SELECT ===== */
        .custom-select-container {
            position: relative;
            flex: 1;
            min-width: 240px;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            z-index: 50; 
        }

        .custom-select-trigger {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 18px;
            background: rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            color: #f0eef8;
            cursor: pointer;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            user-select: none;
        }

        .custom-select-trigger:hover,
        .custom-select-container.open .custom-select-trigger {
            border-color: rgba(168, 85, 247, 0.6);
            box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.15), inset 0 2px 4px rgba(0, 0, 0, 0.2);
            background: rgba(25, 20, 45, 0.4);
        }

        .custom-select-trigger span {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .custom-select-trigger .chevron {
            width: 18px;
            height: 18px;
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            opacity: 0.7;
            flex-shrink: 0;
            margin-left: 10px;
        }

        .custom-select-container.open .custom-select-trigger .chevron {
            transform: rotate(180deg);
        }

        .custom-options-panel {
            position: absolute;
            top: calc(100% + 12px);
            left: 0;
            right: 0;
            background: rgba(18, 14, 32, 0.95);
            backdrop-filter: blur(40px) saturate(200%);
            -webkit-backdrop-filter: blur(40px) saturate(200%);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-top: 1px solid rgba(255, 255, 255, 0.25);
            border-left: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 18px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.05);
            padding: 8px;
            display: flex;
            flex-direction: column;
            gap: 2px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px) scale(0.98);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            max-height: 350px;
            overflow-y: auto;
            transform-origin: top center;
        }

        .custom-select-container.open .custom-options-panel {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
        }

        .custom-options-panel::-webkit-scrollbar { width: 4px; }
        .custom-options-panel::-webkit-scrollbar-thumb { background: rgba(168, 85, 247, 0.4); border-radius: 10px; }
        
        .custom-option {
            padding: 10px 14px;
            border-radius: 12px;
            cursor: pointer;
            color: rgba(240, 238, 248, 0.85);
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }

        .custom-option:hover, .custom-option.selected {
            background: rgba(168, 85, 247, 0.3);
            color: #fff;
            transform: translateX(4px);
        }

        .custom-option.selected {
            font-weight: 600;
        }

        .custom-option-group-label {
            padding: 16px 14px 6px;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(168, 85, 247, 0.7);
            font-weight: 700;
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
            border-radius: 20px;
            overflow: hidden;
            background: rgba(30, 25, 55, 0.2);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-top: 1px solid rgba(255, 255, 255, 0.15);
        }

        .skeleton-img {
            width: 100%;
            height: 280px;
            background: linear-gradient(90deg, rgba(255, 255, 255, 0.03) 25%, rgba(255, 255, 255, 0.08) 50%, rgba(255, 255, 255, 0.03) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        .skeleton-text {
            height: 16px;
            margin: 14px;
            border-radius: 8px;
            background: linear-gradient(90deg, rgba(255, 255, 255, 0.03) 25%, rgba(255, 255, 255, 0.08) 50%, rgba(255, 255, 255, 0.03) 75%);
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

        .section-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.6rem;
            font-weight: 700;
            color: #f0eef8;
            margin: 48px 0 24px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 10px;
            text-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }

        /* ===== ANIME CARD ===== */
        .card {
            border-radius: 20px;
            overflow: hidden;
            cursor: pointer;
            background: rgba(30, 25, 55, 0.15);
            backdrop-filter: blur(16px) saturate(150%);
            -webkit-backdrop-filter: blur(16px) saturate(150%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            border-left: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            opacity: 0;
            transform: translateY(30px);
            animation: cardEnter 0.6s ease forwards;
            position: relative;
        }

        .card:hover {
            transform: translateY(-8px) scale(1.03);
            background: rgba(40, 32, 70, 0.25);
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow:
                0 20px 40px rgba(0, 0, 0, 0.3),
                0 0 30px rgba(168, 85, 247, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.15);
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
            transition: all 0.5s ease;
        }

        .card:hover .card-image-wrapper img {
            transform: scale(1.08);
        }

        .card-image-wrapper img.blurred-img {
            filter: blur(20px) brightness(0.6);
            transform: scale(1.1);
        }
        
        .card:hover .card-image-wrapper img.blurred-img {
            transform: scale(1.15); /* Keep overscaled on hover so edges don't show */
        }

        .nsfw-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 13, 27, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            color: #ff6b6b;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            backdrop-filter: blur(8px);
            transition: opacity 0.3s ease;
        }

        .nsfw-overlay span {
            background: rgba(0, 0, 0, 0.75);
            padding: 8px 14px;
            border-radius: 20px;
            border: 1px solid rgba(255, 107, 107, 0.4);
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
            pointer-events: none;
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
            background: rgba(26, 19, 51, 0.4);
            backdrop-filter: blur(40px) saturate(200%);
            -webkit-backdrop-filter: blur(40px) saturate(200%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-top: 1px solid rgba(255, 255, 255, 0.3);
            border-left: 1px solid rgba(255, 255, 255, 0.2);
            padding: 0;
            border-radius: 28px;
            max-width: 680px;
            width: 100%;
            max-height: 85vh;
            overflow-y: auto;
            transform: translateY(30px) scale(0.95);
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.1);
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

            .section-title {
                margin: 32px 0 16px;
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

<!-- iOS Glass Background Layers -->
<div class="bg-mirror">
    <div class="bg-orb bg-orb-1"></div>
    <div class="bg-orb bg-orb-2"></div>
    <div class="bg-orb bg-orb-3"></div>
</div>
<div class="bg-glass-layer"></div>

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

        <div class="custom-select-container" id="seasonSelectContainer">
            <div class="custom-select-trigger" onclick="toggleSelect()">
                <span id="customSelectLabel">— Pilih Musim & Tahun —</span>
                <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
            </div>
            <div class="custom-options-panel" id="customOptionsPanel">
                <div class="custom-option" onclick="pilihMusim('', '🔄 Reset Pilihan Terbaru', event)">🔄 Reset / Terbaru</div>
                <div class="custom-option" onclick="pilihMusim('upcoming', '🌟 Upcoming (Mendatang)', event)">🌟 Upcoming (Mendatang)</div>
                
                <div class="custom-option-group-label">Tahun 2026</div>
                <div class="custom-option" onclick="pilihMusim('2026/fall', '🍂 Fall 2026', event)">🍂 Fall 2026</div>
                <div class="custom-option" onclick="pilihMusim('2026/summer', '☀️ Summer 2026', event)">☀️ Summer 2026</div>
                <div class="custom-option" onclick="pilihMusim('2026/spring', '🌸 Spring 2026', event)">🌸 Spring 2026</div>
                <div class="custom-option" onclick="pilihMusim('2026/winter', '❄️ Winter 2026', event)">❄️ Winter 2026</div>
                
                <div class="custom-option-group-label">Tahun 2025</div>
                <div class="custom-option" onclick="pilihMusim('2025/fall', '🍂 Fall 2025', event)">🍂 Fall 2025</div>
                <div class="custom-option" onclick="pilihMusim('2025/summer', '☀️ Summer 2025', event)">☀️ Summer 2025</div>
                <div class="custom-option" onclick="pilihMusim('2025/spring', '🌸 Spring 2025', event)">🌸 Spring 2025</div>
                <div class="custom-option" onclick="pilihMusim('2025/winter', '❄️ Winter 2025', event)">❄️ Winter 2025</div>
                
                <div class="custom-option-group-label">Tahun 2024</div>
                <div class="custom-option" onclick="pilihMusim('2024/fall', '🍂 Fall 2024', event)">🍂 Fall 2024</div>
                <div class="custom-option" onclick="pilihMusim('2024/summer', '☀️ Summer 2024', event)">☀️ Summer 2024</div>
                <div class="custom-option" onclick="pilihMusim('2024/spring', '🌸 Spring 2024', event)">🌸 Spring 2024</div>
                <div class="custom-option" onclick="pilihMusim('2024/winter', '❄️ Winter 2024', event)">❄️ Winter 2024</div>
            </div>
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
    <div id="resultGrid"></div>

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

    async function fetchAPI(url, fetchComplete = false) {
        const errorMsg = document.getElementById('error');
        const grid = document.getElementById('resultGrid');
        const skeleton = document.getElementById('skeletonGrid');
        const loading = document.getElementById('loading');

        grid.innerHTML = '';
        errorMsg.style.display = 'none';
        skeleton.style.display = 'grid';
        loading.style.display = 'block';
        loading.innerText = 'Mengambil data anime...';
        dataAnime = [];

        try {
            let response = await axios.get(url);
            let currentData = response.data.data;
            dataAnime = dataAnime.concat(currentData);

            if (fetchComplete && response.data.pagination && response.data.pagination.has_next_page) {
                let page = 2;
                let hasNextPage = response.data.pagination.has_next_page;
                
                // Ambil maksimal hingga 10 halaman agar tidak melampaui rate-limits Jikan
                while (hasNextPage && page <= 10) {
                    loading.innerText = `Mengambil kelengkapan data (Halaman ${page})...`;
                    // Delay 400ms untuk menghindari rate limit API
                    await new Promise(res => setTimeout(res, 400));
                    
                    const nextUrl = url.includes('?') ? `${url}&page=${page}` : `${url}?page=${page}`;
                    let nextRes = await axios.get(nextUrl);
                    
                    dataAnime = dataAnime.concat(nextRes.data.data);
                    hasNextPage = nextRes.data.pagination.has_next_page;
                    page++;
                }
            }

            skeleton.style.display = 'none';
            loading.style.display = 'none';
            tampilkanData(dataAnime);
        } catch (error) {
            skeleton.style.display = 'none';
            loading.style.display = 'none';
            errorMsg.style.display = 'block';
            console.error(error);
        }
    }

    function cariAnime() {
        const query = document.getElementById('searchInput').value.trim();
        if (!query) return;

        // Reset custom select
        document.getElementById('customSelectLabel').innerText = '— Pilih Musim & Tahun —';
        document.querySelectorAll('.custom-option').forEach(opt => opt.classList.remove('selected'));

        // Cukup ambil halaman pertama untuk pencarian
        fetchAPI(`https://api.jikan.moe/v4/anime?q=${encodeURIComponent(query)}&limit=25`, false);
    }

    function toggleSelect() {
        document.getElementById('seasonSelectContainer').classList.toggle('open');
    }

    // Tutup custom select jika klik di luar
    document.addEventListener('click', function(e) {
        const container = document.getElementById('seasonSelectContainer');
        if (container && !container.contains(e.target)) {
            container.classList.remove('open');
        }
    });

    function pilihMusim(value, label, event) {
        document.getElementById('customSelectLabel').innerText = label;
        document.getElementById('seasonSelectContainer').classList.remove('open');
        
        // Tandai opsi terpilih
        const options = document.querySelectorAll('.custom-option');
        options.forEach(opt => opt.classList.remove('selected'));
        if (event) {
            event.currentTarget.classList.add('selected');
        }

        // Hapus query di input pencarian jika ada filter
        document.getElementById('searchInput').value = '';

        if (!value) {
            fetchAPI('https://api.jikan.moe/v4/seasons/now', true);
            return;
        }

        const url = value === 'upcoming'
            ? 'https://api.jikan.moe/v4/seasons/upcoming'
            : `https://api.jikan.moe/v4/seasons/${value}`;
        fetchAPI(url, true);
    }

    function tampilkanData(animeList) {
        const grid = document.getElementById('resultGrid');
        grid.innerHTML = '';

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

        // Kategori terurut sesuai permintaan
        const typeOrder = ['TV', 'Movie', 'ONA', 'OVA', 'Special', 'Music', 'Others'];
        const grouped = {};

        // Pengelompokkan map & save original index untuk modal
        animeList.forEach((anime, originalIndex) => {
            anime._originalIndex = originalIndex;
            let type = anime.type || 'Others';
            if (!typeOrder.includes(type)) type = 'Others';
            
            if (!grouped[type]) grouped[type] = [];
            grouped[type].push(anime);
        });

        const iconMap = { 'TV': '📺', 'Movie': '🎬', 'ONA': '🌐', 'OVA': '💿', 'Special': '🎁', 'Music': '🎵', 'Others': '📦' };

        typeOrder.forEach(type => {
            if (grouped[type] && grouped[type].length > 0) {
                // Antisipasi duplikat akibat paginasi overlapping
                const uniqueAnime = [];
                const seen = new Set();
                grouped[type].forEach(a => {
                    if (!seen.has(a.mal_id)) {
                        seen.add(a.mal_id);
                        uniqueAnime.push(a);
                    }
                });

                // Buat Judul Segmen
                const sectionTitle = document.createElement('h2');
                sectionTitle.className = 'section-title';
                sectionTitle.innerHTML = `${iconMap[type] || '📌'} ${type === 'TV' ? 'TV Series' : type === 'Others' ? 'Lain-lain' : type}`;
                grid.appendChild(sectionTitle);

                // Buat grid spesifik per segmen
                const typeGrid = document.createElement('div');
                typeGrid.className = 'grid type-grid';
                
                uniqueAnime.forEach((anime, idx) => {
                    const card = document.createElement('div');
                    card.className = 'card';
                    card.style.animationDelay = `${(idx % 15) * 0.05}s`;
                    card.onclick = () => bukaModal(anime._originalIndex);

                    const score = anime.score ? anime.score.toFixed(1) : null;
                    const episodes = anime.episodes ? `${anime.episodes} ep` : '';

                    // Cek apakah ada genre eksplisit/hentai
                    let isHentai = false;
                    if (anime.genres) {
                        isHentai = isHentai || anime.genres.some(g => g.name.toLowerCase() === 'hentai' || g.name.toLowerCase() === 'erotica' || g.name.toLowerCase() === 'ecchi');
                    }
                    if (!isHentai && anime.explicit_genres) {
                        isHentai = isHentai || anime.explicit_genres.some(g => g.name.toLowerCase() === 'hentai' || g.name.toLowerCase() === 'erotica' || g.name.toLowerCase() === 'ecchi');
                    }

                    card.innerHTML = `
                        <div class="card-image-wrapper">
                            <img src="${anime.images.jpg.large_image_url || anime.images.jpg.image_url}" alt="${anime.title}" loading="lazy" class="${isHentai ? 'blurred-img' : ''}">
                            ${isHentai ? `
                            <div class="nsfw-overlay" onclick="bukaSensor(event, this)">
                                <span>⚠️ 18+ (Klik untuk lihat)</span>
                            </div>` : ''}
                            ${score ? `<div class="card-score">
                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                ${score}
                            </div>` : ''}
                        </div>
                        <div class="card-info">
                            <h3>${anime.title}</h3>
                            <div class="card-meta">
                                ${episodes ? `<span>📺 ${episodes}</span>` : ''}
                                ${anime.season ? `<span>📅 ${anime.season} ${anime.year || ''}</span>` : ''}
                            </div>
                        </div>
                    `;
                    typeGrid.appendChild(card);
                });
                grid.appendChild(typeGrid);
            }
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

    // Fungsi untuk membuka sensor NSFW (Hentai) tanpa membuka Modal
    function bukaSensor(e, element) {
        e.stopPropagation(); // Mencegah klik menyebar ke card (Mencegah modal terbuka)
        const img = element.previousElementSibling;
        if (img) img.classList.remove('blurred-img');
        element.style.display = 'none';
    }

    // Load current season on page load (Bawaan ambil komplet)
    window.onload = () => {
        fetchAPI('https://api.jikan.moe/v4/seasons/now', true);
    };
</script>

</body>
</html>