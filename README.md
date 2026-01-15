# FP Landing Page

Plugin WordPress per la gestione e creazione di landing page personalizzate **senza dover usare Elementor o Gutenberg**.

## Versione
1.0.0

## Requisiti
- WordPress 6.0+
- PHP 7.4+

## Caratteristiche

- ✅ **Custom Post Type "Landing Page"** - Gestisci le landing page come contenuti dedicati
- ✅ **Shortcodes per sezioni comuni** - Hero, Features, CTA, Testimonials, Pricing, Form
- ✅ **Sistema di template personalizzato** - Template dedicato per le landing page
- ✅ **Meta box per configurazione** - Colori, stile header, footer personalizzabile
- ✅ **CSS responsive integrato** - Stili moderni e responsive già inclusi
- ✅ **Nessuna dipendenza da builder** - Funziona con qualsiasi tema WordPress

## Installazione

1. Esegui lo script `RICREA-JUNCTION-FP-LANDING-PAGE.bat` o `RICREA-JUNCTION-FP-LANDING-PAGE.ps1` (come amministratore) per creare la JUNCTION
2. Nella cartella LAB, esegui `composer install` (già fatto)
3. Vai in WordPress → Plugin e attiva il plugin
4. Vai in WordPress → Landing Pages per creare la prima landing page

## Utilizzo

### Creare una Landing Page

1. Vai in **Landing Pages → Aggiungi Nuova**
2. Inserisci il titolo
3. Nel contenuto, componi la landing page usando gli shortcode delle sezioni (vedi sotto)
4. Configura colori e stile nella meta box "Impostazioni Landing Page"
5. Pubblica e prendi nota dell'ID della landing page

### Inserire la Landing Page in una Pagina

Inserisci questo **singolo shortcode** nella pagina dove vuoi mostrare la landing page:

```
[fp_landing_page id="123"]
```

Sostituisci `123` con l'ID della landing page (lo trovi nella meta box "Shortcodes Disponibili" quando modifichi la landing page).

### Shortcodes per Comporre la Landing Page

Gli shortcode seguenti si usano **nel contenuto della landing page** (non nella pagina finale) per creare le sezioni:

#### Hero Section
```
[fp_lp_hero title="Titolo Principale" subtitle="Sottotitolo" button_text="Clicca qui" button_url="#" image="url-immagine"]
```

#### Features
```
[fp_lp_features title="Le Nostre Features" columns="3"]
Titolo 1|🎯|Descrizione feature 1
Titolo 2|⚡|Descrizione feature 2
Titolo 3|🚀|Descrizione feature 3
[/fp_lp_features]
```

#### Call to Action
```
[fp_lp_cta title="Pronto a Iniziare?" text="Iscriviti ora e ottieni accesso immediato" button_text="Registrati" button_url="/registrazione" bg_color="#0073aa" text_color="#ffffff"]
```

#### Testimonials
```
[fp_lp_testimonials title="Cosa Dicono di Noi"]
Mario Rossi|CEO|Ottimo servizio, molto soddisfatto!|url-avatar.jpg
Giulia Bianchi|Designer|Fantastico, lo consiglio a tutti|url-avatar2.jpg
[/fp_lp_testimonials]
```

#### Pricing
```
[fp_lp_pricing title="I Nostri Piani"]
Base|€29|mese|Feature 1,Feature 2,Feature 3|Scegli|/registrazione
Pro|€59|mese|Tutte Base,Feature 4,Feature 5|Scegli|/registrazione
[/fp_lp_pricing]
```

#### Form Contatto
```
[fp_lp_form title="Contattaci" email="info@example.com" fields="name,email,message"]
```

## Struttura

```
FP-Landing-Page-1/
├── fp-landing-page.php       # File principale
├── composer.json              # Autoload PSR-4
├── src/
│   ├── Plugin.php            # Classe principale
│   ├── Activation.php        # Gestione attivazione
│   ├── Deactivation.php      # Gestione disattivazione
│   ├── Template.php          # Sistema template
│   ├── PostTypes/
│   │   └── LandingPage.php   # Custom Post Type
│   ├── Admin/
│   │   ├── MetaBoxes.php     # Meta box admin
│   │   └── Settings.php      # Impostazioni
│   ├── REST/
│   │   └── Controller.php    # API REST
│   └── Shortcodes/
│       └── Landing.php       # Shortcodes
├── assets/
│   └── css/
│       └── fp-landing-page.css  # Stili
└── templates/
    └── single-landing-page.php  # Template landing page
```

## Sviluppo

Il plugin segue il modello JUNCTION/LAB:
- **LAB**: Cartella sorgente (questa cartella in OneDrive\Desktop)
- **JUNCTION**: Symlink in `wp-content/plugins/FP-Landing-Page` che punta alla LAB

## Licenza
GPL v2 o successiva
