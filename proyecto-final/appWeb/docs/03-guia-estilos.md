# Guía de estilos

Sistema visual del front. Los valores deben coincidir con las variables de `assets/css/main.css`.

## 1. Principios

- **Mobile First:** el diseño base es para pantallas pequeñas; las mejoras se agregan con `min-width`.
- **Claridad:** jerarquía tipográfica simple y espaciado consistente.
- **Consistencia:** un solo conjunto de tokens para color, espacio, radio y sombra.
- **Accesibilidad:** contraste AA, foco visible y áreas táctiles de al menos 44 × 44 px.
- **Rendimiento:** sin fuentes ni librerías externas obligatorias.

## 2. Breakpoints

| Nombre | Ancho | Uso |
|---|---|---|
| Base | 0–479 px | Móvil |
| `sm` | ≥ 480 px | Móvil grande |
| `md` | ≥ 768 px | Tablet |
| `lg` | ≥ 1024 px | Escritorio |

```css
@media (min-width: 768px) {
  /* mejoras para tablet */
}
```

## 3. Tokens

### Color

| Token | Valor | Uso |
|---|---|---|
| `--color-primary` | `#1f5eff` | Acciones principales |
| `--color-primary-dark` | `#1746c4` | Hover y énfasis |
| `--color-accent` | `#00a67e` | Éxito y detalles |
| `--color-bg` | `#f7f8fb` | Fondo general |
| `--color-surface` | `#ffffff` | Tarjetas y formularios |
| `--color-text` | `#16202a` | Texto principal |
| `--color-muted` | `#5b6b7c` | Texto secundario |
| `--color-border` | `#d9e0e8` | Bordes |
| `--color-danger` | `#c0392b` | Errores |
| `--color-warning` | `#b7791f` | Advertencias |
| `--color-success` | `#1e7d4f` | Confirmaciones |

### Espaciado

| Token | Valor |
|---|---|
| `--space-1` | `0.25rem` |
| `--space-2` | `0.5rem` |
| `--space-3` | `0.75rem` |
| `--space-4` | `1rem` |
| `--space-5` | `1.5rem` |
| `--space-6` | `2rem` |
| `--space-7` | `3rem` |

### Tipografía

- Familia: `system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif`.
- Escala móvil: `1rem` base, títulos `1.5rem`, `1.875rem`, `2.25rem`.
- Altura de línea: `1.6` en párrafos y `1.2` en títulos.
- Ancho máximo de lectura: `65ch`.

### Bordes y sombras

| Token | Valor |
|---|---|
| `--radius-sm` | `0.375rem` |
| `--radius-md` | `0.75rem` |
| `--radius-lg` | `1rem` |
| `--shadow-sm` | `0 1px 2px rgb(22 32 42 / 8%)` |
| `--shadow-md` | `0 8px 24px rgb(22 32 42 / 10%)` |
| `--container` | `72rem` |

## 4. Layout

- Contenedor centrado: `.container` con `width: min(100% - 2rem, var(--container))`.
- Rejilla de tarjetas: una columna en móvil, dos en tablet y tres en escritorio.
- Espaciado vertical entre secciones: `var(--space-7)`.
- Evitar anchos fijos en píxeles; usar `max-width`, `min()`, `clamp()` y `fr`.

## 5. Componentes

### Botones

```css
.button {
  min-height: 44px;
  padding: var(--space-3) var(--space-5);
  border-radius: var(--radius-md);
  font-weight: 600;
}
.button--primary { background: var(--color-primary); color: #fff; }
.button--ghost { border: 1px solid var(--color-border); background: transparent; }
```

Estados: `:hover`, `:focus-visible`, `:disabled`, `.is-loading`.

### Formularios

- Un campo por fila en móvil; dos en tablet cuando tenga sentido.
- Etiqueta arriba del campo.
- Borde de error con `--color-danger` y texto debajo.
- Ayuda con `--color-muted` y tamaño `0.875rem`.
- Nunca usar el placeholder como única etiqueta.

### Tarjetas

- Fondo `--color-surface`, borde suave, radio `--radius-md`.
- Sombra solo al interactuar o para elementos destacados.
- Título, descripción y acción con jerarquía clara.

### Tablas

- En móvil, convertir a tarjetas o permitir scroll horizontal con contenedor `.table-scroll`.
- Encabezados con `scope="col"`.
- Estados con `.badge` y texto, no solo color.

### Alertas

- `.alert--success`, `.alert--error`, `.alert--warning`, `.alert--info`.
- Icono o texto que refuerce el significado.
- `role="status"` o `role="alert"` según urgencia.

## 6. Convención de clases

BEM:

```text
.card
.card__title
.card__body
.card--featured
```

Utilidades limitadas:

```text
.u-visually-hidden
.u-text-center
.u-mt-4
```

Evitar `!important` salvo utilidades justificadas.

## 7. Imágenes

- Formatos preferidos: SVG para iconos, WebP/AVIF para fotografías.
- Atributos `width` y `height` para evitar saltos de layout.
- `loading="lazy"` salvo la imagen principal.
- Texto alternativo descriptivo o `alt=""` si es decorativa.
- Logotipo en SVG con versión clara y oscura.

## 8. Modo oscuro (opcional)

Si se implementa:

- Tokens redefinidos en `@media (prefers-color-scheme: dark)`.
- Mantener contraste AA.
- No depender solo del color para comunicar estados.
- Probar formularios, tablas y badges.

## 9. Buenas prácticas

- No usar estilos en línea salvo casos excepcionales.
- No duplicar bloques CSS; extraer componentes.
- Preferir `rem` para tipografía y espaciado.
- Usar `clamp()` para títulos fluidos.
- Revisar en 320 px, 768 px y 1280 px antes de cada entrega.
