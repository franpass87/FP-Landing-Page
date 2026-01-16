/**
 * FP Landing Page Builder - JavaScript
 * Versione migliorata con UX avanzata
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        const builder = {
            sectionIcons: {
                'title': '📝',
                'text': '📄',
                'image': '🖼️',
                'gallery': '🎨',
                'shortcode': '⚡',
                'cta': '🔘',
                'video': '🎬',
                'separator': '➖',
                'features': '⭐',
                'counters': '🔢',
                'faq': '❓',
                'tabs': '📑'
            },
            
            init: function() {
                this.bindEvents();
                this.initSortable();
                this.initColorPickers();
                this.updateAllItemCounts();
                this.initSaveIndicator();
            },
            
            loadExistingSections: function() {
                const $list = $('#fp-lp-sections-list');
                const data = $('#fp-lp-sections-data').val();
                
                if (!data || data === '[]') {
                    return;
                }
                
                try {
                    const sections = JSON.parse(data);
                    if (Array.isArray(sections) && sections.length > 0) {
                        // Rimuovi empty state se presente
                        $list.find('.fp-lp-empty-state').remove();
                        
                        sections.forEach((section, index) => {
                            const $section = this.createSectionElement(section.type, section.data, index);
                            $list.append($section);
                        });
                    }
                } catch (e) {
                    console.error('Error loading sections:', e);
                }
            },
            
            createSectionElement: function(type, data, index) {
                const sectionTitles = {
                    'title': 'Titolo',
                    'text': 'Testo',
                    'image': 'Immagine',
                    'gallery': 'Galleria',
                    'shortcode': 'Shortcode',
                    'cta': 'Call to Action',
                    'video': 'Video',
                    'separator': 'Separatore',
                    'features': 'Features',
                    'counters': 'Contatori',
                    'faq': 'FAQ',
                    'tabs': 'Tabs'
                };
                
                const sectionTitle = sectionTitles[type] || type;
                const sectionIcon = this.getSectionIcon(type);
                const $section = $('<div>').addClass('fp-lp-section-item').attr({
                    'data-index': index,
                    'data-type': type
                });
                
                $section.html(`
                    <div class="fp-lp-section-header">
                        <span class="fp-lp-drag-handle">⋮⋮</span>
                        <span class="fp-lp-section-icon">${sectionIcon}</span>
                        <span class="fp-lp-section-title">${sectionTitle}</span>
                        <div class="fp-lp-section-actions">
                            <button type="button" class="button-link fp-lp-duplicate-section" title="Duplica">📋</button>
                            <button type="button" class="button-link fp-lp-move-up" title="Sposta su">⬆</button>
                            <button type="button" class="button-link fp-lp-move-down" title="Sposta giù">⬇</button>
                            <button type="button" class="button-link fp-lp-toggle-section" title="Espandi/Comprimi">▼</button>
                            <button type="button" class="button-link fp-lp-remove-section" style="color: #b32d2e;" title="Rimuovi">✕</button>
                        </div>
                    </div>
                    <div class="fp-lp-section-content">
                        ${this.getSectionFieldsHTML(type, index, data)}
                    </div>
                `);
                
                // Se ci sono immagini, carica il preview
                if (type === 'image' && data.image_id) {
                    this.loadImagePreview($section, data.image_id);
                }
                
                if (type === 'gallery' && data.gallery_ids) {
                    this.loadGalleryPreview($section, data.gallery_ids);
                }
                
                return $section;
            },
            
            loadImagePreview: function($section, imageId) {
                const $preview = $section.find('.fp-lp-image-preview');
                const $button = $section.find('.fp-lp-select-image');
                const $removeBtn = $section.find('.fp-lp-remove-image');
                
                if (imageId) {
                    // Carica immagine via AJAX
                    $.ajax({
                        url: ajaxurl || '/wp-admin/admin-ajax.php',
                        type: 'POST',
                        data: {
                            action: 'fp_lp_get_image',
                            image_id: imageId
                        },
                        success: function(response) {
                            if (response.success && response.data.url) {
                                const $img = $('<img>').attr({
                                    src: response.data.url,
                                    style: 'max-width: 200px; height: auto; display: block;'
                                });
                                $preview.html($img);
                                $button.text('Cambia Immagine');
                                $removeBtn.show();
                            }
                        }
                    });
                }
            },
            
            loadGalleryPreview: function($section, galleryIds) {
                const $preview = $section.find('.fp-lp-gallery-preview');
                const $button = $section.find('.fp-lp-select-gallery');
                const $removeBtn = $section.find('.fp-lp-remove-gallery');
                
                if (galleryIds) {
                    const ids = galleryIds.split(',');
                    if (ids.length > 0) {
                        // Carica immagini via AJAX
                        $.ajax({
                            url: ajaxurl || '/wp-admin/admin-ajax.php',
                            type: 'POST',
                            data: {
                                action: 'fp_lp_get_gallery',
                                gallery_ids: galleryIds
                            },
                            success: function(response) {
                                if (response.success && response.data.images) {
                                    let previewHtml = '';
                                    response.data.images.forEach(function(img) {
                                        previewHtml += '<img src="' + img.url + '" style="width: 80px; height: 80px; object-fit: cover; border: 1px solid #ddd; border-radius: 4px;">';
                                    });
                                    $preview.html(previewHtml);
                                    $button.text('Modifica Galleria');
                                    $removeBtn.show();
                                }
                            }
                        });
                    }
                }
            },
            
            bindEvents: function() {
                const self = this;
                
                // Aggiungi sezione
                $(document).on('click', '.fp-lp-add-section', function(e) {
                    e.preventDefault();
                    const type = $(this).data('section-type');
                    self.addSection(type);
                });
                
                // Duplica sezione
                $(document).on('click', '.fp-lp-duplicate-section', function(e) {
                    e.preventDefault();
                    const $section = $(this).closest('.fp-lp-section-item');
                    const type = $section.data('type');
                    const data = {};
                    
                    // Raccogli tutti i dati della sezione
                    $section.find('.fp-lp-field').each(function() {
                        const field = $(this).data('field');
                        let value = $(this).val();
                        if ($(this).attr('type') === 'number') {
                            value = value ? parseInt(value, 10) : '';
                        }
                        data[field] = value;
                    });
                    
                    // Duplica features, counters, faq, tabs se presenti
                    if (type === 'features') {
                        data.features = [];
                        $section.find('.fp-lp-feature-item').each(function() {
                            const feature = {};
                            $(this).find('.fp-lp-feature-field').each(function() {
                                feature[$(this).data('field')] = $(this).val();
                            });
                            data.features.push(feature);
                        });
                    } else if (type === 'counters') {
                        data.counters = [];
                        $section.find('.fp-lp-counter-item').each(function() {
                            const counter = {};
                            $(this).find('.fp-lp-counter-field').each(function() {
                                counter[$(this).data('field')] = $(this).val();
                            });
                            data.counters.push(counter);
                        });
                    } else if (type === 'faq') {
                        data.faqs = [];
                        $section.find('.fp-lp-faq-item').each(function() {
                            const faq = {};
                            $(this).find('.fp-lp-faq-field').each(function() {
                                faq[$(this).data('field')] = $(this).val();
                            });
                            data.faqs.push(faq);
                        });
                    } else if (type === 'tabs') {
                        data.tabs = [];
                        $section.find('.fp-lp-tab-item').each(function() {
                            const tab = {};
                            $(this).find('.fp-lp-tab-field').each(function() {
                                tab[$(this).data('field')] = $(this).val();
                            });
                            data.tabs.push(tab);
                        });
                    }
                    
                    // Aggiungi nuova sezione dopo quella corrente
                    self.addSection(type, data);
                    self.updateSectionsData();
                });
                
                // Rimuovi sezione
                $(document).on('click', '.fp-lp-remove-section', function(e) {
                    e.preventDefault();
                    if (confirm('Sei sicuro di voler rimuovere questa sezione?')) {
                        $(this).closest('.fp-lp-section-item').remove();
                        self.updateSectionsData();
                    }
                });
                
                // Toggle sezione
                $(document).on('click', '.fp-lp-toggle-section', function(e) {
                    e.preventDefault();
                    const $btn = $(this);
                    const $section = $btn.closest('.fp-lp-section-item');
                    const $content = $section.find('.fp-lp-section-content');
                    $content.slideToggle();
                    $btn.text($content.is(':visible') ? '▼' : '▶');
                });
                
                // Move up/down
                $(document).on('click', '.fp-lp-move-up', function(e) {
                    e.preventDefault();
                    const $section = $(this).closest('.fp-lp-section-item');
                    const $prev = $section.prev('.fp-lp-section-item');
                    if ($prev.length) {
                        $section.insertBefore($prev);
                        self.updateSectionsData();
                    }
                });
                
                $(document).on('click', '.fp-lp-move-down', function(e) {
                    e.preventDefault();
                    const $section = $(this).closest('.fp-lp-section-item');
                    const $next = $section.next('.fp-lp-section-item');
                    if ($next.length) {
                        $section.insertAfter($next);
                        self.updateSectionsData();
                    }
                });
                
                // Update on field change
                $(document).on('input change', '.fp-lp-field', function() {
                    self.updateSectionsData();
                });
                
                // Update on complex field change (features, counters, faq, tabs)
                $(document).on('input change', '.fp-lp-feature-field, .fp-lp-counter-field, .fp-lp-faq-field, .fp-lp-tab-field', function() {
                    self.updateSectionsData();
                });
                
                // Media library - Immagine singola
                $(document).on('click', '.fp-lp-select-image', function(e) {
                    e.preventDefault();
                    const $button = $(this);
                    const $section = $button.closest('.fp-lp-section-item');
                    const $preview = $section.find('.fp-lp-image-preview');
                    const $input = $section.find('[data-field="image_id"]');
                    const $removeBtn = $section.find('.fp-lp-remove-image');
                    const currentId = $input.val();
                    
                    const imageFrame = wp.media({
                        title: 'Seleziona Immagine',
                        library: { type: 'image' },
                        button: { text: 'Usa questa immagine' },
                        multiple: false
                    });
                    
                    // Seleziona immagine corrente se presente
                    if (currentId) {
                        imageFrame.on('open', function() {
                            const selection = imageFrame.state().get('selection');
                            const attachment = wp.media.attachment(currentId);
                            attachment.fetch();
                            selection.add(attachment ? [attachment] : []);
                        });
                    }
                    
                    imageFrame.on('select', function() {
                        const attachment = imageFrame.state().get('selection').first().toJSON();
                        $input.val(attachment.id);
                        
                        const imgUrl = attachment.sizes && attachment.sizes.medium 
                            ? attachment.sizes.medium.url 
                            : attachment.url;
                        
                        const $img = $('<img>').attr({
                            src: imgUrl,
                            style: 'max-width: 200px; height: auto; display: block;'
                        });
                        
                        $preview.html($img);
                        $button.text('Cambia Immagine');
                        $removeBtn.show();
                        self.updateSectionsData();
                    });
                    
                    imageFrame.open();
                });
                
                $(document).on('click', '.fp-lp-remove-image', function(e) {
                    e.preventDefault();
                    const $button = $(this);
                    const $section = $button.closest('.fp-lp-section-item');
                    const $preview = $section.find('.fp-lp-image-preview');
                    const $input = $section.find('[data-field="image_id"]');
                    const $selectBtn = $section.find('.fp-lp-select-image');
                    
                    $input.val('');
                    $preview.html('<div style="width: 200px; height: 150px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #666;">Nessuna immagine</div>');
                    $selectBtn.text('Seleziona Immagine');
                    $button.hide();
                    self.updateSectionsData();
                });
                
                // Media library - Galleria
                $(document).on('click', '.fp-lp-select-gallery', function(e) {
                    e.preventDefault();
                    const $button = $(this);
                    const $section = $button.closest('.fp-lp-section-item');
                    const $preview = $section.find('.fp-lp-gallery-preview');
                    const $input = $section.find('[data-field="gallery_ids"]');
                    const $removeBtn = $section.find('.fp-lp-remove-gallery');
                    const currentIds = $input.val() ? $input.val().split(',') : [];
                    
                    const galleryFrame = wp.media({
                        title: 'Crea Galleria',
                        library: { type: 'image' },
                        button: { text: 'Crea Galleria' },
                        multiple: true
                    });
                    
                    // Seleziona immagini correnti se presenti
                    if (currentIds.length > 0) {
                        galleryFrame.on('open', function() {
                            const selection = galleryFrame.state().get('selection');
                            $.each(currentIds, function(index, id) {
                                const attachment = wp.media.attachment(id);
                                attachment.fetch();
                                selection.add(attachment ? [attachment] : []);
                            });
                        });
                    }
                    
                    galleryFrame.on('select', function() {
                        const ids = [];
                        galleryFrame.state().get('selection').each(function(attachment) {
                            ids.push(attachment.id);
                        });
                        
                        $input.val(ids.join(','));
                        
                        // Aggiorna preview
                        let previewHtml = '';
                        if (ids.length > 0) {
                            galleryFrame.state().get('selection').each(function(attachment) {
                                const attData = attachment.toJSON();
                                const url = attData.sizes && attData.sizes.thumbnail 
                                    ? attData.sizes.thumbnail.url 
                                    : attData.url;
                                previewHtml += '<img src="' + url + '" style="width: 80px; height: 80px; object-fit: cover; border: 1px solid #ddd; border-radius: 4px;">';
                            });
                        } else {
                            previewHtml = '<p style="color: #666;">Nessuna immagine nella galleria</p>';
                        }
                        
                        $preview.html(previewHtml);
                        $button.text('Modifica Galleria');
                        if (ids.length > 0) {
                            $removeBtn.show();
                        } else {
                            $removeBtn.hide();
                        }
                        self.updateSectionsData();
                    });
                    
                    galleryFrame.open();
                });
                
                $(document).on('click', '.fp-lp-remove-gallery', function(e) {
                    e.preventDefault();
                    const $button = $(this);
                    const $section = $button.closest('.fp-lp-section-item');
                    const $preview = $section.find('.fp-lp-gallery-preview');
                    const $input = $section.find('[data-field="gallery_ids"]');
                    const $selectBtn = $section.find('.fp-lp-select-gallery');
                    
                    $input.val('');
                    $preview.html('<p style="color: #666;">Nessuna immagine nella galleria</p>');
                    $selectBtn.text('Crea Galleria');
                    $button.hide();
                    self.updateSectionsData();
                });
                
                // Add feature
                $(document).on('click', '.fp-lp-add-feature', function(e) {
                    e.preventDefault();
                    const $section = $(this).closest('.fp-lp-section-item');
                    const $list = $section.find('.fp-lp-features-list');
                    const newIndex = $list.find('.fp-lp-feature-item').length;
                    $list.append(`
                        <div class="fp-lp-feature-item" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; background: #f9f9f9;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <strong>Feature #${newIndex + 1}</strong>
                                <button type="button" class="button fp-lp-remove-feature" data-index="${$section.data('index')}" data-feature-index="${newIndex}">Rimuovi</button>
                            </div>
                            <table style="width: 100%;">
                                <tr>
                                    <td style="padding: 5px 0;"><label>Icona</label></td>
                                    <td style="padding: 5px 0;">
                                        <div style="display: flex; gap: 5px; align-items: center;">
                                            <input type="text" class="fp-lp-feature-field fp-lp-icon-input" data-field="icon" data-feature-index="${newIndex}" value="" style="flex: 1;" placeholder="fa fa-star">
                                            <button type="button" class="button fp-lp-icon-picker-btn" data-target-input=".fp-lp-feature-field[data-feature-index='${newIndex}'][data-field='icon']">
                                                <span class="dashicons dashicons-admin-appearance"></span> Scegli Icona
                                            </button>
                                        </div>
                                        <p class="description">Seleziona un'icona dalla libreria o inserisci manualmente la classe CSS</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0;"><label>Colore Icona</label></td>
                                    <td style="padding: 5px 0;">
                                        <input type="text" class="fp-lp-feature-field" data-field="icon_color" data-feature-index="${newIndex}" value="" style="width: 200px;" placeholder="#0073aa">
                                        <p class="description">Colore hex personalizzato per questa icona (opzionale)</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0;"><label>Titolo</label></td>
                                    <td style="padding: 5px 0;">
                                        <input type="text" class="fp-lp-feature-field" data-field="title" data-feature-index="${newIndex}" value="" style="width: 100%;" placeholder="Titolo feature">
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 0;"><label>Testo</label></td>
                                    <td style="padding: 5px 0;">
                                        <textarea class="fp-lp-feature-field" data-field="text" data-feature-index="${newIndex}" rows="3" style="width: 100%;" placeholder="Descrizione feature"></textarea>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    `);
                    self.updateSectionsData();
                    self.updateItemCount($section);
                });
                
                $(document).on('click', '.fp-lp-remove-feature', function(e) {
                    e.preventDefault();
                    const $section = $(this).closest('.fp-lp-section-item');
                    $(this).closest('.fp-lp-feature-item').remove();
                    self.updateSectionsData();
                    self.updateItemCount($section);
                });
                
                // Add counter
                $(document).on('click', '.fp-lp-add-counter', function(e) {
                    e.preventDefault();
                    const $section = $(this).closest('.fp-lp-section-item');
                    const $list = $section.find('.fp-lp-counters-list');
                    const newIndex = $list.find('.fp-lp-counter-item').length;
                    $list.append(`
                        <div class="fp-lp-counter-item" data-counter-index="${newIndex}">
                            <div class="fp-lp-counter-header">
                                <span>Counter ${newIndex + 1}</span>
                                <button type="button" class="button button-small fp-lp-remove-counter">Rimuovi</button>
                            </div>
                            <div class="fp-lp-counter-content">
                                <p><label>Numero:</label><input type="number" class="fp-lp-counter-field" data-field="number" data-counter-index="${newIndex}" placeholder="1000" style="width:100%;"></p>
                                <p><label>Label:</label><input type="text" class="fp-lp-counter-field" data-field="label" data-counter-index="${newIndex}" placeholder="Clienti" style="width:100%;"></p>
                                <p><label>Prefisso:</label><input type="text" class="fp-lp-counter-field" data-field="prefix" data-counter-index="${newIndex}" style="width:45%;"> <label>Suffisso:</label><input type="text" class="fp-lp-counter-field" data-field="suffix" data-counter-index="${newIndex}" placeholder="+" style="width:45%;"></p>
                            </div>
                        </div>
                    `);
                    self.updateSectionsData();
                    self.updateItemCount($section);
                });
                
                $(document).on('click', '.fp-lp-remove-counter', function(e) {
                    e.preventDefault();
                    const $section = $(this).closest('.fp-lp-section-item');
                    $(this).closest('.fp-lp-counter-item').remove();
                    self.updateSectionsData();
                    self.updateItemCount($section);
                });
                
                // Add FAQ
                $(document).on('click', '.fp-lp-add-faq', function(e) {
                    e.preventDefault();
                    const $section = $(this).closest('.fp-lp-section-item');
                    const $list = $section.find('.fp-lp-faq-list');
                    const newIndex = $list.find('.fp-lp-faq-item').length;
                    $list.append(`
                        <div class="fp-lp-faq-item" data-faq-index="${newIndex}">
                            <div class="fp-lp-faq-header">
                                <span>FAQ ${newIndex + 1}</span>
                                <button type="button" class="button button-small fp-lp-remove-faq">Rimuovi</button>
                            </div>
                            <div class="fp-lp-faq-content">
                                <p><label>Domanda:</label><input type="text" class="fp-lp-faq-field" data-field="question" data-faq-index="${newIndex}" style="width:100%;"></p>
                                <p><label>Risposta:</label><textarea class="fp-lp-faq-field" data-field="answer" data-faq-index="${newIndex}" rows="3" style="width:100%;"></textarea></p>
                            </div>
                        </div>
                    `);
                    self.updateSectionsData();
                    self.updateItemCount($section);
                });
                
                $(document).on('click', '.fp-lp-remove-faq', function(e) {
                    e.preventDefault();
                    const $section = $(this).closest('.fp-lp-section-item');
                    $(this).closest('.fp-lp-faq-item').remove();
                    self.updateSectionsData();
                    self.updateItemCount($section);
                });
                
                // Add Tab
                $(document).on('click', '.fp-lp-add-tab', function(e) {
                    e.preventDefault();
                    const $section = $(this).closest('.fp-lp-section-item');
                    const $list = $section.find('.fp-lp-tabs-list');
                    const newIndex = $list.find('.fp-lp-tab-item').length;
                    $list.append(`
                        <div class="fp-lp-tab-item" data-tab-index="${newIndex}">
                            <div class="fp-lp-tab-header">
                                <span>Tab ${newIndex + 1}</span>
                                <button type="button" class="button button-small fp-lp-remove-tab">Rimuovi</button>
                            </div>
                            <div class="fp-lp-tab-content">
                                <p><label>Titolo:</label><input type="text" class="fp-lp-tab-field" data-field="title" data-tab-index="${newIndex}" style="width:100%;"></p>
                                <p><label>Contenuto:</label><textarea class="fp-lp-tab-field" data-field="content" data-tab-index="${newIndex}" rows="4" style="width:100%;"></textarea></p>
                            </div>
                        </div>
                    `);
                    self.updateSectionsData();
                    self.updateItemCount($section);
                });
                
                $(document).on('click', '.fp-lp-remove-tab', function(e) {
                    e.preventDefault();
                    const $section = $(this).closest('.fp-lp-section-item');
                    $(this).closest('.fp-lp-tab-item').remove();
                    self.updateSectionsData();
                    self.updateItemCount($section);
                });
                
                // Salva prima di submit
                $('#post').on('submit', function() {
                    self.updateSectionsData();
                });
            },
            
            addSection: function(type, data) {
                data = data || {};
                const index = Date.now();
                const $list = $('#fp-lp-sections-list');
                const sectionTitles = {
                    'title': 'Titolo',
                    'text': 'Testo',
                    'image': 'Immagine',
                    'gallery': 'Galleria',
                    'shortcode': 'Shortcode',
                    'cta': 'Call to Action',
                    'video': 'Video',
                    'separator': 'Separatore',
                    'features': 'Features',
                    'counters': 'Contatori',
                    'faq': 'FAQ',
                    'tabs': 'Tabs'
                };
                
                // Rimuovi empty state
                $list.find('.fp-lp-empty-state').remove();
                
                const sectionTitle = sectionTitles[type] || type;
                const sectionIcon = this.getSectionIcon(type);
                const $section = $('<div>').addClass('fp-lp-section-item').attr({
                    'data-index': index,
                    'data-type': type
                });
                
                $section.html(`
                    <div class="fp-lp-section-header">
                        <span class="fp-lp-drag-handle">⋮⋮</span>
                        <span class="fp-lp-section-icon">${sectionIcon}</span>
                        <span class="fp-lp-section-title">${sectionTitle}</span>
                        <div class="fp-lp-section-actions">
                            <button type="button" class="button-link fp-lp-duplicate-section" title="Duplica">📋</button>
                            <button type="button" class="button-link fp-lp-move-up" title="Sposta su">⬆</button>
                            <button type="button" class="button-link fp-lp-move-down" title="Sposta giù">⬇</button>
                            <button type="button" class="button-link fp-lp-toggle-section" title="Espandi/Comprimi">▼</button>
                            <button type="button" class="button-link fp-lp-remove-section" style="color: #b32d2e;" title="Rimuovi">✕</button>
                        </div>
                    </div>
                    <div class="fp-lp-section-content" style="display: none;">
                        ${this.getSectionFieldsHTML(type, index, data)}
                    </div>
                `);
                
                $list.append($section);
                this.updateSectionsData();
                this.updateItemCount($section);
                
                // Espandi la nuova sezione
                $section.find('.fp-lp-section-content').slideDown();
            },
            
            getSectionFieldsHTML: function(type, index, data) {
                data = data || {};
                
                const fields = {
                    'title': `
                        <table class="form-table">
                            <tr>
                                <th><label>Testo Titolo</label></th>
                                <td><input type="text" class="fp-lp-field" data-field="text" value="${this.escapeHtml(data.text || '')}" style="width: 100%;" placeholder="Inserisci il titolo"></td>
                            </tr>
                            <tr>
                                <th><label>Livello (H1, H2, H3)</label></th>
                                <td>
                                    <select class="fp-lp-field" data-field="level">
                                        <option value="h1" ${data.level === 'h1' ? 'selected' : ''}>H1</option>
                                        <option value="h2" ${!data.level || data.level === 'h2' ? 'selected' : ''}>H2</option>
                                        <option value="h3" ${data.level === 'h3' ? 'selected' : ''}>H3</option>
                                        <option value="h4" ${data.level === 'h4' ? 'selected' : ''}>H4</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Allineamento</label></th>
                                <td>
                                    <select class="fp-lp-field" data-field="align">
                                        <option value="left" ${!data.align || data.align === 'left' ? 'selected' : ''}>Sinistra</option>
                                        <option value="center" ${data.align === 'center' ? 'selected' : ''}>Centro</option>
                                        <option value="right" ${data.align === 'right' ? 'selected' : ''}>Destra</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th colspan="2" style="padding-top: 15px; border-top: 1px solid #ddd;"><strong>Personalizzazioni Avanzate Titolo</strong></th>
                            </tr>
                            <tr>
                                <th><label>Dimensione Font (px)</label></th>
                                <td><input type="number" class="fp-lp-field" data-field="font_size" value="${data.font_size || ''}" placeholder="36" min="12" max="120" style="width: 100%;"><p class="description">Dimensione del font in pixel (es: 36 per H1, 24 per H2)</p></td>
                            </tr>
                            <tr>
                                <th><label>Colore Testo</label></th>
                                <td><input type="text" class="fp-lp-field" data-field="text_color" value="${this.escapeHtml(data.text_color || '')}" placeholder="#333333" style="width: 100%;"><p class="description">Colore del testo (es: #333333, #000000)</p></td>
                            </tr>
                            <tr>
                                <th><label>Peso Font</label></th>
                                <td>
                                    <select class="fp-lp-field" data-field="font_weight">
                                        <option value="" ${!data.font_weight || data.font_weight === '' ? 'selected' : ''}>Default</option>
                                        <option value="300" ${data.font_weight === '300' ? 'selected' : ''}>300 (Light)</option>
                                        <option value="400" ${data.font_weight === '400' ? 'selected' : ''}>400 (Normal)</option>
                                        <option value="600" ${data.font_weight === '600' ? 'selected' : ''}>600 (Semi-Bold)</option>
                                        <option value="700" ${data.font_weight === '700' ? 'selected' : ''}>700 (Bold)</option>
                                        <option value="800" ${data.font_weight === '800' ? 'selected' : ''}>800 (Extra-Bold)</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th colspan="2" style="padding-top: 15px; border-top: 1px solid #ddd;"><strong>Responsive Titolo</strong></th>
                            </tr>
                            <tr>
                                <th><label>Font Size Mobile (px)</label></th>
                                <td><input type="number" class="fp-lp-field" data-field="font_size_mobile" value="${data.font_size_mobile || ''}" placeholder="24" min="12" max="120" style="width: 100%;"><p class="description">Dimensione font su mobile (&lt;768px)</p></td>
                            </tr>
                            <tr>
                                <th><label>Font Size Tablet (px)</label></th>
                                <td><input type="number" class="fp-lp-field" data-field="font_size_tablet" value="${data.font_size_tablet || ''}" placeholder="30" min="12" max="120" style="width: 100%;"><p class="description">Dimensione font su tablet (768px-1024px)</p></td>
                            </tr>
                            <tr>
                                <th><label>Font Size Desktop (px)</label></th>
                                <td><input type="number" class="fp-lp-field" data-field="font_size_desktop" value="${data.font_size_desktop || ''}" placeholder="36" min="12" max="120" style="width: 100%;"><p class="description">Dimensione font su desktop (&gt;1024px)</p></td>
                            </tr>
                            <tr>
                                <th><label>Allineamento Mobile</label></th>
                                <td>
                                    <select class="fp-lp-field" data-field="align_mobile">
                                        <option value="" ${!data.align_mobile || data.align_mobile === '' ? 'selected' : ''}>Usa allineamento generale</option>
                                        <option value="left" ${data.align_mobile === 'left' ? 'selected' : ''}>Sinistra</option>
                                        <option value="center" ${data.align_mobile === 'center' ? 'selected' : ''}>Centro</option>
                                        <option value="right" ${data.align_mobile === 'right' ? 'selected' : ''}>Destra</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Allineamento Tablet</label></th>
                                <td>
                                    <select class="fp-lp-field" data-field="align_tablet">
                                        <option value="" ${!data.align_tablet || data.align_tablet === '' ? 'selected' : ''}>Usa allineamento generale</option>
                                        <option value="left" ${data.align_tablet === 'left' ? 'selected' : ''}>Sinistra</option>
                                        <option value="center" ${data.align_tablet === 'center' ? 'selected' : ''}>Centro</option>
                                        <option value="right" ${data.align_tablet === 'right' ? 'selected' : ''}>Destra</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Allineamento Desktop</label></th>
                                <td>
                                    <select class="fp-lp-field" data-field="align_desktop">
                                        <option value="" ${!data.align_desktop || data.align_desktop === '' ? 'selected' : ''}>Usa allineamento generale</option>
                                        <option value="left" ${data.align_desktop === 'left' ? 'selected' : ''}>Sinistra</option>
                                        <option value="center" ${data.align_desktop === 'center' ? 'selected' : ''}>Centro</option>
                                        <option value="right" ${data.align_desktop === 'right' ? 'selected' : ''}>Destra</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        ${this.getCommonCustomizationFieldsHTML(data)}
                    `,
                    'text': `
                        <table class="form-table">
                            <tr>
                                <th><label>Contenuto</label></th>
                                <td><textarea class="fp-lp-field" data-field="content" rows="8" style="width: 100%;">${this.escapeHtml(data.content || '')}</textarea><p class="description">HTML consentito</p></td>
                            </tr>
                            <tr>
                                <th><label>Allineamento</label></th>
                                <td>
                                    <select class="fp-lp-field" data-field="align">
                                        <option value="left" ${!data.align || data.align === 'left' ? 'selected' : ''}>Sinistra</option>
                                        <option value="center" ${data.align === 'center' ? 'selected' : ''}>Centro</option>
                                        <option value="right" ${data.align === 'right' ? 'selected' : ''}>Destra</option>
                                        <option value="justify" ${data.align === 'justify' ? 'selected' : ''}>Giustificato</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        ${this.getCommonCustomizationFieldsHTML(data)}
                    `,
                    'image': `
                        <table class="form-table">
                            <tr>
                                <th><label>Immagine</label></th>
                                <td>
                                    <div class="fp-lp-image-preview" style="margin-bottom: 10px;">
                                        <div style="width: 200px; height: 150px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #666;">Nessuna immagine</div>
                                    </div>
                                    <input type="hidden" class="fp-lp-field" data-field="image_id" value="${data.image_id || ''}">
                                    <button type="button" class="button fp-lp-select-image" data-index="${index}">Seleziona Immagine</button>
                                    <button type="button" class="button fp-lp-remove-image" data-index="${index}" style="display:none;">Rimuovi</button>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Alt Text</label></th>
                                <td><input type="text" class="fp-lp-field" data-field="alt" value="${this.escapeHtml(data.alt || '')}" style="width: 100%;" placeholder="Testo alternativo"></td>
                            </tr>
                            <tr>
                                <th><label>Allineamento</label></th>
                                <td>
                                    <select class="fp-lp-field" data-field="align">
                                        <option value="left" ${data.align === 'left' ? 'selected' : ''}>Sinistra</option>
                                        <option value="center" ${!data.align || data.align === 'center' ? 'selected' : ''}>Centro</option>
                                        <option value="right" ${data.align === 'right' ? 'selected' : ''}>Destra</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Link (URL)</label></th>
                                <td><input type="url" class="fp-lp-field" data-field="link" value="${this.escapeHtml(data.link || '')}" style="width: 100%;" placeholder="https://"></td>
                            </tr>
                            <tr>
                                <th colspan="2" style="padding-top: 15px; border-top: 1px solid #ddd;"><strong>Personalizzazioni Avanzate Immagine</strong></th>
                            </tr>
                            <tr>
                                <th><label>Max Width (px o %)</label></th>
                                <td><input type="text" class="fp-lp-field" data-field="max_width" value="${this.escapeHtml(data.max_width || '')}" placeholder="800px o 100%" style="width: 100%;"><p class="description">Larghezza massima dell'immagine (es: "800px", "100%", "50vw")</p></td>
                            </tr>
                            <tr>
                                <th><label>Border Radius (px)</label></th>
                                <td><input type="number" class="fp-lp-field" data-field="border_radius" value="${data.border_radius || ''}" placeholder="0" min="0" max="50" style="width: 100%;"><p class="description">Arrotondamento degli angoli (0-50px)</p></td>
                            </tr>
                            <tr>
                                <th><label>Box Shadow</label></th>
                                <td><input type="text" class="fp-lp-field" data-field="box_shadow" value="${this.escapeHtml(data.box_shadow || '')}" placeholder="0 2px 8px rgba(0,0,0,0.1)" style="width: 100%;"><p class="description">Ombra CSS personalizzata (es: "0 2px 8px rgba(0,0,0,0.1)")</p></td>
                            </tr>
                            <tr>
                                <th colspan="2" style="padding-top: 15px; border-top: 1px solid #ddd;"><strong>Responsive Immagine</strong></th>
                            </tr>
                            <tr>
                                <th><label>Max Width Mobile (px o %)</label></th>
                                <td><input type="text" class="fp-lp-field" data-field="max_width_mobile" value="${this.escapeHtml(data.max_width_mobile || '')}" placeholder="100%" style="width: 100%;"><p class="description">Larghezza massima su mobile (&lt;768px)</p></td>
                            </tr>
                            <tr>
                                <th><label>Max Width Tablet (px o %)</label></th>
                                <td><input type="text" class="fp-lp-field" data-field="max_width_tablet" value="${this.escapeHtml(data.max_width_tablet || '')}" placeholder="80%" style="width: 100%;"><p class="description">Larghezza massima su tablet (768px-1024px)</p></td>
                            </tr>
                            <tr>
                                <th><label>Max Width Desktop (px o %)</label></th>
                                <td><input type="text" class="fp-lp-field" data-field="max_width_desktop" value="${this.escapeHtml(data.max_width_desktop || '')}" placeholder="800px" style="width: 100%;"><p class="description">Larghezza massima su desktop (&gt;1024px)</p></td>
                            </tr>
                            <tr>
                                <th><label>Allineamento Mobile</label></th>
                                <td>
                                    <select class="fp-lp-field" data-field="align_mobile">
                                        <option value="" ${!data.align_mobile || data.align_mobile === '' ? 'selected' : ''}>Usa allineamento generale</option>
                                        <option value="left" ${data.align_mobile === 'left' ? 'selected' : ''}>Sinistra</option>
                                        <option value="center" ${data.align_mobile === 'center' ? 'selected' : ''}>Centro</option>
                                        <option value="right" ${data.align_mobile === 'right' ? 'selected' : ''}>Destra</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Allineamento Tablet</label></th>
                                <td>
                                    <select class="fp-lp-field" data-field="align_tablet">
                                        <option value="" ${!data.align_tablet || data.align_tablet === '' ? 'selected' : ''}>Usa allineamento generale</option>
                                        <option value="left" ${data.align_tablet === 'left' ? 'selected' : ''}>Sinistra</option>
                                        <option value="center" ${data.align_tablet === 'center' ? 'selected' : ''}>Centro</option>
                                        <option value="right" ${data.align_tablet === 'right' ? 'selected' : ''}>Destra</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Allineamento Desktop</label></th>
                                <td>
                                    <select class="fp-lp-field" data-field="align_desktop">
                                        <option value="" ${!data.align_desktop || data.align_desktop === '' ? 'selected' : ''}>Usa allineamento generale</option>
                                        <option value="left" ${data.align_desktop === 'left' ? 'selected' : ''}>Sinistra</option>
                                        <option value="center" ${data.align_desktop === 'center' ? 'selected' : ''}>Centro</option>
                                        <option value="right" ${data.align_desktop === 'right' ? 'selected' : ''}>Destra</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        ${this.getCommonCustomizationFieldsHTML(data)}
                    `,
                    'gallery': `
                        <table class="form-table">
                            <tr>
                                <th><label>Galleria</label></th>
                                <td>
                                    <div class="fp-lp-gallery-preview" style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 10px;">
                                        <p style="color: #666;">Nessuna immagine nella galleria</p>
                                    </div>
                                    <input type="hidden" class="fp-lp-field" data-field="gallery_ids" value="${data.gallery_ids || ''}">
                                    <button type="button" class="button fp-lp-select-gallery" data-index="${index}">Crea Galleria</button>
                                    <button type="button" class="button fp-lp-remove-gallery" data-index="${index}" style="display:none;">Rimuovi Galleria</button>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Colonne</label></th>
                                <td>
                                    <select class="fp-lp-field" data-field="columns">
                                        <option value="2" ${data.columns === '2' ? 'selected' : ''}>2</option>
                                        <option value="3" ${!data.columns || data.columns === '3' ? 'selected' : ''}>3</option>
                                        <option value="4" ${data.columns === '4' ? 'selected' : ''}>4</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th colspan="2" style="padding-top: 15px; border-top: 1px solid #ddd;"><strong>Personalizzazioni Avanzate Galleria</strong></th>
                            </tr>
                            <tr>
                                <th><label>Border Radius Immagini (px)</label></th>
                                <td><input type="number" class="fp-lp-field" data-field="image_border_radius" value="${data.image_border_radius || ''}" placeholder="4" min="0" max="50" style="width: 100%;"><p class="description">Arrotondamento degli angoli delle immagini (0-50px)</p></td>
                            </tr>
                            <tr>
                                <th><label>Gap tra Immagini (px)</label></th>
                                <td><input type="number" class="fp-lp-field" data-field="gap" value="${data.gap || ''}" placeholder="10" min="0" max="50" style="width: 100%;"><p class="description">Spaziatura tra le immagini della galleria (0-50px)</p></td>
                            </tr>
                        </table>
                        ${this.getCommonCustomizationFieldsHTML(data)}
                    `,
                    'shortcode': `
                        <table class="form-table">
                            <tr>
                                <th><label>Shortcode</label></th>
                                <td><textarea class="fp-lp-field" data-field="shortcode" rows="3" style="width: 100%; font-family: monospace;">${this.escapeHtml(data.shortcode || '')}</textarea><p class="description">Inserisci lo shortcode completo, es: [fp_form id="123"]</p></td>
                            </tr>
                        </table>
                        ${this.getCommonCustomizationFieldsHTML(data)}
                    `,
                    'cta': `
                        <table class="form-table">
                            <tr>
                                <th><label>Testo Pulsante</label></th>
                                <td><input type="text" class="fp-lp-field" data-field="button_text" value="${this.escapeHtml(data.button_text || '')}" style="width: 100%;"></td>
                            </tr>
                            <tr>
                                <th><label>URL</label></th>
                                <td><input type="url" class="fp-lp-field" data-field="button_url" value="${this.escapeHtml(data.button_url || '#')}" style="width: 100%;"></td>
                            </tr>
                            <tr>
                                <th><label>Stile</label></th>
                                <td>
                                    <select class="fp-lp-field" data-field="style">
                                        <option value="primary" ${!data.style || data.style === 'primary' ? 'selected' : ''}>Primario</option>
                                        <option value="secondary" ${data.style === 'secondary' ? 'selected' : ''}>Secondario</option>
                                        <option value="outline" ${data.style === 'outline' ? 'selected' : ''}>Outline</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Allineamento</label></th>
                                <td>
                                    <select class="fp-lp-field" data-field="align">
                                        <option value="left" ${data.align === 'left' ? 'selected' : ''}>Sinistra</option>
                                        <option value="center" ${!data.align || data.align === 'center' ? 'selected' : ''}>Centro</option>
                                        <option value="right" ${data.align === 'right' ? 'selected' : ''}>Destra</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th colspan="2" style="padding-top: 15px; border-top: 1px solid #ddd;"><strong>Personalizzazioni Avanzate CTA</strong></th>
                            </tr>
                            <tr>
                                <th><label>Colore Background Pulsante</label></th>
                                <td><input type="text" class="fp-lp-field" data-field="button_bg_color" value="${this.escapeHtml(data.button_bg_color || '')}" placeholder="#0073aa" style="width: 100%;"><p class="description">Colore di sfondo del pulsante (es: #0073aa, #333333)</p></td>
                            </tr>
                            <tr>
                                <th><label>Colore Testo Pulsante</label></th>
                                <td><input type="text" class="fp-lp-field" data-field="button_text_color" value="${this.escapeHtml(data.button_text_color || '')}" placeholder="#ffffff" style="width: 100%;"><p class="description">Colore del testo del pulsante (es: #ffffff, #000000)</p></td>
                            </tr>
                            <tr>
                                <th><label>Border Radius (px)</label></th>
                                <td><input type="number" class="fp-lp-field" data-field="button_border_radius" value="${data.button_border_radius || ''}" placeholder="4" min="0" max="50" style="width: 100%;"><p class="description">Arrotondamento degli angoli (es: 4 per angoli leggermente arrotondati, 50 per completamente rotondo)</p></td>
                            </tr>
                            <tr>
                                <th colspan="2" style="padding-top: 15px; border-top: 1px solid #ddd;"><strong>Responsive CTA</strong></th>
                            </tr>
                            <tr>
                                <th><label>Allineamento Mobile</label></th>
                                <td>
                                    <select class="fp-lp-field" data-field="align_mobile">
                                        <option value="" ${!data.align_mobile || data.align_mobile === '' ? 'selected' : ''}>Usa allineamento generale</option>
                                        <option value="left" ${data.align_mobile === 'left' ? 'selected' : ''}>Sinistra</option>
                                        <option value="center" ${data.align_mobile === 'center' ? 'selected' : ''}>Centro</option>
                                        <option value="right" ${data.align_mobile === 'right' ? 'selected' : ''}>Destra</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Allineamento Tablet</label></th>
                                <td>
                                    <select class="fp-lp-field" data-field="align_tablet">
                                        <option value="" ${!data.align_tablet || data.align_tablet === '' ? 'selected' : ''}>Usa allineamento generale</option>
                                        <option value="left" ${data.align_tablet === 'left' ? 'selected' : ''}>Sinistra</option>
                                        <option value="center" ${data.align_tablet === 'center' ? 'selected' : ''}>Centro</option>
                                        <option value="right" ${data.align_tablet === 'right' ? 'selected' : ''}>Destra</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Allineamento Desktop</label></th>
                                <td>
                                    <select class="fp-lp-field" data-field="align_desktop">
                                        <option value="" ${!data.align_desktop || data.align_desktop === '' ? 'selected' : ''}>Usa allineamento generale</option>
                                        <option value="left" ${data.align_desktop === 'left' ? 'selected' : ''}>Sinistra</option>
                                        <option value="center" ${data.align_desktop === 'center' ? 'selected' : ''}>Centro</option>
                                        <option value="right" ${data.align_desktop === 'right' ? 'selected' : ''}>Destra</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        ${this.getCommonCustomizationFieldsHTML(data)}
                    `,
                    'video': `
                        <table class="form-table">
                            <tr>
                                <th><label>URL Video (YouTube/Vimeo)</label></th>
                                <td><input type="url" class="fp-lp-field" data-field="video_url" value="${this.escapeHtml(data.video_url || '')}" style="width: 100%;" placeholder="https://youtube.com/watch?v=..."><p class="description">Incolla l'URL completo di YouTube o Vimeo</p></td>
                            </tr>
                            <tr>
                                <th><label>Allineamento</label></th>
                                <td>
                                    <select class="fp-lp-field" data-field="align">
                                        <option value="left" ${data.align === 'left' ? 'selected' : ''}>Sinistra</option>
                                        <option value="center" ${!data.align || data.align === 'center' ? 'selected' : ''}>Centro</option>
                                        <option value="right" ${data.align === 'right' ? 'selected' : ''}>Destra</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        ${this.getCommonCustomizationFieldsHTML(data)}
                    `,
                    'separator': `
                        <table class="form-table">
                            <tr>
                                <th><label>Stile</label></th>
                                <td>
                                    <select class="fp-lp-field" data-field="style">
                                        <option value="solid" ${!data.style || data.style === 'solid' ? 'selected' : ''}>Linea Solida</option>
                                        <option value="dashed" ${data.style === 'dashed' ? 'selected' : ''}>Linea Tratteggiata</option>
                                        <option value="dotted" ${data.style === 'dotted' ? 'selected' : ''}>Linea Puntinata</option>
                                        <option value="space" ${data.style === 'space' ? 'selected' : ''}>Spazio</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label>Altezza (px)</label></th>
                                <td><input type="number" class="fp-lp-field" data-field="height" value="${data.height || '40'}" min="10" max="200" step="10"></td>
                            </tr>
                        </table>
                    `,
                    'features': `
                        <table class="form-table">
                            <tr>
                                <th><label>Colonne</label></th>
                                <td>
                                    <select class="fp-lp-field" data-field="columns">
                                        <option value="2" ${data.columns === '2' ? 'selected' : ''}>2</option>
                                        <option value="3" ${!data.columns || data.columns === '3' ? 'selected' : ''}>3</option>
                                        <option value="4" ${data.columns === '4' ? 'selected' : ''}>4</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <div class="fp-lp-features-list">
                            <div class="fp-lp-feature-item" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; background: #f9f9f9;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                    <strong>Feature #1</strong>
                                    <button type="button" class="button fp-lp-remove-feature" style="display:none;">Rimuovi</button>
                                </div>
                                <table style="width: 100%;">
                                    <tr>
                                        <td style="padding: 5px 0;"><label>Icona</label></td>
                                        <td style="padding: 5px 0;">
                                            <div style="display: flex; gap: 5px; align-items: center;">
                                                <input type="text" class="fp-lp-feature-field fp-lp-icon-input" data-field="icon" data-feature-index="0" value="" style="flex: 1;" placeholder="fa fa-star">
                                                <button type="button" class="button fp-lp-icon-picker-btn" data-target-input=".fp-lp-feature-field[data-feature-index='0'][data-field='icon']">
                                                    <span class="dashicons dashicons-admin-appearance"></span> Scegli Icona
                                                </button>
                                            </div>
                                            <p class="description">Seleziona un'icona dalla libreria o inserisci manualmente la classe CSS</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0;"><label>Colore Icona</label></td>
                                        <td style="padding: 5px 0;">
                                            <input type="text" class="fp-lp-feature-field" data-field="icon_color" data-feature-index="0" value="" style="width: 200px;" placeholder="#0073aa">
                                            <p class="description">Colore hex personalizzato per questa icona (opzionale)</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0;"><label>Titolo</label></td>
                                        <td style="padding: 5px 0;">
                                            <input type="text" class="fp-lp-feature-field" data-field="title" data-feature-index="0" value="" style="width: 100%;" placeholder="Titolo feature">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0;"><label>Testo</label></td>
                                        <td style="padding: 5px 0;">
                                            <textarea class="fp-lp-feature-field" data-field="text" data-feature-index="0" rows="3" style="width: 100%;" placeholder="Descrizione feature"></textarea>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <button type="button" class="button fp-lp-add-feature">+ Aggiungi Feature</button>
                        ${this.getCommonCustomizationFieldsHTML(data)}
                    `,
                    'counters': `
                        <table class="form-table">
                            <tr>
                                <th><label>Colonne</label></th>
                                <td>
                                    <select class="fp-lp-field" data-field="columns">
                                        <option value="2" ${data.columns === '2' ? 'selected' : ''}>2</option>
                                        <option value="3" ${data.columns === '3' ? 'selected' : ''}>3</option>
                                        <option value="4" ${!data.columns || data.columns === '4' ? 'selected' : ''}>4</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <div class="fp-lp-counters-list">
                            <div class="fp-lp-counter-item" data-counter-index="0">
                                <div class="fp-lp-counter-header">
                                    <span>Counter 1</span>
                                    <button type="button" class="button button-small fp-lp-remove-counter">Rimuovi</button>
                                </div>
                                <div class="fp-lp-counter-content">
                                    <p><label>Numero:</label><input type="number" class="fp-lp-counter-field" data-field="number" data-counter-index="0" placeholder="1000" style="width:100%;"></p>
                                    <p><label>Label:</label><input type="text" class="fp-lp-counter-field" data-field="label" data-counter-index="0" placeholder="Clienti" style="width:100%;"></p>
                                    <p><label>Prefisso:</label><input type="text" class="fp-lp-counter-field" data-field="prefix" data-counter-index="0" placeholder="" style="width:45%;"> <label>Suffisso:</label><input type="text" class="fp-lp-counter-field" data-field="suffix" data-counter-index="0" placeholder="+" style="width:45%;"></p>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="button fp-lp-add-counter">+ Aggiungi Counter</button>
                        ${this.getCommonCustomizationFieldsHTML(data)}
                    `,
                    'faq': `
                        <div class="fp-lp-faq-list">
                            <div class="fp-lp-faq-item" data-faq-index="0">
                                <div class="fp-lp-faq-header">
                                    <span>FAQ 1</span>
                                    <button type="button" class="button button-small fp-lp-remove-faq">Rimuovi</button>
                                </div>
                                <div class="fp-lp-faq-content">
                                    <p><label>Domanda:</label><input type="text" class="fp-lp-faq-field" data-field="question" data-faq-index="0" placeholder="La tua domanda" style="width:100%;"></p>
                                    <p><label>Risposta:</label><textarea class="fp-lp-faq-field" data-field="answer" data-faq-index="0" rows="3" style="width:100%;"></textarea></p>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="button fp-lp-add-faq">+ Aggiungi FAQ</button>
                        ${this.getCommonCustomizationFieldsHTML(data)}
                    `,
                    'tabs': `
                        <div class="fp-lp-tabs-list">
                            <div class="fp-lp-tab-item" data-tab-index="0">
                                <div class="fp-lp-tab-header">
                                    <span>Tab 1</span>
                                    <button type="button" class="button button-small fp-lp-remove-tab">Rimuovi</button>
                                </div>
                                <div class="fp-lp-tab-content">
                                    <p><label>Titolo Tab:</label><input type="text" class="fp-lp-tab-field" data-field="title" data-tab-index="0" placeholder="Titolo" style="width:100%;"></p>
                                    <p><label>Contenuto:</label><textarea class="fp-lp-tab-field" data-field="content" data-tab-index="0" rows="4" style="width:100%;"></textarea></p>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="button fp-lp-add-tab">+ Aggiungi Tab</button>
                        ${this.getCommonCustomizationFieldsHTML(data)}
                    `
                };
                
                return fields[type] || '';
            },
            
            getCommonCustomizationFieldsHTML: function(data) {
                data = data || {};
                const hideMobileChecked = data.hide_mobile === '1' || data.hide_mobile === 1 ? 'checked' : '';
                const hideTabletChecked = data.hide_tablet === '1' || data.hide_tablet === 1 ? 'checked' : '';
                const hideDesktopChecked = data.hide_desktop === '1' || data.hide_desktop === 1 ? 'checked' : '';
                return `
                    <table class="form-table">
                        <tr>
                            <th colspan="2" style="padding-top: 15px; border-top: 1px solid #ddd;"><strong>Personalizzazioni CSS</strong></th>
                        </tr>
                        <tr>
                            <th><label>Colore Background</label></th>
                            <td><input type="text" class="fp-lp-field" data-field="bg_color" value="${this.escapeHtml(data.bg_color || '')}" placeholder="#ffffff o trasparente" style="width: 100%;"><p class="description">Colore di sfondo della sezione (es: #ffffff, #f5f5f5, trasparente)</p></td>
                        </tr>
                        <tr>
                            <th><label>Padding (px)</label></th>
                            <td><input type="text" class="fp-lp-field" data-field="padding" value="${this.escapeHtml(data.padding || '')}" placeholder="20px" style="width: 100%;"><p class="description">Spaziatura interna (es: "20px" o "20px 10px" per top-bottom left-right)</p></td>
                        </tr>
                        <tr>
                            <th><label>Margin (px)</label></th>
                            <td><input type="text" class="fp-lp-field" data-field="margin" value="${this.escapeHtml(data.margin || '')}" placeholder="20px" style="width: 100%;"><p class="description">Spaziatura esterna (es: "20px" o "20px 10px")</p></td>
                        </tr>
                        <tr>
                            <th><label>Classe CSS Personalizzata</label></th>
                            <td><input type="text" class="fp-lp-field" data-field="css_class" value="${this.escapeHtml(data.css_class || '')}" placeholder="mia-classe" style="width: 100%;"><p class="description">Aggiungi una classe CSS personalizzata (senza punto)</p></td>
                        </tr>
                        <tr>
                            <th><label>ID HTML Personalizzato</label></th>
                            <td><input type="text" class="fp-lp-field" data-field="css_id" value="${this.escapeHtml(data.css_id || '')}" placeholder="mio-id" style="width: 100%;"><p class="description">Aggiungi un ID HTML personalizzato (senza #)</p></td>
                        </tr>
                        <tr>
                            <th colspan="2" style="padding-top: 15px; border-top: 1px solid #ddd;"><strong>Opzioni Responsive</strong></th>
                        </tr>
                        <tr>
                            <th><label>Padding Mobile (px)</label></th>
                            <td><input type="text" class="fp-lp-field" data-field="padding_mobile" value="${this.escapeHtml(data.padding_mobile || '')}" placeholder="10px" style="width: 100%;"><p class="description">Spaziatura interna su dispositivi mobile (&lt;768px)</p></td>
                        </tr>
                        <tr>
                            <th><label>Padding Tablet (px)</label></th>
                            <td><input type="text" class="fp-lp-field" data-field="padding_tablet" value="${this.escapeHtml(data.padding_tablet || '')}" placeholder="15px" style="width: 100%;"><p class="description">Spaziatura interna su tablet (768px-1024px)</p></td>
                        </tr>
                        <tr>
                            <th><label>Padding Desktop (px)</label></th>
                            <td><input type="text" class="fp-lp-field" data-field="padding_desktop" value="${this.escapeHtml(data.padding_desktop || '')}" placeholder="20px" style="width: 100%;"><p class="description">Spaziatura interna su desktop (&gt;1024px)</p></td>
                        </tr>
                        <tr>
                            <th><label>Margin Mobile (px)</label></th>
                            <td><input type="text" class="fp-lp-field" data-field="margin_mobile" value="${this.escapeHtml(data.margin_mobile || '')}" placeholder="10px" style="width: 100%;"><p class="description">Spaziatura esterna su dispositivi mobile</p></td>
                        </tr>
                        <tr>
                            <th><label>Margin Tablet (px)</label></th>
                            <td><input type="text" class="fp-lp-field" data-field="margin_tablet" value="${this.escapeHtml(data.margin_tablet || '')}" placeholder="15px" style="width: 100%;"><p class="description">Spaziatura esterna su tablet</p></td>
                        </tr>
                        <tr>
                            <th><label>Margin Desktop (px)</label></th>
                            <td><input type="text" class="fp-lp-field" data-field="margin_desktop" value="${this.escapeHtml(data.margin_desktop || '')}" placeholder="20px" style="width: 100%;"><p class="description">Spaziatura esterna su desktop</p></td>
                        </tr>
                        <tr>
                            <th><label>Visibilità</label></th>
                            <td>
                                <label style="margin-right: 15px;"><input type="checkbox" class="fp-lp-field" data-field="hide_mobile" value="1" ${hideMobileChecked}> Nascondi su Mobile</label>
                                <label style="margin-right: 15px;"><input type="checkbox" class="fp-lp-field" data-field="hide_tablet" value="1" ${hideTabletChecked}> Nascondi su Tablet</label>
                                <label><input type="checkbox" class="fp-lp-field" data-field="hide_desktop" value="1" ${hideDesktopChecked}> Nascondi su Desktop</label>
                            </td>
                        </tr>
                    </table>
                `;
            },
            
            escapeHtml: function(text) {
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
            },
            
            updateSectionsData: function() {
                const sections = [];
                const self = this;
                
                $('#fp-lp-sections-list .fp-lp-section-item').each(function() {
                    const $section = $(this);
                    const type = $section.data('type');
                    const data = {};
                    
                    // Campi semplici
                    $section.find('.fp-lp-field').each(function() {
                        const $field = $(this);
                        const field = $field.data('field');
                        let value;
                        
                        // Gestisci checkbox separatamente
                        if ($field.attr('type') === 'checkbox') {
                            // Salva solo se selezionato, altrimenti non salvare il campo
                            if ($field.is(':checked')) {
                                value = '1';
                            } else {
                                // Non salvare il campo se non selezionato
                                return; // Skip this field
                            }
                        } else {
                            value = $field.val();
                            
                            // Converti numeri se necessario
                            if ($field.attr('type') === 'number') {
                                value = value ? parseInt(value, 10) : '';
                            }
                        }
                        
                        if (field) {
                            data[field] = value;
                        }
                    });
                    
                    // Campi Features
                    if (type === 'features') {
                        data.features = [];
                        $section.find('.fp-lp-feature-item').each(function() {
                            const $item = $(this);
                            const feature = {};
                            $item.find('.fp-lp-feature-field').each(function() {
                                const $field = $(this);
                                feature[$field.data('field')] = $field.val();
                            });
                            if (feature.icon || feature.title || feature.text) {
                                data.features.push(feature);
                            }
                        });
                    }
                    
                    // Campi Counters
                    if (type === 'counters') {
                        data.counters = [];
                        $section.find('.fp-lp-counter-item').each(function() {
                            const $item = $(this);
                            const counter = {};
                            $item.find('.fp-lp-counter-field').each(function() {
                                const $field = $(this);
                                counter[$field.data('field')] = $field.val();
                            });
                            if (counter.number || counter.label) {
                                data.counters.push(counter);
                            }
                        });
                    }
                    
                    // Campi FAQ
                    if (type === 'faq') {
                        data.faqs = [];
                        $section.find('.fp-lp-faq-item').each(function() {
                            const $item = $(this);
                            const faq = {};
                            $item.find('.fp-lp-faq-field').each(function() {
                                const $field = $(this);
                                faq[$field.data('field')] = $field.val();
                            });
                            if (faq.question || faq.answer) {
                                data.faqs.push(faq);
                            }
                        });
                    }
                    
                    // Campi Tabs
                    if (type === 'tabs') {
                        data.tabs = [];
                        $section.find('.fp-lp-tab-item').each(function() {
                            const $item = $(this);
                            const tab = {};
                            $item.find('.fp-lp-tab-field').each(function() {
                                const $field = $(this);
                                tab[$field.data('field')] = $field.val();
                            });
                            if (tab.title || tab.content) {
                                data.tabs.push(tab);
                            }
                        });
                    }
                    
                    sections.push({
                        type: type,
                        data: data
                    });
                });
                
                $('#fp-lp-sections-data').val(JSON.stringify(sections));
            },
            
            initSortable: function() {
                if ($.fn.sortable) {
                    $('#fp-lp-sections-list').sortable({
                        handle: '.fp-lp-section-header',
                        axis: 'y',
                        placeholder: 'fp-lp-sortable-placeholder',
                        tolerance: 'pointer',
                        update: () => {
                            this.updateSectionsData();
                        }
                    });
                }
            },
            
            // Inizializza color picker per campi colore
            initColorPickers: function() {
                const self = this;
                
                // Aggiungi color picker a tutti i campi colore esistenti
                this.addColorPickersToFields();
                
                // Event handler per sincronizzare color picker -> text input
                $(document).on('input', '.fp-lp-color-input', function() {
                    const $input = $(this);
                    const $textInput = $input.closest('.fp-lp-color-wrapper').find('.fp-lp-field');
                    $textInput.val($input.val()).trigger('change');
                });
                
                // Event handler per sincronizzare text input -> color picker
                $(document).on('input', '.fp-lp-field[data-field*="color"]', function() {
                    const $input = $(this);
                    const $colorInput = $input.closest('.fp-lp-color-wrapper').find('.fp-lp-color-input');
                    const val = $input.val();
                    if ($colorInput.length && /^#[0-9A-Fa-f]{6}$/i.test(val)) {
                        $colorInput.val(val);
                    }
                });
            },
            
            // Aggiunge color picker ai campi colore
            addColorPickersToFields: function() {
                $('.fp-lp-field[data-field*="color"]').each(function() {
                    const $input = $(this);
                    // Se già wrappato, salta
                    if ($input.parent().hasClass('fp-lp-color-wrapper')) return;
                    
                    const currentVal = $input.val() || '#333333';
                    const colorVal = /^#[0-9A-Fa-f]{6}$/i.test(currentVal) ? currentVal : '#333333';
                    
                    // Wrap input e aggiungi color picker
                    $input.wrap('<div class="fp-lp-color-wrapper" style="display:flex;gap:8px;align-items:center;"></div>');
                    $input.before('<input type="color" class="fp-lp-color-input" value="' + colorVal + '" style="width:40px;height:32px;padding:2px;border:1px solid #8c8f94;border-radius:4px;cursor:pointer;">');
                });
            },
            
            // Aggiorna contatori elementi in sezioni complesse
            updateAllItemCounts: function() {
                const self = this;
                $('.fp-lp-section-item').each(function() {
                    self.updateItemCount($(this));
                });
            },
            
            updateItemCount: function($section) {
                const type = $section.data('type');
                let count = 0;
                
                if (type === 'features') {
                    count = $section.find('.fp-lp-feature-item').length;
                } else if (type === 'counters') {
                    count = $section.find('.fp-lp-counter-item').length;
                } else if (type === 'faq') {
                    count = $section.find('.fp-lp-faq-item').length;
                } else if (type === 'tabs') {
                    count = $section.find('.fp-lp-tab-item').length;
                } else {
                    $section.find('.fp-lp-item-count').remove();
                    return;
                }
                
                let $counter = $section.find('.fp-lp-item-count');
                if ($counter.length === 0) {
                    $counter = $('<span class="fp-lp-item-count"></span>');
                    $section.find('.fp-lp-section-title').append($counter);
                }
                $counter.text(count + ' ' + (count === 1 ? 'elemento' : 'elementi'));
            },
            
            // Indicatore stato salvataggio
            initSaveIndicator: function() {
                const $indicator = $('.fp-lp-save-indicator');
                if ($indicator.length === 0) return;
                
                let saveTimeout;
                $(document).on('input change', '.fp-lp-field, .fp-lp-feature-field, .fp-lp-counter-field, .fp-lp-faq-field, .fp-lp-tab-field', function() {
                    $indicator
                        .removeClass('ready')
                        .addClass('modified')
                        .html('⏳ Modifiche non salvate');
                    
                    clearTimeout(saveTimeout);
                    saveTimeout = setTimeout(function() {
                        $indicator
                            .removeClass('modified')
                            .addClass('ready')
                            .html('✓ Pronto per salvare');
                    }, 800);
                });
            },
            
            // Ottieni icona per tipo sezione
            getSectionIcon: function(type) {
                return this.sectionIcons[type] || '📦';
            }
        };
        
        // Bind eventi comprimi/espandi tutto
        $(document).on('click', '.fp-lp-collapse-all', function(e) {
            e.preventDefault();
            $('.fp-lp-section-content').slideUp(200);
            $('.fp-lp-toggle-section').text('▶');
        });
        
        $(document).on('click', '.fp-lp-expand-all', function(e) {
            e.preventDefault();
            $('.fp-lp-section-content').slideDown(200);
            $('.fp-lp-toggle-section').text('▼');
        });
        
        builder.init();
        
        // Inizializza Icon Picker
        iconPicker.init();
    });
    
    // Icon Picker
    const iconPicker = {
        currentTarget: null,
        selectedIcon: null,
        
        init: function() {
            const self = this;
            
            // Apri modal quando si clicca su un pulsante selettore
            $(document).on('click', '.fp-lp-icon-picker-btn', function(e) {
                e.preventDefault();
                const $btn = $(this);
                const targetSelector = $btn.data('target-input');
                self.currentTarget = $(targetSelector);
                self.openModal();
            });
            
            // Cambia tab categoria
            $(document).on('click', '.fp-lp-icon-tab', function() {
                const category = $(this).data('category');
                $('.fp-lp-icon-tab').removeClass('active');
                $(this).addClass('active');
                $('.fp-lp-icons-grid').hide();
                $('#fp-lp-icons-grid-' + category).show();
                self.filterIcons('');
            });
            
            // Cerca icone
            $(document).on('input', '#fp-lp-icon-search-input', function() {
                const search = $(this).val().toLowerCase();
                self.filterIcons(search);
            });
            
            // Seleziona icona
            $(document).on('click', '.fp-lp-icon-item', function() {
                $('.fp-lp-icon-item').removeClass('selected');
                $(this).addClass('selected');
                self.selectedIcon = $(this).data('icon');
                $('#fp-lp-icon-picker-select').prop('disabled', false);
            });
            
            // Conferma selezione
            $(document).on('click', '#fp-lp-icon-picker-select', function() {
                if (self.selectedIcon && self.currentTarget) {
                    self.currentTarget.val(self.selectedIcon).trigger('change');
                    self.closeModal();
                }
            });
            
            // Annulla/Chiudi
            $(document).on('click', '#fp-lp-icon-picker-cancel, .fp-lp-icon-modal-close, .fp-lp-icon-modal-overlay', function() {
                self.closeModal();
            });
            
            // Chiudi con ESC
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $('#fp-lp-icon-picker-modal').is(':visible')) {
                    self.closeModal();
                }
            });
        },
        
        openModal: function() {
            this.selectedIcon = null;
            $('#fp-lp-icon-picker-modal').fadeIn(200);
            $('#fp-lp-icon-search-input').val('').focus();
            $('.fp-lp-icon-item').removeClass('selected');
            $('#fp-lp-icon-picker-select').prop('disabled', true);
            
            // Mostra categoria solid di default
            $('.fp-lp-icon-tab').removeClass('active');
            $('.fp-lp-icon-tab[data-category="solid"]').addClass('active');
            $('.fp-lp-icons-grid').hide();
            $('#fp-lp-icons-grid-solid').show();
        },
        
        closeModal: function() {
            $('#fp-lp-icon-picker-modal').fadeOut(200);
            this.currentTarget = null;
            this.selectedIcon = null;
            $('#fp-lp-icon-search-input').val('');
            $('.fp-lp-icon-item').removeClass('selected');
        },
        
        filterIcons: function(search) {
            const activeCategory = $('.fp-lp-icon-tab.active').data('category');
            const $grid = $('#fp-lp-icons-grid-' + activeCategory);
            
            if (!search) {
                $grid.find('.fp-lp-icon-item').show();
                return;
            }
            
            $grid.find('.fp-lp-icon-item').each(function() {
                const $item = $(this);
                const iconName = $item.data('icon').toLowerCase();
                if (iconName.includes(search)) {
                    $item.show();
                } else {
                    $item.hide();
                }
            });
        }
    };
    
})(jQuery);
