/**
 * @file Raiders Paradise - Interacciones de la interfaz y gráficas SVG.
 *
 * JavaScript sin dependencias ni compilación. Se carga al final de los layouts
 * (tienda, admin y hoja) y se activa con atributos data-* en el HTML:
 *
 * | Atributo                         | Efecto                                                     |
 * |----------------------------------|------------------------------------------------------------|
 * | data-grafica='{"tipo":...}'      | Dibuja una gráfica (line, bar o donut) dentro del elemento |
 * | data-toggle="#id"                | Abre o cierra el elemento indicado (menús, sidebar móvil)  |
 * | data-paso="1" / "-1"             | Botones +/− de cantidad junto a un input numérico          |
 * | data-confirmar="mensaje"         | Pide confirmación antes de enviar un formulario            |
 * | data-autoenviar                  | Envía el formulario al cambiar un select                   |
 * | data-preview="#img"              | Vista previa de la imagen elegida en un input file         |
 * | data-rango="7|30|90|mes|anio"    | Llena las fechas desde/hasta de un formulario de reportes  |
 * | data-demo / data-pass            | Llena el login con una cuenta de prueba                    |
 * | data-autoimprimir (en <body>)    | Abre el diálogo de impresión (reportes PDF)                |
 * | .alerta-ok[data-auto]            | La alerta de éxito se oculta a los 6 segundos              |
 *
 * Los datos de las gráficas los genera App\Services\ReporteService con la forma:
 * {tipo: 'line'|'bar'|'donut', titulo: string, labels: string[], valores: number[], formato: 'money'|'int'}
 *
 * @author  Equipo de desarrollo Raiders Paradise (ADSO - SENA)
 * @version 2.0.0
 * @since   2026-10-29
 */
(function () {
    'use strict';

    /** Colores categóricos de las gráficas, derivados de la marca. @type {string[]} */
    const PALETA = ['#363088', '#E84615', '#1E2247', '#8f86d6', '#f39a6b', '#391C1D', '#6b6f8a'];
    /** Espacio de nombres para crear elementos SVG. @type {string} */
    const NS = 'http://www.w3.org/2000/svg';

    /**
     * Formatea un valor como pesos colombianos: 1250000 -> "$ 1.250.000".
     * @param {number} v Valor en pesos.
     * @returns {string}
     */
    const cop = (v) => '$ ' + Math.round(v).toLocaleString('es-CO');
    /**
     * Formato corto para ejes y totales: 2500000 -> "$2,5 M", 45000 -> "$45 mil".
     * @param {number} v Valor en pesos.
     * @returns {string}
     */
    const corto = (v) => {
        if (Math.abs(v) >= 1e6) return '$' + (v / 1e6).toLocaleString('es-CO', { maximumFractionDigits: 1 }) + ' M';
        if (Math.abs(v) >= 1e3) return '$' + Math.round(v / 1e3).toLocaleString('es-CO') + ' mil';
        return '$' + Math.round(v);
    };
    /**
     * Formatea un valor completo según el tipo de dato de la gráfica.
     * @param {number} v Valor.
     * @param {'money'|'int'} f Formato.
     * @returns {string}
     */
    const fmt = (v, f) => (f === 'money' ? cop(v) : Math.round(v).toLocaleString('es-CO'));
    /**
     * Formatea un valor de eje (abreviado si es dinero).
     * @param {number} v Valor.
     * @param {'money'|'int'} f Formato.
     * @returns {string}
     */
    const fmtEje = (v, f) => (f === 'money' ? corto(v) : Math.round(v).toLocaleString('es-CO'));

    /**
     * Crea un elemento SVG con atributos y opcionalmente lo agrega a un padre.
     * @param {string} tag Nombre de la etiqueta SVG (rect, path, text...).
     * @param {Object<string, string|number>} attrs Atributos del elemento.
     * @param {Element} [parent] Elemento contenedor.
     * @returns {SVGElement}
     */
    function el(tag, attrs, parent) {
        const n = document.createElementNS(NS, tag);
        for (const k in attrs) n.setAttribute(k, attrs[k]);
        if (parent) parent.appendChild(n);
        return n;
    }

    /**
     * Calcula un intervalo "redondo" (1, 2, 5 o 10 × 10^k) para las líneas de la rejilla.
     * @param {number} max Valor máximo de la serie.
     * @param {number} n Número aproximado de divisiones.
     * @returns {number}
     */
    function pasoBonito(max, n) {
        if (max <= 0) return 1;
        const bruto = max / n;
        const mag = Math.pow(10, Math.floor(Math.log10(bruto)));
        const r = bruto / mag;
        return (r <= 1 ? 1 : r <= 2 ? 2 : r <= 5 ? 5 : 10) * mag;
    }

    /**
     * Crea el globo de información que se muestra al pasar el mouse por una gráfica.
     * @param {HTMLElement} cont Contenedor de la gráfica.
     * @returns {{show: function(number, number, string): void, hide: function(): void}}
     */
    function tooltip(cont) {
        const t = document.createElement('div');
        t.className = 'grafica-tip';
        cont.appendChild(t);
        return {
            show(x, y, html) { t.innerHTML = html; t.style.left = x + 'px'; t.style.top = y + 'px'; t.style.opacity = 1; },
            hide() { t.style.opacity = 0; },
        };
    }

    /* ---------- Línea / área (ventas en el tiempo) ---------- */
    /**
     * Dibuja una gráfica de línea con área (series en el tiempo, por ejemplo ventas diarias).
     * @param {HTMLElement} cont Contenedor.
     * @param {{titulo: string, labels: string[], valores: number[], formato: string}} d Datos.
     * @returns {void}
     */
    function linea(cont, d) {
        const W = 720, H = 260, m = { t: 14, r: 12, b: 28, l: 70 };
        const svg = el('svg', { viewBox: `0 0 ${W} ${H}`, role: 'img', 'aria-label': d.titulo || 'Gráfica' }, cont);
        const vals = d.valores, n = vals.length;
        if (!n) return;
        const max = Math.max(...vals, 1);
        const paso = pasoBonito(max, 4), techo = Math.ceil(max / paso) * paso;
        const x = (i) => m.l + (n === 1 ? (W - m.l - m.r) / 2 : (i * (W - m.l - m.r)) / (n - 1));
        const y = (v) => m.t + (H - m.t - m.b) * (1 - v / techo);

        for (let v = 0; v <= techo + 1e-9; v += paso) {
            el('line', { x1: m.l, x2: W - m.r, y1: y(v), y2: y(v), class: 'rejilla' }, svg);
            el('text', { x: m.l - 10, y: y(v) + 4, 'text-anchor': 'end', class: 'eje' }, svg).textContent = fmtEje(v, d.formato);
        }
        const cada = Math.max(1, Math.ceil(n / 8));
        d.labels.forEach((l, i) => {
            if ((i % cada === 0 && n - 1 - i >= cada / 2) || i === n - 1) el('text', { x: x(i), y: H - 6, 'text-anchor': 'middle', class: 'eje' }, svg).textContent = l;
        });

        const defs = el('defs', {}, svg);
        const g = el('linearGradient', { id: 'area-' + Math.random().toString(36).slice(2), x1: 0, y1: 0, x2: 0, y2: 1 }, defs);
        el('stop', { offset: 0, 'stop-color': '#363088', 'stop-opacity': 0.28 }, g);
        el('stop', { offset: 1, 'stop-color': '#363088', 'stop-opacity': 0 }, g);
        const pts = vals.map((v, i) => [x(i), y(v)]);
        const path = pts.map((p, i) => (i ? 'L' : 'M') + p[0].toFixed(1) + ' ' + p[1].toFixed(1)).join(' ');
        el('path', { d: `${path} L${x(n - 1)} ${y(0)} L${x(0)} ${y(0)} Z`, fill: `url(#${g.id})` }, svg);
        el('path', { d: path, fill: 'none', stroke: '#363088', 'stroke-width': 2.5, 'stroke-linejoin': 'round', 'stroke-linecap': 'round' }, svg);

        // Punto final destacado
        const ult = pts[n - 1];
        el('circle', { cx: ult[0], cy: ult[1], r: 4.5, fill: '#E84615', stroke: '#fff', 'stroke-width': 2 }, svg);

        const tip = tooltip(cont);
        const guia = el('line', { y1: m.t, y2: H - m.b, stroke: '#1E2247', 'stroke-dasharray': '3 3', opacity: 0 }, svg);
        const punto = el('circle', { r: 5, fill: '#363088', stroke: '#fff', 'stroke-width': 2, opacity: 0 }, svg);
        const capa = el('rect', { x: m.l, y: m.t, width: W - m.l - m.r, height: H - m.t - m.b, fill: 'transparent' }, svg);
        capa.addEventListener('mousemove', (e) => {
            const r = svg.getBoundingClientRect();
            const px = ((e.clientX - r.left) / r.width) * W;
            const i = Math.max(0, Math.min(n - 1, Math.round(((px - m.l) / (W - m.l - m.r)) * (n - 1))));
            guia.setAttribute('x1', x(i)); guia.setAttribute('x2', x(i)); guia.setAttribute('opacity', 0.35);
            punto.setAttribute('cx', x(i)); punto.setAttribute('cy', y(vals[i])); punto.setAttribute('opacity', 1);
            tip.show((x(i) / W) * r.width, (y(vals[i]) / H) * r.height, `<strong>${d.labels[i]}</strong><br>${fmt(vals[i], d.formato)}`);
        });
        capa.addEventListener('mouseleave', () => { tip.hide(); guia.setAttribute('opacity', 0); punto.setAttribute('opacity', 0); });
    }

    /* ---------- Barras horizontales (rankings) ---------- */
    /**
     * Dibuja barras horizontales (rankings). La primera barra se resalta con el color brasa.
     * @param {HTMLElement} cont Contenedor.
     * @param {{titulo: string, labels: string[], valores: number[], formato: string}} d Datos.
     * @returns {void}
     */
    function barras(cont, d) {
        const n = d.valores.length;
        if (!n) return;
        const fila = 34, W = 720, etq = 210, der = 96;
        const H = n * fila + 6;
        const svg = el('svg', { viewBox: `0 0 ${W} ${H}`, role: 'img', 'aria-label': d.titulo || 'Gráfica' }, cont);
        const max = Math.max(...d.valores, 1);
        d.valores.forEach((v, i) => {
            const y = i * fila + 4;
            const ancho = Math.max(2, ((W - etq - der) * v) / max);
            const txt = d.labels[i].length > 30 ? d.labels[i].slice(0, 29) + '…' : d.labels[i];
            el('text', { x: etq - 10, y: y + 18, 'text-anchor': 'end', class: 'eje', style: 'font-size:12px;fill:#1E2247' }, svg).textContent = txt;
            const r = el('rect', { x: etq, y: y + 4, width: ancho, height: 20, rx: 4, fill: i === 0 ? '#E84615' : '#363088' }, svg);
            el('title', {}, r).textContent = `${d.labels[i]}: ${fmt(v, d.formato)}`;
            el('text', { x: etq + ancho + 8, y: y + 18, class: 'eje', style: 'font-size:12px;font-weight:600;fill:#1E2247' }, svg).textContent = fmtEje(v, d.formato);
        });
    }

    /* ---------- Dona (participación) ---------- */
    /**
     * Dibuja una gráfica de dona (participación) con su leyenda y porcentajes.
     * @param {HTMLElement} cont Contenedor.
     * @param {{titulo: string, labels: string[], valores: number[], formato: string}} d Datos.
     * @returns {void}
     */
    function dona(cont, d) {
        const total = d.valores.reduce((a, b) => a + b, 0);
        const wrap = document.createElement('div');
        wrap.className = 'dona-wrap';
        cont.appendChild(wrap);
        const S = 180, R = 80, r = 52, c = S / 2;
        const svg = el('svg', { viewBox: `0 0 ${S} ${S}`, role: 'img', 'aria-label': d.titulo || 'Gráfica' }, wrap);
        if (!total) {
            el('circle', { cx: c, cy: c, r: (R + r) / 2, fill: 'none', stroke: '#eceaf4', 'stroke-width': R - r }, svg);
        }
        let ang = -Math.PI / 2;
        d.valores.forEach((v, i) => {
            if (!v) return;
            const a = (v / total) * Math.PI * 2;
            const fin = ang + a - (a > 0.04 ? 0.012 : 0);
            const large = a > Math.PI ? 1 : 0;
            const p = (rad, rr) => [c + rr * Math.cos(rad), c + rr * Math.sin(rad)];
            let dPath;
            if (a >= Math.PI * 2 - 1e-6) {
                dPath = `M${c} ${c - R} A${R} ${R} 0 1 1 ${c - 0.01} ${c - R} L${c - 0.01} ${c - r} A${r} ${r} 0 1 0 ${c} ${c - r} Z`;
            } else {
                const [x1, y1] = p(ang, R), [x2, y2] = p(fin, R), [x3, y3] = p(fin, r), [x4, y4] = p(ang, r);
                dPath = `M${x1} ${y1} A${R} ${R} 0 ${large} 1 ${x2} ${y2} L${x3} ${y3} A${r} ${r} 0 ${large} 0 ${x4} ${y4} Z`;
            }
            const seg = el('path', { d: dPath, fill: PALETA[i % PALETA.length] }, svg);
            el('title', {}, seg).textContent = `${d.labels[i]}: ${fmt(v, d.formato)} (${((v / total) * 100).toFixed(1)}%)`;
            ang += a;
        });
        el('text', { x: c, y: c - 2, 'text-anchor': 'middle', style: 'font:700 20px Barlow Condensed, sans-serif;fill:#1E2247' }, svg).textContent = d.formato === 'money' ? corto(total) : total.toLocaleString('es-CO');
        el('text', { x: c, y: c + 16, 'text-anchor': 'middle', class: 'eje' }, svg).textContent = 'total';

        const ley = document.createElement('div');
        ley.className = 'leyenda';
        d.labels.forEach((l, i) => {
            const pct = total ? ((d.valores[i] / total) * 100).toFixed(0) + '%' : '0%';
            const row = document.createElement('div');
            row.innerHTML = `<i style="background:${PALETA[i % PALETA.length]}"></i>${l}<span>${pct}</span>`;
            ley.appendChild(row);
        });
        wrap.appendChild(ley);
    }

    /**
     * Busca todos los elementos [data-grafica] y dibuja la gráfica correspondiente.
     * Si no hay datos o el JSON es inválido, muestra un mensaje en su lugar.
     * @returns {void}
     */
    function pintarGraficas() {
        document.querySelectorAll('[data-grafica]').forEach((cont) => {
            try {
                const d = JSON.parse(cont.dataset.grafica);
                cont.innerHTML = '';
                if (!d || !d.valores || !d.valores.length) {
                    cont.innerHTML = '<p class="muted small">Sin datos para el periodo seleccionado.</p>';
                    return;
                }
                ({ line: linea, bar: barras, donut: dona }[d.tipo] || barras)(cont, d);
            } catch (e) {
                cont.innerHTML = '<p class="muted small">No fue posible dibujar la gráfica.</p>';
            }
        });
    }

    /* ---------- Interacciones generales ---------- */
    // ---- Delegación de clics: menús, +/−, cerrar alertas, cuentas demo y rangos de fecha ----
    document.addEventListener('click', (e) => {
        // Menús desplegables
        const toggle = e.target.closest('[data-toggle]');
        if (toggle) {
            const destino = document.querySelector(toggle.dataset.toggle);
            if (destino) {
                const clase = toggle.dataset.clase || 'abierto';
                const abierto = destino.classList.toggle(clase);
                toggle.setAttribute('aria-expanded', abierto);
                e.stopPropagation();
                return;
            }
        }
        document.querySelectorAll('.menu-usuario.abierto').forEach((m) => { if (!m.contains(e.target)) m.classList.remove('abierto'); });

        // Botones +/- de cantidad
        const paso = e.target.closest('[data-paso]');
        if (paso) {
            const input = paso.parentElement.querySelector('input');
            const nuevo = Math.min(+input.max || 99, Math.max(+input.min || 1, (+input.value || 1) + (+paso.dataset.paso)));
            input.value = nuevo;
        }

        // Cerrar alertas
        if (e.target.matches('.alerta .cerrar')) e.target.closest('.alerta').remove();

        // Rellenar cuentas de prueba en el login
        const demo = e.target.closest('[data-demo]');
        if (demo) {
            document.querySelector('#email').value = demo.dataset.demo;
            document.querySelector('#password').value = demo.dataset.pass;
            document.querySelector('#password').focus();
        }

        // Rangos rápidos de fechas en reportes
        const rango = e.target.closest('[data-rango]');
        if (rango) {
            e.preventDefault();
            const form = rango.closest('form');
            const hoy = new Date();
            const iso = (dt) => new Date(dt.getTime() - dt.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
            let desde = new Date(hoy);
            const r = rango.dataset.rango;
            if (r === 'mes') desde = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
            else if (r === 'anio') desde = new Date(hoy.getFullYear(), 0, 1);
            else desde.setDate(hoy.getDate() - (+r - 1));
            form.querySelector('[name=desde]').value = iso(desde);
            form.querySelector('[name=hasta]').value = iso(hoy);
        }
    });

    // ---- Confirmación antes de acciones destructivas (data-confirmar) ----
    document.addEventListener('submit', (e) => {
        const msg = e.target.dataset.confirmar;
        if (msg && !window.confirm(msg)) e.preventDefault();
    });

    // ---- Selects que envían el formulario y vista previa de imágenes ----
    document.addEventListener('change', (e) => {
        if (e.target.matches('[data-autoenviar]')) e.target.form.submit();
        // Vista previa de imagen al subir
        if (e.target.matches('[data-preview]') && e.target.files[0]) {
            const img = document.querySelector(e.target.dataset.preview);
            if (img) img.src = URL.createObjectURL(e.target.files[0]);
        }
    });

    // Impresión automática de reportes en PDF
    if (document.body.dataset.autoimprimir !== undefined) {
        window.addEventListener('load', () => setTimeout(() => window.print(), 400));
    }

    // Las alertas de éxito desaparecen solas
    document.querySelectorAll('.alerta-ok[data-auto]').forEach((a) => setTimeout(() => a.remove(), 6000));

    document.addEventListener('DOMContentLoaded', pintarGraficas);
    if (document.readyState !== 'loading') pintarGraficas();
})();
