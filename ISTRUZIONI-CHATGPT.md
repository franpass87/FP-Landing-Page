# 📋 Istruzioni per ChatGPT - Generazione Landing Page JSON

Questo documento fornisce istruzioni dettagliate per generare file JSON compatibili con il plugin **FP Landing Page** di WordPress.

## 🎯 Struttura Base del JSON

Il formato JSON deve seguire questa struttura:

```json
{
  "title": "Titolo della Landing Page",
  "sections": [
    // Array di sezioni
  ],
  "settings": {
    // Impostazioni opzionali
  }
}
```

---

## 📦 Tipi di Sezioni Disponibili

### 1. **title** - Titolo/Heading
```json
{
  "type": "title",
  "data": {
    "text": "Il Tuo Titolo Principale",
    "level": "h1",
    "align": "center",
    "font_size": 48,
    "text_color": "#333333",
    "font_weight": "700"
  }
}
```

**Parametri disponibili:**
- `text` (stringa, obbligatorio): Testo del titolo
- `level` (stringa): `h1`, `h2`, `h3`, `h4`, `h5`, `h6` (default: `h2`) - Supporta tutti i livelli heading HTML
- `align` (stringa): `left`, `center`, `right` (default: `left`)
- `font_size` (numero): Dimensione font in pixel
- `text_color` (stringa): Colore hex (es: `#333333`)
- `font_weight` (stringa): `300`, `400`, `600`, `700`, `800`
- `font_size_mobile`, `font_size_tablet`, `font_size_desktop` (numeri): Dimensione responsive
- `align_mobile`, `align_tablet`, `align_desktop` (stringhe): Allineamento responsive

---

### 2. **text** - Testo/Paragrafo
```json
{
  "type": "text",
  "data": {
    "content": "<p>Questo è un paragrafo di testo con <strong>formattazione HTML</strong> supportata.</p>",
    "align": "center"
  }
}
```

**Parametri disponibili:**
- `content` (stringa HTML, obbligatorio): Contenuto del testo (HTML consentito)
- `align` (stringa): `left`, `center`, `right`, `justify`
- `align_mobile`, `align_tablet`, `align_desktop`: Allineamento responsive

---

### 3. **image** - Immagine
```json
{
  "type": "image",
  "data": {
    "image_id": 123,
    "alt": "Descrizione immagine",
    "align": "center",
    "link": "https://example.com",
    "max_width": "800px",
    "border_radius": 8,
    "box_shadow": "0 2px 8px rgba(0,0,0,0.1)"
  }
}
```

**Parametri disponibili:**
- `image_id` (numero, obbligatorio): ID media WordPress (puoi usare `0` se non conosci l'ID)
- `alt` (stringa): Testo alternativo
- `align` (stringa): `left`, `center`, `right`
- `link` (stringa): URL per rendere l'immagine cliccabile
- `max_width` (stringa): Larghezza massima (es: `800px`, `100%`, `50vw`)
- `border_radius` (numero): Angoli arrotondati in pixel
- `box_shadow` (stringa): Ombra CSS
- `max_width_mobile`, `max_width_tablet`, `max_width_desktop` (stringhe): Larghezza responsive
- `align_mobile`, `align_tablet`, `align_desktop` (stringhe): Allineamento responsive (`left`, `center`, `right`)

---

### 4. **gallery** - Galleria Immagini
```json
{
  "type": "gallery",
  "data": {
    "gallery_ids": "123,124,125,126",
    "columns": 3,
    "image_border_radius": 4,
    "gap": 10
  }
}
```

**Parametri disponibili:**
- `gallery_ids` (stringa, obbligatorio): ID immagini separati da virgola
- `columns` (numero): `1`, `2`, `3`, `4` (default: `3`) - Numero di colonne della griglia
- `image_border_radius` (numero): Angoli arrotondati immagini
- `gap` (numero): Spazio tra immagini in pixel

---

### 5. **cta** - Call to Action / Pulsante
```json
{
  "type": "cta",
  "data": {
    "button_text": "Inizia Ora",
    "button_url": "https://example.com/registrazione",
    "style": "primary",
    "align": "center",
    "button_bg_color": "#0073aa",
    "button_text_color": "#ffffff",
    "button_border_radius": 4
  }
}
```

**Parametri disponibili:**
- `button_text` (stringa, obbligatorio): Testo del pulsante
- `button_url` (stringa): URL di destinazione (default: `#` se non specificato)
- `style` (stringa): `primary`, `secondary`, `outline` (default: `primary`)
- `align` (stringa): `left`, `center`, `right`
- `button_bg_color` (stringa): Colore sfondo hex
- `button_text_color` (stringa): Colore testo hex
- `button_border_radius` (numero): Angoli arrotondati (0-50)
- `align_mobile`, `align_tablet`, `align_desktop`: Allineamento responsive

---

### 6. **video** - Video (YouTube/Vimeo)
```json
{
  "type": "video",
  "data": {
    "video_url": "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
    "align": "center"
  }
}
```

**Parametri disponibili:**
- `video_url` (stringa, obbligatorio): URL YouTube o Vimeo
- `align` (stringa): `left`, `center`, `right`

**Note:** Supporta URL YouTube e Vimeo. Il plugin converte automaticamente in embed.

---

### 7. **separator** - Separatore/Spazio
```json
{
  "type": "separator",
  "data": {
    "style": "solid",
    "height": 40,
    "color": "#e0e0e0"
  }
}
```

**Parametri disponibili:**
- `style` (stringa): `solid`, `dashed`, `dotted`, `space` (default: `solid`)
- `height` (numero): Altezza in pixel (default: `40`)
- `color` (stringa): Colore hex del bordo per linee (es: `#e0e0e0`) - Non applicabile allo stile `space`

---

### 8. **features** - Sezione Features/Caratteristiche
```json
{
  "type": "features",
  "data": {
    "columns": 3,
    "icon_color": "#0073aa",
    "title_color": "#1d2327",
    "text_color": "#666",
    "features": [
      {
        "icon": "fa fa-star",
        "title": "Feature 1",
        "text": "Descrizione della prima feature con dettagli.",
        "icon_color": "#ff6b6b"
      },
      {
        "icon": "fa fa-rocket",
        "title": "Feature 2",
        "text": "Descrizione della seconda feature."
      },
      {
        "icon": "fa fa-heart",
        "title": "Feature 3",
        "text": "Descrizione della terza feature."
      }
    ]
  }
}
```

**Parametri disponibili:**
- `columns` (numero): `1`, `2`, `3`, `4` (default: `3`) - Numero di colonne della griglia
- `icon_color` (stringa, opzionale): Colore hex per tutte le icone della sezione (es: `#0073aa`)
- `title_color` (stringa, opzionale): Colore hex per i titoli delle features (es: `#1d2327`)
- `text_color` (stringa, opzionale): Colore hex per il testo descrittivo (es: `#666`)
- `features` (array, obbligatorio): Array di oggetti feature
  - `icon` (stringa): Classe CSS icona Font Awesome (es: `fa fa-star`)
  - `title` (stringa): Titolo feature
  - `text` (stringa): Descrizione feature (HTML consentito)
  - `icon_color` (stringa, opzionale): Colore hex per l'icona di questa feature specifica (sovrascrive il colore globale)

---

### 9. **counters** - Contatori/Numeri
```json
{
  "type": "counters",
  "data": {
    "columns": 4,
    "icon_color": "#0073aa",
    "number_color": "#1d2327",
    "label_color": "#50575e",
    "counters": [
      {
        "icon": "fa fa-users",
        "number": "1000",
        "label": "Clienti Soddisfatti",
        "prefix": "",
        "suffix": "+",
        "icon_color": "#ff6b6b"
      },
      {
        "number": "500",
        "label": "Progetti Completati",
        "prefix": "",
        "suffix": ""
      }
    ]
  }
}
```

**Parametri disponibili:**
- `columns` (numero): `1`, `2`, `3`, `4` (default: `4`) - Numero di colonne della griglia
- `icon_color` (stringa, opzionale): Colore hex per tutte le icone della sezione (es: `#0073aa`)
- `number_color` (stringa, opzionale): Colore hex per i numeri (es: `#1d2327`) - Include anche prefix e suffix
- `label_color` (stringa, opzionale): Colore hex per le etichette (es: `#50575e`)
- `counters` (array, obbligatorio): Array di oggetti contatore
  - `icon` (stringa, opzionale): Classe CSS icona Font Awesome (es: `fa fa-users`)
  - `number` (stringa): Numero da visualizzare
  - `label` (stringa): Etichetta
  - `prefix` (stringa, opzionale): Prefisso (es: `€`, `$`)
  - `suffix` (stringa, opzionale): Suffisso (es: `+`, `%`)
  - `icon_color` (stringa, opzionale): Colore hex per l'icona di questo contatore specifico (sovrascrive il colore globale)

---

### 10. **faq** - Domande Frequenti
```json
{
  "type": "faq",
  "data": {
    "icon_color": "#333",
    "question_color": "#1d2327",
    "answer_color": "#555",
    "bg_color": "#f8f8f8",
    "faqs": [
      {
        "question": "Qual è la prima domanda?",
        "answer": "Questa è la risposta alla prima domanda."
      },
      {
        "question": "Qual è la seconda domanda?",
        "answer": "<p>Questa è la risposta alla seconda domanda con <strong>formattazione HTML</strong>.</p>"
      }
    ]
  }
}
```

**Parametri disponibili:**
- `icon_color` (stringa, opzionale): Colore hex per l'icona "+" di espansione (es: `#333`)
- `question_color` (stringa, opzionale): Colore hex per il testo delle domande (es: `#1d2327`)
- `answer_color` (stringa, opzionale): Colore hex per il testo delle risposte (es: `#555`)
- `bg_color` (stringa, opzionale): Colore hex per lo sfondo delle domande (es: `#f8f8f8`)
- `faqs` (array, obbligatorio): Array di oggetti FAQ
  - `question` (stringa): Domanda
  - `answer` (stringa): Risposta (HTML consentito)

---

### 11. **tabs** - Tab/Schede
```json
{
  "type": "tabs",
  "data": {
    "text_color": "#666",
    "active_bg_color": "#0073aa",
    "active_text_color": "#0073aa",
    "border_color": "#e0e0e0",
    "content_color": "#555",
    "tabs": [
      {
        "title": "Tab 1",
        "content": "<p>Contenuto del primo tab con <strong>HTML</strong>.</p>"
      },
      {
        "title": "Tab 2",
        "content": "<p>Contenuto del secondo tab.</p>"
      }
    ]
  }
}
```

**Parametri disponibili:**
- `text_color` (stringa, opzionale): Colore hex per il testo dei tab inattivi (es: `#666`)
- `active_bg_color` (stringa, opzionale): Colore hex per lo sfondo del tab attivo (es: `#0073aa`)
- `active_text_color` (stringa, opzionale): Colore hex per il testo del tab attivo (es: `#0073aa`)
- `border_color` (stringa, opzionale): Colore hex per il bordo inferiore dei tab (es: `#e0e0e0`)
- `content_color` (stringa, opzionale): Colore hex per il contenuto dei tab (es: `#555`)
- `tabs` (array, obbligatorio): Array di oggetti tab
  - `title` (stringa): Titolo tab
  - `content` (stringa): Contenuto tab (HTML consentito)

---

### 12. **shortcode** - Shortcode WordPress
```json
{
  "type": "shortcode",
  "data": {
    "shortcode": "[contact-form-7 id=\"123\"]"
  }
}
```

**Parametri disponibili:**
- `shortcode` (stringa, obbligatorio): Shortcode WordPress completo

---

## 🎨 Personalizzazioni CSS Comuni (per tutte le sezioni)

Ogni sezione può includere questi campi opzionali per personalizzazione:

```json
{
  "type": "title",
  "data": {
    "text": "Titolo",
    "bg_color": "#f5f5f5",
    "padding": "40px 20px",
    "margin": "20px 0",
    "css_class": "mia-classe-personalizzata",
    "css_id": "mio-id-unico",
    "padding_mobile": "20px 10px",
    "padding_tablet": "30px 15px",
    "padding_desktop": "40px 20px",
    "margin_mobile": "10px 0",
    "margin_tablet": "15px 0",
    "margin_desktop": "20px 0",
    "hide_mobile": false,
    "hide_tablet": false,
    "hide_desktop": false
  }
}
```

**Campi di personalizzazione:**
- `bg_color` (stringa): Colore sfondo hex o `trasparente`
- `padding` (stringa): Padding CSS (es: `20px`, `20px 10px`)
- `margin` (stringa): Margin CSS
- `css_class` (stringa): Classe CSS personalizzata (senza punto)
- `css_id` (stringa): ID HTML personalizzato (senza #)
- `padding_mobile`, `padding_tablet`, `padding_desktop` (stringhe): Padding responsive
- `margin_mobile`, `margin_tablet`, `margin_desktop` (stringhe): Margin responsive
- `hide_mobile` (booleano/number/stringa): Nascondi sezione su mobile (`true`, `1`, `"1"`, `"on"`)
- `hide_tablet` (booleano/number/stringa): Nascondi sezione su tablet (`true`, `1`, `"1"`, `"on"`)
- `hide_desktop` (booleano/number/stringa): Nascondi sezione su desktop (`true`, `1`, `"1"`, `"on"`)

**Nota sulla visibilità:** Non impostare tutti e tre i parametri `hide_*` a `true` contemporaneamente, altrimenti la sezione verrà sempre mostrata (per evitare errori di configurazione).

---

## ⚙️ Sezione Settings (Opzionale)

Le impostazioni globali della landing page:

```json
{
  "title": "Titolo Landing Page",
  "sections": [...],
  "settings": {
    "bg_color": "#ffffff",
    "text_color": "#333333",
    "header_style": "default",
    "footer_text": "<p>Testo footer opzionale</p>",
    "custom_css": "/* CSS personalizzato */\n.fp-landing-page-container {\n    /* Esempio */\n}"
  }
}
```

**Parametri settings:**
- `bg_color` (stringa): Colore sfondo globale
- `text_color` (stringa): Colore testo globale
- `header_style` (stringa): `default`, `transparent`, `hidden`
- `footer_text` (stringa): Testo footer (HTML consentito)
- `custom_css` (stringa): CSS personalizzato che verrà applicato solo a questa landing page (puoi usare `#fp-landing-page-{ID}` per targetizzare specificamente questa pagina)

---

## 📝 Esempio Completo

```json
{
  "title": "Landing Page Prodotto XYZ",
  "sections": [
    {
      "type": "title",
      "data": {
        "text": "Benvenuto in Prodotto XYZ",
        "level": "h1",
        "align": "center",
        "font_size": 48,
        "text_color": "#1a1a1a",
        "font_weight": "700"
      }
    },
    {
      "type": "text",
      "data": {
        "content": "<p>Scopri le incredibili caratteristiche del nostro prodotto rivoluzionario.</p>",
        "align": "center"
      }
    },
    {
      "type": "cta",
      "data": {
        "button_text": "Prova Gratis",
        "button_url": "https://example.com/trial",
        "style": "primary",
        "align": "center",
        "button_bg_color": "#0073aa",
        "button_text_color": "#ffffff",
        "button_border_radius": 4
      }
    },
    {
      "type": "separator",
      "data": {
        "style": "solid",
        "height": 60,
        "color": "#e0e0e0"
      }
    },
    {
      "type": "features",
      "data": {
        "columns": 3,
        "icon_color": "#0073aa",
        "title_color": "#1d2327",
        "text_color": "#666",
        "features": [
          {
            "icon": "fa fa-rocket",
            "title": "Velocità",
            "text": "Caricamento ultra-rapido"
          },
          {
            "icon": "fa fa-shield",
            "title": "Sicurezza",
            "text": "Protezione dati avanzata"
          },
          {
            "icon": "fa fa-heart",
            "title": "Supporto",
            "text": "Assistenza 24/7"
          }
        ]
      }
    }
  ],
  "settings": {
    "bg_color": "#ffffff",
    "text_color": "#333333",
    "header_style": "default"
  }
}
```

---

## 🚀 Prompt Template per ChatGPT

Usa questo template quando chiedi a ChatGPT di generare una landing page:

```
Crea un file JSON per una landing page WordPress usando il plugin FP Landing Page.

Tema/Argomento: [descrivi il tema della landing page]
Obiettivo: [es: vendita prodotto, lead generation, presentazione servizio]

Include:
- [ ] Sezione hero con titolo principale
- [ ] Sezione features/caratteristiche
- [ ] Call to action
- [ ] Sezione FAQ
- [ ] Altro: [specifica]

Seguire esattamente il formato JSON descritto nelle istruzioni FP Landing Page.
Ogni sezione deve avere "type" e "data" con i parametri corretti.
```

---

## ⚠️ Note Importanti

1. **IDs Immagini:** Se non conosci gli ID delle immagini WordPress, usa `0` per `image_id`. Dovrai selezionare l'immagine manualmente dopo l'import.

2. **HTML Consentito:** Nei campi `content`, `answer`, `footer_text` puoi usare HTML base (p, strong, em, a, ul, ol, li).

3. **Validazione JSON:** Assicurati che il JSON sia valido (usa un validator online se necessario).

4. **Caratteri Speciali:** Evita caratteri speciali non standard nei valori stringa. Usa escape per virgolette se necessario.

5. **Ordine Sezioni:** Le sezioni vengono importate nell'ordine dell'array.

6. **Parametri Responsive:** I parametri responsive (mobile/tablet/desktop) sono opzionali. Se non specificati, vengono usati i valori base.

7. **Valori Default:** Molti parametri hanno valori di default. Se un parametro non è specificato, il plugin usa i valori predefiniti.

8. **Immagine ID:** Per `image_id`, se usi `0`, dopo l'import dovrai selezionare l'immagine manualmente dal builder. Stesso discorso per `gallery_ids` - usa `"0"` o stringa vuota se non conosci gli ID.

9. **Shortcode Personalizzati:** La sezione `shortcode` può contenere qualsiasi shortcode WordPress valido, inclusi shortcode di altri plugin (Contact Form 7, WooCommerce, ecc.).

10. **HTML nei Contenuti:** I tag HTML consentiti includono: `p`, `strong`, `em`, `a`, `ul`, `ol`, `li`, `br`, `span`. Tag complessi o script vengono filtrati per sicurezza.

11. **Modifica Post-Import:** Dopo l'import, puoi sempre modificare la landing page dal builder WordPress, riordinare le sezioni, aggiungere/rimuovere elementi e personalizzare ulteriormente.

12. **URL Video:** Il plugin supporta automaticamente URL YouTube standard (`youtube.com/watch?v=` o `youtu.be/`) e Vimeo. Non serve convertire manualmente in embed URL.

---

## 📌 Checklist Generazione

Quando generi una landing page JSON, verifica:

- [ ] JSON valido (sintassi corretta)
- [ ] Ogni sezione ha `type` e `data`
- [ ] I tipi di sezione sono tra quelli supportati
- [ ] Campi obbligatori presenti
- [ ] Colori in formato hex (`#ffffff`)
- [ ] Array (features, counters, faqs, tabs) sono array di oggetti
- [ ] HTML nei contenuti è ben formato
- [ ] Nessun carattere speciale problematico
- [ ] Parametri `hide_*` usati correttamente (non tutti a `true`)

---

## 🎨 Personalizzazione Colori

Il plugin supporta una personalizzazione completa dei colori per tutte le sezioni:

- **Title**: `text_color` (colore del testo)
- **CTA**: `button_bg_color`, `button_text_color` (colori pulsante)
- **Separator**: `color` (colore del bordo, solo per linee)
- **Features**: `icon_color` (globale e individuale), `title_color`, `text_color`
- **Counters**: `icon_color` (globale e individuale), `number_color`, `label_color`
- **FAQ**: `icon_color`, `question_color`, `answer_color`, `bg_color`
- **Tabs**: `text_color`, `active_bg_color`, `active_text_color`, `border_color`, `content_color`

Tutti i colori sono opzionali e utilizzano il formato hex (es: `#0073aa`, `#ffffff`). Se non specificati, vengono utilizzati i valori di default del plugin.

---

## 🔗 Risorse

- **Plugin:** FP Landing Page v1.0.4
- **Tipi sezioni supportati:** 12 (title, text, image, gallery, cta, video, separator, features, counters, faq, tabs, shortcode)
- **Formato:** JSON standard UTF-8
- **Personalizzazione colori:** Completa per tutte le sezioni con elementi colorabili
