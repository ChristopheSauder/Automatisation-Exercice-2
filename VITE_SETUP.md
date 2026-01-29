# Configuration Vite - Mode Développement et Production

## Architecture

Vite bundlise les assets frontside et les servit soit via le dev server (HMR) soit via la build production.

```
assets/                    # 📝 Sources (vous éditez ici)
  ├─ style.css            # Styles source
  └─ script.js            # JavaScript source

public/build/              # 🏗️  Build production output
  ├─ manifest.json        # Mapping fichiers (pour PHP)
  ├─ assets/
  │  ├─ style-[hash].css  # Styles compilés (versionnés par hash)
  │  ├─ script.js         # Scripts compilés
  │  └─ main-[hash].js    # Entrée Vite
  └─ ...
```

## Mode Production (par défaut)

### Configuration
```bash
APP_ENV=prod
```

### Servir les assets
Les assets sont buildés une fois et servis depuis `public/build/`:

```bash
npm run build          # Compile assets → public/build/
```

Les fichiers HTML/Twig chargent depuis `/build/` en utilisant le `manifest.json` pour retrouver les fichiers hashés.

### Exemple de HTML généré
```html
<link rel="stylesheet" href="/build/assets/style-CGQc5HcV.css">
<script src="/build/assets/script.js"></script>
```

## Mode Développement

### Configuration
```bash
APP_ENV=dev
```

### Servir les assets avec HMR
Le Vite dev server tourne en parallèle et servit les assets avec Hot Module Reload:

```bash
# Terminal 1: Serveur PHP
docker compose up

# Terminal 2: Serveur Vite dev
npm run dev           # Tourne sur http://localhost:5173
```

Les fichiers HTML/Twig chargent depuis le dev server avec le HMR client:

### Exemple de HTML généré (mode dev)
```html
<script type="module" src="http://localhost:5173/@vite/client"></script>
<link rel="stylesheet" href="http://localhost:5173/assets/style.css">
<script src="http://localhost:5173/assets/script.js"></script>
```

## Commandes Vite

```bash
npm run dev            # 🔥 Dev server avec HMR (localhost:5173)
npm run build          # 🏗️  Build production (public/build/)
npm run preview        # 👀 Prévisualiser la build (localhost:4173)
npm run lint           # 🔍 Lint JavaScript (public/assets)
```

## Configuration

### vite.config.js
- **root**: `.` (racine du projet)
- **base**: `/build/` (URL path en production)
- **outDir**: `public/build/` (dossier output)
- **manifest**: `true` (génère manifest.json)
- **HMR**: `localhost:5173` (pour le dev)

### Twig Extension (src/Twig/MyExtension.php)

Nouvelle fonction: `vite_asset(entry)`

```php
public function viteAsset(string $entry): string
{
    $isDev = getenv('APP_ENV') === 'dev';
    
    if ($isDev) {
        // En dev: charger du dev server
        return 'http://localhost:5173/' . $entry;
    }

    // En prod: charger du manifest + build
    if (isset($this->manifest[$entry])) {
        return '/build/' . $this->manifest[$entry]['file'];
    }
    return "/build/{$entry}";
}
```

### layout.twig

```twig
{# HMR client (uniquement en dev) #}
{{ vite_client() | raw }}

{# Assets #}
<link rel="stylesheet" href="{{ vite_asset('assets/style.css') }}">
<script src="{{ vite_asset('assets/script.js') }}"></script>
```

## Workflow Développement

1. **Mettre à jour .env**: `APP_ENV=dev`
2. **Lancer Vite dev server**: `npm run dev`
3. **Modifier les fichiers** dans `assets/`
4. **Les changements reflètent automatiquement** dans le navigateur (HMR)

## Workflow Production

1. **Mettre à jour .env**: `APP_ENV=prod`
2. **Builder les assets**: `npm run build`
3. **Les fichiers bundlés** sont dans `public/build/`
4. **Déployer** le projet entier

## Dépannage

### Les assets ne se chargent pas

1. **Vérifier APP_ENV**:
   ```bash
   grep APP_ENV .env
   ```

2. **En mode dev**: 
   - Vérifier que `npm run dev` tourne sur 5173
   - Vérifier que le HTML contient `localhost:5173`

3. **En mode prod**:
   - Vérifier que `npm run build` s'est exécuté
   - Vérifier que `public/build/manifest.json` existe
   - Vérifier les URLs dans le HTML en inspectant

### HMR ne fonctionne pas

- Vérifier que `npm run dev` est lancé
- Vérifier la console du navigateur pour les erreurs WebSocket
- Essayer un hard refresh (Ctrl+Shift+R)

## Notes

- Les fichiers `assets/` sont des **sources**, ne pas les modifier directement en production
- Le `manifest.json` est généré automatiquement par Vite
- Les hash des fichiers changent à chaque build (sauf le contenu ne change pas)
- Le dev server ne écrit rien sur le disque (tout en mémoire)
