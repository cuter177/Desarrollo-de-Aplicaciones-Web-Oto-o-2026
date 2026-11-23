# Documentación en LaTeX

Los entregables finales deben presentarse en PDF. En esta etapa el proyecto LaTeX aún no se ha creado; este documento define cómo se generará.

## Herramientas

El equipo de desarrollo cuenta con:

```bash
latexmk --version
pdflatex --version
```

## Estructura prevista

```text
docs/latex/
├── main.tex              # Documento maestro
├── preambulo.tex         # Paquetes, fuentes y configuración
├── portada.tex
├── secciones/
│   ├── 01-introduccion.tex
│   ├── 02-requisitos.tex
│   ├── 03-modelacion.tex
│   ├── 04-desarrollo.tex
│   ├── 05-base-datos.tex
│   ├── 06-manual-usuario.tex
│   ├── 07-manual-tecnico.tex
│   ├── 08-contrato.tex
│   └── 09-conclusiones.tex
├── figuras/
├── bibliografia.bib
└── build/                # Salida (ignorada por Git)
```

## Compilación

```bash
cd docs/latex
latexmk -pdf -outdir=build main.tex
```

Para limpiar auxiliares:

```bash
latexmk -C -outdir=build main.tex
```

## Paquetes previstos

- `babel` (español) y `fontenc`/`inputenc` (UTF-8).
- `geometry` para márgenes.
- `graphicx` para figuras.
- `hyperref` para enlaces.
- `listings` o `minted` para código.
- `booktabs` y `longtable` para tablas.
- `titlesec` y `fancyhdr` para formato.
- `biblatex` para referencias.

## Contenido

El documento maestro consolidará:

1. Portada e identificación del equipo.
2. Introducción y objetivo del proyecto.
3. Requisitos funcionales y no funcionales.
4. Modelación: mapa de sitio, guiones y navegación.
5. Desarrollo: arquitectura, frontend, backend y base de datos.
6. Manual de usuario.
7. Manual técnico.
8. Contrato y alcances.
9. Conclusiones y trabajo futuro.

## Pendientes

- [ ] Crear `main.tex` y el preámbulo.
- [ ] Convertir los documentos Markdown a secciones LaTeX.
- [ ] Exportar los diagramas Mermaid como imágenes.
- [ ] Incluir capturas de pantalla del sistema terminado.
- [ ] Generar los PDFs y anexarlos a la entrega.
