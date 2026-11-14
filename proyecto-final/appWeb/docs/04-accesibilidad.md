# Accesibilidad

Criterios para que el sitio sea usable con teclado, lectores de pantalla y diferentes capacidades visuales.

## 1. HTML semántico

- Un solo `<h1>` por página y jerarquía de encabezados sin saltos.
- `<header>`, `<nav>`, `<main>`, `<section>`, `<article>` y `<footer>` según corresponda.
- Listas con `<ul>`/`<ol>` para navegación y catálogos.
- Botones con `<button>` y enlaces con `<a href>`; no usar `<div>` clicable.
- Formularios con `<form>`, `<label>`, `<fieldset>` y `<legend>`.

## 2. Formularios

- Cada campo tiene `<label for="...">` asociado.
- Los campos obligatorios se indican con texto, no solo con color.
- Los errores se muestran junto al campo y se anuncian con `aria-describedby`.
- El resumen de errores recibe el foco al enviar un formulario inválido.
- No usar el placeholder como etiqueta.
- Mantener los datos capturados tras un error.

```html
<div class="form__field">
  <label class="form__label" for="email">Correo electrónico</label>
  <input
    class="form__input"
    id="email"
    name="email"
    type="email"
    autocomplete="email"
    required
    aria-describedby="email-error"
  >
  <p class="form__error" id="email-error" role="alert">
    Ingresa un correo válido.
  </p>
</div>
```

## 3. Teclado

- Todo elemento interactivo es alcanzable con `Tab`.
- El orden de tabulación sigue el orden visual.
- `Esc` cierra modales y menús.
- El foco se mueve al modal al abrirlo y vuelve al disparador al cerrarlo.
- No atrapar el foco fuera de un modal abierto.
- Enlace “Saltar al contenido” como primer elemento enfocable.

```html
<a class="skip-link" href="#contenido">Saltar al contenido</a>
```

## 4. Foco visible

- Nunca eliminar el foco sin reemplazo.
- Usar `:focus-visible` con un contorno claro.

```css
:focus-visible {
  outline: 3px solid var(--color-primary);
  outline-offset: 2px;
}
```

## 5. Contraste y color

- Texto normal: contraste mínimo 4.5:1.
- Texto grande: mínimo 3:1.
- No comunicar estados solo con color; agregar texto o icono.
- Verificar botones, badges, enlaces y bordes de campos.

## 6. Imágenes y multimedia

- `alt` descriptivo en imágenes con contenido.
- `alt=""` en imágenes decorativas.
- No usar texto dentro de imágenes para contenido esencial.
- Si se agrega video, incluir subtítulos y controles.
- Evitar animaciones automáticas; respetar `prefers-reduced-motion`.

```css
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
  }
}
```

## 7. ARIA

- Usar ARIA solo cuando el HTML nativo no alcance.
- `aria-label` para botones con icono sin texto visible.
- `aria-current="page"` en el enlace activo.
- `aria-expanded` y `aria-controls` en menús desplegables.
- `role="alert"` para errores urgentes y `role="status"` para confirmaciones.
- No usar `aria-hidden` en elementos enfocables.

## 8. Tablas

- `<caption>` que describa la tabla.
- `<th scope="col">` y `<th scope="row">`.
- En móvil, permitir scroll horizontal o transformar en tarjetas.
- No usar tablas solo para layout.

## 9. Mensajes dinámicos

- Los errores y confirmaciones se anuncian en regiones `aria-live`.
- Evitar desplazamientos bruscos de layout.
- Deshabilitar el botón de envío mientras se procesa y anunciar el estado.
- No usar solo un spinner sin texto.

## 10. Pruebas

- [ ] Navegar toda la página solo con teclado.
- [ ] Verificar el orden de foco.
- [ ] Probar con lector de pantalla (NVDA, VoiceOver o TalkBack).
- [ ] Revisar contraste con una herramienta.
- [ ] Probar zoom al 200 %.
- [ ] Revisar en 320 px de ancho.
- [ ] Validar HTML con el validador del W3C.
- [ ] Verificar formularios con errores.
- [ ] Comprobar `prefers-reduced-motion`.
- [ ] Revisar que las imágenes tengan `alt` adecuado.

## 11. Referencias

- WCAG 2.2, nivel AA.
- MDN Accessibility.
- WAI-ARIA Authoring Practices.
