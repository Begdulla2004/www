/**
 * Formula va rasm qo'shish uchun JavaScript kodi
 */

// Global o'zgaruvchilar
let currentAttemptId = null;
let mediaItems = [];

// DOM elementlarini tanlash
document.addEventListener('DOMContentLoaded', function() {
    initMediaEditor();
    
    // Sahifa yuklanganda media elementlarini olish
    document.addEventListener('liveQuestionStarted', function(e) {
        if (e.detail && e.detail.attemptId) {
            setCurrentAttemptId(e.detail.attemptId);
            loadMediaItems();
        }
    });
});

function initMediaEditor() {
    // Media toolbar elementlari
    const imageUploadBtn = document.getElementById('image-upload-btn');
    const imageFileInput = document.getElementById('image-file-input');
    const formulaBtn = document.getElementById('formula-btn');
    const formulaAddBtn = document.getElementById('formula-add-btn');
    const formulaModal = document.getElementById('formula-modal');
    const formulaInput = document.getElementById('formula-input');
    
    // Rasm yuklash
    if (imageUploadBtn && imageFileInput) {
        imageUploadBtn.addEventListener('click', () => imageFileInput.click());
        imageFileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                // Rasmni to'g'ridan-to'g'ri ko'rsatish
                const file = this.files[0];
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const imgSrc = e.target.result;
                    const mediaId = 'img_' + Date.now();
                    
                    // Rasmni media galleryga qo'shish
                    addMediaToGallery({
                        id: mediaId,
                        type: 'image',
                        content: imgSrc
                    });
                    
                    // Rasmni textarea'ga qo'shish
                    insertMediaReference(mediaId, 'image');
                };
                
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Formula qo'shish
    if (formulaBtn && formulaModal) {
        formulaBtn.addEventListener('click', () => formulaModal.style.display = 'block');
        
        // Modal yopish
        const closeBtn = formulaModal.querySelector('.close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => formulaModal.style.display = 'none');
        }
    }
    
    // Formula preview va qo'shish
    if (formulaInput && formulaAddBtn) {
        formulaInput.addEventListener('input', function() {
            previewFormula(this.value);
        });
        
        formulaAddBtn.addEventListener('click', function() {
            const latex = formulaInput.value.trim();
            if (latex) {
                saveFormula(latex);
                formulaModal.style.display = 'none';
                formulaInput.value = '';
                document.getElementById('formula-preview').innerHTML = '';
            }
        });
    }
}

function setCurrentAttemptId(attemptId) {
    currentAttemptId = attemptId;
    console.log('Current attempt ID set to:', attemptId);
}

function uploadImage(file) {
    if (!currentAttemptId) {
        showToast('Xatolik: Urinish ID si topilmadi', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('image', file);
    formData.append('attempt_id', currentAttemptId);
    
    showToast('Rasm yuklanmoqda...', 'info');
    
    fetch('./api/attempts.php?action=upload_image', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'ok') {
            showToast('Rasm muvaffaqiyatli yuklandi', 'success');
            loadMediaItems();
        } else {
            showToast('Xatolik: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Rasm yuklashda xatolik:', error);
        showToast('Rasm yuklashda xatolik yuz berdi', 'error');
    });
}

function saveFormula(latex) {
    const formulaId = 'formula_' + Date.now();
    
    // Formulani media galleryga qo'shish
    addMediaToGallery({
        id: formulaId,
        type: 'formula',
        content: latex
    });
    
    // Formulani textarea'ga qo'shish
    insertMediaReference(formulaId, 'formula');
}

function previewFormula(latex) {
    const formulaPreview = document.getElementById('formula-preview');
    if (formulaPreview) {
        formulaPreview.textContent = '\\[' + latex + '\\]';
        if (window.MathJax) {
            MathJax.typesetPromise([formulaPreview]).catch(err => console.error('MathJax xatosi:', err));
        }
    }
}

function loadMediaItems() {
    if (!currentAttemptId) return;
    
    fetch('./api/attempts.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'get_media_items',
            attempt_id: currentAttemptId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'ok') {
            mediaItems = data.media_items || [];
            renderMediaGallery();
        }
    })
    .catch(error => console.error('Media elementlarini olishda xatolik:', error));
}

function renderMediaGallery() {
    const mediaGallery = document.getElementById('media-gallery');
    if (!mediaGallery) return;
    
    mediaGallery.innerHTML = '';
    
    if (mediaItems.length === 0) {
        mediaGallery.innerHTML = '<p class="no-media">Media elementlari mavjud emas</p>';
        return;
    }
    
    mediaItems.forEach(item => {
        const mediaItem = document.createElement('div');
        mediaItem.className = 'media-item';
        mediaItem.dataset.id = item.id;
        mediaItem.dataset.type = item.type;
        
        if (item.type === 'image') {
            mediaItem.innerHTML = `
                <div class="media-content">
                    <img src="${item.path}" alt="Rasm" />
                </div>
                <div class="media-actions">
                    <button class="insert-btn" onclick="insertMediaReference('${item.id}', '${item.type}')">Qo'shish</button>
                    <button class="delete-btn" onclick="deleteMediaItem('${item.id}', '${item.type}')">O'chirish</button>
                </div>
            `;
        } else if (item.type === 'formula') {
            mediaItem.innerHTML = `
                <div class="media-content formula">
                    <div class="formula-display">\\[${item.latex}\\]</div>
                </div>
                <div class="media-actions">
                    <button class="insert-btn" onclick="insertMediaReference('${item.id}', '${item.type}')">Qo'shish</button>
                    <button class="delete-btn" onclick="deleteMediaItem('${item.id}', '${item.type}')">O'chirish</button>
                </div>
            `;
        }
        
        mediaGallery.appendChild(mediaItem);
    });
    
    if (window.MathJax) {
        MathJax.typesetPromise([mediaGallery]).catch(err => console.error('MathJax xatosi:', err));
    }
}

function insertMediaReference(id, type) {
    const textarea = document.querySelector('textarea.answer-input');
    if (!textarea) return;
    
    let referenceText = type === 'image' ? `[Rasm:${id}]` : `[Formula:${id}]`;
    
    // Kursorni saqlash
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    
    // Referensni qo'shish
    textarea.value = text.substring(0, start) + referenceText + text.substring(end);
    
    // Kursorni yangi pozitsiyaga o'rnatish
    textarea.selectionStart = textarea.selectionEnd = start + referenceText.length;
    
    // Textarea fokusini qaytarish
    textarea.focus();
}

function deleteMediaItem(id, type) {
    if (!currentAttemptId) {
        showToast('Xatolik: Urinish ID si topilmadi', 'error');
        return;
    }
    
    if (confirm('Haqiqatan ham bu media elementini o\'chirmoqchimisiz?')) {
        fetch('./api/attempts.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'delete_media_item',
                attempt_id: currentAttemptId,
                media_id: id,
                media_type: type
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'ok') {
                showToast('Media elementi muvaffaqiyatli o\'chirildi', 'success');
                mediaItems = mediaItems.filter(item => !(item.id === id && item.type === type));
                renderMediaGallery();
                
                // Javobdan referenslarni o'chirish
                const textarea = document.querySelector('textarea.answer-input');
                if (textarea) {
                    const referenceText = type === 'image' ? `[Rasm:${id}]` : `[Formula:${id}]`;
                    textarea.value = textarea.value.replace(referenceText, '');
                }
            } else {
                showToast('Xatolik: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Media elementini o\'chirishda xatolik:', error);
            showToast('Media elementini o\'chirishda xatolik yuz berdi', 'error');
        });
    }
}

function showToast(message, type = 'info') {
    if (window.showToast) {
        window.showToast(message, type);
    } else {
        alert(message);
    }
}

function clearMediaGallery() {
    mediaItems = [];
    const mediaGallery = document.getElementById('media-gallery');
    if (mediaGallery) {
        mediaGallery.innerHTML = '<p class="no-media">Media elementlari mavjud emas</p>';
    }
}

function hideMediaElements() {
    const elements = ['media-toolbar', 'media-gallery', 'formula-modal'];
    elements.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    });
}

function showMediaElements() {
    const mediaToolbar = document.getElementById('media-toolbar');
    const mediaGallery = document.getElementById('media-gallery');
    
    if (mediaToolbar) mediaToolbar.style.display = 'flex';
    if (mediaGallery) mediaGallery.style.display = 'flex';
}

// Media elementlarini ko'rsatish va qo'shish funksiyalari
function addMediaToGallery(item) {
    const gallery = document.getElementById('media-gallery');
    if (!gallery) return;
    
    // Media elementini yaratish
    const mediaElement = document.createElement('div');
    mediaElement.className = 'media-item';
    mediaElement.dataset.id = item.id;
    mediaElement.dataset.type = item.type;
    
    // Turi bo'yicha ko'rsatish (CSS bilan mos keladigan markup)
    if (item.type === 'image') {
        mediaElement.innerHTML = `
            <div class="media-content">
                <img src="${item.content}" alt="Rasm" />
            </div>
            <div class="media-actions">
                <button class="insert-btn btn-insert" data-id="${item.id}" data-type="${item.type}">Qo'shish</button>
                <button class="delete-btn btn-delete" data-id="${item.id}" data-type="${item.type}">O'chirish</button>
            </div>
        `;
    } else if (item.type === 'formula') {
        mediaElement.innerHTML = `
            <div class="media-content formula">
                <div class="formula-display">\\[${item.content}\\]</div>
            </div>
            <div class="media-actions">
                <button class="insert-btn btn-insert" data-id="${item.id}" data-type="${item.type}">Qo'shish</button>
                <button class="delete-btn btn-delete" data-id="${item.id}" data-type="${item.type}">O'chirish</button>
            </div>
        `;
        // MathJax bilan formulani render qilish
        if (window.MathJax) {
            MathJax.typeset([mediaElement.querySelector('.formula-display')]);
        }
    }
    
    // Event listener qo'shish
    mediaElement.querySelector('.btn-insert').addEventListener('click', function() {
        insertMediaReference(this.dataset.id, this.dataset.type);
    });
    
    mediaElement.querySelector('.btn-delete').addEventListener('click', function() {
        deleteMediaItem(this.dataset.id, this.dataset.type);
    });
    
    // Galleryga qo'shish
    gallery.appendChild(mediaElement);
    
    // Media elementini ro'yxatga qo'shish
    mediaItems.push(item);
}

// Media referensini textarea'ga qo'shish
function insertMediaReference(id, type) {
    const textarea = document.getElementById('essay_text') || document.getElementById('live_essay_text');
    if (!textarea) return;
    
    let reference = '';
    if (type === 'image') {
        reference = `[IMAGE:${id}]`;
    } else if (type === 'formula') {
        reference = `[FORMULA:${id}]`;
    }
    
    // Textarea'ga qo'shish
    const startPos = textarea.selectionStart;
    const endPos = textarea.selectionEnd;
    const text = textarea.value;
    
    textarea.value = text.substring(0, startPos) + reference + text.substring(endPos);
    textarea.focus();
    textarea.selectionStart = startPos + reference.length;
    textarea.selectionEnd = startPos + reference.length;
    
    showToast('Media qo\'shildi', 'success');
}

// Media elementini o'chirish
function deleteMediaItem(id, type) {
    const gallery = document.getElementById('media-gallery');
    if (!gallery) return;
    
    // DOM dan o'chirish
    const mediaElement = gallery.querySelector(`.media-item[data-id="${id}"]`);
    if (mediaElement) {
        gallery.removeChild(mediaElement);
    }
    
    // Ro'yxatdan o'chirish
    mediaItems = mediaItems.filter(item => item.id !== id);
    
    showToast('Media o\'chirildi', 'success');
}

// Formula preview
function previewFormula(latex) {
    const preview = document.getElementById('formula-preview');
    if (!preview) return;
    
    preview.innerHTML = `\\(${latex}\\)`;
    
    // MathJax bilan render qilish
    if (window.MathJax) {
        MathJax.typeset([preview]);
    }
}

// Toast xabarlarni ko'rsatish
function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    if (!toast) {
        // Toast elementi yo'q bo'lsa, yaratamiz
        const newToast = document.createElement('div');
        newToast.id = 'toast';
        newToast.className = 'toast';
        document.body.appendChild(newToast);
        
        setTimeout(() => {
            showToast(message, type);
        }, 100);
        return;
    }
    
    toast.textContent = message;
    toast.className = `toast ${type}`;
    toast.style.display = 'block';
    
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3000);
}
