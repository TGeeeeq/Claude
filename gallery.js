document.addEventListener('DOMContentLoaded', () => {
    const animals = [
        'Walk', 'Karel', 'Yakul', 'Avala', 'Princezna', 'Květa', 'Riky', 'Flíček', 'List', 
        'Atila', 'Kesy', 'Pogo', 'Kulich', 'Eduard', 'Emil', 'Amálka', 'Končí', 
        'Lucinka', 'Anaya', 'Roman', 'Máša', 'Lotka', 'Denis', 'Hanička', 'Patricie', 'Safír', 'Holoubci', 'Králíci', 'Kachny', 'Husy', 'Pipinky'
    ];

    const galleryData = [];
    let idCounter = 1;

    const removeAccents = (str) => {
        return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    };

    animals.forEach(animal => {
        const fileNameBase = removeAccents(animal.toLowerCase());
        for (let i = 1; i <= 8; i++) {
            galleryData.push({
                id: idCounter++,
                name: animal,
                category: animal,
                img: `assets/${fileNameBase}${i}.webp`,
                desc: `${animal} - Fotografie ${i}`
            });
        }
    });

    const galleryGrid = document.getElementById('gallery-grid');
    const filterButtonsContainer = document.getElementById('filter-buttons');
    const searchInput = document.getElementById('animal-search');

    const categories = ['all', ...new Set(galleryData.map(item => item.category))];
    categories.forEach(cat => {
        if (cat !== 'all') {
            const btn = document.createElement('button');
            btn.className = 'filter-btn';
            btn.dataset.filter = cat;
            btn.textContent = cat;
            filterButtonsContainer.appendChild(btn);
        }
    });

    function renderGallery(filter = 'all', search = '') {
        galleryGrid.innerHTML = '';
        const filteredData = galleryData.filter(item => {
            const matchesFilter = filter === 'all' || item.category === filter;
            const matchesSearch = item.name.toLowerCase().includes(search.toLowerCase());
            return matchesFilter && matchesSearch;
        });

        if (filteredData.length === 0) {
            galleryGrid.innerHTML = '<p style="grid-column: 1/-1; text-align: center; padding: 40px;">Žádné obrázky nebyly nalezeny.</p>';
            return;
        }

        filteredData.forEach(item => {
            const div = document.createElement('div');
            div.className = 'gallery-item';
            // Skryto ve výchozím stavu
            div.style.display = 'none'; 
            
            // loading="lazy" pro líné načítání
            // onload zobrazí prvek, onerror ho odstraní
            div.innerHTML = `
                <img src="${item.img}" 
                     alt="${item.name}" 
                     loading="lazy"
                     onload="this.closest('.gallery-item').style.display='block'"
                     onerror="this.closest('.gallery-item').remove()">
                <div class="gallery-item-info">
                    <h4>${item.name}</h4>
                </div>
            `;
            galleryGrid.appendChild(div);
        });
    }

    // Lightbox logika
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    let currentImages = [];
    let currentIndex = 0;

    galleryGrid.addEventListener('click', (e) => {
        const item = e.target.closest('.gallery-item');
        if (!item || item.style.display === 'none') return;

        const allVisibleImgs = Array.from(galleryGrid.querySelectorAll('.gallery-item'))
            .filter(el => el.style.display !== 'none')
            .map(el => el.querySelector('img'));
        
        if (allVisibleImgs.length === 0) return;

        currentImages = allVisibleImgs.map(img => img.src);
        const clickedImg = item.querySelector('img');
        currentIndex = currentImages.indexOf(clickedImg.src);

        if (currentIndex !== -1) {
            showLightbox();
        }
    });

    function showLightbox() {
        if (currentImages.length === 0) return;
        lightboxImg.src = currentImages[currentIndex];
        lightbox.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    const nextImg = () => { 
        if (currentImages.length === 0) return;
        currentIndex = (currentIndex + 1) % currentImages.length; 
        showLightbox(); 
    };
    
    const prevImg = () => { 
        if (currentImages.length === 0) return;
        currentIndex = (currentIndex - 1 + currentImages.length) % currentImages.length; 
        showLightbox(); 
    };

    if (document.querySelector('.lightbox-next')) document.querySelector('.lightbox-next').onclick = (e) => { e.stopPropagation(); nextImg(); };
    if (document.querySelector('.lightbox-prev')) document.querySelector('.lightbox-prev').onclick = (e) => { e.stopPropagation(); prevImg(); };
    if (document.querySelector('.lightbox-close')) document.querySelector('.lightbox-close').onclick = () => { lightbox.style.display = 'none'; document.body.style.overflow = 'auto'; };
    
    if (lightbox) lightbox.onclick = () => { lightbox.style.display = 'none'; document.body.style.overflow = 'auto'; };
    if (lightboxImg) lightboxImg.onclick = (e) => e.stopPropagation();

    document.addEventListener('keydown', (e) => {
        if (lightbox && lightbox.style.display === 'flex') {
            if (e.key === 'Escape') { lightbox.style.display = 'none'; document.body.style.overflow = 'auto'; }
            if (e.key === 'ArrowRight') nextImg();
            if (e.key === 'ArrowLeft') prevImg();
        }
    });

    filterButtonsContainer.addEventListener('click', (e) => {
        if (e.target.classList.contains('filter-btn')) {
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            e.target.classList.add('active');
            renderGallery(e.target.dataset.filter, searchInput.value);
        }
    });

    searchInput.addEventListener('input', (e) => {
        const activeBtn = document.querySelector('.filter-btn.active');
        const activeFilter = activeBtn ? activeBtn.dataset.filter : 'all';
        renderGallery(activeFilter, e.target.value);
    });

    const urlParams = new URLSearchParams(window.location.search);
    const animalParam = urlParams.get('animal');
    if (animalParam) {
        const targetBtn = Array.from(document.querySelectorAll('.filter-btn'))
            .find(btn => btn.dataset.filter.toLowerCase() === animalParam.toLowerCase());
        if (targetBtn) {
            targetBtn.click();
        } else {
            renderGallery();
        }
    } else {
        const allBtn = Array.from(document.querySelectorAll('.filter-btn')).find(btn => btn.dataset.filter === 'all');
        if (allBtn) allBtn.classList.add('active');
        renderGallery();
    }
});