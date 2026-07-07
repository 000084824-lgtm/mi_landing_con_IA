Flor de Cerezo — Hero

Instrucciones rápidas

1) Servir localmente:

```bash
# Desde la carpeta del proyecto
python -m http.server 8000
# luego abrir http://localhost:8000
```

2) Reemplazar imágenes por locales (recomendado para producción):
- Crea `assets/images/` y coloca las imágenes con nombres claros, por ejemplo `hero-1200x630.jpg` y `hero-800x600.webp`.
- En `index.html` sustituye las URLs de `src` y `srcset` por rutas locales: `/assets/images/hero-800w.webp` etc.
- Genera WebP/AVIF y conserva un fallback JPEG.

3) Favicons y OG images:
- Añade un `assets/favicon.ico` y actualiza la ruta en el `<head>`.
- Para `og:image` usa una imagen 1200x630 optimizada.

4) Performance:
- Habilita compresión y cache headers en tu servidor/CDN.
- Genera tamaños y `srcset` adecuados para tu catálogo.

5) Accesibilidad y testing:
- Prueba con Lighthouse y axe-core.
- Verifica contraste de texto y comportamiento con teclado.

6) Notas:
- El proyecto actualmente usa imágenes de Picsum como ejemplo; reemplázalas por tus assets para estabilidad.
